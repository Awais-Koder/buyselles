<?php

namespace App\Contracts\Repositories;

interface AdminWalletRepositoryInterface extends RepositoryInterface
{
    public function updateWhere(array $params, array $data): bool;
}
