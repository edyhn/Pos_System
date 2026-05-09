<?php

namespace App\Livewire;

use App\Models\Store;
use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserForm extends Component
{
    use WithFileUploads;

    public $userId;
    public $user_id;
    public $name;
    public $password;
    public $password_confirmation;
    public $phone;
    public $role = 'cashier';
    public $store_id;
    public $is_active = true;
    public $photo;
    public $existingPhoto;

    public $isEdit = false;
    public $stores;

    protected function rules()
    {
        $rules = [
            'name' => 'required|min:2|max:255',
            'phone' => 'nullable|max:20',
            'role' => 'required|in:owner,cashier',
            'store_id' => 'nullable|exists:stores,id',
            'is_active' => 'boolean',
            'photo' => 'nullable|image|max:1024',
        ];

        if ($this->isEdit) {
            $rules['user_id'] = 'required|unique:users,user_id,' . $this->userId;
            if ($this->password) {
                $rules['password'] = 'min:6|confirmed';
            }
        } else {
            $rules['user_id'] = 'required|unique:users,user_id';
            $rules['password'] = 'required|min:6|confirmed';
        }

        return $rules;
    }

    public function mount($id = null)
    {
        $this->stores = Store::where('is_active', true)->get();

        if ($id) {
            $this->isEdit = true;
            $this->userId = $id;
            $user = User::findOrFail($id);
            $this->user_id = $user->user_id;
            $this->name = $user->name;
            $this->phone = $user->phone;
            $this->role = $user->role;
            $this->store_id = $user->store_id;
            $this->is_active = $user->is_active;
            $this->existingPhoto = $user->photo;
        } else {
            $this->user_id = $this->generateUserId('cashier');
        }
    }

    public function generateUserId($role)
    {
        $prefix = $role === 'owner' ? 'OWN' : 'KSR';
        $lastUser = User::where('user_id', 'like', $prefix . '%')
            ->orderBy('user_id', 'desc')->first();

        if ($lastUser) {
            $lastNumber = (int) substr($lastUser->user_id, 3);
            return $prefix . str_pad($lastNumber + 1, 3, '0', STR_PAD_LEFT);
        }

        return $prefix . '001';
    }

    public function updatedRole($value)
    {
        if (!$this->isEdit) {
            $this->user_id = $this->generateUserId($value);
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'user_id' => $this->user_id,
            'name' => $this->name,
            'phone' => $this->phone,
            'role' => $this->role,
            'store_id' => $this->role === 'cashier' ? $this->store_id : null,
            'is_active' => $this->is_active,
        ];

        if ($this->photo) {
            $path = $this->photo->store('users', 'public');
            $data['photo'] = $path;
            if ($this->existingPhoto) {
                Storage::disk('public')->delete($this->existingPhoto);
            }
        }

        if ($this->password) {
            $data['password'] = Hash::make($this->password);
        }

        if ($this->isEdit) {
            User::where('id', $this->userId)->update($data);
            session()->flash('message', 'Pengguna berhasil diupdate.');
        } else {
            $data['password'] ??= Hash::make($this->password);
            User::create($data);
            session()->flash('message', 'Pengguna berhasil ditambahkan. ID: ' . $this->user_id);
        }

        return redirect()->route('users.index');
    }

    public function render()
    {
        return view('livewire.user-form');
    }
}
