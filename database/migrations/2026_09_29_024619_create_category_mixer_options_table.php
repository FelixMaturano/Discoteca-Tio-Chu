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
        // Tabla para registrar las opciones especiales configurables por categoría
        Schema::create('category_mixer_options', function (Blueprint $table) {
            $table->id();
            $table->string('category'); // 'Ron', 'Singani', 'Whisky', 'Gin', etc.
            $table->string('mixer_name'); // 'Coca Cola 2L', 'Aquarius Pomelo', 'Sprite 2L', etc.
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete(); // Producto mixer asociado
            $table->timestamps();
        });

        // Añadir columna a bar_sales para guardar el mixer seleccionado en esa noche/fila
        Schema::table('bar_sales', function (Blueprint $table) {
            $table->string('selected_special_mixer')->nullable()->after('vendido');
        });
    }

    public function down(): void
    {
        Schema::table('bar_sales', function (Blueprint $table) {
            $table->dropColumn('selected_special_mixer');
        });
        Schema::dropIfExists('category_mixer_options');
    }

};
