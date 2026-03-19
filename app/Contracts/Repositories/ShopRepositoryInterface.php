<?php

namespace App\Contracts\Repositories;

interface ShopRepositoryInterface extends RepositoryInterface
{
    public function updateWhere(array $params, array $data): bool;
}
