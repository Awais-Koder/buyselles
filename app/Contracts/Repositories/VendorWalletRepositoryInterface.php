<?php

namespace App\Contracts\Repositories;

interface VendorWalletRepositoryInterface extends RepositoryInterface
{
    public function updateWhere(array $params, array $data): bool;
}
