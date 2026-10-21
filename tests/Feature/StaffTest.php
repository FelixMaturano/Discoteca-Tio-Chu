<?php

namespace Tests\Feature;

use App\Models\NightSession;
use App\Models\Staff;
use App\Models\StaffAttendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_admin_can_create_staff_with_canonical_role_and_default_pay(): void
    {
        // Arrange
        $payload = [
            'name' => 'María Prueba',
            'phone' => '70012345',
            'role' => 'MESERO',
            'default_pay' => 125.50,
            'assigned_bar' => 'Meseros',
            'works_friday' => 1,
            'works_saturday' => 1,
            'works_sunday' => 0,
        ];

        // Act
        $response = $this->post(route('staff.store'), $payload);

        // Assert
        $response->assertRedirect(route('staff.index', ['day' => 'todos']));
        $this->assertDatabaseHas('staff', [
            'name' => 'María Prueba',
            'phone' => '70012345',
            'role' => 'MESERO',
            'default_pay' => '125.50',
            'works_friday' => 1,
            'works_saturday' => 1,
            'works_sunday' => 0,
        ]);
    }

    public function test_admin_can_create_each_canonical_staff_role(): void
    {
        // Arrange
        $roles = ['MESERO', 'BARTENDER', 'SEGURIDAD', 'LIMPIEZA'];

        // Act y Assert
        foreach ($roles as $index => $role) {
            $response = $this->post(route('staff.store'), [
                'name' => 'Trabajador '.$index,
                'role' => $role,
                'default_pay' => 100,
            ]);

            $response->assertRedirect(route('staff.index', ['day' => 'todos']));
            $this->assertDatabaseHas('staff', [
                'name' => 'Trabajador '.$index,
                'role' => $role,
                'default_pay' => '100.00',
            ]);
        }

        $this->assertDatabaseCount('staff', count($roles));
    }

    public function test_staff_creation_rejects_invalid_role_and_pay(): void
    {
        // Arrange
        $invalidPayloads = [
            [
                'name' => 'Rol Inválido',
                'role' => 'DJ / Sonido',
                'default_pay' => 100,
                'error' => 'role',
            ],
            [
                'name' => 'Jornal Inválido',
                'role' => 'BARTENDER',
                'default_pay' => -1,
                'error' => 'default_pay',
            ],
            [
                'name' => 'Jornal Ausente',
                'role' => 'LIMPIEZA',
                'error' => 'default_pay',
            ],
        ];

        // Act y Assert
        foreach ($invalidPayloads as $payload) {
            $error = $payload['error'];
            unset($payload['error']);

            $response = $this->from(route('staff.create'))->post(route('staff.store'), $payload);

            $response->assertRedirect(route('staff.create'));
            $response->assertSessionHasErrors($error);
            $this->assertDatabaseCount('staff', 0);
        }
    }

    public function test_admin_can_update_staff_details_and_status(): void
    {
        // Arrange
        $staff = Staff::create([
            'name' => 'Nombre Inicial',
            'phone' => '70000000',
            'role' => 'MESERO',
            'default_pay' => 100,
            'works_friday' => true,
            'works_saturday' => true,
            'works_sunday' => true,
            'is_active' => true,
        ]);

        // Act
        $response = $this->put(route('staff.update', $staff), [
            'name' => 'Nombre Actualizado',
            'phone' => '71112223',
            'role' => 'BARTENDER',
            'default_pay' => 140,
            'friday_pay' => 145,
            'is_active' => 0,
            'works_friday' => 1,
            'works_saturday' => 0,
            'works_sunday' => 0,
        ]);

        // Assert
        $response->assertRedirect(route('staff.index'));
        $this->assertDatabaseHas('staff', [
            'id' => $staff->id,
            'name' => 'Nombre Actualizado',
            'phone' => '71112223',
            'role' => 'BARTENDER',
            'default_pay' => '140.00',
            'friday_pay' => '145.00',
            'is_active' => 0,
            'works_friday' => 1,
            'works_saturday' => 0,
            'works_sunday' => 0,
        ]);
    }

    public function test_admin_can_delete_staff(): void
    {
        // Arrange
        $staff = Staff::create([
            'name' => 'Personal a Eliminar',
            'role' => 'LIMPIEZA',
            'default_pay' => 100,
        ]);

        // Act
        $response = $this->delete(route('staff.destroy', $staff));

        // Assert
        $response->assertRedirect(route('staff.index', ['day' => 'todos']));
        $this->assertDatabaseMissing('staff', ['id' => $staff->id]);
    }

    public function test_deleting_staff_only_removes_the_selected_record(): void
    {
        // Arrange
        $selectedStaff = Staff::create([
            'name' => 'Personal Seleccionado',
            'role' => 'MESERO',
            'default_pay' => 100,
        ]);
        $otherStaff = Staff::create([
            'name' => 'Personal Conservado',
            'role' => 'BARTENDER',
            'default_pay' => 110,
        ]);

        // Act
        $response = $this->delete(route('staff.destroy', $selectedStaff));

        // Assert
        $response->assertRedirect(route('staff.index', ['day' => 'todos']));
        $this->assertDatabaseMissing('staff', ['id' => $selectedStaff->id]);
        $this->assertDatabaseHas('staff', ['id' => $otherStaff->id]);
    }

    public function test_staff_create_and_edit_forms_render_canonical_roles(): void
    {
        // Arrange
        $staff = Staff::create([
            'name' => 'Bartender de Prueba',
            'role' => 'BARTENDER',
            'default_pay' => 120,
        ]);

        // Act
        $createResponse = $this->get(route('staff.create'));
        $editResponse = $this->get(route('staff.edit', $staff));

        // Assert
        $createResponse->assertOk();
        $createResponse->assertSee('value="MESERO"', false);
        $createResponse->assertSee('value="BARTENDER"', false);
        $createResponse->assertSee('value="SEGURIDAD"', false);
        $createResponse->assertSee('value="LIMPIEZA"', false);
        $editResponse->assertOk();
        $editResponse->assertSee('value="BARTENDER" selected', false);
    }

    public function test_staff_attendance_ajax_syncs_selection_and_preserves_paid_attendance(): void
    {
        // Arrange
        $session = NightSession::create([
            'session_date' => '2026-10-16',
            'day_name' => 'Viernes',
            'status' => 'open',
            'pos_commission_rate' => 0.013,
        ]);
        $selectedStaff = Staff::create([
            'name' => 'Mesero Seleccionado',
            'role' => 'MESERO',
            'default_pay' => 100,
            'friday_pay' => 125,
            'works_friday' => true,
        ]);
        $unselectedStaff = Staff::create([
            'name' => 'Mesero Desmarcado',
            'role' => 'MESERO',
            'default_pay' => 100,
        ]);
        $paidStaff = Staff::create([
            'name' => 'Bartender Pagado',
            'role' => 'BARTENDER',
            'default_pay' => 110,
        ]);
        StaffAttendance::create([
            'night_session_id' => $session->id,
            'staff_id' => $unselectedStaff->id,
            'pay_amount' => 100,
            'is_paid' => false,
        ]);
        StaffAttendance::create([
            'night_session_id' => $session->id,
            'staff_id' => $paidStaff->id,
            'pay_amount' => 110,
            'is_paid' => true,
        ]);

        // Act
        $response = $this->postJson(route('staff.syncAttendance'), [
            'session_id' => $session->id,
            'staff_ids' => [$selectedStaff->id, $paidStaff->id],
        ]);

        // Assert
        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('count', 2);
        $this->assertDatabaseHas('staff_attendances', [
            'night_session_id' => $session->id,
            'staff_id' => $selectedStaff->id,
            'pay_amount' => '125.00',
            'is_paid' => 0,
        ]);
        $this->assertDatabaseMissing('staff_attendances', [
            'night_session_id' => $session->id,
            'staff_id' => $unselectedStaff->id,
        ]);
        $this->assertDatabaseHas('staff_attendances', [
            'night_session_id' => $session->id,
            'staff_id' => $paidStaff->id,
            'is_paid' => 1,
        ]);
    }

    public function test_staff_attendance_ajax_rejects_closed_sessions_and_unknown_staff(): void
    {
        // Arrange
        $closedSession = NightSession::create([
            'session_date' => '2026-10-14',
            'day_name' => 'Miércoles',
            'status' => 'closed',
            'pos_commission_rate' => 0.013,
        ]);
        $openSession = NightSession::create([
            'session_date' => '2026-10-16',
            'day_name' => 'Viernes',
            'status' => 'open',
            'pos_commission_rate' => 0.013,
        ]);

        // Act y Assert: sesión cerrada
        $closedResponse = $this->postJson(route('staff.syncAttendance'), [
            'session_id' => $closedSession->id,
            'staff_ids' => [],
        ]);
        $closedResponse->assertStatus(422)->assertJsonPath('success', false);

        // Act y Assert: id de personal inexistente
        $invalidStaffResponse = $this->postJson(route('staff.syncAttendance'), [
            'session_id' => $openSession->id,
            'staff_ids' => [999999],
        ]);
        $invalidStaffResponse->assertUnprocessable();
        $invalidStaffResponse->assertJsonValidationErrors('staff_ids.0');
        $this->assertDatabaseCount('staff_attendances', 0);
    }
}
