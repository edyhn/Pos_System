<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Discount;
use App\Models\Product;
use App\Models\Transaction;
use Livewire\Component;
use App\Services\CheckoutService;

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
    public $barcode = '';
    public $reference_number = '';

    protected function getListeners(): array
    {
        return [
            'productSelected',
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

        if ($product->stock <= 0) {
            session()->flash('error', "{$product->name} sudah habis.");
            return;
        }

        $existingKey = collect($this->cart)->search(fn($item) => $item['product_id'] === $productId);

        if ($existingKey !== false) {
            $currentQty = $this->cart[$existingKey]['quantity'];
            if ($currentQty + 1 > $product->stock) {
                session()->flash('error', "Stok {$product->name} hanya tersedia {$product->stock}.");
                return;
            }
            $this->cart[$existingKey]['quantity'] = $currentQty + 1;
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

    public function scanBarcode($barcode = null): void
    {
        $barcode = $barcode ? trim($barcode) : trim($this->barcode);
        if (empty($barcode)) return;

        $product = Product::where('store_id', $this->storeId)
            ->where('is_active', true)
            ->where(function ($q) use ($barcode) {
                $q->where('barcode', $barcode)
                  ->orWhere('sku', $barcode);
            })
            ->first();

        if (!$product) {
            session()->flash('error', "Produk dengan barcode/SKU '$barcode' tidak ditemukan.");
            $this->barcode = '';
            return;
        }

        $this->addToCart($product->id);
        $this->barcode = '';

        $this->dispatch('barcodeScanned', productName: $product->name);
    }

    public function updateQuantity($index, $quantity): void
    {
        if (!isset($this->cart[$index])) return;
        $maxStock = $this->cart[$index]['stock'];
        $quantity = max(1, min((int) $quantity, $maxStock));
        $this->cart[$index]['quantity'] = $quantity;
        $this->cart[$index]['subtotal'] = $quantity * $this->cart[$index]['price'];

        if ((int) $quantity >= $maxStock) {
            session()->flash('error', "Stok {$this->cart[$index]['name']} maksimal {$maxStock}.");
        }
    }

    public function getSubtotalProperty(): float
    {
        return array_sum(array_column($this->cart, 'subtotal'));
    }

    public function getDiscountAmountProperty(): float
    {
        return $this->discountData['total_discount'];
    }

    public function getDiscountDataProperty(): array
    {
        if (empty($this->cart)) {
            return ['total_discount' => 0, 'items' => []];
        }

        $storeId = $this->storeId;
        $cartSubtotal = $this->subtotal;

        $activeDiscounts = Discount::byStore($storeId)->active()
            ->with('products')->orderBy('priority', 'desc')->get();

        $discountDetails = [];
        $totalDiscount = 0;

        foreach ($this->cart as $index => $item) {
            $itemDiscount = 0;
            $itemDiscountNames = [];

            foreach ($activeDiscounts as $discount) {
                $discountProductIds = $discount->products->pluck('id');
                $appliesToAll = $discountProductIds->isEmpty();
                $appliesToProduct = $appliesToAll || $discountProductIds->contains($item['product_id']);

                if (!$appliesToProduct) continue;
                if ($discount->min_purchase && $cartSubtotal < (float) $discount->min_purchase) continue;

                $potential = $discount->type === 'percentage'
                    ? $item['subtotal'] * ((float) $discount->value / 100)
                    : (float) $discount->value * $item['quantity'];

                $potential = min($potential, $item['subtotal']);
                if ($potential <= 0) continue;

                if ($discount->stackable) {
                    $itemDiscount += $potential;
                    $itemDiscountNames[] = $discount->name;
                } elseif ($potential > $itemDiscount) {
                    $itemDiscount = $potential;
                    $itemDiscountNames = [$discount->name];
                }
            }

            $itemDiscount = min($itemDiscount, $item['subtotal']);
            $discountDetails[$index] = [
                'amount' => $itemDiscount,
                'names' => $itemDiscountNames,
            ];
            $totalDiscount += $itemDiscount;
        }

        return ['total_discount' => $totalDiscount, 'items' => $discountDetails];
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
        return max(0, $this->subtotal + $this->taxAmount - $this->discountAmount);
    }

    public function getChangeProperty(): float
    {
        if ($this->payment_amount > 0) {
            return max(0, $this->payment_amount - $this->total);
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
            'payment_method' => 'required|in:cash,qris,transfer,debit_card',
            'customer_name' => 'nullable|max:255',
            'reference_number' => 'nullable|max:100',
        ]);

        try {
            $transaction = $checkoutService->processCheckout(
                cart: $this->cart,
                customerName: $this->customer_name,
                paymentMethod: $this->payment_method,
                paymentAmount: (float) $this->payment_amount,
                taxEnabled: $this->tax_enabled,
                storeId: $this->storeId,
                userId: auth()->id(),
                referenceNumber: $this->reference_number ?: null,
                discountData: $this->discountData,
            );

            $this->dispatch('transactionCompleted',
                transactionId: $transaction->id,
                invoiceNumber: $transaction->invoice_number,
                total: $transaction->total_amount,
                paymentMethod: $this->payment_method,
                customerName: $this->customer_name,
                discountAmount: $this->discountAmount,
            );
            $this->cart = [];
            $this->customer_name = '';
            $this->payment_amount = 0;
            $this->reference_number = '';

            session()->flash('success', 'Transaksi berhasil! Invoice: ' . $transaction->invoice_number);
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
                  ->orWhere('sku', 'like', '%' . $this->search . '%')
                  ->orWhere('barcode', 'like', '%' . $this->search . '%');
            });
        }

        $products = $query->orderBy('name')->take(200)->get();
        $categories = Category::where('store_id', $this->storeId)->where('is_active', true)->get();
        $activeDiscounts = Discount::byStore($this->storeId)->active()->with('products')->orderBy('priority', 'desc')->get();

        $discountedProductIds = collect();
        foreach ($activeDiscounts as $discount) {
            $discountProducts = $discount->products->pluck('id');
            if ($discountProducts->isEmpty()) {
                $discountedProductIds = $products->pluck('id');
                break;
            }
            $discountedProductIds = $discountedProductIds->merge($discountProducts);
        }

        return view('livewire.cashier', compact('products', 'categories', 'activeDiscounts', 'discountedProductIds'));
    }
}
