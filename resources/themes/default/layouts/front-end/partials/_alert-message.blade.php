<audio id="myAudio">
    <source src="{{ dynamicAsset(path: 'public/assets/front-end/sound/notification.mp3') }}" type="audio/mpeg">
</audio>

<div class="alert--container active">
    {{-- Order status change notification for customer --}}
    <a href="javascript:" id="order-status-notification-link">
        <div class="alert alert--message-2 alert-dismissible fade show" id="order-status-notification" role="alert">
            <img width="28" src="{{ dynamicAsset(path: 'public/assets/back-end/img/icons/order.svg') }}"
                onerror="this.src='{{ dynamicAsset(path: 'public/assets/back-end/img/info-2.png') }}'" alt="">
            <div class="w--0">
                <h6 class="title text-truncate mb-1">{{ translate('Order_Update') }}</h6>
                <span class="message" id="order-status-notification-message">
                    {{ translate('Order_Status_Changed') }}
                </span>
            </div>
            <button type="button" class="close __close position-relative p-0" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </a>

    @if (env('APP_MODE') == 'demo')
        <div class="alert alert--message-2 alert-dismissible fade show" id="demo-reset-warning">
            <img width="28" class="align-self-start"
                src="{{ theme_asset(path: 'assets/front-end/img/info-2.png') }}" alt="">
            <div class="w--0">
                <h6>{{ translate('warning') . '!' }}</h6>
                <span>
                    {{ translate('though_it_is_a_demo_site') . '.' . translate('_our_system_automatically_reset_after_one_hour_&_that_why_you_logged_out') . '.' }}
                </span>
            </div>
            <button type="button" class="close __close position-relative p-0" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="alert alert--message-2 alert-dismissible fade show product-restock-stock-alert">
        <div class="d-flex min-w-60px">
            <img width="50" class="align-self-start aspect-1 border rounded image object-cover" src=""
                alt="">
        </div>
        <div class="w-100 text-start overflow-hidden">
            <h6 class="title text-truncate mb-1"></h6>
            <span class="message"></span>
            <div class="d-flex justify-content-between gap-3 mt-2">
                <a href="javascript:" class="text-decoration-underline text-capitalize get-view-by-onclick product-link"
                    data-link="">
                    {{ translate('click_to_view') }}
                </a>
            </div>
        </div>
        <button type="button" class="close position-relative font-semi-bold p-0 product-restock-stock-close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
</div>

@if (auth('customer')->check())
    <span id="customer-reverb-config" data-id="{{ auth('customer')->id() }}"
        data-key="{{ config('broadcasting.connections.reverb.key') }}"
        data-host="{{ config('broadcasting.connections.reverb.options.host', '127.0.0.1') }}"
        data-port="{{ (int) config('broadcasting.connections.reverb.options.port', 8080) }}"
        data-tls="{{ config('broadcasting.connections.reverb.options.useTLS', false) ? 'true' : 'false' }}"
        class="d-none"></span>
    <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var configEl = document.getElementById('customer-reverb-config');
            if (!configEl) return;
            var customerId = configEl.getAttribute('data-id');
            var reverbKey = configEl.getAttribute('data-key');
            if (!reverbKey || !customerId) return;

            try {
                var pusher = new Pusher(reverbKey, {
                    wsHost: configEl.getAttribute('data-host'),
                    wsPort: parseInt(configEl.getAttribute('data-port')),
                    wssPort: parseInt(configEl.getAttribute('data-port')),
                    forceTLS: configEl.getAttribute('data-tls') === 'true',
                    enabledTransports: ['ws', 'wss'],
                    cluster: '',
                    authEndpoint: '/broadcasting/auth',
                    auth: {
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="_token"]') ?
                                document.querySelector('meta[name="_token"]').getAttribute('content') : ''
                        }
                    }
                });

                var orderChannel = pusher.subscribe('private-customer.' + customerId + '.orders');
                orderChannel.bind('order-status-changed', function(data) {
                    var orderAlert = document.getElementById('order-status-notification');
                    var orderAlertMsg = document.getElementById('order-status-notification-message');
                    var orderAlertLink = document.getElementById('order-status-notification-link');
                    if (orderAlertMsg) {
                        orderAlertMsg.textContent = data.message ||
                            '{{ translate('Order_Status_Changed') }}';
                    }
                    if (orderAlertLink && data.order_id) {
                        orderAlertLink.setAttribute('href',
                            '{{ route('account-order-details') }}' + '?id=' +
                            data.order_id);
                    }
                    if (orderAlert) {
                        orderAlert.classList.add('active');
                    }
                    var audio = document.getElementById('myAudio');
                    if (audio) {
                        audio.play().catch(function() {});
                    }
                    setTimeout(function() {
                        if (orderAlert) {
                            orderAlert.classList.remove('active');
                        }
                    }, 6000);
                });
            } catch (e) {
                console.warn('Customer Reverb connection failed:', e);
            }
        });
    </script>
@endif
