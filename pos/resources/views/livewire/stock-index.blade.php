<div>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-gray-800">Stok Produk</h1>
        <div class="flex gap-2">
            <a href="{{ route('stock.export-excel') }}" class="px-3 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700">Excel</a>
            <a href="{{ route('stock.export-pdf') }}" class="px-3 py-2 bg-red-600 text-white rounded-lg text-sm hover:bg-red-700">PDF</a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-4 border-b border-gray-100 flex items-center gap-4">
            <input type="text" wire:model.live="search" placeholder="Cari produk..." class="flex-1 max-w-xs px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" wire:model.live="showLowStock" class="rounded border-gray-300 text-blue-600">
                Stok minimum saja
            </label>
        </div>

        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 text-left text-sm text-gray-500">
                    <th class="px-4 py-3 font-medium">Produk</th>
                    <th class="px-4 py-3 font-medium">SKU</th>
                    <th class="px-4 py-3 font-medium">Kategori</th>
                    <th class="px-4 py-3 font-medium text-right">Stok</th>
                    <th class="px-4 py-3 font-medium text-right">Min. Stok</th>
                    <th class="px-4 py-3 font-medium text-right">Harga</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($products as $product)
                    <tr class="hover:bg-gray-50 text-sm {{ $product->isLowStock() ? 'bg-red-50' : '' }}">
                        <td class="px-4 py-3 font-medium text-gray-800">{{ $product->name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $product->sku ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $product->category?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-right font-bold {{ $product->isLowStock() ? 'text-red-600' : 'text-gray-800' }}">{{ $product->stock }}</td>
                        <td class="px-4 py-3 text-right text-gray-500">{{ $product->min_stock }}</td>
                        <td class="px-4 py-3 text-right text-gray-800">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400">Tidak ada produk.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-gray-100">{{ $products->links() }}</div>
    </div>
</div>
