<?php

namespace Tests\Feature;

use App\Models\CashClosing;
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
        $this->assertDatabaseCount('qr_payments', 1);
        $this->assertDatabaseHas('qr_payments', [
            'night_session_id' => $session->id,
            'point_of_sale' => 'Barra Principal',
            'operator_name' => 'Operador de prueba',
            'bank_app' => 'YASTA',
            'amount' => '125.50',
            'reference_code' => 'YASTA-REC-001',
            'is_confirmed' => true,
        ]);
    }

    public function test_formulario_qr_incluye_el_identificador_del_comprobante(): void
    {
        $this->createOpenSession();

        $response = $this->get(route('qrs.index'));

        $response->assertOk();
        $response->assertSee('name="reference_code"', false);
        $response->assertSee('Identificador del comprobante');
        $response->assertSee('name="bank_app" value="YASTA"', false);
        $response->assertSee('name="bank_app" value="YAPE"', false);
        $response->assertSee('images/LogosQR/yasta.png');
        $response->assertSee('images/LogosQR/yape.png');
        $response->assertSee('name="amount"', false);
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
        $this->assertDatabaseCount('qr_payments', 1);
        $this->assertDatabaseHas('qr_payments', [
            'night_session_id' => $session->id,
            'point_of_sale' => 'Tienda',
            'operator_name' => 'Operador de prueba',
            'bank_app' => 'YAPE',
            'amount' => '82.00',
            'reference_code' => 'YAPE-REC-002',
            'is_confirmed' => true,
        ]);
    }

    public function test_tc_h14_03_rechaza_montos_invalidos_con_mensaje_controlado(): void
    {
        // Arrange
        $session = $this->createOpenSession();
        $invalidAmounts = [
            [0, 'El monto cobrado debe ser mayor a 0.', 'QR-INVALID-001'],
            [-10, 'El monto cobrado debe ser mayor a 0.', 'QR-INVALID-002'],
            ['no-es-un-monto', 'El monto cobrado debe ser un valor numérico.', 'QR-INVALID-003'],
        ];

        foreach ($invalidAmounts as [$amount, $expectedMessage, $referenceCode]) {
            $response = $this->from(route('qrs.index'))->post(route('qrs.store'), [
                'night_session_id' => $session->id,
                'point_of_sale' => 'Barra Principal',
                'operator_name' => 'Operador de prueba',
                'bank_app' => 'YASTA',
                'amount' => $amount,
                'reference_code' => $referenceCode,
            ]);

            $response->assertStatus(302);
            $response->assertRedirect(route('qrs.index'));
            $response->assertSessionHasErrors('amount');
            $response->assertSessionHas('errors', function ($errors) use ($expectedMessage) {
                return $errors->getBag('default')->first('amount') === $expectedMessage;
            });
            $this->assertDatabaseCount('qr_payments', 0);
        }
    }

    public function test_rechaza_una_aplicacion_bancaria_no_permitida(): void
    {
        $session = $this->createOpenSession();

        $response = $this->from(route('qrs.index'))->post(route('qrs.store'), [
            'night_session_id' => $session->id,
            'point_of_sale' => 'Barra Principal',
            'operator_name' => 'Operador de prueba',
            'bank_app' => 'BANCO_INVALIDO',
            'amount' => 10,
            'reference_code' => 'QR-INVALID-BANK-001',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('qrs.index'));
        $response->assertSessionHasErrors('bank_app');
        $this->assertDatabaseCount('qr_payments', 0);
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
        $this->assertDatabaseCount('qr_payments', 1);
        $this->assertSame(
            45.0,
            (float) CashClosing::where('night_session_id', $session->id)->value('total_qr_yasta')
        );

        $secondResponse = $this->from(route('qrs.index'))->post(route('qrs.store'), $payload);

        // Assert
        $secondResponse->assertStatus(302);
        $secondResponse->assertRedirect(route('qrs.index'));
        $secondResponse->assertSessionHasErrors('reference_code');
        $this->assertSame(1, QrPayment::where('reference_code', 'YASTA-REC-IDEMP-001')->count());
        $this->assertDatabaseCount('qr_payments', 1);
        $this->assertDatabaseHas('qr_payments', [
            'night_session_id' => $session->id,
            'reference_code' => 'YASTA-REC-IDEMP-001',
        ]);
        $this->assertSame(
            45.0,
            (float) CashClosing::where('night_session_id', $session->id)->value('total_qr_yasta')
        );
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
