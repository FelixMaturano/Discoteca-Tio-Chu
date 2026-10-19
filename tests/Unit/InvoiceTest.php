<?php

namespace Tests\Unit;

use App\Models\Invoice;
use PHPUnit\Framework\TestCase;

class InvoiceTest extends TestCase
{
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
}
