<?php

namespace App\Models\Documents;

use App\Models\Order\Order;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Invoice extends Model
{
    protected $fillable = [
        'order_id', 'invoice_number', 'status',
        'invoice_template_id', 'metadata', 'issued_at', 'cancelled_at',
    ];

    protected $casts = [
        'metadata'     => 'array',
        'issued_at'    => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Invoice $invoice) {
            if (empty($invoice->invoice_number)) {
                $invoice->invoice_number = self::generateNumber();
            }
            if (empty($invoice->issued_at) && $invoice->status === 'issued') {
                $invoice->issued_at = now();
            }
        });
    }

    public static function generateNumber(): string
    {
        $year = now()->year;
        $last = static::whereYear('created_at', $year)
            ->orderByDesc('id')
            ->lockForUpdate()
            ->first();

        $next = 1;
        if ($last && preg_match('/INV-\d{4}-(\d+)/', $last->invoice_number, $m)) {
            $next = ((int) $m[1]) + 1;
        }

        return sprintf('INV-%d-%06d', $year, $next);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(InvoiceTemplate::class, 'invoice_template_id');
    }

    public function cancel(): void
    {
        $this->update([
            'status'       => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }

    public function isIssued(): bool
    {
        return $this->status === 'issued';
    }
}
