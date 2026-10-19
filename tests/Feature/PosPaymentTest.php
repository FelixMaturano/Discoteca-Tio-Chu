<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\NightSession;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PosPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    #[DataProvider('validGrossAndRateCases')]
    public function test_tc_h13_01_registra_bruto_y_guarda_comision_y_neto(
        string $grossAmount,
        float $commissionRatePercent,
        string $expectedCommission,
        string $expectedNet
    ): void {
        // Arrange
        $session = $this->createOpenSession();
        $commissionRate = $commissionRatePercent / 100;
        $payload = [
            'night_session_id' => $session->id,
            'payment_method' => 'tarjeta',
            'amount' => $grossAmount,
            'commission_rate' => $commissionRate,
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
            'amount' => number_format((float) $grossAmount, 2, '.', ''),
            'commission_rate' => number_format($commissionRate, 4, '.', ''),
            'commission_amount' => $expectedCommission,
            'net_amount' => $expectedNet,
        ]);
    }

    #[DataProvider('decimalGrossAndRateCases')]
    public function test_tc_h13_02_redondea_la_comision_de_importes_decimales(
        string $grossAmount,
        float $commissionRatePercent,
        string $expectedCommission,
        string $expectedNet
    ): void {
        // Arrange
        $session = $this->createOpenSession();
        $commissionRate = $commissionRatePercent / 100;
        $calculatedCommission = number_format(
            round((float) $grossAmount * $commissionRatePercent / 100, 2),
            2,
            '.',
            ''
        );
        $this->assertSame($expectedCommission, $calculatedCommission);

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
        $this->assertSame($expectedCommission, number_format((float) $invoice->commission_amount, 2, '.', ''));
        $this->assertSame($expectedNet, number_format((float) $invoice->net_amount, 2, '.', ''));
        $this->assertSame(
            round((float) $invoice->amount, 2),
            round((float) $invoice->commission_amount + (float) $invoice->net_amount, 2)
        );
        $this->assertDatabaseHas('invoices', [
            'id' => $invoice->id,
            'amount' => number_format((float) $grossAmount, 2, '.', ''),
            'commission_rate' => number_format($commissionRate, 4, '.', ''),
            'commission_amount' => $expectedCommission,
            'net_amount' => $expectedNet,
        ]);
    }

    #[DataProvider('invalidGrossAndRateCases')]
    public function test_tc_h13_03_rechaza_importes_y_tasas_invalidos(
        mixed $grossAmount,
        float $commissionRate,
        string $errorField
    ): void {
        // Arrange
        $session = $this->createOpenSession();

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
        $response->assertSessionHasErrors($errorField);
        $this->assertDatabaseCount('invoices', 0);
    }

    #[DataProvider('boundaryGrossAndRateCases')]
    public function test_tc_h13_04_acepta_valores_limite_y_tasa_cero(
        string $grossAmount,
        float $commissionRatePercent,
        string $expectedCommission,
        string $expectedNet
    ): void {
        // Arrange
        $session = $this->createOpenSession();
        $commissionRate = $commissionRatePercent / 100;

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
        $this->assertDatabaseHas('invoices', [
            'night_session_id' => $session->id,
            'amount' => $grossAmount,
            'commission_rate' => number_format($commissionRate, 4, '.', ''),
            'commission_amount' => $expectedCommission,
            'net_amount' => $expectedNet,
        ]);
    }

    public static function validGrossAndRateCases(): array
    {
        return [
            '5 percent configured' => ['100.00', 5.0, '5.00', '95.00'],
            '4 percent with decimal gross' => ['250.75', 4.0, '10.03', '240.72'],
        ];
    }

    public static function decimalGrossAndRateCases(): array
    {
        return [
            'half-cent rounds up' => ['100.10', 5.0, '5.01', '95.09'],
            'fractional percentage' => ['100.50', 3.5, '3.52', '96.98'],
            'small decimal commission' => ['19.99', 1.3, '0.26', '19.73'],
        ];
    }

    public static function invalidGrossAndRateCases(): array
    {
        return [
            'zero gross' => [0, 0.05, 'amount'],
            'negative gross' => [-1, 0.05, 'amount'],
            'negative rate' => [100, -0.0001, 'commission_rate'],
            'rate above 100 percent' => [100, 1.0001, 'commission_rate'],
        ];
    }

    public static function boundaryGrossAndRateCases(): array
    {
        return [
            'minimum valid gross at zero percent' => ['0.01', 0.0, '0.00', '0.01'],
            'maximum gross at zero percent' => ['99999999.99', 0.0, '0.00', '99999999.99'],
            'maximum gross at 100 percent' => ['99999999.99', 100.0, '99999999.99', '0.00'],
        ];
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
