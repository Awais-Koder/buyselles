<?php

namespace App\Contracts\Repositories;

interface AnalyticScriptRepositoryInterface extends RepositoryInterface
{
    public function updateOrInsert(array $params, array $data): bool;
}
