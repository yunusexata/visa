<?php

namespace App\Repositories\Client;

use App\Models\Client\ClientMagicLink;
use App\Repositories\MasterDataRepository;

class ClientMagicLinkRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return ClientMagicLink::class;
    }

    public static function datatable()
    {
        return ClientMagicLink::query();
    }
}
