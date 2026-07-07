<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OnboardingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'store_name' => ['required', 'string', 'max:255'],
            'timezone' => ['required', 'string', 'timezone'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:20'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
            'receipt_footer' => ['nullable', 'string', 'max:255'],
            'enable_customer_name' => ['boolean'],
            'enable_table_number' => ['boolean'],
            'enable_order_notes' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'store_name.required' => 'Nama toko wajib diisi.',
            'logo.image' => 'File logo harus berupa gambar.',
            'logo.mimes' => 'Format logo harus PNG atau JPG.',
            'logo.max' => 'Ukuran logo maksimal 2MB.',
            'logo.uploaded' => 'Gagal mengunggah logo. Periksa ukuran file atau coba lagi.',
        ];
    }
}
