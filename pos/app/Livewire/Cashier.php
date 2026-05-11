<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Subscription;
use App\Models\StockMovement;
use Livewire\Component;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use App\Services\MidtransService;

class Cashier extends Component
{
    public $storeId;
    public $search = '';
    public $category_id = '';
    public $cart = [];
    public $customer_name = '';
    public $payment_method = 'cash';
    public $payment_amount = 0;
    public $tax_enabled = false;
    public $midtransSnapToken = null;
    public $midtransOrderId = null;
    public $showMidtransPopup = false;

    protected function getListeners(): array
    {
        return [
            'productSelected',
            'completeMidtransPayment',
        ];
    }

    public function mount()
    {
        $this->storeId = auth()->user()->store_id;
    }

    public function addToCart($productId)
    {
        $product = Product::where('store_id', $this->storeId)
            ->where('is_active', true)
            ->findOrFail($productId);

        $existingKey = collect($this->cart)->search(fn($item) => $item['product_id'] === $productId);

        if ($existingKey !== false) {
            $this->cart[$existingKey]['quantity']++;
            $this->cart[$existingKey]['subtotal'] = $this->cart[$existingKey]['quantity'] * $this->cart[$existingKey]['price'];
        } else {
            $this->cart[] = [
                'product_id' => $product->id,
                'name' => $product->name,
                'price' => (float) $product->price,
                'quantity' => 1,
                'subtotal' => (float) $product->price,
                'is_taxed' => $product->is_taxed,
                'tax_rate' => (float) $product->tax_rate,
                'stock' => $product->stock,
            ];
        }
    }

    public function removeFromCart($index)
    {
        if (!isset($this->cart[$index])) return;
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
    }

    public function updateQuantity($index, $quantity)
    {
        if (!isset($this->cart[$index])) return;
        $quantity = max(1, (int) $quantity);
        $this->cart[$index]['quantity'] = $quantity;
        $this->cart[$index]['subtotal'] = $quantity * $this->cart[$index]['price'];
    }

    public function getSubtotalProperty()
    {
        return array_sum(array_column($this->cart, 'subtotal'));
    }

    public function getTaxAmountProperty()
    {
        if (!$this->tax_enabled) return 0;

        $tax = 0;
        foreach ($this->cart as $item) {
            if ($item['is_taxed']) {
                $tax += $item['subtotal'] * ($item['tax_rate'] / 100);
            }
        }
        return $tax;
    }

    public function getTotalProperty()
    {
        return $this->subtotal + $this->taxAmount;
    }

    public function getChangeProperty()
    {
        if ($this->payment_method === 'cash' && $this->payment_amount > 0) {
            return $this->payment_amount - $this->total;
        }
        return 0;
    }

