<?php

namespace App\Http\Controllers;

use App\Models\BarSale;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->get('category');
        $query = Product::query();

        if ($category) {
            $query->where('category', $category);
        }

        $products = $query->orderBy('id')->get();
        $categories = Product::select('category')->distinct()->pluck('category');

        $drinkImages = [];
        $drinksDir = public_path('images/drinks');
        if (is_dir($drinksDir)) {
            $files = scandir($drinksDir);
            foreach ($files as $file) {
                if ($file !== '.' && $file !== '..' && in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['png', 'jpg', 'jpeg', 'webp'])) {
                    $drinkImages[] = [
                        'filename' => $file,
                        'path' => 'images/drinks/' . $file,
                        'url' => asset('images/drinks/' . $file),
                    ];
                }
            }
        }

        return view('products.index', compact('products', 'categories', 'category', 'drinkImages'));
    }

    public function create()
    {
        return view('products.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string|max:100',
            'sale_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'unit' => 'required|string|max:50',
            'image_path' => 'nullable|string|max:255',
            'units_per_package' => 'required|integer|min:1',
            'stock_warehouse' => 'required|integer|min:0',
            'image' => 'nullable|image|max:5120',
        ]);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
            $file->move(public_path('images/drinks'), $filename);
            $validated['image_path'] = 'images/drinks/' . $filename;
        }

        unset($validated['image']);

        $product = Product::create($validated);
        $this->syncStockBreakdown($product);
        $product->save();

        return redirect()->route('products.index')->with('success', 'Producto registrado correctamente.');
    }

    public function edit(Product $product)
    {
        return view('products.edit', compact('product'));
    }

    public function update(Request $request, Product $product)
    {
        if ($request->wantsJson() || $request->ajax() || $request->has('_ajax')) {
            $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'category' => 'required|string|max:100',
                'sale_price' => 'required|numeric|min:0',
                'cost_price' => 'nullable|numeric|min:0',
                'unit' => 'required|string|max:50',
                'image_path' => 'nullable|string|max:255',
                'units_per_package' => 'required|integer|min:1',
                'stock_warehouse' => 'required|integer|min:0',
                'is_active' => 'required|boolean',
                'image' => 'nullable|image|max:5120',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => $validator->errors()->first(),
                    'errors' => $validator->errors(),
                ], 422);
            }

            $validated = $validator->validated();
        } else {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'category' => 'required|string|max:100',
                'sale_price' => 'required|numeric|min:0',
                'cost_price' => 'nullable|numeric|min:0',
                'unit' => 'required|string|max:50',
                'image_path' => 'nullable|string|max:255',
                'units_per_package' => 'required|integer|min:1',
                'stock_warehouse' => 'required|integer|min:0',
                'is_active' => 'required|boolean',
                'image' => 'nullable|image|max:5120',
            ]);
        }

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $file->getClientOriginalName());
            $file->move(public_path('images/drinks'), $filename);
            $validated['image_path'] = 'images/drinks/' . $filename;
        }

        unset($validated['image']);

        $product->update($validated);
        BarSale::where('product_id', $product->id)
            ->whereHas('nightSession', fn ($query) => $query->where('status', 'open'))
            ->get()
            ->each(function (BarSale $barSale) use ($product): void {
                $barSale->unit_price = $product->sale_price;
                if ($product->category !== 'Mixers') {
                    $barSale->subtotal = (int) $barSale->vendido * (float) $product->sale_price;
                }
                $barSale->save();
            });
        $this->syncStockBreakdown($product);
        $product->save();

        if ($request->wantsJson() || $request->ajax() || $request->has('_ajax')) {
            return response()->json([
                'success' => true,
                'message' => 'Bebida "' . $product->name . '" actualizada correctamente.',
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'category' => $product->category,
                    'sale_price' => (float)$product->sale_price,
                    'cost_price' => (float)($product->cost_price ?? 0),
                    'unit' => $product->unit,
                    'units_per_package' => (int)$product->units_per_package,
                    'stock_warehouse' => (int)$product->stock_warehouse,
                    'stock_packages' => (int)$product->stock_packages,
                    'stock_units' => (int)$product->stock_units,
                    'is_active' => (bool)$product->is_active,
                    'image_url' => $product->image_url,
                ],
            ]);
        }

        return redirect()->route('products.index')->with('success', 'Producto actualizado correctamente.');
    }

    public function destroy(Request $request, Product $product)
    {
        $product->delete();
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Producto eliminado correctamente.']);
        }
        return redirect()->route('products.index')->with('success', 'Producto eliminado.');
    }

    /**
     * Actualización ágil de stock con 1 solo toque (Fetch/AJAX o Form)
     */
    public function updateQuickStock(Request $request, Product $product)
    {
        $validated = $request->validate([
            'delta' => 'nullable|integer',
            'stock' => 'nullable|integer|min:0',
            'stock_packages' => 'nullable|integer|min:0',
            'stock_units' => 'nullable|integer|min:0',
        ]);

        if (isset($validated['stock_packages']) || isset($validated['stock_units'])) {
            $unitsPerPackage = max(1, (int) $product->units_per_package);
            $packages = max(0, (int)($validated['stock_packages'] ?? $product->stock_packages));
            $units = max(0, (int)($validated['stock_units'] ?? $product->stock_units));
            $packages += intdiv($units, $unitsPerPackage);
            $units %= $unitsPerPackage;
            $product->stock_packages = $packages;
            $product->stock_units = $units;
            $product->stock_warehouse = ($packages * $unitsPerPackage) + $units;
        } elseif (isset($validated['delta'])) {
            $product->stock_warehouse = max(0, (int)$product->stock_warehouse + (int)$validated['delta']);
        } elseif (isset($validated['stock'])) {
            $product->stock_warehouse = max(0, (int)$validated['stock']);
        }

        $this->syncStockBreakdown($product);
        $product->save();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'product_id' => $product->id,
                'stock_warehouse' => $product->stock_warehouse,
                'stock_packages' => $product->stock_packages,
                'stock_units' => $product->stock_units,
                'status' => $product->stock_warehouse > 10 ? 'in_stock' : ($product->stock_warehouse > 0 ? 'low_stock' : 'out_of_stock'),
                'message' => 'Stock de ' . $product->name . ' actualizado a ' . $product->stock_warehouse . '.',
            ]);
        }

        return back()->with('success', 'Stock actualizado a ' . $product->stock_warehouse . '.');
    }

    private function syncStockBreakdown(Product $product): void
    {
        $unitsPerPackage = max(1, (int) $product->units_per_package);
        $stock = max(0, (int) $product->stock_warehouse);
        $product->stock_packages = intdiv($stock, $unitsPerPackage);
        $product->stock_units = $stock % $unitsPerPackage;
    }
}
