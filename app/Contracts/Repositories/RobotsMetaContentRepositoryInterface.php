<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface RobotsMetaContentRepositoryInterface extends RepositoryInterface
{
    public function getListWhereNotIn(array $orderBy = [], ?string $searchValue = null, array $filters = [], array $relations = [], array $nullFields = [], array $whereNotIn = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, ?int $offset = null): Collection|LengthAwarePaginator;

    public function updateByParams(array $params, array $data): bool;

    public function updateOrInsert(array $params, array $data): bool;
}
