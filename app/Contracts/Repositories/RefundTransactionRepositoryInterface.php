<?php

namespace App\Contracts\Repositories;

interface RefundTransactionRepositoryInterface extends RepositoryInterface
{
    public function updateWhere(array $params, array $data): bool;
}