    public function checkout()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Keranjang masih kosong.');
            return;
        }

        $this->validate([
            'payment_method' => 'required|in:cash,qris,transfer,debit_card,midtrans',
            'customer_name' => 'nullable|max:255',
        ]);

        if ($this->payment_method === 'cash' && $this->payment_amount < $this->total) {
            session()->flash('error', 'Pembayaran kurang dari total.');
            return;
        }

        if ($this->payment_method === 'midtrans') {
            $this->processMidtransPayment();
            return;
        }

        $productIds = array_column($this->cart, 'product_id');
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($this->cart as $item) {
            $product = $products->get($item['product_id']);
            if (!$product) {
                session()->flash('error', 'Produk tidak ditemukan.');
                return;
            }
            if ($product->stock < $item['quantity']) {
                session()->flash('error', 'Stok ' . $product->name . ' tidak mencukupi.');
                return;
            }
        }

        DB::transaction(function () use ($products) {
            $query = Transaction::whereDate('created_at', today())->where('store_id', $this->storeId);
            $count = DB::connection()->getDriverName() === 'sqlite' ? $query->count() : $query->lockForUpdate()->count();
            $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);

            $transaction = Transaction::create([
                'store_id' => $this->storeId,
                'user_id' => auth()->id(),
                'invoice_number' => $invoiceNumber,
                'customer_name' => $this->customer_name,
                'subtotal' => $this->subtotal,
                'tax_amount' => $this->taxAmount,
                'total_amount' => $this->total,
                'payment_amount' => $this->payment_method === 'cash' ? $this->payment_amount : $this->total,
                'change_amount' => $this->payment_method === 'cash' ? max(0, $this->payment_amount - $this->total) : 0,
                'payment_method' => $this->payment_method,
                'status' => 'completed',
            ]);

            foreach ($this->cart as $item) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                    'subtotal' => $item['subtotal'],
                    'is_taxed' => $item['is_taxed'],
                ]);

                $product = $products->get($item['product_id']);
                $product->decrement('stock', $item['quantity']);

                StockMovement::create([
                    'store_id' => $this->storeId,
                    'product_id' => $item['product_id'],
                    'user_id' => auth()->id(),
                    'reference_type' => 'transaction',
                    'reference_id' => $transaction->id,
                    'type' => 'out',
                    'quantity' => $item['quantity'],
                    'note' => 'Penjualan #' . $invoiceNumber,
                ]);

                if ($product->is_subscription) {
                    $startDate = now();
                    Subscription::create([
                        'store_id' => $this->storeId,
                        'transaction_id' => $transaction->id,
                        'product_id' => $item['product_id'],
                        'customer_identifier' => $this->customer_name,
                        'start_date' => $startDate,
                        'end_date' => $startDate->copy()->addDays($product->subscription_days),
                        'status' => 'active',
                    ]);
                }
            }

            \App\Services\ActivityLogger::log('create', 'Transaksi penjualan: ' . $invoiceNumber . ' - Rp ' . number_format($this->total, 0, ',', '.'));

            $this->dispatch('transactionCompleted',
                transactionId: $transaction->id,
                invoiceNumber: $invoiceNumber,
                total: $this->total,
                paymentMethod: $this->payment_method,
                customerName: $this->customer_name
            );
            $this->cart = [];
            $this->customer_name = '';
            $this->payment_amount = 0;

            session()->flash('success', 'Transaksi berhasil! Invoice: ' . $invoiceNumber);
        });
    }

    public function processMidtransPayment()
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Keranjang masih kosong.');
            return;
        }

        if (empty(config('midtrans.server_key'))) {
            session()->flash('error', 'Midtrans belum dikonfigurasi. Hubungi pemilik toko.');
            return;
        }

        $lock = Cache::lock('invoice-number-' . date('Ymd'), 10);
        $lock->block(5);
        try {
            $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad(
                Transaction::whereDate('created_at', today())->where('store_id', $this->storeId)->count() + 1, 4, '0', STR_PAD_LEFT
            );
        } finally {
            $lock->release();
        }

        $this->midtransOrderId = $invoiceNumber;

        $itemDetails = [];
        foreach ($this->cart as $item) {
            $itemDetails[] = [
                'id' => (string) $item['product_id'],
                'price' => (int) $item['price'],
                'quantity' => (int) $item['quantity'],
                'name' => substr($item['name'], 0, 50),
            ];
        }

        try {
            $midtrans = app(MidtransService::class);
            $params = [
                'transaction_details' => [
                    'order_id' => $this->midtransOrderId,
                    'gross_amount' => (int) $this->total,
                ],
                'item_details' => $itemDetails,
                'customer_details' => [
                    'first_name' => $this->customer_name ?: 'Customer',
                    'phone' => '',
                ],
                'callbacks' => [
                    'finish' => route('cashier'),
                ],
            ];

            $snapResponse = $midtrans->createSnapTransaction($params);
            $this->midtransSnapToken = $snapResponse->token;
            $this->showMidtransPopup = true;

            $this->dispatch('midtransReady', token: $this->midtransSnapToken);
        } catch (\Exception $e) {
            session()->flash('error', 'Gagal membuat transaksi Midtrans: ' . $e->getMessage());
        }
    }

    public function completeMidtransPayment()
    {
        if (!$this->midtransOrderId || empty($this->cart)) return;

        $productIds = array_column($this->cart, 'product_id');
        $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

        foreach ($this->cart as $item) {
            $product = $products->get($item['product_id']);
            if (!$product) {
                session()->flash('error', 'Produk tidak ditemukan.');
                return;
            }
            if ($product->stock < $item['quantity']) {
                session()->flash('error', 'Stok ' . $product->name . ' tidak mencukupi.');
                return;
            }
        }

        $lock = Cache::lock('midtrans-' . $this->midtransOrderId, 10);

        try {
            $result = $lock->get(function () use ($products) {
                $existing = Transaction::where('invoice_number', $this->midtransOrderId)->first();
                if ($existing && $existing->status === 'completed') {
                    $this->cart = [];
                    $this->customer_name = '';
                    $this->payment_amount = 0;
                    $this->midtransSnapToken = null;
                    $this->midtransOrderId = null;
                    $this->showMidtransPopup = false;
                    session()->flash('success', 'Pembayaran berhasil! Invoice: ' . $existing->invoice_number);
                    return true;
                }

                DB::transaction(function () use ($products) {
                    $invoiceNumber = $this->midtransOrderId;

                    $transaction = Transaction::updateOrCreate(
                        ['invoice_number' => $invoiceNumber],
                        [
                            'store_id' => $this->storeId,
                            'user_id' => auth()->id(),
                            'customer_name' => $this->customer_name,
                            'subtotal' => $this->subtotal,
                            'tax_amount' => $this->taxAmount,
                            'total_amount' => $this->total,
                            'payment_amount' => $this->total,
                            'change_amount' => 0,
                            'payment_method' => 'midtrans',
                            'status' => 'completed',
                        ]
                    );

                    if (!$transaction->items()->exists()) {
                        foreach ($this->cart as $item) {
                            TransactionItem::create([
                                'transaction_id' => $transaction->id,
                                'product_id' => $item['product_id'],
                                'product_name' => $item['name'],
                                'quantity' => $item['quantity'],
                                'price' => $item['price'],
                                'subtotal' => $item['subtotal'],
                                'is_taxed' => $item['is_taxed'],
                            ]);

                            $product = $products->get($item['product_id']);
                            $product->decrement('stock', $item['quantity']);
                            StockMovement::create([
                                'store_id' => $this->storeId,
                                'product_id' => $item['product_id'],
                                'user_id' => auth()->id(),
                                'reference_type' => 'transaction',
                                'reference_id' => $transaction->id,
                                'type' => 'out',
                                'quantity' => $item['quantity'],
                                'note' => 'Penjualan Midtrans #' . $invoiceNumber,
                            ]);

                            if ($product->is_subscription) {
                                Subscription::create([
                                    'store_id' => $this->storeId,
                                    'transaction_id' => $transaction->id,
                                    'product_id' => $item['product_id'],
                                    'customer_identifier' => $this->customer_name,
                                    'start_date' => now(),
                                    'end_date' => now()->addDays($product->subscription_days),
                                    'status' => 'active',
                                ]);
                            }
                        }
                    }

                    $this->dispatch('transactionCompleted',
                        transactionId: $transaction->id,
                        invoiceNumber: $invoiceNumber,
                        total: $this->total,
                        paymentMethod: 'midtrans',
                        customerName: $this->customer_name
                    );
                    $this->cart = [];
                    $this->customer_name = '';
                    $this->payment_amount = 0;
                    $this->midtransSnapToken = null;
                    $this->midtransOrderId = null;
                    $this->showMidtransPopup = false;

                    session()->flash('success', 'Pembayaran berhasil! Invoice: ' . $invoiceNumber);
                });

                return true;
            });

            if (!$result) {
                session()->flash('error', 'Pembayaran sedang diproses, silakan tunggu.');
            }
        } finally {
            $lock->release();
        }
    }

    public function render()
    {
        $query = Product::where('store_id', $this->storeId)->where('is_active', true);

        if ($this->category_id) {
            $query->where('category_id', $this->category_id);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('sku', 'like', '%' . $this->search . '%');
            });
        }

        $products = $query->orderBy('name')->get();
        $categories = Category::where('store_id', $this->storeId)->get();

        return view('livewire.cashier', compact('products', 'categories'));
    }
}
