<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface WithdrawRequestRepositoryInterface extends RepositoryInterface
{
    public function getListWhereNull(array $orderBy = [], ?string $searchValue = null, array $filters = [], array $nullFilters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, ?int $offset = null): Collection|LengthAwarePaginator;

    public function getFirstWhereNotNull(array $params, array $filters = [], array $orderBy = [], array $relations = []): ?Model;
}
