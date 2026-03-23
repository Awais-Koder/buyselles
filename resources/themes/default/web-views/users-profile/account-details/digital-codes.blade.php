@extends('layouts.front-end.app')

@section('title', translate('order_Details') . ' - ' . translate('digital_codes'))

@section('content')
    <div class="container pb-5 mb-2 mb-md-4 mt-3 rtl __inline-47 text-start">
        <div class="row g-3">
            @include('web-views.partials._profile-aside')

            <section class="col-lg-9">
                @include('web-views.users-profile.account-details.partial')

                <div class="bg-sm-white mt-3">
                    <div class="p-sm-3 d-flex flex-column gap-3 pb-md-5">

                        @if ($digitalCodes->count() > 0 && $order->payment_status == 'paid')
                            {{-- Header with Print Receipt button --}}
                            <div class="bg-white border rounded-10 overflow-hidden">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 px-3 py-2"
                                    style="background: linear-gradient(135deg, #006161 0%, #063c93 100%);">
                                    <div class="d-flex align-items-center gap-2">
                                        <i class="fi fi-rr-key text-white fs-16"></i>
                                        <h6 class="m-0 text-white fs-14 fw-semibold">
                                            {{ translate('Your_Digital_Codes') }}
                                        </h6>
                                    </div>
                                    <button type="button" id="printThermalReceiptBtn"
                                        class="btn btn-sm btn-light d-flex align-items-center gap-1 rounded-pill px-3 py-1">
                                        <i class="fi fi-rr-print fs-12"></i>
                                        <span class="fs-12 fw-semibold">{{ translate('Print_Receipt') }}</span>
                                    </button>
                                </div>

                                {{-- Code cards --}}
                                <div class="p-3">
                                    <div class="row g-2">
                                        @foreach ($digitalCodes as $dCode)
                                            <div class="col-md-6">
                                                <div class="border rounded-8 p-3 h-100 position-relative"
                                                    style="background: #f8fbff;">
                                                    <div class="fs-12 fw-semibold text-dark mb-1 text-truncate"
                                                        title="{{ $dCode['productName'] }}">
                                                        {{ $dCode['productName'] }}
                                                    </div>
                                                    <div class="d-flex align-items-center gap-2 mb-2">
                                                        <code class="fs-15 fw-bold text-break"
                                                            style="letter-spacing:1.5px; color:#063c93; background:transparent;">
                                                            {{ $dCode['code'] }}
                                                        </code>
                                                        <button type="button"
                                                            class="btn btn-sm p-0 border-0 copy-digital-code-btn"
                                                            data-code="{{ $dCode['code'] }}"
                                                            title="{{ translate('Copy') }}">
                                                            <i class="fi fi-rr-copy fs-14 text-muted"></i>
                                                        </button>
                                                    </div>
                                                    <div class="d-flex flex-wrap gap-2 fs-11 text-muted">
                                                        @if (!empty($dCode['serial']))
                                                            <span>
                                                                <strong>{{ translate('Serial') }}:</strong>
                                                                {{ $dCode['serial'] }}
                                                            </span>
                                                        @endif
                                                        @if (!empty($dCode['expiry']))
                                                            <span>
                                                                <strong>{{ translate('Expires') }}:</strong>
                                                                {{ $dCode['expiry'] }}
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="text-center mt-2">
                                        <small class="text-danger fs-11">
                                            <i class="fi fi-rr-shield-exclamation"></i>
                                            {{ translate('Keep_these_codes_safe._Do_not_share_with_anyone.') }}
                                        </small>
                                    </div>
                                </div>
                            </div>

                            {{-- Hidden data for thermal receipt printing --}}
                            @php
                                $receiptData = [
                                    'shopName' => getWebConfig(name: 'company_name') ?? 'Buyselles',
                                    'shopPhone' => getWebConfig(name: 'company_phone') ?? '',
                                    'orderId' => $order->id,
                                    'orderDate' => $order->created_at?->format('Y-m-d H:i'),
                                    'customerName' =>
                                        $order->customer?->f_name . ' ' . ($order->customer?->l_name ?? ''),
                                    'codes' => $digitalCodes->toArray(),
                                ];
                            @endphp
                            <script type="application/json" id="thermalReceiptData">{!! json_encode($receiptData) !!}</script>
                        @else
                            {{-- No codes available --}}
                            <div class="bg-white border rounded-10 p-4 text-center">
                                <i class="fi fi-rr-key fs-30 text-muted mb-2 d-block"></i>
                                <h6 class="text-muted fs-14">{{ translate('No_digital_codes_available') }}</h6>
                                @if ($order->payment_status != 'paid')
                                    <p class="text-muted fs-12 mb-0">
                                        {{ translate('Digital_codes_will_be_available_once_payment_is_confirmed.') }}
                                    </p>
                                @endif
                            </div>
                        @endif

                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection

