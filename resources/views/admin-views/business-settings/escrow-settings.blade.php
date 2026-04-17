@extends('layouts.admin.app')

@section('title', translate('Escrow_Settings'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3 mb-sm-20">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                {{ translate('Business_Setup') }}
            </h2>
        </div>

        @include('admin-views.business-settings.business-setup-inline-menu')

        <form action="{{ route('admin.business-settings.escrow-settings-update') }}" method="POST">
            @csrf

            <div class="card">
                <div class="card-body d-flex flex-column gap-3 gap-sm-20">
                    <div class="p-12 p-sm-20 bg-section rounded">
                        <div class="d-flex justify-content-between align-items-center gap-3">
                            <div>
                                <h2 class="text-capitalize">{{ translate('Escrow_Protection') }}</h2>
                                <p class="mb-0">
                                    {{ translate('Configure_escrow_protection_to_hold_vendor_earnings_until_the_buyer_confirms_receipt_or_the_auto-release_period_expires._This_helps_protect_buyers_from_fraud.') }}
                                </p>
                            </div>
                        </div>
                    </div>

                    @php
                        $escrowProtectionStatus = getWebConfig(name: 'escrow_protection_status') ?? 0;
                        $escrowAutoReleaseHours = getWebConfig(name: 'escrow_auto_release_hours') ?? 48;
                        $escrowPhysicalProducts = getWebConfig(name: 'escrow_physical_products') ?? 0;
                        $escrowDigitalProducts  = getWebConfig(name: 'escrow_digital_products') ?? 0;
                        $disputeWindowDays      = getWebConfig(name: 'dispute_window_days') ?? 7;
                    @endphp

                    <div class="p-12 p-sm-20 bg-section rounded">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label text-capitalize" for="escrow_protection_status">
                                        {{ translate('Escrow_Protection_Status') }}
                                        <span class="tooltip-icon" data-bs-toggle="tooltip" data-bs-placement="right"
                                              data-bs-title="{{ translate('When_enabled,_vendor_earnings_are_held_in_escrow_until_buyer_confirms_receipt_or_auto-release_period_expires.') }}">
                                            <i class="fi fi-sr-info"></i>
                                        </span>
                                    </label>
                                    <label class="d-flex justify-content-between align-items-center gap-3 border rounded px-3 py-10 bg-white user-select-none">
                                        <span class="fw-medium text-dark">{{ translate('status') }}</span>
                                        <label class="switcher" for="escrow_protection_status">
                                            <input
                                                class="switcher_input"
                                                type="checkbox" value="1"
                                                name="escrow_protection_status"
                                                id="escrow_protection_status"
                                                {{ $escrowProtectionStatus == 1 ? 'checked' : '' }}>
                                            <span class="switcher_control"></span>
                                        </label>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label text-capitalize" for="escrow_auto_release_hours">
                                        {{ translate('Auto_Release_After') }} ({{ translate('Hours') }})
                                        <span class="tooltip-icon" data-bs-toggle="tooltip" data-bs-placement="right"
                                              data-bs-title="{{ translate('Funds_will_be_automatically_released_to_the_vendor_after_this_many_hours_if_no_dispute_is_opened.') }}">
                                            <i class="fi fi-sr-info"></i>
                                        </span>
                                    </label>
                                    <input type="number" class="form-control"
                                           name="escrow_auto_release_hours"
                                           id="escrow_auto_release_hours"
                                           step="1" min="1" max="720"
                                           placeholder="{{ translate('ex') . ': 48' }}"
                                           value="{{ $escrowAutoReleaseHours }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-12 p-sm-20 bg-section rounded">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label text-capitalize" for="dispute_window_days">
                                        {{ translate('Dispute_Window_After_Delivery') }} ({{ translate('Days') }})
                                        <span class="tooltip-icon" data-bs-toggle="tooltip" data-bs-placement="right"
                                              data-bs-title="{{ translate('Customers_can_only_open_a_dispute_within_this_many_days_after_order_delivery._After_this_period,_disputes_are_no_longer_allowed.') }}">
                                            <i class="fi fi-sr-info"></i>
                                        </span>
                                    </label>
                                    <input type="number" class="form-control"
                                           name="dispute_window_days"
                                           id="dispute_window_days"
                                           step="1" min="1" max="90"
                                           placeholder="{{ translate('ex') . ': 7' }}"
                                           value="{{ $disputeWindowDays }}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="p-12 p-sm-20 bg-section rounded">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label text-capitalize" for="escrow_physical_products">
                                        {{ translate('Apply_Escrow_to_Physical_Products') }}
                                        <span class="tooltip-icon" data-bs-toggle="tooltip" data-bs-placement="right"
                                              data-bs-title="{{ translate('When_enabled,_escrow_protection_will_apply_to_orders_containing_physical_(tangible)_products.') }}">
                                            <i class="fi fi-sr-info"></i>
                                        </span>
                                    </label>
                                    <label class="d-flex justify-content-between align-items-center gap-3 border rounded px-3 py-10 bg-white user-select-none">
                                        <span class="fw-medium text-dark">{{ translate('status') }}</span>
                                        <label class="switcher" for="escrow_physical_products">
                                            <input
                                                class="switcher_input"
                                                type="checkbox" value="1"
                                                name="escrow_physical_products"
                                                id="escrow_physical_products"
                                                {{ $escrowPhysicalProducts == 1 ? 'checked' : '' }}>
                                            <span class="switcher_control"></span>
                                        </label>
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label text-capitalize" for="escrow_digital_products">
                                        {{ translate('Apply_Escrow_to_Digital_Products') }}
                                        <span class="tooltip-icon" data-bs-toggle="tooltip" data-bs-placement="right"
                                              data-bs-title="{{ translate('When_enabled,_escrow_protection_will_apply_to_orders_containing_digital_products_(gift_cards,_game_keys,_codes,_etc.).') }}">
                                            <i class="fi fi-sr-info"></i>
                                        </span>
                                    </label>
                                    <label class="d-flex justify-content-between align-items-center gap-3 border rounded px-3 py-10 bg-white user-select-none">
                                        <span class="fw-medium text-dark">{{ translate('status') }}</span>
                                        <label class="switcher" for="escrow_digital_products">
                                            <input
                                                class="switcher_input"
                                                type="checkbox" value="1"
                                                name="escrow_digital_products"
                                                id="escrow_digital_products"
                                                {{ $escrowDigitalProducts == 1 ? 'checked' : '' }}>
                                            <span class="switcher_control"></span>
                                        </label>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div>
                        <div class="bg-info bg-opacity-10 fs-12 px-12 py-10 text-dark rounded d-flex gap-2 align-items-center">
                            <i class="fi fi-sr-bulb text-info fs-16"></i>
                            <span>
                                {{ translate('Escrow_protection_only_applies_to_third-party_vendor_orders_with_online_or_wallet_payments._COD_and_offline_payments_are_excluded.') }}
                            </span>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end trans3">
                        <div class="d-flex justify-content-sm-end justify-content-center gap-3 flex-grow-1 flex-grow-sm-0 bg-white action-btn-wrapper trans3">
                            <button type="reset" class="btn btn-secondary px-3 px-sm-4 w-120">
                                {{ translate('reset') }}
                            </button>
                            <button type="submit" class="btn btn-primary px-3 px-sm-4">
                                <i class="fi fi-sr-disk"></i>
                                {{ translate('save_information') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection
