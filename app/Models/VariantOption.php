<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class VariantOption extends Model
{
    use HasUlids;

    protected $fillable = ['variant_id', 'label', 'price_modifier', 'cogs_modifier'];

    protected function casts(): array
    {
        return [
            'price_modifier' => 'decimal:2',
            'cogs_modifier' => 'decimal:2',
        ];
    }

    public function variant(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }
}
