<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StoreSale extends Model
{
    use HasFactory;

    protected $fillable = [
        'night_session_id',
        'product_id',
        'quantity',
        'unit_price',
        'total_price',
        'cash_amount',
        'qr_amount',
        'cobrante_name',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'cash_amount' => 'decimal:2',
        'qr_amount' => 'decimal:2',
    ];

    public function nightSession(): BelongsTo
    {
        return $this->belongsTo(NightSession::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
