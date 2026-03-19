<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface extends RepositoryInterface
{
    public function getFirstWhereActive(array $params, array $relations = []): ?Model;

    public function getWebFirstWhereActive(array $params, array $relations = [], array $withCount = []): ?Model;

    public function getFirstWhereWithoutGlobalScope(array $params, array $relations = []): ?Model;

    public function getStockLimitListWhere(array $orderBy = [], ?string $searchValue = null, array $filters = [], array $withCount = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, ?int $offset = null): Collection|LengthAwarePaginator;

    public function addArray(array $data): bool;

    public function getListWhereNotIn(array $filters = [], array $whereNotIn = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, ?int $offset = null): Collection|LengthAwarePaginator;

    public function getListWithScope(array $orderBy = [], ?string $searchValue = null, ?string $scope = null, array $filters = [], array $whereIn = [], array $whereNotIn = [], array $relations = [], array $withCount = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, ?int $offset = null): Collection|LengthAwarePaginator;

    public function getWebListWithScope(array $orderBy = [], ?string $searchValue = null, ?string $scope = null, array $filters = [], array $whereHas = [], array $whereIn = [], array $whereNotIn = [], array $relations = [], array $withCount = [], array $withSum = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, ?int $offset = null): Collection|LengthAwarePaginator;

    public function getTopRatedList(array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, ?int $offset = null): Collection|LengthAwarePaginator;

    public function getTopSellList(array $orderBy = [], array $filters = [], array $whereHas = [], array $whereIn = [], array $whereNotIn = [], array $relations = [], array $withCount = [], array $withSum = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, ?int $offset = null): Collection|LengthAwarePaginator;

    public function getFirstWhereWithCount(array $params, array $withCount = [], array $relations = []): ?Model;

    /**
     * @return Collection|array
     */
    public function getProductIds(array $filters = []): \Illuminate\Support\Collection|array;

    public function updateByParams(array $params, array $data): bool;
}
