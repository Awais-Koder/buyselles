<script src="{{ dynamicAsset(path: 'public/assets/new/back-end/libs/jquery/jquery-3.7.1.min.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/backend/libs/bootstrap/bootstrap.bundle.min.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/backend/libs/jquery-validate/jquery.validate.min.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/backend/libs/select2/select2.min.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/new/back-end/js/select-2-init.js') }}"></script>

<script src="{{ dynamicAsset(path: 'public/assets/backend/libs/intl-tel-input/js/intlTelInput.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/backend/libs/intl-tel-input/js/utils.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/backend/libs/intl-tel-input/js/intlTelInout-validation.js') }}">
</script>

<script src="{{ dynamicAsset(path: 'public/assets/new/back-end/libs/tags-input/tags-input.min.js') }}"></script>
<script
    src="{{ dynamicAsset(path: 'public/assets/new/back-end/libs/spartan-multi-image-picker/spartan-multi-image-picker-min.js') }}">
</script>

<script src="{{ dynamicAsset(path: 'public/assets/new/back-end/libs/swiper/swiper-bundle.min.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/backend/libs/easyzoom/easyzoom.min.js') }}"></script>

<script src="{{ dynamicAsset(path: 'public/assets/backend/libs/sweetalert2/sweetalert2.all.min.js') }}"></script>

<script src="{{ dynamicAsset(path: 'public/assets/new/back-end/libs/lightbox/lightbox.min.js') }}"></script>

<script src="{{ dynamicAsset(path: 'public/assets/new/back-end/libs/moment.min.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/new/back-end/libs/daterangepicker/daterangepicker.min.js') }}">
</script>
<script src="{{ dynamicAsset(path: 'public/assets/new/back-end/libs/daterangepicker/daterangepicker-init.js') }}">
</script>

<script src="{{ dynamicAsset(path: 'public/assets/new/back-end/js/single-image-upload.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/new/back-end/js/multiple-image-upload.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/new/back-end/js/file.upload.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/new/back-end/js/multiple_file_upload.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/backend/multiple-file-upload.js') }}"></script>

<script src="{{ dynamicAsset(path: 'public/assets/new/back-end/js/product.js') }}"></script>

<script src="{{ dynamicAsset(path: 'public/assets/backend/admin/js/promotion/offers-and-deals.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/backend/admin/js/promotion-management/coupon.js') }}"></script>

<script src="{{ dynamicAsset(path: 'public/assets/new/back-end/js/script.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/new/back-end/js/script-extended.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/backend/common-script.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/backend/admin/js/custom.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/new/back-end/js/custom_old.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/new/back-end/js/app-utils.js') }}"></script>

<script src="{{ dynamicAsset(path: 'public/assets/backend/admin/js/common/custom-modal-plugin.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/backend/admin/js/auto-load-func.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/backend/admin/js/advance-search/keyword-highlight.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/backend/admin/js/AI/products/ai-sidebar.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/backend/admin/js/AI/image-compressor/image-compressor.js') }}">
</script>
<script src="{{ dynamicAsset(path: 'public/assets/backend/admin/js/AI/image-compressor/compressor.min.js') }}">
</script>

<script src="{{ dynamicAsset(path: 'public/assets/backend/file-validation/polyfills.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/backend/file-validation/just-validate.min.js') }}"></script>
<script src="{{ dynamicAsset(path: 'public/assets/backend/file-validation/form-advance-validation.js') }}"></script>

<script src="{{ dynamicAsset(path: 'public/assets/backend/backend-utils.js') }}"></script>

{!! ToastMagic::scripts() !!}

@if ($errors->any())
    <script>
        'use strict';
        @foreach ($errors->all() as $index => $error)
            setTimeout(function() {
                toastMagic.error('{{ $error }}');
            }, {{ $index * 500 }});
        @endforeach
    </script>
@endif


@include('layouts.admin.partials._firebase-script')

