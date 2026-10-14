<?php

namespace App\Http\Controllers;

use App\Models\BarSale;
use App\Models\NightSession;
use App\Models\Product;
use App\Services\NightSessionService;
use Illuminate\Http\Request;

class BarInventoryController extends Controller
{
    protected NightSessionService $sessionService;

    public function __construct(NightSessionService $sessionService)
    {
        $this->sessionService = $sessionService;
    }

    public function index(Request $request)
    {
        $session = $this->sessionService->resolveSession($request->get('session_id'));
        $allSessions = NightSession::orderByDesc('session_date')->get();

        $rawBar = $request->get('bar', 'Barra Principal');
        $selectedBar = match($rawBar) {
            'Barra Kelly (Principal)', 'Kelly', 'Principal' => 'Barra Principal',
            'Barra Ariel (Subte)', 'Ariel', 'Subte', 'Subterraneo', 'Subterráneo' => 'Barra Subterráneo',
            default => in_array($rawBar, ['Barra Principal', 'Barra Subterráneo']) ? $rawBar : 'Barra Principal',
        };
        $availableBars = ['Barra Principal', 'Barra Subterráneo'];

        $liquorSales = collect();
        $mixerSales = collect();
        $previousSession = null;

        if ($session) {
            $dateStr = \Carbon\Carbon::parse($session->session_date)->format('Y-m-d');
            // Buscar la noche anterior real excluyendo la sesión actual
            $previousSession = NightSession::where('id', '!=', $session->id)
                ->whereDate('session_date', '<', $dateStr)
                ->orderByDesc('session_date')
                ->first() ?? NightSession::where('id', '<', $session->id)->orderByDesc('id')->first();

            $existingCount = BarSale::where('night_session_id', $session->id)
                ->where('bar_name', $selectedBar)
                ->count();

            if ($existingCount === 0) {
                $products = Product::where('is_active', true)->get();
                foreach ($products as $prod) {
                    BarSale::create([
                        'night_session_id' => $session->id,
                        'product_id' => $prod->id,
                        'bar_name' => $selectedBar,
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
                        'unit_price' => $prod->sale_price,
                        'subtotal' => 0,
                    ]);
                }
            }

            $allBarSales = BarSale::with('product')
                ->where('night_session_id', $session->id)
                ->where('bar_name', $selectedBar)
                ->get();

            $liquorSales = $allBarSales->filter(fn($s) => $s->product && $s->product->category !== 'Mixers');
            $mixerSales = $allBarSales->filter(fn($s) => $s->product && $s->product->category === 'Mixers');
        }

        return view('bar_inventory.index', compact(
            'session',
            'allSessions',
            'selectedBar',
            'availableBars',
            'liquorSales',
            'mixerSales',
            'previousSession'
        ));
    }

