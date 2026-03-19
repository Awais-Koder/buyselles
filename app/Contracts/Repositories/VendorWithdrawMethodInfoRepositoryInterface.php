<?php

namespace App\Contracts\Repositories;

interface VendorWithdrawMethodInfoRepositoryInterface extends RepositoryInterface
{
    public function updateOrInsert(array $params, array $data): bool;

    public function updateWhere(array $params, array $data): bool;
}
