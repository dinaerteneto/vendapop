<?php

namespace App\Domain\Payment;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
    case Refunded = 'refunded';
    case Cancelled = 'cancelled';
}
