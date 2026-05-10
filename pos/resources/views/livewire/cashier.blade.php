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
        <div class="flex-1 flex flex-col">
            <div class="flex gap-2 mb-3">
                <input type="text" wire:model.live="search" placeholder="Cari produk (nama / SKU)..." class="flex-1 px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none">
                <select wire:model.live="category_id" class="px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex-1 overflow-y-auto grid grid-cols-3 gap-2 content-start">
                @forelse ($products as $product)
                    <button wire:click="addToCart({{ $product->id }})" class="text-left p-3 bg-white rounded-xl border border-gray-200 hover:border-blue-300 hover:shadow-sm transition text-sm {{ $product->stock <= 0 ? 'opacity-50' : '' }}">
                        <p class="font-semibold text-gray-800 truncate">{{ $product->name }}</p>
                        <p class="text-blue-600 font-bold mt-1">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">Stok: {{ $product->stock }}</p>
                        @if($product->is_subscription)
                            <span class="text-xs text-purple-600">Langganan {{ $product->subscription_days }}hr</span>
                        @endif
                    </button>
                @empty
                    <div class="col-span-3 text-center py-10 text-gray-400">Produk tidak ditemukan.</div>
                @endforelse
            </div>
        </div>

        <div class="w-96 bg-white rounded-xl shadow-sm border border-gray-200 flex flex-col">
            <div class="p-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-700">Keranjang</h2>
            </div>

            <div class="flex-1 overflow-y-auto p-3 space-y-2">
                @if (session('error'))
                    <div class="p-2 bg-red-50 border border-red-200 rounded text-red-600 text-xs">{{ session('error') }}</div>
                @endif
                @if (session('success'))
                    <div class="p-2 bg-green-50 border border-green-200 rounded text-green-600 text-xs">{{ session('success') }}</div>
                @endif

                @forelse ($cart as $index => $item)
                    <div class="flex items-center gap-2 p-2 bg-gray-50 rounded-lg">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 truncate">{{ $item['name'] }}</p>
                            <p class="text-xs text-gray-500">Rp {{ number_format($item['price'], 0, ',', '.') }}</p>
                        </div>
                        <div class="flex items-center gap-1">
                            <button wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] - 1 }})" class="w-6 h-6 flex items-center justify-center bg-gray-200 rounded hover:bg-gray-300 text-sm">-</button>
                            <span class="w-8 text-center text-sm font-medium">{{ $item['quantity'] }}</span>
                            <button wire:click="updateQuantity({{ $index }}, {{ $item['quantity'] + 1 }})" class="w-6 h-6 flex items-center justify-center bg-gray-200 rounded hover:bg-gray-300 text-sm">+</button>
                        </div>
                        <p class="text-sm font-bold text-gray-800 w-20 text-right">Rp {{ number_format($item['subtotal'], 0, ',', '.') }}</p>
                        <button wire:click="removeFromCart({{ $index }})" class="text-red-500 hover:text-red-700 text-sm font-bold">×</button>
                    </div>
                @empty
                    <div class="text-center py-8 text-gray-400 text-sm">Belum ada produk.</div>
                @endforelse
            </div>

            <div class="p-4 border-t border-gray-100 space-y-2">
                <input type="text" wire:model="customer_name" placeholder="Nama customer (opsional)" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">

                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" wire:model.live="tax_enabled" class="rounded border-gray-300 text-blue-600">
                    <span>Pajak (PPN)</span>
                </label>

                <div class="text-sm space-y-1">
                    <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span class="font-medium">Rp {{ number_format($this->subtotal, 0, ',', '.') }}</span></div>
                    @if($tax_enabled)
                        <div class="flex justify-between"><span class="text-gray-500">Pajak</span><span class="font-medium">Rp {{ number_format($this->taxAmount, 0, ',', '.') }}</span></div>
                    @endif
                    <div class="flex justify-between text-base font-bold border-t pt-1"><span>Total</span><span>Rp {{ number_format($this->total, 0, ',', '.') }}</span></div>
                </div>

                <select wire:model.live="payment_method" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    <option value="cash">Tunai</option>
                    <option value="qris">QRIS</option>
                    <option value="transfer">Transfer Bank</option>
                    <option value="debit_card">Kartu Debit</option>
                    <option value="midtrans">Midtrans (QRIS / VA)</option>
                </select>

                @if($payment_method === 'cash')
                    <input type="number" wire:model.live="payment_amount" placeholder="Jumlah bayar" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 outline-none">
                    @if($payment_amount > 0 && $payment_amount >= $this->total)
                        <div class="text-sm flex justify-between"><span class="text-gray-500">Kembali</span><span class="font-bold text-green-600">Rp {{ number_format($this->change, 0, ',', '.') }}</span></div>
                    @endif
                @endif

                @if($payment_method === 'qris')
                    @php $store = \App\Models\Store::find($storeId); @endphp
                    @if($store && $store->qris_image)
                        <div class="text-center">
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($store->qris_image) }}" class="w-48 h-48 object-contain mx-auto border rounded-lg">
                            <p class="text-xs text-gray-500 mt-1">Scan QRIS untuk membayar</p>
                        </div>
                    @else
                        <p class="text-xs text-yellow-600 text-center">QRIS belum diupload. Atur di Pengaturan Toko.</p>
                    @endif
                @endif

                <button wire:click="checkout" class="w-full py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition text-sm {{ empty($cart) ? 'opacity-50' : '' }}" {{ empty($cart) ? 'disabled' : '' }}>
                    Bayar
                </button>
            </div>
        </div>
    </div>

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
                        onClose: function () {
                            // User closed popup
                        }
                    });
                }
            });
        });
    </script>

    @if($payment_method === 'midtrans')
        <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
    @endif

    <div id="printModal" class="fixed inset-0 z-50 flex items-center justify-center hidden">
        <div class="fixed inset-0 bg-black/50" onclick="closePrintModal()"></div>
        <div class="relative bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
            <h3 class="text-lg font-bold text-gray-800 mb-1">Transaksi Berhasil</h3>
            <p class="text-xs text-gray-500 mb-4">Pastikan data sudah benar sebelum mencetak struk.</p>
            <div class="space-y-2 text-sm bg-gray-50 rounded-lg p-4 mb-4">
                <div class="flex justify-between">
                    <span class="text-gray-500">Invoice</span>
                    <span class="font-semibold text-gray-800" id="printInvoice"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Total</span>
                    <span class="font-semibold text-gray-800" id="printTotal"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Pembayaran</span>
                    <span class="font-semibold text-gray-800 capitalize" id="printPayment"></span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Customer</span>
                    <span class="font-semibold text-gray-800" id="printCustomer"></span>
                </div>
            </div>
            <p class="text-gray-700 mb-4 text-center font-medium">Cetak struk?</p>
            <div class="flex gap-2">
                <button onclick="confirmPrint()" class="flex-1 py-2.5 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition text-sm">Ya, Cetak</button>
                <button onclick="closePrintModal()" class="flex-1 py-2.5 bg-gray-200 text-gray-700 font-medium rounded-lg hover:bg-gray-300 transition text-sm">Tidak</button>
            </div>
        </div>
    </div>
</div>
