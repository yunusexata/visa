<?php

namespace App\Repositories\Client;

use App\Models\Client\ClientEsign;
use App\Repositories\MasterDataRepository;

class ClientEsignRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return ClientEsign::class;
    }

    public static function datatable()
    {
        return ClientEsign::query();
    }
}
