<?php

namespace App\Livewire;

use App\Models\Notification;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\ReceiptReprintRequest;
use App\Models\RefundRequest;
use Livewire\Component;

class NotificationBell extends Component
{
    public $count = 0;
    public $showDropdown = false;
    public $lowStockProducts = [];
    public $pendingApprovals = [];
    public $pendingRequests = [];
    public $draftPos = [];
    public $user;

    protected function getListeners()
    {
        return ['check-notifications' => 'refreshCount'];
    }

    public function refreshCount()
    {
        $user = auth()->user();
        $this->user = $user;
        $storeId = $user->store_id;

        if ($user->isOwner()) {
            $reprintPending = ReceiptReprintRequest::where('store_id', $storeId)
                ->where('status', 'pending')->count();
            $refundPending = RefundRequest::where('store_id', $storeId)
                ->where('status', 'pending')->count();
            $this->pendingApprovals = ReceiptReprintRequest::where('store_id', $storeId)
                ->where('status', 'pending')
                ->with('user')
                ->latest()->take(5)->get()
                ->concat(
                    RefundRequest::where('store_id', $storeId)
                        ->where('status', 'pending')
                        ->with('user')
                        ->latest()->take(5)->get()
                );

            $this->lowStockProducts = Product::byStore($storeId)
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->where(function ($q2) {
                        $q2->whereColumn('stock', '<=', 'min_stock')
                           ->where('min_stock', '>', 0);
                    })->orWhere('stock', 0);
                })
                ->latest('stock')->take(5)->get();

            $this->draftPos = PurchaseOrder::where('store_id', $storeId)
                ->where('status', 'draft')
                ->latest()->take(5)->get();

            $this->count = $reprintPending + $refundPending
                + $this->lowStockProducts->count()
                + $this->draftPos->count();
        } else {
            $reprintPending = ReceiptReprintRequest::where('store_id', $storeId)
                ->where('user_id', $user->id)
                ->where('status', 'pending')->count();
            $refundPending = RefundRequest::where('store_id', $storeId)
                ->where('user_id', $user->id)
                ->where('status', 'pending')->count();

            $this->pendingRequests = ReceiptReprintRequest::where('store_id', $storeId)
                ->where('user_id', $user->id)
                ->where('status', 'pending')
                ->latest()->take(5)->get()
                ->concat(
                    RefundRequest::where('store_id', $storeId)
                        ->where('user_id', $user->id)
                        ->where('status', 'pending')
                        ->latest()->take(5)->get()
                );

            $this->lowStockProducts = Product::byStore($storeId)
                ->where('is_active', true)
                ->where(function ($q) {
                    $q->where(function ($q2) {
                        $q2->whereColumn('stock', '<=', 'min_stock')
                           ->where('min_stock', '>', 0);
                    })->orWhere('stock', 0);
                })
                ->latest('stock')->take(5)->get();

            $this->count = $reprintPending + $refundPending
                + $this->lowStockProducts->count();
        }
    }

    public function toggle()
    {
        $this->showDropdown = !$this->showDropdown;
        if ($this->showDropdown) {
            $this->refreshCount();
        }
    }

    public function markAllRead()
    {
        $userId = auth()->id();
        Notification::where('notifiable_id', $userId)
            ->where('notifiable_type', \App\Models\User::class)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
        $this->showDropdown = false;
    }

    public function render()
    {
        return view('livewire.notification-bell');
    }
}
