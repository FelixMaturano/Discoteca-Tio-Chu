<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Staff extends Model
{
    use HasFactory;

    protected $table = 'staff';

    protected $fillable = [
        'name',
        'role',
        'default_pay',
        'works_friday',
        'works_saturday',
        'works_sunday',
        'friday_pay',
        'saturday_pay',
        'sunday_pay',
        'assigned_bar',
        'phone',
        'is_active',
    ];

    protected $casts = [
        'default_pay' => 'decimal:2',
        'friday_pay' => 'decimal:2',
        'saturday_pay' => 'decimal:2',
        'sunday_pay' => 'decimal:2',
        'works_friday' => 'boolean',
        'works_saturday' => 'boolean',
        'works_sunday' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function attendances(): HasMany
    {
        return $this->hasMany(StaffAttendance::class);
    }

    /**
     * Verifica si el trabajador está programado para un día específico
     */
    public function worksOnDay(string $dayName): bool
    {
        $day = mb_strtolower(trim($dayName));
        if (str_contains($day, 'viernes')) return (bool)$this->works_friday;
        if (str_contains($day, 'sabado') || str_contains($day, 'sábado')) return (bool)$this->works_saturday;
        if (str_contains($day, 'domingo')) return (bool)$this->works_sunday;
        return false; // Días extraordinarios (Jueves, etc.) se seleccionan manualmente
    }

    /**
     * Obtiene la tarifa correspondiente al día específico
     */
    public function getPayForDay(string $dayName): float
    {
        $day = mb_strtolower(trim($dayName));
        if (str_contains($day, 'viernes') && $this->friday_pay !== null) {
            return (float)$this->friday_pay;
        }
        if ((str_contains($day, 'sabado') || str_contains($day, 'sábado')) && $this->saturday_pay !== null) {
            return (float)$this->saturday_pay;
        }
        if (str_contains($day, 'domingo') && $this->sunday_pay !== null) {
            return (float)$this->sunday_pay;
        }
        return (float)$this->default_pay;
    }

    /**
     * Categoriza al trabajador en uno de los 4 grupos principales oficiales:
     * 1. Seguridad, 2. Barra, 3. Mozos, 4. Limpieza
     */
    public function getRoleCategory(): string
    {
        $role = mb_strtolower(trim($this->role ?? ''));
        $name = mb_strtolower(trim($this->name ?? ''));
        $bar = mb_strtolower(trim($this->assigned_bar ?? ''));

        if (str_contains($role, 'seguridad') || str_contains($name, '(s)') || str_contains($bar, 'seguridad')) {
            return 'seguridad';
        }

        if (str_contains($role, 'bartender') || str_contains($role, 'barra') || str_contains($bar, 'barra')) {
            return 'barra';
        }

        if (str_contains($role, 'mozo') || str_contains($role, 'meser') || str_contains($role, 'staff') || str_contains($bar, 'pista')) {
            return 'mozos';
        }

        if (str_contains($role, 'limpieza')) {
            return 'limpieza';
        }

        return 'mozos';
    }

    /**
     * Retorna el número de orden oficial para agrupar y ordenar al personal:
     * 1. Seguridad
     * 2. Barra
     * 3. Mozos
     * 4. Limpieza
     */
    public function getRoleCategoryRank(): int
    {
        return match($this->getRoleCategory()) {
            'seguridad' => 1,
            'barra' => 2,
            'mozos' => 3,
            'limpieza' => 4,
            default => 5,
        };
    }
}
