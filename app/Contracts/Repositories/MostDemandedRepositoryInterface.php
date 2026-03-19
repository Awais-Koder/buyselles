<?php

namespace App\Contracts\Repositories;

interface MostDemandedRepositoryInterface extends RepositoryInterface
{
    public function updateWhere(array $params, array $data): bool;
}
