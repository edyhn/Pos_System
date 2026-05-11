<div>
    @if (session('message'))
        <div class="mb-4 flex items-center gap-2 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-sm">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('message') }}</span>
        </div>
    @endif
    @if (session('error'))
        <div class="mb-4 flex items-center gap-2 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-red-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 14l5-5 5 5M12 4v12"/></svg>
            </div>
            <div>
                <h1 class="text-lg font-bold text-gray-900">Barang Keluar</h1>
                <p class="text-sm text-gray-500">Riwayat pengeluaran barang (penjualan, rusak, expired, dll)</p>
            </div>
        </div>
        @if(!$showForm)
            <button wire:click="openForm" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition text-sm font-medium shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Barang Keluar
            </button>
        @endif
    </div>

    @if($showForm)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center gap-2 mb-5">
                <div class="w-8 h-8 rounded-lg bg-red-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                </div>
                <h2 class="font-semibold text-gray-800">Form Barang Keluar</h2>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Produk <span class="text-red-500">*</span></label>
                    <input type="text" wire:model.live.debounce="productSearch" placeholder="Cari produk..." class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm mb-2 bg-white">
                    <select wire:model.live="product_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
                        <option value="">Pilih Produk</option>
                        @foreach ($this->products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} (SKU: {{ $p->sku }}, Stok: {{ $p->stock }})</option>
                        @endforeach
                    </select>
                    @error('product_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah <span class="text-red-500">*</span></label>
                    <div x-data="{ formatted: '' }">
                        <input type="text" x-model="formatted"
                               x-on:input="formatted = $event.target.value.replace(/\D/g, ''); if (formatted) { let n = parseInt(formatted); formatted = n.toLocaleString('id-ID'); $wire.set('quantity', n); } else { $wire.set('quantity', 0); }"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
                    </div>
                    @error('quantity') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Referensi</label>
                    <select wire:model.live="referenceType" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
                        <option value="penjualan">Penjualan</option>
                        <option value="rusak">Rusak</option>
                        <option value="expired">Expired</option>
                        <option value="lainnya">Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan / Alasan <span class="text-red-500">*</span></label>
                    <textarea wire:model="note" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white" placeholder="Alasan barang keluar (wajib)"></textarea>
                    @error('note') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div class="mt-5 flex gap-2">
                <button wire:click="addStockOut" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition text-sm font-medium shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Simpan
                </button>
                <button wire:click="$set('showForm', false)" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition text-sm font-medium">Batal</button>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4 mb-4">
        <div class="flex flex-wrap gap-3">
            <div class="relative flex-1 min-w-[180px]">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input type="text" wire:model.live.debounce="search" placeholder="Cari produk..." class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white">
            </div>
            <div>
                <label class="block text-[10px] font-medium text-gray-500 mb-0.5">Dari</label>
                <input type="date" wire:model.live="dateFrom" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
            </div>
            <div>
                <label class="block text-[10px] font-medium text-gray-500 mb-0.5">Sampai</label>
                <input type="date" wire:model.live="dateTo" class="px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="px-4 py-3.5">Tanggal</th>
                        <th class="px-4 py-3.5">Produk</th>
                        <th class="px-4 py-3.5 text-right">Jumlah</th>
                        <th class="px-4 py-3.5">Referensi</th>
                        <th class="px-4 py-3.5">User</th>
                        <th class="px-4 py-3.5">Catatan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($movements as $m)
                        <tr class="hover:bg-gray-50 text-sm transition">
                            <td class="px-4 py-3.5 text-gray-500 whitespace-nowrap text-xs">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-3.5 font-medium text-gray-800">{{ $m->product->name ?? '-' }}</td>
                            <td class="px-4 py-3.5 text-right">
                                <span class="inline-flex items-center gap-1 font-semibold text-red-600">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/></svg>
                                    {{ $m->quantity }}
                                </span>
                            </td>
                            <td class="px-4 py-3.5 text-gray-500">{{ $m->reference_type }} {{ $m->reference_id ? '#' . $m->reference_id : '' }}</td>
                            <td class="px-4 py-3.5 text-gray-500">{{ $m->user->name ?? '-' }}</td>
                            <td class="px-4 py-3.5 text-gray-500 max-w-[200px] truncate">{{ $m->note ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center">
                                <div class="flex flex-col items-center text-gray-400">
                                    <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 14l5-5 5 5M12 4v12"/></svg>
                                    <p class="text-sm font-medium">Belum ada barang keluar</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100 bg-gray-50/50">
            {{ $movements->links() }}
        </div>
    </div>
</div>