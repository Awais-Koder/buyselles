@extends('layouts.admin.app')

@section('title', translate('location_Setup') . ' - ' . $city->country->name . ' > ' . $city->name . ' ' .
    translate('areas'))

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
                <li class="breadcrumb-item">
                    <a
                        href="{{ route('admin.business-settings.location.cities', $city->country->id) }}">{{ $city->country->name }}</a>
                </li>
                <li class="breadcrumb-item active" aria-current="page">{{ $city->name }} — {{ translate('areas') }}</li>
            </ol>
        </nav>

        {{-- Add Area Form --}}
        <div class="row mt-20">
            <div class="col-md-12">
                <div class="card mb-3">
                    <div class="card-body">
                        <form action="{{ route('admin.business-settings.location.add-area') }}" method="POST">
                            @csrf
                            <input type="hidden" name="city_id" value="{{ $city->id }}">
                            <div class="row align-items-end">
                                <div class="col-md-5">
                                    <label class="form-label">{{ translate('area_name') }} <span
                                            class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control"
                                        placeholder="{{ translate('enter_area_name') }}" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label d-block">{{ translate('COD_available') }}</label>
                                    <label class="switcher" for="new-area-cod">
                                        <input class="switcher_input" type="checkbox" name="cod_available" value="1"
                                            id="new-area-cod" checked>
                                        <span class="switcher_control"></span>
                                    </label>
                                </div>
                                <div class="col-md-4">
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
                            <h3 class="mb-0">
                                {{ translate('area_list') }} — {{ $city->country->name }} > {{ $city->name }}
                                <span class="badge text-dark bg-body-secondary fw-semibold rounded-50">
                                    {{ $areas->total() }}
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
                                        <th>{{ translate('area_name') }}</th>
                                        <th class="text-center">{{ translate('COD') }}</th>
                                        <th class="text-center">{{ translate('status') }}</th>
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
                                                    <span class="badge bg-danger">{{ translate('no') }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <form action="{{ route('admin.business-settings.location.area-status') }}"
                                                    method="post" id="area-status-{{ $area->id }}-form"
                                                    class="no-reload-form">
                                                    @csrf
                                                    <input type="hidden" name="id" value="{{ $area->id }}">
                                                    <label class="switcher mx-auto" for="area-status-{{ $area->id }}">
                                                        <input class="switcher_input custom-modal-plugin" type="checkbox"
                                                            value="1" name="status"
                                                            id="area-status-{{ $area->id }}"
                                                            {{ $area->is_active ? 'checked' : '' }}
                                                            data-modal-type="input-change-form" data-reload="true"
                                                            data-modal-form="#area-status-{{ $area->id }}-form"
                                                            data-on-title="{{ translate('Want_to_Turn_ON') . ' ' . $area->name . ' ' . translate('status') }}"
                                                            data-off-title="{{ translate('Want_to_Turn_OFF') . ' ' . $area->name . ' ' . translate('status') }}"
                                                            data-on-message="<p>{{ translate('if_enabled_this_area_will_be_available_for_location_selection') }}</p>"
                                                            data-off-message="<p>{{ translate('if_disabled_this_area_will_be_hidden_from_location_selection') }}</p>"
                                                            data-on-button-text="{{ translate('turn_on') }}"
                                                            data-off-button-text="{{ translate('turn_off') }}">
                                                        <span class="switcher_control"></span>
                                                    </label>
                                                </form>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-center gap-3">
                                                    <button class="btn btn-outline-primary icon-btn"
                                                        title="{{ translate('edit') }}" data-bs-toggle="modal"
                                                        data-bs-target="#editAreaModal-{{ $area->id }}">
                                                        <i class="fi fi-sr-pencil"></i>
                                                    </button>
                                                    <form
                                                        action="{{ route('admin.business-settings.location.delete-area') }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <input type="hidden" name="id"
                                                            value="{{ $area->id }}">
                                                        <button type="submit" class="btn btn-outline-danger icon-btn"
                                                            title="{{ translate('delete') }}"
                                                            onclick="return confirm('{{ translate('are_you_sure') }}')">
                                                            <i class="fi fi-rr-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>

                                        {{-- Edit Modal --}}
                                        <div class="modal fade" id="editAreaModal-{{ $area->id }}" tabindex="-1"
                                            aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <form
                                                        action="{{ route('admin.business-settings.location.update-area', $area->id) }}"
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
                                                                <label
                                                                    class="form-label d-block">{{ translate('COD_available') }}</label>
                                                                <label class="switcher"
                                                                    for="edit-area-cod-{{ $area->id }}">
                                                                    <input class="switcher_input" type="checkbox"
                                                                        name="cod_available" value="1"
                                                                        id="edit-area-cod-{{ $area->id }}"
                                                                        {{ $area->cod_available ? 'checked' : '' }}>
                                                                    <span class="switcher_control"></span>
                                                                </label>
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
                                {{ $areas->links() }}
                            </div>
                        </div>

                        @if (count($areas) == 0)
                            @include('layouts.admin.partials._empty-state', [
                                'text' => 'no_area_found',
                                'image' => 'default',
                            ])
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
