<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'ulid', 'exists:categories,id'],
            'photo' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:1024'],
            'cogs' => ['required', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'min_stock_alert' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama produk wajib diisi.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori tidak valid.',
            'photo.image' => 'File harus berupa gambar.',
            'photo.max' => 'Ukuran foto maksimal 1MB.',
            'cogs.required' => 'Harga modal wajib diisi.',
            'cogs.min' => 'Harga modal tidak boleh negatif.',
            'price.required' => 'Harga jual wajib diisi.',
            'price.min' => 'Harga jual tidak boleh negatif.',
            'stock.required' => 'Stok awal wajib diisi.',
            'stock.min' => 'Stok tidak boleh negatif.',
        ];
    }
}
