<?php

namespace Tests\Feature;

use App\Models\NightSession;
use App\Models\Product;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InitialHtmlPayloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_and_inventory_pages_keep_initial_html_under_250_kb(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        for ($index = 1; $index <= 150; $index++) {
            Product::create([
                'name' => 'Bebida de prueba '.$index,
                'category' => 'Licores',
                'sale_price' => 80,
                'cost_price' => 50,
                'unit' => 'botella',
                'units_per_package' => 6,
                'stock_warehouse' => 15,
                'is_active' => true,
            ]);
            Staff::create([
                'name' => 'Personal de prueba '.$index,
                'role' => 'MESERO',
                'default_pay' => 100,
                'is_active' => true,
            ]);
        }

        $productsResponse = $this->get(route('products.index'));
        $productsResponse->assertOk()
            ->assertSee('data-cy="btn-view-cards"', false)
            ->assertSee('data-cy="stock-input-cards"', false);

        $session = NightSession::create([
            'session_date' => '2026-10-14',
            'day_name' => 'Miércoles',
            'status' => 'open',
        ]);

        $salesResponse = $this->get(route('sales.index', ['session_id' => $session->id]));
        $salesResponse->assertOk();

        $inventoryResponse = $this->get(route('barInventory.index', ['session_id' => $session->id]));
        $inventoryResponse->assertOk();

        $staffResponse = $this->get(route('staff.index', ['day' => 'todos']));
        $staffResponse->assertOk();

        foreach ([
            'products' => $productsResponse,
            'sales' => $salesResponse,
            'inventory' => $inventoryResponse,
            'staff' => $staffResponse,
        ] as $page => $response) {
            $this->assertLessThanOrEqual(
                250 * 1024,
                strlen($response->getContent()),
                "{$page} inicial excede 250 KB"
            );
        }
    }
}
