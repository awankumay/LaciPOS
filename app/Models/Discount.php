<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;

class Discount extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'name', 'discount_type', 'discount_value',
        'start_date', 'end_date', 'start_time', 'end_time',
        'quota', 'is_active',
    ];

    protected $appends = ['is_discount_active'];

    protected function casts(): array
    {
        return [
            'discount_value' => 'decimal:2',
            'start_date' => 'date:Y-m-d',
            'end_date' => 'date:Y-m-d',
            'quota' => 'integer',
            'quota_used' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function products(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function getIsDiscountActiveAttribute(): bool
    {
        if (!$this->is_active || !$this->discount_type || !$this->discount_value) {
            return false;
        }

        if ($this->quota !== null && $this->quota_used >= $this->quota) {
            return false;
        }

        $now = now();

        if ($this->start_date && $now->copy()->startOfDay()->lt($this->start_date)) {
            return false;
        }
        if ($this->end_date && $now->copy()->startOfDay()->gt($this->end_date)) {
            return false;
        }

        if ($this->start_time || $this->end_time) {
            $currentTime = $now->format('H:i:s');
            if ($this->start_time && $currentTime < $this->start_time) {
                return false;
            }
            if ($this->end_time && $currentTime > $this->end_time) {
                return false;
            }
        }

        return true;
    }
}
