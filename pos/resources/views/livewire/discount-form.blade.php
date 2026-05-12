<div>
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold text-gray-800">{{ $discount ? 'Edit' : 'Buat' }} Diskon</h1>
        <a href="{{ route('discounts.index') }}" class="px-4 py-2 text-gray-600 bg-gray-200 rounded-lg hover:bg-gray-300">
            Kembali
        </a>
    </div>

    <form wire:submit="save" class="space-y-6">
        <div class="p-6 bg-white rounded-lg shadow">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div class="md:col-span-2">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Nama Diskon</label>
                    <input type="text" wire:model="name" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('name') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Tipe</label>
                    <select wire:model="type" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="percentage">Persentase (%)</option>
                        <option value="fixed">Nominal (Rp)</option>
                    </select>
                </div>

                <div x-data="{ formatted: '{{ $discount ? number_format($discount->value, 0, ',', '.') : '' }}' }">
                    <label class="block mb-1 text-sm font-medium text-gray-700">
                        Nilai <span x-text="$wire.type === 'percentage' ? '(%)' : '(Rp)'" class="text-gray-500"></span>
                    </label>
                    <input type="text" x-model="formatted"
                           x-on:input="formatted = $event.target.value.replace(/\D/g, ''); if (formatted) { let n = parseInt(formatted); formatted = n.toLocaleString('id-ID'); $wire.set('value', n); } else { $wire.set('value', 0); }"
                           :placeholder="$wire.type === 'percentage' ? 'Contoh: 10' : 'Contoh: 50.000'"
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-gray-500" x-text="$wire.type === 'percentage' ? 'Persentase potongan dari harga produk.' : 'Nominal potongan dalam Rupiah.'"></p>
                    @error('value') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <div x-data="{ formatted: '{{ $discount && $discount->min_purchase ? number_format($discount->min_purchase, 0, ',', '.') : '' }}' }">
                    <label class="block mb-1 text-sm font-medium text-gray-700">Min. Pembelian (opsional)</label>
                    <input type="text" x-model="formatted"
                           x-on:input="formatted = $event.target.value.replace(/\D/g, ''); if (formatted) { let n = parseInt(formatted); formatted = n.toLocaleString('id-ID'); $wire.set('min_purchase', n); } else { $wire.set('min_purchase', null); }"
                           placeholder="0"
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('min_purchase') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Prioritas</label>
                    <select wire:model="priority" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="0">Rendah</option>
                        <option value="1">Normal</option>
                        <option value="2">Tinggi</option>
                        <option value="3">Sangat Tinggi</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Diskon dengan prioritas lebih tinggi akan didahulukan saat bertabrakan dengan diskon lain.</p>
                    @error('priority') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Mulai</label>
                    <input type="datetime-local" wire:model="start_date" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('start_date') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block mb-1 text-sm font-medium text-gray-700">Berakhir</label>
                    <input type="datetime-local" wire:model="end_date" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('end_date') <span class="text-sm text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2">
                        <input type="checkbox" wire:model="stackable" class="rounded">
                        <span class="text-sm font-medium text-gray-700">Bisa digabung (stackable)</span>
                    </label>
                    <label class="flex items-center gap-2">
                        <input type="checkbox" wire:model="is_active" class="rounded">
                        <span class="text-sm font-medium text-gray-700">Aktif</span>
                    </label>
                </div>
            </div>
        </div>

        <div class="p-6 bg-white rounded-lg shadow">
            <h2 class="mb-4 text-lg font-semibold text-gray-800">Produk yang Terkena Diskon</h2>
            <p class="mb-2 text-sm text-gray-500">Kosongkan jika diskon berlaku untuk semua produk.</p>
            <div class="grid grid-cols-2 gap-2 md:grid-cols-4 max-h-60 overflow-y-auto">
                @foreach ($products as $product)
                    <label class="flex items-center gap-2 p-2 rounded hover:bg-gray-50 dark:hover:bg-white/5">
                        <input type="checkbox" value="{{ $product->id }}" wire:model="selectedProducts" class="rounded">
                        <span class="text-sm text-gray-800">{{ $product->name }}</span>
                    </label>
                @endforeach
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="px-6 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                {{ $discount ? 'Simpan Perubahan' : 'Buat Diskon' }}
            </button>
        </div>
    </form>
</div>
