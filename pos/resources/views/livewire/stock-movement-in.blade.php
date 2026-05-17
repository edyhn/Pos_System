<div>
    <script>
        document.addEventListener('keydown', function (e) {
            if (e.key === 'F2' && !e.ctrlKey && !e.altKey && !e.metaKey) {
                e.preventDefault();
                const input = document.getElementById('stockBarcodeInput');
                if (input) {
                    input.focus();
                    input.select();
                }
            }
        });

        document.addEventListener('livewire:initialized', function () {
            Livewire.on('barcodeScanSuccess', function (data) {
                // Auto-focus to quantity field after scan
                setTimeout(() => {
                    const qtyInput = document.querySelector('input[x-model="formatted"]');
                    if (qtyInput) {
                        qtyInput.focus();
                        qtyInput.select();
                    }
                }, 200);
            });
        });
    </script>

    @if (session('message'))
        <div class="mb-4 flex items-center gap-2 px-4 py-3 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 text-sm">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>{{ session('message') }}</span>
        </div>
    @endif

    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
            </div>
            <div>
                <h1 class="text-lg font-bold text-gray-900">Barang Masuk</h1>
                <p class="text-sm text-gray-500">Riwayat penerimaan barang (PO, retur, refund)</p>
            </div>
        </div>
        @if(!$showForm)
            <button wire:click="openForm" class="inline-flex items-center gap-2 px-4 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition text-sm font-medium shadow-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Barang Masuk
            </button>
        @endif
    </div>

    @if($showForm)
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center gap-2 mb-5">
                <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                </div>
                <h2 class="font-semibold text-gray-800">Form Barang Masuk</h2>
            </div>

            {{-- Barcode Scanner Section --}}
            <div class="mb-5 p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-200">
                <div class="flex items-center gap-2 mb-2">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2zM7 5h10v14H7V5zm2 2h6v2H9V7zm0 4h6v2H9v-2zm0 4h6v2H9v-2z"/></svg>
                    <span class="text-sm font-semibold text-blue-700">Scan Barcode Produk</span>
                    <span class="text-[10px] text-blue-400 font-mono bg-white/60 px-2 py-0.5 rounded hidden sm:inline">F2</span>
                </div>
                <div class="flex gap-2 items-center">
                    <div class="relative flex-1">
                        <input id="stockBarcodeInput"
                               type="text"
                               wire:model.live="barcodeInput"
                               x-on:keydown.enter="$wire.scanBarcode($el.value)"
                               placeholder="Scan barcode atau ketik SKU, lalu Enter..."
                               autofocus
                               class="w-full px-3 py-2.5 border-2 border-blue-300 rounded-lg text-sm font-mono tracking-wider focus:ring-2 focus:ring-blue-500 focus:border-blue-600 outline-none bg-white">
                    </div>
                    <button wire:click="scanBarcode" class="px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium">
                        Scan
                    </button>
                </div>
                @if (session('scan_error'))
                    <div class="mt-2 flex items-center gap-1.5 text-sm text-red-600 font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('scan_error') }}</span>
                    </div>
                @endif
                @if($scannedProductName)
                    <div class="mt-2 flex items-center gap-1.5 text-sm text-emerald-600 font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>✓ Produk dipilih: <strong>{{ $scannedProductName }}</strong></span>
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-3 mb-4">
                <div class="flex-1 h-px bg-gray-200"></div>
                <span class="text-xs text-gray-400 font-medium">atau pilih manual</span>
                <div class="flex-1 h-px bg-gray-200"></div>
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
                        <option value="manual">Manual</option>
                        <option value="po">Purchase Order</option>
                    </select>
                </div>
                <div>
                    @if($referenceType === 'po')
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pilih PO (Sent)</label>
                        <select wire:model="selectedPo" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white">
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
                    <textarea wire:model="note" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm bg-white" placeholder="Catatan (opsional)"></textarea>
                </div>
            </div>
            <div class="mt-5 flex gap-2">
                <button wire:click="addStockIn" class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700 transition text-sm font-medium shadow-sm">
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
                                <span class="inline-flex items-center gap-1 font-semibold text-emerald-600">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/></svg>
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
                                    <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3"/></svg>
                                    <p class="text-sm font-medium">Belum ada barang masuk</p>
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