<?php

namespace App\Repositories\Service;

use App\Models\Service\SendEmailLog;
use App\Repositories\MasterDataRepository;

class SendEmailLogRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return SendEmailLog::class;
    }

    public static function datatable()
    {
        return SendEmailLog::query();
    }
}
