<?php

namespace App\Http\Controllers;

class VendorController extends Controller
{
    public function index()
    {
        return view('vendors.index');
    }

    public function create()
    {
        return view('vendors.create');
    }

    public function edit(string $id)
    {
        return view('vendors.edit', compact('id'));
    }
}
