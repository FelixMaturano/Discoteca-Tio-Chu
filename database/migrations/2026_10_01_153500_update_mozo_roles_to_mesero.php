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
        DB::table('staff')
            ->where('role', 'like', '%mozo%')
            ->update([
                'role' => DB::raw("REPLACE(role, 'Mozo', 'Mesero')")
            ]);

        DB::table('staff')
            ->where('role', 'like', '%MOZO%')
            ->update([
                'role' => DB::raw("REPLACE(role, 'MOZO', 'Mesero')")
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed
    }
};
