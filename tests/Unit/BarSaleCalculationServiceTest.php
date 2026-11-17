<?php

namespace Tests\Unit;

use App\Models\BarSale;
use App\Models\Product;
use App\Services\BarSaleCalculationService;
use PHPUnit\Framework\TestCase;

class BarSaleCalculationServiceTest extends TestCase
{
    public function test_summary_applies_default_and_special_mixer_combos(): void
    {
        $liquor = $this->sale(1, 'Licores', 3, 10, '{"3":1}');
        $defaultMixer = $this->sale(2, 'Mixers', 5, 2);
        $specialMixer = $this->sale(3, 'Mixers', 1, 3);

        $summary = (new BarSaleCalculationService)->summarize(
            collect([$liquor, $defaultMixer, $specialMixer]),
            false,
            [1 => ['mixer_id' => 2, 'ratio' => 2]]
        );

        $this->assertSame(4, $defaultMixer->included_in_combos);
        $this->assertSame(1, $specialMixer->included_in_combos);
        $this->assertSame(1, $defaultMixer->extras);
        $this->assertEquals(2, $defaultMixer->subtotal);
        $this->assertEquals(32, $summary['grandTotalBar']);
    }

    public function test_store_summary_includes_manual_sales_in_total_without_persisting_calculation_fields(): void
    {
        $liquor = $this->sale(1, 'Licores', 3, 10, '{"3":1}');
        $defaultMixer = $this->sale(2, 'Mixers', 5, 2);
        $specialMixer = $this->sale(3, 'Mixers', 1, 3);

        $summary = (new BarSaleCalculationService)->summarize(
            collect([$liquor, $defaultMixer, $specialMixer]),
            true,
            [1 => ['mixer_id' => 2, 'ratio' => 2]],
            10
        );

        $this->assertSame(9, $summary['totalTiendaUnits']);
        $this->assertEquals(42, $summary['grandTotalBar']);
        $this->assertSame(4, $defaultMixer->included_in_combos);
    }

    private function sale(int $productId, string $category, int $sold, float $price, ?string $specialMixers = null): BarSale
    {
        $product = (new Product)->forceFill([
            'id' => $productId,
            'name' => 'Producto '.$productId,
            'category' => $category,
        ]);
        $sale = (new BarSale)->forceFill([
            'product_id' => $productId,
            'vendido' => $sold,
            'unit_price' => $price,
            'subtotal' => $sold * $price,
            'selected_special_mixer' => $specialMixers,
        ]);
        $sale->setRelation('product', $product);

        return $sale;
    }
}
