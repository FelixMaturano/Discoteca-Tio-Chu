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
        Schema::table('staff', function (Blueprint $table) {
            $table->boolean('works_friday')->default(true)->after('default_pay');
            $table->boolean('works_saturday')->default(true)->after('works_friday');
            $table->boolean('works_sunday')->default(true)->after('works_saturday');
            $table->decimal('friday_pay', 10, 2)->nullable()->after('works_sunday');
            $table->decimal('saturday_pay', 10, 2)->nullable()->after('friday_pay');
            $table->decimal('sunday_pay', 10, 2)->nullable()->after('saturday_pay');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn([
                'works_friday',
                'works_saturday',
                'works_sunday',
                'friday_pay',
                'saturday_pay',
                'sunday_pay',
            ]);
        });
    }
};
