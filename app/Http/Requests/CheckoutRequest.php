<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'address_id' => 'nullable|exists:user_addresses,id',
            'shipping' => 'required_without:address_id|array',
            'shipping.full_name' => 'required_without:address_id|string|max:255',
            'shipping.phone' => 'required_without:address_id|string|max:25',
            'shipping.email' => 'nullable|email|max:255',
            'shipping.address' => 'required_without:address_id|string|max:1000',
            'shipping.city' => 'required_without:address_id|string|max:255',
            'shipping.postal_code' => 'required_without:address_id|string|max:20',
            'shipping.save_address' => 'nullable|boolean',
            'shipping.is_default' => 'nullable|boolean',
            'promo_code' => 'nullable|string|exists:promos,promo_code',
            'notes' => 'nullable|string|max:1000',
            'shipping_method' => 'required|in:regular,express',
            'payment_method' => 'required|in:bank_transfer,qris,e_wallet,midtrans',
            'payment_proof' => 'required_unless:payment_method,midtrans|image|mimes:jpg,jpeg,png,webp|max:5120',
        ];
    }
}
