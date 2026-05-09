<?php

namespace App\Livewire;

use App\Models\StockMovement;
use Livewire\Component;
use Livewire\WithPagination;

class StockMovementOut extends Component
{
    use WithPagination;

    public $storeId;
    public $search = '';
    public $dateFrom = '';
    public $dateTo = '';

    public function mount()
    {
        $this->storeId = auth()->user()->store_id;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function updatingDateFrom()
    {
        $this->resetPage();
    }

    public function updatingDateTo()
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = StockMovement::where('store_id', $this->storeId)
            ->where('type', 'out')
            ->with('product', 'user');

        if ($this->search) {
            $query->whereHas('product', function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->dateFrom) {
            $query->whereDate('created_at', '>=', $this->dateFrom);
        }

        if ($this->dateTo) {
            $query->whereDate('created_at', '<=', $this->dateTo);
        }

        $movements = $query->latest()->paginate(20);

        return view('livewire.stock-movement-out', compact('movements'));
    }
}
