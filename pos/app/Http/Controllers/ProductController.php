<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('products.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        return redirect()->route('products.index')->with('info', 'Gunakan form untuk menambah produk.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $product = \App\Models\Product::where('store_id', auth()->user()->store_id)
            ->with(['category', 'stockMovements' => fn($q) => $q->with('user')->latest()->take(50)])
            ->with(['transactionItems' => fn($q) => $q->with('transaction')->latest()->take(50)])
            ->with(['stockOpnameItems' => fn($q) => $q->with('stockOpname')->latest()->take(50)])
            ->findOrFail($id);
        return view('products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        return view('products.edit', compact('id'));
    }

    public function update(Request $request, string $id)
    {
        return redirect()->route('products.index')->with('info', 'Gunakan form untuk mengupdate produk.');
    }

    public function destroy(string $id)
    {
        return redirect()->route('products.index')->with('info', 'Gunakan fitur di halaman produk untuk menghapus.');
    }
}
