<?php

namespace App\Livewire;

use App\Models\Category;
use Livewire\Component;
use Livewire\WithPagination;

class CategoryIndex extends Component
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
        $category = Category::where('store_id', $this->storeId)->findOrFail($id);
        $name = $category->name;
        $category->delete();
        \App\Services\ActivityLogger::log('delete', 'Menghapus kategori: ' . $name);
        session()->flash('message', 'Kategori berhasil dihapus.');
    }

    public function toggleActive($id)
    {
        $category = Category::where('store_id', $this->storeId)->findOrFail($id);
        $category->update(['is_active' => !$category->is_active]);
        $status = $category->is_active ? 'mengaktifkan' : 'menonaktifkan';
        \App\Services\ActivityLogger::log('update', $status . ' kategori: ' . $category->name);
        session()->flash('message', 'Status kategori berhasil diubah.');
    }

    public function render()
    {
        $query = Category::where('store_id', $this->storeId);

        if ($this->search) {
            $query->where('name', 'like', '%' . $this->search . '%');
        }

        $categories = $query->orderBy('name')->paginate(10);

        return view('livewire.category-index', compact('categories'));
    }
}
