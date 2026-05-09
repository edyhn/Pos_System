<div>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-gray-800">Produk</h1>
        <a href="{{ route('products.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">+ Tambah Produk</a>
    </div>

    @if (session('message'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">{{ session('message') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="p-4 border-b border-gray-100 flex items-center gap-4">
            <input type="text" wire:model.live="search" placeholder="Cari produk..." class="flex-1 max-w-xs px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
            <label class="flex items-center gap-2 text-sm text-gray-600">
                <input type="checkbox" wire:model.live="showInactive" class="rounded border-gray-300 text-blue-600">
                Tampilkan nonaktif
            </label>
        </div>

        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 text-left text-sm text-gray-500">
                    <th class="px-4 py-3 font-medium">Nama</th>
                    <th class="px-4 py-3 font-medium">Kategori</th>
                    <th class="px-4 py-3 font-medium">SKU</th>
                    <th class="px-4 py-3 font-medium text-right">Harga</th>
                    <th class="px-4 py-3 font-medium text-right">Stok</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($products as $product)
                    <tr class="hover:bg-gray-50 text-sm {{ $product->isLowStock() ? 'bg-yellow-50' : '' }}">
                        <td class="px-4 py-3 font-medium text-gray-800">
                            {{ $product->name }}
                            @if($product->is_subscription)
                                <span class="ml-1 px-1.5 py-0.5 text-xs bg-purple-100 text-purple-700 rounded">Langganan</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $product->category->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $product->sku ?? '-' }}</td>
                        <td class="px-4 py-3 text-right text-gray-800 font-medium">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right {{ $product->isLowStock() ? 'text-red-600 font-bold' : 'text-gray-800' }}">
                            {{ $product->stock }}
                            @if($product->isLowStock())
                                <span class="ml-1 text-xs text-red-500">(min: {{ $product->min_stock }})</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <button wire:click="toggleActive({{ $product->id }})" class="px-2 py-1 text-xs rounded-full {{ $product->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                            </button>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('products.edit', $product) }}" class="text-blue-600 hover:text-blue-800">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-400">Belum ada produk.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        <div class="p-4 border-t border-gray-100">
            {{ $products->links() }}
        </div>
    </div>
</div>
