<?php

namespace Tests\Feature;

use App\Models\NightSession;
use App\Models\Staff;
use App\Models\StaffAttendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class H6AutomaticPayrollTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_tc_h6_01_al_abrir_noche_carga_personal_programado_con_jornal_del_dia(): void
    {
        $scheduledStaff = $this->createStaff([
            'name' => 'Personal viernes',
            'default_pay' => 100,
            'friday_pay' => 125,
            'works_friday' => true,
        ]);
        $staffNotScheduledForFriday = $this->createStaff([
            'name' => 'Personal solo sábado',
            'works_friday' => false,
            'works_saturday' => true,
        ]);
        $inactiveScheduledStaff = $this->createStaff([
            'name' => 'Personal inactivo',
            'works_friday' => true,
            'is_active' => false,
        ]);

        $response = $this->post(route('sessions.store'), [
            'session_date' => '2026-10-16',
            'pos_commission_rate' => 0.013,
        ]);

        $response->assertRedirect(route('sessions.index'));
        $session = NightSession::whereDate('session_date', '2026-10-16')->firstOrFail();

        $this->assertDatabaseCount('staff_attendances', 1);
        $this->assertDatabaseHas('staff_attendances', [
            'night_session_id' => $session->id,
            'staff_id' => $scheduledStaff->id,
            'pay_amount' => '125.00',
            'is_paid' => false,
        ]);
        $this->assertDatabaseMissing('staff_attendances', [
            'night_session_id' => $session->id,
            'staff_id' => $staffNotScheduledForFriday->id,
        ]);
        $this->assertDatabaseMissing('staff_attendances', [
            'night_session_id' => $session->id,
            'staff_id' => $inactiveScheduledStaff->id,
        ]);
    }

    public function test_tc_h6_02_dia_extraordinario_no_carga_personal_automaticamente_pero_permite_asignacion_manual(): void
    {
        $staff = $this->createStaff([
            'name' => 'Personal asignado extraordinario',
            'default_pay' => 90,
            'works_friday' => true,
            'works_saturday' => true,
            'works_sunday' => true,
        ]);

        $this->post(route('sessions.store'), [
            'session_date' => '2026-10-15',
            'pos_commission_rate' => 0.013,
        ])->assertRedirect(route('sessions.index'));

        $session = NightSession::whereDate('session_date', '2026-10-15')->firstOrFail();
        $this->assertSame('Jueves', $session->day_name);
        $this->assertSame(0, StaffAttendance::where('night_session_id', $session->id)->count());

        $response = $this->post(route('closing.attendances.store'), [
            'night_session_id' => $session->id,
            'staff_id' => $staff->id,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('staff_attendances', [
            'night_session_id' => $session->id,
            'staff_id' => $staff->id,
            'pay_amount' => '90.00',
            'is_paid' => false,
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createStaff(array $attributes): Staff
    {
        return Staff::create(array_merge([
            'role' => 'MESERO',
            'default_pay' => 100,
            'works_friday' => false,
            'works_saturday' => false,
            'works_sunday' => false,
            'is_active' => true,
        ], $attributes));
    }
}
