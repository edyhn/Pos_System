<div>
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Forecast Stok & Reorder</h1>

    <div class="grid grid-cols-2 md:grid-cols-6 gap-3 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-3 text-center">
            <p class="text-xs text-gray-500">Total Produk</p>
            <p class="text-xl font-bold text-gray-800">{{ $summary['total'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-red-200 p-3 text-center">
            <p class="text-xs text-red-500">Habis / Kritis</p>
            <p class="text-xl font-bold text-red-600">{{ $summary['habis'] + $summary['kritis'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-3 text-center">
            <p class="text-xs text-orange-500">Menipis</p>
            <p class="text-xl font-bold text-orange-500">{{ $summary['menipis'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-green-200 p-3 text-center">
            <p class="text-xs text-green-500">Aman</p>
            <p class="text-xl font-bold text-green-600">{{ $summary['aman'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-blue-200 p-3 text-center">
            <p class="text-xs text-blue-500">Perlu Reorder</p>
            <p class="text-xl font-bold text-blue-600">{{ $summary['needs_reorder'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-purple-200 p-3 text-center">
            <p class="text-xs text-purple-500">Total Reorder Qty</p>
            <p class="text-xl font-bold text-purple-600">{{ $summary['total_reorder_qty'] }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <div class="flex flex-wrap gap-2 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Cari Produk</label>
                <input type="text" wire:model.live.debounce="search" placeholder="Nama atau SKU..."
                    class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Kategori</label>
                <select wire:model.live="categoryFilter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Semua</option>
                    @foreach ($this->categories as $c)
                        <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Lead Time (hari)</label>
                <select wire:model.live="leadTimeDays" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="3">3 Hari</option>
                    <option value="7">7 Hari</option>
                    <option value="14">14 Hari</option>
                    <option value="30">30 Hari</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Safety Stock</label>
                <select wire:model.live="safetyStock" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Urutkan</label>
                <select wire:model.live="sortBy" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="status">Status</option>
                    <option value="estimated_days">Estimasi Habis</option>
                    <option value="recommended_qty">Qty Reorder</option>
                </select>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 text-left text-sm text-gray-500">
                    <th class="px-4 py-3 font-medium">Produk</th>
                    <th class="px-4 py-3 font-medium">Kategori</th>
                    <th class="px-4 py-3 font-medium text-right">Stok</th>
                    <th class="px-4 py-3 font-medium text-right">Min Stok</th>
                    <th class="px-4 py-3 font-medium text-right">Rata-rata Jual/hari</th>
                    <th class="px-4 py-3 font-medium text-right">Estimasi Habis</th>
                    <th class="px-4 py-3 font-medium">Status</th>
                    <th class="px-4 py-3 font-medium text-right">Reorder Qty</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($forecasts as $f)
                    <tr class="hover:bg-gray-50 text-sm
                        @if($f->status == 'habis') bg-red-50
                        @elseif($f->status == 'kritis') bg-orange-50
                        @elseif($f->status == 'menipis') bg-yellow-50
                        @endif">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-800">{{ $f->product->name }}</p>
                            <p class="text-xs text-gray-400">{{ $f->product->sku }}</p>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $f->product->category->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-right font-medium
                            {{ $f->stock == 0 ? 'text-red-600' : ($f->stock <= $f->min_stock ? 'text-orange-500' : 'text-gray-800') }}">
                            {{ $f->stock }}
                        </td>
                        <td class="px-4 py-3 text-right text-gray-500">{{ $f->min_stock }}</td>
                        <td class="px-4 py-3 text-right text-gray-800">{{ $f->avg_daily_sales > 0 ? number_format($f->avg_daily_sales, 1) : '0' }}</td>
                        <td class="px-4 py-3 text-right">
                            @if($f->estimated_days !== null)
                                <span class="font-medium
                                    @if($f->estimated_days <= 0) text-red-600
                                    @elseif($f->estimated_days <= 7) text-orange-600
                                    @else text-gray-800 @endif">
                                    {{ $f->estimated_days }} hari
                                </span>
                                <p class="text-xs text-gray-400">{{ $f->estimated_date }}</p>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-full
                                @if($f->status == 'habis') bg-red-100 text-red-700
                                @elseif($f->status == 'kritis') bg-orange-100 text-orange-700
                                @elseif($f->status == 'menipis') bg-yellow-100 text-yellow-700
                                @elseif($f->status == 'aman') bg-green-100 text-green-700
                                @else bg-gray-100 text-gray-700 @endif">
                                {{ ucfirst($f->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($f->recommended_qty > 0)
                                <span class="font-bold text-blue-600">{{ $f->recommended_qty }}</span>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-gray-400">Tidak ada produk.</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-gray-100">{{ $products->links() }}</div>
    </div>
</div>
