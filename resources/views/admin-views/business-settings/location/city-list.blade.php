@extends('layouts.admin.app')

@section('title', translate('location_Setup') . ' - ' . $country->name . ' ' . translate('cities'))

@section('content')
    <div class="content container-fluid">
        <div class="mb-3">
            <h2 class="h1 mb-0 d-flex align-items-center gap-2">
                <i class="fi fi-sr-marker"></i>
                {{ translate('location_Setup') }}
            </h2>
        </div>

        @include('admin-views.business-settings.business-setup-inline-menu')

        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="mt-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('admin.business-settings.location.index') }}">{{ translate('countries') }}</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">{{ $country->name }} — {{ translate('cities') }}</li>
            </ol>
        </nav>

        {{-- Add City Form --}}
        <div class="row mt-20">
            <div class="col-md-12">
                <div class="card mb-3">
                    <div class="card-body">
                        <form action="{{ route('admin.business-settings.location.add-city') }}" method="POST">
                            @csrf
                            <input type="hidden" name="country_id" value="{{ $country->id }}">
                            <div class="row align-items-end">
                                <div class="col-md-6">
                                    <label class="form-label">{{ translate('city_name') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control"
                                        placeholder="{{ translate('enter_city_name') }}" required>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary">
                                        {{ translate('add_city') }}
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- City List --}}
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body d-flex flex-column gap-20">
                        <div class="d-flex justify-content-between align-items-center gap-20 flex-wrap">
                            <h3 class="mb-0">
                                {{ translate('city_list') }} — {{ $country->name }}
                                <span class="badge text-dark bg-body-secondary fw-semibold rounded-50">
                                    {{ $cities->total() }}
                                </span>
                            </h3>
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
                                        <th class="text-center">{{ translate('status') }}</th>
                                        <th class="text-center">{{ translate('action') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($cities as $key => $city)
                                        <tr>
                                            <td>{{ $cities->firstItem() + $key }}</td>
                                            <td>{{ $city->name }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.business-settings.location.areas', $city->id) }}"
                                                    class="badge bg-info text-white">
                                                    {{ $city->areas_count }} {{ translate('areas') }}
                                                </a>
                                            </td>
                                            <td>
                                                <form action="{{ route('admin.business-settings.location.city-status') }}"
                                                    method="post" id="city-status-{{ $city->id }}-form"
                                                    class="no-reload-form">
                                                    @csrf
                                                    <input type="hidden" name="id" value="{{ $city->id }}">
                                                    <label class="switcher mx-auto" for="city-status-{{ $city->id }}">
                                                        <input class="switcher_input custom-modal-plugin" type="checkbox"
                                                            value="1" name="status"
                                                            id="city-status-{{ $city->id }}"
                                                            {{ $city->is_active ? 'checked' : '' }}
                                                            data-modal-type="input-change-form" data-reload="true"
                                                            data-modal-form="#city-status-{{ $city->id }}-form"
                                                            data-on-title="{{ translate('Want_to_Turn_ON') . ' ' . $city->name . ' ' . translate('status') }}"
                                                            data-off-title="{{ translate('Want_to_Turn_OFF') . ' ' . $city->name . ' ' . translate('status') }}"
                                                            data-on-message="<p>{{ translate('if_enabled_this_city_will_be_available_for_location_selection') }}</p>"
                                                            data-off-message="<p>{{ translate('if_disabled_this_city_will_be_hidden_from_location_selection') }}</p>"
                                                            data-on-button-text="{{ translate('turn_on') }}"
                                                            data-off-button-text="{{ translate('turn_off') }}">
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                </form>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-center gap-3">
                                                    <a href="{{ route('admin.business-settings.location.areas', $city->id) }}"
                                                        class="btn btn-outline-info icon-btn"
                                                        title="{{ translate('manage_areas') }}">
                                                        <i class="fi fi-sr-eye"></i>
                                                    </a>
                                                    <button class="btn btn-outline-primary icon-btn"
                                                        title="{{ translate('edit') }}" data-bs-toggle="modal"
                                                        data-bs-target="#editCityModal-{{ $city->id }}">
                                                        <i class="fi fi-sr-pencil"></i>
                                                    </button>
                                                    <form
                                                        action="{{ route('admin.business-settings.location.delete-city') }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input type="hidden" name="id" value="{{ $city->id }}">
                                                        <button type="submit" class="btn btn-outline-danger icon-btn"
                                                            title="{{ translate('delete') }}"
                                                            onclick="return confirm('{{ translate('are_you_sure_this_will_delete_all_areas_under_this_city') }}')">
                                                            <i class="fi fi-rr-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- Edit Modal --}}
                                        <div class="modal fade" id="editCityModal-{{ $city->id }}" tabindex="-1"
                                            aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form
                                                        action="{{ route('admin.business-settings.location.update-city', $city->id) }}"
                                                        method="POST">
                                                        @csrf
                                                        <div class="modal-header">
                                                            <h5 class="modal-title">{{ translate('edit_city') }}</h5>
                                                            <button type="button" class="btn-close"
                                                                data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="mb-3">
                                                                <label class="form-label">{{ translate('city_name') }}
                                                                    <span class="text-danger">*</span></label>
                                                                <input type="text" name="name" class="form-control"
                                                                    value="{{ $city->name }}" required>
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

                        <div class="table-responsive mt-4">
                            <div class="d-flex justify-content-lg-end">
                                {{ $cities->links() }}
                            </div>
                        </div>

                        @if (count($cities) == 0)
                            @include('layouts.admin.partials._empty-state', [
                                'text' => 'no_city_found',
                                'image' => 'default',
                            ])
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
