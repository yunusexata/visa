<?php

namespace App\Repositories\Client;

use App\Models\Client\ClientClosingHistory;
use App\Repositories\MasterDataRepository;

class ClientClosingHistoryRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return ClientClosingHistory::class;
    }

    public static function datatable()
    {
        return ClientClosingHistory::query();
    }
}
