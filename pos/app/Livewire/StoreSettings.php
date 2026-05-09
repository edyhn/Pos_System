<?php

namespace App\Livewire;

use App\Models\Store;
use App\Models\StoreSetting;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;

class StoreSettings extends Component
{
    use WithFileUploads;

    public $storeId;
    public $name;
    public $code;
    public $phone;
    public $address;
    public $receipt_footer;
    public $printer_type;
    public $printer_address;
    public $printer_port;
    public $qris_image;
    public $existingQrisImage;

    public function mount()
    {
        $user = auth()->user();
        $storeId = $user->store_id;

        if ($user->isOwner() && !$storeId) {
            $storeId = Store::where('is_active', true)->first()?->id;
        }

        $this->storeId = $storeId;
        $store = Store::findOrFail($storeId);
        $this->name = $store->name;
        $this->code = $store->code;
        $this->phone = $store->phone;
        $this->address = $store->address;
        $this->receipt_footer = $store->receipt_footer;
        $this->existingQrisImage = $store->qris_image;

        $this->printer_type = StoreSetting::where('store_id', $storeId)
            ->where('key', 'printer_type')->first()?->value ?? 'network';
        $this->printer_address = StoreSetting::where('store_id', $storeId)
            ->where('key', 'printer_address')->first()?->value ?? '';
        $this->printer_port = StoreSetting::where('store_id', $storeId)
            ->where('key', 'printer_port')->first()?->value ?? '9100';
    }

    public function save()
    {
        $data = [
            'name' => $this->name,
            'code' => $this->code,
            'phone' => $this->phone,
            'address' => $this->address,
            'receipt_footer' => $this->receipt_footer,
        ];

        if ($this->qris_image) {
            $path = $this->qris_image->store('qris', 'public');
            $data['qris_image'] = $path;

            if ($this->existingQrisImage) {
                Storage::disk('public')->delete($this->existingQrisImage);
            }
        }

        Store::where('id', $this->storeId)->update($data);

        StoreSetting::updateOrCreate(
            ['store_id' => $this->storeId, 'key' => 'printer_type'],
            ['value' => $this->printer_type]
        );
        StoreSetting::updateOrCreate(
            ['store_id' => $this->storeId, 'key' => 'printer_address'],
            ['value' => $this->printer_address]
        );
        StoreSetting::updateOrCreate(
            ['store_id' => $this->storeId, 'key' => 'printer_port'],
            ['value' => $this->printer_port]
        );

        $this->existingQrisImage = $data['qris_image'] ?? $this->existingQrisImage;
        $this->qris_image = null;

        session()->flash('message', 'Pengaturan berhasil disimpan.');
    }

    public function removeQris()
    {
        if ($this->existingQrisImage) {
            Storage::disk('public')->delete($this->existingQrisImage);
        }
        Store::where('id', $this->storeId)->update(['qris_image' => null]);
        $this->existingQrisImage = null;
        session()->flash('message', 'QRIS berhasil dihapus.');
    }

    public function render()
    {
        return view('livewire.store-settings');
    }
}
