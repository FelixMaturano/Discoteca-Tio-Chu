<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_stock_movements', function (Blueprint $table) {
            $table->string('source')->nullable();
            $table->string('destination')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('product_stock_movements', function (Blueprint $table) {
            $table->dropColumn(['source', 'destination']);
        });
    }
};
