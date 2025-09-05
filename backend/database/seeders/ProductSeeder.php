<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run()
    {
        // Create root categories
        $electronics = Product::create([
            'product_name' => 'Electronics',
            'product_type' => 'category',
            'product_parent_id' => null
        ]);

        $clothing = Product::create([
            'product_name' => 'Clothing',
            'product_type' => 'category',
            'product_parent_id' => null
        ]);

        // Electronics subcategories
        $computers = Product::create([
            'product_name' => 'Computers',
            'product_type' => 'subcategory',
            'product_parent_id' => $electronics->product_id
        ]);

        $phones = Product::create([
            'product_name' => 'Mobile Phones',
            'product_type' => 'subcategory',
            'product_parent_id' => $electronics->product_id
        ]);

        // Computer products
        Product::create([
            'product_name' => 'MacBook Pro 16"',
            'product_type' => 'product',
            'product_parent_id' => $computers->product_id
        ]);

        Product::create([
            'product_name' => 'Dell XPS 13',
            'product_type' => 'product',
            'product_parent_id' => $computers->product_id
        ]);

        // Phone products
        Product::create([
            'product_name' => 'iPhone 15 Pro',
            'product_type' => 'product',
            'product_parent_id' => $phones->product_id
        ]);

        Product::create([
            'product_name' => 'Samsung Galaxy S24',
            'product_type' => 'product',
            'product_parent_id' => $phones->product_id
        ]);

        // Clothing subcategories
        $mens = Product::create([
            'product_name' => "Men's Clothing",
            'product_type' => 'subcategory',
            'product_parent_id' => $clothing->product_id
        ]);

        $womens = Product::create([
            'product_name' => "Women's Clothing",
            'product_type' => 'subcategory',
            'product_parent_id' => $clothing->product_id
        ]);

        // Clothing products
        Product::create([
            'product_name' => 'Cotton T-Shirt',
            'product_type' => 'product',
            'product_parent_id' => $mens->product_id
        ]);

        Product::create([
            'product_name' => 'Denim Jeans',
            'product_type' => 'product',
            'product_parent_id' => $mens->product_id
        ]);

        Product::create([
            'product_name' => 'Summer Dress',
            'product_type' => 'product',
            'product_parent_id' => $womens->product_id
        ]);
    }
}