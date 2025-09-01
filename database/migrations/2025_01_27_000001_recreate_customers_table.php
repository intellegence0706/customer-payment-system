<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Disable foreign key checks temporarily
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('customers');
        
        // Create new customers table with only the 22 parameters from the image
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            
            $table->string('customer_code', 50)->nullable()->comment('顧客コード');
            $table->string('user_kana_name', 100)->nullable()->comment('利用者カナ氏名');
            $table->string('user_name', 100)->nullable()->comment('利用者氏名');
            $table->string('account_kana_name', 100)->nullable()->comment('口座カナ氏名');
            $table->string('account_holder_name', 100)->nullable()->comment('口座人氏名');
            
            // Payment information
            $table->string('payment_classification', 10)->nullable()->comment('支払区分');
            $table->string('payment_method', 255)->nullable()->comment('支払方法');
            $table->decimal('billing_amount', 12, 2)->nullable()->comment('請求金額');
            $table->decimal('collection_request_amount', 12, 2)->nullable()->comment('徴収請求額');
            $table->decimal('consumption_tax', 12, 2)->nullable()->comment('消費税');
            
            // Banking information
            $table->string('bank_number', 10)->nullable()->comment('銀行番号');
            $table->string('bank_name', 255)->nullable()->comment('銀行名');
            $table->string('branch_number', 10)->nullable()->comment('支店番号');
            $table->string('branch_name', 255)->nullable()->comment('支店名');
            $table->string('deposit_type', 50)->nullable()->comment('預金種目');
            $table->string('account_number', 50)->nullable()->comment('口座番号');
            $table->string('customer_number', 50)->unique()->comment('顧客番号');
            
            // Billing address
            $table->string('billing_postal_code', 10)->nullable()->comment('請求先郵便番号');
            $table->string('billing_prefecture', 50)->nullable()->comment('請求先県名');
            $table->string('billing_city', 100)->nullable()->comment('請求先市区町村');
            $table->string('billing_street', 200)->nullable()->comment('請求先番地');
            $table->decimal('billing_difference', 12, 2)->nullable()->comment('請求先差額');
            
            $table->timestamps();
            
            // Indexes for better performance
            $table->index('customer_code');
            $table->index('customer_number');
            $table->index('user_name');
            $table->index('bank_name');
            $table->index('payment_classification');
        });
        
        // Re-enable foreign key checks
        Schema::enableForeignKeyConstraints();
    }

    public function down()
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('customers');
        Schema::enableForeignKeyConstraints();
    }
};
