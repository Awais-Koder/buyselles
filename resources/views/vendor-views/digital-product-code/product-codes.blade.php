@extends('layouts.vendor.app')

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
                            <a href="{{ route('vendor.dashboard.index') }}">{{ translate('dashboard') }}</a>
                        </li>
                        <li class="breadcrumb-item">
                            <a href="{{ route('vendor.products.list', 'all') }}">{{ translate('products') }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ $product->name }}</li>
                    </ol>
                </nav>
            </div>
            <a href="{{ route('vendor.products.digital-code-import.product-import', $product->id) }}"
                class="btn btn-primary">
                <i class="fi fi-sr-inbox-in me-2"></i>
                {{ translate('Upload More Codes') }}
            </a>
        </div>

        {{-- Import summary (shown right after upload) --}}
        @if (session('import_summary'))
            @php $summary = session('import_summary'); @endphp
            <div class="alert alert-{{ $summary['processed'] > 0 ? 'success' : 'warning' }} alert-dismissible fade show"
                role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <strong><i class="fi fi-sr-check me-1"></i>{{ translate('Import Complete') }}</strong>
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
            </div>
        @endif

        @if (session('import_error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
                <i class="fi fi-sr-triangle-warning me-1"></i>
                {{ session('import_error') }}
            </div>
        @endif

        {{-- ⚠ Expiry warning banner --}}
        @if ($expiringCount > 0)
            <div class="alert alert-warning d-flex align-items-center gap-2 py-2" role="alert">
                <i class="fi fi-sr-triangle-warning fs-5"></i>
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
                    <div class="h3 mb-1 text-dark">{{ $stats['inactive'] }}</div>
                    <div class="small text-muted">{{ translate('Inactive') }}</div>
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
                    <i class="fi fi-rr-list me-2"></i>
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
                                <th class="text-center">{{ translate('Active') }}</th>
                                <th>{{ translate('Added') }}</th>
                                <th class="text-center">{{ translate('Action') }}</th>
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
                                        {{-- PIN masked by default; vendor can reveal on demand --}}
                                        <span class="font-monospace text-muted pin-display"
                                            id="pin-{{ $code->id }}">████-████-████-████</span>
                                        <i class="fi fi-rr-lock text-secondary ms-1 small pin-lock-icon"
                                            id="pin-lock-{{ $code->id }}"
                                            title="{{ translate('PIN is AES-256 encrypted. Only revealed at delivery.') }}"></i>
                                        @if ($canViewPin)
                                            <button type="button"
                                                class="btn btn-link btn-sm p-0 ms-1 text-secondary reveal-pin-btn"
                                                data-code-id="{{ $code->id }}"
                                                title="{{ translate('Reveal decrypted PIN') }}">
                                                <i class="fi fi-sr-eye fs-6" id="pin-eye-{{ $code->id }}"></i>
                                            </button>
                                        @endif
                                    </td>
                                    <td class="small">
                                        @if ($code->expiry_date)
                                            <span
                                                class="{{ $isPastExpiry ? 'text-danger' : ($isExpiringSoon ? 'text-warning fw-semibold' : 'text-muted') }}">
                                                {{ $code->expiry_date->format('Y-m-d') }}
                                                @if ($isExpiringSoon)
                                                    <i class="fi fi-sr-triangle-warning ms-1"
                                                        title="{{ translate('Expiring within 7 days') }}"></i>
                                                @elseif ($isPastExpiry)
                                                    <i class="fi fi-sr-cross ms-1"></i>
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
                                    <td class="text-center">
                                        @if (in_array($code->status, ['available', 'expired']))
                                            <label class="switcher mx-auto">
                                                <input type="checkbox" class="switcher_input toggle-code-status"
                                                    data-code-id="{{ $code->id }}"
                                                    {{ $code->is_active ? 'checked' : '' }}>
                                                <span class="switcher_control"></span>
                                            </label>
                                        @else
                                            <span class="text-muted small"
                                                title="{{ translate('Cannot_toggle_reserved_or_sold_codes') }}">—</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">
                                        {{ $code->created_at->format('Y-m-d') }}
                                    </td>
                                    <td class="text-center">
                                        @if (in_array($code->status, ['available', 'expired']))
                                            <button type="button" class="btn btn-outline-danger btn-sm delete-code-btn"
                                                data-code-id="{{ $code->id }}"
                                                title="{{ translate('Delete this code') }}">
                                                <i class="fi fi-rr-trash"></i>
                                            </button>
                                        @else
                                            <span class="text-muted small"
                                                title="{{ translate('Cannot_delete_reserved_or_sold_codes') }}">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fi fi-rr-inbox fs-2 d-block mb-2"></i>
                                        {{ translate('No codes in the pool yet.') }}
                                        <br>
                                        <a href="{{ route('vendor.products.digital-code-import.product-import', $product->id) }}"
                                            class="btn btn-sm btn-primary mt-2">
                                            <i class="fi fi-rr-upload me-1"></i>
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
            <i class="fi fi-sr-lock me-1"></i>
            {{ translate('All PINs are stored AES-256 encrypted. They are only decrypted at the moment of delivery to the customer.') }}
            @if ($canViewPin)
                {{ translate('You can reveal any PIN on demand using the eye icon.') }}
            @endif
        </div>

    </div>
@endsection

@push('css_or_js')
    <style>
        .pin-spin {
            display: inline-block;
            animation: pin-spin-anim 0.8s linear infinite;
        }

        @@keyframes pin-spin-anim {
            from {
                transform: rotate(0deg);
            }

            to {
                transform: rotate(360deg);
            }
        }
    </style>
@endpush

@push('script')
    <script>
        'use strict';

        @if ($canViewPin)
            (function() {
                const decryptUrl = '{{ route('vendor.products.digital-code-import.decrypt-code', ':id') }}';
                const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

                document.querySelectorAll('.reveal-pin-btn').forEach(function(btn) {
                    let revealed = false;

                    btn.addEventListener('click', function() {
                        const codeId = this.dataset.codeId;
                        const pinSpan = document.getElementById('pin-' + codeId);
                        const lockIcon = document.getElementById('pin-lock-' + codeId);
                        const eyeIcon = document.getElementById('pin-eye-' + codeId);

                        if (revealed) {
                            // Hide again
                            pinSpan.textContent = '████-████-████-████';
                            pinSpan.classList.remove('text-success', 'fw-semibold');
                            pinSpan.classList.add('text-muted');
                            lockIcon.classList.remove('d-none');
                            eyeIcon.className = 'fi fi-sr-eye fs-6';
                            revealed = false;
                            return;
                        }

                        // Show spinner while fetching
                        eyeIcon.className = 'fi fi-rr-refresh fs-6 pin-spin';

                        fetch(decryptUrl.replace(':id', codeId), {
                                method: 'GET',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                },
                            })
                            .then(function(r) {
                                return r.json();
                            })
                            .then(function(data) {
                                if (data.pin) {
                                    pinSpan.textContent = data.pin;
                                    pinSpan.classList.remove('text-muted');
                                    pinSpan.classList.add('text-success', 'fw-semibold');
                                    lockIcon.classList.add('d-none');
                                    eyeIcon.className = 'fi fi-sr-eye-crossed fs-6';
                                    revealed = true;
                                } else {
                                    eyeIcon.className = 'fi fi-sr-eye fs-6';
                                    toastr.error(data.error ||
                                        '{{ addslashes(translate('Decryption failed')) }}');
                                }
                            })
                            .catch(function() {
                                eyeIcon.className = 'fi fi-sr-eye fs-6';
                                toastr.error(
                                    '{{ addslashes(translate('Network error. Please try again.')) }}'
                                    );
                            });
                    });
                });
            })();
        @endif

        // ── Toggle Active/Inactive ───────────────────────────────────────────
        document.querySelectorAll('.toggle-code-status').forEach(function(toggle) {
            toggle.addEventListener('change', function() {
                const codeId = this.dataset.codeId;
                const checkbox = this;
                const toggleUrl =
                    '{{ route('vendor.products.digital-code-import.toggle-code-status', ':id') }}'.replace(
                        ':id', codeId);

                fetch(toggleUrl, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                ?.getAttribute('content') ?? '',
                        },
                    })
                    .then(function(r) {
                        return r.json();
                    })
                    .then(function(data) {
                        if (data.success) {
                            toastr.success(data.message);
                        } else {
                            toastr.error(data.message);
                            checkbox.checked = !checkbox.checked;
                        }
                    })
                    .catch(function() {
                        toastr.error('{{ translate('Something went wrong') }}');
                        checkbox.checked = !checkbox.checked;
                    });
            });
        });

        // ── Delete Code ──────────────────────────────────────────────────────
        document.querySelectorAll('.delete-code-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const codeId = this.dataset.codeId;
                const row = this.closest('tr');
                const deleteUrl = '{{ route('vendor.products.digital-code-import.delete-code', ':id') }}'
                    .replace(':id', codeId);

                const getText = document.getElementById('get-sweet-alert-messages');
                Swal.fire({
                    title: getText?.dataset.areYouSure || 'Are you sure?',
                    text: '{{ translate('This action cannot be undone') }}',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    cancelButtonText: getText?.dataset.cancel || 'Cancel',
                    confirmButtonText: getText?.dataset.confirm || 'Confirm',
                    reverseButtons: true,
                }).then(function(result) {
                    if (!result.isConfirmed) return;

                    fetch(deleteUrl, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                        'meta[name="csrf-token"]')
                                    ?.getAttribute('content') ?? '',
                            },
                        })
                        .then(function(r) {
                            return r.json();
                        })
                        .then(function(data) {
                            if (data.success) {
                                toastr.success(data.message);
                                row.remove();
                            } else {
                                toastr.error(data.message);
                            }
                        })
                        .catch(function() {
                            toastr.error('{{ translate('Something went wrong') }}');
                        });
                });
            });
        });
    </script>
@endpush
