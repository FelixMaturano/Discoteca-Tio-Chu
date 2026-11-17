<?php

namespace App\Http\Controllers;

use App\Models\BarSale;
use App\Models\CashClosing;
use App\Models\CategoryMixerOption;
use App\Models\NightSession;
use App\Models\Product;
use App\Services\BarSaleCalculationService;
use App\Services\NightSessionService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BarSaleController extends Controller
{
    protected NightSessionService $sessionService;

    protected BarSaleCalculationService $calculationService;

    public function __construct(NightSessionService $sessionService, BarSaleCalculationService $calculationService)
    {
        $this->sessionService = $sessionService;
        $this->calculationService = $calculationService;
    }

    public function index(Request $request)
    {
        $session = $this->sessionService->resolveSession($request->get('session_id'));
        $allSessions = NightSession::query()
            ->orderByDesc('session_date')
            ->limit(25)
            ->get(['id', 'day_name', 'session_date', 'status']);
        if ($session && ! $allSessions->contains('id', $session->id)) {
            $allSessions->prepend($session);
        }

        $selectedBar = $this->normalizeBarName($request->get('bar', 'Barra Principal'));
        $availableBars = ['Barra Principal', 'Barra Subterráneo', 'Tienda'];

        $allBarSales = collect();
        $allBarSalesPage = collect();
        $liquorSales = collect();
        $mixerSales = collect();
        $liquorSalesPage = collect();
        $mixerSalesPage = collect();
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
        $storeSalesQrTotal = 0;
        $storeSalesCardTotal = 0;
        $totalStoreSales = 0;

        if ($session) {
            $closing = CashClosing::firstOrCreate(['night_session_id' => $session->id]);
            [$qrPosNames, $invoiceBarNames] = $this->paymentPointNames($selectedBar);

            $barQrPayments = $session->qrPayments()
                ->whereIn('point_of_sale', $qrPosNames)
                ->orderByDesc('id')
                ->paginate(25, ['*'], 'qr_page')
                ->withQueryString();
            $barTotalQrYasta = (float) $session->qrPayments()
                ->whereIn('point_of_sale', $qrPosNames)
                ->where('bank_app', 'YASTA')
                ->sum('amount');
            $barTotalQrYape = (float) $session->qrPayments()
                ->whereIn('point_of_sale', $qrPosNames)
                ->where('bank_app', 'YAPE')
                ->sum('amount');
            $barTotalQr = (float) $session->qrPayments()
                ->whereIn('point_of_sale', $qrPosNames)
                ->sum('amount');

            $barCardInvoices = $session->invoices()
                ->whereIn('bar_name', $invoiceBarNames)
                ->where('payment_method', 'tarjeta')
                ->orderBy('correlative_num')
                ->paginate(25, ['*'], 'card_page')
                ->withQueryString();
            $barTotalCard = (float) $session->invoices()
                ->whereIn('bar_name', $invoiceBarNames)
                ->where('payment_method', 'tarjeta')
                ->sum('amount');
            $barTotalCardCommission = (float) $session->invoices()
                ->whereIn('bar_name', $invoiceBarNames)
                ->where('payment_method', 'tarjeta')
                ->sum('commission_amount');
            $barTotalCardNet = (float) $session->invoices()
                ->whereIn('bar_name', $invoiceBarNames)
                ->where('payment_method', 'tarjeta')
                ->sum('net_amount');

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
                ->whereHas('product')
                ->where('night_session_id', $session->id)
                ->where('bar_name', $selectedBar)
                ->get();

            $totalStoreSales = (float) $session->storeSales()->sum('total_price');
            $storeSalesQrTotal = (float) $session->storeSales()->sum('qr_amount');
            $storeSalesCardTotal = (float) $session->storeSales()->sum('card_amount');
            $summary = $this->calculationService->summarize(
                $allBarSales,
                $selectedBar === 'Tienda',
                $mapping,
                $totalStoreSales
            );
            $allBarSales = $summary['allBarSales'];
            $liquorSales = $summary['liquorSales'];
            $mixerSales = $summary['mixerSales'];
            $totalTiendaUnits = $summary['totalTiendaUnits'];
            $totalLiquorCombos = $summary['totalLiquorCombos'];
            $subtotalLiquors = $summary['subtotalLiquors'];
            $totalMixerConsumed = $summary['totalMixerConsumed'];
            $totalMixerExtras = $summary['totalMixerExtras'];
            $subtotalMixers = $summary['subtotalMixers'];
            $grandTotalBar = $summary['grandTotalBar'];

            if ($selectedBar === 'Tienda') {
                $allBarSalesPage = $this->paginateCollection($allBarSales, $request, 'sales_page');
            } else {
                $liquorSalesPage = $this->paginateCollection($liquorSales, $request, 'liquors_page');
                $mixerSalesPage = $this->paginateCollection($mixerSales, $request, 'mixers_page');
            }

            // Obtener mixers especiales y categorías de tragos
            $specialMixerOptions = CategoryMixerOption::all()->groupBy('category');
            $categories = Product::getDrinkSubcategories();
            $allMixerProducts = Product::where('category', 'Mixers')
                ->where('is_active', true)
                ->get(['id', 'name', 'sale_price']);

            $barCashRemaining = max(0, $grandTotalBar - $barTotalQr - $barTotalCardNet);
        } else {
            $totalTiendaUnits = 0;
            $allBarSales = collect();
            $barQrPayments = collect();
            $barTotalQr = 0;
            $barTotalQrYasta = 0;
            $barTotalQrYape = 0;
            $barCardInvoices = collect();
            $barTotalCard = 0;
            $barTotalCardCommission = 0;
            $barTotalCardNet = 0;
            $barCashRemaining = 0;
            $storeSalesQrTotal = 0;
            $storeSalesCardTotal = 0;
            $totalStoreSales = 0;
        }

        return view('sales.index', compact(
            'session',
            'allSessions',
            'selectedBar',
            'availableBars',
            'liquorSales',
            'mixerSales',
            'allBarSales',
            'liquorSalesPage',
            'mixerSalesPage',
            'allBarSalesPage',
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
            'barCardInvoices',
            'barTotalCard',
            'barTotalCardCommission',
            'barTotalCardNet',
            'barCashRemaining',
            'storeSalesQrTotal',
            'storeSalesCardTotal',
            'totalStoreSales',
        ));

    }

    /**
     * @return array{0: array<int, string>, 1: array<int, string>}
     */
    private function paymentPointNames(string $selectedBar): array
    {
        if ($selectedBar === 'Tienda') {
            return [['Tienda', 'tienda'], ['Tienda', 'tienda']];
        }

        if (str_contains($selectedBar, 'Subte') || str_contains($selectedBar, 'Ariel') || str_contains($selectedBar, 'Subterráneo') || str_contains($selectedBar, 'Subterraneo')) {
            return [
                ['Barra Subterráneo', 'Subte', 'Barra Subte', 'Subterráneo', 'Subterraneo', 'Barra Ariel (Subte)', 'Ariel'],
                ['Barra Subterráneo', 'Subterráneo', 'Subterraneo', 'Subte', 'Barra Ariel (Subte)', 'Ariel'],
            ];
        }

        return [
            ['Barra Principal', 'Principal', 'Barra Kelly (Principal)', 'Kelly'],
            ['Barra Principal', 'Principal', 'Barra Kelly (Principal)', 'Kelly'],
        ];
    }

    private function normalizeBarName(mixed $rawBar): string
    {
        if (! is_string($rawBar)) {
            return 'Barra Principal';
        }

        return match ($rawBar) {
            'Barra Kelly (Principal)', 'Kelly', 'Principal' => 'Barra Principal',
            'Barra Ariel (Subte)', 'Ariel', 'Subte', 'Subterraneo', 'Subterráneo' => 'Barra Subterráneo',
            'Tienda' => 'Tienda',
            default => $rawBar,
        };
    }

    /**
     * @template TItem
     *
     * @param  Collection<int, TItem>  $items
     * @return LengthAwarePaginator<TItem>
     */
    private function paginateCollection(Collection $items, Request $request, string $pageName): LengthAwarePaginator
    {
        $perPage = 10;
        $currentPage = LengthAwarePaginator::resolveCurrentPage($pageName);

        return new LengthAwarePaginator(
            $items->forPage($currentPage, $perPage)->values(),
            $items->count(),
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
                'pageName' => $pageName,
            ]
        );
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
        return DB::transaction(fn () => $this->updateBulkTransaction($request), 3);
    }

    private function updateBulkTransaction(Request $request)
    {
        $sessionId = $request->input('night_session_id');
        $sessionId = $sessionId ? (int) $sessionId : null;
        $session = $sessionId
            ? NightSession::query()->lockForUpdate()->find($sessionId)
            : null;
        if ($session && ! $session->isOpen()) {
            return back()->with('error', 'La noche está cerrada. Para modificar ventas o combos, reabre la noche desde Cierre de Caja.');
        }

        $validatedSales = $request->validate([
            'sales' => 'sometimes|array',
            'sales.*' => 'array',
        ]);
        $rows = $validatedSales['sales'] ?? [];

        $mapping = Product::getMixerMapping();
        $barName = $this->normalizeBarName($request->input('bar_name', 'Barra Principal'));

        if ($sessionId) {
            $sales = BarSale::with('product')
                ->where('night_session_id', $sessionId)
                ->where('bar_name', $barName)
                ->whereHas('product')
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($barName === 'Tienda') {
                $this->updateStoreSales($sales, $rows, $mapping);
            } else {
                $this->updateBarSales($sales, $rows, $mapping);
                $this->propagateNextSessionInventory($sessionId, $barName);
            }
        }

        $this->updateClosingAmounts($request, $sessionId);

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

    /**
     * @param  Collection<int, BarSale>  $sales
     * @param  array<array-key, array<string, mixed>>  $rows
     * @param  array<int, array{mixer_id: int, ratio?: int}>  $mapping
     */
    private function updateStoreSales(Collection $sales, array $rows, array $mapping): void
    {
        foreach ($rows as $id => $data) {
            $barSale = $sales->get((int) $id);
            if (! $barSale) {
                continue;
            }

            $this->applySelectedSpecialMixers($barSale, $data);
            $barSale->vendido = max(0, (int) ($data['vendido'] ?? 0));
            $barSale->unit_price = $this->resolveUnitPrice($data['unit_price'] ?? null, $barSale);
            if ($barSale->product->category !== 'Mixers') {
                $barSale->packages = 0;
                $barSale->units = 0;
                $barSale->total_initial = $barSale->vendido;
                $barSale->saldo = 0;
                $barSale->subtotal = (int) $barSale->vendido * (float) $barSale->unit_price;
                $this->syncProductStock($barSale);
                $barSale->save();
            }
        }

        $this->recalculateMixerSubtotals($sales, $mapping, true);
    }

    /**
     * @param  Collection<int, BarSale>  $sales
     * @param  array<array-key, array<string, mixed>>  $rows
     * @param  array<int, array{mixer_id: int, ratio?: int}>  $mapping
     */
    private function updateBarSales(Collection $sales, array $rows, array $mapping): void
    {
        foreach ($rows as $id => $data) {
            $barSale = $sales->get((int) $id);
            if (! $barSale) {
                continue;
            }

            $this->applySelectedSpecialMixers($barSale, $data);
            $packages = (int) ($data['packages'] ?? 0);
            $units = (int) ($data['units'] ?? 0);
            $saldo = (int) ($data['saldo'] ?? 0);
            $unitPrice = $this->resolveUnitPrice($data['unit_price'] ?? null, $barSale);
            $unitsPerPackage = max(1, (int) $barSale->product->units_per_package);
            $totalInitial = ($packages * $unitsPerPackage) + $units;

            $barSale->packages = $packages;
            $barSale->units = $units;
            $barSale->total_initial = $totalInitial;
            $barSale->saldo = $saldo;
            $barSale->vendido = max(0, $totalInitial - $saldo);
            $barSale->unit_price = $unitPrice;
            $this->syncProductStock($barSale);
            if ($barSale->product->category !== 'Mixers') {
                $barSale->subtotal = (int) $barSale->vendido * $unitPrice;
                $barSale->save();
            }
        }

        $this->recalculateMixerSubtotals($sales, $mapping, false);
    }

    /**
     * @param  Collection<int, BarSale>  $sales
     * @param  array<int, array{mixer_id: int, ratio?: int}>  $mapping
     */
    private function recalculateMixerSubtotals(Collection $sales, array $mapping, bool $isStore): void
    {
        $liquorSales = $sales->filter(fn (BarSale $sale): bool => $sale->product->category !== 'Mixers');
        $combosPerMixer = $this->calculationService->calculateIncludedMixers($liquorSales, $mapping);

        foreach ($sales as $sale) {
            if ($sale->product->category !== 'Mixers') {
                continue;
            }

            $included = $combosPerMixer[$sale->product_id] ?? 0;
            if ($isStore) {
                $sale->vendido = max((int) $sale->vendido, $included);
                $sale->total_initial = $sale->vendido;
                $sale->saldo = 0;
                $sale->packages = 0;
                $sale->units = 0;
                $this->syncProductStock($sale);
            }
            $extras = max(0, (int) $sale->vendido - $included);
            $sale->subtotal = $extras * (float) $sale->unit_price;
            $sale->save();
        }
    }

    /**
     * @param  array<array-key, mixed>  $data
     */
    private function applySelectedSpecialMixers(BarSale $barSale, array $data): void
    {
        if (! array_key_exists('selected_special_mixer', $data)) {
            return;
        }

        $specialMixers = $data['selected_special_mixer'];
        $barSale->selected_special_mixer = is_array($specialMixers)
            ? json_encode($specialMixers)
            : $specialMixers;
    }

    private function resolveUnitPrice(mixed $requestedPrice, BarSale $barSale): float
    {
        if (is_numeric($requestedPrice)) {
            return (float) $requestedPrice;
        }

        return (float) $barSale->unit_price;
    }

    private function propagateNextSessionInventory(int $sessionId, string $barName): void
    {
        $session = NightSession::query()->lockForUpdate()->find($sessionId);
        if (! $session) {
            return;
        }

        $dateStr = Carbon::parse($session->session_date)->format('Y-m-d');
        $nextSession = NightSession::where('id', '!=', $session->id)
            ->whereDate('session_date', '>', $dateStr)
            ->orderBy('session_date')
            ->lockForUpdate()
            ->first() ?? NightSession::where('id', '>', $session->id)
            ->orderBy('id')
            ->lockForUpdate()
            ->first();

        if (! $nextSession) {
            return;
        }

        $nextSales = BarSale::with('product')
            ->where('night_session_id', $nextSession->id)
            ->where('bar_name', $barName)
            ->whereHas('product')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($nextSales as $nextSale) {
            $currentSale = BarSale::where('night_session_id', $session->id)
                ->where('bar_name', $barName)
                ->where('product_id', $nextSale->product_id)
                ->lockForUpdate()
                ->first();
            $currentBalance = $currentSale ? (int) $currentSale->saldo : 0;
            $unitsPerPackage = max(1, (int) $nextSale->product->units_per_package);
            $initialPackages = intdiv($currentBalance, $unitsPerPackage);
            $initialUnits = $currentBalance % $unitsPerPackage;
            $totalPackages = $initialPackages + (int) $nextSale->added_packages + (int) $nextSale->night_packages;
            $totalUnits = $initialUnits + (int) $nextSale->added_units + (int) $nextSale->night_units;
            $totalInitial = ($totalPackages * $unitsPerPackage) + $totalUnits;

            $nextSale->initial_packages = $initialPackages;
            $nextSale->initial_units = $initialUnits;
            $nextSale->packages = $totalPackages;
            $nextSale->units = $totalUnits;
            $nextSale->total_initial = $totalInitial;
            $nextSale->vendido = max(0, $totalInitial - (int) $nextSale->saldo);
            $this->syncProductStock($nextSale);

            if ($nextSale->product->category !== 'Mixers') {
                $nextSale->subtotal = (int) $nextSale->vendido * (float) $nextSale->unit_price;
            }
            $nextSale->save();
        }
    }

    private function updateClosingAmounts(Request $request, ?int $sessionId): void
    {
        if (! $sessionId) {
            return;
        }

        $session = NightSession::query()->lockForUpdate()->find($sessionId);
        if (! $session) {
            return;
        }

        $closing = CashClosing::where('night_session_id', $session->id)
            ->lockForUpdate()
            ->first() ?? CashClosing::firstOrCreate(['night_session_id' => $session->id]);

        if ($request->has('guardarropa_amount')) {
            $closing->total_guardarropa = (float) $request->input('guardarropa_amount', 0);
        }
        if ($request->has('snacks_amount')) {
            $closing->total_snacks = (float) $request->input('snacks_amount', 0);
        }

        $closing->save();
        $this->sessionService->recalculateClosing($session);
    }

    private function syncProductStock(BarSale $barSale): void
    {
        $product = Product::query()->lockForUpdate()->find($barSale->product_id);
        if (! $product) {
            return;
        }

        $barSale->setRelation('product', $product);
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
