<div>
    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Barang Masuk</h1>
            <p class="text-gray-500 text-sm">Riwayat penerimaan barang (PO, retur, refund).</p>
        </div>
        @if(!$showForm)
            <button wire:click="openForm" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm">+ Tambah Barang Masuk</button>
        @endif
    </div>

    @if (session('message'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">{{ session('message') }}</div>
    @endif

    @if($showForm)
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
            <h2 class="font-semibold text-gray-700 mb-4">Form Barang Masuk</h2>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pilih Produk</label>
                    <input type="text" wire:model.live.debounce="productSearch" placeholder="Cari produk..." class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm mb-2">
                    <select wire:model="product_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">Pilih Produk</option>
                        @foreach ($this->products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} (SKU: {{ $p->sku }}, Stok: {{ $p->stock }})</option>
                        @endforeach
                    </select>
                    @error('product_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah</label>
                    <input type="number" wire:model="quantity" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" min="1">
                    @error('quantity') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Referensi</label>
                    <select wire:model.live="referenceType" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="manual">Manual</option>
                        <option value="po">Purchase Order</option>
                    </select>
                </div>
                <div>
                    @if($referenceType === 'po')
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pilih PO (Sent)</label>
                        <select wire:model="selectedPo" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="">Pilih PO</option>
                            @foreach ($this->sentPos as $po)
                                <option value="{{ $po->id }}">{{ $po->po_number }} - {{ $po->vendor?->name ?? 'N/A' }}</option>
                            @endforeach
                        </select>
                        @error('selectedPo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    @endif
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                    <textarea wire:model="note" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Catatan (opsional)"></textarea>
                </div>
            </div>
            <div class="mt-4 flex gap-2">
                <button wire:click="addStockIn" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">Simpan</button>
                <button wire:click="$set('showForm', false)" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 text-sm">Batal</button>
            </div>
        </div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 mb-6">
        <div class="flex flex-wrap gap-2">
            <input type="text" wire:model.live.debounce="search" placeholder="Cari produk..."
                class="px-3 py-2 border border-gray-300 rounded-lg text-sm flex-1 min-w-[150px]">
            <input type="date" wire:model.live="dateFrom" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
            <input type="date" wire:model.live="dateTo" class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 text-left text-sm text-gray-500">
                    <th class="px-4 py-3 font-medium">Tanggal</th>
                    <th class="px-4 py-3 font-medium">Produk</th>
                    <th class="px-4 py-3 font-medium">Jumlah</th>
                    <th class="px-4 py-3 font-medium">Referensi</th>
                    <th class="px-4 py-3 font-medium">User</th>
                    <th class="px-4 py-3 font-medium">Catatan</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($movements as $m)
                    <tr class="hover:bg-gray-50 text-sm">
                        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $m->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3 text-gray-800">{{ $m->product->name ?? '-' }}</td>
                        <td class="px-4 py-3 font-medium text-green-600">+{{ $m->quantity }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $m->reference_type }} {{ $m->reference_id ? '#' . $m->reference_id : '' }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $m->user->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-500 max-w-[200px] truncate">{{ $m->note ?? '-' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-400">Belum ada barang masuk.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-gray-100">{{ $movements->links() }}</div>
    </div>
</div>