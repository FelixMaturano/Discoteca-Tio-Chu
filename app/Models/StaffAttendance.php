<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $night_session_id
 * @property int $staff_id
 * @property string $pay_amount
 * @property bool $is_paid
 * @property float $debt_amount
 * @property-read NightSession|null $nightSession
 * @property-read Staff|null $staff
 */
class StaffAttendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'night_session_id',
        'staff_id',
        'pay_amount',
        'is_paid',
        'notes',
    ];

    protected $casts = [
        'pay_amount' => 'decimal:2',
        'is_paid' => 'boolean',
    ];

    /** @return BelongsTo<NightSession, $this> */
    public function nightSession(): BelongsTo
    {
        return $this->belongsTo(NightSession::class);
    }

    /** @return BelongsTo<Staff, $this> */
    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }
}
