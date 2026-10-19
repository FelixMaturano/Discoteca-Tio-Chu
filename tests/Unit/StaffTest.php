<?php

namespace Tests\Unit;

use App\Models\Staff;
use PHPUnit\Framework\TestCase;

class StaffTest extends TestCase
{
    public function test_canonical_roles_map_to_expected_staff_categories(): void
    {
        $roleVariants = [
            'MESERO' => ['MESERO', 'Staff / Mozo', 'Mesera'],
            'BARTENDER' => ['BARTENDER', 'Bartender Principal', 'Bartender Subterráneo'],
            'SEGURIDAD' => ['SEGURIDAD', 'Seguridad'],
            'LIMPIEZA' => ['LIMPIEZA', 'Limpieza'],
        ];

        foreach ($roleVariants as $canonicalRole => $variants) {
            foreach ($variants as $role) {
                $staff = new Staff(['role' => $role]);

                $this->assertSame($canonicalRole, $staff->getCanonicalRole());
            }
        }
    }

    public function test_weekly_availability_uses_each_schedule_flag(): void
    {
        $staff = new Staff([
            'works_friday' => true,
            'works_saturday' => false,
            'works_sunday' => true,
        ]);

        $this->assertTrue($staff->worksOnDay('Viernes'));
        $this->assertFalse($staff->worksOnDay('Sábado'));
        $this->assertTrue($staff->worksOnDay('Domingo'));
        $this->assertFalse($staff->worksOnDay('Jueves'));
    }

    public function test_daily_pay_uses_specific_rate_or_falls_back_to_default(): void
    {
        $staff = new Staff([
            'default_pay' => 100,
            'friday_pay' => 125,
            'saturday_pay' => null,
            'sunday_pay' => 150,
        ]);

        $this->assertSame(125.0, $staff->getPayForDay('Viernes'));
        $this->assertSame(100.0, $staff->getPayForDay('Sábado'));
        $this->assertSame(150.0, $staff->getPayForDay('Domingo'));
        $this->assertSame(100.0, $staff->getPayForDay('Jueves'));
    }

    public function test_role_category_rank_matches_payroll_order(): void
    {
        $expectedRanks = [
            'SEGURIDAD' => 1,
            'BARTENDER' => 2,
            'MESERO' => 3,
            'LIMPIEZA' => 4,
        ];

        foreach ($expectedRanks as $role => $expectedRank) {
            $staff = new Staff(['role' => $role]);

            $this->assertSame($expectedRank, $staff->getRoleCategoryRank());
        }
    }
}
