<?php

namespace App\Contracts\Repositories;

interface TranslationRepositoryInterface
{
    /**
     * @param  object  $request  Data value must be in key and value pair structure, ex: params = ['name'=>'John Doe']
     */
    public function add(object $request, string $model, int|string $id): bool;

    public function update(object $request, string $model, int|string $id): bool;

    public function delete(string $model, int|string $id): bool;

    public function updateData(string $model, string $id, string $lang, string $key, string $value): bool;
}
