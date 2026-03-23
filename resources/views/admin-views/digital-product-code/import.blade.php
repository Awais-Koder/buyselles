@extends('layouts.admin.app')

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
                        <a href="{{ route('admin.dashboard.index') }}">{{ translate('dashboard') }}</a>
                    </li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('admin.products.list', 'in_house') }}">{{ translate('products') }}</a>
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
                            {{ translate('Download_the_Excel_template._Add_one_digital_code_(PIN)_per_row._You_can_restock_existing_in-house_products_or_create_new_ones_in_the_same_file.') }}
                        </p>
                        <ul class="list-unstyled mb-0 text-muted small">
                            <li>
                                {{ translate('Existing_products:_fill_product_name_+_pin_(product_id_also_accepted)') }}
                            </li>
                            <li>
                                {{ translate('New_products:_fill_product_name_+_price_+_category_id_+_pin') }}</li>
                            <li>
                                {{ translate('serial_number_and_expiry_date_are_optional') }}</li>
                            <li>
                                {{ translate('expiry_date_format:_YYYY-MM-DD_(e.g._2026-12-31)') }}</li>
                            <li>
                                {{ translate('Codes_past_expiry_are_auto-marked_expired_nightly') }}</li>
                            <li>
                                {{ translate('The_green_example_row_in_the_template_is_skipped_automatically') }}</li>
                        </ul>
                        <div class="mt-auto">
                            <a href="{{ route('admin.products.digital-code-import.template') }}"
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
                        <form action="{{ route('admin.products.digital-code-import.upload') }}" method="POST"
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

                            {{-- Info box --}}
                            <div class="alert alert-info py-2 small mb-3">
                                <i class="bi bi-info-circle-fill me-1"></i>
                                {{ translate('The_import_runs_in_the_background._You_will_receive_a_dashboard_notification_and_an_email_when_the_job_is_complete.') }}
                            </div>

                            {{-- Warning box --}}
                            <div class="alert alert-warning py-2 small mb-3">
                                <i class="bi bi-exclamation-triangle-fill me-1"></i>
                                {{ translate('Each_code_is_added_to_the_pool._Existing_codes_are_NOT_removed.') }}
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
                            {{ translate('Excel_Column_Format') }}
                        </h5>
                    </div>
                    <div class="card-body">

                        {{-- Scenario legend --}}
                        <div class="row g-2 small mb-3">
                            <div class="col-md-6">
                                <div class="alert alert-secondary py-2 mb-0">
                                    <strong>{{ translate('Restocking_an_existing_product') }}</strong><br>
                                    {{ translate('Required:') }} <code>product_name</code> + <code>pin</code><br>
                                    <span
                                        class="text-muted">{{ translate('(or use product_id instead of product_name)') }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="alert alert-info py-2 mb-0">
                                    <strong>{{ translate('Creating_a_new_product') }}</strong><br>
                                    {{ translate('Required:') }} <code>product_name</code> + <code>price</code> +
                                    <code>category_id</code> + <code>pin</code>
                                </div>
                            </div>
                        </div>

                        {{-- Column table --}}
                        <div class="table-responsive mb-3">
                            <table class="table table-bordered table-sm mb-0">
                                <thead
                                    style="background-color: var(--bs-primary); color: var(--bs-white); text-align:center;">
                                    <tr>
                                        <th>Column</th>
                                        <th>Required?</th>
                                        <th>Description</th>
                                        <th>Example</th>
                                    </tr>
                                </thead>
                                <tbody class="small">
                                    <tr>
                                        <td><code>product_id</code></td>
                                        <td><span class="badge bg-secondary">Optional</span></td>
                                        <td>Numeric ID of the product. Use this OR <code>product_name</code>.</td>
                                        <td class="text-muted">101</td>
                                    </tr>
                                    <tr>
                                        <td><code>product_name</code></td>
                                        <td><span class="badge bg-danger">Required</span></td>
                                        <td>Exact product name. Used to look up or create the product.</td>
                                        <td class="text-muted">PUBG Mobile 60 UC</td>
                                    </tr>
                                    <tr style="background:#EAF6FF;">
                                        <td><code>price</code></td>
                                        <td><span class="badge bg-warning text-dark">New only</span></td>
                                        <td>Selling price. Required only when creating a brand-new product.</td>
                                        <td class="text-muted">9.99</td>
                                    </tr>
                                    <tr style="background:#EAF6FF;">
                                        <td><code>category_id</code></td>
                                        <td><span class="badge bg-warning text-dark">New only</span></td>
                                        <td>Numeric category ID. Required only when creating a new product.</td>
                                        <td class="text-muted">25</td>
                                    </tr>
                                    <tr style="background:#FFF3CD;">
                                        <td><code>pin</code></td>
                                        <td><span class="badge bg-danger">Required</span></td>
                                        <td>The digital code / gift card PIN. Encrypted at rest.</td>
                                        <td class="text-warning fw-semibold">ABCD-1234-EFGH-5678</td>
                                    </tr>
                                    <tr>
                                        <td><code>serial_number</code></td>
                                        <td><span class="badge bg-secondary">Optional</span></td>
                                        <td>Extra reference / serial number for internal tracking.</td>
                                        <td class="text-muted">SN-00123</td>
                                    </tr>
                                    <tr>
                                        <td><code>expiry_date</code></td>
                                        <td><span class="badge bg-secondary">Optional</span></td>
                                        <td>
                                            Expiry date in <strong>YYYY-MM-DD</strong> format.<br>
                                            <span class="text-muted">Leave blank for codes that never expire.
                                                Codes past this date are automatically removed from available stock every
                                                night at 03:00.</span>
                                        </td>
                                        <td class="text-muted">2026-12-31</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        {{-- Live example rows --}}
                        <p class="fw-semibold mb-2 small"><i class="bi bi-eye me-1"></i>How the filled file should look:
                        </p>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0 small text-center">
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
                                    <tr class="table-success">
                                        <td colspan="7"
                                            class="text-center fst-italic text-success fw-semibold small py-1">
                                            ⚠ EXAMPLE — DELETE THIS ROW &nbsp;(this row appears in the template but is
                                            automatically skipped during import)
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">101</td>
                                        <td class="text-start text-muted">PUBG Mobile 60 UC</td>
                                        <td class="text-muted"><em>blank</em></td>
                                        <td class="text-muted"><em>blank</em></td>
                                        <td class="fw-semibold text-warning">ABCD-1234-EFGH-5678</td>
                                        <td class="text-muted">SN-00123</td>
                                        <td class="text-muted">2026-12-31</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted"><em>blank</em></td>
                                        <td class="text-start text-muted">Steam Wallet $10</td>
                                        <td class="text-primary fw-semibold">10.00</td>
                                        <td class="text-primary fw-semibold">25</td>
                                        <td class="fw-semibold text-warning">STEAM-XXXX-9999-YYYY</td>
                                        <td class="text-muted"><em>blank</em></td>
                                        <td class="text-muted"><em>blank</em></td>
                                    </tr>
                                </tbody>
                            </table>
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
