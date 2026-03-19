<?php

namespace App\Contracts\Repositories;

use Illuminate\Support\Collection;

interface ProductTagRepositoryInterface extends RepositoryInterface
{
    public function getIds(string $fieldName = 'tag_id', array $filters = []): Collection|array;
}
