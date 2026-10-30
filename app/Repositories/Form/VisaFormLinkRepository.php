<?php

namespace App\Repositories\Form;

use App\Models\Form\VisaFormLink;
use App\Repositories\MasterDataRepository;

class VisaFormLinkRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return VisaFormLink::class;
    }

    public static function datatable()
    {
        return VisaFormLink::query();
    }
}