<script>
    let placeholderImageUrl = "{{ dynamicAsset(path: 'public/assets/new/back-end/img/svg/image-upload.svg') }}";
    const iconPath = "{{ dynamicAsset(path: 'public/assets/new/back-end/img/icons/file.svg') }}";
</script>

@if (App\Utils\Helpers::module_permission_check('order_management') &&
        (in_array(request()->ip(), ['127.0.0.1', '::1']) ? true : env('APP_MODE') != 'dev'))
    <script>
        'use strict'
        let getInitialDataForPanelTime = parseInt(
            $('#get-initial-data-for-panel-time').data('value'),
            10
        );
        setInterval(function() {
            getInitialDataForPanel();
        }, getInitialDataForPanelTime);
    </script>
@endif

@if (env('APP_MODE') == 'demo')
    <script>
        'use strict'

        function checkDemoResetTime() {
            let currentMinute = new Date().getMinutes();
            if (currentMinute > 55 && currentMinute <= 60) {
                $('#demo-reset-warning').addClass('active');
            } else {
                $('#demo-reset-warning').removeClass('active');
            }
        }
        checkDemoResetTime();
        setInterval(checkDemoResetTime, 60000);

        window.addEventListener('scroll', function() {
            if (window.scrollY > 100) {
                document.body.classList.add('page-scrolled');
            } else {
                document.body.classList.remove('page-scrolled');
            }
        });
    </script>
@endif

