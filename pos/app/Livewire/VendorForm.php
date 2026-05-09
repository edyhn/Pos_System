<?php

namespace App\Livewire;

use App\Models\Vendor;
use Livewire\Component;

class VendorForm extends Component
{
    public $vendorId;
    public $name;
    public $contact_person;
    public $phone;
    public $email;
    public $address;
    public $is_active = true;

    public $isEdit = false;
    public $storeId;

    protected $rules = [
        'name' => 'required|min:2|max:255',
        'contact_person' => 'nullable|max:255',
        'phone' => 'nullable|max:20',
        'email' => 'nullable|email|max:255',
        'address' => 'nullable',
        'is_active' => 'boolean',
    ];

    public function mount($id = null)
    {
        $this->storeId = auth()->user()->store_id;

        if ($id) {
            $this->isEdit = true;
            $this->vendorId = $id;
            $vendor = Vendor::where('store_id', $this->storeId)->findOrFail($id);
            $this->name = $vendor->name;
            $this->contact_person = $vendor->contact_person;
            $this->phone = $vendor->phone;
            $this->email = $vendor->email;
            $this->address = $vendor->address;
            $this->is_active = $vendor->is_active;
        }
    }

    public function save()
    {
        $this->validate();

        $data = [
            'store_id' => $this->storeId,
            'name' => $this->name,
            'contact_person' => $this->contact_person,
            'phone' => $this->phone,
            'email' => $this->email,
            'address' => $this->address,
            'is_active' => $this->is_active,
        ];

        if ($this->isEdit) {
            Vendor::where('store_id', $this->storeId)->where('id', $this->vendorId)->update($data);
            session()->flash('message', 'Vendor berhasil diupdate.');
        } else {
            Vendor::create($data);
            session()->flash('message', 'Vendor berhasil ditambahkan.');
        }

        return redirect()->route('vendors.index');
    }

    public function render()
    {
        return view('livewire.vendor-form');
    }
}
