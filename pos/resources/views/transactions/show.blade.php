@extends('layouts.app')

@section('title', 'Detail Transaksi ' . $transaction->invoice_number)

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Detail Transaksi</h1>
        <div class="flex gap-2">
            <a href="{{ route('print.receipt', $transaction) }}" target="_blank" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">Cetak Struk</a>
            <a href="{{ route('transactions.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">Kembali</a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <p class="text-xs text-gray-500">Invoice</p>
                <p class="font-mono font-semibold text-gray-800">{{ $transaction->invoice_number }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Tanggal</p>
                <p class="text-gray-800">{{ $transaction->created_at->format('d/m/Y H:i') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Kasir</p>
                <p class="text-gray-800">{{ $transaction->user->name }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Customer</p>
                <p class="text-gray-800">{{ $transaction->customer_name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Pembayaran</p>
                <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-700">{{ strtoupper($transaction->payment_method) }}</span>
            </div>
            <div>
                <p class="text-xs text-gray-500">Status</p>
                <span class="px-2 py-1 text-xs rounded-full {{ $transaction->status == 'completed' ? 'bg-green-100 text-green-700' : ($transaction->status == 'refunded' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700') }}">
                    {{ $transaction->status }}
                </span>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-4">
        <div class="px-6 py-3 border-b border-gray-100 bg-gray-50">
            <h2 class="font-semibold text-gray-700">Item</h2>
        </div>
        <table class="w-full">
            <thead>
                <tr class="text-left text-sm text-gray-500">
                    <th class="px-6 py-3 font-medium">Produk</th>
                    <th class="px-6 py-3 font-medium text-center">Qty</th>
                    <th class="px-6 py-3 font-medium text-right">Harga</th>
                    <th class="px-6 py-3 font-medium text-right">Subtotal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($transaction->items as $item)
                    <tr class="text-sm">
                        <td class="px-6 py-3 text-gray-800">{{ $item->product_name }}</td>
                        <td class="px-6 py-3 text-center text-gray-600">{{ $item->quantity }}</td>
                        <td class="px-6 py-3 text-right text-gray-600">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                        <td class="px-6 py-3 text-right font-medium">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if($transaction->subscriptions->count())
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-4">
            <div class="px-6 py-3 border-b border-gray-100 bg-gray-50">
                <h2 class="font-semibold text-gray-700">Langganan Aktif</h2>
            </div>
            <table class="w-full">
                <thead>
                    <tr class="text-left text-sm text-gray-500">
                        <th class="px-6 py-3 font-medium">Produk</th>
                        <th class="px-6 py-3 font-medium">Customer</th>
                        <th class="px-6 py-3 font-medium">Mulai</th>
                        <th class="px-6 py-3 font-medium">Berakhir</th>
                        <th class="px-6 py-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach ($transaction->subscriptions as $sub)
                        <tr class="text-sm">
                            <td class="px-6 py-3 text-gray-800">{{ $sub->product->name }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $sub->customer_identifier }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $sub->start_date->format('d/m/Y') }}</td>
                            <td class="px-6 py-3 text-gray-600">{{ $sub->end_date->format('d/m/Y') }}</td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-1 text-xs rounded-full {{ $sub->status == 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                                    {{ $sub->status }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <div class="max-w-xs ml-auto space-y-2 text-sm">
            <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span>Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span></div>
            @if($transaction->tax_amount > 0)
                <div class="flex justify-between"><span class="text-gray-500">Pajak (PPN)</span><span>Rp {{ number_format($transaction->tax_amount, 0, ',', '.') }}</span></div>
            @endif
            <div class="flex justify-between text-base font-bold border-t pt-2"><span>Total</span><span>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span></div>
            <div class="flex justify-between"><span class="text-gray-500">Bayar ({{ strtoupper($transaction->payment_method) }})</span><span>Rp {{ number_format($transaction->payment_amount, 0, ',', '.') }}</span></div>
            @if($transaction->change_amount > 0)
                <div class="flex justify-between"><span class="text-gray-500">Kembali</span><span class="text-green-600 font-medium">Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</span></div>
            @endif
            @if($transaction->notes)
                <div class="border-t pt-2 mt-2"><span class="text-gray-500">Catatan:</span><p class="text-gray-700">{{ $transaction->notes }}</p></div>
            @endif
        </div>
    </div>
</div>
@endsection
