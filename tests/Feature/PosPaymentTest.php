<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\NightSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_tc_h13_01_registra_bruto_y_guarda_comision_y_neto(): void
    {
        // Arrange
        $session = $this->createOpenSession();
        $payload = [
            'night_session_id' => $session->id,
            'payment_method' => 'tarjeta',
            'amount' => 100,
            'commission_rate' => 0.05,
        ];

        // Act
        $response = $this->from(route('invoices.index'))->post(route('invoices.store'), $payload);

        // Assert
        $response->assertStatus(302);
        $response->assertRedirect(route('invoices.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('invoices', [
            'night_session_id' => $session->id,
            'payment_method' => 'tarjeta',
            'amount' => '100.00',
            'commission_rate' => '0.0500',
            'commission_amount' => '5.00',
            'net_amount' => '95.00',
        ]);
    }

    public function test_tc_h13_02_redondea_la_comision_de_importes_decimales(): void
    {
        // Arrange
        $session = $this->createOpenSession();
        $grossAmount = 100.50;
        $commissionRate = 0.035;
        $expectedCommission = round($grossAmount * $commissionRate, 2);
        $expectedNet = round($grossAmount - $expectedCommission, 2);

        // Act
        $response = $this->from(route('invoices.index'))->post(route('invoices.store'), [
            'night_session_id' => $session->id,
            'payment_method' => 'tarjeta',
            'amount' => $grossAmount,
            'commission_rate' => $commissionRate,
        ]);

        // Assert
        $response->assertStatus(302);
        $response->assertRedirect(route('invoices.index'));
        $response->assertSessionHas('success');

        $invoice = Invoice::where('night_session_id', $session->id)->firstOrFail();
        $this->assertSame($expectedCommission, (float) $invoice->commission_amount);
        $this->assertSame($expectedNet, (float) $invoice->net_amount);
        $this->assertSame(
            round((float) $invoice->amount, 2),
            round((float) $invoice->commission_amount + (float) $invoice->net_amount, 2)
        );
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'amount' => '100.50',
            'commission_amount' => '3.52',
            'net_amount' => '96.98',
        ]);
    }

    public function test_tc_h13_03_rechaza_importes_y_tasas_invalidos(): void
    {
        // Arrange
        $session = $this->createOpenSession();
        $invalidPayloads = [
            [
                'amount' => 0,
                'commission_rate' => 0.05,
                'error' => 'amount',
            ],
            [
                'amount' => -1,
                'commission_rate' => 0.05,
                'error' => 'amount',
            ],
            [
                'amount' => 100,
                'commission_rate' => 1.01,
                'error' => 'commission_rate',
            ],
        ];

        // Act y Assert
        foreach ($invalidPayloads as $invalidPayload) {
            $response = $this->from(route('invoices.index'))->post(route('invoices.store'), [
                'night_session_id' => $session->id,
                'payment_method' => 'tarjeta',
                'amount' => $invalidPayload['amount'],
                'commission_rate' => $invalidPayload['commission_rate'],
            ]);

            $response->assertStatus(302);
            $response->assertRedirect(route('invoices.index'));
            $response->assertSessionHasErrors($invalidPayload['error']);
            $this->assertDatabaseCount('invoices', 0);
        }
    }

    public function test_tc_h13_04_acepta_monto_maximo_con_tasa_cero(): void
    {
        // Arrange
        $session = $this->createOpenSession();
        $grossAmount = '99999999.99';

        // Act
        $response = $this->from(route('invoices.index'))->post(route('invoices.store'), [
            'night_session_id' => $session->id,
            'payment_method' => 'tarjeta',
            'amount' => $grossAmount,
            'commission_rate' => 0,
        ]);

        // Assert
        $response->assertStatus(302);
        $response->assertRedirect(route('invoices.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('invoices', [
            'night_session_id' => $session->id,
            'amount' => $grossAmount,
            'commission_rate' => '0.0000',
            'commission_amount' => '0.00',
            'net_amount' => $grossAmount,
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
