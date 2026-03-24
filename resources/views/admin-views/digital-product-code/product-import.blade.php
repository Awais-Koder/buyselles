@extends('layouts.admin.app')

@section('title', translate('Upload_Codes') . ' — ' . $product->name)

@section('content')
    <div class="content container-fluid">

        {{-- Page Header --}}
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{ dynamicAsset(path: 'public/assets/new/back-end/img/inhouse-product-list.png') }}" alt="">
                {{ translate('Upload Digital Codes') }}
            </h2>
            <nav aria-label="breadcrumb" class="mt-1">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.dashboard.index') }}">{{ translate('dashboard') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.products.list', 'in-house') }}">{{ translate('products') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.products.digital-code-import.product-codes', $product->id) }}">
                            {{ $product->name }}
                        </a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">{{ translate('Upload Codes') }}</li>
                </ol>
            </nav>
        </div>

        <div class="row g-3">

            {{-- Step 1 — Download template --}}
            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                            <span class="badge bg-primary rounded-circle"
                                style="width:28px;height:28px;line-height:28px;">1</span>
                            {{ translate('Download Template') }}
                        </h5>
                    </div>
                    <div class="card-body d-flex flex-column gap-3">
                        <p class="text-muted mb-0">
                            {{ translate('Download the pre-filled template for') }}
                            <strong>{{ $product->name }}</strong>.
                            {{ translate('Add one code per row and upload it back.') }}
                        </p>

                        <div class="table-responsive">
                            <table class="table table-sm table-bordered small mb-0">
                                <thead style="background:#063C93;color:#fff;text-align:center;">
                                    <tr>
                                        <th>Column</th>
                                        <th>Required?</th>
                                        <th>Example</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><code>pin</code></td>
                                        <td><span class="badge bg-danger">Required</span></td>
                                        <td class="text-warning fw-semibold">ABCD-1234-EFGH</td>
                                    </tr>
                                    <tr>
                                        <td><code>serial_number</code></td>
                                        <td><span class="badge bg-secondary">Optional</span></td>
                                        <td class="text-muted">SN-00123</td>
                                    </tr>
                                    <tr>
                                        <td><code>expiry_date</code></td>
                                        <td><span class="badge bg-secondary">Optional</span></td>
                                        <td class="text-muted">2026-12-31</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <ul class="list-unstyled mb-0 text-muted small">
                            <li>
                                {{ translate('expiry_date format: YYYY-MM-DD (e.g. 2026-12-31)') }}</li>
                            <li>
                                {{ translate('Leave expiry_date blank for codes that never expire') }}</li>
                            <li>
                                {{ translate('Duplicate PINs and serial numbers are rejected automatically') }}</li>
                            <li>
                                {{ translate('Codes past expiry are removed from stock nightly at 03:00') }}</li>
                            <li>
                                {{ translate('All PINs are encrypted with AES-256 before being saved') }}</li>
                        </ul>

                        <div class="mt-auto">
                            <a href="{{ route('admin.products.digital-code-import.product-template', $product->id) }}"
                                class="btn btn-outline-primary w-100">
                                <i class="fi fi-rr-download me-2"></i>
                                {{ translate('Download Excel Template') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step 2 — Upload --}}
            <div class="col-lg-7">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                            <span class="badge bg-success rounded-circle"
                                style="width:28px;height:28px;line-height:28px;">2</span>
                            {{ translate('Upload Filled File') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <form
                            action="{{ route('admin.products.digital-code-import.product-import-upload', $product->id) }}"
                            method="POST" enctype="multipart/form-data" id="importForm">
                            @csrf

                            <div class="mb-3">
                                <label for="excel_file" class="form-label fw-semibold">
                                    {{ translate('Select Excel / CSV File') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="file" class="form-control @error('excel_file') is-invalid @enderror"
                                    id="excel_file" name="excel_file" accept=".xlsx,.xls,.csv" required>
                                @error('excel_file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text text-muted">
                                    {{ translate('Accepted: xlsx, xls, csv. Max size: 10MB') }}
                                </div>
                            </div>

                            <div class="alert alert-info py-2 small mb-3">
                                <i class="fi fi-sr-info me-1"></i>
                                {{ translate('The file is processed immediately. Results appear on the next page.') }}
                            </div>

                            <div class="alert alert-warning py-2 small mb-3">
                                <i class="fi fi-sr-triangle-warning me-1"></i>
                                {{ translate('Codes are added to the pool. Existing codes are NOT removed.') }}
                            </div>

                            <button type="submit" class="btn btn-success w-100" id="submitBtn">
                                <span class="normal-state">
                                    <i class="fi fi-sr-inbox-in me-2"></i>
                                    {{ translate('Upload & Import Now') }}
                                </span>
                                <span class="loading-state d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status"
                                        aria-hidden="true"></span>
                                    {{ translate('Processing...') }}
                                </span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@push('script')
    <script>
        document.getElementById('importForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            btn.disabled = true;
            btn.querySelector('.normal-state').classList.add('d-none');
            btn.querySelector('.loading-state').classList.remove('d-none');
        });
    </script>
@endpush
