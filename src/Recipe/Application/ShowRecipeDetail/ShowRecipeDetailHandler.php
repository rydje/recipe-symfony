<?php

namespace App\Recipe\Application\ShowRecipeDetail;

use App\Recipe\Domain\Gateway\ExternalRecipeGateway;
use App\Shared\Application\Query\QueryHandler;

class ShowRecipeDetailHandler implements QueryHandler
{
    public function __construct(
        private ExternalRecipeGateway $externalRecipeGateway
    )
    {
    }

    public function __invoke(ShowRecipeDetailQuery $query): ShowRecipeDetailResponse
    {
        $recipe = $this->externalRecipeGateway->detail($query->id());

        $response = new ShowRecipeDetailResponse();
        $response->addRecipeDetails($recipe);

        return $response;
    }
}