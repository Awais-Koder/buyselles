@extends('layouts.admin.app')

@section('title', translate('add_supplier'))

@section('content')
<div class="content container-fluid">
    <div class="card">
        <div class="card-body">
            <h3 class="mb-4">{{ translate('add_supplier') }}</h3>

            <form action="{{ route('admin.supplier.store') }}" method="post" id="supplier-form">
                @csrf

                <div class="row gy-3">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="form-label">{{ translate('name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control"
                                   placeholder="{{ translate('ex') }}: Reloadly Production"
                                   value="{{ old('name') }}" required>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="form-label">{{ translate('driver') }} <span class="text-danger">*</span></label>
                            <select name="driver" class="form-control" id="driver-select" required>
                                <option value="">{{ translate('select_driver') }}</option>
                                @foreach($drivers as $driver)
                                    <option value="{{ $driver }}" {{ old('driver') == $driver ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $driver)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="form-label">{{ translate('base_url') }} <span class="text-danger">*</span></label>
                            <input type="url" name="base_url" class="form-control"
                                   placeholder="https://api.example.com"
                                   value="{{ old('base_url') }}" required>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="form-label">{{ translate('auth_type') }} <span class="text-danger">*</span></label>
                            <select name="auth_type" class="form-control" required>
                                <option value="api_key" {{ old('auth_type') == 'api_key' ? 'selected' : '' }}>API Key</option>
                                <option value="bearer_token" {{ old('auth_type') == 'bearer_token' ? 'selected' : '' }}>Bearer Token</option>
                                <option value="oauth2" {{ old('auth_type') == 'oauth2' ? 'selected' : '' }}>OAuth2</option>
                                <option value="basic" {{ old('auth_type') == 'basic' ? 'selected' : '' }}>Basic Auth</option>
                                <option value="hmac" {{ old('auth_type') == 'hmac' ? 'selected' : '' }}>HMAC</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="form-label">{{ translate('rate_limit_per_minute') }} <span class="text-danger">*</span></label>
                            <input type="number" name="rate_limit_per_minute" class="form-control"
                                   value="{{ old('rate_limit_per_minute', 60) }}" min="1" max="1000" required>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="form-label">{{ translate('priority') }} <span class="text-danger">*</span></label>
                            <input type="number" name="priority" class="form-control"
                                   value="{{ old('priority', 0) }}" min="0" required>
                            <small class="text-muted">{{ translate('lower_number_=_higher_priority') }}</small>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="form-label">{{ translate('sandbox_mode') }}</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" name="is_sandbox" value="1"
                                       id="sandbox-toggle" {{ old('is_sandbox') ? 'checked' : '' }}>
                                <label class="form-check-label" for="sandbox-toggle">{{ translate('enable_sandbox') }}</label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Credentials Section --}}
                <hr class="my-4">
                <h5 class="mb-3"><i class="fi fi-rr-lock"></i> {{ translate('credentials') }}</h5>
                <div class="row gy-3" id="credentials-section">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="form-label">{{ translate('api_key') }}</label>
                            <input type="password" name="credentials[api_key]" class="form-control"
                                   placeholder="{{ translate('enter_api_key') }}"
                                   autocomplete="new-password">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="form-label">{{ translate('api_secret') }}</label>
                            <input type="password" name="credentials[api_secret]" class="form-control"
                                   placeholder="{{ translate('enter_api_secret') }}"
                                   autocomplete="new-password">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="form-label">{{ translate('client_id') }}</label>
                            <input type="text" name="credentials[client_id]" class="form-control"
                                   placeholder="{{ translate('enter_client_id') }}">
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="form-label">{{ translate('client_secret') }}</label>
                            <input type="password" name="credentials[client_secret]" class="form-control"
                                   placeholder="{{ translate('enter_client_secret') }}"
                                   autocomplete="new-password">
                        </div>
                    </div>
                </div>

                {{-- Settings Section --}}
                <hr class="my-4">
                <h5 class="mb-3"><i class="fi fi-rr-settings"></i> {{ translate('driver_settings') }}</h5>
                <div class="row gy-3" id="settings-section">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="form-label">{{ translate('webhook_secret') }}</label>
                            <input type="password" name="settings[webhook_secret]" class="form-control"
                                   placeholder="{{ translate('for_webhook_signature_verification') }}"
                                   autocomplete="new-password">
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-3 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fi fi-sr-check"></i> {{ translate('save') }}
                    </button>
                    <a href="{{ route('admin.supplier.list') }}" class="btn btn-secondary">
                        {{ translate('cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
