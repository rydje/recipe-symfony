<?php

namespace App\Recipe\Domain\Entity;

class Recipe
{
    public function __construct(
        private string $id,
        private string $name,
        private int $calories,
        private array $ingredients,
        private string $imageUrl,
        private array $direction,
        private int $prepTime,
        private int $cookTime,
        private int $readyIn,
        private int $servings,
    )
    {
    }

    public function id(): string
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function calories(): int
    {
        return $this->name;
    }

    public function ingredients(): array
    {
        return $this->ingredients;
    }
    
    public function imageUrl(): string
    {
        return $this->imageUrl;
    }

    public function direction(): array
    {
        return $this->direction;
    }

    public function prepTime(): int
    {
        return $this->prepTime;
    }

    public function cookTime(): int
    {
        return $this->cookTime;
    }

    public function servings(): int
    {
        return $this->servings;
    }

    public function readyIn(): int
    {
        return $this->readyIn;
    }
}