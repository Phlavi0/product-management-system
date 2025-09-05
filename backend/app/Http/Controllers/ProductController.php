<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    /**
     * Display a listing of products with optional filtering
     */
    public function index(Request $request): JsonResponse
    {
        try {            
            $query = Product::with(['parent', 'children']);

            // Debug each filter condition
            if ($request->has('search') && $request->search) {
                $query->where('product_name', 'like', '%' . $request->search . '%');
            }

            if ($request->has('type') && $request->type) {
                $query->where('product_type', $request->type);
            }

            if ($request->has('parent_id')) {
                if ($request->parent_id === 'null' || $request->parent_id === '') {
                    $query->whereNull('product_parent_id');
                } else {
                    $query->where('product_parent_id', $request->parent_id);
                }
            }

            if ($request->has('roots_only') && $request->roots_only) {
                $query->whereNull('product_parent_id');
            }

            $products = $query->orderBy('product_name')->get();

            return response()->json([
                'success' => true,
                'data' => $products,
                'count' => $products->count()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching products: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Store a newly created product
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        try {
            $product = Product::create($request->validated());
            $product->load(['parent', 'children']);

            return response()->json([
                'success' => true,
                'message' => 'Product created successfully',
                'data' => $product
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified product
     */
    public function show($id): JsonResponse
    {
        try {
            $product = Product::with(['parent', 'children'])->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }
    }

    /**
     * Update the specified product
     */
    public function update(UpdateProductRequest $request, $id): JsonResponse
    {
        try {
            $product = Product::findOrFail($id);
            $product->update($request->validated());
            $product->load(['parent', 'children']);

            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => $product
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified product
     */
    public function destroy($id): JsonResponse
    {
        try {
            $product = Product::findOrFail($id);
            
            // Check if product has children
            if ($product->children()->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete product with child products'
                ], 422);
            }

            $product->delete();

            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting product: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get children of a specific product
     */
    public function children($id): JsonResponse
    {
        try {
            $product = Product::findOrFail($id);
            $children = $product->children()->with(['parent', 'children'])->get();

            return response()->json([
                'success' => true,
                'data' => $children
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Product not found'
            ], 404);
        }
    }

    /**
     * Get product tree structure
     */
    public function tree(): JsonResponse
    {
        try {
            $rootProducts = Product::with('descendants')
                ->whereNull('product_parent_id')
                ->orderBy('product_name')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $rootProducts
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching product tree: ' . $e->getMessage()
            ], 500);
        }
    }
}