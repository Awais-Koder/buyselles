<?php

namespace App\Contracts\Repositories;

interface WishlistRepositoryInterface extends RepositoryInterface
{
    public function getListWhereCount(?string $searchValue = null, array $filters = [], array $relations = []): int;

    public function getCount(array $params): ?int;
}
