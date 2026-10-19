<?php

namespace Tests\Unit;

use App\Models\NightSession;
use Carbon\Carbon;
use Tests\TestCase;

class NightSessionTest extends TestCase
{
    public function test_weekday_mapping_covers_all_days(): void
    {
        $datesByWeekday = [
            '2026-10-11' => 'Domingo',
            '2026-10-12' => 'Lunes',
            '2026-10-13' => 'Martes',
            '2026-10-14' => 'Miércoles',
            '2026-10-15' => 'Jueves',
            '2026-10-16' => 'Viernes',
            '2026-10-17' => 'Sábado',
        ];

        foreach ($datesByWeekday as $date => $expectedWeekday) {
            $this->assertSame($expectedWeekday, NightSession::weekdayNameForDate($date));
        }
    }

    public function test_session_date_keeps_its_local_calendar_day(): void
    {
        config(['app.timezone' => 'America/La_Paz']);

        $session = new NightSession;
        $session->session_date = Carbon::create(2026, 12, 31, 23, 30, 0, 'America/La_Paz');

        $this->assertSame('2026-12-31', $session->getAttributes()['session_date']);
    }
}
