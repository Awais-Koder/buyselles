<?php

namespace App\Contracts\Repositories;

interface CategoryShippingCostRepositoryInterface extends RepositoryInterface
{
    public function updateOrInsert(array $params, array $data): bool;
}
