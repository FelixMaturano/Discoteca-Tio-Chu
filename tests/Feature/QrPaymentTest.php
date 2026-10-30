<?php

namespace Tests\Feature;

use App\Models\CashClosing;
use App\Models\NightSession;
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
            'is_confirmed' => true,
        ]);
    }

    public function test_formulario_qr_solo_requiere_datos_operativos(): void
    {
        $this->createOpenSession();

        $response = $this->get(route('qrs.index'));

        $response->assertOk();
        $response->assertDontSee('name="reference_code"', false);
        $response->assertDontSee('Identificador del comprobante');
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
            'is_confirmed' => true,
        ]);
    }

    public function test_tc_h14_03_rechaza_montos_invalidos_con_mensaje_controlado(): void
    {
        // Arrange
        $session = $this->createOpenSession();
        $invalidAmounts = [
            [0, 'El monto cobrado debe ser mayor a 0.'],
            [-10, 'El monto cobrado debe ser mayor a 0.'],
            ['no-es-un-monto', 'El monto cobrado debe ser un valor numérico.'],
        ];

        foreach ($invalidAmounts as [$amount, $expectedMessage]) {
            $response = $this->from(route('qrs.index'))->post(route('qrs.store'), [
                'night_session_id' => $session->id,
                'point_of_sale' => 'Barra Principal',
                'operator_name' => 'Operador de prueba',
                'bank_app' => 'YASTA',
                'amount' => $amount,
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
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('qrs.index'));
        $response->assertSessionHasErrors('bank_app');
        $this->assertDatabaseCount('qr_payments', 0);
    }

    public function test_tc_h14_04_permite_registrar_cobros_sin_identificador(): void
    {
        // Arrange
        $session = $this->createOpenSession();
        $payload = [
            'night_session_id' => $session->id,
            'point_of_sale' => 'Subte',
            'operator_name' => 'Operador de prueba',
            'bank_app' => 'YASTA',
            'amount' => 45.00,
        ];

        $response = $this->from(route('qrs.index'))->post(route('qrs.store'), $payload);
        $response->assertStatus(302);
        $response->assertSessionHas('success');
        $this->assertDatabaseCount('qr_payments', 1);
        $this->assertSame(
            45.0,
            (float) CashClosing::where('night_session_id', $session->id)->value('total_qr_yasta')
        );

        $this->assertDatabaseHas('qr_payments', [
            'night_session_id' => $session->id,
            'amount' => '45.00',
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
