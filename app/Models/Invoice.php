<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'night_session_id',
        'correlative_num',
        'payment_method',
        'bar_name',
        'amount',
        'commission_rate',
        'commission_amount',
        'net_amount',
        'notes',
    ];

    protected $casts = [
        'correlative_num' => 'integer',
        'amount' => 'decimal:2',
        'commission_rate' => 'decimal:4',
        'commission_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
    ];

    public function nightSession(): BelongsTo
    {
        return $this->belongsTo(NightSession::class);
    }

    /**
     * Calcula comisión y neto según método de pago
     */
    public function calculateNet(): void
    {
        if ($this->payment_method === 'tarjeta') {
            $this->commission_amount = round($this->amount * (float)$this->commission_rate, 2);
            $this->net_amount = round($this->amount - $this->commission_amount, 2);
        } else {
            $this->commission_amount = 0;
            $this->net_amount = $this->amount;
        }
    }
}
