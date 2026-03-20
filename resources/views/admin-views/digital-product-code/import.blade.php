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
                            {{ translate('Download_the_Excel_template_that_contains_all_your_digital_products._Fill_in_the') }}
                            <strong class="text-warning">{{ translate('digital_code_(fill_this)') }}</strong>
                            {{ translate('column_and_upload_the_file_back.') }}
                        </p>
                        <ul class="list-unstyled mb-0 text-muted small">
                            <li><i class="bi bi-check2 text-success me-1"></i>
                                {{ translate('Columns:_Product_ID,_Product_Name,_Type,_Has_Code_Already,_digital_code') }}
                            </li>
                            <li><i class="bi bi-check2 text-success me-1"></i>
                                {{ translate('Do_NOT_change_the_Product_ID_column') }}</li>
                            <li><i class="bi bi-check2 text-success me-1"></i>
                                {{ translate('Rows_with_empty_digital_code_are_skipped') }}</li>
                            <li><i class="bi bi-check2 text-success me-1"></i>
                                {{ translate('Only_digital-type_products_are_included') }}</li>
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
                                {{ translate('Existing_digital_codes_for_matching_products_will_be_overwritten.') }}
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
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm mb-0 text-center">
                                <thead style="background-color: var(--bs-primary); color: var(--bs-white);">
                                    <tr>
                                        <th>{{ translate('Product_ID') }}</th>
                                        <th>{{ translate('Product_Name') }}</th>
                                        <th>{{ translate('Digital_Product_Type') }}</th>
                                        <th>{{ translate('Has_Code_Already') }}</th>
                                        <th style="color: var(--bs-warning);">{{ translate('digital_code_(fill_this)') }}
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="text-muted">101</td>
                                        <td class="text-muted text-start">PUBG Mobile 60 UC</td>
                                        <td class="text-muted">ready_product</td>
                                        <td class="text-muted">No</td>
                                        <td class="text-warning fw-semibold">ABCD-1234-EFGH</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">102</td>
                                        <td class="text-muted text-start">Netflix 1 Month</td>
                                        <td class="text-muted">ready_product</td>
                                        <td class="text-muted">Yes</td>
                                        <td class="text-warning fw-semibold">XXXX-9999-YYYY</td>
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