{{-- Reverb / WebSocket real-time admin inbox notifications --}}
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
    'use strict';
    (function() {
        var chattingNewNotificationAlert = $('#chatting-new-notification-check');
        var chattingNewNotificationAlertMsg = $('#chatting-new-notification-check-message');

        function showChatNotification(message) {
            chattingNewNotificationAlertMsg.html(message);
            chattingNewNotificationAlert.addClass('active');
            if (typeof playAudio === 'function') {
                playAudio();
            }
            setTimeout(function() {
                chattingNewNotificationAlert.removeClass('active');
            }, 5000);
        }

        // --- WebSocket via Reverb ---
        var reverbKey = '{{ config('broadcasting.connections.reverb.key') }}';
        if (reverbKey) {
            try {
                var pusher = new Pusher(reverbKey, {
                    wsHost: '{{ config('broadcasting.connections.reverb.options.host', '127.0.0.1') }}',
                    wsPort: {{ (int) config('broadcasting.connections.reverb.options.port', 8080) }},
                    wssPort: {{ (int) config('broadcasting.connections.reverb.options.port', 8080) }},
                    forceTLS: {{ config('broadcasting.connections.reverb.options.useTLS', false) ? 'true' : 'false' }},
                    enabledTransports: ['ws', 'wss'],
                    cluster: '',
                    authEndpoint: '/admin/broadcasting/auth',
                    auth: {
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
                        }
                    }
                });

                var channel = pusher.subscribe('private-admin.chat');
                channel.bind('chatting', function(data) {
                    showChatNotification(data.message || '{{ translate('New_Message') }}');
                });

                // --- Order status change notifications ---
                var orderChannel = pusher.subscribe('private-admin.orders');
                orderChannel.bind('order-status-changed', function(data) {
                    var orderAlert = $('#order-status-notification');
                    var orderAlertMsg = $('#order-status-notification-message');
                    var orderAlertLink = $('#order-status-notification-link');
                    orderAlertMsg.html(data.message || '{{ translate('Order_Status_Changed') }}');
                    if (data.order_id) {
                        orderAlertLink.attr('href', '{{ url('/admin/orders/details') }}/' + data.order_id);
                    }
                    orderAlert.addClass('active');
                    if (typeof playAudio === 'function') {
                        playAudio();
                    }
                    setTimeout(function() {
                        orderAlert.removeClass('active');
                    }, 6000);
                });


            } catch (e) {
                console.warn('Reverb connection failed, falling back to polling.', e);
            }
        }

        // --- Polling fallback (always runs, catches any missed messages) ---
        var adminChatNotifRoute = $('#getAdminChattingNewNotificationCheckRoute').data('route');
        if (adminChatNotifRoute) {
            setInterval(function() {
                $.get({
                    url: adminChatNotifRoute,
                    dataType: 'json',
                    success: function(response) {
                        if (response.newMessagesExist !== 0 && response.message) {
                            showChatNotification(response.message);
                        }
                    }
                });
            }, 20000);
        }

        // --- Support ticket polling (always runs, independent of APP_MODE) ---
        var realTimeRoute = $('#route-for-real-time-activities').data('route');
        if (realTimeRoute) {
            setInterval(function() {
                $.get({
                    url: realTimeRoute,
                    dataType: 'json',
                    success: function(response) {
                        var openTicketCount = response.open_support_ticket_count;
                        if (typeof openTicketCount === 'undefined') {
                            return;
                        }
                        var prevTicketCount = parseInt(sessionStorage.getItem(
                            'bs_prev_open_ticket_count') ?? '-1', 10);
                        if (prevTicketCount === -1) {
                            sessionStorage.setItem('bs_prev_open_ticket_count',
                            openTicketCount);
                            return;
                        }
                        if (openTicketCount > prevTicketCount) {
                            sessionStorage.setItem('bs_prev_open_ticket_count',
                            openTicketCount);
                            var diff = openTicketCount - prevTicketCount;
                            var ticketAlert = $('#support-ticket-notification');
                            var ticketAlertLink = $('#support-ticket-notification-link');
                            $('#support-ticket-notification-message').html(
                                diff > 1 ? diff +
                                ' {{ translate('New_Support_Tickets') }}' :
                                '{{ translate('New_Support_Ticket') }}'
                            );
                            ticketAlertLink.attr('href',
                                '{{ route('admin.support-ticket.view') }}');
                            ticketAlert.addClass('active');
                            if (typeof playAudio === 'function') {
                                playAudio();
                            }
                            setTimeout(function() {
                                ticketAlert.removeClass('active');
                            }, 7000);
                        } else {
                            sessionStorage.setItem('bs_prev_open_ticket_count',
                            openTicketCount);
                        }
                        // keep sidebar badge in sync
                        if (typeof updateBadge === 'function') {
                            updateBadge('#sidebar-support-ticket-badge', openTicketCount);
                        }

                        // --- Location request (city/area) polling ---
                        var locationRequestCount = response.pending_location_request_count;
                        if (typeof locationRequestCount !== 'undefined') {
                            var prevLocationCount = parseInt(sessionStorage.getItem(
                                'bs_prev_location_request_count') ?? '-1', 10);
                            if (prevLocationCount === -1) {
                                sessionStorage.setItem('bs_prev_location_request_count',
                                locationRequestCount);
                            } else if (locationRequestCount > prevLocationCount) {
                                sessionStorage.setItem('bs_prev_location_request_count',
                                locationRequestCount);
                                var locDiff = locationRequestCount - prevLocationCount;
                                var locAlert = $('#location-request-notification');
                                var locAlertLink = $('#location-request-notification-link');
                                $('#location-request-notification-message').html(
                                    locDiff > 1 ? locDiff +
                                    ' {{ translate('New_Location_Requests') }}' :
                                    '{{ translate('New_Location_Request') }}'
                                );
                                locAlertLink.attr('href',
                                    '{{ route('admin.business-settings.location.city-requests') }}');
                                locAlert.addClass('active');
                                if (typeof playAudio === 'function') {
                                    playAudio();
                                }
                                setTimeout(function() {
                                    locAlert.removeClass('active');
                                }, 7000);
                            } else {
                                sessionStorage.setItem('bs_prev_location_request_count',
                                locationRequestCount);
                            }
                        }
                    }
                });
            }, 30000);
        }
    }());
</script>
