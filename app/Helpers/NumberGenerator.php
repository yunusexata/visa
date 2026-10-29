<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NumberGenerator
{
    const COMPANY_CODE = "BOOK";
    const RESET_TYPE_YEARLY = 1;
    const RESET_TYPE_MONTHLY = 2;
    const RESET_TYPE_DAILY = 3;
    const SEPARATOR = "/";

    public static function generate(
        $className,
    ) {

        $lastModel = $className::withTrashed()->select('id_customer')
            ->orderBy('id', 'DESC')
            ->lockForUpdate()
            ->first();
        if (!empty($lastModel)) {
            $lastNumber = substr($lastModel->id_customer, 3);
            $lastNumber = $lastNumber ? $lastNumber : 0;
        } else {
            $lastNumber = 0;
        }

        // Get Current Number
        $currentNumber = str_pad(strval($lastNumber + 1), 5, "0", STR_PAD_LEFT);

        $kode_huruf = strtoupper(Str::random(3));

        return $kode_huruf . $currentNumber;
    }


    public static function simpleYearCode(
        $className,
        $code,
        $date,
        $zeroPad = 6,
    ) {
        $dateTime = Carbon::parse($date);
        $year = substr($dateTime->year, 2);

        $lastModel = $className::withTrashed()->select('number')
            ->orderBy('id', 'DESC')
            ->first();

        if (!empty($lastModel)) {
            $lastNumber = intval(substr($lastModel->number, 4));
        } else {
            $lastNumber = 0;
        }

        // Get Current Number
        $currentNumber = strval($lastNumber + 1);
        $currentNumber = str_pad($currentNumber, $zeroPad, "0", STR_PAD_LEFT);

        return "{$code}{$year}{$currentNumber}";
    }
}
