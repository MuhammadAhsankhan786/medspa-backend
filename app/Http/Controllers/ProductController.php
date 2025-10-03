<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // List all products
    public function index()
    {
        return Product::all();
    }

    // Add new product
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'SKU' => 'required|string|unique:products',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'location_id' => 'nullable|exists:locations,id',
        ]);

        $product = Product::create($data);
        return response()->json($product, 201);
    }

    // Show single product
    public function show(Product $product)
    {
        return $product;
    }

    // Update product
    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'SKU' => 'sometimes|string|unique:products,SKU,' . $product->id,
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric',
            'stock' => 'sometimes|integer',
            'location_id' => 'nullable|exists:locations,id',
        ]);

        $product->update($data);
        return response()->json($product);
    }

    // Delete product
    public function destroy(Product $product)
    {
        $product->delete();
        return response()->json(null, 204);
    }
}
