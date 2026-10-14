<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Normalizar nombres en bar_sales
        DB::table('bar_sales')
            ->whereIn('bar_name', ['Barra Kelly (Principal)', 'Kelly'])
            ->update(['bar_name' => 'Barra Principal']);

        DB::table('bar_sales')
            ->whereIn('bar_name', ['Barra Ariel (Subte)', 'Ariel', 'Subte', 'Subterraneo'])
            ->update(['bar_name' => 'Barra Subterráneo']);

        // 2. Normalizar nombres en qr_payments
        DB::table('qr_payments')
            ->whereIn('point_of_sale', ['Barra Kelly (Principal)', 'Kelly'])
            ->update(['point_of_sale' => 'Barra Principal']);

        DB::table('qr_payments')
            ->whereIn('point_of_sale', ['Barra Ariel (Subte)', 'Ariel', 'Barra Subte'])
            ->update(['point_of_sale' => 'Subte']);

        // 3. Normalizar nombres en invoices
        DB::table('invoices')
            ->whereIn('bar_name', ['Barra Kelly (Principal)', 'Kelly'])
            ->update(['bar_name' => 'Principal']);

        DB::table('invoices')
            ->whereIn('bar_name', ['Barra Ariel (Subte)', 'Ariel'])
            ->update(['bar_name' => 'Subterraneo']);

        // 4. Normalizar nombres en staff
        DB::table('staff')
            ->whereIn('assigned_bar', ['Barra Kelly (Principal)', 'Barra Kelly'])
            ->update(['assigned_bar' => 'Barra Principal']);

        DB::table('staff')
            ->whereIn('assigned_bar', ['Barra Ariel (Subte)', 'Barra Ariel'])
            ->update(['assigned_bar' => 'Barra Subterráneo']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert not strictly needed as institutional names are the desired persistent state
    }
};
