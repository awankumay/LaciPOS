<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'payment_method' => ['required', 'in:cash,bank_transfer,e_wallet'],
            'payment_provider' => ['required_if:payment_method,bank_transfer,e_wallet', 'nullable', 'string'],
            'cash_received' => ['required_if:payment_method,cash', 'nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.productId' => ['required', 'string'],
            'items.*.productName' => ['required', 'string'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'items.*.cogs' => ['required', 'numeric', 'min:0'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.variantLabel' => ['nullable', 'string'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
            'discount_type' => ['nullable', 'string', 'in:percentage,nominal'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'discount_note' => ['nullable', 'string', 'max:255'],
            'items.*.discount_type' => ['nullable', 'string', 'in:percentage,nominal'],
            'items.*.discount_value' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
