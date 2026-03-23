@extends('layouts.admin.app')

@section('title', translate('Digital Codes') . ' — ' . $product->name)

@section('content')
    <div class="content container-fluid">

        {{-- Page Header --}}
        <div class="mb-3 d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
                <h2 class="h1 mb-1 text-capitalize d-flex align-items-center gap-2">
                    <img src="{{ dynamicAsset(path: 'public/assets/new/back-end/img/inhouse-product-list.png') }}"
                        alt="">
                    {{ translate('Digital Codes') }}
                </h2>
                <p class="text-muted mb-0">{{ $product->name }}</p>
                <nav aria-label="breadcrumb" class="mt-1">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.dashboard.index') }}">{{ translate('dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('admin.products.list', 'in-house') }}">{{ translate('products') }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('admin.products.digital-code-import.product-import', $product->id) }}"
                class="btn btn-primary">
                <i class="bi bi-upload me-2"></i>
                {{ translate('Upload More Codes') }}
            </a>
        </div>

        {{-- Import summary (shown right after upload) --}}
        @if (session('import_summary'))
            @php $summary = session('import_summary'); @endphp
            <div class="alert alert-{{ $summary['processed'] > 0 ? 'success' : 'warning' }} alert-dismissible fade show"
                role="alert">
                <strong><i class="bi bi-check-circle-fill me-1"></i>{{ translate('Import Complete') }}</strong>
                <div class="mt-1">
                    <div><strong>{{ $summary['processed'] }}</strong> {{ translate('code(s) imported successfully') }}
                    </div>
                    @if ($summary['duplicates'] > 0)
                        <div class="text-warning">
                            <strong>{{ $summary['duplicates'] }}</strong>
                            {{ translate('code(s) skipped — duplicate PIN or serial number already exists in the system.') }}
                        </div>
                    @endif
                    @if ($summary['skipped'] > 0)
                        <div class="text-muted"><strong>{{ $summary['skipped'] }}</strong>
                            {{ translate('blank/example row(s) skipped') }}</div>
                    @endif
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if (session('import_error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                {{ session('import_error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- ⚠ Expiry warning banner --}}
        @if ($expiringCount > 0)
            <div class="alert alert-warning d-flex align-items-center gap-2 py-2" role="alert">
                <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                <div>
                    <strong>{{ translate('Expiry Warning') }}:</strong>
                    {{ $expiringCount }} {{ translate('code(s) will expire within the next 7 days.') }}
                    {{ translate('Consider uploading new codes soon.') }}
                </div>
            </div>
        @endif

        {{-- Stats --}}
        <div class="row g-3 mb-3">
            <div class="col-6 col-md-3 col-lg-2">
                <div class="card text-center py-3">
                    <div class="h3 mb-1 text-success">{{ $stats['available'] }}</div>
                    <div class="small text-muted">{{ translate('Available') }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <div class="card text-center py-3">
                    <div class="h3 mb-1 text-warning">{{ $stats['reserved'] }}</div>
                    <div class="small text-muted">{{ translate('Reserved') }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <div class="card text-center py-3">
                    <div class="h3 mb-1 text-info">{{ $stats['sold'] }}</div>
                    <div class="small text-muted">{{ translate('Sold') }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <div class="card text-center py-3">
                    <div class="h3 mb-1 text-danger">{{ $stats['expired'] }}</div>
                    <div class="small text-muted">{{ translate('Expired') }}</div>
                </div>
            </div>
            <div class="col-6 col-md-3 col-lg-2">
                <div class="card text-center py-3">
                    <div class="h3 mb-1 text-secondary">{{ $stats['total'] }}</div>
                    <div class="small text-muted">{{ translate('Total') }}</div>
                </div>
            </div>
        </div>

        {{-- Codes table --}}
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-table me-2"></i>
                    {{ translate('Code Pool') }}
                    <span class="badge bg-secondary ms-1">{{ $stats['total'] }}</span>
                </h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-sm mb-0">
                        <thead style="background-color: var(--bs-primary); color: var(--bs-white);">
                            <tr>
                                <th class="py-3 ps-3">#</th>
                                <th>{{ translate('Serial Number') }}</th>
                                <th>{{ translate('PIN / Code') }}</th>
                                <th>{{ translate('Expiry Date') }}</th>
                                <th class="text-center">{{ translate('Status') }}</th>
                                <th>{{ translate('Added') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($codes as $index => $code)
                                @php
                                    $isExpiringSoon =
                                        $code->expiry_date &&
                                        $code->status === 'available' &&
                                        $code->expiry_date->between(now(), now()->addDays(7));
                                    $isPastExpiry = $code->expiry_date && $code->expiry_date->isPast();
                                @endphp
                                <tr class="{{ $isExpiringSoon ? 'table-warning' : '' }}">
                                    <td class="ps-3 text-muted small">
                                        {{ ($codes->currentPage() - 1) * $codes->perPage() + $index + 1 }}
                                    </td>
                                    <td class="text-muted small">
                                        {{ $code->serial_number ?: '—' }}
                                    </td>
                                    <td>
                                        {{-- PIN is never shown in list view — security requirement --}}
                                        <span class="font-monospace text-muted">
                                            ████-████-████-████
                                        </span>
                                        <i class="bi bi-lock-fill text-secondary ms-1 small"
                                            title="{{ translate('PIN is encrypted. Only revealed at time of delivery.') }}"></i>
                                    </td>
                                    <td class="small">
                                        @if ($code->expiry_date)
                                            <span
                                                class="{{ $isPastExpiry ? 'text-danger' : ($isExpiringSoon ? 'text-warning fw-semibold' : 'text-muted') }}">
                                                {{ $code->expiry_date->format('Y-m-d') }}
                                                @if ($isExpiringSoon)
                                                    <i class="bi bi-exclamation-triangle-fill ms-1"
                                                        title="{{ translate('Expiring within 7 days') }}"></i>
                                                @elseif ($isPastExpiry)
                                                    <i class="bi bi-x-circle-fill ms-1"></i>
                                                @endif
                                            </span>
                                        @else
                                            <span class="text-muted">{{ translate('No expiry') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if ($code->status === 'available')
                                            <span class="badge bg-success">{{ translate('Available') }}</span>
                                        @elseif ($code->status === 'reserved')
                                            <span class="badge bg-warning text-dark">{{ translate('Reserved') }}</span>
                                        @elseif ($code->status === 'sold')
                                            <span class="badge bg-info text-dark">{{ translate('Sold') }}</span>
                                        @elseif ($code->status === 'expired')
                                            <span class="badge bg-danger">{{ translate('Expired') }}</span>
                                        @else
                                            <span class="badge bg-secondary">{{ ucfirst($code->status) }}</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">
                                        {{ $code->created_at->format('Y-m-d') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                        {{ translate('No codes in the pool yet.') }}
                                        <br>
                                        <a href="{{ route('admin.products.digital-code-import.product-import', $product->id) }}"
                                            class="btn btn-sm btn-primary mt-2">
                                            <i class="bi bi-upload me-1"></i>
                                            {{ translate('Upload Codes') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if ($codes->hasPages())
                <div class="card-footer d-flex justify-content-end">
                    {{ $codes->links() }}
                </div>
            @endif
        </div>

        <div class="mt-2 small text-muted">
            <i class="bi bi-lock-fill me-1"></i>
            {{ translate('All PINs are stored AES-256 encrypted. They are only decrypted at the moment of delivery to the customer.') }}
        </div>

    </div>
@endsection
