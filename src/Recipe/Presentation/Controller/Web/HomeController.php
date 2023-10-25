<?php

namespace App\Recipe\Presentation\Controller\Web;

use App\Recipe\Application\ListRecipes\ListRecipesQuery;
use App\Recipe\Domain\Gateway\ExternalRecipeGateway;
use App\Shared\Application\Query\QueryBus;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private ExternalRecipeGateway $externalRecipeGateway,
        private QueryBus $queryBus
    ) {
    }

    #[Route('/', name: 'home')]
    public function home(): Response
    {
        $popularCategories = [
            ['name' => 'Pasta', 'image' => '/images/pasta.jpg', 'searchTerm' => 'pasta'],
            ['name' => 'Pizza', 'image' => '/images/pizza.jpg', 'searchTerm' => 'pizza'],
            ['name' => 'Vegan', 'image' => '/images/vegan.jpg', 'searchTerm' => 'vegan'],
            ['name' => 'Chicken', 'image' => '/images/chicken.jpg', 'searchTerm' => 'chicken'],
            ['name' => 'Desserts', 'image' => '/images/dessert.jpg', 'searchTerm' => 'desserts'],
            ['name' => 'Breakfast', 'image' => '/images/breakfast.jpg', 'searchTerm' => 'breakfast'],
        ];

        $response = $this->queryBus->ask(new ListRecipesQuery());
        $todayRecipe = $response->getRecipesData();

        return $this->render('home.html.twig', [
            'popularCategories' => $popularCategories,
            'recipes' => $todayRecipe
        ]);
    }
}
