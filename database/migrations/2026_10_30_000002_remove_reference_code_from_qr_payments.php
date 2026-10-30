<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('qr_payments', function (Blueprint $table) {
            $table->dropColumn('reference_code');
        });
    }

    public function down(): void
    {
        Schema::table('qr_payments', function (Blueprint $table) {
            $table->string('reference_code')->nullable();
        });
    }
};
