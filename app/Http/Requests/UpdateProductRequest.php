<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'category_id' => ['required', 'ulid', 'exists:categories,id'],
            'discount_id' => ['nullable', 'ulid', 'exists:discounts,id'],
            'photo' => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:1024'],
            'remove_photo' => ['boolean'],
            'cogs' => ['required', 'numeric', 'min:0'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock' => ['required', 'integer', 'min:0'],
            'min_stock_alert' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $cogs = $this->input('cogs');
            $price = $this->input('price');

            if (is_numeric($cogs) && is_numeric($price) && (float) $price < (float) $cogs) {
                $validator->errors()->add('price', 'Harga jual tidak boleh kurang dari harga modal.');
            }
        });
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Nama produk wajib diisi.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'cogs.required' => 'Harga modal wajib diisi.',
            'price.required' => 'Harga jual wajib diisi.',
            'stock.required' => 'Stok wajib diisi.',
        ];
    }
}
