<?php

namespace App\Contracts\Repositories;

interface ReviewReplyRepositoryInterface extends RepositoryInterface
{
    public function updateOrInsert(array $params, array $data): bool;
}
