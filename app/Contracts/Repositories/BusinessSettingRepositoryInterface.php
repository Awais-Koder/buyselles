<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface BusinessSettingRepositoryInterface extends RepositoryInterface
{
    public function getListWhereIn(array $orderBy = [], ?string $searchValue = null, array $filters = [], array $whereInFilters = [], array $relations = [], array $nullFields = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, ?int $offset = null): Collection|LengthAwarePaginator;

    public function updateOrInsert(string $type, mixed $value): bool;

    public function updateWhere(array $params, array $data): bool;

    public function whereJsonContains(array $params, array $value): ?Model;
}
