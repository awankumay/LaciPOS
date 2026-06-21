<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasUlids;

    protected $fillable = [
        'order_number', 'user_id', 'status', 'payment_method',
        'payment_provider', 'subtotal', 'tax_rate', 'tax_type', 'tax_amount',
        'service_charge_rate', 'service_charge_type', 'service_charge_amount',
        'discount_type', 'discount_value', 'discount_amount', 'discount_note',
        'total_amount', 'cash_received', 'change_amount', 'notes',
        'customer_name', 'table_number',
        'payment_method_id', 'payment_method_name', 'payment_account_details',
        'payment_admin_fee_rate', 'payment_admin_fee_amount',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'service_charge_rate' => 'decimal:2',
            'service_charge_amount' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'cash_received' => 'decimal:2',
            'change_amount' => 'decimal:2',
            'payment_admin_fee_rate' => 'decimal:2',
            'payment_admin_fee_amount' => 'decimal:2',
        ];
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Generate order number: TRX-YYYYMMDD-NNN
     */
    public static function generateOrderNumber(): string
    {
        $today = now()->format('Ymd');
        $prefix = "TRX-{$today}-";

        $lastOrder = static::where('order_number', 'like', "{$prefix}%")
            ->orderByDesc('order_number')
            ->first();

        if ($lastOrder) {
            $lastNumber = (int) Str::afterLast($lastOrder->order_number, '-');
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        return $prefix . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
