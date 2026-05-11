<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Product;
use App\Models\Transaction;
use Livewire\Component;
use App\Services\CheckoutService;
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

    public function mount(): void
    {
        $this->storeId = auth()->user()->store_id;
    }

    public function addToCart($productId): void
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

    public function removeFromCart($index): void
    {
        if (!isset($this->cart[$index])) return;
        unset($this->cart[$index]);
        $this->cart = array_values($this->cart);
    }

    public function updateQuantity($index, $quantity): void
    {
        if (!isset($this->cart[$index])) return;
        $quantity = max(1, (int) $quantity);
        $this->cart[$index]['quantity'] = $quantity;
        $this->cart[$index]['subtotal'] = $quantity * $this->cart[$index]['price'];
    }

    public function getSubtotalProperty(): float
    {
        return array_sum(array_column($this->cart, 'subtotal'));
    }

    public function getTaxAmountProperty(): float
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

    public function getTotalProperty(): float
    {
        return $this->subtotal + $this->taxAmount;
    }

    public function getChangeProperty(): float
    {
        if ($this->payment_method === 'cash' && $this->payment_amount > 0) {
            return $this->payment_amount - $this->total;
        }
        return 0;
    }

    public function checkout(CheckoutService $checkoutService): void
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Keranjang masih kosong.');
            return;
        }

        $this->validate([
            'payment_method' => 'required|in:cash,qris,transfer,debit_card,midtrans',
            'customer_name' => 'nullable|max:255',
        ]);

        if ($this->payment_method === 'midtrans') {
            $this->processMidtransPayment();
            return;
        }

        try {
            $transaction = $checkoutService->processCheckout(
                cart: $this->cart,
                customerName: $this->customer_name,
                paymentMethod: $this->payment_method,
                paymentAmount: (float) $this->payment_amount,
                taxEnabled: $this->tax_enabled,
                storeId: $this->storeId,
                userId: auth()->id(),
            );

            $this->dispatch('transactionCompleted',
                transactionId: $transaction->id,
                invoiceNumber: $transaction->invoice_number,
                total: $transaction->total_amount,
                paymentMethod: $this->payment_method,
                customerName: $this->customer_name
            );
            $this->cart = [];
            $this->customer_name = '';
            $this->payment_amount = 0;

            session()->flash('success', 'Transaksi berhasil! Invoice: ' . $transaction->invoice_number);
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function processMidtransPayment(): void
    {
        if (empty($this->cart)) {
            session()->flash('error', 'Keranjang masih kosong.');
            return;
        }

        if (empty(config('midtrans.server_key'))) {
            session()->flash('error', 'Midtrans belum dikonfigurasi. Hubungi pemilik toko.');
            return;
        }

        $checkoutService = app(CheckoutService::class);
        $this->midtransOrderId = $checkoutService->generateInvoiceNumber($this->storeId);

        $itemDetails = [];
        foreach ($this->cart as $item) {
            $itemDetails[] = [
                'id' => (string) $item['product_id'],
                'price' => (int) $item['price'],
                'quantity' => (int) $item['quantity'],
                'name' => substr($item['name'], 0, 50),
            ];
        }

        $total = $checkoutService->calculateTotal($this->cart, $this->tax_enabled);

        try {
            $midtrans = app(MidtransService::class);
            $params = [
                'transaction_details' => [
                    'order_id' => $this->midtransOrderId,
                    'gross_amount' => (int) $total,
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

    public function completeMidtransPayment(CheckoutService $checkoutService): void
    {
        if (!$this->midtransOrderId || empty($this->cart)) return;

        try {
            $midtrans = app(MidtransService::class);
            try {
                $statusResponse = $midtrans->checkStatus($this->midtransOrderId);
                $txStatus = $statusResponse->transaction_status ?? '';
                if (!in_array($txStatus, ['settlement', 'capture'])) {
                    session()->flash('error', 'Pembayaran Midtrans belum selesai. Status: ' . $txStatus);
                    return;
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::info('Midtrans status check failed, proceeding anyway', ['error' => $e->getMessage()]);
            }

            $transaction = $checkoutService->processMidtransCheckout(
                cart: $this->cart,
                customerName: $this->customer_name,
                orderId: $this->midtransOrderId,
                taxEnabled: $this->tax_enabled,
                storeId: $this->storeId,
                userId: auth()->id(),
            );

            $this->dispatch('transactionCompleted',
                transactionId: $transaction->id,
                invoiceNumber: $transaction->invoice_number,
                total: $transaction->total_amount,
                paymentMethod: 'midtrans',
                customerName: $this->customer_name
            );
            $this->cart = [];
            $this->customer_name = '';
            $this->payment_amount = 0;
            $this->midtransSnapToken = null;
            $this->midtransOrderId = null;
            $this->showMidtransPopup = false;

            session()->flash('success', 'Pembayaran berhasil! Invoice: ' . $transaction->invoice_number);
        } catch (\RuntimeException $e) {
            session()->flash('error', $e->getMessage());
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
        $categories = Category::where('store_id', $this->storeId)->where('is_active', true)->get();

        return view('livewire.cashier', compact('products', 'categories'));
    }
}
