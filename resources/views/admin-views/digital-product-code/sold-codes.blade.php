@extends('layouts.admin.app')

@section('title', translate('Sold Digital Codes — Tracking'))

@section('content')
    <div class="content container-fluid">

        {{-- Page Header --}}
        <div class="mb-3 d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
                <h2 class="h1 mb-1 text-capitalize d-flex align-items-center gap-2">
                    <img src="{{ dynamicAsset(path: 'public/assets/new/back-end/img/inhouse-product-list.png') }}"
                        alt="">
                    {{ translate('Sold Digital Codes') }}
                </h2>
                <p class="text-muted mb-0 fs-13">
                    {{ translate('Track which code was delivered to which customer. Click any row item to go to its details.') }}
                </p>
                <nav aria-label="breadcrumb" class="mt-1">
                    <ol class="breadcrumb mb-0">
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.dashboard.index') }}">{{ translate('dashboard') }}</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('admin.products.digital-code-import.index') }}">{{ translate('Digital Codes') }}</a>
                        </li>
                        <li class="breadcrumb-item active" aria-current="page">{{ translate('Sold Codes') }}</li>
                    </ol>
                </nav>
            </div>
        </div>

        {{-- Search --}}
        <div class="card __card mb-3">
            <div class="card-body py-2 px-3">
                <form method="GET" action="{{ route('admin.products.digital-code-import.sold-codes') }}">
                    <div class="input-group input-group-sm">
                        <input type="text" name="search" value="{{ $search }}" class="form-control fs-13"
                            placeholder="{{ translate('Search by customer name, email, product name, or order ID…') }}">
                        <button class="btn btn-primary" type="submit">
                            <i class="fi fi-rr-search me-1"></i>{{ translate('Search') }}
                        </button>
                        @if ($search)
                            <a href="{{ route('admin.products.digital-code-import.sold-codes') }}"
                                class="btn btn-outline-secondary">
                                {{ translate('Clear') }}
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        {{-- Table --}}
        <div class="card __card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-borderless align-middle mb-0 fs-13">
                        <thead class="bg-soft-brand border-bottom">
                            <tr>
                                <th class="ps-3 py-3">#</th>
                                <th>{{ translate('Customer') }}</th>
                                <th>{{ translate('Product') }}</th>
                                <th>{{ translate('Vendor') }}</th>
                                <th>{{ translate('Order') }}</th>
                                <th>{{ translate('Assigned At') }}</th>
                                <th>{{ translate('Code (masked)') }}</th>
                                <th class="text-center pe-3">{{ translate('Actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($codes as $code)
                                @php
                                    $customer = $code->order?->customer;
                                    $product = $code->product;
                                    $seller = $code->order?->seller;
                                    $shop = $seller?->shop;
                                    $isInhouse = is_null($seller);
                                @endphp
                                <tr>
                                    <td class="ps-3 text-muted">{{ $codes->firstItem() + $loop->index }}</td>

                                    {{-- Customer --}}
                                    <td>
                                        @if ($customer)
                                            <a href="{{ route('admin.customer.view', $customer->id) }}"
                                                class="text-dark fw-semibold d-block lh-1">
                                                {{ $customer->name }}
                                            </a>
                                            <small class="text-muted">{{ $customer->email }}</small>
                                        @else
                                            <span class="text-muted">{{ translate('Guest / Deleted') }}</span>
                                        @endif
                                    </td>

                                    {{-- Product --}}
                                    <td>
                                        @if ($product)
                                            <a href="{{ route('admin.products.view', [$product->added_by, $product->id]) }}"
                                                class="text-dark fw-semibold d-block lh-1"
                                                @if (strlen($product->name) > 50) data-bs-toggle="tooltip"
                                                    data-bs-placement="top"
                                                    title="{{ $product->name }}" @endif>
                                                {{ Str::limit($product->name, 50) }}
                                            </a>
                                            @if ($code->serial_number)
                                                <small class="text-muted">S/N: {{ $code->serial_number }}</small>
                                            @endif
                                            @if ($code->expiry_date)
                                                <small class="text-{{ $code->isExpired() ? 'danger' : 'muted' }}">
                                                    Exp: {{ $code->expiry_date->format('d M Y') }}
                                                </small>
                                            @endif
                                        @else
                                            <span class="text-muted">{{ translate('Product Deleted') }}</span>
                                        @endif
                                    </td>

                                    {{-- Vendor --}}
                                    <td>
                                        @if ($isInhouse)
                                            <span class="badge badge-soft-primary fs-11">{{ translate('In-house') }}</span>
                                        @elseif($seller)
                                            <a href="{{ route('admin.vendors.view', $seller->id) }}"
                                                class="text-dark fw-semibold d-block lh-1">
                                                {{ $shop?->name ?? $seller->f_name . ' ' . $seller->l_name }}
                                            </a>
                                            <small class="text-muted">{{ $seller->email }}</small>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>

                                    {{-- Order --}}
                                    <td>
                                        @if ($code->order_id)
                                            <a href="{{ route('admin.orders.details', $code->order_id) }}"
                                                class="fw-semibold web-text-primary">
                                                #{{ $code->order_id }}
                                            </a>
                                            @if ($code->order)
                                                <br><small
                                                    class="badge badge-soft-{{ $code->order->payment_status === 'paid' ? 'success' : 'warning' }}">
                                                    {{ $code->order->payment_status }}
                                                </small>
                                            @endif
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>

                                    {{-- Assigned At --}}
                                    <td class="text-muted">
                                        @if ($code->assigned_at)
                                            {{ $code->assigned_at->format('d M Y') }}<br>
                                            <small>{{ $code->assigned_at->format('H:i') }}</small>
                                        @else
                                            —
                                        @endif
                                    </td>

                                    {{-- Code masked / inline reveal --}}
                                    <td style="white-space:nowrap;">
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="code-text font-monospace fs-12" id="code-text-{{ $code->id }}"
                                                data-revealed="0" data-plain="">••••••••••</span>
                                            <button type="button"
                                                class="btn btn-sm btn-link p-0 ms-1 btn-reveal-code text-muted"
                                                data-id="{{ $code->id }}"
                                                data-url="{{ route('admin.products.digital-code-import.decrypt-code', $code->id) }}"
                                                title="{{ translate('Reveal / Hide') }}">
                                                <i class="fi fi-rr-eye" id="eye-icon-{{ $code->id }}"></i>
                                            </button>
                                            <button type="button"
                                                class="btn btn-sm btn-link p-0 text-muted d-none btn-copy-code"
                                                data-id="{{ $code->id }}" title="{{ translate('Copy') }}">
                                                <i class="fi fi-rr-copy-alt"></i>
                                            </button>
                                        </div>
                                    </td>

                                    {{-- Actions --}}
                                    <td class="text-center pe-3">
                                        <div class="d-flex align-items-center justify-content-center gap-2">
                                            {{-- Jump to order --}}
                                            @if ($code->order_id)
                                                <a href="{{ route('admin.orders.details', $code->order_id) }}"
                                                    class="btn btn-sm btn-outline-primary"
                                                    title="{{ translate('View Order') }}">
                                                    <i class="fi fi-rr-receipt"></i>
                                                </a>
                                            @endif
                                            {{-- Jump to customer --}}
                                            @if ($customer)
                                                <a href="{{ route('admin.customer.view', $customer->id) }}"
                                                    class="btn btn-sm btn-outline-secondary"
                                                    title="{{ translate('View Customer') }}">
                                                    <i class="fi fi-rr-user"></i>
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fi fi-rr-search fs-2 d-block mb-2"></i>
                                        {{ $search ? translate('No sold codes match your search.') : translate('No sold codes yet.') }}
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

    </div>
@endsection

@push('script')
    <script>
        (function() {
            'use strict';

            // ── Bootstrap tooltips ───────────────────────────────────────────────────
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(el) {
                new bootstrap.Tooltip(el);
            });

            // ── Inline AJAX reveal / hide ────────────────────────────────────────────
            document.addEventListener('click', function(e) {
                const revealBtn = e.target.closest('.btn-reveal-code');
                if (revealBtn) {
                    const id = revealBtn.dataset.id;
                    const url = revealBtn.dataset.url;
                    const textEl = document.getElementById('code-text-' + id);
                    const eyeIcon = document.getElementById('eye-icon-' + id);
                    const copyBtn = revealBtn.closest('div').querySelector('.btn-copy-code');

                    // Already revealed → toggle hide
                    if (textEl.dataset.revealed === '1') {
                        textEl.textContent = '••••••••••';
                        textEl.dataset.revealed = '0';
                        eyeIcon.className = 'fi fi-rr-eye';
                        if (copyBtn) copyBtn.classList.add('d-none');
                        return;
                    }

                    // Already fetched once → just show again
                    if (textEl.dataset.plain) {
                        textEl.textContent = textEl.dataset.plain;
                        textEl.dataset.revealed = '1';
                        eyeIcon.className = 'fi fi-rr-eye-crossed';
                        if (copyBtn) copyBtn.classList.remove('d-none');
                        return;
                    }

                    // First fetch — show spinner
                    eyeIcon.className = 'spinner-border spinner-border-sm';
                    revealBtn.disabled = true;

                    fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(function(r) {
                            return r.json();
                        })
                        .then(function(data) {
                            if (data.error) {
                                eyeIcon.className = 'fi fi-rr-eye';
                                revealBtn.disabled = false;
                                toastr.error(data.error);
                                return;
                            }
                            const plain = data.pin || '';
                            textEl.dataset.plain = plain;
                            textEl.textContent = plain;
                            textEl.dataset.revealed = '1';
                            eyeIcon.className = 'fi fi-rr-eye-crossed';
                            revealBtn.disabled = false;
                            if (copyBtn) copyBtn.classList.remove('d-none');
                        })
                        .catch(function() {
                            eyeIcon.className = 'fi fi-rr-eye';
                            revealBtn.disabled = false;
                            toastr.error('{{ translate('Request failed. Please try again.') }}');
                        });
                }
            });

            // ── Inline copy ──────────────────────────────────────────────────────────
            document.addEventListener('click', function(e) {
                const copyBtn = e.target.closest('.btn-copy-code');
                if (copyBtn) {
                    const id = copyBtn.dataset.id;
                    const textEl = document.getElementById('code-text-' + id);
                    if (!textEl || !textEl.dataset.plain) return;
                    navigator.clipboard.writeText(textEl.dataset.plain).then(function() {
                        toastr.success('{{ translate('Code copied to clipboard!') }}');
                    });
                }
            });

        })();
    </script>
@endpush
