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
        if (!Schema::hasColumn('bar_sales', 'selected_special_mixer')) {
            Schema::table('bar_sales', function (Blueprint $table) {
                $table->text('selected_special_mixer')->nullable()->after('added_units');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bar_sales', function (Blueprint $table) {
            $table->dropColumn('selected_special_mixer');
        });
    }
};
