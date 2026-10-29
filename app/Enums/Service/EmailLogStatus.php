<?php

namespace App\Enums\Service;

enum EmailLogStatus: string
{
    case PENDING = 'pending';
    case SENDING = 'sending';
    case SENT = 'sent';
    case FAILED = 'failed';
}
