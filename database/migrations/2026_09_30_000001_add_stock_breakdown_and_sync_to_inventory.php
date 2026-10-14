<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('stock_packages')->default(0)->after('stock_warehouse');
            $table->unsignedInteger('stock_units')->default(0)->after('stock_packages');
        });

        Schema::table('bar_sales', function (Blueprint $table) {
            $table->unsignedInteger('stock_synced_vendido')->default(0)->after('vendido');
        });

        DB::table('products')->select('id', 'stock_warehouse', 'units_per_package')->get()->each(function ($product): void {
            $unitsPerPackage = max(1, (int) $product->units_per_package);
            $stock = max(0, (int) $product->stock_warehouse);

            DB::table('products')->where('id', $product->id)->update([
                'stock_packages' => intdiv($stock, $unitsPerPackage),
                'stock_units' => $stock % $unitsPerPackage,
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('bar_sales', function (Blueprint $table) {
            $table->dropColumn('stock_synced_vendido');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['stock_packages', 'stock_units']);
        });
    }
};
