<?php

namespace Tests\Feature;

use App\Models\BarSale;
use App\Models\NightSession;
use App\Models\Product;
use App\Models\ProductStockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class BarDispatchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_tc_h12_01_despacha_stock_a_barra_principal(): void
    {
        // Arrange
        $product = $this->createProductWithStock(50);
        $session = $this->createOpenSession();
        $barSale = $this->createBarSale($session, $product, 'Barra Principal');

        // Act
        $response = $this->dispatchToBar($session, $barSale, 'Barra Principal', 8);

        // Assert
        $response->assertStatus(302);
        $response->assertRedirect(route('barInventory.index', [
            'session_id' => $session->id,
            'bar' => 'Barra Principal',
        ]));
        $response->assertSessionHas('success');
        $this->assertSame(42, $product->fresh()->stock_warehouse);
        $this->assertSame(8, $barSale->fresh()->total_initial);
        $this->assertDatabaseHas('product_stock_movements', [
            'product_id' => $product->id,
            'movement_type' => 'transfer',
            'source' => 'Bodega Central',
            'destination' => 'Barra Principal',
            'quantity' => -8,
            'stock_before' => 50,
            'stock_after' => 42,
        ]);
    }

    public function test_tc_h12_02_despacha_a_subterraneo_y_tienda_sin_mezclar_saldos(): void
    {
        // Arrange
        $product = $this->createProductWithStock(20);
        $session = $this->createOpenSession();
        $subterraneoSale = $this->createBarSale($session, $product, 'Barra Subterráneo');
        $tiendaSale = $this->createBarSale($session, $product, 'Tienda');

        // Act
        $subterraneoResponse = $this->dispatchToBar($session, $subterraneoSale, 'Barra Subterráneo', 5);
        $tiendaResponse = $this->dispatchToBar($session, $tiendaSale, 'Tienda', 7);

        // Assert
        $subterraneoResponse->assertRedirect(route('barInventory.index', [
            'session_id' => $session->id,
            'bar' => 'Barra Subterráneo',
        ]));
        $tiendaResponse->assertRedirect(route('barInventory.index', [
            'session_id' => $session->id,
            'bar' => 'Tienda',
        ]));
        $this->assertSame(5, $subterraneoSale->fresh()->total_initial);
        $this->assertSame(7, $tiendaSale->fresh()->total_initial);
        $this->assertSame(8, $product->fresh()->stock_warehouse);
        $this->assertDatabaseHas('product_stock_movements', [
            'product_id' => $product->id,
            'destination' => 'Barra Subterráneo',
            'quantity' => -5,
        ]);
        $this->assertDatabaseHas('product_stock_movements', [
            'product_id' => $product->id,
            'destination' => 'Tienda',
            'quantity' => -7,
        ]);
    }

    public function test_tc_h12_03_rechaza_despacho_superior_al_stock_de_bodega(): void
    {
        // Arrange
        $product = $this->createProductWithStock(5);
        $session = $this->createOpenSession();
        $barSale = $this->createBarSale($session, $product, 'Barra Principal');

        // Act
        $response = $this->dispatchToBar($session, $barSale, 'Barra Principal', 6);

        // Assert
        $response->assertStatus(302);
        $response->assertRedirect(route('barInventory.index', [
            'session_id' => $session->id,
            'bar' => 'Barra Principal',
        ]));
        $response->assertSessionHasErrors("inventory.{$barSale->id}.added_units");
        $this->assertSame(5, $product->fresh()->stock_warehouse);
        $this->assertSame(0, $barSale->fresh()->total_initial);
        $this->assertDatabaseCount('product_stock_movements', 0);
    }

    public function test_tc_h12_04_revierte_toda_la_transferencia_si_falla_la_persistencia(): void
    {
        // Arrange
        $product = $this->createProductWithStock(20);
        $session = $this->createOpenSession();
        $barSale = $this->createBarSale($session, $product, 'Barra Principal');
        BarSale::updated(function (): void {
            throw new RuntimeException('Fallo de persistencia inyectado.');
        });
        $this->withoutExceptionHandling();

        // Act
        $exceptionThrown = false;
        try {
            $this->dispatchToBar($session, $barSale, 'Barra Principal', 4);
        } catch (RuntimeException $exception) {
            $exceptionThrown = true;
            $this->assertSame('Fallo de persistencia inyectado.', $exception->getMessage());
        }

        // Assert
        $this->assertTrue($exceptionThrown, 'Se esperaba que el fallo inyectado se propagara.');
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_warehouse' => 20,
            'stock_packages' => 3,
            'stock_units' => 2,
        ]);
        $this->assertDatabaseHas('bar_sales', [
            'id' => $barSale->id,
            'added_packages' => 0,
            'added_units' => 0,
            'total_initial' => 0,
        ]);
        $this->assertDatabaseCount('product_stock_movements', 0);
    }

    public function test_tc_h12_05_bodega_barra_y_movimiento_reflejan_el_mismo_despacho(): void
    {
        // Arrange
        $product = $this->createProductWithStock(30);
        $session = $this->createOpenSession();
        $barSale = $this->createBarSale($session, $product, 'Barra Principal');

        // Act
        $this->dispatchToBar($session, $barSale, 'Barra Principal', 4);

        // Assert
        $movement = ProductStockMovement::where('product_id', $product->id)
            ->where('movement_type', 'transfer')
            ->firstOrFail();
        $this->assertSame(30, $movement->stock_before);
        $this->assertSame(26, $movement->stock_after);
        $this->assertSame(-4, $movement->quantity);
        $this->assertSame(26, $product->fresh()->stock_warehouse);
        $this->assertSame(4, $barSale->fresh()->total_initial);
        $this->assertSame('Bodega Central', $movement->source);
        $this->assertSame('Barra Principal', $movement->destination);
        $barSaleAfterDispatch = $barSale->fresh();
        $dispatchedToBar = ((int) $barSaleAfterDispatch->added_packages * (int) $product->units_per_package)
            + (int) $barSaleAfterDispatch->added_units;
        $this->assertSame($dispatchedToBar, $movement->stock_before - $movement->stock_after);
        $this->assertSame($dispatchedToBar, 30 - (int) $product->fresh()->stock_warehouse);

        $salesResponse = $this->put(route('sales.updateBulk'), [
            'night_session_id' => $session->id,
            'bar_name' => 'Barra Principal',
            'sales' => [
                $barSale->id => [
                    'packages' => 0,
                    'units' => 4,
                    'saldo' => 2,
                    'unit_price' => 10,
                ],
            ],
        ]);

        $salesResponse->assertSessionHas('success');
        $this->assertSame(26, $product->fresh()->stock_warehouse);
    }

    public function test_bulk_dispatch_rolls_back_all_rows_when_one_product_lacks_stock(): void
    {
        // Arrange
        $availableProduct = $this->createProductWithStock(20);
        $insufficientProduct = $this->createProductWithStock(2);
        $session = $this->createOpenSession();
        $availableSale = $this->createBarSale($session, $availableProduct, 'Barra Principal');
        $insufficientSale = $this->createBarSale($session, $insufficientProduct, 'Barra Principal');

        // Act
        $response = $this->from(route('barInventory.index', [
            'session_id' => $session->id,
            'bar' => 'Barra Principal',
        ]))->put(route('barInventory.updateBulk'), [
            'night_session_id' => $session->id,
            'bar_name' => 'Barra Principal',
            'inventory' => [
                $availableSale->id => ['added_units' => 5],
                $insufficientSale->id => ['added_units' => 5],
            ],
        ]);

        // Assert
        $response->assertRedirect(route('barInventory.index', [
            'session_id' => $session->id,
            'bar' => 'Barra Principal',
        ]));
        $response->assertSessionHasErrors("inventory.{$insufficientSale->id}.added_units");
        $this->assertSame(20, $availableProduct->fresh()->stock_warehouse);
        $this->assertSame(2, $insufficientProduct->fresh()->stock_warehouse);
        $this->assertSame(0, $availableSale->fresh()->total_initial);
        $this->assertSame(0, $insufficientSale->fresh()->total_initial);
        $this->assertDatabaseCount('product_stock_movements', 0);
    }

    public function test_reducing_dispatch_restores_stock_with_reverse_movement(): void
    {
        // Arrange
        $product = $this->createProductWithStock(20);
        $session = $this->createOpenSession();
        $barSale = $this->createBarSale($session, $product, 'Barra Principal');
        $this->dispatchToBar($session, $barSale, 'Barra Principal', 10);

        // Act
        $response = $this->dispatchToBar($session, $barSale, 'Barra Principal', 4);

        // Assert
        $response->assertSessionHas('success');
        $this->assertSame(16, $product->fresh()->stock_warehouse);
        $this->assertSame(4, $barSale->fresh()->total_initial);
        $this->assertDatabaseHas('product_stock_movements', [
            'product_id' => $product->id,
            'movement_type' => 'transfer',
            'source' => 'Barra Principal',
            'destination' => 'Bodega Central',
            'quantity' => 6,
            'stock_before' => 10,
            'stock_after' => 16,
        ]);
        $this->assertDatabaseCount('product_stock_movements', 2);
    }

    public function test_dispatch_converts_packages_and_units(): void
    {
        // Arrange
        $product = $this->createProductWithStock(30);
        $session = $this->createOpenSession();
        $barSale = $this->createBarSale($session, $product, 'Barra Principal');

        // Act
        $response = $this->dispatchToBar($session, $barSale, 'Barra Principal', 15);

        // Assert
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('bar_sales', [
            'id' => $barSale->id,
            'added_packages' => 2,
            'added_units' => 3,
            'total_initial' => 15,
        ]);
        $this->assertSame(15, $product->fresh()->stock_warehouse);
        $this->assertDatabaseHas('product_stock_movements', [
            'product_id' => $product->id,
            'quantity' => -15,
            'stock_before' => 30,
            'stock_after' => 15,
        ]);
    }

    public function test_sync_previous_night_carries_bar_saldo_forward_without_changing_warehouse(): void
    {
        // Arrange
        $productWithPreviousSaldo = $this->createProductWithStock(30);
        $productWithoutPreviousSaldo = $this->createProductWithStock(12);
        $previousSession = NightSession::create([
            'session_date' => '2026-10-09',
            'day_name' => 'Viernes',
            'status' => 'closed',
            'pos_commission_rate' => 0.013,
        ]);
        $currentSession = NightSession::create([
            'session_date' => '2026-10-16',
            'day_name' => 'Viernes',
            'status' => 'open',
            'pos_commission_rate' => 0.013,
        ]);
        $previousBarSale = $this->createBarSale($previousSession, $productWithPreviousSaldo, 'Barra Principal');
        $previousBarSale->update(['saldo' => 14]);
        $currentBarSale = $this->createBarSale($currentSession, $productWithPreviousSaldo, 'Barra Principal');
        $noPreviousSale = $this->createBarSale($currentSession, $productWithoutPreviousSaldo, 'Barra Principal');

        // Act
        $response = $this->from(route('barInventory.index', [
            'session_id' => $currentSession->id,
            'bar' => 'Barra Principal',
        ]))->post(route('barInventory.sync'), [
            'session_id' => $currentSession->id,
            'bar_name' => 'Barra Principal',
        ]);

        // Assert
        $response->assertRedirect(route('barInventory.index', [
            'session_id' => $currentSession->id,
            'bar' => 'Barra Principal',
        ]));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('bar_sales', [
            'id' => $currentBarSale->id,
            'initial_packages' => 2,
            'initial_units' => 2,
            'total_initial' => 14,
            'saldo' => 0,
        ]);
        $this->assertDatabaseHas('bar_sales', [
            'id' => $noPreviousSale->id,
            'initial_packages' => 0,
            'initial_units' => 0,
            'total_initial' => 0,
        ]);
        $this->assertSame(30, $productWithPreviousSaldo->fresh()->stock_warehouse);
        $this->assertSame(12, $productWithoutPreviousSaldo->fresh()->stock_warehouse);
    }

    public function test_sync_previous_night_rejects_closed_session_and_missing_history(): void
    {
        // Arrange
        $session = $this->createOpenSession();
        $requestData = [
            'session_id' => $session->id,
            'bar_name' => 'Barra Principal',
        ];

        // Act y Assert: no existe noche anterior
        $noHistoryResponse = $this->from(route('barInventory.index', ['session_id' => $session->id]))
            ->post(route('barInventory.sync'), $requestData);
        $noHistoryResponse->assertSessionHas('error', 'No se encontró una noche anterior para sincronizar saldos.');

        // Act y Assert: noche cerrada
        $session->update(['status' => 'closed']);
        $closedResponse = $this->from(route('barInventory.index', ['session_id' => $session->id]))
            ->post(route('barInventory.sync'), $requestData);
        $closedResponse->assertSessionHas('error', 'La noche está cerrada. No se pueden sincronizar saldos en una noche cerrada.');
    }

    public function test_inventory_index_renders_and_initializes_rows_for_each_bar(): void
    {
        // Arrange
        $product = $this->createProductWithStock(20);
        $session = $this->createOpenSession();

        // Act y Assert
        foreach (['Barra Principal', 'Barra Subterráneo', 'Tienda'] as $barName) {
            $response = $this->get(route('barInventory.index', [
                'session_id' => $session->id,
                'bar' => $barName,
            ]));

            $response->assertOk();
            $response->assertSee('Inventario de Barras');
            $this->assertDatabaseHas('bar_sales', [
                'night_session_id' => $session->id,
                'product_id' => $product->id,
                'bar_name' => $barName,
            ]);
        }

        $this->assertDatabaseCount('bar_sales', 3);
    }

    private function dispatchToBar(
        NightSession $session,
        BarSale $barSale,
        string $destination,
        int $quantity
    ) {
        $unitsPerPackage = max(1, (int) $barSale->product->units_per_package);

        return $this->from(route('barInventory.index', [
            'session_id' => $session->id,
            'bar' => $destination,
        ]))->put(route('barInventory.updateBulk'), [
            'night_session_id' => $session->id,
            'bar_name' => $destination,
            'inventory' => [
                $barSale->id => [
                    'initial_packages' => 0,
                    'initial_units' => 0,
                    'added_packages' => intdiv($quantity, $unitsPerPackage),
                    'added_units' => $quantity % $unitsPerPackage,
                    'night_packages' => 0,
                    'night_units' => 0,
                ],
            ],
        ]);
    }

    private function createProductWithStock(int $stock): Product
    {
        return Product::create([
            'name' => 'Producto de prueba',
            'category' => 'Otros',
            'sale_price' => 10,
            'cost_price' => 5,
            'unit' => 'Unidad',
            'units_per_package' => 6,
            'stock_warehouse' => $stock,
            'stock_packages' => intdiv($stock, 6),
            'stock_units' => $stock % 6,
            'is_active' => true,
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

    private function createBarSale(NightSession $session, Product $product, string $barName): BarSale
    {
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
            'unit_price' => $product->sale_price,
            'subtotal' => 0,
            'stock_synced_vendido' => 0,
        ]);
    }
}
