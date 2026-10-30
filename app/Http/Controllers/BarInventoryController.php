<?php

namespace App\Http\Controllers;

use App\Models\BarSale;
use App\Models\NightSession;
use App\Models\Product;
use App\Models\ProductStockMovement;
use App\Services\NightSessionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
        $selectedBar = match ($rawBar) {
            'Barra Kelly (Principal)', 'Kelly', 'Principal' => 'Barra Principal',
            'Barra Ariel (Subte)', 'Ariel', 'Subte', 'Subterraneo', 'Subterráneo' => 'Barra Subterráneo',
            default => in_array($rawBar, ['Barra Principal', 'Barra Subterráneo', 'Tienda']) ? $rawBar : 'Barra Principal',
        };
        $availableBars = ['Barra Principal', 'Barra Subterráneo', 'Tienda'];

        $liquorSales = collect();
        $mixerSales = collect();
        $previousSession = null;

        if ($session) {
            $dateStr = Carbon::parse($session->session_date)->format('Y-m-d');
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

            $liquorSales = $allBarSales->filter(fn ($s) => $s->product && $s->product->category !== 'Mixers');
            $mixerSales = $allBarSales->filter(fn ($s) => $s->product && $s->product->category === 'Mixers');
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
        $validated = $request->validate([
            'night_session_id' => 'required|exists:night_sessions,id',
            'bar_name' => [
                'required',
                Rule::in([
                    'Barra Principal',
                    'Barra Subterráneo',
                    'Tienda',
                    'Barra Kelly (Principal)',
                    'Kelly',
                    'Principal',
                    'Barra Ariel (Subte)',
                    'Ariel',
                    'Subte',
                    'Subterraneo',
                    'Subterráneo',
                ]),
            ],
            'inventory' => 'required|array|min:1',
            'inventory.*.initial_packages' => 'nullable|integer|min:0',
            'inventory.*.initial_units' => 'nullable|integer|min:0',
            'inventory.*.added_packages' => 'nullable|integer|min:0',
            'inventory.*.added_units' => 'nullable|integer|min:0',
            'inventory.*.night_packages' => 'nullable|integer|min:0',
            'inventory.*.night_units' => 'nullable|integer|min:0',
        ]);

        $sessionId = (int) $validated['night_session_id'];
        $rawBar = $validated['bar_name'];
        $selectedBar = match ($rawBar) {
            'Barra Kelly (Principal)', 'Kelly', 'Principal' => 'Barra Principal',
            'Barra Ariel (Subte)', 'Ariel', 'Subte', 'Subterraneo', 'Subterráneo' => 'Barra Subterráneo',
            default => $rawBar,
        };

        $session = NightSession::findOrFail($sessionId);
        if (! $session->isOpen()) {
            return back()->with('error', 'La noche está cerrada. Para modificar aperturas o inventario, reabre la noche desde Cierre de Caja.');
        }

        DB::transaction(function () use ($sessionId, $selectedBar, $validated): void {
            $lockedSession = NightSession::query()->lockForUpdate()->findOrFail($sessionId);
            if (! $lockedSession->isOpen()) {
                throw ValidationException::withMessages([
                    'night_session_id' => 'La noche está cerrada y no admite despachos.',
                ]);
            }

            foreach ($validated['inventory'] as $id => $data) {
                $barSale = BarSale::query()
                    ->where('night_session_id', $sessionId)
                    ->where('bar_name', $selectedBar)
                    ->whereKey($id)
                    ->lockForUpdate()
                    ->first();

                if (! $barSale) {
                    throw ValidationException::withMessages([
                        "inventory.{$id}" => 'El producto no pertenece a esta noche y barra.',
                    ]);
                }

                $product = Product::query()->lockForUpdate()->findOrFail($barSale->product_id);
                $unitsPerPkg = max(1, (int) $product->units_per_package);

                $initPkg = (int) ($data['initial_packages'] ?? 0);
                $initUnits = (int) ($data['initial_units'] ?? 0);
                $addPkg = (int) ($data['added_packages'] ?? 0);
                $addUnits = (int) ($data['added_units'] ?? 0);
                $nightPkg = (int) ($data['night_packages'] ?? 0);
                $nightUnits = (int) ($data['night_units'] ?? 0);

                $previousDispatch = ((int) $barSale->added_packages * $unitsPerPkg)
                    + (int) $barSale->added_units;
                $requestedDispatch = ($addPkg * $unitsPerPkg) + $addUnits;
                $dispatchDifference = $requestedDispatch - $previousDispatch;
                $stockBefore = (int) $product->stock_warehouse;

                if ($dispatchDifference > $stockBefore) {
                    throw ValidationException::withMessages([
                        "inventory.{$id}.added_units" => 'El despacho supera el stock disponible en Bodega Central.',
                    ]);
                }

                if ($dispatchDifference !== 0) {
                    $stockAfter = $stockBefore - $dispatchDifference;
                    $isDispatch = $dispatchDifference > 0;

                    ProductStockMovement::create([
                        'product_id' => $product->id,
                        'movement_type' => 'transfer',
                        'source' => $isDispatch ? 'Bodega Central' : $selectedBar,
                        'destination' => $isDispatch ? $selectedBar : 'Bodega Central',
                        'quantity' => -$dispatchDifference,
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                    ]);

                    $product->stock_warehouse = $stockAfter;
                    $product->stock_packages = intdiv($stockAfter, $unitsPerPkg);
                    $product->stock_units = $stockAfter % $unitsPerPkg;
                    $product->save();
                }

                $apertPkg = $initPkg + $addPkg;
                $apertUnits = $initUnits + $addUnits;
                $totalNightPkg = $apertPkg + $nightPkg;
                $totalNightUnits = $apertUnits + $nightUnits;
                $totalNightBot = ($totalNightPkg * $unitsPerPkg) + $totalNightUnits;

                $barSale->initial_packages = $initPkg;
                $barSale->initial_units = $initUnits;
                $barSale->added_packages = $addPkg;
                $barSale->added_units = $addUnits;
                $barSale->night_packages = $nightPkg;
                $barSale->night_units = $nightUnits;
                $barSale->packages = $totalNightPkg;
                $barSale->units = $totalNightUnits;
                $barSale->total_initial = $totalNightBot;
                $barSale->vendido = max(0, $totalNightBot - (int) $barSale->saldo);

                if ($product->category !== 'Mixers') {
                    $barSale->subtotal = $barSale->vendido * (float) $barSale->unit_price;
                }

                $barSale->save();
            }
        });

        return redirect()->route('barInventory.index', [
            'session_id' => $sessionId,
            'bar' => $selectedBar,
        ])->with('success', 'Inventario y aperturas de '.$selectedBar.' guardados correctamente.');
    }

    /**
     * Sincronizar saldos de la noche anterior (en caso de que se haya reabierto o cambiado)
     */
    public function syncFromPreviousNight(Request $request)
    {
        $validated = $request->validate([
            'session_id' => 'required|exists:night_sessions,id',
            'bar_name' => [
                'required',
                Rule::in([
                    'Barra Principal', 'Barra Subterráneo', 'Tienda',
                    'Barra Kelly (Principal)', 'Kelly', 'Principal',
                    'Barra Ariel (Subte)', 'Ariel', 'Subte', 'Subterraneo', 'Subterráneo',
                ]),
            ],
        ]);

        $sessionId = (int) $validated['session_id'];
        $selectedBar = match ($validated['bar_name']) {
            'Barra Kelly (Principal)', 'Kelly', 'Principal' => 'Barra Principal',
            'Barra Ariel (Subte)', 'Ariel', 'Subte', 'Subterraneo', 'Subterráneo' => 'Barra Subterráneo',
            default => $validated['bar_name'],
        };
        $session = NightSession::findOrFail($sessionId);
        if (! $session->isOpen()) {
            return back()->with('error', 'La noche está cerrada. No se pueden sincronizar saldos en una noche cerrada.');
        }
        $dateStr = Carbon::parse($session->session_date)->format('Y-m-d');
        $previousSession = NightSession::where('id', '!=', $session->id)
            ->whereDate('session_date', '<', $dateStr)
            ->orderByDesc('session_date')
            ->first() ?? NightSession::where('id', '<', $session->id)->orderByDesc('id')->first();

        if (! $previousSession) {
            return back()->with('error', 'No se encontró una noche anterior para sincronizar saldos.');
        }

        $allBarSales = BarSale::with('product')
            ->where('night_session_id', $session->id)
            ->where('bar_name', $selectedBar)
            ->get();

        DB::transaction(function () use ($allBarSales, $previousSession, $selectedBar): void {
            foreach ($allBarSales as $barSale) {
                $prevSale = BarSale::where('night_session_id', $previousSession->id)
                    ->where('bar_name', $selectedBar)
                    ->where('product_id', $barSale->product_id)
                    ->first();

                $prevSaldo = $prevSale ? (int) $prevSale->saldo : 0;
                $unitsPerPkg = $barSale->product->units_per_package > 0 ? (int) $barSale->product->units_per_package : 1;

                $initPkg = intdiv($prevSaldo, $unitsPerPkg);
                $initUnits = $prevSaldo % $unitsPerPkg;

                $barSale->initial_packages = $initPkg;
                $barSale->initial_units = $initUnits;
                $barSale->packages = $initPkg + (int) $barSale->added_packages + (int) $barSale->night_packages;
                $barSale->units = $initUnits + (int) $barSale->added_units + (int) $barSale->night_units;
                $barSale->total_initial = ($barSale->packages * $unitsPerPkg) + $barSale->units;
                // El Saldo Cierre de la nueva noche se mantiene siempre en 0
                $barSale->saldo = 0;
                $barSale->vendido = max(0, $barSale->total_initial - (int) $barSale->saldo);
                if ($barSale->product->category !== 'Mixers') {
                    $barSale->subtotal = $barSale->vendido * (float) $barSale->unit_price;
                }
                $barSale->save();
            }
        });

        return back()->with('success', 'Saldos de la noche anterior sincronizados correctamente para '.$selectedBar.'.');
    }
}
