<?php

namespace App\Repositories\Admin;

use App\Models\UpodSurveyResponse;
use App\Repositories\MasterDataRepository;

class UpodSurveyResponseRepository extends MasterDataRepository
{
    protected static function className(): string
    {
        return UpodSurveyResponse::class;
    }

    public static function datatable()
    {
        return UpodSurveyResponse::query();
    }
}
