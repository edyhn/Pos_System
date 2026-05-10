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
        $store = Store::find($storeId);
        if (!$store) {
            session()->flash('error', 'Tidak ada toko yang ditemukan. Hubungi administrator.');
            return;
        }
        $this->name = $store->name;
        $this->code = $store->code;
        $this->phone = $store->phone;
        $this->address = $store->address;
        $this->receipt_footer = $store->receipt_footer;
        $this->existingQrisImage = $store->qris_image;

        $settings = StoreSetting::where('store_id', $storeId)
            ->whereIn('key', ['printer_type', 'printer_address', 'printer_port'])
            ->get()
            ->keyBy('key');

        $this->printer_type = $settings->get('printer_type')?->value ?? 'network';
        $this->printer_address = $settings->get('printer_address')?->value ?? '';
        $this->printer_port = $settings->get('printer_port')?->value ?? '9100';
    }

    protected function rules()
    {
        return [
            'name' => 'required|min:2|max:255',
            'code' => 'nullable|max:50',
            'phone' => 'nullable|max:20',
            'address' => 'nullable',
            'receipt_footer' => 'nullable|max:500',
            'printer_type' => 'nullable|in:network,usb',
            'printer_address' => 'nullable|ip',
            'printer_port' => 'nullable|numeric|min:1|max:65535',
            'qris_image' => 'nullable|image|max:2048',
        ];
    }

    public function save()
    {
        $this->validate();

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
