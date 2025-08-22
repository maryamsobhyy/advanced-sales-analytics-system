<?php

namespace App\Http\Requests\Orders;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */

    public function rules(): array
    {
        return [
            'product_id' => [
                'required',
                'integer',
                'exists:products,id',
                function ($attribute, $value, $fail) {
                    $product = \App\Models\Product::find($value);
                    if ($product && !$product->is_active) {
                        $fail('The selected product is not available.');
                    }
                },
            ],
            'quantity' => [
                'required',
                'integer',
                'min:1',
                'max:100'
            ],
            'price' => [
                'sometimes',
                'numeric',
                'min:0'
            ],
            'date' => [
                'sometimes',
                'date',
                'before_or_equal:now'
            ],
            'city' => [
                'sometimes',
                'string',
                'max:100'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'product_id.required' => 'Product ID is required.',
            'product_id.exists' => 'The selected product does not exist.',
            'quantity.required' => 'Quantity is required.',
            'quantity.min' => 'Quantity must be at least 1.',
            'quantity.max' => 'Maximum quantity allowed is 100.',
            'price.numeric' => 'Price must be a valid number.',
            'price.min' => 'Price cannot be negative.',
            'date.before_or_equal' => 'Order date cannot be in the future.',
        ];
    }

    // protected function failedValidation(Validator $validator)
    // {
    //     throw new HttpResponseException(
    //         response()->json([
    //             'success' => false,
    //             'message' => 'Validation failed.',
    //             'errors' => $validator->errors(),
    //         ], 422)
    //     );
    // }

    protected function prepareForValidation()
    {
        // Set default date if not provided
        if (!$this->has('date')) {
            $this->merge([
                'date' => now()->toDateTimeString()
            ]);
        }
    }
}
