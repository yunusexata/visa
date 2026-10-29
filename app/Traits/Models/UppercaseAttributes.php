<?php

namespace App\Traits\Models;

trait UppercaseAttributes
{
    protected static function bootUppercaseAttributes()
    {
        static::saving(function ($model) {

            foreach ($model->uppercase ?? [] as $attribute) {

                if (! is_null($model->{$attribute})) {
                    $model->{$attribute} = mb_strtoupper($model->{$attribute});
                }
            }
        });
    }
}
