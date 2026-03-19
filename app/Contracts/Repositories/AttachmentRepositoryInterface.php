<?php

namespace App\Contracts\Repositories;

interface AttachmentRepositoryInterface extends RepositoryInterface
{
    public function updateWhere(array $params, array $data): bool;

    public function updateOrInsert(array $params, array $data): bool;
}
