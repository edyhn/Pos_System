<?php

namespace App\Http\Controllers;

class StockOpnameController extends Controller
{
    public function index()
    {
        return view('stock-opname.index');
    }

    public function create()
    {
        return view('stock-opname.create');
    }
}
