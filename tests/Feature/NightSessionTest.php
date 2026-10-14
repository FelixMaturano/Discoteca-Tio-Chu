<?php

namespace Tests\Feature;

use App\Models\NightSession;
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

    public function test_tc_h2_01_abrir_sesion_con_fecha_valida()
    {
        // Arrange (usamos 0.05 en lugar de 5.0 para cumplir max:1)
        $payload = [
            'session_date' => '2026-03-15',
            'pos_commission_rate' => 0.05,
        ];

        // Act
        $response = $this->postJson(route('sessions.store'), $payload);

        // Assert
        $response->assertRedirect(route('sessions.index'));
        $this->assertDatabaseHas('night_sessions', [
            'session_date' => '2026-03-15',
            'day_name' => 'Domingo',
        ]);
    }

    public function test_tc_h2_02_abrir_sesion_en_fecha_limite_de_anio()
    {
        // Arrange
        $payload = [
            'session_date' => '2026-12-31',
            'pos_commission_rate' => 0.05,
        ];

        // Act
        $response = $this->postJson(route('sessions.store'), $payload);

        // Assert
        $response->assertRedirect(route('sessions.index'));
        $this->assertDatabaseHas('night_sessions', [
            'session_date' => '2026-12-31',
            'day_name' => 'Jueves',
        ]);
    }

    public function test_tc_h2_03_intentar_apertura_duplicada_para_misma_fecha()
    {
        // Arrange: Crear directamente el registro sin depender de Factory
        NightSession::create([
            'session_date' => '2026-03-15',
            'day_name' => 'Domingo',
            'pos_commission_rate' => 0.05,
            'is_open' => false,
            'status' => 'closed',
        ]);

        $payload = [
            'session_date' => '2026-03-15',
            'pos_commission_rate' => 0.05,
        ];

        // Act
        $response = $this->postJson(route('sessions.store'), $payload);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['session_date']);
    }

    public function test_tc_h2_04_enviar_fecha_vacia_o_invalida()
    {
        // Arrange
        $payload = [
            'session_date' => 'fecha-invalida',
            'pos_commission_rate' => 0.05,
        ];

        // Act
        $response = $this->postJson(route('sessions.store'), $payload);

        // Assert
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['session_date']);
        $this->assertDatabaseCount('night_sessions', 0);
    }
}