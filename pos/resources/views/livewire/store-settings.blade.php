<div>
    <h1 class="text-2xl font-bold text-gray-800 mb-4">Pengaturan Toko</h1>

    @if (session('message'))
        <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-green-700 text-sm">{{ session('message') }}</div>
    @endif

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 max-w-2xl">
        <form wire:submit="save">
            <div class="grid grid-cols-2 gap-4">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Toko</label>
                    <input type="text" wire:model="name" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kode Toko</label>
                    <input type="text" wire:model="code" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Telepon</label>
                    <input type="text" wire:model="phone" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Alamat</label>
                    <textarea wire:model="address" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm"></textarea>
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Footer Struk</label>
                <textarea wire:model="receipt_footer" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Terima kasih telah berbelanja"></textarea>
            </div>

            <hr class="my-4">
            <h3 class="text-md font-semibold text-gray-800 mb-3">QRIS Pembayaran</h3>

            <div class="mb-4">
                @if ($existingQrisImage)
                    <div class="mb-2">
                        <img src="{{ Storage::url($existingQrisImage) }}" class="w-48 h-48 object-contain border rounded-lg">
                        <button type="button" wire:click="removeQris" class="mt-2 text-sm text-red-600 hover:underline">Hapus QRIS</button>
                    </div>
                @endif
                <label class="block text-sm font-medium text-gray-700 mb-1">Upload Gambar QRIS</label>
                <input type="file" wire:model="qris_image" accept="image/*" class="w-full text-sm">
                @error('qris_image') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                <div wire:loading wire:target="qris_image" class="text-blue-600 text-sm mt-1">Mengupload...</div>
            </div>

            <hr class="my-4">
            <h3 class="text-md font-semibold text-gray-800 mb-3">Pengaturan Printer Thermal</h3>

            <div class="grid grid-cols-2 gap-4">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tipe Printer</label>
                    <select wire:model="printer_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        <option value="network">Network (IP)</option>
                        <option value="usb">USB</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Port</label>
                    <input type="text" wire:model="printer_port" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="9100">
                </div>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Printer (IP)</label>
                <input type="text" wire:model="printer_address" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="192.168.1.100">
                <p class="text-xs text-gray-400 mt-1">Kosongkan jika menggunakan printer USB.</p>
            </div>

            <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm">Simpan</button>
        </form>
    </div>
</div>
