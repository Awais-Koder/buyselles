@extends('layouts.vendor.app')

@section('title', translate('manage_locations') . ' - ' . translate('countries'))

@section('content')
    <div class="content container-fluid">
        <h2 class="h1 mb-0 text-capitalize d-flex mb-3">
            {{ translate('shop_info') }}
        </h2>

        @include('vendor-views.shop.inline-menu')

        {{-- Country List (read-only, admin-managed) --}}
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body d-flex flex-column gap-20">
                        <div class="d-flex justify-content-between align-items-center gap-20 flex-wrap">
                            <h5 class="mb-0">
                                <i class="fi fi-rr-marker"></i>
                                {{ translate('countries') }}
                                <span class="badge text-dark bg-body-secondary fw-semibold rounded-50">
                                    {{ $countries->total() }}
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
                                        <th>{{ translate('country_name') }}</th>
                                        <th>{{ translate('code') }}</th>
                                        <th class="text-center">{{ translate('cities') }}</th>
                                        <th class="text-center">{{ translate('action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($countries as $key => $country)
                                        <tr>
                                            <td>{{ $countries->firstItem() + $key }}</td>
                                            <td>{{ $country->name }}</td>
                                            <td>{{ $country->code ?? '-' }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('vendor.shop.location.manage-cities', $country->id) }}"
                                                    class="badge bg-info text-white">
                                                    {{ $country->cities_count }} {{ translate('cities') }}
                                                </a>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-center gap-3">
                                                    <a href="{{ route('vendor.shop.location.manage-cities', $country->id) }}"
                                                        class="btn btn-outline-info icon-btn"
                                                        title="{{ translate('manage_cities') }}">
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
                            {{ $countries->links() }}
                        </div>

                        @if ($countries->isEmpty())
                            @include('layouts.vendor.partials._empty-state', [
                                'text' => 'no_country_found',
                                'image' => 'default',
                            ])
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- My City Requests --}}
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
                                            <th>{{ translate('country') }}</th>
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
                                                <td>{{ $req->country?->name ?? '-' }}</td>
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
