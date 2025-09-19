<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Increase precision to allow larger amounts
        DB::statement('ALTER TABLE payments MODIFY amount DECIMAL(12,2)');
    }

    public function down(): void
    {
        // Revert to previous precision used in the original migration
        DB::statement('ALTER TABLE payments MODIFY amount DECIMAL(10,2)');
    }
};


