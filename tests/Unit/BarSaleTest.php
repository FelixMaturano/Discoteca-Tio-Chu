<?php

namespace Tests\Unit;

use App\Models\BarSale;
use App\Models\Product;
use PHPUnit\Framework\TestCase;

class BarSaleTest extends TestCase
{
    public function test_calculate_totals_uses_package_size_saldo_and_unit_price(): void
    {
        $product = new Product(['units_per_package' => 6]);
        $barSale = new BarSale([
            'packages' => 2,
            'units' => 3,
            'saldo' => 4,
            'unit_price' => 25,
        ]);
        $barSale->setRelation('product', $product);

        $barSale->calculateTotals();

        $this->assertSame(15, $barSale->total_initial);
        $this->assertSame(11, $barSale->vendido);
        $this->assertSame('275.00', $barSale->subtotal);
    }

    public function test_calculate_totals_clamps_sold_units_at_zero(): void
    {
        $product = new Product(['units_per_package' => 6]);
        $barSale = new BarSale([
            'packages' => 1,
            'units' => 2,
            'saldo' => 10,
            'unit_price' => 25,
        ]);
        $barSale->setRelation('product', $product);

        $barSale->calculateTotals();

        $this->assertSame(8, $barSale->total_initial);
        $this->assertSame(0, $barSale->vendido);
        $this->assertSame('0.00', $barSale->subtotal);
    }
}
