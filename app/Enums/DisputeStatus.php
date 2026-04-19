<?php

namespace App\Enums;

enum DisputeStatus
{
    const OPEN = 'open';

    const VENDOR_RESPONSE = 'vendor_response';

    const UNDER_REVIEW = 'under_review';

    const RESOLVED_REFUND = 'resolved_refund';

    const RESOLVED_RELEASE = 'resolved_release';

    const CLOSED = 'closed';

    const AUTO_CLOSED = 'auto_closed';

    const PENDING_CLOSURE = 'pending_closure';
}
