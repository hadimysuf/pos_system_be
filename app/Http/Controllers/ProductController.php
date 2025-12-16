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
        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%")
                ->orWhere('sku', 'like', "%{$request->search}%");
        }

        // 📂 Filter category
        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        // 🚨 Low stock only
        if ($request->low_stock) {
            $query->whereColumn('stock', '<=', 'low_stock_threshold');
        }

        $products = $query->latest()->paginate(10);

        return ProductResource::collection($products);
    }

    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'name' => 'required|string|max:150',
            'price' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
        ]);

        $category = Category::findOrFail($request->category_id);

        $sku = $this->generateSku($category);

        $product = Product::create([
            'category_id' => $category->id,
            'sku' => $sku,
            'name' => $request->name,
            'price' => $request->price,
            'cost' => $request->cost,
            'stock' => $request->stock,
            'low_stock_threshold' => $request->low_stock_threshold,
        ]);

        return new ProductResource($product);
    }

    public function show(Product $product)
    {
        return new ProductResource($product->load('category'));
    }

    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'price' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'low_stock_threshold' => 'required|integer|min:0',
        ]);

        $product->update($request->all());

        return new ProductResource($product);
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'message' => 'Product soft deleted'
        ]);
    }

    /* ===========================
       🔑 SKU GENERATOR
    =========================== */
    private function generateSku(Category $category): string
    {
        $prefix = strtoupper(Str::substr($category->name, 0, 3));
        $count = Product::where('category_id', $category->id)->withTrashed()->count() + 1;

        return $prefix . '-' . str_pad($count, 5, '0', STR_PAD_LEFT);
    }
}
