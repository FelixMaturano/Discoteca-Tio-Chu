<?php

namespace Tests\Feature;

use App\Models\NightSession;
use App\Models\QrPayment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QrPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_tc_h14_01_registra_cobro_yasta_con_monto_valido(): void
    {
        // Arrange
        $session = $this->createOpenSession();
        $payload = [
            'night_session_id' => $session->id,
            'point_of_sale' => 'Barra Principal',
            'operator_name' => 'Operador de prueba',
            'bank_app' => 'YASTA',
            'amount' => 125.50,
            'reference_code' => 'YASTA-REC-001',
        ];

        // Act
        $response = $this->from(route('qrs.index'))->post(route('qrs.store'), $payload);

        // Assert
        $response->assertStatus(302);
        $response->assertRedirect(route('qrs.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('qr_payments', [
            'night_session_id' => $session->id,
            'bank_app' => 'YASTA',
            'amount' => '125.50',
            'reference_code' => 'YASTA-REC-001',
        ]);
    }

    public function test_formulario_qr_incluye_el_identificador_del_comprobante(): void
    {
        $this->createOpenSession();

        $response = $this->get(route('qrs.index'));

        $response->assertOk();
        $response->assertSee('name="reference_code"', false);
        $response->assertSee('Identificador del comprobante');
    }

    public function test_tc_h14_02_registra_cobro_yape_con_monto_valido(): void
    {
        // Arrange
        $session = $this->createOpenSession();
        $payload = [
            'night_session_id' => $session->id,
            'point_of_sale' => 'Tienda',
            'operator_name' => 'Operador de prueba',
            'bank_app' => 'YAPE',
            'amount' => 82.00,
            'reference_code' => 'YAPE-REC-002',
        ];

        // Act
        $response = $this->from(route('qrs.index'))->post(route('qrs.store'), $payload);

        // Assert
        $response->assertStatus(302);
        $response->assertRedirect(route('qrs.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('qr_payments', [
            'night_session_id' => $session->id,
            'bank_app' => 'YAPE',
            'amount' => '82.00',
            'reference_code' => 'YAPE-REC-002',
        ]);
    }

    public function test_tc_h14_03_rechaza_montos_invalidos_y_medio_no_permitido(): void
    {
        // Arrange
        $session = $this->createOpenSession();
        $invalidPayloads = [
            [
                'night_session_id' => $session->id,
                'operator_name' => 'Operador de prueba',
                'bank_app' => 'YASTA',
                'amount' => 0,
                'reference_code' => 'QR-INVALID-001',
            ],
            [
                'night_session_id' => $session->id,
                'operator_name' => 'Operador de prueba',
                'bank_app' => 'YAPE',
                'amount' => -10,
                'reference_code' => 'QR-INVALID-002',
            ],
            [
                'night_session_id' => $session->id,
                'operator_name' => 'Operador de prueba',
                'bank_app' => 'BANCO_INVALIDO',
                'amount' => 10,
                'reference_code' => 'QR-INVALID-003',
            ],
        ];

        // Act y Assert
        foreach ($invalidPayloads as $payload) {
            $response = $this->from(route('qrs.index'))->post(route('qrs.store'), $payload);

            $response->assertStatus(302);
            $response->assertRedirect(route('qrs.index'));
            $response->assertSessionHasErrors(
                $payload['bank_app'] === 'BANCO_INVALIDO' ? 'bank_app' : 'amount'
            );
            $this->assertDatabaseCount('qr_payments', 0);
        }
    }

    public function test_tc_h14_04_rechaza_el_mismo_comprobante_y_no_duplica_el_cobro(): void
    {
        // Arrange
        $session = $this->createOpenSession();
        $payload = [
            'night_session_id' => $session->id,
            'point_of_sale' => 'Subte',
            'operator_name' => 'Operador de prueba',
            'bank_app' => 'YASTA',
            'amount' => 45.00,
            'reference_code' => 'YASTA-REC-IDEMP-001',
        ];

        // Act: enviar dos veces el mismo comprobante
        $firstResponse = $this->from(route('qrs.index'))->post(route('qrs.store'), $payload);
        $firstResponse->assertStatus(302);
        $firstResponse->assertSessionHas('success');

        $secondResponse = $this->from(route('qrs.index'))->post(route('qrs.store'), $payload);

        // Assert
        $secondResponse->assertStatus(302);
        $secondResponse->assertRedirect(route('qrs.index'));
        $secondResponse->assertSessionHasErrors('reference_code');
        $this->assertSame(
            1,
            QrPayment::where('reference_code', 'YASTA-REC-IDEMP-001')->count()
        );
        $this->assertDatabaseHas('qr_payments', [
            'night_session_id' => $session->id,
            'reference_code' => 'YASTA-REC-IDEMP-001',
        ]);
    }

    private function createOpenSession(): NightSession
    {
        return NightSession::create([
            'session_date' => '2026-10-14',
            'day_name' => 'Miércoles',
            'status' => 'open',
            'pos_commission_rate' => 0.013,
        ]);
    }
}
