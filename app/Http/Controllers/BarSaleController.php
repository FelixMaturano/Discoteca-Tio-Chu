<?php

namespace App\Http\Controllers;

use App\Models\BarSale;
use App\Models\CashClosing;
use App\Models\CategoryMixerOption;
use App\Models\NightSession;
use App\Models\Product;
use App\Services\NightSessionService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class BarSaleController extends Controller
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
            'Tienda' => 'Tienda',
            default => $rawBar,
        };
        $availableBars = ['Barra Principal', 'Barra Subterráneo', 'Tienda'];

        $liquorSales = collect();
        $mixerSales = collect();
        $totalLiquorCombos = 0;
        $subtotalLiquors = 0;
        $totalMixerConsumed = 0;
        $totalMixerExtras = 0;
        $subtotalMixers = 0;
        $grandTotalBar = 0;
        $closing = null;
        $mapping = Product::getMixerMapping();

        $specialMixerOptions = collect();
        $categories = [];
        $allMixerProducts = collect();
        $storeSales = collect();
        $totalStoreSales = 0;
        $allProducts = collect();

        if ($session) {
            $closing = CashClosing::firstOrCreate(['night_session_id' => $session->id]);

            // Pagos QR y Facturas/Tarjetas vinculados al punto de venta actual
            if ($selectedBar === 'Tienda') {
                $qrPosNames = ['Tienda', 'tienda'];
                $invoiceBarNames = ['Tienda', 'tienda'];
            } elseif (str_contains($selectedBar, 'Subte') || str_contains($selectedBar, 'Ariel') || str_contains($selectedBar, 'Subterráneo') || str_contains($selectedBar, 'Subterraneo')) {
                $qrPosNames = ['Barra Subterráneo', 'Subte', 'Barra Subte', 'Subterráneo', 'Subterraneo', 'Barra Ariel (Subte)', 'Ariel'];
                $invoiceBarNames = ['Barra Subterráneo', 'Subterráneo', 'Subterraneo', 'Subte', 'Barra Ariel (Subte)', 'Ariel'];
            } else {
                // Barra Principal
                $qrPosNames = ['Barra Principal', 'Principal', 'Barra Kelly (Principal)', 'Kelly'];
                $invoiceBarNames = ['Barra Principal', 'Principal', 'Barra Kelly (Principal)', 'Kelly'];
            }

            $barQrPayments = $session->qrPayments()
                ->whereIn('point_of_sale', $qrPosNames)
                ->orderByDesc('id')
                ->get();

            $barTotalQrYasta = (float) $barQrPayments->where('bank_app', 'YASTA')->sum('amount');
            $barTotalQrYape = (float) $barQrPayments->where('bank_app', 'YAPE')->sum('amount');
            $barTotalQr = (float) $barQrPayments->sum('amount');

            $barInvoices = $session->invoices()
                ->whereIn('bar_name', $invoiceBarNames)
                ->orderBy('correlative_num')
                ->get();

            $barCardInvoices = $barInvoices->where('payment_method', 'tarjeta');
            $barTotalCard = (float) $barCardInvoices->sum('amount');
            $barTotalCardCommission = (float) $barCardInvoices->sum('commission_amount');
            $barTotalCardNet = (float) $barCardInvoices->sum('net_amount');

            // Barras tradicionales (Principal y Subte): Apertura, Unidades, Saldo y deducción de sodas
            if ($session->isOpen()) {
                BarSale::where('night_session_id', $session->id)
                    ->whereHas('product')
                    ->with('product')
                    ->get()
                    ->each(function (BarSale $barSale): void {
                        $barSale->unit_price = $barSale->product->sale_price;
                        if ($barSale->product->category !== 'Mixers') {
                            $barSale->subtotal = (int) $barSale->vendido * (float) $barSale->product->sale_price;
                        }
                        $barSale->save();
                    });
            }

            $existingCount = BarSale::where('night_session_id', $session->id)
                ->where('bar_name', $selectedBar)
                ->count();

            if ($existingCount === 0) {
                $previousSession = NightSession::where('session_date', '<', $session->session_date)
                    ->orderByDesc('session_date')
                    ->first() ?? NightSession::where('id', '<', $session->id)->orderByDesc('id')->first();

                $products = Product::where('is_active', true)->get();
                foreach ($products as $prod) {
                    BarSale::firstOrCreate(
                        [
                            'night_session_id' => $session->id,
                            'product_id' => $prod->id,
                            'bar_name' => $selectedBar,
                        ],
                        [
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
                        ]
                    );
                }
            }

            if ($selectedBar === 'Tienda') {
                // Asegurar que todos los productos activos existan para Tienda
                $existingProductIds = BarSale::where('night_session_id', $session->id)
                    ->where('bar_name', 'Tienda')
                    ->pluck('product_id')
                    ->toArray();

                $missingProducts = Product::where('is_active', true)
                    ->whereNotIn('id', $existingProductIds)
                    ->get();

                foreach ($missingProducts as $prod) {
                    BarSale::create([
                        'night_session_id' => $session->id,
                        'product_id' => $prod->id,
                        'bar_name' => 'Tienda',
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

            if ($selectedBar === 'Tienda') {
                $allBarSales = $allBarSales->sort(function ($a, $b) {
                    $isMixerA = $a->product?->category === 'Mixers' ? 1 : 0;
                    $isMixerB = $b->product?->category === 'Mixers' ? 1 : 0;
                    if ($isMixerA !== $isMixerB) {
                        return $isMixerA <=> $isMixerB;
                    }

                    return ($a->product?->id ?? $a->product_id) <=> ($b->product?->id ?? $b->product_id);
                })->values();

                // Calcular mixers incluidos en combos para Tienda (igual que en las otras barras)
                $combosPerMixer = [];
                $liquorsTienda = $allBarSales->filter(fn ($s) => $s->product && $s->product->category !== 'Mixers');

                foreach ($liquorsTienda as $liq) {
                    $specialMixers = [];
                    if (! empty($liq->selected_special_mixer)) {
                        $decoded = is_string($liq->selected_special_mixer) ? json_decode($liq->selected_special_mixer, true) : $liq->selected_special_mixer;
                        if (is_array($decoded)) {
                            $specialMixers = $decoded;
                        }
                    }

                    $vendido = (int) $liq->vendido;
                    $specialCount = 0;

                    foreach ($specialMixers as $specialMixerId => $qty) {
                        $qty = (int) $qty;
                        if ($qty > 0 && $specialMixerId) {
                            $specialMixerId = (int) $specialMixerId;
                            $combosPerMixer[$specialMixerId] = ($combosPerMixer[$specialMixerId] ?? 0) + $qty;
                            $specialCount += $qty;
                        }
                    }

                    $mixerInfo = $mapping[$liq->product_id] ?? null;
                    if ($mixerInfo) {
                        $defaultMixerId = (int) $mixerInfo['mixer_id'];
                        $ratio = $mixerInfo['ratio'] ?? 1;
                        $remainingCombos = max(0, $vendido - $specialCount);
                        $combosPerMixer[$defaultMixerId] = ($combosPerMixer[$defaultMixerId] ?? 0) + ($remainingCombos * $ratio);
                    }
                }

                foreach ($allBarSales as $sale) {
                    if ($sale->product && $sale->product->category === 'Mixers') {
                        $included = $combosPerMixer[$sale->product_id] ?? 0;
                        $sale->included_in_combos = $included;
                        if ((int) $sale->vendido < $included) {
                            $sale->vendido = $included;
                        }
                        $extras = max(0, (int) $sale->vendido - $included);
                        $sale->subtotal = $extras * (float) $sale->unit_price;
                    } else {
                        $sale->subtotal = (int) $sale->vendido * (float) $sale->unit_price;
                    }
                }

                $totalTiendaUnits = (int) $allBarSales->sum('vendido');
                $grandTotalBar = (float) $allBarSales->sum('subtotal') + (float) ($session->storeSales()->sum('total_price'));
            } else {
                $totalTiendaUnits = 0;
                // Calcular cuántos mixers se cubrieron por los combos vendidos de licores (con deducción de especiales)
                $combosPerMixer = [];
                foreach ($liquorSales as $liq) {
                    $specialMixers = [];
                    if (! empty($liq->selected_special_mixer)) {
                        $decoded = is_string($liq->selected_special_mixer) ? json_decode($liq->selected_special_mixer, true) : $liq->selected_special_mixer;
                        if (is_array($decoded)) {
                            $specialMixers = $decoded;
                        }
                    }

                    $vendido = (int) $liq->vendido;
                    $specialCount = 0;

                    foreach ($specialMixers as $specialMixerId => $qty) {
                        $qty = (int) $qty;
                        if ($qty > 0 && $specialMixerId) {
                            $specialMixerId = (int) $specialMixerId;
                            $combosPerMixer[$specialMixerId] = ($combosPerMixer[$specialMixerId] ?? 0) + $qty;
                            $specialCount += $qty;
                        }
                    }

                    $mixerInfo = $mapping[$liq->product_id] ?? null;
                    if ($mixerInfo) {
                        $defaultMixerId = (int) $mixerInfo['mixer_id'];
                        $ratio = $mixerInfo['ratio'] ?? 1;
                        $remainingCombos = max(0, $vendido - $specialCount);
                        $combosPerMixer[$defaultMixerId] = ($combosPerMixer[$defaultMixerId] ?? 0) + ($remainingCombos * $ratio);
                    }
                }

                // Calcular extras para cada mixer
                foreach ($mixerSales as $mix) {
                    $mix->included_in_combos = $combosPerMixer[$mix->product_id] ?? 0;
                    $mix->extras = max(0, (int) $mix->vendido - $mix->included_in_combos);
                    $mix->subtotal = $mix->extras * (float) $mix->unit_price;
                }

                $totalLiquorCombos = $liquorSales->sum('vendido');
                $subtotalLiquors = $liquorSales->sum('subtotal');
                $totalMixerConsumed = $mixerSales->sum('vendido');
                $totalMixerExtras = $mixerSales->sum('extras');
                $subtotalMixers = $mixerSales->sum('subtotal');
                $grandTotalBar = $subtotalLiquors + $subtotalMixers;
            }

            // Obtener mixers especiales y categorías de tragos
            $specialMixerOptions = CategoryMixerOption::all()->groupBy('category');
            $categories = Product::getDrinkSubcategories();
            $allMixerProducts = Product::where('category', 'Mixers')->where('is_active', true)->get();
            $storeSales = $session->storeSales()->with('product')->orderByDesc('id')->get();
            $totalStoreSales = (float) $storeSales->sum('total_price');
            $allProducts = Product::where('is_active', true)->orderBy('name')->get();

            $barCashRemaining = max(0, $grandTotalBar - $barTotalQr - $barTotalCardNet);
        } else {
            $totalTiendaUnits = 0;
            $allBarSales = collect();
            $barQrPayments = collect();
            $barTotalQr = 0;
            $barTotalQrYasta = 0;
            $barTotalQrYape = 0;
            $barInvoices = collect();
            $barCardInvoices = collect();
            $barTotalCard = 0;
            $barTotalCardCommission = 0;
            $barTotalCardNet = 0;
            $barCashRemaining = 0;
            $storeSales = collect();
            $totalStoreSales = 0;
            $allProducts = collect();
        }

        return view('sales.index', compact(
            'session',
            'allSessions',
            'selectedBar',
            'availableBars',
            'liquorSales',
            'mixerSales',
            'allBarSales',
            'totalTiendaUnits',
            'mapping',
            'totalLiquorCombos',
            'subtotalLiquors',
            'totalMixerConsumed',
            'totalMixerExtras',
            'subtotalMixers',
            'grandTotalBar',
            'closing',
            'specialMixerOptions',
            'categories',
            'allMixerProducts',
            'barQrPayments',
            'barTotalQr',
            'barTotalQrYasta',
            'barTotalQrYape',
            'barInvoices',
            'barCardInvoices',
            'barTotalCard',
            'barTotalCardCommission',
            'barTotalCardNet',
            'barCashRemaining',
            'storeSales',
            'totalStoreSales',
            'allProducts'
        ));

    }

    public function storeSpecialMixer(Request $request)
    {
        $request->validate([
            'category' => 'required|string',
            'mixer_name' => 'required|string',
            'product_id' => 'nullable|exists:products,id',
        ]);

        CategoryMixerOption::firstOrCreate([
            'category' => $request->category,
            'mixer_name' => trim($request->mixer_name),
        ], [
            'product_id' => $request->product_id,
        ]);

        return back()->with('success', 'Variante especial añadida para '.$request->category);
    }

    public function updateBulk(Request $request)
    {
        $sessionId = $request->input('night_session_id');
        $session = NightSession::find($sessionId);
        if ($session && ! $session->isOpen()) {
            return back()->with('error', 'La noche está cerrada. Para modificar ventas o combos, reabre la noche desde Cierre de Caja.');
        }

        $rows = $request->input('sales', []);
        $mapping = Product::getMixerMapping();
        $rawBar = $request->input('bar_name', 'Barra Principal');
        $barName = match ($rawBar) {
            'Barra Kelly (Principal)', 'Kelly', 'Principal' => 'Barra Principal',
            'Barra Ariel (Subte)', 'Ariel', 'Subte', 'Subterraneo', 'Subterráneo' => 'Barra Subterráneo',
            'Tienda' => 'Tienda',
            default => $rawBar,
        };

        if ($barName === 'Tienda') {
            $updatedMixers = [];
            $combosPerMixer = [];

            // 1ra pasada: Licores
            foreach ($rows as $id => $data) {
                $barSale = BarSale::with('product')->find($id);
                if (! $barSale) {
                    continue;
                }

                if (isset($data['selected_special_mixer'])) {
                    $specialVal = $data['selected_special_mixer'];
                    if (is_string($specialVal) && (str_starts_with($specialVal, '{') || str_starts_with($specialVal, '['))) {
                        $barSale->selected_special_mixer = $specialVal;
                    } elseif (is_array($specialVal)) {
                        $barSale->selected_special_mixer = json_encode($specialVal);
                    } else {
                        $barSale->selected_special_mixer = $specialVal;
                    }
                }

                $vendido = max(0, (int) ($data['vendido'] ?? 0));
                $unitPrice = isset($data['unit_price']) && is_numeric($data['unit_price'])
                    ? (float) $data['unit_price']
                    : (float) ($barSale->unit_price ?? $barSale->product?->sale_price ?? 0);

                if ($barSale->product && $barSale->product->category === 'Mixers') {
                    $updatedMixers[$id] = [
                        'sale' => $barSale,
                        'vendido' => $vendido,
                        'unitPrice' => $unitPrice,
                    ];
                } else {
                    $subtotal = $vendido * $unitPrice;
                    $barSale->packages = 0;
                    $barSale->units = 0;
                    $barSale->total_initial = $vendido;
                    $barSale->saldo = 0;
                    $barSale->vendido = $vendido;
                    $barSale->unit_price = $unitPrice;
                    $barSale->subtotal = $subtotal;
                    $this->syncProductStock($barSale);
                    $barSale->save();

                    // Acumular mixers incluidos en combos (con especiales)
                    $specialMixers = [];
                    if (! empty($barSale->selected_special_mixer)) {
                        $decoded = is_string($barSale->selected_special_mixer)
                            ? json_decode($barSale->selected_special_mixer, true)
                            : $barSale->selected_special_mixer;
                        if (is_array($decoded)) {
                            $specialMixers = $decoded;
                        }
                    }

                    $specialCount = 0;
                    foreach ($specialMixers as $specialMixerId => $qty) {
                        $qty = (int) $qty;
                        if ($qty > 0 && $specialMixerId) {
                            $specialMixerId = (int) $specialMixerId;
                            $combosPerMixer[$specialMixerId] = ($combosPerMixer[$specialMixerId] ?? 0) + $qty;
                            $specialCount += $qty;
                        }
                    }

                    $mixerInfo = $mapping[$barSale->product_id] ?? null;
                    if ($mixerInfo) {
                        $defaultMixerId = (int) $mixerInfo['mixer_id'];
                        $ratio = $mixerInfo['ratio'] ?? 1;
                        $remainingCombos = max(0, $vendido - $specialCount);
                        $combosPerMixer[$defaultMixerId] = ($combosPerMixer[$defaultMixerId] ?? 0) + ($remainingCombos * $ratio);
                    }
                }
            }

            // 2da pasada: Mixers en Tienda
            foreach ($updatedMixers as $id => $item) {
                $barSale = $item['sale'];
                $userVendido = $item['vendido'];
                $unitPrice = $item['unitPrice'];

                $includedInCombos = $combosPerMixer[$barSale->product_id] ?? 0;
                $totalSoldOrConsumed = max($userVendido, $includedInCombos);
                $extras = max(0, $totalSoldOrConsumed - $includedInCombos);
                $subtotal = $extras * $unitPrice;

                $barSale->packages = 0;
                $barSale->units = 0;
                $barSale->total_initial = $totalSoldOrConsumed;
                $barSale->saldo = 0;
                $barSale->vendido = $totalSoldOrConsumed;
                $barSale->unit_price = $unitPrice;
                $barSale->subtotal = $subtotal;
                $this->syncProductStock($barSale);
                $barSale->save();
            }

            // Sincronizar mixers que no vinieron explícitamente en $rows pero tienen combos incluidos
            $processedMixerIds = collect($updatedMixers)->pluck('sale.product_id')->toArray();
            foreach ($combosPerMixer as $mixerProdId => $qtyIncluded) {
                if ($qtyIncluded > 0 && ! in_array($mixerProdId, $processedMixerIds)) {
                    $mixerSale = BarSale::where('night_session_id', $sessionId)
                        ->where('bar_name', 'Tienda')
                        ->where('product_id', $mixerProdId)
                        ->first();
                    if ($mixerSale) {
                        $mixerSale->vendido = max((int) $mixerSale->vendido, $qtyIncluded);
                        $mixerSale->subtotal = max(0, (int) $mixerSale->vendido - $qtyIncluded) * (float) $mixerSale->unit_price;
                        $this->syncProductStock($mixerSale);
                        $mixerSale->save();
                    }
                }
            }
        } else {
            // 1ra pasada: Actualizar inventario de licores y calcular subtotal de combos
            $updatedSales = [];
            $combosPerMixer = [];

            foreach ($rows as $id => $data) {
                $barSale = BarSale::with('product')->find($id);
                if (! $barSale) {
                    continue;
                }

                if (isset($data['selected_special_mixer'])) {
                    $specialVal = $data['selected_special_mixer'];
                    if (is_string($specialVal) && (str_starts_with($specialVal, '{') || str_starts_with($specialVal, '['))) {
                        $barSale->selected_special_mixer = $specialVal;
                    } elseif (is_array($specialVal)) {
                        $barSale->selected_special_mixer = json_encode($specialVal);
                    } else {
                        $barSale->selected_special_mixer = $specialVal;
                    }
                }

                $packages = (int) ($data['packages'] ?? 0);
                $units = (int) ($data['units'] ?? 0);
                $saldo = (int) ($data['saldo'] ?? 0);
                $unitPrice = (float) ($data['unit_price'] ?? $barSale->unit_price);

                $unitsPerPkg = $barSale->product ? (int) ($barSale->product->units_per_package ?? 1) : 1;
                if ($unitsPerPkg < 1) {
                    $unitsPerPkg = 1;
                }

                // Recalcular total inicial y vendido desde packages/units/saldo
                $totalInitial = ($packages * $unitsPerPkg) + $units;
                $vendido = max(0, $totalInitial - $saldo);

                $barSale->packages = $packages;
                $barSale->units = $units;
                $barSale->total_initial = $totalInitial;
                $barSale->saldo = $saldo;
                $barSale->vendido = $vendido;
                $barSale->unit_price = $unitPrice;
                $this->syncProductStock($barSale);

                if ($barSale->product && $barSale->product->category !== 'Mixers') {
                    $barSale->subtotal = $vendido * $unitPrice;
                    $barSale->save();

                    // Acumular mixers incluidos en combos (con especiales)
                    $specialMixers = [];
                    if (! empty($barSale->selected_special_mixer)) {
                        $decoded = is_string($barSale->selected_special_mixer)
                            ? json_decode($barSale->selected_special_mixer, true)
                            : $barSale->selected_special_mixer;
                        if (is_array($decoded)) {
                            $specialMixers = $decoded;
                        }
                    }

                    $specialCount = 0;
                    foreach ($specialMixers as $specialMixerId => $qty) {
                        $qty = (int) $qty;
                        if ($qty > 0 && $specialMixerId) {
                            $specialMixerId = (int) $specialMixerId;
                            $combosPerMixer[$specialMixerId] = ($combosPerMixer[$specialMixerId] ?? 0) + $qty;
                            $specialCount += $qty;
                        }
                    }

                    $mixerInfo = $mapping[$barSale->product_id] ?? null;
                    if ($mixerInfo) {
                        $defaultMixerId = (int) $mixerInfo['mixer_id'];
                        $ratio = $mixerInfo['ratio'] ?? 1;
                        $remainingCombos = max(0, $vendido - $specialCount);
                        $combosPerMixer[$defaultMixerId] = ($combosPerMixer[$defaultMixerId] ?? 0) + ($remainingCombos * $ratio);
                    }
                } else {
                    // Mixer — guardar pendiente para 2da pasada
                    $updatedSales[$id] = $barSale;
                }
            }

            // 2da pasada: Guardar mixers con descuento de combos incluidos
            foreach ($updatedSales as $id => $mixerSale) {
                $includedInCombos = $combosPerMixer[$mixerSale->product_id] ?? 0;
                $extras = max(0, $mixerSale->vendido - $includedInCombos);
                $mixerSale->subtotal = $extras * $mixerSale->unit_price;
                $mixerSale->save();
            }

            // Propagar saldos a la siguiente noche si existe (para mantener el inventario sincronizado)
            if ($sessionId) {
                $session = NightSession::find($sessionId);
                if ($session) {
                    $dateStr = Carbon::parse($session->session_date)->format('Y-m-d');
                    $nextSession = NightSession::where('id', '!=', $session->id)
                        ->whereDate('session_date', '>', $dateStr)
                        ->orderBy('session_date')
                        ->first() ?? NightSession::where('id', '>', $session->id)->orderBy('id')->first();

                    if ($nextSession) {
                        $nextSales = BarSale::with('product')
                            ->where('night_session_id', $nextSession->id)
                            ->where('bar_name', $barName)
                            ->get();

                        foreach ($nextSales as $nextSale) {
                            if (! $nextSale->product) {
                                continue;
                            }
                            $curSale = BarSale::where('night_session_id', $session->id)
                                ->where('bar_name', $barName)
                                ->where('product_id', $nextSale->product_id)
                                ->first();

                            $curSaldo = $curSale ? (int) $curSale->saldo : 0;
                            $unitsPerPkg = $nextSale->product->units_per_package > 0 ? (int) $nextSale->product->units_per_package : 1;

                            $nextInitPkg = intdiv($curSaldo, $unitsPerPkg);
                            $nextInitUnits = $curSaldo % $unitsPerPkg;

                            $nextSale->initial_packages = $nextInitPkg;
                            $nextSale->initial_units = $nextInitUnits;

                            $totNightPkg = $nextInitPkg + (int) $nextSale->added_packages + (int) $nextSale->night_packages;
                            $totNightUnits = $nextInitUnits + (int) $nextSale->added_units + (int) $nextSale->night_units;
                            $totNightBot = ($totNightPkg * $unitsPerPkg) + $totNightUnits;

                            $nextSale->packages = $totNightPkg;
                            $nextSale->units = $totNightUnits;
                            $nextSale->total_initial = $totNightBot;

                            // Saldo Cierre de la siguiente noche se mantiene en 0 por defecto
                            $nextSale->saldo = (int) ($nextSale->saldo ?? 0);
                            $nextSale->vendido = max(0, $totNightBot - (int) $nextSale->saldo);

                            $this->syncProductStock($nextSale);

                            if ($nextSale->product->category !== 'Mixers') {
                                $nextSale->subtotal = $nextSale->vendido * (float) $nextSale->unit_price;
                            }

                            $nextSale->save();
                        }
                    }
                }
            }
        }

        if ($sessionId) {
            $session = NightSession::find($sessionId);
            if ($session) {
                $closing = CashClosing::firstOrCreate(['night_session_id' => $session->id]);
                if ($request->has('guardarropa_amount')) {
                    $closing->total_guardarropa = (float) $request->input('guardarropa_amount', 0);
                }
                if ($request->has('snacks_amount')) {
                    $closing->total_snacks = (float) $request->input('snacks_amount', 0);
                }
                $closing->save();
                $this->sessionService->recalculateClosing($session);
            }
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Ventas de '.$barName.' guardadas correctamente.',
            ]);
        }

        // Redirigir de vuelta con los parámetros de sesión y barra para que se vean los cambios
        return redirect()->route('sales.index', [
            'session_id' => $sessionId,
            'bar' => $barName,
        ])->with('success', 'Ventas de '.$barName.' guardadas correctamente.');
    }

    private function syncProductStock(BarSale $barSale): void
    {
        if (! $barSale->product) {
            return;
        }

        $previouslySynced = (int) $barSale->stock_synced_vendido;
        $currentSold = max(0, (int) $barSale->vendido);
        $difference = $currentSold - $previouslySynced;
        $unitsPerPackage = max(1, (int) ($barSale->product->units_per_package ?? 1));
        $openingStock = (((int) $barSale->initial_packages + (int) $barSale->added_packages) * $unitsPerPackage)
            + (int) $barSale->initial_units
            + (int) $barSale->added_units;

        if ($difference !== 0 && $openingStock === 0) {
            $product = $barSale->product;
            $product->stock_warehouse = max(0, (int) $product->stock_warehouse - $difference);
            $unitsPerPackage = max(1, (int) $product->units_per_package);
            $product->stock_packages = intdiv($product->stock_warehouse, $unitsPerPackage);
            $product->stock_units = $product->stock_warehouse % $unitsPerPackage;
            $product->save();
        }

        $barSale->stock_synced_vendido = $currentSold;
    }
}
