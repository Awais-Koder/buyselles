<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface OrderTransactionRepositoryInterface extends RepositoryInterface
{
    public function getListWhereBetween(array $filters = [], ?string $selectColumn = null, ?string $whereBetween = null, ?string $groupBy = null, array $whereBetweenFilters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, ?int $offset = null): Collection|LengthAwarePaginator;

    public function getCommissionEarningStatisticsData(?string $sellerIs = null, array $dataRange = [], array $groupBy = [], int $dateStart = 1, int $dateEnd = 12): array;

    public function getEarningStatisticsData(?string $sellerIs = null, array $dataRange = [], array $groupBy = [], int $dateStart = 1, int $dateEnd = 12): array;
}
