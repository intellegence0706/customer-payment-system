<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE payments MODIFY amount DECIMAL(18,2)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE payments MODIFY amount DECIMAL(12,2)');
    }
};









