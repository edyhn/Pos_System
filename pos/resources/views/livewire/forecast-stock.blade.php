<div>
    @if (session('message'))
        <div class="mb-4 flex items-center gap-2 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-sm">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('message') }}</span>
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
            </div>
            <div>
                <h1 class="text-lg font-bold text-gray-900">Forecast Stok & Reorder</h1>
                <p class="text-sm text-gray-500">Prediksi stok dan rekomendasi reorder barang</p>
            </div>
        </div>
        <button wire:click="previewPO" class="inline-flex items-center gap-1.5 px-4 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition text-sm font-medium shadow-sm {{ $summary['needs_reorder'] == 0 ? 'opacity-50 cursor-not-allowed' : '' }}" {{ $summary['needs_reorder'] == 0 ? 'disabled' : '' }}>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            Buat Draft PO ({{ $summary['needs_reorder'] }})
        </button>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-3 text-center">
            <p class="text-xs font-medium text-gray-500">Total Produk</p>
            <p class="text-xl font-bold text-gray-800">{{ $summary['total'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-red-200 p-3 text-center">
            <p class="text-xs font-medium text-red-500">Habis</p>
            <p class="text-xl font-bold text-red-600">{{ $summary['habis'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-orange-200 p-3 text-center">
            <p class="text-xs font-medium text-orange-500">Kritis</p>
            <p class="text-xl font-bold text-orange-600">{{ $summary['kritis'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-yellow-200 p-3 text-center">
            <p class="text-xs font-medium text-yellow-600">Menipis</p>
            <p class="text-xl font-bold text-yellow-600">{{ $summary['menipis'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-green-200 p-3 text-center">
            <p class="text-xs font-medium text-green-500">Aman</p>
            <p class="text-xl font-bold text-green-600">{{ $summary['aman'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-cyan-200 p-3 text-center">
            <p class="text-xs font-medium text-cyan-600">Ada Draft PO</p>
            <p class="text-xl font-bold text-cyan-600">{{ $summary['has_auto_po'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-blue-200 p-3 text-center">
            <p class="text-xs font-medium text-gray-500">Perlu Reorder</p>
            <p class="text-xl font-bold text-blue-600">{{ $summary['needs_reorder'] }}</p>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-purple-200 p-3 text-center">
            <p class="text-xs font-medium text-gray-500">Total Reorder</p>
            <p class="text-xl font-bold text-purple-600">{{ $summary['total_reorder_qty'] }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
        <div class="p-4 flex flex-wrap items-center gap-3">
            <div class="relative flex-1 min-w-[200px] max-w-xs">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" wire:model.live.debounce="search" placeholder="Cari nama atau SKU..." class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white">
            </div>
            <select wire:model.live="categoryFilter" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
                <option value="">Semua Kategori</option>
                @foreach ($this->categories as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                @endforeach
            </select>
            <select wire:model.live="leadTimeDays" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
                <option value="3">Lead Time 3 Hari</option>
                <option value="7">Lead Time 7 Hari</option>
                <option value="14">Lead Time 14 Hari</option>
                <option value="30">Lead Time 30 Hari</option>
            </select>
            <select wire:model.live="safetyStock" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
                <option value="5">Safety 5</option>
                <option value="10">Safety 10</option>
                <option value="20">Safety 20</option>
                <option value="50">Safety 50</option>
            </select>
            <select wire:model.live="sortBy" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
                <option value="status">Urut: Status</option>
                <option value="estimated_days">Urut: Estimasi Habis</option>
                <option value="recommended_qty">Urut: Qty Reorder</option>
            </select>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="px-4 py-3.5">Produk</th>
                        <th class="px-4 py-3.5">Kategori</th>
                        <th class="px-4 py-3.5 text-right">Stok</th>
                        <th class="px-4 py-3.5 text-right">Min Stok</th>
                        <th class="px-4 py-3.5 text-right">Rata Jual/hari</th>
                        <th class="px-4 py-3.5 text-right">Estimasi Habis</th>
                        <th class="px-4 py-3.5">Status</th>
                        <th class="px-4 py-3.5 text-right">Reorder</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($forecasts as $f)
                        <tr class="hover:bg-gray-50 text-sm transition
                            @if($f->status == 'habis') bg-red-50
                            @elseif($f->status == 'kritis') bg-orange-50
                            @elseif($f->status == 'menipis') bg-yellow-50
                            @endif">
                            <td class="px-4 py-3.5">
                                <p class="font-medium text-gray-800">{{ $f->product->name }}</p>
                                <p class="text-xs text-gray-400 font-mono">{{ $f->product->sku }}</p>
                            </td>
                            <td class="px-4 py-3.5 text-gray-500">{{ $f->product->category->name ?? '-' }}</td>
                            <td class="px-4 py-3.5 text-right font-medium
                                {{ $f->stock == 0 ? 'text-red-600' : ($f->stock <= $f->min_stock ? 'text-orange-500' : 'text-gray-800') }}">
                                {{ $f->stock }}
                            </td>
                            <td class="px-4 py-3.5 text-right text-gray-500">{{ $f->min_stock }}</td>
                            <td class="px-4 py-3.5 text-right text-gray-800">{{ $f->avg_daily_sales > 0 ? number_format($f->avg_daily_sales, 1) : '0' }}</td>
                            <td class="px-4 py-3.5 text-right">
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
                            <td class="px-4 py-3.5">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-full
                                    @if($f->status == 'habis') bg-red-100 text-red-700
                                    @elseif($f->status == 'kritis') bg-orange-100 text-orange-700
                                    @elseif($f->status == 'menipis') bg-yellow-100 text-yellow-700
                                    @elseif($f->status == 'aman') bg-green-100 text-green-700
                                    @else bg-gray-100 text-gray-700 @endif">
                                    <span class="w-1.5 h-1.5 rounded-full
                                        @if($f->status == 'habis') bg-red-500
                                        @elseif($f->status == 'kritis') bg-orange-500
                                        @elseif($f->status == 'menipis') bg-yellow-500
                                        @elseif($f->status == 'aman') bg-green-500
                                        @else bg-gray-500 @endif">
                                    </span>
                                    {{ ucfirst($f->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-right">
                                @if($f->has_auto_po)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-cyan-100 text-cyan-700 text-xs font-medium rounded-full">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Draft PO
                                    </span>
                                @elseif($f->recommended_qty > 0)
                                    <span class="font-bold text-blue-600">{{ $f->recommended_qty }}</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center text-gray-400">
                                    <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                    <p class="text-sm font-medium">Tidak ada produk</p>
                                    <p class="text-xs mt-1">Sesuaikan filter untuk melihat data</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100 bg-gray-50/50">
            {{ $products->links() }}
        </div>
    </div>

    @if ($showPreview)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40" wire:click.self="closePreview">
            <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[85vh] flex flex-col">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 shrink-0">
                    <div>
                        <h2 class="text-lg font-bold text-gray-900">Preview Draft PO</h2>
                        <p class="text-sm text-gray-500">{{ count($previewVendorGroups) }} PO / {{ $previewCount }} produk</p>
                    </div>
                    <button wire:click="closePreview" class="p-1.5 rounded-lg hover:bg-gray-100 transition text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="overflow-y-auto p-6 space-y-5 flex-1">
                    @foreach ($previewVendorGroups as $group)
                        <div class="border border-gray-200 rounded-xl overflow-hidden">
                            <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    <span class="font-semibold text-gray-800 text-sm">{{ $group['vendor_name'] }}</span>
                                </div>
                                <span class="text-xs text-gray-500">{{ $group['count'] }} produk · {{ $group['total_qty'] }} item</span>
                            </div>
                            <div class="divide-y divide-gray-100">
                                @foreach ($group['items'] as $item)
                                    <div class="flex items-center gap-4 px-4 py-2.5">
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium text-gray-800 text-sm truncate">{{ $item['product_name'] }}</p>
                                            <p class="text-xs text-gray-400 font-mono">{{ $item['sku'] }}</p>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <p class="text-xs text-gray-500">Stok: {{ $item['stock'] }}</p>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <p class="text-xs text-gray-500">Qty</p>
                                            <p class="font-bold text-blue-600">{{ $item['qty'] }}</p>
                                        </div>
                                        <div class="text-right shrink-0 w-28">
                                            <p class="text-xs text-gray-500">Subtotal</p>
                                            <p class="font-medium text-gray-800">{{ number_format($item['subtotal'], 0, ',', '.') }}</p>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="px-6 py-4 border-t border-gray-200 bg-gray-50/50 rounded-b-2xl shrink-0">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm text-gray-500">Total item: <strong>{{ $previewTotal }}</strong></span>
                        <span class="text-sm text-gray-500">Total produk: <strong>{{ $previewCount }}</strong></span>
                        <span class="text-sm text-gray-500">Draft PO: <strong>{{ count($previewVendorGroups) }}</strong></span>
                        <span class="text-lg font-bold text-gray-900">Rp {{ number_format($previewTotalPrice, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex gap-3 mt-4">
                        <button wire:click="closePreview" class="flex-1 px-4 py-2.5 border border-gray-300 text-gray-700 rounded-xl hover:bg-gray-100 transition text-sm font-medium">
                            Batal
                        </button>
                        <button wire:click="confirmPO" class="flex-1 px-4 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition text-sm font-medium shadow-sm">
                            Konfirmasi & Buat {{ count($previewVendorGroups) }} Draft PO
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
