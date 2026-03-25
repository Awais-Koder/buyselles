<div class="offcanvas-body p-3 overflow-auto flex-grow-1">
    <div class="bg-light p-3 rounded mb-3">

        <div class="form-group">
            <label class="form-label text-dark">
                {{ translate('Select_Withdrawal_Method') }}
                <span class="text-danger">*</span>

                <span class="tooltip-icon" data-toggle="tooltip" data-placement="top"
                    aria-label="{{ translate('select_a_withdrawal_method_to_set_up_your_payment_details_for_sending_withdrawal_requests_to_the_admin_.') }}"
                    data-title="{{ translate('select_a_withdrawal_method_to_set_up_your_payment_details_for_sending_withdrawal_requests_to_the_admin_.') }}"
                    title="">
                    <i class="fi fi-sr-info"></i>
                </span>
            </label>

            <select name="withdrawal_method_id"
                class="form-control js-select2-custom vendor-withdrawal-method {{ Request::is('vendor/dashboard') ? 'vendor-withdrawal-method-dashboard' : '' }}"
                required data-route="{{ route('vendor.business-settings.withdraw.render-withdraw-method-infos') }}">
                <option value="" selected disabled>{{ translate('Select') }}</option>
                @foreach ($withdrawalMethods as $withdrawalMethod)
                    <option value="{{ $withdrawalMethod['id'] }}" data-type="pre-defined">
                        {{ $withdrawalMethod['method_name'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="" id="withdraw-request-method-filed">
            {{-- Dynamic form fields loaded via AJAX when a method is selected --}}
        </div>
    </div>
</div>
