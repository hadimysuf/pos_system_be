<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Resources\ProductResource;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category');

        // 🔍 Search
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('sku', 'like', "%{$request->search}%");
            });
        }

        // 📂 Filter category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // 🚨 Low stock (query param)
        if ($request->boolean('low_stock')) {
            $query->whereColumn('stock', '<=', 'low_stock_threshold');
        }

        return ProductResource::collection(
            $query->latest()->paginate(10)
        );
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:150',
            'price' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
        ]);

        $category = Category::findOrFail($validated['category_id']);

        $validated['sku'] = $this->generateSku($category);

        $product = Product::create($validated);

        return new ProductResource($product->load('category'));
    }

    public function show(Product $product)
    {
        return new ProductResource($product->load('category'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'price' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
        ]);

        $product->update($validated);

        return new ProductResource($product->load('category'));
    }

    public function destroy(Product $product)
    {
        $product->delete(); // soft delete

        return response()->json([
            'message' => 'Product deleted successfully'
        ]);
    }

    /* ===========================
       🚨 LOW STOCK ENDPOINT
    =========================== */
    public function lowStock()
    {
        $products = Product::whereColumn('stock', '<=', 'low_stock_threshold')
            ->orderBy('stock', 'asc')
            ->get();

        return response()->json([
            'total' => $products->count(),
            'data'  => ProductResource::collection($products),
        ]);
    }

    /* ===========================
       🔑 SKU GENERATOR
    =========================== */
    private function generateSku(Category $category): string
    {
        $prefix = strtoupper(Str::substr($category->name, 0, 3));
        $count  = Product::where('category_id', $category->id)
            ->withTrashed()
            ->count() + 1;

        return $prefix . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }
}
