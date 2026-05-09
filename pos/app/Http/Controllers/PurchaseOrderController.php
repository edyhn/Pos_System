<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        return view('purchase-orders.index');
    }

    public function create()
    {
        return view('purchase-orders.create');
    }

    public function show(PurchaseOrder $purchase_order)
    {
        $purchase_order->load('items.product', 'vendor', 'user');
        return view('purchase-orders.show', compact('purchase_order'));
    }

    public function edit(PurchaseOrder $purchase_order)
    {
        return view('purchase-orders.edit', compact('purchase_order'));
    }
}
