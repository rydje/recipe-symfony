<?php

namespace App\Recipe\Application\ListRecipes;

use App\Recipe\Domain\Entity\Recipe;
use App\Shared\Application\Query\Response;

class ListRecipesResponse implements Response
{
    private array $recipesData = [];

    /**
     * @param Recipe[] $recipes
     */
    public function addRecipes(array $recipes)
    {
        foreach($recipes as $recipe) {
            $this->recipesData[] = [
                'id' => $recipe->id(),
                'name' => $recipe->name(),
                'imageUrl' => $recipe->imageUrl(),
                'servings' => $recipe->servings(),
                'readyIn' => $recipe->readyIn()
            ];
        }
    }

    public function getRecipesData(): array
    {
        return $this->recipesData;
    }
}