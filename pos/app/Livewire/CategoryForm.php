<?php

namespace App\Livewire;

use App\Models\Category;
use Livewire\Component;
use Illuminate\Support\Str;

class CategoryForm extends Component
{
    public $categoryId;
    public $name;
    public $slug;
    public $description;
    public $is_active = true;

    public $storeId;
    public $isEdit = false;

    protected $rules = [
        'name' => 'required|min:2|max:255',
        'slug' => 'required|min:2|max:255',
        'description' => 'nullable',
        'is_active' => 'boolean',
    ];

    public function mount($id = null)
    {
        $this->storeId = auth()->user()->store_id;

        if ($id) {
            $this->isEdit = true;
            $this->categoryId = $id;
            $category = Category::where('store_id', $this->storeId)->findOrFail($id);
            $this->name = $category->name;
            $this->slug = $category->slug;
            $this->description = $category->description;
            $this->is_active = $category->is_active;
        }
    }

    public function updatedName($value)
    {
        if (!$this->isEdit) {
            $this->slug = Str::slug($value);
        }
    }

    public function save()
    {
        $this->validate();

        $this->validate([
            'slug' => 'required|unique:categories,slug,' . ($this->categoryId ?? '') . ',id,store_id,' . $this->storeId,
        ]);

        $data = [
            'store_id' => $this->storeId,
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'is_active' => $this->is_active,
        ];

        if ($this->isEdit) {
            Category::where('store_id', $this->storeId)->where('id', $this->categoryId)->update($data);
            \App\Services\ActivityLogger::log('update', 'Memperbarui kategori: ' . $this->name);
            session()->flash('message', 'Kategori berhasil diupdate.');
        } else {
            Category::create($data);
            \App\Services\ActivityLogger::log('create', 'Menambahkan kategori baru: ' . $this->name);
            session()->flash('message', 'Kategori berhasil ditambahkan.');
        }

        return redirect()->route('categories.index');
    }

    public function render()
    {
        return view('livewire.category-form');
    }
}
