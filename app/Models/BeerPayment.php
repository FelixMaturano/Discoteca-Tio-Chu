<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BeerPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'night_session_id',
        'payment_method',
        'qr_provider',
        'amount',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function nightSession(): BelongsTo
    {
        return $this->belongsTo(NightSession::class);
    }
}
