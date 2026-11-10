<?php

namespace Tests\Feature;

use App\Models\BarSale;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\NightSession;
use App\Models\Product;
use App\Models\QrPayment;
use App\Models\Staff;
use App\Models\StaffAttendance;
use App\Models\User;
use App\Services\NightSessionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class Sprint4FeaturesTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected NightSessionService $sessionService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->user = User::where('email', 'DonLudo@gmail.com')->first() ?? User::factory()->create();
        $this->sessionService = app(NightSessionService::class);
    }

    // =========================================================================
    // HISTORIA H7: BALANCE EXPRESS (TC-H7-01 .. TC-H7-04)
    // =========================================================================

    public function test_tc_h7_01_balance_express_calculates_correct_incomes_expenses_and_net_balance(): void
    {
        $this->actingAs($this->user);

        // Crear sesión limpia
        $session = NightSession::create([
            'session_date' => '2026-11-20',
            'day_name' => 'Viernes',
            'pos_commission_rate' => 0.05,
            'opening_cash' => 200.00,
            'status' => 'open',
        ]);

        $product = Product::first();

        // 1. Ingreso por venta de barra
        BarSale::create([
            'night_session_id' => $session->id,
            'bar_name' => 'Barra Principal',
            'product_id' => $product->id,
            'inicial' => 10,
            'ingreso' => 0,
            'total' => 10,
            'sobrante' => 5,
            'vendido' => 5,
            'unit_price' => 100.00,
            'subtotal' => 500.00,
        ]);

        // 2. Ingreso por QR YASTA
        QrPayment::create([
            'night_session_id' => $session->id,
            'point_of_sale' => 'Barra Principal',
            'bank_app' => 'YASTA',
            'operator_name' => 'Bartender Kelly',
            'amount' => 150.00,
            'reference_number' => 'QR-H7-01',
        ]);

        // 3. Ingreso por Tarjeta POS
        Invoice::create([
            'night_session_id' => $session->id,
            'correlative_num' => 101,
            'bar_name' => 'Barra Principal',
            'payment_method' => 'tarjeta',
            'amount' => 100.00,
            'commission_amount' => 5.00,
            'net_amount' => 95.00,
        ]);

        // 4. Egreso por gasto operativo
        Expense::create([
            'night_session_id' => $session->id,
            'category' => 'interno',
            'description' => 'Hielo y Limones',
            'amount' => 60.00,
        ]);

        // 5. Egreso por pago a personal
        $staff = Staff::first();
        StaffAttendance::create([
            'night_session_id' => $session->id,
            'staff_id' => $staff->id,
            'pay_amount' => 120.00,
            'is_paid' => true,
        ]);

        $response = $this->get(route('closing.index', ['session_id' => $session->id]));
        $response->assertStatus(200);

        // Validar atributos data-cy de H7 en la vista
        $response->assertSee('data-cy="balance-express-panel"', false);
        $response->assertSee('data-cy="balance-neto-caja"', false);
        $response->assertSee('data-cy="balance-total-ingresos"', false);
        $response->assertSee('data-cy="balance-total-egresos"', false);

        $closing = $this->sessionService->recalculateClosing($session);

        // Ventas = 500, QR = 150, Tarjeta = 100 -> Efectivo barras bruto = 250 -> neto de apertura = 50
        // Total Ingresos = 100 (Tarjeta bruto) + 150 (QR) + 50 (Efectivo) = 300
        // Total Egresos = 60 (Gasto) + 120 (Personal) = 180
        // Saldo Neto = 300 - 180 = 120
        $this->assertEquals(180.00, (float) $closing->total_expenses + (float) $closing->total_staff_paid);
        $this->assertEquals(120.00, (float) $closing->net_cash_closing);
    }

    public function test_tc_h7_02_balance_express_view_renders_key_components_without_errors(): void
    {
        $this->actingAs($this->user);
        $session = NightSession::first();

        $response = $this->get(route('closing.index', ['session_id' => $session->id]));
        $response->assertStatus(200);
        $response->assertSee('Balance Express');
        $response->assertSee('Plata que debe quedar en caja');
        $response->assertSee('Tarjetas (Neto)');
        $response->assertSee('QR Yasta');
        $response->assertSee('QR Yape');
        $response->assertSee('Efectivo en Mano');
    }

    public function test_tc_h7_03_balance_express_empty_session_handles_zero_activity(): void
    {
        $this->actingAs($this->user);

        $session = NightSession::create([
            'session_date' => '2026-11-21',
            'day_name' => 'Sábado',
            'pos_commission_rate' => 0.05,
            'opening_cash' => 0.00,
            'status' => 'open',
        ]);

        $response = $this->get(route('closing.index', ['session_id' => $session->id]));
        $response->assertStatus(200);

        $closing = $this->sessionService->recalculateClosing($session);
        $this->assertEquals(0.00, (float) $closing->total_bar_sales);
        $this->assertEquals(0.00, (float) $closing->total_expenses);
        $this->assertEquals(0.00, (float) $closing->total_staff_paid);
        $this->assertEquals(0.00, (float) $closing->net_cash_closing);
    }

    public function test_tc_h7_04_balance_express_reconciles_with_bar_sales_expenses_and_staff_payments(): void
    {
        $this->actingAs($this->user);

        $session = NightSession::create([
            'session_date' => '2026-11-22',
            'day_name' => 'Domingo',
            'pos_commission_rate' => 0.05,
            'opening_cash' => 100.00,
            'status' => 'open',
        ]);

        $product = Product::first();

        BarSale::create([
            'night_session_id' => $session->id,
            'bar_name' => 'Barra Principal',
            'product_id' => $product->id,
            'inicial' => 20,
            'ingreso' => 0,
            'total' => 20,
            'sobrante' => 10,
            'vendido' => 10,
            'unit_price' => 50.00,
            'subtotal' => 500.00,
        ]);

        Expense::create([
            'night_session_id' => $session->id,
            'category' => 'interno',
            'description' => 'Servilletas',
            'amount' => 50.00,
        ]);

        $closing = $this->sessionService->recalculateClosing($session);
        // Efectivo bruto = 500 - 0 - 0 = 500; menos apertura 100 = 400.
        // Ingresos = 400. Egresos = 50. Neto = 350.
        $this->assertEquals(400.00, (float) $closing->total_cash_invoices);
        $this->assertEquals(50.00, (float) $closing->total_expenses);
        $this->assertEquals(350.00, (float) $closing->net_cash_closing);
    }

    // =========================================================================
    // HISTORIA H8: ARQUEO DE EFECTIVO FÍSICO (TC-H8-01 .. TC-H8-04)
    // =========================================================================

    public function test_tc_h8_01_physical_cash_expected_calculation_from_sales_and_expenses(): void
    {
        $this->actingAs($this->user);

        $session = NightSession::create([
            'session_date' => '2026-11-23',
            'day_name' => 'Lunes',
            'pos_commission_rate' => 0.05,
            'opening_cash' => 150.00,
            'status' => 'open',
        ]);

        $product = Product::first();

        // Venta en efectivo 300
        BarSale::create([
            'night_session_id' => $session->id,
            'bar_name' => 'Barra Principal',
            'product_id' => $product->id,
            'inicial' => 10,
            'ingreso' => 0,
            'total' => 10,
            'sobrante' => 7,
            'vendido' => 3,
            'unit_price' => 100.00,
            'subtotal' => 300.00,
        ]);

        // Gasto en mano 50
        Expense::create([
            'night_session_id' => $session->id,
            'category' => 'interno',
            'description' => 'Taxi emergencia',
            'amount' => 50.00,
        ]);

        $closing = $this->sessionService->recalculateClosing($session);

        // pureCashIn = total_cash_invoices (300 - 150 = 150) + opening_cash (150) = 300
        // pureCashOut = total_staff_paid (0) + total_expenses (50) = 50
        // expectedCashInHand = 300 - 50 = 250.00
        $pureCashIn = (float) $closing->total_cash_invoices + (float) $session->opening_cash;
        $pureCashOut = (float) $closing->total_staff_paid + (float) $closing->total_expenses;
        $expectedCash = $pureCashIn - $pureCashOut;

        $this->assertEquals(250.00, $expectedCash);

        $response = $this->get(route('closing.index', ['session_id' => $session->id]));
        $response->assertStatus(200);
        $response->assertSee('data-cy="card-arqueo-fisico"', false);
        $response->assertSee('data-cy="expected-physical-cash"', false);
        $response->assertSee('250.00');
    }

    public function test_tc_h8_02_reported_cash_classification_plata_a_favor_faltante_and_caja_cuadrada(): void
    {
        $this->actingAs($this->user);

        $session = NightSession::create([
            'session_date' => '2026-11-24',
            'day_name' => 'Martes',
            'pos_commission_rate' => 0.05,
            'opening_cash' => 100.00,
            'status' => 'open',
        ]);

        $closing = $this->sessionService->recalculateClosing($session);

        // Caso 1: Plata a Favor (Reportado 150 > Esperado 100)
        $closing->update(['reported_cash' => 150.00]);
        $response = $this->get(route('closing.index', ['session_id' => $session->id]));
        $response->assertSee('data-cy="badge-plata-a-favor"', false);
        $response->assertSee('50.00');

        // Caso 2: Faltante (Reportado 70 < Esperado 100)
        $closing->update(['reported_cash' => 70.00]);
        $response = $this->get(route('closing.index', ['session_id' => $session->id]));
        $response->assertSee('data-cy="badge-faltante"', false);
        $response->assertSee('30.00');

        // Caso 3: Caja Cuadrada (Reportado 100 == Esperado 100)
        $closing->update(['reported_cash' => 100.00]);
        $response = $this->get(route('closing.index', ['session_id' => $session->id]));
        $response->assertSee('data-cy="badge-caja-cuadrada"', false);
        $response->assertSee('Cuadrada');
    }

    public function test_tc_h8_03_physical_cash_audit_zero_activity_session(): void
    {
        $this->actingAs($this->user);

        $session = NightSession::create([
            'session_date' => '2026-11-25',
            'day_name' => 'Miércoles',
            'pos_commission_rate' => 0.05,
            'opening_cash' => 0.00,
            'status' => 'open',
        ]);

        $closing = $this->sessionService->recalculateClosing($session);
        $closing->update(['reported_cash' => 0.00]);

        $response = $this->get(route('closing.index', ['session_id' => $session->id]));
        $response->assertStatus(200);
        $response->assertSee('data-cy="badge-caja-cuadrada"', false);
        $response->assertSee('0.00');
    }

    public function test_tc_h8_04_update_reported_cash_endpoint_persists_in_database_and_returns_json(): void
    {
        $this->actingAs($this->user);

        $session = NightSession::create([
            'session_date' => '2026-11-26',
            'day_name' => 'Jueves',
            'pos_commission_rate' => 0.05,
            'opening_cash' => 100.00,
            'status' => 'open',
        ]);

        // Enviar AJAX / JSON
        $response = $this->postJson(route('cash.reported.update'), [
            'night_session_id' => $session->id,
            'reported_cash' => 450.50,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'reported_cash' => 450.50,
            ]);

        $this->assertDatabaseHas('cash_closings', [
            'night_session_id' => $session->id,
            'reported_cash' => 450.50,
        ]);
    }

    // =========================================================================
    // HISTORIA H9: DASHBOARD EJECUTIVO (TC-H9-01 .. TC-H9-03)
    // =========================================================================

    public function test_tc_h9_01_dashboard_displays_real_session_kpis(): void
    {
        $this->actingAs($this->user);

        $session = NightSession::create([
            'session_date' => '2026-11-27',
            'day_name' => 'Viernes',
            'pos_commission_rate' => 0.05,
            'opening_cash' => 100.00,
            'status' => 'open',
        ]);

        $product = Product::first();

        // Venta en barras 800
        BarSale::create([
            'night_session_id' => $session->id,
            'bar_name' => 'Barra Principal',
            'product_id' => $product->id,
            'inicial' => 10,
            'ingreso' => 0,
            'total' => 10,
            'sobrante' => 2,
            'vendido' => 8,
            'unit_price' => 100.00,
            'subtotal' => 800.00,
        ]);

        // QR 250
        QrPayment::create([
            'night_session_id' => $session->id,
            'point_of_sale' => 'Barra Principal',
            'bank_app' => 'YASTA',
            'operator_name' => 'Bartender Kelly',
            'amount' => 250.00,
            'reference_number' => 'QR-H9-01',
        ]);

        // Tarjeta 200 bruto (190 neto)
        Invoice::create([
            'night_session_id' => $session->id,
            'correlative_num' => 102,
            'bar_name' => 'Barra Principal',
            'payment_method' => 'tarjeta',
            'amount' => 200.00,
            'commission_amount' => 10.00,
            'net_amount' => 190.00,
        ]);

        $response = $this->get(route('dashboard', ['session_id' => $session->id]));
        $response->assertStatus(200);

        // Los 4 KPIs con sus data-cy
        $response->assertSee('data-cy="kpi-venta-barras"', false);
        $response->assertSee('data-cy="kpi-tarjetas-neto"', false);
        $response->assertSee('data-cy="kpi-cobros-qr"', false);
        $response->assertSee('data-cy="kpi-efectivo-caja"', false);

        // Montos reales
        $response->assertSee('800.00'); // Venta en Barras
        $response->assertSee('190.00'); // Tarjeta Neto
        $response->assertSee('250.00'); // Cobros QR
    }

    public function test_tc_h9_02_dashboard_updates_kpis_when_new_transactions_occur(): void
    {
        $this->actingAs($this->user);

        $session = NightSession::create([
            'session_date' => '2026-11-28',
            'day_name' => 'Sábado',
            'pos_commission_rate' => 0.05,
            'opening_cash' => 50.00,
            'status' => 'open',
        ]);

        // Primer chequeo
        $response1 = $this->get(route('dashboard', ['session_id' => $session->id]));
        $response1->assertStatus(200);

        // Insertar pago QR
        QrPayment::create([
            'night_session_id' => $session->id,
            'point_of_sale' => 'Barra Principal',
            'bank_app' => 'YAPE',
            'operator_name' => 'Bartender Ariel',
            'amount' => 320.00,
            'reference_number' => 'QR-H9-02',
        ]);

        // Segundo chequeo
        $response2 = $this->get(route('dashboard', ['session_id' => $session->id]));
        $response2->assertStatus(200);
        $response2->assertSee('320.00');
    }

    public function test_tc_h9_03_dashboard_newly_opened_session_displays_initial_state(): void
    {
        $this->actingAs($this->user);

        $session = NightSession::create([
            'session_date' => '2026-11-29',
            'day_name' => 'Domingo',
            'pos_commission_rate' => 0.05,
            'opening_cash' => 0.00,
            'status' => 'open',
        ]);

        $response = $this->get(route('dashboard', ['session_id' => $session->id]));
        $response->assertStatus(200);
        $response->assertSee('0.00');
    }

    // =========================================================================
    // HISTORIA H18: CIERRE SEGURO DE NOCHE (TC-H18-01 .. TC-H18-05)
    // =========================================================================

    public function test_tc_h18_01_close_night_sets_session_status_to_closed(): void
    {
        $this->actingAs($this->user);

        $session = NightSession::create([
            'session_date' => '2026-11-30',
            'day_name' => 'Lunes',
            'pos_commission_rate' => 0.05,
            'opening_cash' => 100.00,
            'status' => 'open',
        ]);

        $response = $this->post(route('closing.close', $session));
        $response->assertRedirect();

        $session->refresh();
        $this->assertEquals('closed', $session->status);
        $this->assertFalse($session->isOpen());

        // Al visitar closing, se muestra el badge de noche cerrada
        $viewResponse = $this->get(route('closing.index', ['session_id' => $session->id]));
        $viewResponse->assertSee('data-cy="badge-night-closed-indicator"', false);
        $viewResponse->assertSee('data-cy="btn-reopen-night"', false);
        $viewResponse->assertSee('data-cy="badge-arqueo-locked"', false);
    }

    public function test_tc_h18_02_closed_night_blocks_modifications_across_endpoints(): void
    {
        $this->actingAs($this->user);

        $session = NightSession::create([
            'session_date' => '2026-12-01',
            'day_name' => 'Martes',
            'pos_commission_rate' => 0.05,
            'opening_cash' => 100.00,
            'status' => 'closed',
        ]);

        $staff = Staff::first();

        // 1. Bloqueo al agregar gasto
        $responseExpense = $this->post(route('closing.expenses.store'), [
            'night_session_id' => $session->id,
            'category' => 'interno',
            'description' => 'Intento de gasto prohibido',
            'amount' => 100.00,
        ]);
        $responseExpense->assertSessionHas('error');
        $this->assertDatabaseMissing('expenses', ['description' => 'Intento de gasto prohibido']);

        // 2. Bloqueo al registrar personal
        $responseStaff = $this->post(route('closing.attendances.store'), [
            'night_session_id' => $session->id,
            'staff_id' => $staff->id,
            'pay_amount' => 100.00,
        ]);
        $responseStaff->assertSessionHas('error');

        // 3. Bloqueo al actualizar reported-cash
        $responseCash = $this->postJson(route('cash.reported.update'), [
            'night_session_id' => $session->id,
            'reported_cash' => 500.00,
        ]);
        $responseCash->assertStatus(403);
    }

    public function test_tc_h18_03_reopen_night_restores_session_to_open_status(): void
    {
        $this->actingAs($this->user);

        $session = NightSession::create([
            'session_date' => '2026-12-02',
            'day_name' => 'Miércoles',
            'pos_commission_rate' => 0.05,
            'opening_cash' => 100.00,
            'status' => 'closed',
        ]);

        $response = $this->post(route('closing.reopen', $session));
        $response->assertRedirect();

        $session->refresh();
        $this->assertEquals('open', $session->status);
        $this->assertTrue($session->isOpen());

        // Ahora sí permite registrar gastos
        $responseExpense = $this->post(route('closing.expenses.store'), [
            'night_session_id' => $session->id,
            'category' => 'interno',
            'description' => 'Gasto autorizado tras reapertura',
            'amount' => 80.00,
        ]);
        $responseExpense->assertSessionHas('success');
        $this->assertDatabaseHas('expenses', ['description' => 'Gasto autorizado tras reapertura']);
    }

    public function test_tc_h18_04_guest_cannot_close_or_reopen_night(): void
    {
        $session = NightSession::create([
            'session_date' => '2026-12-03',
            'day_name' => 'Jueves',
            'pos_commission_rate' => 0.05,
            'opening_cash' => 100.00,
            'status' => 'open',
        ]);

        // Sin autenticación
        $responseClose = $this->post(route('closing.close', $session));
        $responseClose->assertRedirect(route('login'));

        $responseReopen = $this->post(route('closing.reopen', $session));
        $responseReopen->assertRedirect(route('login'));

        $session->refresh();
        $this->assertEquals('open', $session->status);
    }

    public function test_tc_h18_05_idempotent_close_night_preserves_state(): void
    {
        $this->actingAs($this->user);

        $session = NightSession::create([
            'session_date' => '2026-12-04',
            'day_name' => 'Viernes',
            'pos_commission_rate' => 0.05,
            'opening_cash' => 100.00,
            'status' => 'closed',
        ]);

        // Repetir cierre
        $response = $this->post(route('closing.close', $session));
        $response->assertRedirect();

        $session->refresh();
        $this->assertEquals('closed', $session->status);
    }

    // =========================================================================
    // HISTORIA H10: VISTA TÁCTIL BODEGA (TC-H10-01 .. TC-H10-04)
    // =========================================================================

    public function test_tc_h10_01_warehouse_view_renders_both_cards_and_table_containers(): void
    {
        $this->actingAs($this->user);

        $response = $this->get(route('products.index'));
        $response->assertStatus(200);

        // Ambas vistas presentes y switchers identificados
        $response->assertSee('data-cy="btn-view-cards"', false);
        $response->assertSee('data-cy="btn-view-table"', false);
        $response->assertSee('data-cy="view-cards-container"', false);
        $response->assertSee('data-cy="view-table-container"', false);
    }

    public function test_tc_h10_02_quick_stock_step_adjustment_persists_and_syncs_packages_and_units(): void
    {
        $this->actingAs($this->user);

        $product = Product::create([
            'name' => 'Fernet Branca 750ml Test',
            'category' => 'Ron',
            'sale_price' => 120.00,
            'cost_price' => 70.00,
            'unit' => 'botella',
            'units_per_package' => 12,
            'stock_warehouse' => 20,
            'is_active' => true,
        ]);

        // Ajuste con paso táctil +10 (delta = 10)
        $response = $this->patchJson(route('products.quickStock', $product), [
            'delta' => 10,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'stock_warehouse' => 30,
                'stock_packages' => 2, // 30 / 12 = 2 cajas
                'stock_units' => 6,    // 30 % 12 = 6 sueltas
            ]);

        $product->refresh();
        $this->assertEquals(30, $product->stock_warehouse);
        $this->assertEquals(2, $product->stock_packages);
        $this->assertEquals(6, $product->stock_units);

        // Ajuste con paso táctil -1
        $responseMinus = $this->patchJson(route('products.quickStock', $product), [
            'delta' => -1,
        ]);
        $responseMinus->assertStatus(200)
            ->assertJson([
                'stock_warehouse' => 29,
                'stock_packages' => 2,
                'stock_units' => 5,
            ]);

        $product->refresh();
        $this->assertEquals(29, $product->stock_warehouse);
    }

    public function test_tc_h10_03_warehouse_renders_large_catalog_exceeding_30_products(): void
    {
        $this->actingAs($this->user);

        // Insertar productos adicionales hasta superar los 30
        $currentCount = Product::count();
        $needed = max(0, 35 - $currentCount);

        for ($i = 1; $i <= $needed; $i++) {
            Product::create([
                'name' => 'Bebida Especial Vol '.$i,
                'category' => 'Singani',
                'sale_price' => 80.00 + $i,
                'cost_price' => 50.00,
                'unit' => 'botella',
                'units_per_package' => 6,
                'stock_warehouse' => 15,
                'is_active' => true,
            ]);
        }

        $this->assertGreaterThanOrEqual(30, Product::count());

        $response = $this->get(route('products.index'));
        $response->assertStatus(200);
        $response->assertSee('Bebida Especial Vol 1');
    }

    public function test_tc_h10_04_quick_stock_prevents_negative_inventory(): void
    {
        $this->actingAs($this->user);

        $product = Product::create([
            'name' => 'Vodka Stolichnaya Test',
            'category' => 'Otro',
            'sale_price' => 110.00,
            'cost_price' => 65.00,
            'unit' => 'botella',
            'units_per_package' => 6,
            'stock_warehouse' => 5,
            'is_active' => true,
        ]);

        // Intentar restar 10 cuando solo hay 5
        $response = $this->patchJson(route('products.quickStock', $product), [
            'delta' => -10,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('delta');

        $product->refresh();
        $this->assertEquals(5, $product->stock_warehouse);
    }
}
