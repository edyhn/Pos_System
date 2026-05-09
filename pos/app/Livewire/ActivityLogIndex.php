<?php

namespace App\Livewire;

use App\Models\ActivityLog;
use Livewire\Component;
use Livewire\WithPagination;

class ActivityLogIndex extends Component
{
    use WithPagination;

    public $storeId;
    public $search = '';

    public function mount()
    {
        $this->storeId = auth()->user()->store_id;
    }

    public function render()
    {
        $logs = ActivityLog::where('store_id', $this->storeId)
            ->when($this->search, function ($q) {
                $q->where('action', 'like', "%{$this->search}%")
                  ->orWhere('description', 'like', "%{$this->search}%");
            })
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('livewire.activity-log-index', compact('logs'));
    }
}
