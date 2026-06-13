@extends('layouts.vendor.app')
@section('title', translate('wallet_transfer_to_customer'))

@push('css_or_js')
    <style>
        .wallet-balance-display {
            font-size: 1.5rem;
            font-weight: 700;
        }
        .customer-search-wrap {
            position: relative;
        }
        .customer-search-dropdown {
            position: absolute;
            top: 100%;
            left: 0;
            right: 0;
            z-index: 1050;
            max-height: 260px;
            overflow-y: auto;
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: 0 0 6px 6px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.1);
            display: none;
        }
        .customer-search-dropdown .dropdown-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 14px;
            cursor: pointer;
            border-bottom: 1px solid #f0f0f0;
            transition: background 0.12s;
        }
        .customer-search-dropdown .dropdown-item:last-child {
            border-bottom: none;
        }
        .customer-search-dropdown .dropdown-item:hover {
            background: #f0f7ff;
        }
        .customer-search-dropdown .dropdown-item .avatar {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #4a90d9;
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 13px;
            flex-shrink: 0;
        }
        .customer-search-dropdown .dropdown-item .info .name {
            font-weight: 600;
            font-size: 14px;
            line-height: 1.3;
        }
        .customer-search-dropdown .dropdown-item .info .detail {
            font-size: 12px;
            color: #6c757d;
        }
        .customer-search-dropdown .dropdown-item .info .email-highlight {
            color: #4a90d9;
        }
        .customer-search-dropdown .no-results {
            padding: 20px;
            text-align: center;
            color: #6c757d;
        }
        .customer-search-dropdown .searching {
            padding: 20px;
            text-align: center;
            color: #6c757d;
        }
        .customer-search-dropdown .searching .spinner {
            display: inline-block;
            width: 18px;
            height: 18px;
            border: 2px solid #e0e0e0;
            border-top-color: #4a90d9;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
            vertical-align: middle;
            margin-right: 6px;
        }
        @keyframes spin {
            to { transform: rotate(360deg); }
        }
        .selected-customer-badge {
            display: none;
            align-items: center;
            gap: 8px;
            padding: 8px 12px;
            background: #e8f4ff;
            border: 1px solid #b8daff;
            border-radius: 6px;
            margin-top: 8px;
        }
        .selected-customer-badge .name {
            font-weight: 600;
            font-size: 14px;
            flex: 1;
        }
        .selected-customer-badge .clear-btn {
            background: none;
            border: none;
            color: #dc3545;
            cursor: pointer;
            font-size: 16px;
            padding: 0 4px;
            line-height: 1;
        }
        .selected-customer-badge .clear-btn:hover {
            color: #a71d2a;
        }
    </style>
