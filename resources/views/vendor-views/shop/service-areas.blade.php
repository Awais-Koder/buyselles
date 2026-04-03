@extends('layouts.vendor.app')

@section('title', translate('service_areas'))

@section('content')
    <div class="content container-fluid">
        <h2 class="h1 mb-0 text-capitalize d-flex mb-3">
            {{ translate('shop_info') }}
        </h2>

        @include('vendor-views.shop.inline-menu')

        <form action="{{ route('vendor.shop.service-areas.update') }}" method="post" id="service-areas-form">
            @csrf

            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fi fi-rr-marker"></i>
                        {{ translate('Service_Areas') }}
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-4">
                        {{ translate('Select_the_areas_where_you_deliver_products.') }}
                    </p>

                    @if ($countries->isEmpty())
                        <div class="text-center py-5">
                            <img src="{{ asset('public/assets/back-end/img/empty-state-icon/default.png') }}" alt=""
                                class="mb-3" width="100">
                            <p class="text-muted">
                                {{ translate('No_locations_have_been_configured_yet._Please_contact_the_admin.') }}
                            </p>
                        </div>
                    @else
                        @foreach ($countries as $country)
                            <div class="card border mb-3">
                                <div class="card-header bg-light py-2 cursor-pointer" data-toggle="collapse"
                                    data-target="#country-{{ $country->id }}" aria-expanded="false">
                                    <div class="d-flex align-items-center justify-content-between w-100">
                                        <h6 class="mb-0">
                                            <i class="fi fi-rr-globe mr-1"></i>
                                            {{ $country->name }}
                                            @if ($country->code)
                                                <span class="text-muted">({{ $country->code }})</span>
                                            @endif
                                        </h6>
                                        <i class="fi fi-rr-angle-small-down collapse-icon"></i>
                                    </div>
                                </div>
                                <div class="collapse" id="country-{{ $country->id }}">
                                    <div class="card-body p-0">
                                        @foreach ($country->cities as $city)
                                            @if ($city->areas->isNotEmpty())
                                                <div class="border-bottom">
                                                    <div class="bg-light-2 px-3 py-2 cursor-pointer" data-toggle="collapse"
                                                        data-target="#city-{{ $city->id }}" aria-expanded="false">
                                                        <div class="d-flex align-items-center justify-content-between">
                                                            <span class="font-weight-semibold">
                                                                <i class="fi fi-rr-building mr-1"></i>
                                                                {{ $city->name }}
                                                                <span
                                                                    class="badge badge-soft-info ml-1">{{ $city->areas->count() }}
                                                                    {{ translate('areas') }}</span>
                                                            </span>
                                                            <div class="d-flex align-items-center gap-2">
                                                                <label
                                                                    class="mb-0 mr-3 cursor-pointer text-primary select-all-city"
                                                                    data-city-id="{{ $city->id }}">
                                                                    {{ translate('Select_All') }}
                                                                </label>
                                                                <i class="fi fi-rr-angle-small-down collapse-icon"></i>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="collapse" id="city-{{ $city->id }}">
                                                        <div class="table-responsive">
                                                            <table class="table table-hover mb-0">
                                                                <thead class="thead-light">
                                                                    <tr>
                                                                        <th class="text-center" width="50">
                                                                            {{ translate('Select') }}
                                                                        </th>
                                                                        <th>{{ translate('Area') }}</th>
                                                                        <th>{{ translate('COD') }}</th>
                                                                        {{-- Shipping cost columns hidden for now --}}
                                                                        <th width="180" class="d-none">
                                                                            {{ translate('Shipping_Cost') }}
                                                                            ({{ getCurrencySymbol() }})
                                                                        </th>
                                                                        <th width="150" class="d-none">
                                                                            {{ translate('Est._Days') }}
                                                                        </th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach ($city->areas as $area)
                                                                        @php
                                                                            $isSelected = in_array(
                                                                                $area->id,
                                                                                $selectedAreaIds,
                                                                            );
                                                                            $rate = $shippingRates->get($area->id);
                                                                        @endphp
                                                                        <tr class="area-row {{ $isSelected ? 'table-success' : '' }}"
                                                                            data-city-id="{{ $city->id }}">
                                                                            <td class="text-center">
                                                                                <input type="checkbox" name="area_ids[]"
                                                                                    value="{{ $area->id }}"
                                                                                    class="area-checkbox"
                                                                                    data-city-id="{{ $city->id }}"
                                                                                    {{ $isSelected ? 'checked' : '' }}>
                                                                            </td>
                                                                            <td>
                                                                                <span
                                                                                    class="font-weight-medium">{{ $area->name }}</span>
                                                                            </td>
                                                                            <td>
                                                                                @if ($area->cod_available)
                                                                                    <span
                                                                                        class="badge badge-soft-success">{{ translate('Available') }}</span>
                                                                                @else
                                                                                    <span
                                                                                        class="badge badge-soft-danger">{{ translate('N/A') }}</span>
                                                                                @endif
                                                                            </td>
                                                                            {{-- Shipping cost inputs hidden for now --}}
                                                                            <td class="d-none">
                                                                                <input type="number" step="0.01"
                                                                                    min="0"
                                                                                    class="form-control form-control-sm shipping-cost-input"
                                                                                    name="shipping_cost[{{ $area->id }}]"
                                                                                    value="{{ $rate ? $rate->shipping_cost : '0' }}"
                                                                                    placeholder="0.00"
                                                                                    {{ !$isSelected ? 'disabled' : '' }}>
                                                                            </td>
                                                                            <td class="d-none">
                                                                                <input type="number" min="1"
                                                                                    max="90"
                                                                                    class="form-control form-control-sm estimated-days-input"
                                                                                    name="estimated_days[{{ $area->id }}]"
                                                                                    value="{{ $rate ? $rate->estimated_days : '' }}"
                                                                                    placeholder="{{ translate('Days') }}"
                                                                                    {{ !$isSelected ? 'disabled' : '' }}>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach

                        <div class="d-flex justify-content-end mt-3">
                            <button type="submit" class="btn btn--primary px-5">
                                {{ translate('Save_Changes') }}
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </form>

        {{-- ── Manage Locations (inline) ────────────────────────────────── --}}
        <div class="card mb-3" id="manage-locations">
            <div class="card-header cursor-pointer" data-toggle="collapse" data-target="#manage-locations-body"
                aria-expanded="false">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">
                        <i class="fi fi-rr-settings me-1"></i>
                        {{ translate('Manage_Locations') }}
                        <small
                            class="text-muted fs-12 fw-normal ms-1">{{ translate('Add_countries,_cities_&_areas') }}</small>
                    </h5>
                    <i class="fi fi-rr-angle-small-down collapse-icon"></i>
                </div>
            </div>
            <div class="collapse" id="manage-locations-body">
                <div class="card-body">

                    {{-- Tab nav --}}
                    <ul class="nav nav-tabs mb-3" id="locationMgmtTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" id="tab-country" data-toggle="tab" href="#pane-country"
                                role="tab">
                                <i class="fi fi-rr-globe mr-1"></i> {{ translate('Country') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-city" data-toggle="tab" href="#pane-city" role="tab">
                                <i class="fi fi-rr-building mr-1"></i> {{ translate('City') }}
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" id="tab-area" data-toggle="tab" href="#pane-area" role="tab">
                                <i class="fi fi-rr-marker mr-1"></i> {{ translate('Area') }}
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content">

                        {{-- Add Country --}}
                        <div class="tab-pane fade show active" id="pane-country" role="tabpanel">
                            <form action="{{ route('vendor.shop.location.add-country') }}" method="POST">
                                @csrf
                                <div class="row align-items-end">
                                    <div class="col-md-5">
                                        <label class="form-label title-color">{{ translate('Country_Name') }} <span
                                                class="input-required-icon">*</span></label>
                                        <input type="text" name="name" class="form-control"
                                            placeholder="{{ translate('e.g._Saudi_Arabia') }}" required>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label title-color">{{ translate('Country_Code') }}</label>
                                        <input type="text" name="code" class="form-control"
                                            placeholder="{{ translate('e.g._SA') }}" maxlength="10">
                                    </div>
                                    <div class="col-md-4 mt-2 mt-md-0">
                                        <button type="submit" class="btn btn--primary">
                                            {{ translate('Add_Country') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        {{-- Add City --}}
                        <div class="tab-pane fade" id="pane-city" role="tabpanel">
                            <form action="{{ route('vendor.shop.location.add-city') }}" method="POST">
                                @csrf
                                <div class="row align-items-end">
                                    <div class="col-md-4">
                                        <label class="form-label title-color">{{ translate('Country') }} <span
                                                class="input-required-icon">*</span></label>
                                        <select name="country_id" id="mgmt-country-for-city" class="form-control"
                                            required>
                                            <option value="">{{ translate('Select_Country') }}</option>
                                            @foreach ($countries as $c)
                                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label title-color">{{ translate('City_Name') }} <span
                                                class="input-required-icon">*</span></label>
                                        <input type="text" name="name" class="form-control"
                                            placeholder="{{ translate('e.g._Riyadh') }}" required>
                                    </div>
                                    <div class="col-md-4 mt-2 mt-md-0">
                                        <button type="submit" class="btn btn--primary">
                                            {{ translate('Add_City') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        {{-- Add Area --}}
                        <div class="tab-pane fade" id="pane-area" role="tabpanel">
                            <form action="{{ route('vendor.shop.location.add-area') }}" method="POST">
                                @csrf
                                <div class="row align-items-end">
                                    <div class="col-md-3">
                                        <label class="form-label title-color">{{ translate('Country') }} <span
                                                class="input-required-icon">*</span></label>
                                        <select id="mgmt-country-for-area" class="form-control">
                                            <option value="">{{ translate('Select_Country') }}</option>
                                            @foreach ($countries as $c)
                                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label title-color">{{ translate('City') }} <span
                                                class="input-required-icon">*</span></label>
                                        <select name="city_id" id="mgmt-city-for-area" class="form-control" disabled
                                            required>
                                            <option value="">{{ translate('Select_City') }}</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label title-color">{{ translate('Area_Name') }} <span
                                                class="input-required-icon">*</span></label>
                                        <input type="text" name="name" class="form-control"
                                            placeholder="{{ translate('e.g._Al_Olaya') }}" required>
                                    </div>
                                    <div class="col-md-3 mt-2 mt-md-0">
                                        <div class="form-group mb-0">
                                            <label
                                                class="form-label title-color d-block">{{ translate('COD_Available') }}</label>
                                            <label class="switcher mb-2">
                                                <input type="checkbox" name="cod_available" class="switcher_input"
                                                    value="1">
                                                <span class="switcher_control"></span>
                                            </label>
                                        </div>
                                        <button type="submit" class="btn btn--primary">
                                            {{ translate('Add_Area') }}
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                    </div>{{-- .tab-content --}}
                </div>
            </div>
        </div>
        {{-- ── END Manage Locations ─────────────────────────────────────── --}}

    </div>
@endsection

@push('script')
    <script>
        'use strict';

        // Load cities when country selected in Add Area tab
        $(document).on('change', '#mgmt-country-for-area', function() {
            var countryId = $(this).val();
            var $citySelect = $('#mgmt-city-for-area');
            $citySelect.prop('disabled', true).html('<option value="">{{ translate('Loading...') }}</option>');
            if (!countryId) {
                $citySelect.html('<option value="">{{ translate('Select_City') }}</option>');
                return;
            }
            $.getJSON('{{ route('vendor.shop.location.all-cities', ':id') }}'.replace(':id', countryId), function(
                data) {
                $citySelect.html('<option value="">{{ translate('Select_City') }}</option>');
                $.each(data, function(i, city) {
                    $citySelect.append($('<option>', {
                        value: city.id,
                        text: city.name
                    }));
                });
                $citySelect.prop('disabled', false);
            });
        });

        // Toggle shipping inputs when checkbox is toggled
        $(document).on('change', '.area-checkbox', function() {
            let row = $(this).closest('tr');
            let isChecked = $(this).is(':checked');

            row.find('.shipping-cost-input, .estimated-days-input').prop('disabled', !isChecked);

            if (isChecked) {
                row.addClass('table-success');
            } else {
                row.removeClass('table-success');
                row.find('.shipping-cost-input').val('0');
                row.find('.estimated-days-input').val('');
            }
        });

        // Select all areas in a city
        $(document).on('click', '.select-all-city', function(e) {
            e.stopPropagation();
            let cityId = $(this).data('city-id');
            let checkboxes = $(`.area-checkbox[data-city-id="${cityId}"]`);
            let allChecked = checkboxes.filter(':checked').length === checkboxes.length;

            checkboxes.prop('checked', !allChecked).trigger('change');

            $(this).text(allChecked ?
                '{{ translate('Select_All') }}' :
                '{{ translate('Deselect_All') }}'
            );
        });

        // Auto-expand countries/cities that have selected areas
        @foreach ($countries as $country)
            @php
                $hasSelectedInCountry = false;
            @endphp
            @foreach ($country->cities as $city)
                @php
                    $hasSelectedInCity = $city->areas->pluck('id')->intersect($selectedAreaIds)->isNotEmpty();
                    if ($hasSelectedInCity) {
                        $hasSelectedInCountry = true;
                    }
                @endphp
                @if ($hasSelectedInCity)
                    $('#city-{{ $city->id }}').addClass('show');
                @endif
            @endforeach
            @if ($hasSelectedInCountry)
                $('#country-{{ $country->id }}').addClass('show');
            @endif
        @endforeach
    </script>
@endpush
