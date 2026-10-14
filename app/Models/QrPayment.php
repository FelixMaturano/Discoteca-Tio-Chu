<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QrPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'night_session_id',
        'point_of_sale',
        'operator_name',
        'bank_app',
        'amount',
        'reference_code',
        'is_confirmed',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_confirmed' => 'boolean',
    ];

    public function nightSession(): BelongsTo
    {
        return $this->belongsTo(NightSession::class);
    }
}
