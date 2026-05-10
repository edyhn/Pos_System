<div>
    <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold text-gray-800">{{ $isEdit ? 'Edit Purchase Order' : 'Buat Purchase Order' }}</h1>
        <a href="{{ route('purchase-orders.index') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition text-sm">Kembali</a>
    </div>

    @if (session('error'))
        <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-red-600 text-sm">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <form wire:submit="save">
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Vendor</label>
                    <select wire:model="vendor_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="">Pilih Vendor</option>
                        @foreach ($vendors as $v)
                            <option value="{{ $v->id }}">{{ $v->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                    <input type="text" wire:model="notes" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
            </div>

            <div class="mb-4">
                <div class="flex justify-between items-center mb-2">
                    <h3 class="text-sm font-semibold text-gray-700">Items</h3>
                    <button type="button" wire:click="addItem" class="text-sm text-blue-600 hover:text-blue-800">+ Tambah Item</button>
                </div>

                @foreach ($items as $index => $item)
                    <div class="flex items-center gap-2 mb-2">
                        <select wire:model="items.{{ $index }}.product_id" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="">Pilih Produk</option>
                            @foreach ($products as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} (Rp {{ number_format($p->cost_price ?: $p->price, 0, ',', '.') }})</option>
                            @endforeach
                        </select>
                        <input type="number" wire:model="items.{{ $index }}.quantity" class="w-20 px-3 py-2 border border-gray-300 rounded-lg text-sm text-right" min="1">
                        <input type="number" wire:model="items.{{ $index }}.price" class="w-28 px-3 py-2 border border-gray-300 rounded-lg text-sm text-right">
                        <span class="text-sm font-medium w-28 text-right">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</span>
                        <button type="button" wire:click="removeItem({{ $index }})" class="text-red-500 hover:text-red-700 text-lg">×</button>
                    </div>
                @endforeach
                @if(empty($items))
                    <p class="text-sm text-gray-400 text-center py-4">Belum ada item. Klik "+ Tambah Item"</p>
                @endif
            </div>

            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">{{ $isEdit ? 'Update PO' : 'Simpan PO' }}</button>
        </form>
    </div>
</div>
