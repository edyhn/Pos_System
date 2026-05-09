<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    public function receipt()
    {
        return view('approvals.receipt');
    }

    public function refund()
    {
        return view('approvals.refund');
    }
}
