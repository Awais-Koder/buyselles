<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface DeliveryManRepositoryInterface extends RepositoryInterface
{
    public function getTopRatedList(array $orderBy = [], array $filters = [], array $whereHasFilters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, ?int $offset = null): Collection|LengthAwarePaginator;

    public function getListWhereIn(array $orderBy = [], ?string $searchValue = null, array $filters = [], array $relations = [], array $nullFields = [], array $withCounts = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, ?int $offset = null): Collection|LengthAwarePaginator;
}
