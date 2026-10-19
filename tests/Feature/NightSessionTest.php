<?php

namespace Tests\Feature;

use App\Models\NightSession;
use App\Models\Product;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NightSessionTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Arrange global: Autenticar un usuario para los endpoints protegidos
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    public function test_tc_h2_01_abrir_sesion_con_fecha_valida(): void
    {
        // Arrange (usamos 0.05 en lugar de 5.0 para cumplir max:1)
        $payload = [
            'session_date' => '2026-03-15',
            'pos_commission_rate' => 0.05,
        ];

        // Act
        $response = $this->post(route('sessions.store'), $payload);

        // Assert
        $response->assertStatus(302);
        $response->assertRedirect(route('sessions.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseCount('night_sessions', 1);
        $this->assertDatabaseHas('night_sessions', [
            'session_date' => '2026-03-15',
            'day_name' => 'Domingo',
        ]);
    }

    public function test_tc_h2_02_abrir_sesion_en_fecha_limite_de_mes_y_anio_sin_desplazamiento(): void
    {
        // Arrange
        $payload = [
            'session_date' => '2026-12-31',
            'pos_commission_rate' => 0.05,
        ];

        // Act
        $response = $this->post(route('sessions.store'), $payload);

        // Assert
        $response->assertStatus(302);
        $response->assertRedirect(route('sessions.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseCount('night_sessions', 1);
        $this->assertDatabaseHas('night_sessions', [
            'session_date' => '2026-12-31',
            'day_name' => 'Jueves',
        ]);

        $session = NightSession::firstOrFail();
        $this->assertSame('2026-12-31', $session->session_date->format('Y-m-d'));
    }

    public function test_tc_h2_03_intentar_apertura_duplicada_para_misma_fecha(): void
    {
        // Arrange: Crear directamente el registro sin depender de Factory
        NightSession::create([
            'session_date' => '2026-03-15',
            'day_name' => 'Domingo',
            'pos_commission_rate' => 0.05,
            'status' => 'closed',
        ]);

        $payload = [
            'session_date' => '2026-03-15',
            'pos_commission_rate' => 0.05,
        ];

        // Act
        $response = $this->from(route('sessions.create'))
            ->post(route('sessions.store'), $payload);

        // Assert
        $response->assertStatus(302);
        $response->assertRedirect(route('sessions.create'));
        $response->assertSessionHasErrors(['session_date']);
        $this->assertDatabaseCount('night_sessions', 1);
    }

    public function test_tc_h2_04_enviar_fecha_vacia_o_invalida(): void
    {
        foreach (['', 'fecha-invalida'] as $invalidDate) {
            $response = $this->from(route('sessions.create'))
                ->post(route('sessions.store'), [
                    'session_date' => $invalidDate,
                    'pos_commission_rate' => 0.05,
                ]);

            $response->assertStatus(302);
            $response->assertRedirect(route('sessions.create'));
            $response->assertSessionHasErrors(['session_date']);
            $this->assertDatabaseCount('night_sessions', 0);
        }
    }

    public function test_open_session_creates_initial_closing_and_inventory(): void
    {
        // Arrange
        $product = Product::create([
            'name' => 'Producto Apertura',
            'category' => 'Otros',
            'sale_price' => 20,
            'cost_price' => 10,
            'unit' => 'Unidad',
            'units_per_package' => 6,
            'stock_warehouse' => 30,
            'stock_packages' => 5,
            'stock_units' => 0,
            'is_active' => true,
        ]);
        $staff = Staff::create([
            'name' => 'Mesero Viernes',
            'role' => 'MESERO',
            'default_pay' => 100,
            'friday_pay' => 125,
            'works_friday' => true,
            'works_saturday' => false,
            'works_sunday' => false,
            'is_active' => true,
        ]);

        // Act
        $response = $this->from(route('sessions.create'))->post(route('sessions.store'), [
            'session_date' => '2026-10-16',
            'pos_commission_rate' => 0.05,
        ]);

        // Assert
        $response->assertRedirect(route('sessions.index'));
        $response->assertSessionHas('success');
        $session = NightSession::whereDate('session_date', '2026-10-16')->firstOrFail();
        $this->assertSame('Viernes', $session->day_name);
        $this->assertSame('open', $session->status);
        $this->assertSame($session->id, session('active_night_session_id'));
        $this->assertDatabaseHas('cash_closings', ['night_session_id' => $session->id]);
        $this->assertDatabaseCount('bar_sales', 3);

        foreach (['Barra Principal', 'Barra Subterráneo', 'Tienda'] as $barName) {
            $this->assertDatabaseHas('bar_sales', [
                'night_session_id' => $session->id,
                'product_id' => $product->id,
                'bar_name' => $barName,
                'total_initial' => 0,
            ]);
        }

        $this->assertDatabaseHas('staff_attendances', [
            'night_session_id' => $session->id,
            'staff_id' => $staff->id,
            'pay_amount' => '125.00',
        ]);
    }

    public function test_open_session_is_rejected_when_another_session_is_open(): void
    {
        // Arrange
        NightSession::create([
            'session_date' => '2026-10-14',
            'day_name' => 'Miércoles',
            'pos_commission_rate' => 0.05,
            'status' => 'open',
        ]);

        // Act
        $response = $this->from(route('sessions.create'))->post(route('sessions.store'), [
            'session_date' => '2026-10-16',
            'pos_commission_rate' => 0.05,
        ]);

        // Assert
        $response->assertRedirect(route('sessions.create'));
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('night_sessions', 1);
        $this->assertDatabaseCount('cash_closings', 0);
        $this->assertDatabaseCount('bar_sales', 0);
    }

    public function test_session_create_form_loads_and_status_can_be_updated(): void
    {
        // Arrange
        $session = NightSession::create([
            'session_date' => '2026-10-14',
            'day_name' => 'Miércoles',
            'pos_commission_rate' => 0.013,
            'status' => 'closed',
        ]);

        // Act
        $createResponse = $this->get(route('sessions.create'));
        $updateResponse = $this->from(route('sessions.index'))
            ->patch(route('sessions.updateStatus', $session), [
                'status' => 'open',
                'pos_commission_rate' => 0.025,
            ]);

        // Assert
        $createResponse->assertOk();
        $createResponse->assertSee('session_date');
        $updateResponse->assertRedirect(route('sessions.index'));
        $updateResponse->assertSessionHas('success');
        $this->assertDatabaseHas('night_sessions', [
            'id' => $session->id,
            'status' => 'open',
            'pos_commission_rate' => '0.0250',
        ]);
    }

    public function test_session_status_rejects_invalid_values(): void
    {
        // Arrange
        $session = NightSession::create([
            'session_date' => '2026-10-14',
            'day_name' => 'Miércoles',
            'pos_commission_rate' => 0.013,
            'status' => 'closed',
        ]);

        // Act
        $response = $this->from(route('sessions.index'))
            ->patch(route('sessions.updateStatus', $session), ['status' => 'in_progress']);

        // Assert
        $response->assertRedirect(route('sessions.index'));
        $response->assertSessionHasErrors('status');
        $this->assertDatabaseHas('night_sessions', [
            'id' => $session->id,
            'status' => 'closed',
        ]);
    }

    public function test_sessions_index_renders_open_and_closed_session_summary(): void
    {
        // Arrange
        NightSession::create([
            'session_date' => '2026-10-14',
            'day_name' => 'Miércoles',
            'pos_commission_rate' => 0.013,
            'status' => 'open',
        ]);
        NightSession::create([
            'session_date' => '2026-10-15',
            'day_name' => 'Jueves',
            'pos_commission_rate' => 0.013,
            'status' => 'closed',
        ]);

        // Act
        $response = $this->get(route('sessions.index'));

        // Assert
        $response->assertOk();
        $response->assertSee('Historial de Noches de Atención');
        $response->assertSee('Noche en Curso');
        $response->assertSee('2');
    }
}
