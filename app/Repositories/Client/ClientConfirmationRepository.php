<?php

namespace App\Repositories\Client;

use App\Models\Client\ClientConfirmation;
use App\Repositories\MasterDataRepository;

class ClientConfirmationRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return ClientConfirmation::class;
    }

    public static function datatable()
    {
        return ClientConfirmation::query();
    }
}
