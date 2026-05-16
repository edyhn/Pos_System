@extends('layouts.app')

@section('title', 'Detail Produk')

@section('content')
<div class="max-w-7xl mx-auto" x-data="{ tab: 'stock' }">
    <div class="flex justify-between items-center mb-4">
        <div class="flex items-center gap-3">
            <h1 class="text-2xl font-bold text-gray-800">{{ $product->name }}</h1>
            <span class="px-2 py-1 text-xs rounded-full {{ $product->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
            </span>
            @if($product->is_subscription)
                <span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-700">Langganan {{ $product->subscription_days }} hr</span>
            @endif
        </div>
        <div class="flex gap-2">
            <a href="{{ route('products.edit', $product) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">Edit</a>
            <a href="{{ route('products.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">Kembali</a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <div class="grid grid-cols-2 lg:grid-cols-3 gap-6">
            <div>
                <p class="text-xs text-gray-500">Kategori</p>
                <p class="text-sm font-medium text-gray-800">{{ $product->category?->name ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">SKU</p>
                <p class="text-sm font-medium text-gray-800">{{ $product->sku ?? '-' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Barcode</p>
                <p class="text-sm font-medium text-gray-800 font-mono">{{ $product->barcode ?? '-' }}</p>
                @if($product->barcode)
                    <div class="mt-2 p-2 bg-white border border-gray-100 rounded-lg inline-block">
                        {!! \App\Services\BarcodeService::toSVG($product->barcode, 1.5, 45) !!}
                    </div>
                @endif
            </div>
            <div>
                <p class="text-xs text-gray-500">Harga Jual</p>
                <p class="text-sm font-medium text-gray-800">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Harga Modal</p>
                <p class="text-sm font-medium text-gray-800">Rp {{ number_format($product->cost_price ?? 0, 0, ',', '.') }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Stok / Min Stok</p>
                <p class="text-sm font-medium {{ $product->isLowStock() ? 'text-red-600' : 'text-gray-800' }}">{{ $product->stock }} / {{ $product->min_stock }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Satuan</p>
                <p class="text-sm font-medium text-gray-800">{{ $product->unit }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500">Kena Pajak</p>
                <p class="text-sm font-medium text-gray-800">{{ $product->is_taxed ? 'Ya (' . $product->tax_rate . '%)' : 'Tidak' }}</p>
            </div>
            @if($product->is_subscription)
            <div>
                <p class="text-xs text-gray-500">Masa Langganan</p>
                <p class="text-sm font-medium text-gray-800">{{ $product->subscription_days }} hari</p>
            </div>
            @endif
        </div>

        @if($product->image)
        <div class="mt-4">
            <p class="text-xs text-gray-500 mb-1">Gambar</p>
            <img src="{{ Storage::url($product->image) }}" class="h-48 object-contain border rounded-lg">
        </div>
        @endif

        @if($product->description)
        <div class="mt-4">
            <p class="text-xs text-gray-500 mb-1">Deskripsi</p>
            <p class="text-sm text-gray-700">{{ $product->description }}</p>
        </div>
        @endif
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="border-b border-gray-100">
            <nav class="flex">
                <button @@click="tab = 'stock'" :class="tab === 'stock' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500'" class="px-4 py-3 text-sm font-medium">Riwayat Stok</button>
                <button @@click="tab = 'transaction'" :class="tab === 'transaction' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500'" class="px-4 py-3 text-sm font-medium">Transaksi</button>
                <button @@click="tab = 'opname'" :class="tab === 'opname' ? 'border-b-2 border-blue-600 text-blue-600' : 'text-gray-500'" class="px-4 py-3 text-sm font-medium">Stock Opname</button>
            </nav>
        </div>

        <div x-show="tab === 'stock'" class="p-4">
            @if($product->stockMovements->count())
            <table class="w-full">
                <thead><tr class="bg-gray-50 text-left text-xs text-gray-500"><th class="px-3 py-2 font-medium">Tanggal</th><th class="px-3 py-2 font-medium">Tipe</th><th class="px-3 py-2 font-medium text-right">Qty</th><th class="px-3 py-2 font-medium">Referensi</th><th class="px-3 py-2 font-medium">User</th><th class="px-3 py-2 font-medium">Catatan</th></tr></thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @foreach($product->stockMovements as $m)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 text-gray-500">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-3 py-2"><span class="px-1.5 py-0.5 text-xs rounded-full {{ $m->type === 'in' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">{{ $m->type === 'in' ? 'Masuk' : 'Keluar' }}</span></td>
                        <td class="px-3 py-2 text-right font-medium {{ $m->type === 'in' ? 'text-green-600' : 'text-red-600' }}">{{ $m->type === 'in' ? '+' : '-' }}{{ $m->quantity }}</td>
                        <td class="px-3 py-2 text-gray-500">{{ $m->reference_type }}</td>
                        <td class="px-3 py-2 text-gray-500">{{ $m->user?->name ?? '-' }}</td>
                        <td class="px-3 py-2 text-gray-500 max-w-xs truncate">{{ $m->note ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p class="text-gray-400 text-sm text-center py-8">Belum ada data.</p>
            @endif
        </div>

        <div x-show="tab === 'transaction'" class="p-4">
            @if($product->transactionItems->count())
            <table class="w-full">
                <thead><tr class="bg-gray-50 text-left text-xs text-gray-500"><th class="px-3 py-2 font-medium">Invoice</th><th class="px-3 py-2 font-medium">Tanggal</th><th class="px-3 py-2 font-medium text-right">Qty</th><th class="px-3 py-2 font-medium text-right">Total</th></tr></thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @foreach($product->transactionItems as $ti)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 text-gray-800 font-mono">{{ $ti->transaction->invoice_number }}</td>
                        <td class="px-3 py-2 text-gray-500">{{ $ti->transaction->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-3 py-2 text-right text-gray-800">{{ $ti->quantity }}</td>
                        <td class="px-3 py-2 text-right text-gray-800">Rp {{ number_format($ti->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p class="text-gray-400 text-sm text-center py-8">Belum ada data.</p>
            @endif
        </div>

        <div x-show="tab === 'opname'" class="p-4">
            @if($product->stockOpnameItems->count())
            <table class="w-full">
                <thead><tr class="bg-gray-50 text-left text-xs text-gray-500"><th class="px-3 py-2 font-medium">Tanggal</th><th class="px-3 py-2 font-medium text-right">Stok Sistem</th><th class="px-3 py-2 font-medium text-right">Stok Fisik</th><th class="px-3 py-2 font-medium text-right">Selisih</th></tr></thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @foreach($product->stockOpnameItems as $oi)
                    <tr class="hover:bg-gray-50">
                        <td class="px-3 py-2 text-gray-500">{{ $oi->stockOpname->date->format('d/m/Y') }}</td>
                        <td class="px-3 py-2 text-right text-gray-800">{{ $oi->system_stock }}</td>
                        <td class="px-3 py-2 text-right text-gray-800">{{ $oi->actual_stock }}</td>
                        <td class="px-3 py-2 text-right font-medium {{ $oi->difference > 0 ? 'text-green-600' : ($oi->difference < 0 ? 'text-red-600' : 'text-gray-800') }}">{{ $oi->difference > 0 ? '+' : '' }}{{ $oi->difference }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p class="text-gray-400 text-sm text-center py-8">Belum ada data.</p>
            @endif
        </div>
    </div>
</div>
@endsection
