@php
    use Illuminate\Support\Facades\Session;
    $direction = Session::get('direction');
    $currentModules = $seller->vendorPermission?->module_access ?? [];
    $isFullAccess = empty($currentModules);
@endphp

@extends('layouts.admin.app')
@section('title', translate('vendor_permissions') . ' — ' . $seller->f_name . ' ' . $seller->l_name)

@section('content')
    <div class="content container-fluid">
        {{-- Breadcrumb --}}
        <div class="mb-3 d-flex align-items-center gap-2">
            <a href="{{ route('admin.vendor-permission.index') }}" class="text-primary text-capitalize">
                <i class="fi fi-rr-arrow-left me-1"></i>{{ translate('vendor_permissions') }}
            </a>
            <span class="text-muted">/</span>
            <span class="text-capitalize">{{ $seller->f_name }} {{ $seller->l_name }}</span>
        </div>

        <div class="mb-3">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2 text-capitalize">
                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png') }}" width="28"
                    alt="">
                {{ translate('manage_vendor_permissions') }}
            </h2>
        </div>

        <div class="row">
            {{-- Vendor info card --}}
            <div class="col-lg-4 mb-4">
                <div class="card h-100">
                    <div class="card-body text-center py-4">
                        @if ($seller->image)
                            <img class="rounded-circle mb-3"
                                src="{{ getStorageImages(path: $seller->image_full_url, type: 'backend-logo') }}"
                                width="80" height="80" alt="{{ $seller->f_name }}">
                        @else
                            <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white fw-bold mx-auto mb-3"
                                style="width:80px;height:80px;font-size:2rem;">
                                {{ strtoupper(substr($seller->f_name ?? 'V', 0, 1)) }}
                            </div>
                        @endif
                        <h5 class="mb-1">{{ $seller->f_name }} {{ $seller->l_name }}</h5>
                        <p class="text-muted fs-13 mb-2">{{ $seller->email }}</p>
                        @if ($seller->shop)
                            <span class="badge text-bg-info text-capitalize">{{ $seller->shop->name }}</span>
                        @endif
                        <hr>
                        <div class="text-start">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-muted fs-13">{{ translate('status') }}</span>
                                <span
                                    class="badge {{ $seller->status == 'approved' ? 'text-bg-success' : 'text-bg-danger' }} text-capitalize">
                                    {{ $seller->status }}
                                </span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="text-muted fs-13">{{ translate('current_access') }}</span>
                                @if ($isFullAccess)
                                    <span class="badge text-bg-success">{{ translate('full_access') }}</span>
                                @else
                                    <span class="badge text-bg-warning">{{ count($currentModules) }}
                                        {{ translate('modules') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Permissions form --}}
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">{{ translate('module_access_control') }}</h5>
                        <p class="text-muted fs-12 mb-0 mt-1">
                            {{ translate('check_the_modules_this_vendor_is_allowed_to_access._Unchecked_modules_will_be_blocked._If_all_are_checked,_the_vendor_has_full_access.') }}
                        </p>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.vendor-permission.update') }}" method="POST"
                            class="form-advance-validation non-ajax-form-validate" novalidate>
                            @csrf
                            <input type="hidden" name="seller_id" value="{{ $seller->id }}">

                            {{-- Select all --}}
                            <div class="d-flex align-items-center gap-3 mb-4 p-3 bg-light rounded">
                                <div class="form-group d-flex gap-2 mb-0">
                                    <input type="checkbox" id="select-all-vendor-module"
                                        class="form-check-input checkbox--input checkbox--input_lg cursor-pointer"
                                        {{ $isFullAccess ? 'checked' : '' }}>
                                    <label class="form-check-label cursor-pointer fw-semibold text-capitalize"
                                        for="select-all-vendor-module">
                                        {{ translate('grant_full_access_(select_all)') }}
                                    </label>
                                </div>
                                <span class="text-muted fs-12">
                                    {{ translate('when_all_modules_are_selected,_this_equals_full_access') }}
                                </span>
                            </div>

                            <div class="row gy-3 mb-4">
                                @foreach ($vendorModulePermission as $moduleKey => $moduleLabel)
                                    <div class="col-sm-6 col-lg-4">
                                        <div class="form-group d-flex gap-2 align-items-start">
                                            <input type="checkbox" name="modules[]" value="{{ $moduleKey }}"
                                                class="form-check-input checkbox--input vendor-module-permission mt-1 cursor-pointer"
                                                id="{{ $moduleKey }}-perm"
                                                {{ $isFullAccess || in_array($moduleKey, $currentModules) ? 'checked' : '' }}>
                                            <label class="form-check-label cursor-pointer text-capitalize"
                                                style="{{ $direction === 'rtl' ? 'margin-right:1.25rem;' : '' }}"
                                                for="{{ $moduleKey }}-perm">
                                                {{ translate($moduleLabel) }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.vendor-permission.index') }}" class="btn btn-outline-secondary">
                                    {{ translate('cancel') }}
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    {{ translate('save_permissions') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('script')
    <script>
        "use strict";
        const selectAllCheckbox = document.getElementById('select-all-vendor-module');
        const moduleCheckboxes = document.querySelectorAll('.vendor-module-permission');

        selectAllCheckbox.addEventListener('change', function() {
            moduleCheckboxes.forEach(cb => {
                cb.checked = this.checked;
            });
        });

        moduleCheckboxes.forEach(cb => {
            cb.addEventListener('change', function() {
                const allChecked = [...moduleCheckboxes].every(c => c.checked);
                selectAllCheckbox.checked = allChecked;
            });
        });
    </script>
@endpush