@endpush

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2 text-capitalize">
                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png') }}" width="28" alt="">
                {{ translate('wallet_transfer_to_customer') }}
            </h2>
        </div>

        <div class="row">
            {{-- Wallet Info & Transfer Form --}}
            <div class="col-lg-5 mb-4">
                {{-- Wallet Balance Card --}}
                <div class="card mb-4">
                    <div class="card-body text-center">
                        <h5 class="text-muted mb-1">{{ translate('your_wallet_balance') }}</h5>
                        <div class="wallet-balance-display text-primary">
                            {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $vendorWallet?->total_earning ?? 0), currencyCode: getCurrencyCode(type: 'default')) }}
                        </div>
                        <small class="text-muted">
                            {{ translate('withdrawable_balance') }}: 
                            {{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: ($vendorWallet?->total_earning ?? 0) - ($vendorWallet?->pending_withdraw ?? 0)), currencyCode: getCurrencyCode(type: 'default')) }}
                        </small>
                    </div>
                </div>

                {{-- Transfer Form --}}
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ translate('send_balance_to_customer') }}</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('vendor.wallet-transfer.transfer') }}" method="POST" id="transferForm">
                            @csrf
                            <input type="hidden" name="customer_id" id="customer_id_hidden" value="">

                            {{-- Customer Search --}}
                            <div class="form-group mb-3">
                                <label class="form-label">{{ translate('search_customer') }} <span class="text-danger">*</span></label>
                                <div class="customer-search-wrap">
                                    <input type="text" id="customer_search_input" class="form-control"
                                        placeholder="{{ translate('search_by_name_email_or_phone') }}"
                                        autocomplete="off">
                                    <div class="customer-search-dropdown" id="customer_search_dropdown"></div>
                                </div>
                                <div class="selected-customer-badge" id="selected_customer_badge">
                                    <i class="fi fi-rr-user text-primary"></i>
                                    <span class="name" id="selected_customer_name"></span>
                                    <button type="button" class="clear-btn" id="clear_customer_btn">&times;</button>
                                </div>
                            </div>

                            <div class="form-group mb-3">
                                <label for="amount" class="form-label">{{ translate('amount') }} ({{ getCurrencySymbol(type: 'default') }}) <span class="text-danger">*</span></label>
                                <input type="number" name="amount" id="amount" class="form-control" 
                                    step="0.01" min="0.01" 
                                    max="{{ $vendorWallet?->total_earning ?? 0 }}"
                                    required placeholder="{{ translate('enter_amount') }}">
                            </div>

                            <div class="form-group mb-4">
                                <label for="reference" class="form-label">{{ translate('reference') }} ({{ translate('optional') }})</label>
                                <input type="text" name="reference" id="reference" class="form-control"
                                    placeholder="{{ translate('add_a_note') }}" maxlength="255">
                            </div>

                            <button type="submit" class="btn btn-primary w-100" id="submitBtn" disabled>
                                <i class="fi fi-rr-wallet me-1"></i>
                                {{ translate('transfer_balance') }}
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Transfer History --}}
            <div class="col-lg-7">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ translate('transfer_history') }}</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover table-borderless table-thead-bordered align-middle">
                                <thead class="thead-light thead-50 text-capitalize">
                                    <tr>
                                        <th>{{ translate('SL') }}</th>
                                        <th>{{ translate('customer') }}</th>
                                        <th>{{ translate('amount') }}</th>
                                        <th>{{ translate('reference') }}</th>
                                        <th>{{ translate('date') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($transfers as $key => $transfer)
                                        <tr>
                                            <td>{{ $transfers->firstItem() + $key }}</td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="fw-semibold">
                                                        {{ $transfer->toUser?->f_name ?? translate('N/A') }}
                                                        {{ $transfer->toUser?->l_name ?? '' }}
                                                    </span>
                                                    @if ($transfer->toUser?->email)
                                                        <br><small class="text-muted">{{ $transfer->toUser->email }}</small>
                                                    @endif
                                                </div>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-danger">
                                                    -{{ setCurrencySymbol(amount: usdToDefaultCurrency(amount: $transfer->amount), currencyCode: getCurrencyCode(type: 'default')) }}
                                                </span>
                                            </td>
                                            <td>{{ $transfer->reference ?? translate('N/A') }}</td>
                                            <td>{{ $transfer->created_at->format('d M Y, h:i A') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5">
                                                @include('layouts.vendor.partials._empty-state', ['text' => 'no_data_found'], ['image' => 'default'])
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-end mt-3">
                            {{ $transfers->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        "use strict";

        (function() {
            console.log('[WalletTransfer] Initializing customer search...');

            const searchInput = document.getElementById('customer_search_input');
            const dropdown = document.getElementById('customer_search_dropdown');
            const hiddenInput = document.getElementById('customer_id_hidden');
            const badge = document.getElementById('selected_customer_badge');
            const badgeName = document.getElementById('selected_customer_name');
            const clearBtn = document.getElementById('clear_customer_btn');
            const submitBtn = document.getElementById('submitBtn');

            let searchTimeout = null;
            let selectedCustomerId = null;
            let selectedCustomerName = '';
            let currentRequest = null;

            function setSelectedCustomer(id, name) {
                selectedCustomerId = id;
                selectedCustomerName = name;
                hiddenInput.value = id;
                badgeName.textContent = name;
                badge.style.display = 'flex';
                searchInput.value = '';
                dropdown.style.display = 'none';
                submitBtn.disabled = false;
                console.log('[WalletTransfer] Customer selected:', id, name);
            }

            function clearSelectedCustomer() {
                selectedCustomerId = null;
                selectedCustomerName = '';
                hiddenInput.value = '';
                badge.style.display = 'none';
                submitBtn.disabled = true;
                searchInput.value = '';
                searchInput.focus();
                console.log('[WalletTransfer] Customer selection cleared');
            }

            clearBtn.addEventListener('click', clearSelectedCustomer);

            searchInput.addEventListener('input', function() {
                const query = this.value.trim();

                if (selectedCustomerId) {
                    clearSelectedCustomer();
                }

                if (query.length < 2) {
                    dropdown.style.display = 'none';
                    return;
                }

                // Debounce: cancel previous timeout
                if (searchTimeout) {
                    clearTimeout(searchTimeout);
                }

                // Cancel previous in-flight request
                if (currentRequest) {
                    currentRequest.abort();
                    currentRequest = null;
                }

                // Show "searching..." state
                dropdown.innerHTML = '<div class="searching"><span class="spinner"></span>Searching...</div>';
                dropdown.style.display = 'block';

                searchTimeout = setTimeout(function() {
                    const url = '{{ route('vendor.wallet-transfer.search-customers') }}?term=' + encodeURIComponent(query);
                    console.log('[WalletTransfer] Sending GET request:', url);

                    const xhr = new XMLHttpRequest();
                    currentRequest = xhr;

                    xhr.open('GET', url, true);
                    xhr.setRequestHeader('Accept', 'application/json');
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

                    xhr.onload = function() {
                        currentRequest = null;
                        console.log('[WalletTransfer] Response status:', xhr.status);

                        if (xhr.status >= 200 && xhr.status < 300) {
                            try {
                                const data = JSON.parse(xhr.responseText);
                                console.log('[WalletTransfer] Response data:', data);
                                renderResults(data.results || []);
                            } catch (e) {
                                console.error('[WalletTransfer] JSON parse error:', e, xhr.responseText);
                                dropdown.innerHTML = '<div class="no-results">Error parsing response</div>';
                                dropdown.style.display = 'block';
                            }
                        } else {
                            console.error('[WalletTransfer] HTTP error:', xhr.status, xhr.responseText);
                            dropdown.innerHTML = '<div class="no-results">Error (HTTP ' + xhr.status + ')</div>';
                            dropdown.style.display = 'block';
                        }
                    };

                    xhr.onerror = function() {
                        currentRequest = null;
                        console.error('[WalletTransfer] Network error - request failed');
                        dropdown.innerHTML = '<div class="no-results">Network error</div>';
                        dropdown.style.display = 'block';
                    };

                    xhr.send();
                }, 350);
            });

            function renderResults(results) {
                if (!results || results.length === 0) {
                    dropdown.innerHTML = '<div class="no-results">No customers found</div>';
                    dropdown.style.display = 'block';
                    return;
                }

                let html = '';
                results.forEach(function(customer) {
                    const label = customer.text || '';
                    const parts = label.split(' (');
                    const name = parts[0] || label;
                    const email = parts.length > 1 ? parts[1].replace(')', '') : '';
                    const initial = name.charAt(0).toUpperCase();

                    html += '<div class="dropdown-item" data-id="' + customer.id + '" data-name="' + escapeHtml(name) + '">';
                    html += '    <div class="avatar">' + initial + '</div>';
                    html += '    <div class="info">';
                    html += '        <div class="name">' + escapeHtml(name) + '</div>';
                    if (email) {
                        html += '        <div class="detail">' + escapeHtml(email) + '</div>';
                    }
                    html += '    </div>';
                    html += '</div>';
                });

                dropdown.innerHTML = html;
                dropdown.style.display = 'block';

                // Add click handlers
                dropdown.querySelectorAll('.dropdown-item').forEach(function(item) {
                    item.addEventListener('click', function() {
                        const id = this.getAttribute('data-id');
                        const name = this.getAttribute('data-name');
                        setSelectedCustomer(id, name);
                    });
                });

                console.log('[WalletTransfer] Rendered', results.length, 'results');
            }

            function escapeHtml(str) {
                if (!str) return '';
                return str
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            // Close dropdown when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.customer-search-wrap') && !e.target.closest('.selected-customer-badge')) {
                    dropdown.style.display = 'none';
                }
            });

            // Prevent form submit without customer
            document.getElementById('transferForm').addEventListener('submit', function(e) {
                if (!selectedCustomerId) {
                    e.preventDefault();
                    alert('{{ translate('please_select_a_customer') }}');
                }
            });

            console.log('[WalletTransfer] Customer search initialized successfully.');
        })();
    </script>
@endpush
