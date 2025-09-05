<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $primaryKey = 'product_id';
    
    protected $fillable = [
        'product_name',
        'product_type',
        'product_parent_id'
    ];

    protected $casts = [
        'product_parent_id' => 'integer'
    ];

    // Relationship to parent product
    public function parent()
    {
        return $this->belongsTo(Product::class, 'product_parent_id', 'product_id');
    }

    // Relationship to child products
    public function children()
    {
        return $this->hasMany(Product::class, 'product_parent_id', 'product_id');
    }

    // Get all descendants recursively
    public function descendants()
    {
        return $this->children()->with('descendants');
    }

    // Scope for root products (no parent)
    public function scopeRoots($query)
    {
        return $query->whereNull('product_parent_id');
    }

    // Scope for filtering by type
    public function scopeOfType($query, $type)
    {
        return $query->where('product_type', $type);
    }

    // Scope for searching by name
    public function scopeSearch($query, $search)
    {
        return $query->where('product_name', 'like', '%' . $search . '%');
    }
}