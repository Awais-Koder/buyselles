<?php
$minimumOrderAmountStatus = getWebConfig(name: 'minimum_order_amount_status');
$minimumOrderAmountByVendor = getWebConfig(name: 'minimum_order_amount_by_seller');
$freeDeliveryStatus = getWebConfig(name: 'free_delivery_status');
$freeDeliveryResponsibility = getWebConfig(name: 'free_delivery_responsibility');
?>
<div class="inline-page-menu my-4">
    <ul class="list-unstyled flex-wrap">
        <li class="{{ Request::is('vendor/shop/index') && !request()->has('pagetype') ? 'active' : '' }}">
            <a href="{{ route('vendor.shop.index') }}">
                {{ translate('Shop_Settings') }}
            </a>
        </li>
        {{-- Payment Information tab disabled — vendors must use admin-defined withdrawal methods only --}}
        <li class="{{ Request::is('vendor/shop/other-setup') ? 'active' : '' }}">
            <a href="{{ route('vendor.shop.other-setup') }}">
                {{ translate('Other_Setup') }}
            </a>
        </li>
        <li class="{{ Request::is('vendor/shop/service-areas*') ? 'active' : '' }}">
            <a href="{{ route('vendor.shop.service-areas.index') }}">
                {{ translate('Service_Areas') }}
            </a>
        </li>
    </ul>
</div>
