<?php

namespace App\Models;

use App\Support\Catalog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'number', 'order_id', 'client_name', 'client_email', 'client_phone', 'client_address', 'issue_date', 'due_date',
        'items', 'subtotal', 'discount', 'tax_percent', 'tax_amount', 'total', 'paid_amount', 'status', 'notes', 'created_by',
    ];

    protected $appends = ['status_label', 'balance'];

    protected function casts(): array
    {
        return [
            'items' => 'array',
            'issue_date' => 'date:Y-m-d',
            'due_date' => 'date:Y-m-d',
            'subtotal' => 'integer',
            'discount' => 'integer',
            'tax_percent' => 'float',
            'tax_amount' => 'integer',
            'total' => 'integer',
            'paid_amount' => 'integer',
        ];
    }

    public static function generateNumber(): string
    {
        $prefix = Setting::get('invoice_prefix', 'INV');
        $period = now()->format('Ym');
        $count = self::withTrashed()->where('number', 'like', "{$prefix}/{$period}/%")->count() + 1;
        do {
            $number = sprintf('%s/%s/%04d', $prefix, $period, $count++);
        } while (self::withTrashed()->where('number', $number)->exists());

        return $number;
    }

    /**
     * Hitung ulang subtotal, pajak & total dari item.
     */
    public function recalculate(): void
    {
        $subtotal = 0;
        foreach ((array) $this->items as $item) {
            $subtotal += (int) round(((float) ($item['qty'] ?? 0)) * ((int) ($item['price'] ?? 0)));
        }
        $this->subtotal = $subtotal;
        $base = max(0, $subtotal - (int) $this->discount);
        $this->tax_amount = (int) round($base * ((float) $this->tax_percent) / 100);
        $this->total = $base + $this->tax_amount;
    }

    public function syncPaymentStatus(): void
    {
        $this->paid_amount = (int) $this->payments()->sum('amount');
        if ($this->status !== 'cancelled' && $this->status !== 'draft') {
            $this->status = $this->paid_amount <= 0 ? 'unpaid' : ($this->paid_amount >= $this->total ? 'paid' : 'partial');
        }
        $this->save();
    }

    public function getStatusLabelAttribute(): string
    {
        return Catalog::INVOICE_STATUSES[$this->status ?? '']['label'] ?? (string) $this->status;
    }

    public function getBalanceAttribute(): int
    {
        return max(0, (int) $this->total - (int) $this->paid_amount);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class)->orderByDesc('paid_at');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