    public function updateBulk(Request $request)
    {
        $sessionId = $request->input('night_session_id');
        $session = NightSession::find($sessionId);
        if ($session && !$session->isOpen()) {
            return back()->with('error', 'La noche está cerrada. Para modificar aperturas o inventario, reabre la noche desde Cierre de Caja.');
        }

        $rows = $request->input('inventory', []);
        $rawBar = $request->input('bar_name', 'Barra Principal');
        $selectedBar = match($rawBar) {
            'Barra Kelly (Principal)', 'Kelly', 'Principal' => 'Barra Principal',
            'Barra Ariel (Subte)', 'Ariel', 'Subte', 'Subterraneo', 'Subterráneo' => 'Barra Subterráneo',
            default => in_array($rawBar, ['Barra Principal', 'Barra Subterráneo']) ? $rawBar : 'Barra Principal',
        };

        foreach ($rows as $id => $data) {
            $barSale = BarSale::with('product')->find($id);
            if (!$barSale || !$barSale->product) continue;

            $unitsPerPkg = $barSale->product->units_per_package > 0 ? (int)$barSale->product->units_per_package : 1;

            $initPkg   = (int)($data['initial_packages'] ?? 0);
            $initUnits = (int)($data['initial_units']   ?? 0);
            $addPkg    = (int)($data['added_packages']  ?? 0);
            $addUnits  = (int)($data['added_units']     ?? 0);
            $nightPkg  = (int)($data['night_packages']  ?? 0);
            $nightUnits= (int)($data['night_units']     ?? 0);

            // Total Apertura (inicio de noche)
            $apertPkg   = $initPkg + $addPkg;
            $apertUnits = $initUnits + $addUnits;

            // Total Noche (apertura + lo que se agregó durante el turno)
            $totalNightPkg   = $apertPkg + $nightPkg;
            $totalNightUnits = $apertUnits + $nightUnits;
            $totalNightBot   = ($totalNightPkg * $unitsPerPkg) + $totalNightUnits;

            // Guardar todos los campos de referencia y los totales de noche
            $barSale->initial_packages = $initPkg;
            $barSale->initial_units    = $initUnits;
            $barSale->added_packages   = $addPkg;
            $barSale->added_units      = $addUnits;
            $barSale->night_packages   = $nightPkg;
            $barSale->night_units      = $nightUnits;

            // packages/units = Total Noche → lo que Ventas por Barra usa como base
            $barSale->packages     = $totalNightPkg;
            $barSale->units        = $totalNightUnits;
            $barSale->total_initial = $totalNightBot;

            // Ajustar vendido sin sobreescribir el saldo si ya fue editado
            $barSale->vendido = max(0, $totalNightBot - (int)$barSale->saldo);
            if ($barSale->product->category !== 'Mixers') {
                $barSale->subtotal = $barSale->vendido * (float)$barSale->unit_price;
            }

            $barSale->save();
        }

        return redirect()->route('barInventory.index', [
            'session_id' => $sessionId,
            'bar' => $selectedBar,
        ])->with('success', 'Inventario y aperturas de ' . $selectedBar . ' guardados correctamente.');
    }

    /**
     * Sincronizar saldos de la noche anterior (en caso de que se haya reabierto o cambiado)
     */
    public function syncFromPreviousNight(Request $request)
    {
        $sessionId = $request->input('session_id');
        $selectedBar = $request->input('bar_name');

        $session = NightSession::findOrFail($sessionId);
        if (!$session->isOpen()) {
            return back()->with('error', 'La noche está cerrada. No se pueden sincronizar saldos en una noche cerrada.');
        }
        $dateStr = \Carbon\Carbon::parse($session->session_date)->format('Y-m-d');
        $previousSession = NightSession::where('id', '!=', $session->id)
            ->whereDate('session_date', '<', $dateStr)
            ->orderByDesc('session_date')
            ->first() ?? NightSession::where('id', '<', $session->id)->orderByDesc('id')->first();

        if (!$previousSession) {
            return back()->with('error', 'No se encontró una noche anterior para sincronizar saldos.');
        }

        $allBarSales = BarSale::with('product')
            ->where('night_session_id', $session->id)
            ->where('bar_name', $selectedBar)
            ->get();

        foreach ($allBarSales as $barSale) {
            $prevSale = BarSale::where('night_session_id', $previousSession->id)
                ->where('bar_name', $selectedBar)
                ->where('product_id', $barSale->product_id)
                ->first();

            $prevSaldo = $prevSale ? (int)$prevSale->saldo : 0;
            $unitsPerPkg = $barSale->product->units_per_package > 0 ? (int)$barSale->product->units_per_package : 1;

            $initPkg = intdiv($prevSaldo, $unitsPerPkg);
            $initUnits = $prevSaldo % $unitsPerPkg;

            $barSale->initial_packages = $initPkg;
            $barSale->initial_units = $initUnits;
            $barSale->packages = $initPkg + (int)$barSale->added_packages + (int)$barSale->night_packages;
            $barSale->units = $initUnits + (int)$barSale->added_units + (int)$barSale->night_units;
            $barSale->total_initial = ($barSale->packages * $unitsPerPkg) + $barSale->units;
            // El Saldo Cierre de la nueva noche se mantiene siempre en 0
            $barSale->saldo = 0;
            $barSale->vendido = max(0, $barSale->total_initial - (int)$barSale->saldo);
            if ($barSale->product->category !== 'Mixers') {
                $barSale->subtotal = $barSale->vendido * (float)$barSale->unit_price;
            }
            $barSale->save();
        }

        return back()->with('success', 'Saldos de la noche anterior sincronizados correctamente para ' . $selectedBar . '.');
    }
}
