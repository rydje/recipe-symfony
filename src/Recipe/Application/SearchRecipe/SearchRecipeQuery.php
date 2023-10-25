<?php

namespace App\Recipe\Application\SearchRecipe;

use App\Shared\Application\Query\Query;

class SearchRecipeQuery implements Query
{
    public function __construct(
        private readonly string $searchTerm
    )
    {
    }

    public function searchTerm(): string
    {
        return $this->searchTerm;
    }
}