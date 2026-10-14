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
        if (!Schema::hasColumn('invoices', 'bar_name')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->string('bar_name')->default('Principal')->after('payment_method');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('invoices', 'bar_name')) {
            Schema::table('invoices', function (Blueprint $table) {
                $table->dropColumn('bar_name');
            });
        }
    }
};
