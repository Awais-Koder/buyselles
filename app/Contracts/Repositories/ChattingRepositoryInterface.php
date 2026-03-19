<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

interface ChattingRepositoryInterface extends RepositoryInterface
{
    public function getFirstWhereNotNull(array $params, array $filters = [], array $orderBy = [], array $relations = []): ?Model;

    public function getListBySelectWhere(array $joinColumn = [], array $select = [], array $filters = [], array $orderBy = []): Collection;

    public function updateAllWhere(array $params, array $data): bool;

    public function updateListWhereNotNull(?string $searchValue = null, array $filters = [], array $whereNotNull = [], array $data = []): bool;

    public function countUnreadMessages(array $data): array;
}
