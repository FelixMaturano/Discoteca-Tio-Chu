<?php

namespace Tests\Feature;

use App\Models\BarSale;
use App\Models\NightSession;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class H4BarLeftoversAndCombosTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_tc_h4_01_calcula_combos_y_botellas_extra_segun_sobrantes_en_cada_barra(): void
    {
        $session = NightSession::create([
            'session_date' => '2026-10-16',
            'day_name' => 'Viernes',
            'status' => 'open',
            'pos_commission_rate' => 0.013,
        ]);

        $gin = $this->createProduct(20, 'Ganesha Gin', 'Licores', 300);
        $tonic = $this->createProduct(26, 'Agua Tónica', 'Mixers', 20);

        foreach (['Barra Principal', 'Barra Subterráneo'] as $barName) {
            $ginSale = $this->createBarSale($session, $gin, $barName, 300);
            $tonicSale = $this->createBarSale($session, $tonic, $barName, 20);

            $response = $this->put(route('sales.updateBulk'), [
                'night_session_id' => $session->id,
                'bar_name' => $barName,
                'sales' => [
                    $ginSale->id => [
                        'packages' => 0,
                        'units' => 7,
                        'saldo' => 2,
                        'unit_price' => 300,
                    ],
                    $tonicSale->id => [
                        'packages' => 2,
                        'units' => 1,
                        'saldo' => 2,
                        'unit_price' => 20,
                    ],
                ],
            ]);

            $response->assertRedirect(route('sales.index', [
                'session_id' => $session->id,
                'bar' => $barName,
            ]));
            $response->assertSessionHas('success');

            $this->assertDatabaseHas('bar_sales', [
                'id' => $ginSale->id,
                'total_initial' => 7,
                'saldo' => 2,
                'vendido' => 5,
                'subtotal' => '1500.00',
            ]);
            $this->assertDatabaseHas('bar_sales', [
                'id' => $tonicSale->id,
                'total_initial' => 13,
                'saldo' => 2,
                'vendido' => 11,
                'subtotal' => '20.00',
            ]);
        }
    }

    private function createProduct(int $id, string $name, string $category, float $salePrice): Product
    {
        return Product::forceCreate([
            'id' => $id,
            'name' => $name,
            'category' => $category,
            'sale_price' => $salePrice,
            'cost_price' => 0,
            'unit' => 'Botella',
            'units_per_package' => 6,
            'stock_warehouse' => 0,
            'stock_packages' => 0,
            'stock_units' => 0,
            'is_active' => true,
        ]);
    }

    private function createBarSale(
        NightSession $session,
        Product $product,
        string $barName,
        float $unitPrice
    ): BarSale {
        return BarSale::create([
            'night_session_id' => $session->id,
            'product_id' => $product->id,
            'bar_name' => $barName,
            'initial_packages' => 0,
            'initial_units' => 0,
            'added_packages' => 0,
            'added_units' => 0,
            'night_packages' => 0,
            'night_units' => 0,
            'packages' => 0,
            'units' => 0,
            'total_initial' => 0,
            'saldo' => 0,
            'vendido' => 0,
            'unit_price' => $unitPrice,
            'subtotal' => 0,
        ]);
    }
}
