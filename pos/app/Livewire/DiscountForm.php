<?php

namespace App\Livewire;

use App\Models\Discount;
use App\Models\Product;
use Livewire\Component;

class DiscountForm extends Component
{
    public ?Discount $discount = null;
    public $name = '';
    public $type = 'percentage';
    public $value = 0;
    public $min_purchase = null;
    public $start_date = '';
    public $end_date = '';
    public $priority = 0;
    public $stackable = false;
    public $is_active = true;
    public $selectedProducts = [];

    public function mount(?Discount $discount = null): void
    {
        $this->discount = $discount;

        if ($discount) {
            $this->name = $discount->name;
            $this->type = $discount->type;
            $this->value = $discount->value;
            $this->min_purchase = $discount->min_purchase;
            $this->start_date = $discount->start_date->format('Y-m-d\TH:i');
            $this->end_date = $discount->end_date->format('Y-m-d\TH:i');
            $this->priority = $discount->priority;
            $this->stackable = $discount->stackable;
            $this->is_active = $discount->is_active;
            $this->selectedProducts = $discount->products->pluck('id')->map(fn($id) => (string) $id)->toArray();
        } else {
            $this->start_date = now()->format('Y-m-d\TH:i');
            $this->end_date = now()->addDays(30)->format('Y-m-d\TH:i');
        }
    }

    public function save(): void
    {
        $this->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0',
            'min_purchase' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'priority' => 'required|integer|min:0',
            'stackable' => 'boolean',
            'is_active' => 'boolean',
        ]);

        $storeId = auth()->user()->store_id;

        $data = [
            'store_id' => $storeId,
            'name' => $this->name,
            'type' => $this->type,
            'value' => $this->value,
            'min_purchase' => $this->min_purchase ?: null,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'priority' => (int) $this->priority,
            'stackable' => $this->stackable,
            'is_active' => $this->is_active,
        ];

        if ($this->discount) {
            $this->discount->update($data);
            $this->discount->products()->sync($this->selectedProducts);
            session()->flash('success', 'Diskon berhasil diperbarui.');
        } else {
            $discount = Discount::create($data);
            $discount->products()->sync($this->selectedProducts);
            session()->flash('success', 'Diskon berhasil dibuat.');
            $this->redirect(route('discounts.index'));
        }
    }

    public function render()
    {
        $storeId = auth()->user()->store_id;
        $products = Product::byStore($storeId)->where('is_active', true)->orderBy('name')->get();

        return view('livewire.discount-form', compact('products'));
    }
}
