<?php

namespace App\Http\Controllers;

class ForecastController extends Controller
{
    public function sales()
    {
        return view('forecast.sales');
    }

    public function stock()
    {
        return view('forecast.stock');
    }
}
