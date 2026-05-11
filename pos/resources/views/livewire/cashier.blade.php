<div>
    <script>
        let pendingPrintId = null;

        document.addEventListener('livewire:initialized', function () {
            Livewire.on('transactionCompleted', function (data) {
                pendingPrintId = data.transactionId;
                document.getElementById('printInvoice').textContent = data.invoiceNumber;
                document.getElementById('printTotal').textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(data.total);
                document.getElementById('printPayment').textContent = data.paymentMethod;
                document.getElementById('printCustomer').textContent = data.customerName || '-';
                document.getElementById('printModal').classList.remove('hidden');
            });
        });

        function confirmPrint() {
            if (pendingPrintId) {
                window.open('/print/receipt/' + pendingPrintId, '_blank');
            }
            closePrintModal();
        }

        function closePrintModal() {
            document.getElementById('printModal').classList.add('hidden');
            pendingPrintId = null;
        }
    </script>

    <div class="flex gap-4 h-[calc(100vh-8rem)]">
        {{-- LEFT: Product Grid --}}
        <div class="flex-1 flex flex-col min-w-0">
            {{-- Search & Filter --}}
            <div class="flex gap-2 mb-3">
                <div class="relative flex-1">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input type="text" wire:model.live="search" placeholder="Cari produk (nama / SKU)..." class="w-full pl-9 pr-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none bg-white">
                </div>
                <select wire:model.live="category_id" class="px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white min-w-[140px]">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Products --}}
            <div class="flex-1 overflow-y-auto grid grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-3 content-start">
                @forelse ($products as $product)
                    <button wire:click="addToCart({{ $product->id }})"
                            class="relative text-left p-3 bg-white rounded-xl border border-gray-200 hover:border-blue-400 hover:shadow-md transition-all duration-150 text-sm group {{ $product->stock <= 0 ? 'opacity-60' : '' }}">
                        @if($product->stock <= 0)
                            <div class="absolute inset-0 bg-white/40 rounded-xl flex items-center justify-center z-10">
                                <span class="bg-red-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">HABIS</span>
                            </div>
                        @endif
                        <div class="w-full h-20 bg-gradient-to-br from-gray-50 to-gray-100 rounded-lg mb-2 flex items-center justify-center overflow-hidden">
                            @if($product->image)
                                <img src="{{ Storage::url($product->image) }}" class="w-full h-full object-contain p-1">
                            @else
                                <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                            @endif
                        </div>
                        <p class="font-medium text-gray-800 text-sm truncate group-hover:text-blue-600 transition">{{ $product->name }}</p>
                        <p class="text-blue-600 font-bold mt-0.5">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                        <div class="flex items-center gap-1.5 mt-1">
                            <span class="text-[11px] {{ $product->stock <= 0 ? 'text-red-500' : ($product->stock <= $product->min_stock ? 'text-amber-500' : 'text-gray-400') }}">
                                Stok: {{ $product->stock }}
                            </span>
                            @if($product->is_subscription)
                                <span class="text-[10px] bg-purple-50 text-purple-600 px-1.5 py-0.5 rounded">Langganan</span>
                            @endif
                        </div>
                    </button>
                @empty
                    <div class="col-span-full flex flex-col items-center justify-center py-16 text-gray-400">
                        <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                        <p class="text-sm">Produk tidak ditemukan</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- RIGHT: Cart --}}
        <div class="w-96 bg-white rounded-xl shadow-sm border border-gray-200 flex flex-col shrink-0">
            {{-- Cart Header --}}
            <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                    <h2 class="font-semibold text-gray-700 text-sm">Keranjang</h2>
                </div>
                @if(count($cart) > 0)
                    <span class="text-xs bg-blue-50 text-blue-600 px-2 py-0.5 rounded-full font-medium">{{ count($cart) }} item</span>
                @endif
            </div>

            {{-- Cart Items --}}
            <div class="flex-1 overflow-y-auto p-3 space-y-2">
                @if (session('error'))
                    <div class="p-3 bg-red-50 border border-red-200 rounded-lg text-red-600 text-xs flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif
                @if (session('success'))
                    <div class="p-3 bg-emerald-50 border border-emerald-200 rounded-lg text-emerald-600 text-xs flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif

                @forelse ($cart as $index => $item)
                    <div class="flex items-center gap-2.5 p-2.5 bg-gray-50 rounded-lg hover:bg-gray-100 transition group">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $item['name'] }}</p>
                            <p class="text-xs text-gray-400">Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                        </div>
                        <div class="flex items-center gap-1">
                            <button wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] - 1 }})"
                                    class="w-6 h-6 flex items-center justify-center bg-white border border-gray-200 rounded hover:bg-gray-100 text-sm font-medium text-gray-600 transition">-</button>
                            <span class="w-8 text-center text-sm font-semibold text-gray-800">{{ $item['quantity'] }}</span>
                            <button wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] + 1 }})"
                                    class="w-6 h-6 flex items-center justify-center bg-white border border-gray-200 rounded hover:bg-gray-100 text-sm font-medium text-gray-600 transition">+</button>
                        </div>
                        <p class="text-sm font-bold text-gray-800 w-20 text-right">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</p>
                        <button wire:click="removeFromCart({{ $index }})" class="text-red-300 hover:text-red-500 transition p-1 opacity-0 group-hover:opacity-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </div>
                @empty
                    <div class="flex flex-col items-center justify-center py-12 text-gray-400">
                        <svg class="w-12 h-12 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                        <p class="text-sm">Belum ada produk</p>
                        <p class="text-xs mt-1">Klik produk untuk menambahkan</p>
                    </div>
                @endforelse
            </div>

            {{-- Checkout --}}
            <div class="border-t border-gray-100 p-4 space-y-3">
                <input type="text" wire:model="customer_name" placeholder="Nama customer (opsional)"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white">

                <label class="flex items-center gap-2 text-sm cursor-pointer select-none">
                    <input type="checkbox" wire:model.live="tax_enabled" class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-gray-600">Pajak (PPN)</span>
                </label>

                <div class="bg-gray-50 rounded-lg p-3 space-y-1.5 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500">Subtotal</span>
                        <span class="font-medium text-gray-800">Rp {{ number_format($this->subtotal, 0, ',', '.') }}</span>
                    </div>
                    @if($tax_enabled)
                        <div class="flex justify-between">
                            <span class="text-gray-500">Pajak (PPN)</span>
                            <span class="font-medium text-gray-800">Rp {{ number_format($this->taxAmount, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between text-base font-bold border-t border-gray-200 pt-1.5 mt-1.5">
                        <span>Total</span>
                        <span class="text-blue-600">Rp {{ number_format($this->total, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="relative">
                    <select wire:model.live="payment_method" class="w-full appearance-none px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                        <option value="cash">💵 Tunai</option>
                        <option value="qris">📱 QRIS</option>
                        <option value="transfer">🏦 Transfer Bank</option>
                        <option value="debit_card">💳 Kartu Debit</option>
                        <option value="midtrans">🔗 Midtrans (QRIS / VA)</option>
                    </select>
                    <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                </div>

                @if($payment_method === 'cash')
                    <div x-data="{ formatted: '{{ $payment_amount > 0 ? number_format($payment_amount, 0, ',', '.') : '' }}' }">
                        <input type="text" x-model="formatted"
                               x-on:input="formatted = $event.target.value.replace(/\D/g, ''); if (formatted) { let n = parseInt(formatted); formatted = n.toLocaleString('id-ID'); $wire.set('payment_amount', n); } else { $wire.set('payment_amount', 0); }"
                               placeholder="Jumlah bayar"
                               class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none bg-white">
                        @if($payment_amount > 0 && $payment_amount >= $this->total)
                            <div class="flex justify-between items-center mt-2 px-3 py-2 bg-emerald-50 rounded-lg">
                                <span class="text-sm text-gray-600">Kembali</span>
                                <span class="text-sm font-bold text-emerald-600">Rp {{ number_format($this->change, 0, ',', '.') }}</span>
                            </div>
                        @endif
                    </div>
                @endif

                @if($payment_method === 'qris')
                    @php $store = \App\Models\Store::find($storeId); @endphp
                    @if($store && $store->qris_image)
                        <div class="text-center p-3 bg-gray-50 rounded-lg">
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($store->qris_image) }}" class="w-40 h-40 object-contain mx-auto rounded-lg">
                            <p class="text-xs text-gray-500 mt-2">Scan QRIS untuk membayar</p>
                        </div>
                    @else
                        <div class="p-3 bg-amber-50 rounded-lg text-xs text-amber-600 text-center flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                            <span>QRIS belum diupload. Atur di Pengaturan Toko.</span>
                        </div>
                    @endif
                @endif

                <button wire:click="checkout" wire:loading.attr="disabled" wire:target="checkout"
                        class="w-full py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 active:bg-blue-800 transition text-sm flex items-center justify-center gap-2 {{ empty($cart) ? 'opacity-50 cursor-not-allowed' : '' }}"
                        {{ empty($cart) ? 'disabled' : '' }}>
                    <svg wire:loading.remove wire:target="checkout" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/></svg>
                    <svg wire:loading wire:target="checkout" class="w-4 h-4 animate-spin" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span wire:loading.remove wire:target="checkout">Bayar</span>
                    <span wire:loading wire:target="checkout">Memproses...</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Midtrans --}}
    <script>
        document.addEventListener('livewire:initialized', function () {
            Livewire.on('midtransReady', function (data) {
                if (data.token) {
                    snap.pay(data.token, {
                        onSuccess: function () {
                            Livewire.dispatch('completeMidtransPayment');
                        },
                        onPending: function () {
                            alert('Pembayaran sedang diproses. Silakan tunggu konfirmasi.');
                        },
                        onError: function () {
                            alert('Pembayaran gagal. Silakan coba lagi.');
                        },
                        onClose: function () {}
                    });
                }
            });
        });
    </script>

    <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>

    {{-- Print Modal --}}
    <div id="printModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        <div class="fixed inset-0 bg-black/50 backdrop-blur-sm" onclick="closePrintModal()"></div>
        <div class="relative bg-white rounded-xl shadow-2xl p-6 w-full max-w-sm mx-4 transform">
            <div class="text-center mb-4">
                <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-gray-900">Transaksi Berhasil</h3>
                <p class="text-xs text-gray-400 mt-1">Pastikan data sudah benar sebelum mencetak</p>
            </div>
            <div class="space-y-2 text-sm bg-gray-50 rounded-lg p-4 mb-4">
                <div class="flex justify-between">
                    <span class="text-gray-500">Invoice</span>
                    <span class="font-semibold text-gray-800 font-mono" id="printInvoice"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Total</span>
                    <span class="font-bold text-gray-900" id="printTotal"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Pembayaran</span>
                    <span class="font-medium text-gray-800 capitalize" id="printPayment"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Customer</span>
                    <span class="font-medium text-gray-800" id="printCustomer"></span>
                </div>
            </div>
            <p class="text-gray-700 mb-4 text-center font-medium text-sm">Cetak struk?</p>
            <div class="flex gap-2">
                <button onclick="confirmPrint()" class="flex-1 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition text-sm flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                    Ya, Cetak
                </button>
                <button onclick="closePrintModal()" class="flex-1 py-2.5 bg-gray-100 text-gray-700 font-medium rounded-lg hover:bg-gray-200 transition text-sm">Tidak</button>
            </div>
        </div>
    </div>
</div>