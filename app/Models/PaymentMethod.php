<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'name',
        'category',
        'account_details',
        'admin_fee_type',
        'admin_fee',
        'min_amount_for_fee',
        'is_active'
    ];

    protected function casts(): array
    {
        return [
            'admin_fee' => 'decimal:2',
            'min_amount_for_fee' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }
}
