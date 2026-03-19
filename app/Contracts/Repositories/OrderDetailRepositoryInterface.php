<?php

namespace App\Contracts\Repositories;

interface OrderDetailRepositoryInterface extends RepositoryInterface
{
    public function updateWhere(array $params, array $data): bool;

    public function getListWhereCount(?string $searchValue = null, array $filters = [], array $relations = []): int;
}
