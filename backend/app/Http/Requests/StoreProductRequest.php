<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'product_name' => 'required|string|max:255',
            'product_type' => 'required|string|max:100',
            'product_parent_id' => 'nullable|exists:products,product_id'
        ];
    }

    public function messages()
    {
        return [
            'product_name.required' => 'Product name is required.',
            'product_type.required' => 'Product type is required.',
            'product_parent_id.exists' => 'The selected parent product does not exist.'
        ];
    }
}