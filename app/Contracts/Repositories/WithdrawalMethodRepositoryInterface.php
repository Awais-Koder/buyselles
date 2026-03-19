<?php

namespace App\Contracts\Repositories;

interface WithdrawalMethodRepositoryInterface extends RepositoryInterface
{
    public function updateWhereNotIn(array $params, array $data): bool;
}
