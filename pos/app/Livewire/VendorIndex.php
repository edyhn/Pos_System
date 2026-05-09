<?php

namespace App\Livewire;

use App\Models\Vendor;
use Livewire\Component;
use Livewire\WithPagination;

class VendorIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $storeId;

    public function mount()
    {
        $this->storeId = auth()->user()->store_id;
    }

    public function updatingSearch()
    {
        $this->resetPage();
    }

    public function delete($id)
    {
        $vendor = Vendor::where('store_id', $this->storeId)->findOrFail($id);
        $vendor->delete();
        session()->flash('message', 'Vendor berhasil dihapus.');
    }

    public function render()
    {
        $query = Vendor::where('store_id', $this->storeId);

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('contact_person', 'like', '%' . $this->search . '%');
            });
        }

        $vendors = $query->orderBy('name')->paginate(10);

        return view('livewire.vendor-index', compact('vendors'));
    }
}
