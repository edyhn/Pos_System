<?php

namespace App\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class UserIndex extends Component
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

    public function toggleActive($id)
    {
        $query = User::query();
        if (auth()->user()->store_id) {
            $query->where('store_id', auth()->user()->store_id);
        }
        $user = $query->findOrFail($id);
        $user->update(['is_active' => !$user->is_active]);
        \App\Services\ActivityLogger::log('update', ($user->is_active ? 'Mengaktifkan' : 'Menonaktifkan') . ' pengguna: ' . $user->name);
    }

    public function render()
    {
        $query = User::with('store');
        if ($this->storeId) {
            $query->where('store_id', $this->storeId);
        }

        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('user_id', 'like', '%' . $this->search . '%');
            });
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('livewire.user-index', compact('users'));
    }
}
