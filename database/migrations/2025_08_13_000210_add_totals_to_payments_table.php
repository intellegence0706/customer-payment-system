<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->decimal('subtotal_amount', 12, 2)->nullable()->after('amount');
            $table->decimal('tax_total', 12, 2)->nullable()->after('subtotal_amount');
            $table->decimal('other_fees_total', 12, 2)->nullable()->after('tax_total');
            $table->decimal('grand_total', 12, 2)->nullable()->after('other_fees_total');
        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['subtotal_amount', 'tax_total', 'other_fees_total', 'grand_total']);
        });
    }
};


