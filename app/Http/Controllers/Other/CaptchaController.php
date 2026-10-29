<?php

namespace App\Http\Controllers\Other;

use App\Http\Controllers\Controller;

class CaptchaController extends Controller
{
    public function reload()
    {
        return response()->json(['captcha' => captcha_img()]);
    }
}
