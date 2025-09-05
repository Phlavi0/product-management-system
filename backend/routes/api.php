<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Models\Product;
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::get('/products', [ProductController::class, 'index']);
Route::post('/products', [ProductController::class, 'store']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::put('/products/{product}', [ProductController::class, 'update']);
Route::delete('/products/{product}', [ProductController::class, 'destroy']);

// Additional product routes
Route::get('/products/{product}/children', [ProductController::class, 'children']);
Route::get('/products-tree', [ProductController::class, 'tree']);

Route::get('/debug-query', function () {
    \DB::enableQueryLog();
    
    // Test step by step
    $step1 = Product::with(['parent', 'children']);
    echo "Step 1 - After with(): " . get_class($step1) . "<br>";
    
    $step2 = $step1->orderBy('product_name');
    echo "Step 2 - After orderBy(): " . get_class($step2) . "<br>";
    
    $step3 = $step2->get();
    echo "Step 3 - After get(): " . get_class($step3) . "<br>";
    
    // Show the actual SQL query
    echo "SQL Query: " . json_encode(\DB::getQueryLog(), JSON_PRETTY_PRINT) . "<br>";
    
    return $step3;
});