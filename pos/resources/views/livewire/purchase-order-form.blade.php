<div>
    @if (session('error'))
        <div class="mb-4 flex items-center gap-2 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-red-600 text-sm">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-cyan-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            </div>
            <div>
                <h1 class="text-lg font-bold text-gray-900">{{ $isEdit ? 'Edit Purchase Order' : 'Buat Purchase Order' }}</h1>
                <p class="text-sm text-gray-500">{{ $isEdit ? 'Ubah data PO' : 'Buat purchase order baru' }}</p>
            </div>
        </div>
        <a href="{{ route('purchase-orders.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition text-sm font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form wire:submit="save">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Vendor</label>
                    <select wire:model.live="vendor_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
                        <option value="">Pilih Vendor</option>
                        @foreach ($vendors as $v)
                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                    <input type="text" wire:model="notes" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
                </div>
            </div>

            <div class="mb-4">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-800 flex items-center gap-2">
                        <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        Items
                    </h3>
                    <button type="button" wire:click="addItem" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Tambah Item
                    </button>
                </div>

                @foreach ($items as $index => $item)
                    <div class="flex items-center gap-2 mb-2 p-3 bg-gray-50 rounded-lg">
                        <select wire:model="items.{{ $index }}.product_id" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
                            <option value="">Pilih Produk</option>
                            @foreach ($products as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} (Rp{{ number_format($p->cost_price ?: $p->price, 0, ',', '.') }})</option>
                            @endforeach
                        </select>
                        <input type="number" wire:model="items.{{ $index }}.quantity" class="w-20 px-3 py-2 border border-gray-300 rounded-lg text-sm text-right bg-white" min="1" placeholder="Qty">
                        <input type="number" wire:model="items.{{ $index }}.price" class="w-28 px-3 py-2 border border-gray-300 rounded-lg text-sm text-right bg-white" placeholder="Harga">
                        <span class="text-sm font-semibold w-28 text-right text-gray-800">Rp{{ number_format($item['subtotal'], 0, ',', '.') }}</span>
                        <button type="button" wire:click="removeItem({{ $index }})" class="p-1.5 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-lg transition" title="Hapus">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                @endforeach
                @if(empty($items))
                    <div class="flex flex-col items-center py-8 text-gray-400 border-2 border-dashed border-gray-200 rounded-xl">
                        <svg class="w-10 h-10 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <p class="text-sm">Belum ada item</p>
                        <p class="text-xs mt-1">Klik "Tambah Item" untuk menambahkan produk</p>
                    </div>
                @endif
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition text-sm font-medium shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ $isEdit ? 'Update PO' : 'Simpan PO' }}
                </button>
                <a href="{{ route('purchase-orders.index') }}" class="inline-flex items-center gap-2 px-6 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition text-sm font-medium">Batal</a>
            </div>
        </form>
    </div>
</div>
