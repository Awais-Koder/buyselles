@extends('layouts.admin.app')

@section('title', translate('add_product_mapping'))

@section('content')
<div class="content container-fluid">
    <div class="card">
        <div class="card-body">
            <h3 class="mb-4">{{ translate('add_product_supplier_mapping') }}</h3>

            <form action="{{ route('admin.supplier.mapping.store') }}" method="post">
                @csrf

                <div class="row gy-3">
                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="form-label">{{ translate('product') }} <span class="text-danger">*</span></label>
                            <select name="product_id" class="form-control" required>
                                <option value="">{{ translate('select_product') }}</option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}" {{ old('product_id') == $product->id ? 'selected' : '' }}>
                                        {{ $product->name }} (#{{ $product->id }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="form-label">{{ translate('supplier') }} <span class="text-danger">*</span></label>
                            <div class="d-flex gap-2 align-items-end">
                                <div class="flex-grow-1">
                                    <select name="supplier_api_id" id="supplier_api_id" class="form-control" required>
                                        <option value="">{{ translate('select_supplier') }}</option>
                                        @foreach($suppliers as $supplier)
                                            <option value="{{ $supplier->id }}" {{ old('supplier_api_id') == $supplier->id ? 'selected' : '' }}>
                                                {{ $supplier->name }} ({{ $supplier->driver }}){{ $supplier->is_active ? '' : ' — '.translate('inactive') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <button type="button" id="browse-catalog-btn"
                                        class="btn btn-outline-primary text-nowrap"
                                        style="height:38px;display:none;"
                                        data-bs-toggle="modal" data-bs-target="#catalogModal">
                                    <i class="fi fi-rr-search"></i> {{ translate('browse_catalog') }}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="form-label">{{ translate('supplier_product_id_SKU') }} <span class="text-danger">*</span></label>
                            <input type="text" name="supplier_product_id" id="supplier_product_id" class="form-control"
                                   placeholder="{{ translate('ex') }}: PROD-12345"
                                   value="{{ old('supplier_product_id') }}" required>
                        </div>
                    </div>

                    <div class="col-lg-6">
                        <div class="form-group">
                            <label class="form-label">{{ translate('supplier_product_name') }}</label>
                            <input type="text" name="supplier_product_name" id="supplier_product_name" class="form-control"
                                   placeholder="{{ translate('optional_cached_name') }}"
                                   value="{{ old('supplier_product_name') }}">
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="form-label">{{ translate('cost_price') }} <span class="text-danger">*</span></label>
                            <input type="number" name="cost_price" id="cost_price" class="form-control" step="0.01" min="0"
                                   value="{{ old('cost_price', '0.00') }}" required>
                        </div>
                    </div>

                    <div class="col-lg-2">
                        <div class="form-group">
                            <label class="form-label">{{ translate('currency') }}</label>
                            <input type="text" name="cost_currency" id="cost_currency" class="form-control" maxlength="3"
                                   value="{{ old('cost_currency', 'USD') }}">
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-label">{{ translate('markup_type') }} <span class="text-danger">*</span></label>
                            <select name="markup_type" class="form-control" required>
                                <option value="percent" {{ old('markup_type', 'percent') == 'percent' ? 'selected' : '' }}>{{ translate('percent') }} (%)</option>
                                <option value="flat" {{ old('markup_type') == 'flat' ? 'selected' : '' }}>{{ translate('flat') }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-lg-3">
                        <div class="form-group">
                            <label class="form-label">{{ translate('markup_value') }}</label>
                            <input type="number" name="markup_value" class="form-control" step="0.01" min="0"
                                   value="{{ old('markup_value', '10') }}">
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="form-label">{{ translate('priority') }} <span class="text-danger">*</span></label>
                            <input type="number" name="priority" class="form-control" min="0"
                                   value="{{ old('priority', 0) }}" required>
                            <small class="text-muted">{{ translate('lower_number_=_higher_priority') }}</small>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="form-label">{{ translate('min_stock_threshold') }}</label>
                            <input type="number" name="min_stock_threshold" class="form-control" min="0"
                                   value="{{ old('min_stock_threshold', 5) }}">
                            <small class="text-muted">{{ translate('auto_restock_when_below_this') }}</small>
                        </div>
                    </div>

                    <div class="col-lg-4">
                        <div class="form-group">
                            <label class="form-label">{{ translate('max_restock_quantity') }}</label>
                            <input type="number" name="max_restock_qty" class="form-control" min="1"
                                   value="{{ old('max_restock_qty', 50) }}">
                        </div>
                    </div>

                    <div class="col-lg-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="auto_restock" value="1"
                                   id="auto-restock-toggle" {{ old('auto_restock', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="auto-restock-toggle">
                                {{ translate('enable_auto_restock') }}
                            </label>
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-3 mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fi fi-sr-check"></i> {{ translate('save') }}
                    </button>
                    <a href="{{ route('admin.supplier.mapping.list') }}" class="btn btn-secondary">
                        {{ translate('cancel') }}
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ─── Catalog Browse Modal ──────────────────────────────────────────────── --}}
<div class="modal fade" id="catalogModal" tabindex="-1" aria-labelledby="catalogModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="catalogModalLabel">
                    <i class="fi fi-rr-search me-2"></i>{{ translate('browse_supplier_catalog') }}
                </h5>
                <div class="d-flex gap-2 align-items-center me-2">
                    <button type="button" id="catalog-refresh-btn" class="btn btn-sm btn-outline-secondary"
                            title="{{ translate('refresh_catalog') }}">
                        <i class="fi fi-rr-rotate-right"></i>
                    </button>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                {{-- Search bar --}}
                <div class="d-flex gap-2 mb-3">
                    <input type="text" id="catalog-search" class="form-control"
                           placeholder="{{ translate('search_by_product_name') }}&hellip;">
                    <button type="button" id="catalog-search-btn" class="btn btn-primary px-4">
                        <i class="fi fi-rr-search"></i>
                    </button>
                </div>

                {{-- Loading state --}}
                <div id="catalog-loading" class="text-center py-5" style="display:none;">
                    <div class="spinner-border text-primary" role="status"></div>
                    <p class="text-muted mt-2">{{ translate('loading_catalog') }}&hellip;</p>
                </div>

                {{-- Error state --}}
                <div id="catalog-error" class="alert alert-danger" style="display:none;"></div>

                {{-- Products table --}}
                <div id="catalog-table-wrap" style="display:none;">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>{{ translate('id_SKU') }}</th>
                                <th>{{ translate('name') }}</th>
                                <th class="text-end">{{ translate('price') }}</th>
                                <th class="text-center">{{ translate('qty_available') }}</th>
                                <th class="text-center">{{ translate('region') }}</th>
                                <th class="text-center">{{ translate('action') }}</th>
                            </tr>
                        </thead>
                        <tbody id="catalog-tbody"></tbody>
                    </table>

                    {{-- Pagination --}}
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <small id="catalog-count-text" class="text-muted"></small>
                        <div class="d-flex gap-2">
                            <button type="button" id="catalog-prev" class="btn btn-sm btn-outline-secondary" disabled>
                                &larr; {{ translate('previous') }}
                            </button>
                            <button type="button" id="catalog-next" class="btn btn-sm btn-outline-secondary" disabled>
                                {{ translate('next') }} &rarr;
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Empty state --}}
                <div id="catalog-empty" class="text-center py-5" style="display:none;">
                    <i class="fi fi-sr-inbox-in fs-1 text-muted"></i>
                    <p class="text-muted mt-2">{{ translate('no_products_found') }}</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
(function () {
    const catalogUrl    = "{{ rtrim(url('admin/supplier'), '/') }}";
    const searchInput   = document.getElementById('catalog-search');
    const searchBtn     = document.getElementById('catalog-search-btn');
    const loadingEl     = document.getElementById('catalog-loading');
    const errorEl       = document.getElementById('catalog-error');
    const tableWrapEl   = document.getElementById('catalog-table-wrap');
    const tbodyEl       = document.getElementById('catalog-tbody');
    const emptyEl       = document.getElementById('catalog-empty');
    const prevBtn       = document.getElementById('catalog-prev');
    const nextBtn       = document.getElementById('catalog-next');
    const countTextEl   = document.getElementById('catalog-count-text');
    const supplierSel   = document.getElementById('supplier_api_id');
    const browsBtn      = document.getElementById('browse-catalog-btn');
    const refreshBtn    = document.getElementById('catalog-refresh-btn');

    let currentPage = 0;
    let isRefresh   = false;
    const pageSize  = 50;

    // Show/hide Browse Catalog button based on supplier selection
    supplierSel.addEventListener('change', function () {
        browsBtn.style.display = this.value ? 'inline-flex' : 'none';
        currentPage = 0;
    });

    // Trigger search on Enter
    searchInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') { currentPage = 0; isRefresh = false; loadCatalog(); }
    });
    searchBtn.addEventListener('click', function () { currentPage = 0; isRefresh = false; loadCatalog(); });

    // Bust cache and reload
    refreshBtn.addEventListener('click', function () {
        currentPage = 0;
        isRefresh   = true;
        loadCatalog();
    });

    // Load catalog when modal opens
    document.getElementById('catalogModal').addEventListener('show.bs.modal', function () {
        if (!tbodyEl.children.length) {
            loadCatalog();
        }
    });

    prevBtn.addEventListener('click', function () {
        if (currentPage > 0) { currentPage--; isRefresh = false; loadCatalog(); }
    });
    nextBtn.addEventListener('click', function () {
        currentPage++; isRefresh = false; loadCatalog();
    });

    function setVisibility(loading, error, table, empty) {
        loadingEl.style.display   = loading ? '' : 'none';
        errorEl.style.display     = error   ? '' : 'none';
        tableWrapEl.style.display = table   ? '' : 'none';
        emptyEl.style.display     = empty   ? '' : 'none';
    }

    function loadCatalog() {
        const supplierId = supplierSel.value;
        if (!supplierId) return;

        setVisibility(true, false, false, false);
        refreshBtn.disabled = true;

        const params = new URLSearchParams({
            search:  searchInput.value.trim(),
            page:    currentPage,
            size:    pageSize,
            refresh: isRefresh ? '1' : '0',
        });

        fetch(`${catalogUrl}/${supplierId}/catalog?${params}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (!data.success) {
                setVisibility(false, true, false, false);
                errorEl.textContent = data.message || '{{ translate('failed_to_load_catalog') }}';
                return;
            }

            const products = data.products || [];

            if (products.length === 0) {
                setVisibility(false, false, false, true);
                prevBtn.disabled = true;
                nextBtn.disabled = true;
                countTextEl.textContent = '';
                return;
            }

            tbodyEl.innerHTML = products.map(function (p) {
                // 999 is the sentinel used when Bamboo returns null for count (unlimited / not tracked).
                // Show the real number always; append "+" for the sentinel so it's clear "at least 999".
                let stockHtml;
                if (p.stock === 0) {
                    stockHtml = '<span class="badge bg-danger">{{ translate('out_of_stock') }}</span>';
                } else if (p.stock >= 999) {
                    stockHtml = '<span class="badge bg-success">999+</span>';
                } else if (p.stock >= 50) {
                    stockHtml = `<span class="badge bg-success">${p.stock}</span>`;
                } else {
                    stockHtml = `<span class="badge bg-warning text-dark">${p.stock}</span>`;
                }

                const region = p.region
                    ? `<span class="badge bg-secondary">${escHtml(p.region)}</span>`
                    : '<span class="text-muted">—</span>';

                return `<tr>
                    <td><code>${escHtml(String(p.id))}</code></td>
                    <td>${escHtml(p.name)}</td>
                    <td class="text-end fw-semibold">${escHtml(String(p.price))} <small class="text-muted">${escHtml(p.currency)}</small></td>
                    <td class="text-center">${stockHtml}</td>
                    <td class="text-center">${region}</td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-primary select-product-btn"
                            data-id="${escHtml(String(p.id))}"
                            data-name="${escHtml(p.name)}"
                            data-price="${escHtml(String(p.price))}"
                            data-currency="${escHtml(p.currency)}">
                            {{ translate('select') }}
                        </button>
                    </td>
                </tr>`;
            }).join('');

            // Attach select handlers
            tbodyEl.querySelectorAll('.select-product-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    document.getElementById('supplier_product_id').value   = btn.dataset.id;
                    document.getElementById('supplier_product_name').value = btn.dataset.name;
                    document.getElementById('cost_price').value            = btn.dataset.price;
                    document.getElementById('cost_currency').value         = btn.dataset.currency;

                    bootstrap.Modal.getInstance(document.getElementById('catalogModal')).hide();
                });
            });

            const total = data.total ?? products.length;
            prevBtn.disabled = currentPage === 0;
            nextBtn.disabled = (currentPage + 1) * pageSize >= total;

            const from = currentPage * pageSize + 1;
            const to   = Math.min(from + products.length - 1, total);
            countTextEl.textContent = `${from}–${to} {{ translate('of') }} ${total} {{ translate('items') }}`;

            setVisibility(false, false, true, false);
            isRefresh = false;
            refreshBtn.disabled = false;
        })
        .catch(function (err) {
            setVisibility(false, true, false, false);
            errorEl.textContent = '{{ translate('failed_to_load_catalog') }}: ' + err.message;
            refreshBtn.disabled = false;
        });
    }

    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    // If old supplier_api_id was submitted, show the button immediately
    if (supplierSel.value) {
        browsBtn.style.display = 'inline-flex';
    }
})();
</script>
@endpush
