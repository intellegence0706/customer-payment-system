<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ghana')->nullable();
            $table->enum('gender', ['male', 'female', 'other']);
            $table->string('postal_code', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->text('note')->nullable();
            
            // Bank information
            $table->string('bank_code', 4)->nullable();
            $table->string('bank_name')->nullable();
            $table->string('branch_code', 3)->nullable();
            $table->string('branch_name')->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_ghana')->nullable();
            $table->string('account_number', 50)->nullable();
            $table->string('account_holder')->nullable();
            $table->string('customer_number', 50)->unique();
            $table->text('bank_note')->nullable();
            
            $table->timestamps();
            
            $table->index(['name', 'customer_number']);
            $table->index('bank_code');
            $table->index('branch_code');
        });
    }

    public function down()
    {
        Schema::dropIfExists('customers');
    }
};
