<?php

namespace App\Modules\Payments\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'name', 'code', 'icon', 'color', 'description', 'type',
        'settings', 'is_active', 'sort_order',
        'fees_type', 'fees_value', 'min_order', 'max_order',
        'instructions',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
        'fees_value' => 'decimal:2',
        'min_order' => 'decimal:2',
        'max_order' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('id');
    }

    public function calculateFees(float $orderTotal): float
    {
        if ($this->fees_type === 'percent') {
            return round($orderTotal * ($this->fees_value / 100), 2);
        }

        return (float) $this->fees_value;
    }
}
