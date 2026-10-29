<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

class ReviewController extends Controller
{
    /**
     * Display a listing of client reviews.
     */
    public function index()
    {
        return view('admin.reviews.index');
    }
}
