<?php

namespace App\Contracts\Repositories;

interface PhoneOrEmailVerificationRepositoryInterface extends RepositoryInterface
{
    public function updateOrCreate(array $params, array $value): mixed;
}
