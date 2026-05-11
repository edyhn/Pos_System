<?php

namespace App\Livewire;

use App\Models\Discount;
use Livewire\Component;
use Livewire\WithPagination;

class DiscountIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $sortField = 'created_at';
    public $sortDirection = 'desc';

    protected $queryString = ['search'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function sortBy(string $field): void
    {
        if ($this->sortField === $field) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortField = $field;
            $this->sortDirection = 'asc';
        }
    }

    public function toggleActive(int $id): void
    {
        $discount = Discount::where('store_id', auth()->user()->store_id)->findOrFail($id);
        $discount->update(['is_active' => !$discount->is_active]);
    }

    public function delete(int $id): void
    {
        $discount = Discount::where('store_id', auth()->user()->store_id)->findOrFail($id);
        $discount->delete();
        session()->flash('success', 'Diskon berhasil dihapus.');
    }

    public function render()
    {
        $storeId = auth()->user()->store_id;

        $query = Discount::byStore($storeId);

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        $discounts = $query->orderBy($this->sortField, $this->sortDirection)->paginate(10);

        return view('livewire.discount-index', compact('discounts'));
    }
}
