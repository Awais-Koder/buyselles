@extends('layouts.vendor.app')

@section('title', translate('manage_locations') . ' - ' . $country->name . ' ' . translate('cities'))

@section('content')
    <div class="content container-fluid">
        <h2 class="h1 mb-0 text-capitalize d-flex mb-3">
            {{ translate('shop_info') }}
        </h2>

        @include('vendor-views.shop.inline-menu')

        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="mt-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('vendor.shop.location.manage') }}">{{ translate('countries') }}</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">{{ $country->name }} — {{ translate('cities') }}</li>
            </ol>
        </nav>

        {{-- Request City Form --}}
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fi fi-rr-building"></i>
                            {{ translate('request_new_city') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-3">{{ translate('if_the_city_you_need_is_not_listed_below_you_can_request_admin_to_add_it') }}</p>
                        <form action="{{ route('vendor.shop.location.request-city') }}" method="POST">
                            @csrf
                            <input type="hidden" name="country_id" value="{{ $country->id }}">
                            <div class="row align-items-end">
                                <div class="col-md-6">
                                    <label class="form-label">{{ translate('city_name') }} <span class="text-danger">*</span></label>
                                    <input type="text" name="city_name" class="form-control"
                                        placeholder="{{ translate('enter_city_name') }}" required>
                                </div>
                                <div class="col-md-4 mt-2 mt-md-0">
                                    <button type="submit" class="btn btn--primary">
                                        <i class="fi fi-rr-paper-plane"></i> {{ translate('request_city') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- City List (read-only, admin-managed) --}}
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body d-flex flex-column gap-20">
                        <div class="d-flex justify-content-between align-items-center gap-20 flex-wrap">
                            <h5 class="mb-0">
                                {{ translate('city_list') }} — {{ $country->name }}
                                <span class="badge text-dark bg-body-secondary fw-semibold rounded-50">
                                    {{ $cities->total() }}
                                </span>
                            </h5>
                            <form action="{{ url()->current() }}" method="GET">
                                <div class="input-group flex-grow-1 max-w-280">
                                    <input type="search" name="searchValue" class="form-control"
                                        placeholder="{{ translate('search_by_name') }}" value="{{ $searchValue }}">
                                    <div class="input-group-append search-submit">
                                        <button type="submit"><i class="fi fi-rr-search"></i></button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-borderless align-middle">
                                <thead class="text-capitalize">
                                    <tr>
                                        <th>{{ translate('SL') }}</th>
                                        <th>{{ translate('city_name') }}</th>
                                        <th class="text-center">{{ translate('areas') }}</th>
                                        <th class="text-center">{{ translate('action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($cities as $key => $city)
                                        <tr>
                                            <td>{{ $cities->firstItem() + $key }}</td>
                                            <td>{{ $city->name }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('vendor.shop.location.manage-areas', $city->id) }}"
                                                    class="badge bg-info text-white">
                                                    {{ $city->areas_count }} {{ translate('areas') }}
                                                </a>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-center gap-3">
                                                    <a href="{{ route('vendor.shop.location.manage-areas', $city->id) }}"
                                                        class="btn btn-outline-info icon-btn"
                                                        title="{{ translate('manage_areas') }}">
                                                        <i class="fi fi-sr-eye"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="d-flex justify-content-lg-end">
                            {{ $cities->links() }}
                        </div>

                        @if ($cities->isEmpty())
                            @include('layouts.vendor.partials._empty-state', [
                                'text' => 'no_city_found',
                                'image' => 'default',
                            ])
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- My City Requests for this country --}}
        @if ($cityRequests->isNotEmpty())
            <div class="row mt-3">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body d-flex flex-column gap-20">
                            <h5 class="mb-0">
                                <i class="fi fi-rr-time-past"></i>
                                {{ translate('my_city_requests') }}
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-hover table-borderless align-middle">
                                    <thead class="text-capitalize">
                                        <tr>
                                            <th>{{ translate('SL') }}</th>
                                            <th>{{ translate('requested_city') }}</th>
                                            <th class="text-center">{{ translate('status') }}</th>
                                            <th>{{ translate('date') }}</th>
                                            <th>{{ translate('note') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($cityRequests as $key => $req)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $req->city_name }}</td>
                                                <td class="text-center">
                                                    @if ($req->status === 'pending')
                                                        <span class="badge bg-warning text-dark">{{ translate('pending') }}</span>
                                                    @elseif ($req->status === 'approved')
                                                        <span class="badge bg-success">{{ translate('approved') }}</span>
                                                    @else
                                                        <span class="badge bg-danger">{{ translate('rejected') }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $req->created_at->format('d M Y') }}</td>
                                                <td>{{ $req->admin_note ?? '-' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
