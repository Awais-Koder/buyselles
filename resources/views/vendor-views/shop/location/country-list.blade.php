@extends('layouts.vendor.app')

@section('title', translate('manage_locations') . ' - ' . translate('countries'))

@section('content')
    <div class="content container-fluid">
        <h2 class="h1 mb-0 text-capitalize d-flex mb-3">
            {{ translate('shop_info') }}
        </h2>

        @include('vendor-views.shop.inline-menu')

        {{-- Add Country --}}
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fi fi-rr-marker"></i>
                            {{ translate('add_country') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('vendor.shop.location.add-country') }}" method="POST">
                            @csrf
                            <div class="row align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label">{{ translate('country_name') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control"
                                        placeholder="{{ translate('enter_country_name') }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label">{{ translate('country_code') }}</label>
                                    <input type="text" name="code" class="form-control"
                                        placeholder="{{ translate('e.g._SA') }}" maxlength="10">
                                </div>
                                <div class="col-md-4 mt-2 mt-md-0">
                                    <button type="submit" class="btn btn-primary">
                                        {{ translate('add_country') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Country List --}}
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body d-flex flex-column gap-20">
                        <div class="d-flex justify-content-between align-items-center gap-20 flex-wrap">
                            <h5 class="mb-0">
                                {{ translate('country_list') }}
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
                                                    <button class="btn btn-outline-primary icon-btn"
                                                        title="{{ translate('edit') }}" data-bs-toggle="modal"
                                                        data-bs-target="#editCountryModal-{{ $country->id }}">
                                                        <i class="fi fi-sr-pencil"></i>
                                                    </button>
                                                    <form action="{{ route('vendor.shop.location.delete-country') }}"
                                                        method="POST" class="d-inline delete-country-form">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input type="hidden" name="id" value="{{ $country->id }}">
                                                        <button type="button"
                                                            class="btn btn-outline-danger icon-btn btn-delete-country"
                                                            title="{{ translate('delete') }}">
                                                            <i class="fi fi-rr-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- Edit Country Modal --}}
                                        <div class="modal fade" id="editCountryModal-{{ $country->id }}" tabindex="-1"
                                            aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form
                                                        action="{{ route('vendor.shop.location.update-country', $country->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">{{ translate('edit_country') }}</h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">{{ translate('country_name') }}
                                                                    <span class="text-danger">*</span></label>
                                                                <input type="text" name="name" class="form-control"
                                                                    value="{{ $country->name }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label
                                                                    class="form-label">{{ translate('country_code') }}</label>
                                                                <input type="text" name="code" class="form-control"
                                                                    value="{{ $country->code }}" maxlength="10">
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                data-bs-dismiss="modal">{{ translate('close') }}</button>
                                                            <button type="submit"
                                                                class="btn btn-primary">{{ translate('update') }}</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
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
    </div>

    <span id="get-sweet-alert-messages" data-are-you-sure="{{ translate('are_you_sure') }}"
        data-cancel="{{ translate('cancel') }}" data-confirm="{{ translate('yes_delete') }}"
        data-delete-warning="{{ translate('are_you_sure_this_will_delete_all_cities_and_areas_under_this_country') }}">
    </span>
@endsection

@push('script')
    <script>
        $(document).on('click', '.btn-delete-country', function() {
            const btn = $(this);
            const form = btn.closest('form');
            const getText = document.getElementById('get-sweet-alert-messages');
            Swal.fire({
                title: getText?.dataset.areYouSure || 'Are you sure?',
                text: getText?.dataset.deleteWarning || 'This will delete all cities and areas.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                cancelButtonText: getText?.dataset.cancel || 'Cancel',
                confirmButtonText: getText?.dataset.confirm || 'Yes, delete it!',
                reverseButtons: true,
            }).then((result) => {
                if (result.isConfirmed) {
                    form[0].submit();
                }
            });
        });
    </script>
@endpush
