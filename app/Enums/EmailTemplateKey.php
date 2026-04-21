<?php

namespace App\Enums;

enum EmailTemplateKey
{
    const ADD_FUND_TO_WALLET = 'add-fund-to-wallet';

    const REGISTRATION = 'registration';

    const REGISTRATION_APPROVED = 'registration-approved';

    const REGISTRATION_DENIED = 'registration-denied';

    const ACCOUNT_SUSPENDED = 'account-suspended';

    const ACCOUNT_ACTIVATION = 'account-activation';

    const ACCOUNT_BLOCK = 'account-block';

    const ACCOUNT_UNBLOCK = 'account-unblock';

    const DIGITAL_PRODUCT_DOWNLOAD = 'digital-product-download';

    const DIGITAL_PRODUCT_OTP = 'digital-product-otp';

    const ORDER_PLACE = 'order-place';

    const ORDER_DELIVERED = 'order-delivered';

    const ORDER_RECEIVED = 'order-received';

    const FORGET_PASSWORD = 'forgot-password';

    const REGISTRATION_VERIFICATION = 'registration-verification';

    const REGISTRATION_FROM_POS = 'registration-from-pos';

    const RESET_PASSWORD_VERIFICATION = 'reset-password-verification';

    const ORDER_CONFIRMED = 'order-confirmed';

    const ORDER_PROCESSING = 'order-processing';

    const ORDER_OUT_FOR_DELIVERY = 'order-out-for-delivery';

    const ORDER_RETURNED = 'order-returned';

    const ORDER_FAILED = 'order-failed';

    const ORDER_CANCELED = 'order-canceled';

    const DISPUTE_ESCALATED = 'dispute-escalated';

    const DISPUTE_RESOLVED = 'dispute-resolved';

    const ADMIN_EMAIL_LIST = [
        EmailTemplateKey::ORDER_RECEIVED,
        EmailTemplateKey::DISPUTE_ESCALATED,
    ];

    const VENDOR_EMAIL_LIST = [
        EmailTemplateKey::REGISTRATION,
        EmailTemplateKey::REGISTRATION_APPROVED,
        EmailTemplateKey::REGISTRATION_DENIED,
        EmailTemplateKey::ACCOUNT_SUSPENDED,
        EmailTemplateKey::ACCOUNT_ACTIVATION,
        EmailTemplateKey::FORGET_PASSWORD,
        EmailTemplateKey::ORDER_RECEIVED,
        EmailTemplateKey::DISPUTE_ESCALATED,
        EmailTemplateKey::DISPUTE_RESOLVED,
    ];

    const CUSTOMER_EMAIL_LIST = [
        EmailTemplateKey::ORDER_PLACE,
        EmailTemplateKey::ORDER_CONFIRMED,
        EmailTemplateKey::ORDER_PROCESSING,
        EmailTemplateKey::ORDER_OUT_FOR_DELIVERY,
        EmailTemplateKey::ORDER_DELIVERED,
        EmailTemplateKey::ORDER_RETURNED,
        EmailTemplateKey::ORDER_FAILED,
        EmailTemplateKey::ORDER_CANCELED,
        EmailTemplateKey::FORGET_PASSWORD,
        EmailTemplateKey::REGISTRATION_VERIFICATION,
        EmailTemplateKey::REGISTRATION_FROM_POS,
        EmailTemplateKey::ACCOUNT_BLOCK,
        EmailTemplateKey::ACCOUNT_UNBLOCK,
        EmailTemplateKey::DIGITAL_PRODUCT_DOWNLOAD,
        EmailTemplateKey::DIGITAL_PRODUCT_OTP,
        EmailTemplateKey::ADD_FUND_TO_WALLET,
        EmailTemplateKey::DISPUTE_ESCALATED,
        EmailTemplateKey::DISPUTE_RESOLVED,
    ];

    const DELIVERY_MAN_EMAIL_LIST = [
        EmailTemplateKey::RESET_PASSWORD_VERIFICATION,
    ];
}
