@extends('layouts.admin.app')

@section('title', translate('product_supplier_mappings'))

@section('content')
<div class="content container-fluid">
    <div class="card">
        <div class="card-body d-flex flex-column gap-20">
            <div class="d-flex justify-content-between align-items-center gap-20 flex-wrap">
                <h3 class="mb-0">
                    {{ translate('product_supplier_mappings') }}
                    <span class="badge text-dark bg-body-secondary fw-semibold rounded-50">{{ $mappings->total() }}</span>
                </h3>

                <div class="d-flex flex-wrap gap-3 align-items-stretch">
                    <form action="{{ url()->current() }}" method="GET" class="d-flex gap-2">
                        <select name="supplier_id" class="form-control form-control-sm" style="min-width: 150px;">
                            <option value="">{{ translate('all_suppliers') }}</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ $supplierId == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="input-group flex-grow-1 max-w-280">
                            <input type="search" name="searchValue" class="form-control"
                                placeholder="{{ translate('search_product_or_sku') }}"
                                value="{{ $searchValue }}">
                            <div class="input-group-append search-submit">
                                <button type="submit"><i class="fi fi-rr-search"></i></button>
                            </div>
                        </div>
                    </form>

                    <a href="{{ route('admin.supplier.mapping.add') }}" class="btn btn-primary">
                        + {{ translate('add_mapping') }}
                    </a>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover table-borderless align-middle">
                    <thead class="text-capitalize">
                        <tr>
                            <th>{{ translate('SL') }}</th>
                            <th>{{ translate('product') }}</th>
                            <th>{{ translate('supplier') }}</th>
                            <th>{{ translate('supplier_SKU') }}</th>
                            <th class="text-center">{{ translate('cost') }}</th>
                            <th class="text-center">{{ translate('markup') }}</th>
                            <th class="text-center">{{ translate('sell_price') }}</th>
                            <th class="text-center">{{ translate('priority') }}</th>
                            <th class="text-center">{{ translate('auto_restock') }}</th>
                            <th class="text-center">{{ translate('status') }}</th>
                            <th class="text-center">{{ translate('action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                    @forelse ($mappings as $key => $mapping)
                        <tr>
                            <td>{{ $mappings->firstItem() + $key }}</td>
                            <td>
                                <div class="text-truncate max-w-200" title="{{ $mapping->product->name ?? '-' }}">
                                    {{ $mapping->product->name ?? translate('deleted_product') }}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info text-white">{{ $mapping->supplierApi->name ?? '-' }}</span>
                            </td>
                            <td>
                                <code>{{ $mapping->supplier_product_id }}</code>
                            </td>
                            <td class="text-center">{{ $mapping->cost_currency }} {{ number_format($mapping->cost_price, 2) }}</td>
                            <td class="text-center">
                                {{ $mapping->markup_type === 'percent' ? $mapping->markup_value . '%' : $mapping->cost_currency . ' ' . number_format($mapping->markup_value, 2) }}
                            </td>
                            <td class="text-center fw-semibold">
                                {{ $mapping->cost_currency }} {{ number_format($mapping->calculateSellPrice(), 2) }}
                            </td>
                            <td class="text-center">{{ $mapping->priority }}</td>
                            <td class="text-center">
                                @if($mapping->auto_restock)
                                    <span class="badge bg-success">{{ translate('on') }}</span>
                                    <br><small class="text-muted">min:{{ $mapping->min_stock_threshold }} / max:{{ $mapping->max_restock_qty }}</small>
                                @else
                                    <span class="badge bg-secondary">{{ translate('off') }}</span>
                                @endif
                            </td>
                            <td>
                                <form action="{{ route('admin.supplier.mapping.status') }}" method="post"
                                      id="mapping-status{{ $mapping->id }}-form"
                                      class="no-reload-form">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $mapping->id }}">
                                    <label class="switcher mx-auto" for="mapping-status{{ $mapping->id }}">
                                        <input class="switcher_input custom-modal-plugin" type="checkbox" value="1"
                                               name="status" id="mapping-status{{ $mapping->id }}"
                                               {{ $mapping->is_active ? 'checked' : '' }}
                                               data-modal-type="input-change-form" data-reload="true">
                                        <span class="switcher_control"></span>
                                    </label>
                                </form>
                            </td>
                            <td class="text-center">
                                <div class="d-flex gap-2 justify-content-center">
                                    <a href="{{ route('admin.supplier.mapping.edit', $mapping->id) }}" class="btn-icon">
                                        <i class="fi fi-sr-edit"></i>
                                    </a>
                                    <form method="post" action="{{ route('admin.supplier.mapping.delete') }}" class="d-inline delete-mapping-form">
                                        @csrf
                                        <input type="hidden" name="id" value="{{ $mapping->id }}">
                                        <button type="button" class="btn-icon btn-danger-icon delete-mapping-btn">
                                            <i class="fi fi-rr-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="11" class="text-center py-4">
                                <div class="d-flex flex-column align-items-center gap-2">
                                    <i class="fi fi-sr-inbox-in fs-1 text-muted"></i>
                                    <span class="text-muted">{{ translate('no_mappings_found') }}</span>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-sm-end">
                {{ $mappings->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script>
    'use strict';

    $(document).on('click', '.delete-mapping-btn', function() {
        let form = $(this).closest('form');
        const getText = document.getElementById('get-confirm-and-cancel-button-text-for-delete');
        Swal.fire({
            title: getText?.dataset.sure || 'Are you sure?',
            text: getText?.dataset.text || 'You will not be able to revert this!',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            cancelButtonText: getText?.dataset.cancel || 'Cancel',
            confirmButtonText: getText?.dataset.confirm || 'Yes, delete it!',
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                form.submit();
            }
        });
    });
</script>
@endpush
