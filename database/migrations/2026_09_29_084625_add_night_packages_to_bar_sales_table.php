<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bar_sales', function (Blueprint $table) {
            // Paquetes y unidades agregados DURANTE la noche (reposición en turno)
            $table->integer('night_packages')->default(0)->after('added_units');
            $table->integer('night_units')->default(0)->after('night_packages');
        });
    }

    public function down(): void
    {
        Schema::table('bar_sales', function (Blueprint $table) {
            $table->dropColumn(['night_packages', 'night_units']);
        });
    }
};
