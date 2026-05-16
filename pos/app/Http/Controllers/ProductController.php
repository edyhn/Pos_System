<?php

namespace App\Http\Controllers;

class ProductController extends Controller
{
    public function index()
    {
        return view('products.index');
    }

    public function create()
    {
        return view('products.create');
    }

    public function show($id)
    {
        $product = \App\Models\Product::where('store_id', auth()->user()->store_id)
            ->with(['category', 'stockMovements' => fn($q) => $q->with('user')->latest()->take(50)])
            ->with(['transactionItems' => fn($q) => $q->with('transaction')->latest()->take(50)])
            ->with(['stockOpnameItems' => fn($q) => $q->with('stockOpname')->latest()->take(50)])
            ->findOrFail($id);
        return view('products.show', compact('product'));
    }

    public function edit(\App\Models\Product $product)
    {
        return view('products.edit', compact('product'));
    }
}
