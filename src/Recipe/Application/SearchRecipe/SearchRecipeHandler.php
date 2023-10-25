<?php

namespace App\Recipe\Application\SearchRecipe;

use App\Recipe\Domain\Gateway\ExternalRecipeGateway;
use App\Shared\Application\Query\QueryHandler;

class SearchRecipeHandler implements QueryHandler
{
    public function __construct(
        private ExternalRecipeGateway $externalRecipeGateway
    )
    {
    }

    public function __invoke(SearchRecipeQuery $query): SearchRecipeResponse
    {
        $recipes = $this->externalRecipeGateway->search($query->searchTerm());

        $response = new SearchRecipeResponse();
        $response->addRecipes($recipes);

        return $response;
    }
}