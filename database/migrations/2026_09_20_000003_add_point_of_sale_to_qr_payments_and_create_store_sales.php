<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Agregar punto de venta a qr_payments (Barra Principal, Subte, Tienda)
        Schema::table('qr_payments', function (Blueprint $table) {
            $table->string('point_of_sale')->default('Barra Principal')->after('night_session_id');
        });

        // 2. Crear tabla para ventas directas de Tienda (combos sacados directo de almacén con desglose efectivo/QR)
        Schema::create('store_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('night_session_id')->constrained('night_sessions')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->decimal('cash_amount', 10, 2)->default(0);
            $table->decimal('qr_amount', 10, 2)->default(0);
            $table->string('cobrante_name')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_sales');
        Schema::table('qr_payments', function (Blueprint $table) {
            $table->dropColumn('point_of_sale');
        });
    }
};
