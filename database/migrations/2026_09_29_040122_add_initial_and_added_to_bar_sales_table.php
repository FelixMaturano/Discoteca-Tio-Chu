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
        Schema::table('bar_sales', function (Blueprint $table) {
            $table->integer('initial_packages')->default(0)->after('bar_name'); // Paquetes que quedaron de la noche anterior
            $table->integer('initial_units')->default(0)->after('initial_packages'); // Unidades sueltas de la noche anterior
            $table->integer('added_packages')->default(0)->after('initial_units'); // Paquetes nuevos agregados al inicio
            $table->integer('added_units')->default(0)->after('added_packages'); // Unidades nuevas agregadas al inicio
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bar_sales', function (Blueprint $table) {
            $table->dropColumn(['initial_packages', 'initial_units', 'added_packages', 'added_units']);
        });
    }
};
