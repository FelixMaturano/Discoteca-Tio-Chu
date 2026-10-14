<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'night_session_id',
        'category',
        'description',
        'amount',
        'receipt_no',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function nightSession(): BelongsTo
    {
        return $this->belongsTo(NightSession::class);
    }
}
