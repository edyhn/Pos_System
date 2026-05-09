<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    public function in()
    {
        return view('stock-movement.in');
    }

    public function out()
    {
        return view('stock-movement.out');
    }
}
