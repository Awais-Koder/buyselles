<?php

namespace App\Enums\ViewPaths\Admin;

enum Dispute
{
    const INDEX = [
        URI => 'dispute-list',
        VIEW => 'admin-views.dispute.list',
    ];

    const DETAIL = [
        URI => 'dispute-detail',
        VIEW => 'admin-views.dispute.details',
    ];

    const ESCROW_INDEX = [
        URI => 'escrow-list',
        VIEW => 'admin-views.escrow.list',
    ];

    const ESCROW_DETAIL = [
        URI => 'escrow-detail',
        VIEW => 'admin-views.escrow.show',
    ];
}
