<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasUlids;

    public $timestamps = false;

    protected $fillable = [
        'order_id', 'product_id', 'product_name_snapshot',
        'snapshot_cogs', 'snapshot_price', 'variant_label',
        'quantity', 'subtotal', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'snapshot_cogs' => 'decimal:2',
            'snapshot_price' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'quantity' => 'integer',
        ];
    }

    public function order(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
