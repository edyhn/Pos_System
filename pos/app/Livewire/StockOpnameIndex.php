<?php

namespace App\Livewire;

use App\Models\StockOpname;
use App\Models\StockOpnameItem;
use App\Models\Product;
use App\Models\StockMovement;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;

class StockOpnameIndex extends Component
{
    use WithPagination;

    public $storeId;
    public $search = '';
    public $showForm = false;
    public $showDetail = false;
    public $opnameId;
    public $date;
    public $notes = '';
    public $opnameItems = [];
    public $viewOpnameId;

    public function mount()
    {
        $this->storeId = auth()->user()->store_id;
        $this->date = now()->format('Y-m-d');
    }

    public function startOpname()
    {
        $this->showForm = true;
        $products = Product::where('store_id', $this->storeId)->where('is_active', true)->get();
        $this->opnameItems = $products->map(function ($p) {
            return [
                'product_id' => $p->id,
                'product_name' => $p->name,
                'system_stock' => $p->stock,
                'actual_stock' => $p->stock,
                'difference' => 0,
                'note' => '',
            ];
        })->toArray();
    }

    public function updatedOpnameItems($value, $key)
    {
        if (str_ends_with($key, '.actual_stock')) {
            $index = explode('.', $key)[0];
            $actual = (int) ($this->opnameItems[$index]['actual_stock'] ?? 0);
            $system = (int) ($this->opnameItems[$index]['system_stock'] ?? 0);
            $this->opnameItems[$index]['difference'] = $actual - $system;
        }
    }

    public function viewOpname($id)
    {
        $this->viewOpnameId = $id;
        $this->showDetail = true;
        $this->showForm = false;
    }

    public function closeDetail()
    {
        $this->showDetail = false;
        $this->viewOpnameId = null;
    }

    public function saveOpname()
    {
        $this->validate([
            'date' => 'required|date',
            'opnameItems.*.actual_stock' => 'required|integer|min:0',
        ]);

        DB::transaction(function () {
            $opname = StockOpname::create([
                'store_id' => $this->storeId,
                'user_id' => auth()->id(),
                'date' => $this->date,
                'status' => 'completed',
                'notes' => $this->notes,
            ]);

            foreach ($this->opnameItems as $item) {
                StockOpnameItem::create([
                    'stock_opname_id' => $opname->id,
                    'product_id' => $item['product_id'],
                    'system_stock' => $item['system_stock'],
                    'actual_stock' => $item['actual_stock'],
                    'difference' => $item['difference'],
                    'note' => $item['note'],
                ]);

                if ($item['difference'] != 0) {
                    $product = Product::find($item['product_id']);
                    $product->increment('stock', $item['difference']);

                    StockMovement::create([
                        'store_id' => $this->storeId,
                        'product_id' => $item['product_id'],
                        'user_id' => auth()->id(),
                        'reference_type' => 'opname',
                        'reference_id' => $opname->id,
                        'type' => $item['difference'] > 0 ? 'in' : 'out',
                        'quantity' => abs($item['difference']),
                        'note' => 'Stock opname: ' . $this->date,
                    ]);
                }
            }

            \App\Services\ActivityLogger::log('create', 'Stock opname selesai: ' . $this->date);
            $this->showForm = false;
            session()->flash('message', 'Stock opname selesai.');
        });
    }

    public function render()
    {
        $opnames = StockOpname::where('store_id', $this->storeId)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $viewOpname = null;
        $viewItems = collect();
        if ($this->showDetail && $this->viewOpnameId) {
            $viewOpname = StockOpname::with(['items.product', 'user'])
                ->where('store_id', $this->storeId)
                ->find($this->viewOpnameId);
            $viewItems = $viewOpname?->items ?? collect();
        }

        return view('livewire.stock-opname-index', compact('opnames', 'viewOpname', 'viewItems'));
    }
}
