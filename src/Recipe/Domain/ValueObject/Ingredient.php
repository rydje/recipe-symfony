<?php

namespace App\Recipe\Domain\ValueObject;

class Ingredient
{
    public function __construct(
        private string $name,
        private string $quantity
    ) {}

    public function nameAndQuantity(): string
    {
        return $this->quantity . ' ' . $this->name;
    }
}