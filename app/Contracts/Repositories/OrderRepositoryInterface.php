<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface extends RepositoryInterface
{
    /**
     * @param  array  $filters  Filters value must be in key and value pair structure, support one level nested array, ex: Filters = ['category'=>[1,2,5,8], 'email'=>['x@x.com','test@test.com']]
     * @param  int|string  $dataLimit  If you need all data without pagination, you need to set dataLimit = 'all'
     */
    public function getDeliveryManOrderListWhere(string $addedBy, ?string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, ?int $offset = null): Collection|LengthAwarePaginator;

    public function updateWhere(array $params, array $data): bool;

    public function getListWhereNotIn(array $orderBy = [], ?string $searchValue = null, array $filters = [], array $relations = [], array $nullFields = [], array $whereNotIn = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, ?int $offset = null): Collection|LengthAwarePaginator;

    public function getListWhereCount(?string $searchValue = null, array $filters = [], array $relations = []): int;

    public function updateAmountDate(object $request, string|int $userId, string $userType): bool;

    public function getListWhereDate(array $filters = [], ?string $dateType = null, array $filterDate = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, ?int $offset = null): Collection|LengthAwarePaginator;

    public function updateStockOnOrderStatusChange(string|int $orderId, string $status): bool;

    public function manageWalletOnOrderStatusChange(object $order, string $receivedBy): bool;

    public function getTopCustomerList(array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, ?int $offset = null): Collection|LengthAwarePaginator;

    public function getListWhereBetween(array $filters = [], ?string $selectColumn = null, ?string $whereBetween = null, array $whereBetweenFilters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, ?int $offset = null): Collection|LengthAwarePaginator;

    public function getPreviousFirstOrderWhere(int $id, array $params = [], array $relations = []): ?Model;

    public function getNextFirstOrderWhere(int $id, array $params = [], array $relations = []): ?Model;
}
