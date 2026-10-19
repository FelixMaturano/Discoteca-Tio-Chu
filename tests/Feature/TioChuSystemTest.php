<?php

namespace Tests\Feature;

use App\Models\BarSale;
use App\Models\BeerPayment;
use App\Models\NightSession;
use App\Models\Product;
use App\Models\Staff;
use App\Models\StaffAttendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TioChuSystemTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->user = User::where('email', 'DonLudo@gmail.com')->first();
    }

    public function test_unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get(route('dashboard'));
        $response->assertRedirect(route('login'));
    }

    public function test_can_login_with_don_ludo_credentials(): void
    {
        $response = $this->post('/login', [
            'email' => 'DonLudo@gmail.com',
            'password' => 'tiochu123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($this->user);
    }

    public function test_dashboard_loads_for_authenticated_user(): void
    {
        $response = $this->actingAs($this->user)->get(route('dashboard'));
        $response->assertStatus(200);
        $response->assertSee('TÍO CHU');
    }

    public function test_user_can_register_and_delete_beer_payment(): void
    {
        $session = NightSession::first();

        $response = $this->actingAs($this->user)->post(route('beerPayments.store'), [
            'night_session_id' => $session->id,
            'payment_method' => 'qr',
            'qr_provider' => 'YASTA',
            'amount' => 85.50,
            'notes' => 'Cerveza para staff',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('beer_payments', [
            'night_session_id' => $session->id,
            'payment_method' => 'qr',
            'qr_provider' => 'YASTA',
            'amount' => '85.50',
        ]);

        $payment = BeerPayment::where('night_session_id', $session->id)->first();
        $deleteResponse = $this->actingAs($this->user)->delete(route('beerPayments.destroy', $payment));
        $deleteResponse->assertSessionHas('success');
        $this->assertDatabaseMissing('beer_payments', ['id' => $payment->id]);
    }

    public function test_products_catalog_lists_seeded_drinks(): void
    {
        $response = $this->actingAs($this->user)->get(route('products.index'));
        $response->assertStatus(200);
        $response->assertSee('Singani Casa Real Negro');
        $response->assertSee('Fernet Branca');
        $response->assertSee('view-cards-container');
        $response->assertSee('CasaRealNegro.png');
    }

    public function test_product_can_update_image_path(): void
    {
        $product = Product::first();
        $response = $this->actingAs($this->user)->put(route('products.update', $product), [
            'name' => $product->name,
            'category' => $product->category,
            'unit' => $product->unit,
            'sale_price' => $product->sale_price,
            'cost_price' => $product->cost_price,
            'units_per_package' => $product->units_per_package,
            'stock_warehouse' => $product->stock_warehouse,
            'is_active' => 1,
            'image_path' => 'images/drinks/CasaRealNegro.png',
        ]);

        $response->assertRedirect(route('products.index'));
        $this->assertEquals('images/drinks/CasaRealNegro.png', $product->fresh()->image_path);
    }

    public function test_staff_module_lists_staff_members(): void
    {
        $response = $this->actingAs($this->user)->get(route('staff.index'));
        $response->assertStatus(200);
        $response->assertSee('KELLY');
        $response->assertSee('ARIEL');

        // Test all day tabs
        foreach (['viernes', 'sabado', 'domingo', 'todos'] as $day) {
            $r = $this->actingAs($this->user)->get(route('staff.index', ['day' => $day]));
            $r->assertStatus(200);
        }

        // Test extraordinary day (Jueves)
        NightSession::where('status', 'open')->update(['status' => 'closed']);
        $thursday = NightSession::create([
            'session_date' => '2026-10-15',
            'day_name' => 'Jueves',
            'status' => 'open',
            'pos_commission_rate' => 0.013,
        ]);
        $rThursday = $this->actingAs($this->user)->get(route('staff.index', ['session_id' => $thursday->id, 'day' => 'jueves']));
        $rThursday->assertStatus(200);
    }

    public function test_staff_varies_by_day_saturday_has_more_staff_than_friday_and_sunday(): void
    {
        NightSession::query()->delete();

        // 1. Crear Noche de Viernes
        $fridayDate = Carbon::parse('2026-10-02'); // Viernes
        $fridaySession = NightSession::create([
            'session_date' => $fridayDate->format('Y-m-d'),
            'day_name' => 'Viernes',
            'status' => 'open',
            'pos_commission_rate' => 0.05,
        ]);
        foreach (Staff::where('is_active', true)->get() as $member) {
            if ($member->worksOnDay('Viernes')) {
                StaffAttendance::create([
                    'night_session_id' => $fridaySession->id,
                    'staff_id' => $member->id,
                    'pay_amount' => $member->getPayForDay('Viernes'),
                    'is_paid' => false,
                ]);
            }
        }

        // 2. Crear Noche de Sábado
        $saturdayDate = Carbon::parse('2026-10-03'); // Sábado
        $saturdaySession = NightSession::create([
            'session_date' => $saturdayDate->format('Y-m-d'),
            'day_name' => 'Sábado',
            'status' => 'open',
            'pos_commission_rate' => 0.05,
        ]);
        foreach (Staff::where('is_active', true)->get() as $member) {
            if ($member->worksOnDay('Sábado')) {
                StaffAttendance::create([
                    'night_session_id' => $saturdaySession->id,
                    'staff_id' => $member->id,
                    'pay_amount' => $member->getPayForDay('Sábado'),
                    'is_paid' => false,
                ]);
            }
        }

        // 3. Crear Noche de Domingo
        $sundayDate = Carbon::parse('2026-10-04'); // Domingo
        $sundaySession = NightSession::create([
            'session_date' => $sundayDate->format('Y-m-d'),
            'day_name' => 'Domingo',
            'status' => 'open',
            'pos_commission_rate' => 0.05,
        ]);
        foreach (Staff::where('is_active', true)->get() as $member) {
            if ($member->worksOnDay('Domingo')) {
                StaffAttendance::create([
                    'night_session_id' => $sundaySession->id,
                    'staff_id' => $member->id,
                    'pay_amount' => $member->getPayForDay('Domingo'),
                    'is_paid' => false,
                ]);
            }
        }

        $fridayCount = $fridaySession->staffAttendances()->count();
        $saturdayCount = $saturdaySession->staffAttendances()->count();
        $sundayCount = $sundaySession->staffAttendances()->count();

        // El sábado tiene significativamente más personal que viernes y domingo (refuerzos de fin de semana)
        $this->assertGreaterThan($fridayCount, $saturdayCount);
        $this->assertGreaterThan($sundayCount, $saturdayCount);
        $this->assertGreaterThanOrEqual(20, $saturdayCount);
    }

    public function test_can_add_and_remove_extra_staff_member_for_night(): void
    {
        $session = NightSession::first();
        $staffMember = Staff::where('is_active', true)->first();

        // Asegurar que no esté
        StaffAttendance::where('night_session_id', $session->id)->where('staff_id', $staffMember->id)->delete();

        // Agregar personal extra
        $response = $this->actingAs($this->user)->post(route('closing.attendances.store'), [
            'night_session_id' => $session->id,
            'staff_id' => $staffMember->id,
            'pay_amount' => 120.00,
        ]);
        $response->assertSessionHas('success');

        $attendance = StaffAttendance::where('night_session_id', $session->id)->where('staff_id', $staffMember->id)->first();
        $this->assertNotNull($attendance);

        // Quitar de la noche
        $responseDelete = $this->actingAs($this->user)->delete(route('closing.attendances.destroy', $attendance));
        $responseDelete->assertSessionHas('success');
        $this->assertDatabaseMissing('staff_attendances', ['id' => $attendance->id]);
    }

    public function test_sales_module_displays_inventory_table(): void
    {
        $session = NightSession::first();
        $response = $this->actingAs($this->user)->get(route('sales.index', ['session_id' => $session->id]));
        $response->assertStatus(200);
        $response->assertSee('Barra Principal');
    }

    public function test_combos_deduct_included_mixers_and_only_charge_extras(): void
    {
        $session = NightSession::first();
        $singaniSale = BarSale::where('night_session_id', $session->id)
            ->whereIn('bar_name', ['Barra Principal', 'Barra Kelly (Principal)'])
            ->where('product_id', 1) // Singani Casa Real Negro (va con Ginger Ale id 23)
            ->first();

        $gingerSale = BarSale::where('night_session_id', $session->id)
            ->whereIn('bar_name', ['Barra Principal', 'Barra Kelly (Principal)'])
            ->where('product_id', 23) // Ginger Ale 2.0L
            ->first();

        // Vendemos 10 combos de Singani (1 paq de 6 + 4 unidades = 10) y consumimos 12 botellas de Ginger Ale (2 paq de 6 = 12)
        $response = $this->actingAs($this->user)->put(route('sales.updateBulk'), [
            'night_session_id' => $session->id,
            'sales' => [
                $singaniSale->id => [
                    'packages' => 1,
                    'units' => 4,
                    'saldo' => 0, // vendido: 10
                    'unit_price' => 240.00,
                ],
                $gingerSale->id => [
                    'packages' => 2,
                    'units' => 0,
                    'saldo' => 0, // consumido: 12
                    'unit_price' => 25.00,
                ],
            ],
        ]);

        $response->assertSessionHas('success');

        // Singani: 10 * 240 = 2400
        $this->assertDatabaseHas('bar_sales', [
            'id' => $singaniSale->id,
            'vendido' => 10,
            'subtotal' => 2400.00,
        ]);

        // Ginger Ale: 12 consumidas - 10 en combos = 2 extras * 25 = 50 Bs (NO 300 Bs)
        $this->assertDatabaseHas('bar_sales', [
            'id' => $gingerSale->id,
            'vendido' => 12,
            'subtotal' => 50.00,
        ]);
    }

    public function test_gin_deducts_two_tonic_waters_per_combo(): void
    {
        $session = NightSession::first();
        $ginSale = BarSale::where('night_session_id', $session->id)
            ->whereIn('bar_name', ['Barra Principal', 'Barra Kelly (Principal)'])
            ->where('product_id', 20) // Ganesha Gin (va con Agua Tónica id 26, ratio 2)
            ->first();

        $tonicaSale = BarSale::where('night_session_id', $session->id)
            ->whereIn('bar_name', ['Barra Principal', 'Barra Kelly (Principal)'])
            ->where('product_id', 26) // Agua Tónica 1.0L
            ->first();

        // 5 combos de Gin (0 paq + 5 unid = 5 botellas). Consumimos 13 tónicas (2 paq de 6 + 1 unid = 13).
        $response = $this->actingAs($this->user)->put(route('sales.updateBulk'), [
            'night_session_id' => $session->id,
            'sales' => [
                $ginSale->id => [
                    'packages' => 0,
                    'units' => 5,
                    'saldo' => 0, // vendido: 5 combos
                    'unit_price' => 300.00,
                ],
                $tonicaSale->id => [
                    'packages' => 2,
                    'units' => 1,
                    'saldo' => 0, // consumido: 13 tónicas
                    'unit_price' => 20.00,
                ],
            ],
        ]);

        $response->assertSessionHas('success');

        // Gin: 5 * 300 = 1500
        $this->assertDatabaseHas('bar_sales', [
            'id' => $ginSale->id,
            'vendido' => 5,
            'subtotal' => 1500.00,
        ]);

        // Agua Tónica: 13 consumidas - (5 * 2 = 10 incluidas) = 3 extras * 20 = 60 Bs
        $this->assertDatabaseHas('bar_sales', [
            'id' => $tonicaSale->id,
            'vendido' => 13,
            'subtotal' => 60.00,
        ]);
    }

    public function test_tienda_records_guardarropa_and_snacks(): void
    {
        $session = NightSession::first();

        $response = $this->actingAs($this->user)->put(route('sales.updateBulk'), [
            'night_session_id' => $session->id,
            'bar_name' => 'Tienda',
            'guardarropa_amount' => 150.00,
            'snacks_amount' => 85.00,
            'sales' => [],
        ]);

        $response->assertSessionHas('success');

        $this->assertDatabaseHas('cash_closings', [
            'night_session_id' => $session->id,
            'total_guardarropa' => 150.00,
            'total_snacks' => 85.00,
        ]);
    }

    public function test_can_register_invoice_and_calculates_commission(): void
    {
        $session = NightSession::first();

        $response = $this->actingAs($this->user)->post(route('invoices.store'), [
            'night_session_id' => $session->id,
            'correlative_num' => 1,
            'payment_method' => 'tarjeta',
            'amount' => 1000.00,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('invoices', [
            'night_session_id' => $session->id,
            'correlative_num' => 1,
            'payment_method' => 'tarjeta',
            'amount' => 1000.00,
            'commission_amount' => 13.00,
            'net_amount' => 987.00,
        ]);
    }

    public function test_can_register_qr_payment(): void
    {
        $session = NightSession::first();

        $response = $this->actingAs($this->user)->post(route('qrs.store'), [
            'night_session_id' => $session->id,
            'point_of_sale' => 'Tienda',
            'cobrante_name' => 'JUAN MOZO',
            'bank_app' => 'YASTA',
            'amount' => 250.00,
            'reference_code' => 'CMP-TEST-123',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('qr_payments', [
            'night_session_id' => $session->id,
            'point_of_sale' => 'Tienda',
            'operator_name' => 'JUAN MOZO',
            'bank_app' => 'YASTA',
            'amount' => 250.00,
        ]);
    }

    public function test_tienda_registers_combos_directly_with_cash_and_qr_split(): void
    {
        $session = NightSession::first();
        $singani = Product::where('name', 'like', '%Singani Casa Real%')->first() ?? Product::first();
        $singani->update(['stock_warehouse' => 10]);

        // Mesero pide 2 combos de 250 = 500 Bs, pagando 250 en efectivo y 250 en QR
        $response = $this->actingAs($this->user)->post(route('sales.storeSales.store'), [
            'night_session_id' => $session->id,
            'orders' => [
                [
                    'product_id' => $singani->id,
                    'quantity' => 2,
                    'unit_price' => 250.00,
                ],
            ],
            'cash_amount' => 250.00,
            'qr_amount' => 250.00,
            'card_amount' => 0.00,
            'cobrante_name' => 'ARIEL',
            'sync_qr' => 1,
            'bank_app' => 'YASTA',
        ]);

        $response->assertSessionHas('success');

        // Verificar registro en store_sales
        $this->assertDatabaseHas('store_sales', [
            'night_session_id' => $session->id,
            'product_id' => $singani->id,
            'quantity' => 2,
            'total_price' => 500.00,
            'cash_amount' => 250.00,
            'qr_amount' => 250.00,
            'cobrante_name' => 'ARIEL',
        ]);

        // Verificar que el QR se anotó automáticamente en la lista de QRs (Tienda)
        $this->assertDatabaseHas('qr_payments', [
            'night_session_id' => $session->id,
            'point_of_sale' => 'Tienda',
            'operator_name' => 'ARIEL',
            'amount' => 250.00,
        ]);

        // Verificar que consumió directamente de Bodega Central (stock_warehouse)
        $this->assertEquals(8, $singani->fresh()->stock_warehouse);

        // Verificar restricción crítica: Si Bodega Central está en 0, nunca baja a -1
        $singani->update(['stock_warehouse' => 0]);
        $singani->deductWarehouseStock(1);
        $this->assertEquals(0, $singani->fresh()->stock_warehouse);

        // Verificar que inventario de barras no tiene Tienda
        $responseInventory = $this->actingAs($this->user)->get(route('barInventory.index', ['session_id' => $session->id]));
        $responseInventory->assertStatus(200);
        $responseInventory->assertSee('Barra Principal');
        $responseInventory->assertSee('Barra Subterráneo');
        $responseInventory->assertDontSee('Unidades Sueltas');
    }

    public function test_bars_renamed_to_institutional_and_tienda_view_accessible(): void
    {
        $session = NightSession::first();

        // 1. Barra Principal view
        $responsePrincipal = $this->actingAs($this->user)->get(route('sales.index', [
            'session_id' => $session->id,
            'bar' => 'Barra Principal',
        ]));
        $responsePrincipal->assertStatus(200);
        $responsePrincipal->assertSee('Barra Principal');

        // 2. Barra Subterráneo view
        $responseSubte = $this->actingAs($this->user)->get(route('sales.index', [
            'session_id' => $session->id,
            'bar' => 'Barra Subterráneo',
        ]));
        $responseSubte->assertStatus(200);
        $responseSubte->assertSee('Barra Subterráneo');

        // 3. Tienda view
        $responseTienda = $this->actingAs($this->user)->get(route('sales.index', [
            'session_id' => $session->id,
            'bar' => 'Tienda',
        ]));
        $responseTienda->assertStatus(200);
        $responseTienda->assertSee('Tienda Oficial');
        $responseTienda->assertSee('Ventas Directas de Unidades Sueltas');

        // 4. Bodega Central has link to Tienda
        $responseBodega = $this->actingAs($this->user)->get(route('products.index'));
        $responseBodega->assertStatus(200);
        $responseBodega->assertSee('Ventas Tienda (Sueltas)');
    }

    public function test_simplified_tienda_sales_registers_directly_in_db_and_syncs_stock(): void
    {
        $session = NightSession::first();
        $singani = Product::where('name', 'like', '%Singani Casa Real%')->first() ?? Product::first();
        $singani->update(['stock_warehouse' => 15]);

        $ginger = Product::find(23); // Ginger Ale 2.0L (acompañamiento de Singani)
        $ginger->update(['stock_warehouse' => 20]);

        $sprite = Product::find(15); // Sprite 2.0L (mixer especial)
        $sprite->update(['stock_warehouse' => 10]);

        // Asegurar que existen BarSale para Tienda
        $tiendaSale = BarSale::firstOrCreate([
            'night_session_id' => $session->id,
            'product_id' => $singani->id,
            'bar_name' => 'Tienda',
        ], [
            'vendido' => 0,
            'unit_price' => 250.00,
            'subtotal' => 0.00,
        ]);

        $gingerSale = BarSale::firstOrCreate([
            'night_session_id' => $session->id,
            'product_id' => $ginger->id,
            'bar_name' => 'Tienda',
        ], [
            'vendido' => 0,
            'unit_price' => 25.00,
            'subtotal' => 0.00,
        ]);

        $spriteSale = BarSale::firstOrCreate([
            'night_session_id' => $session->id,
            'product_id' => $sprite->id,
            'bar_name' => 'Tienda',
        ], [
            'vendido' => 0,
            'unit_price' => 25.00,
            'subtotal' => 0.00,
        ]);

        // 1. Verificar que la vista de Tienda muestra la tabla simplificada con Acompañamiento y Especial
        $viewResponse = $this->actingAs($this->user)->get(route('sales.index', [
            'session_id' => $session->id,
            'bar' => 'Tienda',
        ]));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('Unidades Vendidas');
        $viewResponse->assertSee('Ganancia / Total (Bs.)');
        $viewResponse->assertSee('Acompañamiento');
        $viewResponse->assertSee('Especial');
        $viewResponse->assertDontSee('Ticket de Venta');
        $viewResponse->assertDontSee('Mesero / Quién retira o cobra');

        // Verificar que el orden respeta exactamente el de las barras y la última bebida es Agua de 600 ml
        $tiendaSalesList = $viewResponse->viewData('allBarSales');
        $this->assertStringContainsString('Agua Vital 600 ml', $tiendaSalesList->last()->product->name);

        // 2. Registrar venta de 4 combos de Singani a 250 Bs (incluye 4 Ginger Ale)
        $response = $this->actingAs($this->user)->put(route('sales.updateBulk'), [
            'night_session_id' => $session->id,
            'bar_name' => 'Tienda',
            'sales' => [
                $tiendaSale->id => [
                    'vendido' => 4,
                    'unit_price' => 250.00,
                ],
                $gingerSale->id => [
                    'vendido' => 4, // 4 incluidas en combo
                    'unit_price' => 25.00,
                ],
            ],
        ]);

        $response->assertSessionHas('success');

        // 3. Verificar persistencia en bar_sales y cálculo de ganancia/total:
        // Singani: 4 * 250 = 1000 Bs
        $this->assertDatabaseHas('bar_sales', [
            'id' => $tiendaSale->id,
            'bar_name' => 'Tienda',
            'vendido' => 4,
            'unit_price' => 250.00,
            'subtotal' => 1000.00,
        ]);

        // Ginger Ale: 4 consumidas - 4 incluidas en combo = 0 extras * 25 = 0 Bs (el combo cubre el mixer)
        $this->assertDatabaseHas('bar_sales', [
            'id' => $gingerSale->id,
            'bar_name' => 'Tienda',
            'vendido' => 4,
            'subtotal' => 0.00,
        ]);

        // 4. Verificar que se descontaron 4 unidades de Singani y 4 de Ginger Ale directamente de Bodega Central
        $this->assertEquals(11, $singani->fresh()->stock_warehouse);
        $this->assertEquals(16, $ginger->fresh()->stock_warehouse);

        // 5. Verificar que el Cierre de Caja suma los 1000 Bs. de Tienda
        $this->assertGreaterThanOrEqual(1000.00, $session->fresh()->cashClosing->total_bar_sales);

        // 6. Probar venta con mixer Especial (1 Singani con Sprite en lugar de Ginger)
        $responseSpecial = $this->actingAs($this->user)->put(route('sales.updateBulk'), [
            'night_session_id' => $session->id,
            'bar_name' => 'Tienda',
            'sales' => [
                $tiendaSale->id => [
                    'vendido' => 1,
                    'unit_price' => 250.00,
                    'selected_special_mixer' => json_encode([$sprite->id => 1]),
                ],
                $gingerSale->id => [
                    'vendido' => 0,
                    'unit_price' => 25.00,
                ],
                $spriteSale->id => [
                    'vendido' => 1,
                    'unit_price' => 25.00,
                ],
            ],
        ]);

        $responseSpecial->assertSessionHas('success');

        // Sprite debe tener subtotal 0 (cubierto por el combo especial)
        $this->assertDatabaseHas('bar_sales', [
            'id' => $spriteSale->id,
            'bar_name' => 'Tienda',
            'vendido' => 1,
            'subtotal' => 0.00,
        ]);

        // Bodega Central: Sprite se descontó en 1 (10 - 1 = 9)
        $this->assertEquals(9, $sprite->fresh()->stock_warehouse);
    }

    public function test_closing_and_expenses_module(): void
    {
        $session = NightSession::first();

        $response = $this->actingAs($this->user)->post(route('closing.expenses.store'), [
            'night_session_id' => $session->id,
            'category' => 'interno',
            'description' => 'Bolsas de hielo',
            'amount' => 60.00,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('expenses', [
            'night_session_id' => $session->id,
            'description' => 'Bolsas de hielo',
            'amount' => 60.00,
        ]);

        $responseView = $this->actingAs($this->user)->get(route('closing.index', ['session_id' => $session->id]));
        $responseView->assertStatus(200);
        $responseView->assertSee('Bolsas de hielo');
    }

    public function test_cannot_create_duplicate_session_date(): void
    {
        NightSession::where('status', 'open')->update(['status' => 'closed']);
        $existingDate = Carbon::parse(NightSession::first()->session_date)->format('Y-m-d');
        $response = $this->actingAs($this->user)->post(route('sessions.store'), [
            'session_date' => $existingDate,
            'pos_commission_rate' => 0.0130,
        ]);

        $response->assertSessionHasErrors('session_date');
    }

    public function test_can_create_session_for_today(): void
    {
        // Si ya hay sesión para hoy, la eliminamos para probar la creación y cerramos abiertas
        NightSession::where('status', 'open')->update(['status' => 'closed']);
        $today = now()->format('Y-m-d');
        NightSession::whereDate('session_date', $today)->delete();

        $response = $this->actingAs($this->user)->post(route('sessions.store'), [
            'session_date' => $today,
            'pos_commission_rate' => 0.0130,
        ]);

        $response->assertSessionHas('success');
    }

    public function test_session_calculates_day_name_automatically(): void
    {
        NightSession::where('status', 'open')->update(['status' => 'closed']);
        $futureDate = now()->addDays(5)->format('Y-m-d');
        $response = $this->actingAs($this->user)->post(route('sessions.store'), [
            'session_date' => $futureDate,
            'pos_commission_rate' => 0.0130,
        ]);

        $response->assertSessionHas('success');
        $session = NightSession::orderByDesc('id')->first();
        $this->assertNotNull($session);
        $this->assertNotEmpty($session->day_name);
    }

    public function test_can_delete_night_session(): void
    {
        $session = NightSession::first();
        $response = $this->actingAs($this->user)->delete(route('sessions.destroy', $session));

        $response->assertRedirect(route('sessions.index'));
        $this->assertDatabaseMissing('night_sessions', [
            'id' => $session->id,
        ]);
    }

    public function test_consolidated_summary_returns_html_and_json_for_selected_nights(): void
    {
        $sessions = NightSession::orderByDesc('session_date')->limit(3)->get();
        $sessionIds = $sessions->pluck('id')->toArray();

        // 1. Historial de Noches view shows checkboxes and consolidated button
        $responseIndex = $this->actingAs($this->user)->get(route('sessions.index'));
        $responseIndex->assertStatus(200);
        $responseIndex->assertSee('Seleccionar Fin de Semana');
        $responseIndex->assertSee('Resumen Consolidado');

        // 2. HTML printable view for selected sessions
        $responseHtml = $this->actingAs($this->user)->get(route('sessions.consolidatedSummary', [
            'session_ids' => $sessionIds,
        ]));
        $responseHtml->assertStatus(200);
        $responseHtml->assertSee('Resumen Consolidado');
        $responseHtml->assertSee('Total Ingresos');
        $responseHtml->assertSee('Ganancia Neta en Caja');
        $responseHtml->assertSee('Barra Principal');
        $responseHtml->assertSee('Barra Subterráneo');
        $responseHtml->assertSee('Tienda Oficial');

        // 3. JSON endpoint for modal / async rendering
        $responseJson = $this->actingAs($this->user)->getJson(route('sessions.consolidatedSummary', [
            'session_ids' => $sessionIds,
        ]));
        $responseJson->assertStatus(200);
        $responseJson->assertJsonStructure([
            'count',
            'date_range',
            'totals' => [
                'ventas',
                'ingresos',
                'efectivo',
                'qr_total',
                'card_gross',
                'card_net',
                'personal',
                'gastos',
                'egresos',
                'neto',
            ],
            'bars' => [
                'principal',
                'subte',
                'tienda',
            ],
            'nights',
        ]);
        $this->assertEquals(count($sessionIds), $responseJson->json('count'));
    }
}
