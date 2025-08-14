<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payment_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->onDelete('cascade');
            $table->integer('row_no')->default(1);
            $table->date('item_date')->nullable();
            $table->string('product_code', 50)->nullable();
            $table->string('product_name');
            $table->decimal('unit_price', 12, 2)->nullable();
            $table->decimal('quantity', 12, 2)->default(1);
            $table->decimal('amount', 12, 2)->default(0); // unit_price * quantity (no tax)
            $table->decimal('tax_rate', 5, 2)->nullable(); // e.g. 10.00
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->string('category', 50)->nullable(); // e.g. other_charges, notice, previous_balance
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['payment_id', 'row_no']);
            $table->index('item_date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payment_items');
    }
};


