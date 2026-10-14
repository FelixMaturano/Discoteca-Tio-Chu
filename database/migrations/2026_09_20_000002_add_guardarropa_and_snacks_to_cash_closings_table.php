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
        Schema::table('cash_closings', function (Blueprint $table) {
            $table->decimal('total_guardarropa', 10, 2)->default(0)->after('total_bar_sales');
            $table->decimal('total_snacks', 10, 2)->default(0)->after('total_guardarropa');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cash_closings', function (Blueprint $table) {
            $table->dropColumn(['total_guardarropa', 'total_snacks']);
        });
    }
};
