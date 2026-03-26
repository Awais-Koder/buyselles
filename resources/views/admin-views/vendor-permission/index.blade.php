@extends('layouts.admin.app')
@section('title', translate('vendor_permissions'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2 text-capitalize">
                <img src="{{ dynamicAsset(path: 'public/assets/back-end/img/add-new-seller.png') }}" width="28"
                    alt="">
                {{ translate('vendor_permissions') }}
            </h2>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="row justify-content-between align-items-center flex-grow-1 mb-4">
                    <div class="col-md-4 col-lg-6 mb-2 mb-sm-0">
                        <h5 class="text-capitalize">
                            {{ translate('all_vendors') }}
                            <span class="badge badge-info text-bg-info">{{ $vendors->total() }}</span>
                        </h5>
                        <p class="text-muted fs-12 mb-0">
                            {{ translate('click_manage_to_customize_module_access_for_each_vendor') }}
                        </p>
                    </div>
                    <div class="col-md-8 col-lg-6 d-flex justify-content-sm-end">
                        <form action="{{ route('admin.vendor-permission.index') }}" method="GET">
                            <div class="input-group">
                                <input type="search" name="search" class="form-control"
                                    placeholder="{{ translate('search_vendor') }}" value="{{ request('search') }}">
                                <div class="input-group-append search-submit">
                                    <button type="submit"><i class="fi fi-rr-search"></i></button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-borderless table-thead-bordered align-middle card-table">
                        <thead class="thead-light thead-50 text-capitalize table-nowrap">
                            <tr>
                                <th>{{ translate('SL') }}</th>
                                <th>{{ translate('vendor') }}</th>
                                <th>{{ translate('shop') }}</th>
                                <th>{{ translate('email') }}</th>
                                <th>{{ translate('permissions') }}</th>
                                <th class="text-center">{{ translate('action') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($vendors as $key => $vendor)
                                <tr>
                                    <td>{{ $vendors->firstItem() + $key }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if ($vendor->image)
                                                <img class="rounded-circle avatar-sm"
                                                    src="{{ getStorageImages(path: $vendor->image_full_url, type: 'backend-logo') }}"
                                                    alt="{{ $vendor->f_name }}">
                                            @else
                                                <div
                                                    class="rounded-circle avatar-sm bg-primary d-flex align-items-center justify-content-center text-white fw-bold">
                                                    {{ strtoupper(substr($vendor->f_name ?? 'V', 0, 1)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <span class="fw-semibold">{{ $vendor->f_name }}
                                                    {{ $vendor->l_name }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $vendor->shop?->name ?? translate('N/A') }}</td>
                                    <td>{{ $vendor->email }}</td>
                                    <td>
                                        @if (
                                            $vendor->vendorPermission &&
                                                $vendor->vendorPermission->module_access &&
                                                count($vendor->vendorPermission->module_access) > 0)
                                            <span class="badge text-bg-warning text-capitalize">
                                                {{ count($vendor->vendorPermission->module_access) }}
                                                {{ translate('modules_enabled') }}
                                            </span>
                                        @else
                                            <span class="badge text-bg-success">{{ translate('full_access') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('admin.vendor-permission.edit', $vendor->id) }}"
                                            class="btn btn-outline-primary btn-sm text-capitalize">
                                            <i class="fi fi-rr-lock me-1"></i>
                                            {{ translate('manage') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach

                            @if (count($vendors) === 0)
                                <tr>
                                    <td colspan="6">
                                        @include('layouts.admin.partials._empty-state', [
                                            'text' => 'no_data_found',
                                        ])
                                    </td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    {{ $vendors->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
