<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApiCategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $categories = Category::where('store_id', $request->user()->store_id)
            ->withCount('products')
            ->orderBy('name')
            ->get();

        return response()->json($categories);
    }

    public function store(Request $request): JsonResponse
    {
        $storeId = $request->user()->store_id;

        $data = $request->validate([
            'name' => 'required|max:255',
            'description' => 'nullable',
        ]);

        $data['store_id'] = $storeId;
        $data['slug'] = Str::slug($data['name']);

        $category = Category::create($data);

        return response()->json($category, 201);
    }

    public function update(Request $request, Category $category): JsonResponse
    {
        if ($category->store_id !== $request->user()->store_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $data = $request->validate([
            'name' => 'sometimes|max:255',
            'description' => 'nullable',
        ]);

        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        $category->update($data);

        return response()->json($category);
    }

    public function destroy(Request $request, Category $category): JsonResponse
    {
        if ($category->store_id !== $request->user()->store_id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        if ($category->products()->exists()) {
            return response()->json(['message' => 'Kategori memiliki produk, tidak bisa dihapus.'], 409);
        }

        $category->delete();

        return response()->json(['message' => 'Category deleted.']);
    }
}
