<?php

namespace App\Contracts\Repositories;

interface DeliveryManWalletRepositoryInterface extends RepositoryInterface
{
    public function updateWhere(array $params, array $data): bool;
}
