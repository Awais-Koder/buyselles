<?php

namespace App\Contracts\Repositories;

use App\Models\WalletTransaction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface WalletTransactionRepositoryInterface extends RepositoryInterface
{
    public function getListWhereSelect(array $orderBy = [], ?string $searchValue = null, array $filters = [], array $relations = [], int|string $dataLimit = DEFAULT_DATA_LIMIT, ?int $offset = null): Collection|LengthAwarePaginator;

    public function addWalletTransaction(string $user_id, float $amount, string $transactionType, string $reference, array $payment_data = []): bool|WalletTransaction;

    public function addFundToWalletBonus(float $amount): string|float;
}
