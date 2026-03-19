<?php

namespace App\Contracts\Repositories;

interface CurrencyRepositoryInterface extends RepositoryInterface
{
    public function updateWhere(array $params, array $data): bool;
}
