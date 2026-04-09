@extends('layouts.vendor.app')

@section('title', translate('developer_api'))

@section('content')
<div class="content container-fluid">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
            <h1 class="mb-1 text-capitalize">{{ translate('developer_api') }}</h1>
            @if($apiKey)
                <span class="text-muted small font-monospace">{{ $apiKey->name }}</span>
            @endif
        </div>
        @if($apiKey)
        <div class="d-flex gap-2 align-items-center flex-wrap">
            @if($apiKey->status === 'active')
                <span class="badge bg-success fs-6 px-3 py-2">
                    <i class="fi fi-rr-check me-1"></i>{{ translate('active') }}
                </span>
            @elseif($apiKey->status === 'pending')
                <span class="badge bg-warning text-dark fs-12 px-3 py-2">
                    <i class="fi fi-rr-time-half-past me-1"></i>{{ translate('pending_approval') }}
                </span>
            @else
                <span class="badge bg-secondary fs-12 px-3 py-2">
                    <i class="fi fi-rr-ban me-1"></i>{{ translate('inactive') }}
                </span>
            @endif
            <a href="{{ route('vendor.developer.logs') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fi fi-rr-list me-1"></i>{{ translate('view_logs') }}
            </a>
        </div>
        @endif
    </div>

    {{-- Status banners --}}
    @if(!$apiKey)
        <div class="d-flex gap-2 alert alert-soft-info mb-3" role="alert">
            <i class="fi fi-sr-info"></i>
            <p class="fs-12 mb-0 text-dark">
                {{ translate('request_an_api_key_to_access_the_partner_api_and_integrate_with_our_marketplace') }}
            </p>
        </div>
    @elseif($apiKey->status === 'pending')
        <div class="d-flex gap-2 alert alert-warning mb-3" role="alert">
            <i class="fi fi-sr-triangle-warning"></i>
            <p class="fs-12 mb-0">
                {{ translate('your_api_key_request_is_pending_admin_approval') }}
                @if($apiKey->request_note)
                    <br><span class="text-muted">{{ translate('your_note') }}: {{ $apiKey->request_note }}</span>
                @endif
            </p>
        </div>
    @elseif($apiKey->status === 'inactive')
        <div class="d-flex gap-2 alert alert-soft-danger mb-3" role="alert">
            <i class="fi fi-sr-cross"></i>
            <p class="fs-12 mb-0">
                {{ translate('your_api_key_has_been_deactivated') }}
                @if($apiKey->admin_note)
                    <br><strong>{{ translate('admin_note') }}:</strong> {{ $apiKey->admin_note }}
                @endif
            </p>
        </div>
    @endif

    {{-- One-time key reveal banner (shown only right after generation / regeneration) --}}
    @if(session('new_api_key'))
    <div class="alert border border-success bg-success bg-opacity-10 mb-4" id="one-time-key-alert">
        <div class="d-flex align-items-center gap-2 mb-2">
            <i class="fi fi-sr-check-circle text-success fs-5"></i>
            <strong class="text-success">{{ translate('copy_your_credentials_now') }}</strong>
        </div>
        <p class="mb-3 small text-muted">
            <i class="fi fi-sr-triangle-warning text-warning me-1"></i>
            {{ translate('these_credentials_are_shown_only_once_and_cannot_be_recovered_store_them_securely') }}
        </p>

        {{-- API Key --}}
        <div class="mb-3">
            <label class="form-label fw-semibold small mb-1">{{ translate('api_key') }}</label>
            <div class="input-group">
                <input type="password" id="flash-api-key" class="form-control font-monospace"
                       value="{{ session('new_api_key') }}" readonly autocomplete="off">
                <button type="button" class="btn btn-outline-secondary toggle-secret" data-target="flash-api-key" title="{{ translate('show_hide') }}">
                    <i class="fi fi-rr-eye"></i>
                </button>
                <button type="button" class="btn btn-outline-primary copy-btn" data-target="flash-api-key" title="{{ translate('copy') }}">
                    <i class="fi fi-rr-copy-alt"></i>
                </button>
            </div>
        </div>

        {{-- API Secret --}}
        <div class="mb-3">
            <label class="form-label fw-semibold small mb-1">{{ translate('api_secret') }}</label>
            <div class="input-group">
                <input type="password" id="flash-api-secret" class="form-control font-monospace"
                       value="{{ session('new_api_secret') }}" readonly autocomplete="off">
                <button type="button" class="btn btn-outline-secondary toggle-secret" data-target="flash-api-secret" title="{{ translate('show_hide') }}">
                    <i class="fi fi-rr-eye"></i>
                </button>
                <button type="button" class="btn btn-outline-primary copy-btn" data-target="flash-api-secret" title="{{ translate('copy') }}">
                    <i class="fi fi-rr-copy-alt"></i>
                </button>
            </div>
        </div>

        <button type="button" class="btn btn-sm btn-success" id="confirm-copied-btn">
            <i class="fi fi-rr-check me-1"></i>{{ translate('i_have_saved_my_credentials') }}
        </button>
    </div>
    @endif

    {{-- ── No API Key: centered request form ────────────────────────────── --}}
    @if(!$apiKey)
    <div class="row justify-content-center">
        <div class="col-lg-7 col-xl-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fi fi-rr-key me-2"></i>{{ translate('request_api_access') }}
                    </h5>
                </div>
                <div class="card-body d-flex flex-column gap-3">
                    <div class="d-flex gap-2 alert alert-soft-info mb-0 py-2" role="alert">
                        <i class="fi fi-sr-info mt-1"></i>
                        <p class="fs-12 mb-0 text-dark">
                            {{ translate('submit_a_request_and_the_admin_will_review_and_activate_your_api_access') }}
                        </p>
                    </div>
                    <form method="POST" action="{{ route('vendor.developer.request-key') }}">
                        @csrf
                        <div class="d-flex flex-column gap-3">
                            <div>
                                <label class="form-label fw-semibold">{{ translate('key_name') }} <span class="text-danger">*</span></label>
                                <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name') }}"
                                       placeholder="{{ translate('e_g_my_store_integration') }}" required maxlength="100">
                                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                            <div>
                                <label class="form-label fw-semibold">
                                    {{ translate('request_note') }}
                                    <span class="text-muted fw-normal">({{ translate('optional') }})</span>
                                </label>
                                <textarea name="request_note" class="form-control" rows="3" maxlength="500"
                                          placeholder="{{ translate('briefly_describe_how_you_plan_to_use_the_api') }}">{{ old('request_note') }}</textarea>
                            </div>
                            <div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fi fi-rr-paper-plane me-1"></i>{{ translate('submit_request') }}
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ── Key exists: two-column layout ────────────────────────────────── --}}
    @if($apiKey)
    <div class="row gy-3">

        {{-- Left: Key Info Card --}}
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fi fi-rr-key me-2"></i>{{ translate('api_credentials') }}
                    </h5>
                </div>
                <div class="card-body d-flex flex-column gap-3">
                    <div>
                        <label class="form-label fw-semibold mb-1">{{ translate('key_name') }}</label>
                        <p class="mb-0">{{ $apiKey->name }}</p>
                    </div>

                    {{-- Masked key display (when no fresh session flash) --}}
                    @if(!session('new_api_key'))
                    <div class="p-3 rounded border bg-light">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="fi fi-sr-lock text-muted"></i>
                            <span class="fw-semibold small">{{ translate('api_key') }}</span>
                        </div>
                        <div class="font-monospace text-muted small">rslr_••••••••••••••••••••••••••••••••••••••••</div>
                        <div class="mt-2 d-flex align-items-center gap-2 mb-1">
                            <i class="fi fi-sr-lock text-muted"></i>
                            <span class="fw-semibold small">{{ translate('api_secret') }}</span>
                        </div>
                        <div class="font-monospace text-muted small">••••••••••••••••••••••••••••••••••••••••••••••••</div>
                        <div class="mt-2 small text-muted fst-italic">
                            <i class="fi fi-sr-info me-1"></i>{{ translate('credentials_are_hidden_use_regenerate_to_get_new_ones') }}
                        </div>
                    </div>
                    @endif

                    <div>
                        <label class="form-label fw-semibold mb-1">{{ translate('rate_limit') }}</label>
                        <p class="mb-0">{{ $apiKey->rate_limit_per_minute }} {{ translate('requests_per_minute') }}</p>
                    </div>

                    <div>
                        <label class="form-label fw-semibold mb-1">{{ translate('total_requests') }}</label>
                        <p class="mb-0">{{ number_format($apiKey->total_requests) }}</p>
                    </div>

                    @if($apiKey->last_used_at)
                        <div>
                            <label class="form-label fw-semibold mb-1">{{ translate('last_used') }}</label>
                            <p class="mb-0">{{ $apiKey->last_used_at->diffForHumans() }}</p>
                        </div>
                    @endif

                    @if($apiKey->approved_at)
                        <div>
                            <label class="form-label fw-semibold mb-1">{{ translate('approved_at') }}</label>
                            <p class="mb-0">{{ $apiKey->approved_at->format('d M Y') }}</p>
                        </div>
                    @endif

                    @if($apiKey->admin_note)
                        <div>
                            <label class="form-label fw-semibold mb-1">{{ translate('admin_note') }}</label>
                            <p class="mb-0 text-muted fst-italic">{{ $apiKey->admin_note }}</p>
                        </div>
                    @endif

                    {{-- Action buttons --}}
                    <div class="d-flex flex-wrap gap-2 mt-auto pt-2 border-top">
                        @if(in_array($apiKey->status, ['active', 'inactive']))
                        <button type="button" class="btn btn-outline-warning btn-sm" id="regenerate-key-btn">
                            <i class="fi fi-rr-refresh me-1"></i>{{ translate('regenerate_key') }}
                        </button>
                        @endif

                        <button type="button" class="btn btn-outline-danger btn-sm" id="revoke-key-btn">
                            <i class="fi fi-rr-trash me-1"></i>{{ translate('revoke_key') }}
                        </button>
                    </div>
                </div>
            </div>
            {{-- Hidden forms outside the card (no nesting issues) --}}
            <form method="POST" action="{{ route('vendor.developer.revoke-key') }}" id="revoke-key-form" class="d-none">@csrf</form>
            <form method="POST" action="{{ route('vendor.developer.regenerate-key') }}" id="regenerate-key-form" class="d-none">@csrf</form>
        </div>

        {{-- Right: IP Whitelist + Permissions --}}
        <div class="col-lg-5 d-flex flex-column gap-3">

            {{-- IP Whitelist --}}
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fi fi-rr-network me-2"></i>{{ translate('ip_whitelist') }}
                    </h5>
                    <span class="badge bg-primary text-white" id="ip-count-badge">
                        {{ count($apiKey->allowed_ips ?? []) }} IPs
                    </span>
                </div>
                <div class="card-body d-flex flex-column gap-3">

                    {{-- Format guide --}}
                    <div class="border rounded p-3 bg-light">
                        <div class="fw-semibold small mb-2">
                            <i class="fi fi-sr-info me-1 text-primary"></i>{{ translate('accepted_formats') }}
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-sm-6 d-flex align-items-start gap-2 small">
                                <i class="fi fi-sr-check text-success mt-1 flex-shrink-0"></i>
                                <div><code>192.168.1.100</code><br><span class="text-muted">Standard IPv4</span></div>
                            </div>
                            <div class="col-sm-6 d-flex align-items-start gap-2 small">
                                <i class="fi fi-sr-check text-success mt-1 flex-shrink-0"></i>
                                <div><code>10.0.0.1</code> &nbsp;<code>172.16.0.5</code><br><span class="text-muted">Private IPv4</span></div>
                            </div>
                            <div class="col-sm-6 d-flex align-items-start gap-2 small">
                                <i class="fi fi-sr-check text-success mt-1 flex-shrink-0"></i>
                                <div><code>2001:db8::1</code><br><span class="text-muted">IPv6 (full or compressed)</span></div>
                            </div>
                            <div class="col-sm-6 d-flex align-items-start gap-2 small">
                                <i class="fi fi-sr-cross text-danger mt-1 flex-shrink-0"></i>
                                <div><code>192.168.1.0/24</code><br><span class="text-muted">CIDR — <strong>not supported</strong></span></div>
                            </div>
                        </div>
                        <div class="pt-2 border-top small text-muted">
                            Type an IP and press <kbd>Enter</kbd> or click <strong>Add</strong>.
                            You can paste multiple IPs separated by commas or newlines.
                            Leave the list empty to allow all IPs.
                        </div>
                    </div>

                    <form method="POST" action="{{ route('vendor.developer.update-ips') }}" id="ip-whitelist-form">
                        @csrf

                        {{-- Current tag list --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold mb-2">{{ translate('current_whitelist') }}</label>
                            <div id="ip-tag-list" class="d-flex flex-wrap gap-2 mb-1" style="min-height:36px;">
                                @forelse($apiKey->allowed_ips ?? [] as $ip)
                                    <span class="ip-tag d-inline-flex align-items-center gap-1 badge bg-secondary px-2 py-1 fw-normal"
                                          data-ip="{{ $ip }}"
                                          style="font-family:monospace; font-size:0.82rem;">
                                        <i class="fi fi-rr-globe-alt" style="font-size:0.75rem;"></i>
                                        <span>{{ $ip }}</span>
                                        <button type="button" class="ip-tag-remove" data-ip="{{ $ip }}"
                                                style="background:none;border:none;color:#fff;font-size:1rem;line-height:1;padding:0 0 0 4px;cursor:pointer;opacity:0.8;"
                                                title="Remove {{ $ip }}">&times;</button>
                                    </span>
                                @empty
                                    <span id="ip-empty-notice" class="text-muted small fst-italic">
                                        {{ translate('no_ips_saved_all_ips_are_allowed') }}
                                    </span>
                                @endforelse
                            </div>
                        </div>

                        {{-- Add input --}}
                        <div class="mb-3">
                            <label class="form-label fw-semibold">{{ translate('add_ip_address') }}</label>
                            <div class="input-group">
                                <input type="text" id="ip-text-input" class="form-control font-monospace"
                                       placeholder="e.g. 192.168.1.100"
                                       autocomplete="off" spellcheck="false">
                                <button type="button" class="btn btn-outline-primary" id="ip-add-btn">
                                    <i class="fi fi-rr-plus me-1"></i>{{ translate('add') }}
                                </button>
                            </div>
                            <div id="ip-error-msg" class="small mt-1" style="display:none;"></div>
                        </div>

                        {{-- Hidden field carries the final list on POST --}}
                        <textarea name="allowed_ips" id="ip-hidden-value" class="d-none" aria-hidden="true">{{ implode("\n", $apiKey->allowed_ips ?? []) }}</textarea>

                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fi fi-rr-check me-1"></i>{{ translate('save_whitelist') }}
                        </button>
                    </form>
                </div>
            </div>

            {{-- Permissions --}}
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fi fi-rr-shield me-2"></i>{{ translate('permissions') }}
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0 d-flex flex-column gap-2">
                        @php
                            $permLabels = [
                                'products.list' => translate('list_products_and_stock'),
                                'orders.create' => translate('place_orders'),
                                'orders.view'   => translate('view_order_status_and_codes'),
                                'balance.view'  => translate('check_wallet_balance'),
                            ];
                        @endphp
                        @foreach($permLabels as $perm => $label)
                            @php $has = in_array($perm, $apiKey->permissions ?? []); @endphp
                            <li class="d-flex align-items-center gap-2">
                                <i class="fi fi-sr-{{ $has ? 'check text-success' : 'cross text-secondary' }}"></i>
                                <span class="{{ $has ? '' : 'text-muted' }}">{{ $label }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>

        {{-- Recent logs --}}
        @if($recentLogs->isNotEmpty())
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="fi fi-rr-list me-2"></i>{{ translate('recent_api_activity') }}
                    </h5>
                    <a href="{{ route('vendor.developer.logs') }}" class="btn btn-sm btn-outline-primary">
                        {{ translate('see_all') }}
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-borderless align-middle mb-0">
                            <thead class="text-capitalize">
                                <tr>
                                    <th>{{ translate('method') }}</th>
                                    <th>{{ translate('endpoint') }}</th>
                                    <th class="text-center">{{ translate('status') }}</th>
                                    <th>{{ translate('ip') }}</th>
                                    <th>{{ translate('time_ms') }}</th>
                                    <th>{{ translate('when') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($recentLogs as $log)
                                    <tr>
                                        <td><span class="badge bg-secondary font-monospace">{{ $log->method }}</span></td>
                                        <td class="font-monospace small">{{ $log->endpoint }}</td>
                                        <td class="text-center">
                                            @php $statusClass = $log->http_status < 300 ? 'success' : ($log->http_status < 500 ? 'warning' : 'danger'); @endphp
                                            <span class="badge bg-{{ $statusClass }}">{{ $log->http_status }}</span>
                                        </td>
                                        <td class="small text-muted">{{ $log->ip_address }}</td>
                                        <td class="small text-muted">{{ $log->response_time_ms }}ms</td>
                                        <td class="small text-muted" title="{{ $log->created_at }}">{{ $log->created_at->diffForHumans() }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        @endif

    </div>
    @endif

</div>

@push('script')
<script>
(function () {
    'use strict';

    // ── Eye toggle (show/hide credential fields) ──────────────────────────
    document.querySelectorAll('.toggle-secret').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById(this.dataset.target);
            var icon  = this.querySelector('i');
            if (!input) { return; }
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fi fi-rr-eye-crossed';
            } else {
                input.type = 'password';
                icon.className = 'fi fi-rr-eye';
            }
        });
    });

    // ── Copy to clipboard ─────────────────────────────────────────────────
    document.querySelectorAll('.copy-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = document.getElementById(this.dataset.target);
            if (!input) { return; }
            navigator.clipboard.writeText(input.value).then(function () {
                btn.innerHTML = '<i class="fi fi-rr-check"></i>';
                setTimeout(function () {
                    btn.innerHTML = '<i class="fi fi-rr-copy-alt"></i>';
                }, 2000);
            });
        });
    });

    // ── "I've saved my credentials" dismisses the one-time banner ────────
    var confirmBtn = document.getElementById('confirm-copied-btn');
    if (confirmBtn) {
        confirmBtn.addEventListener('click', function () {
            var banner = document.getElementById('one-time-key-alert');
            if (banner) { banner.style.display = 'none'; }
        });
    }

    // ── Regenerate key confirmation ───────────────────────────────────────
    var regenerateBtn = document.getElementById('regenerate-key-btn');
    if (regenerateBtn) {
        regenerateBtn.addEventListener('click', function () {
            var getText = document.getElementById('get-sweet-alert-messages');
            Swal.fire({
                title: '{{ translate('regenerate_api_key') }}?',
                html:  '<p class="mb-1">{{ translate('this_will_invalidate_your_current_api_key_and_secret') }}</p>' +
                       '<p class="text-danger mb-0 small"><strong>{{ translate('any_integration_using_the_old_key_will_stop_working_immediately') }}</strong></p>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor:  '#6c757d',
                cancelButtonText:  getText?.dataset.cancel  || '{{ translate('cancel') }}',
                confirmButtonText: '{{ translate('yes_regenerate') }}',
                reverseButtons: true,
            }).then(function (result) {
                if (result.isConfirmed) {
                    document.getElementById('regenerate-key-form').submit();
                }
            });
        });
    }

    // ── Revoke key confirmation ───────────────────────────────────────────
    var revokeBtn = document.getElementById('revoke-key-btn');
    if (revokeBtn) {
        revokeBtn.addEventListener('click', function () {
            var getText = document.getElementById('get-sweet-alert-messages');
            Swal.fire({
                title: getText?.dataset.areYouSure || '{{ translate('are_you_sure') }}',
                text:  '{{ translate('this_will_permanently_revoke_your_api_key') }}',
                icon:  'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor:  '#3085d6',
                cancelButtonText:  getText?.dataset.cancel  || '{{ translate('cancel') }}',
                confirmButtonText: getText?.dataset.confirm || '{{ translate('yes_revoke_it') }}',
                reverseButtons: true,
            }).then(function (result) {
                if (result.isConfirmed) {
                    document.getElementById('revoke-key-form').submit();
                }
            });
        });
    }

    // ── IP Tag / Chips UI ─────────────────────────────────────────────────
    var tagList     = document.getElementById('ip-tag-list');
    var textInput   = document.getElementById('ip-text-input');
    var addBtn      = document.getElementById('ip-add-btn');
    var hiddenField = document.getElementById('ip-hidden-value');
    var errorMsg    = document.getElementById('ip-error-msg');
    var countBadge  = document.getElementById('ip-count-badge');
    var ipForm      = document.getElementById('ip-whitelist-form');

    if (!tagList || !textInput || !hiddenField) { return; } // guard: no key yet

    // IPv4 + IPv6 validation (no CIDR)
    function isValidIp(ip) {
        var ipv4 = /^(25[0-5]|2[0-4]\d|1\d{2}|[1-9]?\d)(\.(25[0-5]|2[0-4]\d|1\d{2}|[1-9]?\d)){3}$/;
        if (ipv4.test(ip)) { return true; }
        if (ip.indexOf(':') !== -1) {
            var full = /^([0-9a-fA-F]{1,4}:){7}[0-9a-fA-F]{1,4}$/;
            var comp = /^(([0-9a-fA-F]{1,4}:)*[0-9a-fA-F]{1,4})?::(([0-9a-fA-F]{1,4}:)*[0-9a-fA-F]{1,4})?$/;
            return full.test(ip) || comp.test(ip);
        }
        return false;
    }

    function showError(msg, isInfo) {
        errorMsg.textContent = msg;
        errorMsg.style.color = isInfo ? '#0dcaf0' : '#dc3545';
        errorMsg.style.display = 'block';
        setTimeout(function () { errorMsg.style.display = 'none'; }, 3500);
    }

    function getCurrentIps() {
        var ips = [];
        tagList.querySelectorAll('.ip-tag').forEach(function (el) { ips.push(el.dataset.ip); });
        return ips;
    }

    function syncHidden() {
        var ips = getCurrentIps();
        hiddenField.value = ips.join('\n');
        if (countBadge) { countBadge.textContent = ips.length + ' IPs'; }
        var notice = document.getElementById('ip-empty-notice');
        if (!notice) {
            notice = document.createElement('span');
            notice.id = 'ip-empty-notice';
            notice.className = 'text-muted small fst-italic';
            notice.textContent = '{{ translate('no_ips_saved_all_ips_are_allowed') }}';
            tagList.appendChild(notice);
        }
        notice.style.display = ips.length === 0 ? '' : 'none';
    }

    function createTagEl(ip) {
        var span = document.createElement('span');
        span.className = 'ip-tag d-inline-flex align-items-center gap-1 badge bg-secondary px-2 py-1 fw-normal';
        span.dataset.ip = ip;
        span.style.fontFamily = 'monospace';
        span.style.fontSize = '0.82rem';

        var icon = document.createElement('i');
        icon.className = 'fi fi-rr-globe-alt';
        icon.style.fontSize = '0.75rem';

        var label = document.createElement('span');
        label.textContent = ip;

        var removeBtn = document.createElement('button');
        removeBtn.type = 'button';
        removeBtn.className = 'ip-tag-remove';
        removeBtn.dataset.ip = ip;
        removeBtn.setAttribute('title', 'Remove ' + ip);
        removeBtn.textContent = '\u00d7';
        removeBtn.style.cssText =
            'background:none;border:none;color:#fff;font-size:1rem;line-height:1;padding:0 0 0 4px;cursor:pointer;opacity:0.8;';

        span.appendChild(icon);
        span.appendChild(label);
        span.appendChild(removeBtn);
        return span;
    }

    function addIp(rawIp) {
        var ip = rawIp.trim();
        if (!ip) { return false; }
        if (!isValidIp(ip)) {
            showError('"' + ip + '" is not a valid IPv4 or IPv6 address.');
            return false;
        }
        var existing = getCurrentIps();
        if (existing.indexOf(ip) !== -1) {
            showError('"' + ip + '" is already in the whitelist.');
            tagList.querySelectorAll('.ip-tag').forEach(function (el) {
                if (el.dataset.ip === ip) {
                    el.style.background = '#fd7e14';
                    setTimeout(function () { el.style.background = ''; }, 1200);
                }
            });
            return false;
        }
        var notice = document.getElementById('ip-empty-notice');
        if (notice) { notice.style.display = 'none'; }
        tagList.appendChild(createTagEl(ip));
        syncHidden();
        return true;
    }

    // Event delegation for remove buttons — Swal confirmation
    tagList.addEventListener('click', function (e) {
        var btn = e.target.closest('.ip-tag-remove');
        if (!btn) { return; }
        var ip  = btn.dataset.ip || btn.closest('.ip-tag')?.dataset.ip || '';
        var tag = btn.closest('.ip-tag');
        if (!tag) { return; }
        Swal.fire({
            title: 'Remove IP?',
            html:  'Remove <code style="font-size:0.95em;">' + ip + '</code> from the whitelist?',
            icon:  'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor:  '#6c757d',
            cancelButtonText:  '{{ translate('cancel') }}',
            confirmButtonText: '{{ translate('yes_remove_it') }}',
            reverseButtons: true,
        }).then(function (result) {
            if (result.isConfirmed) {
                tag.remove();
                syncHidden();
            }
        });
    });

    // Add button click
    if (addBtn) {
        addBtn.addEventListener('click', function () {
            if (addIp(textInput.value)) {
                textInput.value = '';
                textInput.focus();
            }
        });
    }

    // Enter / Comma trigger add; Backspace on empty removes last tag
    textInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            e.stopPropagation();
            if (addIp(textInput.value)) { textInput.value = ''; }
        }
        if (e.key === ',') {
            e.preventDefault();
            if (addIp(textInput.value)) { textInput.value = ''; }
        }
        if (e.key === 'Backspace' && textInput.value === '') {
            var tags = tagList.querySelectorAll('.ip-tag');
            if (tags.length > 0) { tags[tags.length - 1].remove(); syncHidden(); }
        }
    });

    // Paste: bulk-add comma/newline/space separated IPs
    textInput.addEventListener('paste', function (e) {
        e.preventDefault();
        var pasted = (e.clipboardData || window.clipboardData).getData('text');
        var parts  = pasted.split(/[\r\n,;\s]+/);
        var added = 0, dupes = 0, invalid = 0;
        parts.forEach(function (p) {
            p = p.trim();
            if (!p) { return; }
            if (!isValidIp(p)) { invalid++; return; }
            if (getCurrentIps().indexOf(p) !== -1) { dupes++; return; }
            addIp(p);
            added++;
        });
        textInput.value = '';
        var msgs = [];
        if (added)   { msgs.push(added   + ' IP(s) added'); }
        if (dupes)   { msgs.push(dupes   + ' duplicate(s) skipped'); }
        if (invalid) { msgs.push(invalid + ' invalid entry(ies) ignored'); }
        if (msgs.length) { showError(msgs.join(' \u00b7 '), true); }
    });

    // Sync hidden field before form submits (catch any leftover text in input)
    if (ipForm) {
        ipForm.addEventListener('submit', function () {
            if (textInput.value.trim()) {
                addIp(textInput.value.trim());
                textInput.value = '';
            }
            syncHidden();
        });
    }

    // Initial sync (server-rendered tags already in DOM — just update the count)
    syncHidden();

}());
</script>
@endpush

@endsection
