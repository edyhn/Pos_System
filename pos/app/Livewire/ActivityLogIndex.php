<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class ActivityLogIndex extends Component
{
    use WithPagination;

    public $storeId;
    public $search = '';
    public $filterUser = '';
    public $filterAction = '';
    public $filterType = '';
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

    public function updatingFilterUser()
    {
        $this->resetPage();
    }

    public function updatingFilterAction()
    {
        $this->resetPage();
    }

    public function updatingFilterType()
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
        $users = User::where('store_id', $this->storeId)->orderBy('name')->get();

        $logs = ActivityLog::where('store_id', $this->storeId)
            ->when($this->search, function ($q) {
                $q->where(function ($q) {
                    $q->where('action', 'like', "%{$this->search}%")
                      ->orWhere('description', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterUser, function ($q) {
                $q->where('user_id', $this->filterUser);
            })
            ->when($this->filterAction, function ($q) {
                $q->where('action', $this->filterAction);
            })
            ->when($this->filterType, function ($q) {
                $q->where('description', 'like', $this->filterType . '%');
            })
            ->when($this->dateFrom, function ($q) {
                $q->whereDate('created_at', '>=', $this->dateFrom);
            })
            ->when($this->dateTo, function ($q) {
                $q->whereDate('created_at', '<=', $this->dateTo);
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        $typeOptions = [
            '' => 'Semua Tipe',
            'PO' => 'Purchase Order',
            'Barang masuk' => 'Barang Masuk',
            'Barang keluar' => 'Barang Keluar',
            'Stock opname' => 'Stock Opname',
            'Produk' => 'Produk',
            'Request refund' => 'Refund',
            'User' => 'User',
            'Kategori' => 'Kategori',
            'Vendor' => 'Vendor',
        ];

        return view('livewire.activity-log-index', compact('logs', 'users', 'typeOptions'));
    }
}
