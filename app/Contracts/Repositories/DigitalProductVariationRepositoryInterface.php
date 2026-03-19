<?php

namespace App\Contracts\Repositories;

interface DigitalProductVariationRepositoryInterface extends RepositoryInterface
{
    public function updateByParams(array $params, array $data): bool;
}
