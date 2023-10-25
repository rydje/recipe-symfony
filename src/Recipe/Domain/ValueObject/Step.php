<?php

namespace App\Recipe\Domain\ValueObject;

class Step
{
    public function __construct(
        private string $description,
    ) {}

    public function description(): string
    {
        return $this->description;
    }
}