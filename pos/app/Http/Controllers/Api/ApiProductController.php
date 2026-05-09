<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $storeId = $request->user()->store_id;

        $products = Product::where('store_id', $storeId)
            ->with('category:id,name')
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('sku', 'like', "%{$request->search}%")
                  ->orWhere('barcode', 'like', "%{$request->search}%");
            }))
            ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
            ->when($request->has('is_active'), fn($q) => $q->where('is_active', $request->boolean('is_active')))
            ->when($request->low_stock, fn($q) => $q->whereColumn('stock', '<=', 'min_stock'))
            ->orderBy($request->sort ?? 'name', $request->order ?? 'asc')
            ->paginate($request->per_page ?? 50);

        return response()->json($products);
    }

    public function show(Request $request, Product $product): JsonResponse
    {
        if ($product->store_id !== $request->user()->store_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return response()->json($product->load('category'));
    }

    public function store(Request $request): JsonResponse
    {
        $storeId = $request->user()->store_id;

        $data = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|max:255',
            'sku' => 'required|max:100|unique:products,sku,NULL,id,store_id,' . $storeId,
            'barcode' => 'nullable|max:100',
            'price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'unit' => 'nullable|max:50',
            'is_taxed' => 'boolean',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'is_subscription' => 'boolean',
            'subscription_days' => 'nullable|integer|min:1',
            'description' => 'nullable',
            'is_active' => 'boolean',
        ]);

        $data['store_id'] = $storeId;
        $data['slug'] = Str::slug($data['name']) . '-' . Str::random(5);

        $product = Product::create($data);

        return response()->json($product, 201);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        if ($product->store_id !== $request->user()->store_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $storeId = $request->user()->store_id;

        $data = $request->validate([
            'category_id' => 'sometimes|exists:categories,id',
            'name' => 'sometimes|max:255',
            'sku' => 'sometimes|max:100|unique:products,sku,' . $product->id . ',id,store_id,' . $storeId,
            'barcode' => 'nullable|max:100',
            'price' => 'sometimes|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
            'min_stock' => 'sometimes|integer|min:0',
            'unit' => 'nullable|max:50',
            'is_taxed' => 'boolean',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'is_subscription' => 'boolean',
            'subscription_days' => 'nullable|integer|min:1',
            'description' => 'nullable',
            'is_active' => 'boolean',
        ]);

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']) . '-' . Str::random(5);
        }

        $product->update($data);

        return response()->json($product);
    }

    public function destroy(Request $request, Product $product): JsonResponse
    {
        if ($product->store_id !== $request->user()->store_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $product->update(['is_active' => false]);

        return response()->json(['message' => 'Product deactivated.']);
    }
}
