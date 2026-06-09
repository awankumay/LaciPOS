<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class StockLog extends Model
{
    use HasUlids;

    public $timestamps = false; // Hanya created_at, no updated_at
    protected $fillable = ['product_id', 'change', 'reason', 'reference_id', 'notes', 'created_at'];

    protected static function booted(): void
    {
        static::creating(function ($log) {
            $log->created_at = $log->created_at ?? now();
        });
    }

    protected function casts(): array
    {
        return ['change' => 'integer', 'created_at' => 'datetime'];
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
