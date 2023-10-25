<?php

namespace App\Recipe\Application\ShowRecipeDetail;

use App\Recipe\Domain\Entity\Recipe;
use App\Shared\Application\Query\Response;

class ShowRecipeDetailResponse implements Response
{
    private array $recipeDetailsData = [];

    public function addRecipeDetails(Recipe $recipe)
    {
        $this->recipeDetailsData = [
            'id' => $recipe->id(),
            'name' => $recipe->name(),
            'ingredients' => $this->getIngredientsData($recipe),
            'imageUrl' => $recipe->imageUrl(),
            'direction' => $this->getDirectionData($recipe),
            'prepTime' => $recipe->prepTime(),
            'cookingTime' => $recipe->cookTime(),
            'servings' => $recipe->servings()
        ];
    }

    public function getRecipeDetailsData(): array
    {
        return $this->recipeDetailsData;
    }

    private function getIngredientsData(Recipe $recipe): array
    {
        $ingredientsData = [];
        foreach ($recipe->ingredients() as $ingredient) {
            $ingredientsData[] = [
                'name' => $ingredient->nameAndQuantity()
            ];
        }

        return $ingredientsData;
    }

    private function getDirectionData(Recipe $recipe): array
    {
        $ingredientsData = [];
        foreach ($recipe->direction() as $direction) {
            $ingredientsData[] = [
                'description' => $direction->description()
            ];
        }

        return $ingredientsData;
    }
}
