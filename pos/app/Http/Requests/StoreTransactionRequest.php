<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array(auth()->user()?->role, ['owner', 'cashier']);
    }

    public function rules(): array
    {
        return [
            'customer_name' => 'nullable|string|max:255',
            'payment_method' => 'required|in:cash,qris,transfer,debit_card',
            'payment_amount' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'items.required' => 'Keranjang masih kosong.',
            'items.*.quantity.min' => 'Kuantitas minimal 1.',
        ];
    }
}
