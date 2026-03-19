<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Model;

interface DealOfTheDayRepositoryInterface extends RepositoryInterface
{
    public function getFirstWhereWithoutGlobalScope(array $params, array $relations = []): ?Model;

    public function updateWhere(array $params, array $data): bool;
}
