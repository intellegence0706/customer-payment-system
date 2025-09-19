<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->integer('payment_month');
            $table->integer('payment_year');
            $table->decimal('amount', 10, 2);
            $table->date('payment_date');
            $table->string('receipt_number', 50)->nullable();
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['payment_month', 'payment_year']);
            $table->index('payment_date');
            $table->index('status');
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
