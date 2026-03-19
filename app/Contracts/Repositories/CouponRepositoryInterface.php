<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Model;

interface CouponRepositoryInterface extends RepositoryInterface
{
    public function getFirstWhereFilters(array $filters = [], array $relations = []): ?Model;
}
