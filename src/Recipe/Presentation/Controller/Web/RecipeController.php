<?php

namespace App\Recipe\Presentation\Controller\Web;

use App\Recipe\Application\SearchRecipe\SearchRecipeQuery;
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

    #[Route('/recipes', name: 'recipe_search')]
    public function search(Request $request)
    {
        $searchTerm = $request->query->get('searchTerm');

        $response = $this->queryBus->ask(new SearchRecipeQuery($searchTerm));

        return $this->render('recipes/search.html.twig', [
            'searchTerm' => ucfirst($searchTerm),
            'recipes' => $response->getRecipesData()
        ]);
    }    

    #[Route('/recipes/{id}', name: 'recipe_detail')]
    public function detail(string $id): Response
    {
        $response = $this->queryBus->ask(new ShowRecipeDetailQuery($id));

        return $this->render('recipes/detail.html.twig', ['recipeData' => $response->getRecipeDetailsData()]);
    }
}
