<?php

namespace App\Recipe\Infrastructure\Gateway;

use App\Recipe\Domain\Entity\Recipe;
use App\Recipe\Domain\Gateway\ExternalRecipeGateway;
use App\Recipe\Domain\ValueObject\Ingredient;
use App\Recipe\Domain\ValueObject\Step;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SpoonacularExternalRecipeGateway implements ExternalRecipeGateway
{
    const RETRY_THRESHOLD = 3;

    private FilesystemAdapter $cache;

    public function __construct(
        private LoggerInterface $logger,
        private HttpClientInterface $client,
        private string $apiKey
    )
    {
        $this->cache = new FilesystemAdapter();
    }

    public function search($searchTerm): array
    {
        $content = $this->cache->get('spoonacular_search_' . $searchTerm, function (ItemInterface $item) use ($searchTerm): array {
            $item->expiresAfter(3600);

            $response = $this->client->request(
                'GET',
                'https://api.spoonacular.com/recipes/complexSearch?query=' . $searchTerm . '&number=10&apiKey=' . $this->apiKey
            );
    
            $statusCode = $response->getStatusCode();
            if ($statusCode != 200) {
                $this->logger->warning('Exception during Spoonacular API call', [
                    'http-status' => $response->getStatusCode(),
                    'content' => $response->toArray()
                ]);
                throw new Exception('Problem with Spoonacular API');
            }
            $content = $response->toArray();

            return $content;
        });

        $recipes = [];
        try {
            foreach ($content['results'] as $recipeData) {
                $recipes[] = $this->buildRecipe($recipeData);
            }
        } catch (Exception $e) {
            $this->logger->warning('Exception during Spoonacular::search(' . $searchTerm . ') buildRecipe', ['exceptionMessage' => $e->getMessage(), 'content' => $content]);
            $this->cache->delete('spoonacular_search_' . $searchTerm);
        }

        return $recipes;
    }

    public function list($tries = 0): array
    {
        if ($tries >= self::RETRY_THRESHOLD) {
            $this->logger->warning('Spoonacular API retry threshold reached.');
            return [];
        }

        // The callable will only be executed on a cache miss.
        $content = $this->cache->get('spoonacular_list', function (ItemInterface $item): array {
            $item->expiresAfter(3600);

            // ... do some HTTP request or heavy computations
            $response = $this->client->request(
                'GET',
                'https://api.spoonacular.com/recipes/random?number=10&apiKey=' . $this->apiKey
            );
    
            $statusCode = $response->getStatusCode();
            if ($statusCode != 200) {
                $this->logger->warning('Exception during Spoonacular API call', [
                    'http-status' => $response->getStatusCode(),
                    'content' => $response->toArray()
                ]);
                throw new Exception('Problem with Spoonacular API');
            }
            $content = $response->toArray();

            return $content;
        });

        $recipes = [];
        try {
            foreach ($content['recipes'] as $recipeData) {
                $recipes[] = $this->buildRecipe($recipeData);
            }
        } catch (Exception $e) {
            $this->logger->warning('Exception during Spoonacular::list() buildRecipe', ['exceptionMessage' => $e->getMessage(), 'recipeData' => $recipeData]);
            $this->cache->delete('spoonacular_list');
            $recipes = $this->list(++$tries);
        }

        return $recipes;
    }

    public function detail(string $id, int $tries = 0): ?Recipe
    {
        $content = $this->cache->get('spoonacular_detail.' . $id, function (ItemInterface $item) use ($id): array {
            $item->expiresAfter(3600);

            $response = $this->client->request(
                'GET',
                'https://api.spoonacular.com/recipes/' . $id . '/information?&apiKey=' . $this->apiKey
            );
    
            $statusCode = $response->getStatusCode();
            if ($statusCode != 200) {
                // TODO: throw more specific exception or fallback on something else
                // TODO: add logging (error details, API quota...)
                $this->logger->warning('Exception during Spoonacular API call', [
                    'http-status' => $response->getStatusCode(),
                    'content' => $response->toArray()
                ]);
                throw new Exception('Problem with Spoonacular API');
            }
            $content = $response->toArray();

            return $content;
        });

        try {
            $recipe = $this->buildRecipe($content, true, true);
        } catch (Exception $e) {
            $this->logger->warning('Exception during Spoonacular::detail() buildRecipe', ['exception' => $e]);
            $this->cache->delete('spoonacular_detail.' . $id);
            $this->detail($id, ++$tries);
        }

        return $recipe;
    }

    private function buildRecipe($recipeData, bool $buildIngredients = false, bool $buildDirection = false): Recipe
    {
        $ingredients = [];
        if ($buildIngredients) {
            foreach ($recipeData['extendedIngredients'] as $ingredient) {
                $ingredients[] = $this->buildIngredient($ingredient);
            }
        }

        return new Recipe(
            $recipeData['id'],
            $recipeData['title'],
            0,
            $ingredients,
            $recipeData['image'],
            $buildDirection ? $this->buildDirection($recipeData['analyzedInstructions']) : [],
            $recipeData['preparationMinutes'] ?? -1,
            $recipeData['cookingMinutes'] ?? -1,
            $recipeData['readyInMinutes'] ?? -1,
            $recipeData['servings'] ?? 1
        );
    }

    private function buildIngredient($ingredientData): Ingredient
    {
        $unit = $ingredientData['amount'];
        if ($ingredientData['unit'] && ($ingredientData['amount'] != $ingredientData['unit'])) {
            $unit .= ' ' . $ingredientData['unit'];
        }
        return new Ingredient($ingredientData['name'], $unit);
    }

    private function buildDirection($directionData): array
    {
        $steps = [];
        foreach ($directionData as $directionGroup) {
            foreach ($directionGroup['steps'] as $step) {
                $steps[] = new Step($step['step']);
            }   
        }

        return $steps;
    }
}
