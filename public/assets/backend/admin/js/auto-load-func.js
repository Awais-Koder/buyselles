"use strict";

document.addEventListener('DOMContentLoaded', function () {
    const offcanvasEl = document.getElementById('offcanvasSetupGuide');

    if (offcanvasEl && offcanvasEl.getAttribute('data-status') === 'show') {
        const bsOffcanvas = new bootstrap.Offcanvas(offcanvasEl);
        setTimeout(() => {
            bsOffcanvas.show();
        }, 500)
    }

    loadSearchCmdKeys();
});

var audio = document.getElementById("myAudio");
function playAudio() {
    audio.play();
}

function loadSearchCmdKeys() {
    const isMac = navigator.platform.toUpperCase().includes('MAC');
    const shortcutKeys = document.querySelectorAll('.search-shortcut-key');

    shortcutKeys.forEach(el => {
        el.textContent = isMac ? '⌘+K' : 'Ctrl+K';
    });
}

function getInitialDataForPanel() {
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="_token"]').attr("content")
        }
    });
    $.ajax({
        url: $("#route-for-real-time-activities").data("route"),
        type: "GET",
        data: {},
        dataType: "json",
        success: function (response) {
            if (response?.new_order_count > 0) {
                playAudio();
                $("#popup-modal").appendTo("body").modal("show");
            }

            if (document.cookie.indexOf("6valley_restock_request_status=accepted") !== -1 || document.cookie.indexOf("6valley_restock_request_status=reject") !== -1) {
                $(".product-restock-stock-alert").hide();
            } else {
                if (response?.restockProductCount > 0 && response?.restockProduct) {
                    productRestockStockLimitStatus(response?.restockProduct);
                }
            }

            // Real-time badge updates — pending orders
            updateBadge('#sidebar-pending-order-badge', response?.pending_order_count);
            updateBadge('#header-pending-order-badge', response?.pending_order_count);

            // Real-time badge updates — unread chats & contacts
            updateBadge('#header-unread-chat-badge', response?.unread_chat_count);
            updateBadge('#header-unread-contact-badge', response?.unread_contact_count);

            // Real-time order status counts — sidebar
            updateOrderCount('#sidebar-all-order-count', response?.all_order_count);
            updateOrderCount('#sidebar-pending-order-count', response?.pending_order_count);
            updateOrderCount('#sidebar-confirmed-order-count', response?.confirmed_order_count);
            updateOrderCount('#sidebar-processing-order-count', response?.processing_order_count);
            updateOrderCount('#sidebar-out-for-delivery-order-count', response?.out_for_delivery_order_count);
            updateOrderCount('#sidebar-delivered-order-count', response?.delivered_order_count);
            updateOrderCount('#sidebar-returned-order-count', response?.returned_order_count);
            updateOrderCount('#sidebar-failed-order-count', response?.failed_order_count);
            updateOrderCount('#sidebar-canceled-order-count', response?.canceled_order_count);

            // Real-time order status counts — order list summary cards
            updateOrderCount('#order-stats-pending', response?.pending_order_count);
            updateOrderCount('#order-stats-confirmed', response?.confirmed_order_count);
            updateOrderCount('#order-stats-processing', response?.processing_order_count);
            updateOrderCount('#order-stats-out-for-delivery', response?.out_for_delivery_order_count);
            updateOrderCount('#order-stats-delivered', response?.delivered_order_count);
            updateOrderCount('#order-stats-canceled', response?.canceled_order_count);
            updateOrderCount('#order-stats-returned', response?.returned_order_count);
            updateOrderCount('#order-stats-failed', response?.failed_order_count);
        }
    });
}

function updateBadge(selector, count) {
    var $badge = $(selector);
    if ($badge.length === 0) return;
    if (count > 0) {
        $badge.text(count > 99 ? '99+' : count).show();
    } else {
        $badge.text('0').hide();
    }
}

function updateOrderCount(selector, count) {
    var $el = $(selector);
    if ($el.length === 0) return;
    var displayCount = (count !== undefined && count !== null) ? count : 0;
    $el.text(displayCount);
}
