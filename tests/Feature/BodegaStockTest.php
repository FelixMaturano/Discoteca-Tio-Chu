<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class BodegaStockTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_tc_h11_01_incrementa_stock_en_uno_y_diez(): void
    {
        // Arrange
        $product = $this->createProductWithStock(5);

        // Act y Assert: incremento de una unidad
        $oneUnitResponse = $this->from(route('products.index'))
            ->patch(route('products.quickStock', $product), ['delta' => 1]);

        $oneUnitResponse->assertRedirect(route('products.index'));
        $oneUnitResponse->assertSessionHas('success');
        $this->assertSame(6, $product->fresh()->stock_warehouse);
        $this->assertDatabaseHas('product_stock_movements', [
            'product_id' => $product->id,
            'movement_type' => 'adjustment',
            'quantity' => 1,
            'stock_before' => 5,
            'stock_after' => 6,
        ]);

        // Act y Assert: incremento de diez unidades
        $tenUnitsResponse = $this->from(route('products.index'))
            ->patch(route('products.quickStock', $product), ['delta' => 10]);

        $tenUnitsResponse->assertRedirect(route('products.index'));
        $tenUnitsResponse->assertSessionHas('success');
        $this->assertSame(16, $product->fresh()->stock_warehouse);
        $this->assertDatabaseHas('product_stock_movements', [
            'product_id' => $product->id,
            'movement_type' => 'adjustment',
            'quantity' => 10,
            'stock_before' => 6,
            'stock_after' => 16,
        ]);
        $this->assertDatabaseCount('product_stock_movements', 2);
    }

    public function test_tc_h11_02_decrementa_stock_en_uno_y_diez_con_saldo_suficiente(): void
    {
        // Arrange
        $product = $this->createProductWithStock(20);

        // Act y Assert: decremento de una unidad
        $oneUnitResponse = $this->from(route('products.index'))
            ->patch(route('products.quickStock', $product), ['delta' => -1]);

        $oneUnitResponse->assertRedirect(route('products.index'));
        $oneUnitResponse->assertSessionHas('success');
        $this->assertSame(19, $product->fresh()->stock_warehouse);
        $this->assertDatabaseHas('product_stock_movements', [
            'product_id' => $product->id,
            'movement_type' => 'adjustment',
            'quantity' => -1,
            'stock_before' => 20,
            'stock_after' => 19,
        ]);

        // Act y Assert: decremento de diez unidades
        $tenUnitsResponse = $this->from(route('products.index'))
            ->patch(route('products.quickStock', $product), ['delta' => -10]);

        $tenUnitsResponse->assertRedirect(route('products.index'));
        $tenUnitsResponse->assertSessionHas('success');
        $this->assertSame(9, $product->fresh()->stock_warehouse);
        $this->assertDatabaseHas('product_stock_movements', [
            'product_id' => $product->id,
            'movement_type' => 'adjustment',
            'quantity' => -10,
            'stock_before' => 19,
            'stock_after' => 9,
        ]);
        $this->assertDatabaseCount('product_stock_movements', 2);
    }

    public function test_tc_h11_03_impide_dejar_el_stock_negativo(): void
    {
        // Arrange
        $product = $this->createProductWithStock(5);

        // Act
        $response = $this->from(route('products.index'))
            ->patch(route('products.quickStock', $product), ['delta' => -6]);

        // Assert
        $response->assertRedirect(route('products.index'));
        $response->assertSessionHasErrors('delta');
        $this->assertSame(5, $product->fresh()->stock_warehouse);
        $this->assertGreaterThanOrEqual(0, $product->fresh()->stock_warehouse);
        $this->assertDatabaseCount('product_stock_movements', 0);
    }

    public function test_tc_h11_04_registra_merma_y_actualiza_el_stock(): void
    {
        // Arrange
        $product = $this->createProductWithStock(10);

        // Act
        $response = $this->from(route('products.index'))->patch(
            route('products.quickStock', $product),
            ['delta' => -3, 'movement_type' => 'merma']
        );

        // Assert
        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success');
        $this->assertSame(7, $product->fresh()->stock_warehouse);
        $this->assertDatabaseCount('product_stock_movements', 1);
        $this->assertDatabaseHas('product_stock_movements', [
            'product_id' => $product->id,
            'movement_type' => 'merma',
            'quantity' => -3,
            'stock_before' => 10,
            'stock_after' => 7,
        ]);
    }

    public function test_tc_h11_05_revierte_el_movimiento_y_el_stock_si_falla_la_persistencia(): void
    {
        // Arrange
        $product = $this->createProductWithStock(10);
        Product::updated(function (): void {
            throw new RuntimeException('Fallo de persistencia inyectado.');
        });
        $this->withoutExceptionHandling();

        // Act
        $exceptionThrown = false;
        try {
            $this->from(route('products.index'))->patch(
                route('products.quickStock', $product),
                ['delta' => -2, 'movement_type' => 'merma']
            );
        } catch (RuntimeException $exception) {
            $exceptionThrown = true;
            $this->assertSame('Fallo de persistencia inyectado.', $exception->getMessage());
        }

        // Assert
        $this->assertTrue($exceptionThrown, 'Se esperaba que el fallo inyectado se propagara.');
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_warehouse' => 10,
            'stock_packages' => 1,
            'stock_units' => 4,
        ]);
        $this->assertDatabaseCount('product_stock_movements', 0);
    }

    public function test_quick_stock_ajax_returns_updated_stock_breakdown(): void
    {
        // Arrange
        $product = $this->createProductWithStock(8);

        // Act
        $response = $this->patchJson(route('products.quickStock', $product), ['delta' => 4]);

        // Assert
        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('product_id', $product->id)
            ->assertJsonPath('stock_warehouse', 12)
            ->assertJsonPath('stock_packages', 2)
            ->assertJsonPath('stock_units', 0)
            ->assertJsonPath('status', 'in_stock');
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_warehouse' => 12,
            'stock_packages' => 2,
            'stock_units' => 0,
        ]);
    }

    public function test_direct_stock_edit_recalculates_packages_and_units(): void
    {
        // Arrange
        $product = $this->createProductWithStock(8);

        // Act
        $response = $this->from(route('products.index'))
            ->patch(route('products.quickStock', $product), ['stock' => 20]);

        // Assert
        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_warehouse' => 20,
            'stock_packages' => 3,
            'stock_units' => 2,
        ]);
        $this->assertDatabaseHas('product_stock_movements', [
            'product_id' => $product->id,
            'movement_type' => 'adjustment',
            'quantity' => 12,
            'stock_before' => 8,
            'stock_after' => 20,
        ]);
    }

    public function test_direct_package_and_unit_edit_normalizes_unit_overflow(): void
    {
        // Arrange
        $product = $this->createProductWithStock(8);

        // Act
        $response = $this->from(route('products.index'))->patch(
            route('products.quickStock', $product),
            ['stock_packages' => 1, 'stock_units' => 8]
        );

        // Assert
        $response->assertRedirect(route('products.index'));
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_warehouse' => 14,
            'stock_packages' => 2,
            'stock_units' => 2,
        ]);
        $this->assertDatabaseHas('product_stock_movements', [
            'product_id' => $product->id,
            'quantity' => 6,
            'stock_before' => 8,
            'stock_after' => 14,
        ]);
    }

    public function test_product_stock_helpers_keep_package_breakdown_consistent(): void
    {
        // Arrange
        $product = $this->createProductWithStock(8);

        // Act
        $product->deductWarehouseStock(10);
        $product->refresh();
        $product->restoreWarehouseStock(14);

        // Assert
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'stock_warehouse' => 14,
            'stock_packages' => 2,
            'stock_units' => 2,
        ]);
    }

    public function test_invalid_or_zero_delta_does_not_create_movement(): void
    {
        // Arrange
        $product = $this->createProductWithStock(8);

        // Act y Assert: un delta no entero se rechaza
        $invalidResponse = $this->from(route('products.index'))
            ->patch(route('products.quickStock', $product), ['delta' => 'invalid']);

        $invalidResponse->assertRedirect(route('products.index'));
        $invalidResponse->assertSessionHasErrors('delta');
        $this->assertSame(8, $product->fresh()->stock_warehouse);
        $this->assertDatabaseCount('product_stock_movements', 0);

        // Act y Assert: delta cero no modifica stock ni crea movimiento
        $zeroResponse = $this->from(route('products.index'))
            ->patch(route('products.quickStock', $product), ['delta' => 0]);

        $zeroResponse->assertRedirect(route('products.index'));
        $zeroResponse->assertSessionHas('success');
        $this->assertSame(8, $product->fresh()->stock_warehouse);
        $this->assertDatabaseCount('product_stock_movements', 0);
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
}
