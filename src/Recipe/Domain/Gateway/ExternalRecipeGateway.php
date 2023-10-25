<?php

namespace App\Recipe\Domain\Gateway;

use App\Recipe\Domain\Entity\Recipe;

interface ExternalRecipeGateway
{
    public function search($search): array;
    public function list(): array;
    public function detail(string $id): ?Recipe;
}