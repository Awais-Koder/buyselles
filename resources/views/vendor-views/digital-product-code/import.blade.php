@extends('layouts.vendor.app')

@section('title', translate('Digital_Code_Bulk_Import'))

@section('content')
    <div class="content container-fluid">

        {{-- Page Header --}}
        <div class="mb-3">
            <h2 class="h1 mb-0 text-capitalize d-flex align-items-center gap-2">
                <img src="{{ dynamicAsset(path: 'public/assets/new/back-end/img/inhouse-product-list.png') }}" alt="">
                {{ translate('Digital_Code_Bulk_Import') }}
            </h2>
            <nav aria-label="breadcrumb" class="mt-1">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('vendor.dashboard.index') }}">{{ translate('dashboard') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('vendor.products.list', 'all') }}">{{ translate('products') }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">
                        {{ translate('Digital_Code_Import') }}
                    </li>
                </ol>
            </nav>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row g-3">

            {{-- Step 1 — Download Template --}}
            <div class="col-lg-5">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                            <span class="badge bg-primary rounded-circle"
                                style="width:28px;height:28px;line-height:28px;">1</span>
                            {{ translate('Download_Template') }}
                        </h5>
                    </div>
                    <div class="card-body d-flex flex-column gap-3">
                        <p class="text-muted mb-0">
                            {{ translate('Download_the_Excel_template._Add_one_digital_code_(PIN)_per_row._You_can_restock_existing_products_or_create_new_ones_directly_in_the_file.') }}
                        </p>
                        <ul class="list-unstyled mb-0 text-muted small">
                            <li>
                                {{ translate('Existing_products:_fill_in_product_name_+_pin._product_id_also_accepted.') }}
                            </li>
                            <li>
                                {{ translate('New_products:_fill_product_name_+_price_+_category_id_+_pin') }}</li>
                            <li>
                                {{ translate('serial_number_and_expiry_date_are_optional') }}</li>
                            <li>
                                {{ translate('Codes_past_their_expiry_date_will_be_removed_from_stock_automatically') }}
                            </li>
                            <li>
                                {{ translate('Rows_with_empty_pin_are_skipped') }}</li>
                        </ul>
                        <div class="mt-auto">
                            <a href="{{ route('vendor.products.digital-code-import.template') }}"
                                class="btn btn-outline-primary w-100">
                                <i class="bi bi-file-earmark-arrow-down me-2"></i>
                                {{ translate('Download_Excel_Template') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step 2 — Upload Filled File --}}
            <div class="col-lg-7">
                <div class="card h-100">
                    <div class="card-header">
                        <h5 class="card-title mb-0 d-flex align-items-center gap-2">
                            <span class="badge bg-success rounded-circle"
                                style="width:28px;height:28px;line-height:28px;">2</span>
                            {{ translate('Upload_Filled_Excel_File') }}
                        </h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('vendor.products.digital-code-import.upload') }}" method="POST"
                            enctype="multipart/form-data" id="importForm">
                            @csrf

                            <div class="mb-3">
                                <label for="excel_file" class="form-label fw-semibold">
                                    {{ translate('Select_Excel_File') }}
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="file" class="form-control @error('excel_file') is-invalid @enderror"
                                    id="excel_file" name="excel_file" accept=".xlsx,.xls,.csv" required>
                                @error('excel_file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text text-muted">
                                    {{ translate('Accepted:_xlsx,_xls,_csv._Max_size:_10MB') }}
                                </div>
                            </div>

                            <div class="alert alert-info py-2 small mb-3">
                                <i class="bi bi-info-circle-fill me-1"></i>
                                {{ translate('The_import_runs_in_the_background._You_will_receive_an_email_when_the_job_is_complete.') }}
                            </div>

                            <div class="alert alert-warning py-2 small mb-3">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                {{ translate('Each_code_you_upload_is_added_to_the_pool._Existing_codes_are_not_removed.') }}
                            </div>

                            <button type="submit" class="btn btn-success w-100" id="submitBtn">
                                <span class="normal-state">
                                    <i class="bi bi-upload me-2"></i>
                                    {{ translate('Upload_&_Start_Import') }}
                                </span>
                                <span class="loading-state d-none">
                                    <span class="spinner-border spinner-border-sm me-2" role="status"
                                        aria-hidden="true"></span>
                                    {{ translate('Uploading...') }}
                                </span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Format Reference --}}
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="bi bi-table me-2"></i>
                            {{ translate('Expected_File_Format') }}
                        </h5>
                    </div>
                    <div class="card-body p-0">
                        {{-- Column legend --}}
                        <div class="px-3 pt-3 pb-1">
                            <div class="row g-2 small">
                                <div class="col-md-6">
                                    <div class="alert alert-secondary py-2 mb-0">
                                        <strong>{{ translate('Restocking_an_existing_product') }}</strong><br>
                                        {{ translate('Fill:') }} <code>product_name</code> + <code>pin</code><br>
                                        <span
                                            class="text-muted">({{ translate('product_id_can_be_used_instead_of_product_name') }})</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="alert alert-info py-2 mb-0">
                                        <strong>{{ translate('Creating_a_new_product') }}</strong><br>
                                        {{ translate('Fill:') }} <code>product_name</code> + <code>price</code> +
                                        <code>category_id</code> + <code>pin</code>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0 text-center">
                                <thead style="background-color: var(--bs-primary); color: var(--bs-white);">
                                    <tr>
                                        <th>product_id</th>
                                        <th>product_name</th>
                                        <th style="background:#1a4da8;">price</th>
                                        <th style="background:#1a4da8;">category_id</th>
                                        <th style="color: var(--bs-warning);">pin</th>
                                        <th>serial_number</th>
                                        <th>expiry_date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-muted">101</td>
                                        <td class="text-muted text-start">PUBG Mobile 60 UC</td>
                                        <td class="text-muted"><em>leave blank</em></td>
                                        <td class="text-muted"><em>leave blank</em></td>
                                        <td class="text-warning fw-semibold">ABCD-1234-EFGH</td>
                                        <td class="text-muted">SN-001</td>
                                        <td class="text-muted">2025-12-31</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted"><em>leave blank</em></td>
                                        <td class="text-muted text-start">Steam Wallet $10</td>
                                        <td class="text-primary fw-semibold">10.00</td>
                                        <td class="text-primary fw-semibold">25</td>
                                        <td class="text-warning fw-semibold">STEAM-XXXX-9999</td>
                                        <td class="text-muted"></td>
                                        <td class="text-muted"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="px-3 py-2 small text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            <strong>expiry_date</strong> {{ translate('format:') }} <code>YYYY-MM-DD</code>
                            &mdash;
                            {{ translate('Codes_past_this_date_are_automatically_removed_from_stock_each_night.') }}
                            {{ translate('Leave_blank_for_codes_that_do_not_expire.') }}
                        </div>
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
            const normal = btn.querySelector('.normal-state');
            const loading = btn.querySelector('.loading-state');
            btn.disabled = true;
            normal.classList.add('d-none');
            loading.classList.remove('d-none');
        });
    </script>
@endpush
