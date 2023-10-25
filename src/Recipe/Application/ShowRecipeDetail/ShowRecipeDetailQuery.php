<?php

namespace App\Recipe\Application\ShowRecipeDetail;

use App\Shared\Application\Query\Query;

class ShowRecipeDetailQuery implements Query
{
    public function __construct(
        private readonly string $id
    )
    {
    }

    public function id(): string
    {
        return $this->id;
    }
}