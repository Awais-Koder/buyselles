@extends('layouts.admin.app')

@section('title', translate('location_Setup') . ' - ' . translate('city_requests'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <i class="fi fi-sr-marker"></i>
                {{ translate('location_Setup') }}
            </h2>
        </div>

        @include('admin-views.business-settings.business-setup-inline-menu')

        {{-- City Requests --}}
        <div class="row mt-20">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body d-flex flex-column gap-20">
                        <div class="d-flex justify-content-between align-items-center gap-20 flex-wrap">
                            <h3 class="mb-0">
                                {{ translate('vendor_city_requests') }}
                                <span class="badge text-dark bg-body-secondary fw-semibold rounded-50">
                                    {{ $cityRequests->total() }}
                                </span>
                            </h3>
                            <div class="d-flex gap-2 flex-wrap">
                                {{-- Status Filter --}}
                                <form action="{{ url()->current() }}" method="GET" class="d-flex gap-2">
                                    <select name="status" class="form-control form-control-sm max-w-200" onchange="this.form.submit()">
                                        <option value="">{{ translate('all_statuses') }}</option>
                                        <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>{{ translate('pending') }}</option>
                                        <option value="approved" {{ $status === 'approved' ? 'selected' : '' }}>{{ translate('approved') }}</option>
                                        <option value="rejected" {{ $status === 'rejected' ? 'selected' : '' }}>{{ translate('rejected') }}</option>
                                    </select>
                                    <div class="input-group flex-grow-1 max-w-280">
                                        <input type="search" name="searchValue" class="form-control"
                                            placeholder="{{ translate('search_by_city_name') }}" value="{{ $searchValue }}">
                                        <div class="input-group-append search-submit">
                                            <button type="submit"><i class="fi fi-rr-search"></i></button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-borderless align-middle">
                                <thead class="text-capitalize">
                                    <tr>
                                        <th>{{ translate('SL') }}</th>
                                        <th>{{ translate('vendor') }}</th>
                                        <th>{{ translate('country') }}</th>
                                        <th>{{ translate('requested_city') }}</th>
                                        <th class="text-center">{{ translate('status') }}</th>
                                        <th>{{ translate('date') }}</th>
                                        <th class="text-center">{{ translate('action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($cityRequests as $key => $request)
                                        <tr>
                                            <td>{{ $cityRequests->firstItem() + $key }}</td>
                                            <td>
                                                @if ($request->seller)
                                                    {{ $request->seller->f_name }} {{ $request->seller->l_name }}
                                                @else
                                                    <span class="text-muted">{{ translate('deleted_vendor') }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $request->country?->name ?? '-' }}</td>
                                            <td>{{ $request->city_name }}</td>
                                            <td class="text-center">
                                                @if ($request->status === 'pending')
                                                    <span class="badge bg-warning text-dark">{{ translate('pending') }}</span>
                                                @elseif ($request->status === 'approved')
                                                    <span class="badge bg-success">{{ translate('approved') }}</span>
                                                @else
                                                    <span class="badge bg-danger">{{ translate('rejected') }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $request->created_at->format('d M Y, h:i A') }}</td>
                                            <td>
                                                <div class="d-flex justify-content-center gap-2">
                                                    @if ($request->status === 'pending')
                                                        <form action="{{ route('admin.business-settings.location.approve-city-request', $request->id) }}"
                                                            method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-success"
                                                                title="{{ translate('approve') }}">
                                                                <i class="fi fi-sr-check"></i> {{ translate('approve') }}
                                                            </button>
                                                        </form>
                                                        <button class="btn btn-sm btn-outline-danger"
                                                            title="{{ translate('reject') }}"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#rejectModal-{{ $request->id }}">
                                                            <i class="fi fi-sr-cross"></i> {{ translate('reject') }}
                                                        </button>
                                                    @elseif ($request->status === 'approved')
                                                        <span class="text-success">
                                                            <i class="fi fi-sr-check"></i>
                                                            {{ $request->approvedCity?->name ?? translate('city_created') }}
                                                        </span>
                                                    @else
                                                        <span class="text-muted" title="{{ $request->admin_note }}">
                                                            {{ Str::limit($request->admin_note, 40) ?: translate('no_note') }}
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- Reject Modal --}}
                                        @if ($request->status === 'pending')
                                            <div class="modal fade" id="rejectModal-{{ $request->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <form action="{{ route('admin.business-settings.location.reject-city-request', $request->id) }}" method="POST">
                                                            @csrf
                                                            <div class="modal-header">
                                                                <h5 class="modal-title">{{ translate('reject_city_request') }}</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <p>
                                                                    {{ translate('vendor') }}: <strong>{{ $request->seller?->f_name }} {{ $request->seller?->l_name }}</strong><br>
                                                                    {{ translate('country') }}: <strong>{{ $request->country?->name }}</strong><br>
                                                                    {{ translate('requested_city') }}: <strong>{{ $request->city_name }}</strong>
                                                                </p>
                                                                <div class="mb-3">
                                                                    <label class="form-label">{{ translate('rejection_note') }}</label>
                                                                    <textarea name="admin_note" class="form-control" rows="3"
                                                                        placeholder="{{ translate('optional_reason_for_rejection') }}"></textarea>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ translate('close') }}</button>
                                                                <button type="submit" class="btn btn-danger">{{ translate('reject') }}</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="table-responsive mt-4">
                            <div class="d-flex justify-content-lg-end">
                                {{ $cityRequests->appends(request()->query())->links() }}
                            </div>
                        </div>

                        @if (count($cityRequests) == 0)
                            @include('layouts.admin.partials._empty-state', [
                                'text' => 'no_city_requests_found',
                                'image' => 'default',
                            ])
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
