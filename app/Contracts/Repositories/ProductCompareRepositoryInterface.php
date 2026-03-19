<?php

namespace App\Contracts\Repositories;

interface ProductCompareRepositoryInterface extends RepositoryInterface
{
    public function getCount(array $params): ?int;
}
