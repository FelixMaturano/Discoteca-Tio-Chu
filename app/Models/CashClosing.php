<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashClosing extends Model
{
    use HasFactory;

    protected $fillable = [
        'night_session_id',
        'total_card',
        'total_card_commission',
        'total_card_net',
        'total_cash_invoices',
        'reported_cash',
        'total_qr_yasta',
        'total_qr_yape',
        'total_bar_sales',
        'total_guardarropa',
        'total_snacks',
        'total_staff_paid',
        'total_expenses',
        'net_cash_closing',
        'closed_at',
        'notes',
    ];

    protected $casts = [
        'total_card' => 'decimal:2',
        'total_card_commission' => 'decimal:2',
        'total_card_net' => 'decimal:2',
        'total_cash_invoices' => 'decimal:2',
        'reported_cash' => 'decimal:2',
        'total_qr_yasta' => 'decimal:2',
        'total_qr_yape' => 'decimal:2',
        'total_bar_sales' => 'decimal:2',
        'total_guardarropa' => 'decimal:2',
        'total_snacks' => 'decimal:2',
        'total_staff_paid' => 'decimal:2',
        'total_expenses' => 'decimal:2',
        'net_cash_closing' => 'decimal:2',
        'closed_at' => 'datetime',
    ];

    public function nightSession(): BelongsTo
    {
        return $this->belongsTo(NightSession::class);
    }
}
