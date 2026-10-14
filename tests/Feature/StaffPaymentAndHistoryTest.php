<?php

namespace Tests\Feature;

use App\Models\NightSession;
use App\Models\Staff;
use App\Models\StaffAttendance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffPaymentAndHistoryTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->user = User::where('email', 'DonLudo@gmail.com')->first();
    }

    public function test_payment_history_page_loads_and_displays_debts_ordered(): void
    {
        $session = NightSession::first();
        $attendances = StaffAttendance::where('night_session_id', $session->id)->get();
        $this->assertNotEmpty($attendances);

        // Marcamos el primero como pagado y dejamos el segundo como NO pagado
        $first = $attendances[0];
        $first->update(['is_paid' => true, 'pay_amount' => 100.00]);

        if (isset($attendances[1])) {
            $second = $attendances[1];
            $second->update(['is_paid' => false, 'pay_amount' => 120.00]);
        }

        $response = $this->actingAs($this->user)->get(route('paymentHistory.index', ['session_id' => $session->id]));
        $response->assertStatus(200);
        $response->assertSee('Historial de Pagos y Deudas del Personal');
        $response->assertSee('Pagado esa noche');
        $response->assertSee('Deuda: Bs. 0.00');

        if (isset($second)) {
            $response->assertSee('Se le debe:');
            $response->assertSee('120.00');
        }
    }

    public function test_batch_wage_update_modifies_wages_by_category_for_night(): void
    {
        $session = NightSession::first();

        // Enviamos petición para editar sueldos masivamente por categoría
        $response = $this->actingAs($this->user)->put(route('staffPayments.updateBatchWage'), [
            'night_session_id' => $session->id,
            'wages' => [
                'meseros' => 115.00,
                'limpieza' => 105.00,
                'seguridades' => 135.00,
                'barra' => 125.00,
            ]
        ]);

        $response->assertSessionHas('success');

        // Verificar que los de seguridad ahora tengan 135.00 en esta noche
        $securityAttendance = StaffAttendance::where('night_session_id', $session->id)
            ->whereHas('staff', function ($q) {
                $q->where('role', 'like', '%Seguridad%');
            })->first();

        $this->assertNotNull($securityAttendance);
        $this->assertEquals(135.00, (float)$securityAttendance->pay_amount);

        // Verificar que los bartenders ahora tengan 125.00 en esta noche
        $barAttendance = StaffAttendance::where('night_session_id', $session->id)
            ->whereHas('staff', function ($q) {
                $q->where('role', 'like', '%Bartender%');
            })->first();

        $this->assertNotNull($barAttendance);
        $this->assertEquals(125.00, (float)$barAttendance->pay_amount);
    }

    public function test_can_pay_single_unpaid_worker_from_history(): void
    {
        $session = NightSession::first();
        $att = StaffAttendance::where('night_session_id', $session->id)->first();
        $att->update(['is_paid' => false, 'pay_amount' => 120.00]);

        $response = $this->actingAs($this->user)->post(route('paymentHistory.paySingle', $att));
        $response->assertSessionHas('success');

        $att->refresh();
        $this->assertTrue((bool)$att->is_paid);
    }

    public function test_can_register_invoice_with_selected_bar(): void
    {
        $session = NightSession::first();

        $response = $this->actingAs($this->user)->post(route('invoices.store'), [
            'night_session_id' => $session->id,
            'correlative_num' => 50,
            'payment_method' => 'tarjeta',
            'bar_name' => 'Subterraneo',
            'amount' => 350.00,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('invoices', [
            'night_session_id' => $session->id,
            'correlative_num' => 50,
            'bar_name' => 'Subterráneo',
            'amount' => 350.00,
        ]);
    }

    public function test_invoice_automatically_increments_correlative(): void
    {
        $session = NightSession::first();
        $initialMax = (int) (\App\Models\Invoice::where('night_session_id', $session->id)->max('correlative_num') ?? 0);

        $response = $this->actingAs($this->user)->post(route('invoices.store'), [
            'night_session_id' => $session->id,
            'payment_method' => 'efectivo',
            'bar_name' => 'Principal',
            'amount' => 150.00,
        ]);

        $this->assertDatabaseHas('invoices', [
            'night_session_id' => $session->id,
            'correlative_num' => $initialMax + 1,
            'payment_method' => 'efectivo',
            'amount' => 150.00,
        ]);
    }

    public function test_can_delete_invoice_asynchronously(): void
    {
        $session = NightSession::first();
        $inv = \App\Models\Invoice::create([
            'night_session_id' => $session->id,
            'correlative_num' => 99,
            'payment_method' => 'tarjeta',
            'amount' => 100.00,
            'commission_rate' => 0.05,
            'commission_amount' => 5.00,
            'net_amount' => 95.00,
        ]);

        $response = $this->actingAs($this->user)->deleteJson(route('invoices.destroy', $inv));
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('invoices', [
            'id' => $inv->id,
        ]);
    }

    public function test_qr_payment_page_loads_with_cobrantes_select(): void
    {
        $session = NightSession::first();
        $response = $this->actingAs($this->user)->get(route('qrs.index', ['session_id' => $session->id]));

        $response->assertStatus(200);
        $response->assertSee('BARTENDERS');
        $response->assertSee('MESEROS');
        $response->assertSee('yasta.png');
        $response->assertSee('yape.png');
        $response->assertDontSee('(MOZO)');
        $response->assertDontSee('(Mozo)');
    }

    public function test_can_delete_qr_payment_asynchronously(): void
    {
        $session = NightSession::first();
        $qr = \App\Models\QrPayment::create([
            'night_session_id' => $session->id,
            'point_of_sale' => 'Barra Principal',
            'operator_name' => 'ALVARO',
            'bank_app' => 'YASTA',
            'amount' => 50.00,
            'is_confirmed' => true,
        ]);

        $response = $this->actingAs($this->user)->deleteJson(route('qrs.destroy', $qr));
        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('qr_payments', [
            'id' => $qr->id,
        ]);
    }
}
