<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $productId = $this->route('product');
        
        return [
            'product_name' => 'required|string|max:255',
            'product_type' => 'required|string|max:100',
            'product_parent_id' => [
                'nullable',
                'exists:products,product_id',
                function ($attribute, $value, $fail) use ($productId) {
                    if ($value == $productId) {
                        $fail('A product cannot be its own parent.');
                    }
                }
            ]
        ];
    }
}