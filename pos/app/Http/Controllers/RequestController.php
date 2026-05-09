<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RequestController extends Controller
{
    public function receipt()
    {
        return view('requests.receipt');
    }

    public function refund()
    {
        return view('requests.refund');
    }
}
