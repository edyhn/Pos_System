@extends('layouts.app')
@section('title', 'PO ' . $purchase_order->po_number)
@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Detail Purchase Order</h1>
        <div class="flex gap-2">
            @if($purchase_order->status === 'draft')
                <a href="{{ route('purchase-orders.edit', $purchase_order) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">Edit</a>
            @endif
            <a href="{{ route('purchase-orders.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">Kembali</a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <p class="text-xs text-gray-500">No. PO</p>
                <p class="font-mono font-semibold text-gray-800">{{ $purchase_order->po_number }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Tanggal</p>
                <p class="text-gray-800">{{ $purchase_order->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Vendor</p>
                <p class="text-gray-800">{{ $purchase_order->vendor?->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Pembuat</p>
                <p class="text-gray-800">{{ $purchase_order->user?->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Status</p>
                <span class="px-2 py-1 text-xs rounded-full
                    @if($purchase_order->status == 'draft') bg-yellow-100 text-yellow-700
                    @elseif($purchase_order->status == 'sent') bg-blue-100 text-blue-700
                    @elseif($purchase_order->status == 'received') bg-green-100 text-green-700
                    @else bg-red-100 text-red-700 @endif">
                    {{ $purchase_order->status }}
                </span>
                @if($purchase_order->is_auto_draft)
                    <span class="text-xs text-gray-400 ml-1">(auto)</span>
                @endif
            </div>
            @if($purchase_order->notes)
            <div class="col-span-2">
                <p class="text-xs text-gray-500">Catatan</p>
                <p class="text-gray-700">{{ $purchase_order->notes }}</p>
            </div>
            @endif
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-3 border-b border-gray-100 bg-gray-50">
            <h2 class="font-semibold text-gray-700">Item</h2>
        </div>
        <table class="w-full">
            <thead>
                <tr class="text-left text-sm text-gray-500">
                    <th class="px-6 py-3 font-medium">Produk</th>
                    <th class="px-6 py-3 font-medium text-right">Qty</th>
                    <th class="px-6 py-3 font-medium text-right">Harga</th>
                    <th class="px-6 py-3 font-medium text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @php $total = 0; @endphp
                @foreach ($purchase_order->items as $item)
                    @php $total += $item->subtotal; @endphp
                    <tr class="text-sm">
                        <td class="px-6 py-3 text-gray-800">{{ $item->product->name ?? 'Produk #' . $item->product_id }}</td>
                        <td class="px-6 py-3 text-right text-gray-600">{{ $item->quantity }}</td>
                        <td class="px-6 py-3 text-right text-gray-600">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                        <td class="px-6 py-3 text-right font-medium">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="text-sm font-bold border-t-2 border-gray-200">
                    <td colspan="3" class="px-6 py-3 text-right text-gray-700">Total</td>
                    <td class="px-6 py-3 text-right text-gray-800">Rp {{ number_format($total, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection
