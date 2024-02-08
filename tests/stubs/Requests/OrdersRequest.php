<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrdersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'order_no' => ['string', 'max:255'],
            'category' => ['exists:categories,id'],
            'product_slug' => ['exists:products,slug'],
        ];

        if ($order = $this->route('order')) {
            //
        } else {
            $rules['order_no'][] = 'required';
            $rules['category'][] = 'required';
            $rules['product_slug'][] = 'required';
        }

        return $rules;
    }
}