@push('script')
    <script>
        // ── Copy digital code to clipboard ───────────────────────────────
        $(document).on('click', '.copy-digital-code-btn', function() {
            var code = $(this).data('code');
            var $btn = $(this);
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(code).then(function() {
                    $btn.find('i').removeClass('fi-rr-copy').addClass('fi-rr-check text-success');
                    setTimeout(function() {
                        $btn.find('i').removeClass('fi-rr-check text-success').addClass(
                            'fi-rr-copy');
                    }, 2000);
                });
            } else {
                var $temp = $('<textarea>');
                $('body').append($temp);
                $temp.val(code).select();
                document.execCommand('copy');
                $temp.remove();
                $btn.find('i').removeClass('fi-rr-copy').addClass('fi-rr-check text-success');
                setTimeout(function() {
                    $btn.find('i').removeClass('fi-rr-check text-success').addClass('fi-rr-copy');
                }, 2000);
            }
        });

        // ── Thermal Receipt Printer ──────────────────────────────────────
        $(document).on('click', '#printThermalReceiptBtn', function() {
            var dataEl = document.getElementById('thermalReceiptData');
            if (!dataEl) return;
            var d = JSON.parse(dataEl.textContent);

            var codesHtml = '';
            for (var i = 0; i < d.codes.length; i++) {
                var c = d.codes[i];
                codesHtml +=
                    '<div style="margin:6px 0 8px;">' +
                    '<div style="font-size:8pt;font-weight:bold;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">' +
                    escHtml(c.productName) +
                    '</div>' +
                    '<div style="font-size:12pt;font-weight:bold;letter-spacing:3px;word-break:break-all;margin:3px 0;">' +
                    escHtml(c.code) +
                    '</div>' +
                    '<div style="font-size:7pt;color:#555;">' +
                    (c.serial ? 'SN: ' + escHtml(c.serial) + '  ' : '') +
                    (c.expiry ? 'EXP: ' + escHtml(c.expiry) : '') +
                    '</div>' +
                    (i < d.codes.length - 1 ? '<div style="border-bottom:1px dotted #aaa;margin-top:6px;"></div>' :
                        '') +
                    '</div>';
            }

            var html = '<!DOCTYPE html><html><head><meta charset="UTF-8">' +
                '<title>Receipt #' + escHtml(String(d.orderId)) + '</title>' +
                '<style>' +
                '*, *::before, *::after { box-sizing:border-box; margin:0; padding:0; }' +
                'body { font-family:"Courier New",Courier,monospace; width:80mm; margin:0 auto; padding:4mm; color:#000; font-size:9pt; }' +
                '@page { size:80mm auto; margin:0; }' +
                '@media print { html, body { width:80mm; margin:0; padding:4mm; } }' +
                '</style></head><body>' +
                '<div style="text-align:center;font-size:14pt;font-weight:bold;letter-spacing:1px;">' + escHtml(d
                    .shopName) + '</div>' +
                (d.shopPhone ? '<div style="text-align:center;font-size:8pt;">' + escHtml(d.shopPhone) + '</div>' :
                    '') +
                '<div style="border-top:1px dashed #000;margin:6px 0;"></div>' +
                '<table style="width:100%;font-size:8pt;">' +
                '<tr><td>Order</td><td style="text-align:right;">#' + escHtml(String(d.orderId)) + '</td></tr>' +
                '<tr><td>Date</td><td style="text-align:right;">' + escHtml(d.orderDate || '') + '</td></tr>' +
                (d.customerName && d.customerName.trim() ? '<tr><td>Customer</td><td style="text-align:right;">' +
                    escHtml(d.customerName.trim()) + '</td></tr>' : '') +
                '</table>' +
                '<div style="border-top:1px dashed #000;margin:6px 0;"></div>' +
                '<div style="text-align:center;font-size:9pt;font-weight:bold;text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">DIGITAL CODES</div>' +
                codesHtml +
                '<div style="border-top:1px dashed #000;margin:8px 0 4px;"></div>' +
                '<div style="text-align:center;font-size:7pt;">** Keep codes safe — do not share **</div>' +
                '<div style="text-align:center;font-size:7pt;margin-top:2px;">Thank you for your purchase!</div>' +
                '<div style="text-align:center;font-size:7pt;margin-top:2px;">' + escHtml(d.shopName) + '</div>' +
                '</body></html>';

            // Inject into hidden iframe and print
            var frameId = 'thermalPrintFrame';
            var existing = document.getElementById(frameId);
            if (existing) existing.remove();

            var iframe = document.createElement('iframe');
            iframe.id = frameId;
            iframe.style.cssText = 'position:fixed;top:-9999px;left:-9999px;width:80mm;height:0;border:none;';
            document.body.appendChild(iframe);

            var doc = iframe.contentWindow.document;
            doc.open();
            doc.write(html);
            doc.close();

            iframe.contentWindow.onafterprint = function() {
                iframe.remove();
            };
            setTimeout(function() {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            }, 300);
        });

        function escHtml(str) {
            var div = document.createElement('div');
            div.appendChild(document.createTextNode(str));
            return div.innerHTML;
        }
    </script>
@endpush
