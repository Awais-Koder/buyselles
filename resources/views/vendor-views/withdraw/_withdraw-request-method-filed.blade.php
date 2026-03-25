@foreach ($withdrawalMethod?->method_fields as $methodField)
    <div class="form-group">
        <label class="form-label text-dark">
            {{ translate($methodField['input_name']) }}
            <span class="text-danger">{{ $methodField['is_required'] ? '*' : '' }}</span>
        </label>
        <input type="{{ $methodField['input_type'] == 'phone' ? 'tel' : $methodField['input_type'] }}"
            class="form-control" placeholder="{{ translate($methodField['placeholder']) }}"
            name="method_info[{{ $methodField['input_name'] }}]" {{ $methodField['is_required'] ? 'required' : '' }}>
    </div>
@endforeach

<input type="hidden" name="withdraw_method" value="{{ $withdrawalMethod['id'] }}">

<div class="form-group">
    <label class="form-label text-dark">
        {{ translate('Withdraw_Amount ') }}
        ({{ getCurrencySymbol() }})
        <span class="text-danger">*</span>
    </label>
    <input type="number" class="form-control" name="amount" step="any" min=".01" required
        placeholder="{{ translate('Ex') }}: {{ usdToDefaultCurrency(amount: $vendorWallet?->total_earning ?? 0) }}"
        max="{{ usdToDefaultCurrency(amount: $vendorWallet?->total_earning ?? 0) }}">
</div>
