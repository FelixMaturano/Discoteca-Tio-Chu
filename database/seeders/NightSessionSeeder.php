<?php

namespace Database\Seeders;

use App\Models\NightSession;
use App\Models\Product;
use App\Models\BarSale;
use App\Models\CashClosing;
use App\Models\Staff;
use App\Models\StaffAttendance;
use Illuminate\Database\Seeder;

class NightSessionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear una noche activa inicial para que el sistema esté listo
        $date = now()->format('Y-m-d');
        $session = NightSession::whereDate('session_date', $date)->first();
        if (!$session) {
            $session = NightSession::create([
                'session_date' => $date,
                'day_name' => 'Viernes',
                'status' => 'open',
                'pos_commission_rate' => 0.0130, // 1.3%
                'notes' => 'Noche de inauguración del sistema'
            ]);
        }

        // Inicializar resumen de cierre
        CashClosing::firstOrCreate(['night_session_id' => $session->id]);

        // Cargar asistencia de personal por defecto para la noche
        $staff = Staff::where('is_active', true)->get();
        foreach ($staff as $member) {
            StaffAttendance::firstOrCreate(
                [
                    'night_session_id' => $session->id,
                    'staff_id' => $member->id,
                ],
                [
                    'pay_amount' => $member->default_pay,
                    'is_paid' => false,
                ]
            );
        }

        // Inicializar productos en Barra Principal, Barra Subterráneo y Tienda
        $products = Product::where('is_active', true)->get();
        foreach (['Barra Principal', 'Barra Subterráneo', 'Tienda'] as $bar) {
            foreach ($products as $prod) {
                BarSale::firstOrCreate(
                    [
                        'night_session_id' => $session->id,
                        'product_id' => $prod->id,
                        'bar_name' => $bar,
                    ],
                    [
                        'packages' => 0,
                        'units' => 0,
                        'total_initial' => 0,
                        'saldo' => 0,
                        'vendido' => 0,
                        'unit_price' => $prod->sale_price,
                        'subtotal' => 0,
                    ]
                );
            }
        }
    }
}
