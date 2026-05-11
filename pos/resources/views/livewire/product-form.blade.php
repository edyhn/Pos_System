<div>
    @if (session('message'))
        <div class="mb-4 flex items-center gap-2 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-sm">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('message') }}</span>
        </div>
    @endif
    @if ($errors->any())
        <div class="mb-4 flex items-center gap-2 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Ada kesalahan pada form. Periksa kembali input Anda.</span>
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            </div>
            <div>
                <h1 class="text-lg font-bold text-gray-900">{{ $isEdit ? 'Edit' : 'Tambah' }} Produk</h1>
                <p class="text-sm text-gray-500">{{ $isEdit ? 'Ubah data produk' : 'Buat produk baru' }}</p>
            </div>
        </div>
        <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition text-sm font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
        <form wire:submit="save">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Produk <span class="text-red-500">*</span></label>
                    <input type="text" wire:model="name" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white @error('name') border-red-500 @enderror">
                    @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                    <select wire:model.live="category_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white @error('category_id') border-red-500 @enderror">
                        <option value="">Pilih Kategori</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Vendor</label>
                    <select wire:model="vendor_id" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white">
                        <option value="">Pilih Vendor</option>
                        @foreach ($vendors as $vendor)
                            <option value="{{ $vendor->id }}">{{ $vendor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                    <div class="relative">
                        <input type="text" wire:model="sku" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white @error('sku') border-red-500 @enderror">
                        @if(!$isEdit)
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-[10px] text-blue-400 font-medium">otomatis</span>
                        @endif
                    </div>
                    @error('sku') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Barcode</label>
                    <input type="text" wire:model="barcode" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Harga Jual <span class="text-red-500">*</span></label>
                    <div class="relative" x-data="{ formatted: '{{ $price ? number_format($price, 0, ',', '.') : '' }}' }">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium">Rp</span>
                        <input type="text" x-model="formatted"
                               x-on:input="formatted = $event.target.value.replace(/\D/g, ''); if (formatted) { let n = parseInt(formatted); formatted = n.toLocaleString('id-ID'); $wire.set('price', n); } else { $wire.set('price', 0); }"
                               class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white @error('price') border-red-500 @enderror">
                    </div>
                    @error('price') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Harga Modal</label>
                    <div class="relative" x-data="{ formatted: '{{ $cost_price ? number_format($cost_price, 0, ',', '.') : '' }}' }">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm font-medium">Rp</span>
                        <input type="text" x-model="formatted"
                               x-on:input="formatted = $event.target.value.replace(/\D/g, ''); if (formatted) { let n = parseInt(formatted); formatted = n.toLocaleString('id-ID'); $wire.set('cost_price', n); } else { $wire.set('cost_price', 0); }"
                               class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Stok Awal</label>
                    <div x-data="{ formatted: '{{ $stock ? number_format($stock, 0, ',', '.') : '' }}' }">
                        <input type="text" x-model="formatted"
                               x-on:input="formatted = $event.target.value.replace(/\D/g, ''); if (formatted) { let n = parseInt(formatted); formatted = n.toLocaleString('id-ID'); $wire.set('stock', n); } else { $wire.set('stock', 0); }"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white @error('stock') border-red-500 @enderror">
                    </div>
                    @error('stock') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Minimal Stok</label>
                    <div x-data="{ formatted: '{{ $min_stock ? number_format($min_stock, 0, ',', '.') : '' }}' }">
                        <input type="text" x-model="formatted"
                               x-on:input="formatted = $event.target.value.replace(/\D/g, ''); if (formatted) { let n = parseInt(formatted); formatted = n.toLocaleString('id-ID'); $wire.set('min_stock', n); } else { $wire.set('min_stock', 0); }"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white">
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1">Trigger auto PO saat stok di bawah ini</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Satuan</label>
                    <input type="text" wire:model="unit" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white" placeholder="pcs">
                </div>
            </div>

            <div class="border-t border-gray-200 pt-5 mt-5 mb-5">
                <h3 class="text-sm font-semibold text-gray-800 mb-4">Pengaturan Pajak & Langganan</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-gray-50 rounded-xl p-4">
                        <label class="flex items-center gap-2 mb-2">
                            <input type="checkbox" wire:model.live="is_taxed" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">Kena PPN / Pajak</span>
                        </label>
                        @if($is_taxed)
                            <div class="mt-2 pl-6">
                                <label class="block text-xs font-medium text-gray-500 mb-1">Tarif Pajak (%)</label>
                                <input type="number" wire:model="tax_rate" class="w-32 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white" placeholder="11">
                            </div>
                        @endif
                    </div>
                    <div class="bg-gray-50 rounded-xl p-4">
                        <label class="flex items-center gap-2 mb-2">
                            <input type="checkbox" wire:model.live="is_subscription" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <span class="text-sm text-gray-700">Produk Langganan</span>
                        </label>
                        @if($is_subscription)
                            <div class="mt-2 pl-6">
                                <label class="block text-xs font-medium text-gray-500 mb-1">Masa Aktif (hari)</label>
                                <input type="number" wire:model="subscription_days" class="w-32 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white" placeholder="30">
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">Deskripsi</label>
                <textarea wire:model="description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white"></textarea>
            </div>

            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">Gambar Produk</label>
                <div class="mt-1">
                    <input type="file" wire:model="image" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                </div>
                @if ($image)
                    <div class="mt-3">
                        <img src="{{ $image->temporaryUrl() }}" class="h-32 object-contain border border-gray-200 rounded-xl">
                    </div>
                @elseif($isEdit && $existingImage)
                    <div class="mt-3">
                        <img src="{{ Storage::url($existingImage) }}" class="h-32 object-contain border border-gray-200 rounded-xl">
                    </div>
                @endif
                @error('image') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-5 bg-gray-50 rounded-xl px-4 py-3">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" wire:model="is_active" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm font-medium text-gray-700">Aktif</span>
                </label>
            </div>

            <div class="flex gap-3 pt-2">
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition text-sm font-medium shadow-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ $isEdit ? 'Update' : 'Simpan' }}
                </button>
                <a href="{{ route('products.index') }}" class="inline-flex items-center gap-2 px-6 py-2.5 bg-gray-100 text-gray-700 rounded-xl hover:bg-gray-200 transition text-sm font-medium">Batal</a>
            </div>
        </form>
    </div>
</div>
