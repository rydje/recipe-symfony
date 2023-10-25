<?php

namespace App\Recipe\Infrastructure\Gateway;

use App\Recipe\Domain\Entity\Recipe;
use App\Recipe\Domain\Gateway\ExternalRecipeGateway;
use App\Recipe\Domain\ValueObject\Ingredient;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class EdamamExternalRecipeGateway implements ExternalRecipeGateway
{
    private FilesystemAdapter $cache;

    public function __construct(
        private LoggerInterface $logger,
        private HttpClientInterface $client,
        private string $apiKey,
        private string $appId
    ) {
        $this->cache = new FilesystemAdapter();
    }

    public function search($searchTerm): array
    {
        $content = $this->cache->get('edemam_search' . $searchTerm, function (ItemInterface $item): array {
            $item->expiresAfter(3600);

            $queryString = http_build_query([
                'type' => 'public',
                'app_id' => $this->appId,
                'app_key' => $this->apiKey,
                'random' => 'true',
            ]) . '&field=label&field=image&field=yield&field=ingredientLines&field=calories&field=totalTime&field=uri&field=externalId'
            . '&cuisineType=American&cuisineType=Asian&cuisineType=British&cuisineType=Caribbean&cuisineType=Central%20Europe&cuisineType=Chinese&cuisineType=Eastern%20Europe&cuisineType=French&cuisineType=Indian&cuisineType=Italian&cuisineType=Japanese&cuisineType=Kosher&cuisineType=Mediterranean&cuisineType=Mexican&cuisineType=Middle%20Eastern&cuisineType=Nordic&cuisineType=South%20American&cuisineType=South%20East%20Asian';

            $response = $this->client->request(
                'GET',
                'https://api.edamam.com/api/recipes/v2?' . $queryString,
            );

            $statusCode = $response->getStatusCode();
            if ($statusCode != 200) {
                $this->logger->warning('Exception during Edamam API call', [
                    'http-status' => $response->getStatusCode(),
                    'content' => $response->toArray(),
                ]);
                throw new Exception('Problem with Edamam API');
            }
            $content = $response->toArray();

            return $content;
        });

        if (!$content['count']) {
            $this->logger->warning('Edamam::list() returns empty results');
            return [];
        }

        $recipes = [];
        try {
            foreach ($content['hits'] as $recipeData) {
                $recipes[] = $this->buildRecipe($recipeData['recipe']);
            }
        } catch (Exception $e) {
            $this->logger->warning('Exception during Edamam::list() buildRecipe', ['exceptionMessage' => $e->getMessage(), 'recipeData' => $recipeData]);
            return [];
        }

        return $recipes;
    }

    public function list(): array
    {
        $content = $this->cache->get('edemam_list', function (ItemInterface $item): array {
            $item->expiresAfter(3600);

            $queryString = http_build_query([
                'type' => 'public',
                'app_id' => $this->appId,
                'app_key' => $this->apiKey,
                'random' => 'true',
            ]) . '&field=label&field=image&field=yield&field=ingredientLines&field=calories&field=totalTime&field=uri&field=externalId'
            . '&cuisineType=American&cuisineType=Asian&cuisineType=British&cuisineType=Caribbean&cuisineType=Central%20Europe&cuisineType=Chinese&cuisineType=Eastern%20Europe&cuisineType=French&cuisineType=Indian&cuisineType=Italian&cuisineType=Japanese&cuisineType=Kosher&cuisineType=Mediterranean&cuisineType=Mexican&cuisineType=Middle%20Eastern&cuisineType=Nordic&cuisineType=South%20American&cuisineType=South%20East%20Asian';

            $response = $this->client->request(
                'GET',
                'https://api.edamam.com/api/recipes/v2?' . $queryString,
            );

            $statusCode = $response->getStatusCode();
            if ($statusCode != 200) {
                $this->logger->warning('Exception during Edamam API call', [
                    'http-status' => $response->getStatusCode(),
                    'content' => $response->toArray(),
                ]);
                throw new Exception('Problem with Edamam API');
            }
            $content = $response->toArray();

            return $content;
        });

        if (!$content['count']) {
            $this->logger->warning('Edamam::list() returns empty results');
            return [];
        }

        $recipes = [];
        try {
            foreach ($content['hits'] as $recipeData) {
                $recipes[] = $this->buildRecipe($recipeData['recipe']);
            }
        } catch (Exception $e) {
            $this->logger->warning('Exception during Edamam::list() buildRecipe', ['exceptionMessage' => $e->getMessage(), 'recipeData' => $recipeData]);
            return [];
        }

        return $recipes;
    }

    public function detail(string $id): ?Recipe
    {
        $content = $this->cache->get('edemam_detail' . $id, function (ItemInterface $item) use ($id): array {
            $item->expiresAfter(3600);

            $queryString = http_build_query([
                'type' => 'public',
                'app_id' => $this->appId,
                'app_key' => $this->apiKey,
                'random' => 'true',
                ''
            ]) . '&field=label&field=image&field=yield&field=ingredientLines&field=calories&field=totalTime&field=uri&field=externalId';

            $response = $this->client->request(
                'GET',
                'https://api.edamam.com/api/recipes/v2/' . $id . '?' . $queryString,
            );

            $statusCode = $response->getStatusCode();
            if ($statusCode != 200) {
                $this->logger->warning('Exception during Edamam API call', [
                    'http-status' => $response->getStatusCode(),
                    'content' => $response->toArray(),
                ]);
                throw new Exception('Problem with Edamam API');
            }
            $content = $response->toArray();

            return $content;
        });

        try {
            $recipe = $this->buildRecipe($content['recipe']);
        } catch (Exception $e) {
            $this->logger->warning('Exception during Spoonacular::detail() buildRecipe', ['exception' => $e]);
            $this->cache->delete('spoonacular_detail.' . $id);
        }

        return $recipe;
    }

    private function buildRecipe($recipeData): ?Recipe
    {
        $ingredients = [];
        foreach ($recipeData['ingredientLines'] as $ingredient) {
            $ingredients[] = $this->buildIngredient($ingredient);
        }

        $recipeId = substr($recipeData['uri'], strpos($recipeData['uri'], "_") + 1);
        return new Recipe(
            $recipeId,
            $recipeData['label'],
            $recipeData['calories'],
            $ingredients,
            $recipeData['image'],
            [],
            -1,
            -1,
            $recipeData['totalTime'],
            $recipeData['yield']
        );
    }

    private function buildIngredient($ingredientData): Ingredient
    {
        return new Ingredient($ingredientData, '');
    }

    private function buildDirection($directionData): array
    {
    }
}
