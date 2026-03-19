<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface SettingRepositoryInterface extends RepositoryInterface
{
    public function getListWhereIn(array $orderBy = [], ?string $searchValue = null, array $filters = [], array $whereInFilters = [], array $relations = [], array $nullFields = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, ?int $offset = null): Collection|LengthAwarePaginator;

    public function updateOrInsert(array $params, array $data): bool;

    public function updateWhere(array $params, array $data): bool;
}
