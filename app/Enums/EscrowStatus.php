<?php

namespace App\Enums;

enum EscrowStatus
{
    const HELD = 'held';

    const RELEASED = 'released';

    const DISPUTED = 'disputed';

    const REFUNDED = 'refunded';
}
