<?php

namespace App\Contracts\Repositories;

interface BusinessPageRepositoryInterface extends RepositoryInterface
{
    public function updateWhere(array $params, array $data): bool;
}
