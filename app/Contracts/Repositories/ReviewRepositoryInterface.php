<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface ReviewRepositoryInterface extends RepositoryInterface
{
    public function getListWhereIn(bool $globalScope = true, array $orderBy = [], ?string $searchValue = null, array $filters = [], array $whereInFilters = [], array $relations = [], array $nullFields = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, ?int $offset = null): Collection|LengthAwarePaginator;

    public function getListWhereHas(string $whereHas, array $whereHasFilter, array $orderBy = [], ?string $searchValue = null, array $filters = [], array $whereInFilters = [], array $relations = [], array $nullFields = [], bool $globalScope = true, int|string $dataLimit = DEFAULT_DATA_LIMIT, ?int $offset = null): Collection|LengthAwarePaginator;

    public function updateWhere(array $params, array $data): bool;

    public function updateOrInsert(array $params, array $data): bool;

    public function getCount(array $params, array $whereInFilters): ?int;
}
