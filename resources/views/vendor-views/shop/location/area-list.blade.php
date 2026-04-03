@extends('layouts.vendor.app')

@section('title', translate('manage_locations') . ' - ' . $city->name . ' ' . translate('areas'))

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
                <li class="breadcrumb-item">
                    <a
                        href="{{ route('vendor.shop.location.manage-cities', $city->country_id) }}">{{ $city->country->name }}</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">{{ $city->name }} — {{ translate('areas') }}</li>
            </ol>
        </nav>

        {{-- Add Area --}}
        <div class="row mt-3">
            <div class="col-md-12">
                <div class="card mb-3">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fi fi-rr-marker"></i>
                            {{ translate('add_area') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('vendor.shop.location.add-area') }}" method="POST">
                            @csrf
                            <input type="hidden" name="city_id" value="{{ $city->id }}">
                            <div class="row align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label">{{ translate('area_name') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control"
                                        placeholder="{{ translate('enter_area_name') }}" required>
                                </div>
                                <div class="col-md-3 d-flex align-items-end pb-1">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="cod_available"
                                            id="cod_available" value="1" checked>
                                        <label class="form-check-label" for="cod_available">
                                            {{ translate('COD_available') }}
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4 mt-2 mt-md-0">
                                    <button type="submit" class="btn btn-primary">
                                        {{ translate('add_area') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- Area List --}}
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body d-flex flex-column gap-20">
                        <div class="d-flex justify-content-between align-items-center gap-20 flex-wrap">
                            <h5 class="mb-0">
                                {{ translate('area_list') }} — {{ $city->name }}
                                <span class="badge text-dark bg-body-secondary fw-semibold rounded-50">
                                    {{ $areas->total() }}
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
                                        <th>{{ translate('area_name') }}</th>
                                        <th class="text-center">{{ translate('COD') }}</th>
                                        <th class="text-center">{{ translate('action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($areas as $key => $area)
                                        <tr>
                                            <td>{{ $areas->firstItem() + $key }}</td>
                                            <td>{{ $area->name }}</td>
                                            <td class="text-center">
                                                @if ($area->cod_available)
                                                    <span class="badge bg-success">{{ translate('yes') }}</span>
                                                @else
                                                    <span class="badge bg-secondary">{{ translate('no') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-center gap-3">
                                                    <button class="btn btn-outline-primary icon-btn"
                                                        title="{{ translate('edit') }}" data-bs-toggle="modal"
                                                        data-bs-target="#editAreaModal-{{ $area->id }}">
                                                        <i class="fi fi-sr-pencil"></i>
                                                    </button>
                                                    <form action="{{ route('vendor.shop.location.delete-area') }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input type="hidden" name="id" value="{{ $area->id }}">
                                                        <button type="button"
                                                            class="btn btn-outline-danger icon-btn btn-delete-area"
                                                            title="{{ translate('delete') }}">
                                                            <i class="fi fi-rr-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- Edit Area Modal --}}
                                        <div class="modal fade" id="editAreaModal-{{ $area->id }}" tabindex="-1"
                                            aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form
                                                        action="{{ route('vendor.shop.location.update-area', $area->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">{{ translate('edit_area') }}</h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">{{ translate('area_name') }}
                                                                    <span class="text-danger">*</span></label>
                                                                <input type="text" name="name" class="form-control"
                                                                    value="{{ $area->name }}" required>
                                                            </div>
                                                            <div class="mb-3">
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        name="cod_available"
                                                                        id="cod_available_edit_{{ $area->id }}"
                                                                        value="1"
                                                                        {{ $area->cod_available ? 'checked' : '' }}>
                                                                    <label class="form-check-label"
                                                                        for="cod_available_edit_{{ $area->id }}">
                                                                        {{ translate('COD_available') }}
                                                                    </label>
                                                                </div>
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
                            {{ $areas->links() }}
                        </div>

                        @if ($areas->isEmpty())
                            @include('layouts.vendor.partials._empty-state', [
                                'text' => 'no_area_found',
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
        data-delete-warning="{{ translate('are_you_sure_you_want_to_delete_this_area') }}">
    </span>
@endsection

@push('script')
    <script>
        $(document).on('click', '.btn-delete-area', function() {
            const btn = $(this);
            const form = btn.closest('form');
            const getText = document.getElementById('get-sweet-alert-messages');
            Swal.fire({
                title: getText?.dataset.areYouSure || 'Are you sure?',
                text: getText?.dataset.deleteWarning || 'This action cannot be undone.',
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
