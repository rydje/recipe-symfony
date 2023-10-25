<?php

namespace App\Recipe\Application\ListRecipes;

use App\Recipe\Domain\Gateway\ExternalRecipeGateway;
use App\Shared\Application\Query\QueryHandler;

class ListRecipesHandler implements QueryHandler
{
    public function __construct(
        private ExternalRecipeGateway $externalRecipeGateway
    )
    {
    }

    public function __invoke(ListRecipesQuery $query): ListRecipesResponse
    {
        $recipes = $this->externalRecipeGateway->list();

        $response = new ListRecipesResponse();
        $response->addRecipes($recipes);

        return $response;
    }
}