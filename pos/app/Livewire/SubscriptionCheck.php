<?php

namespace App\Livewire;

use App\Models\Subscription;
use Livewire\Component;

class SubscriptionCheck extends Component
{
    public $storeId;
    public $search = '';
    public $subscriptions = [];
    public $searched = false;

    public function mount()
    {
        $this->storeId = auth()->user()->store_id;
    }

    public function check()
    {
        $this->searched = true;

        $this->subscriptions = Subscription::where('store_id', $this->storeId)
            ->when($this->search, function ($q) {
                $q->where('customer_identifier', 'like', '%' . $this->search . '%');
            })
            ->with('product', 'transaction')
            ->orderBy('end_date', 'asc')
            ->get();
    }

    public function render()
    {
        return view('livewire.subscription-check');
    }
}
