<?php

namespace App\Recipe\Presentation\Controller\API;

use App\Recipe\Application\ListRecipes\ListRecipesHandler;
use App\Recipe\Application\SearchRecipe\SearchRecipeQuery;
use App\Recipe\Application\ShowRecipeDetail\ShowRecipeDetailHandler;
use App\Recipe\Application\ShowRecipeDetail\ShowRecipeDetailQuery;
use App\Recipe\Domain\Gateway\ExternalRecipeGateway;
use App\Shared\Application\Query\QueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RecipeController extends AbstractController
{
    public function __construct(
        private ExternalRecipeGateway $externalRecipeGateway,
        private QueryBus $queryBus
    ) {
    }

    #[Route('/recipe', name: 'api.recipe_list')]
    public function list(Request $request): Response
    {
        $response = $this->queryBus->ask(new SearchRecipeQuery($request->query->get('searchTerm', '')));

        return $this->json($response->getRecipesData());
    }

    #[Route('/recipe/{id}', name: 'api.recipe_detail', methods: ['GET', 'HEAD'])]
    public function detail(string $id): Response
    {
        $response = $this->queryBus->ask(new ShowRecipeDetailQuery($id));

        return $this->json($response->getRecipeDetailsData());
    }
}
