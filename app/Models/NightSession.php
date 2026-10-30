<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class NightSession extends Model
{
    use HasFactory;

    public static function weekdayNameForDate(string $date): string
    {
        $weekdays = [
            0 => 'Domingo',
            1 => 'Lunes',
            2 => 'Martes',
            3 => 'Miércoles',
            4 => 'Jueves',
            5 => 'Viernes',
            6 => 'Sábado',
        ];

        return $weekdays[Carbon::parse($date)->dayOfWeek];
    }

    protected $fillable = [
        'session_date',
        'day_name',
        'status',
        'pos_commission_rate',
        'opening_cash',
        'notes',
    ];

    protected $casts = [
        'pos_commission_rate' => 'decimal:4',
        'opening_cash' => 'decimal:2',
    ];

    protected function sessionDate(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value ? Carbon::parse($value) : null,
            set: fn ($value) => $value ? Carbon::parse($value)->format('Y-m-d') : null,
        );
    }

    public function staffAttendances(): HasMany
    {
        return $this->hasMany(StaffAttendance::class);
    }

    public function barSales(): HasMany
    {
        return $this->hasMany(BarSale::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function qrPayments(): HasMany
    {
        return $this->hasMany(QrPayment::class);
    }

    public function storeSales(): HasMany
    {
        return $this->hasMany(StoreSale::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function beerPayments(): HasMany
    {
        return $this->hasMany(BeerPayment::class);
    }

    public function cashClosing(): HasOne
    {
        return $this->hasOne(CashClosing::class);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }
}
