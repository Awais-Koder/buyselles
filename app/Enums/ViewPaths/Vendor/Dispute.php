<?php

namespace App\Enums\ViewPaths\Vendor;

class Dispute
{
    const INDEX = [
        URI => 'list',
        VIEW => 'vendor-views.dispute.list',
    ];

    const DETAIL = [
        URI => 'detail',
        VIEW => 'vendor-views.dispute.details',
    ];
}
