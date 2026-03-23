@extends('layouts.front-end.app')

@section('title', translate('order_Complete'))

@section('content')
    <div class="container mt-5 mb-5 rtl __inline-53 text-align-direction">
        <div class="row d-flex justify-content-center">
            <div class="col-md-10 col-lg-10">
                <div class="card">
                    @if (auth('customer')->check() || session('guest_id'))
                        <div class="card-body">
                            <div class="mb-3 text-center">
                                <i class="fa fa-check-circle __text-60px __color-0f9d58"></i>
                            </div>

                            <h6 class="font-black fw-bold text-center">
                                @if (isset($isNewCustomerInSession) && $isNewCustomerInSession)
                                    {{ translate('Order_Placed_&_Account_Created_Successfully') }}!
                                @else
                                    {{ translate('Order_Placed_Successfully') }}!
                                @endif
                            </h6>

                            @if (isset($order_ids) && count($order_ids) > 0)
                                <p class="text-center fs-12">
                                    {{ translate('your_payment_has_been_successfully_processed_and_your_order') }} -
                                    <span class="fw-bold text-primary">
                                        @foreach ($order_ids as $key => $order)
                                            {{ $order }}
                                        @endforeach
                                    </span>
                                    {{ translate('has_been_placed.') }}
                                </p>
                            @else
                                <p class="text-center fs-12">
                                    {{ translate('your_order_is_being_processed_and_will_be_completed.') }}
                                    {{ translate('You_will_receive_an_email_confirmation_when_your_order_is_placed.') }}
                                </p>
                            @endif

                            <div class="row mt-4">
                                <div class="col-12 text-center">
                                    <a href="{{ route('track-order.index') }}" class="btn btn--primary mb-3 text-center">
                                        {{ translate('track_Order') }}
                                    </a>
                                </div>
                                <div class="col-12 text-center">
                                    <a href="{{ route('home') }}" class="text-center">
                                        {{ translate('Continue_Shopping') }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- Digital Codes Section                                          --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    @if (!empty($digitalCodes) && count($digitalCodes) > 0)
        <div class="container mb-5 rtl __inline-53 text-align-direction">
            <div class="row d-flex justify-content-center">
                <div class="col-md-10 col-lg-10">
                    <div class="card border-success">
                        <div class="card-header bg-success text-white">
                            <h6 class="mb-0">
                                <i class="fa fa-key me-2"></i>
                                {{ translate('Your_Digital_Codes') }}
                            </h6>
                            <small>{{ translate('Codes_have_been_emailed_to_you_too._Keep_them_safe.') }}</small>
                        </div>
                        <div class="card-body">

                            @foreach ($digitalCodes as $item)
                                <div class="border rounded p-3 mb-3">
                                    <p class="fw-bold mb-1 text-muted" style="font-size:0.85rem;">
                                        {{ $item['productName'] }}
                                        @if ($item['orderId'])
                                            &nbsp;&mdash;&nbsp;
                                            <span class="text-secondary">{{ translate('Order') }}
                                                #{{ $item['orderId'] }}</span>
                                        @endif
                                    </p>

                                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                        <code class="fs-5 fw-bold text-dark bg-light px-3 py-2 rounded border"
                                            id="code-{{ $loop->index }}"
                                            style="letter-spacing:3px; font-family:'Courier New',monospace; word-break:break-all;">
                                            {{ $item['code'] }}
                                        </code>
                                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                            onclick="copyCode('code-{{ $loop->index }}', this)"
                                            title="{{ translate('Copy_Code') }}">
                                            <i class="fa fa-copy"></i> {{ translate('Copy') }}
                                        </button>
                                    </div>

                                    @if (!empty($item['serial']) || !empty($item['expiry']))
                                        <p class="text-muted mb-0" style="font-size:0.78rem;">
                                            @if (!empty($item['serial']))
                                                <strong>{{ translate('S/N') }}:</strong> {{ $item['serial'] }}
                                            @endif
                                            @if (!empty($item['expiry']))
                                                &nbsp;&nbsp;
                                                <strong>{{ translate('Exp') }}:</strong> {{ $item['expiry'] }}
                                            @endif
                                        </p>
                                    @endif
                                </div>
                            @endforeach

                            {{-- Action buttons --}}
                            <div class="mt-3 d-flex flex-wrap gap-2">
                                <button type="button" id="printThermalReceiptBtn"
                                    class="btn btn--primary d-flex align-items-center gap-1">
                                    <i class="fa fa-print"></i>
                                    <span>{{ translate('Print_Receipt') }}</span>
                                </button>
                            </div>

                            {{-- Hidden data for thermal receipt printing --}}
                            <script type="application/json" id="thermalReceiptData">
                                @json([
                                    'shopName'     => getWebConfig(name: 'company_name') ?? 'Buyselles',
                                    'shopPhone'    => getWebConfig(name: 'company_phone') ?? '',
                                    'orderIds'     => $order_ids ?? [],
                                    'orderDate'    => now()->format('Y-m-d H:i'),
                                    'codes'        => collect($digitalCodes)->toArray(),
                                ])
                            </script>

                            <p class="text-danger mt-3 mb-0" style="font-size:0.8rem;">
                                <i class="fa fa-exclamation-triangle"></i>
                                {{ translate('Warning:_Do_not_share_your_codes_with_anyone.') }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endsection

@push('script')
    <script>
        function copyCode(elementId, btn) {
            var text = document.getElementById(elementId).innerText.trim();
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(function() {
                    btn.innerHTML = '<i class="fa fa-check"></i> {{ translate('Copied!') }}';
                    setTimeout(function() {
                        btn.innerHTML = '<i class="fa fa-copy"></i> {{ translate('Copy') }}';
                    }, 2000);
                });
            } else {
                var ta = document.createElement('textarea');
                ta.value = text;
                document.body.appendChild(ta);
                ta.select();
                document.execCommand('copy');
                document.body.removeChild(ta);
                btn.innerHTML = '<i class="fa fa-check"></i> {{ translate('Copied!') }}';
                setTimeout(function() {
                    btn.innerHTML = '<i class="fa fa-copy"></i> {{ translate('Copy') }}';
                }, 2000);
            }
        }
    </script>
@endpush
