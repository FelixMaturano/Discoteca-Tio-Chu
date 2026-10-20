<?php

namespace Tests\Unit;

use App\Models\Invoice;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class InvoiceTest extends TestCase
{
    // -----------------------------------------------------------------------
    // CASO EXISTENTE — Sprint 1 (no modificado)
    // -----------------------------------------------------------------------

    public function test_calculate_net_does_not_apply_commission_to_cash_payments(): void
    {
        $invoice = new Invoice([
            'payment_method' => 'efectivo',
            'amount' => '123.45',
            'commission_rate' => '0.2500',
        ]);

        $invoice->calculateNet();

        $this->assertSame('0.00', $invoice->commission_amount);
        $this->assertSame('123.45', $invoice->net_amount);
    }

    // -----------------------------------------------------------------------
    // H13 — TC-H13-UNIT-01: Tarjeta con tasa entera aplica comisión y neto
    // -----------------------------------------------------------------------

    /**
     * @see Invoice::calculateNet()
     */
    #[DataProvider('cardPaymentCases')]
    public function test_tc_h13_unit_01_calcula_comision_y_neto_para_pago_con_tarjeta(
        string $amount,
        string $commissionRate,
        string $expectedCommission,
        string $expectedNet
    ): void {
        // Arrange
        $invoice = new Invoice([
            'payment_method' => 'tarjeta',
            'amount' => $amount,
            'commission_rate' => $commissionRate,
        ]);

        // Act
        $invoice->calculateNet();

        // Assert
        $this->assertSame(
            $expectedCommission,
            $invoice->commission_amount,
            "Comisión esperada {$expectedCommission} para monto {$amount} × tasa {$commissionRate}"
        );
        $this->assertSame(
            $expectedNet,
            $invoice->net_amount,
            "Neto esperado {$expectedNet} para monto {$amount} × tasa {$commissionRate}"
        );
        // Invariante: bruto = comisión + neto
        $this->assertSame(
            round((float) $amount, 2),
            round((float) $invoice->commission_amount + (float) $invoice->net_amount, 2),
            'La suma comisión + neto debe ser igual al monto bruto'
        );
    }

    public static function cardPaymentCases(): array
    {
        return [
            // Descripción              amount        rate      comisión   neto
            '5% sobre importe exacto' => ['100.00', '0.0500', '5.00',   '95.00'],
            '1.3% config Tío Chu' => ['200.00', '0.0130', '2.60',  '197.40'],
            'Tasa mínima 0%' => ['250.00', '0.0000', '0.00',  '250.00'],
            'Tasa máxima 100%' => ['100.00', '1.0000', '100.00', '0.00'],
            'Importe con decimales' => ['250.75', '0.0400', '10.03', '240.72'],
        ];
    }

    // -----------------------------------------------------------------------
    // H13 — TC-H13-UNIT-02: Redondeo a 2 decimales para medios centavos
    // -----------------------------------------------------------------------

    #[DataProvider('decimalRoundingCases')]
    public function test_tc_h13_unit_02_redondea_comision_correctamente_para_importes_decimales(
        string $amount,
        string $commissionRate,
        string $expectedCommission,
        string $expectedNet
    ): void {
        // Arrange
        $invoice = new Invoice([
            'payment_method' => 'tarjeta',
            'amount' => $amount,
            'commission_rate' => $commissionRate,
        ]);

        // Act
        $invoice->calculateNet();

        // Assert
        $this->assertSame($expectedCommission, $invoice->commission_amount);
        $this->assertSame($expectedNet, $invoice->net_amount);
    }

    public static function decimalRoundingCases(): array
    {
        return [
            // Bs.100.10 × 5% = 5.005 → redondea a 5.01, neto 95.09
            'medio centavo redondea arriba' => ['100.10', '0.0500', '5.01', '95.09'],
            // Bs.19.99 × 1.3% = 0.25987 → redondea a 0.26, neto 19.73
            'porcentaje pequeño con decimal' => ['19.99', '0.0130', '0.26', '19.73'],
            // Bs.100.50 × 3.5% = 3.5175 → redondea a 3.52, neto 96.98
            'porcentaje fraccionario' => ['100.50', '0.0350', '3.52', '96.98'],
        ];
    }

    // -----------------------------------------------------------------------
    // H13 — TC-H13-UNIT-03: El método es idempotente (segunda llamada = mismo resultado)
    // -----------------------------------------------------------------------

    public function test_tc_h13_unit_03_calculate_net_es_idempotente(): void
    {
        // Arrange
        $invoice = new Invoice([
            'payment_method' => 'tarjeta',
            'amount' => '150.00',
            'commission_rate' => '0.0500',
        ]);

        // Act — llamar dos veces
        $invoice->calculateNet();
        $commissionAfterFirst = $invoice->commission_amount;
        $netAfterFirst = $invoice->net_amount;

        $invoice->calculateNet();

        // Assert — segunda llamada produce el mismo resultado
        $this->assertSame($commissionAfterFirst, $invoice->commission_amount);
        $this->assertSame($netAfterFirst, $invoice->net_amount);
    }

    // -----------------------------------------------------------------------
    // H13 — TC-H13-UNIT-04: Pago en efectivo ignora la tasa aunque sea >0
    // -----------------------------------------------------------------------

    public function test_tc_h13_unit_04_efectivo_siempre_produce_comision_cero_independientemente_de_tasa(): void
    {
        // Arrange — tasa definida pero método = efectivo
        $invoice = new Invoice([
            'payment_method' => 'efectivo',
            'amount' => '500.00',
            'commission_rate' => '0.0500',
        ]);

        // Act
        $invoice->calculateNet();

        // Assert
        $this->assertSame('0.00', $invoice->commission_amount);
        $this->assertSame('500.00', $invoice->net_amount);
    }
}
