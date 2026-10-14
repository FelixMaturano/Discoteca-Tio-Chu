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
        Schema::create('beer_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('night_session_id')->constrained('night_sessions')->onDelete('cascade');
            $table->enum('payment_method', ['efectivo', 'qr'])->default('efectivo');
            $table->enum('qr_provider', ['YASTA', 'YAPE'])->nullable();
            $table->decimal('amount', 10, 2)->default(0);
            $table->string('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beer_payments');
    }
};
