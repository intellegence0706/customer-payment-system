<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('name_kana')->nullable()->after('name');
            $table->date('date_of_birth')->nullable()->after('name_kana');
            $table->string('prefecture')->nullable()->after('postal_code');
            $table->string('city')->nullable()->after('prefecture');
            $table->string('address_line')->nullable()->after('city');
            $table->string('building')->nullable()->after('address_line');
            $table->string('telephone_number', 20)->nullable()->after('phone_number');
            $table->string('mobile_number', 20)->nullable()->after('telephone_number');
            $table->date('last_visit_date')->nullable()->after('bank_note');
            $table->date('next_visit_date')->nullable()->after('last_visit_date');
            $table->date('reception_date')->nullable()->after('next_visit_date');
            $table->string('residence')->nullable()->after('reception_date');
            $table->string('care_manager')->nullable()->after('residence');
            $table->string('payment_method')->nullable()->after('care_manager');
            $table->decimal('rental_fee', 10, 2)->nullable()->after('payment_method');
            $table->decimal('assembly_delivery_costs', 10, 2)->nullable()->after('rental_fee');
            $table->string('district_court')->nullable()->after('assembly_delivery_costs');
            $table->boolean('billable')->default(true)->after('district_court');
            $table->string('subject')->nullable()->after('billable');
            $table->string('deposit_type')->nullable()->after('account_holder');
            $table->string('salesperson')->nullable()->after('subject');
            $table->string('address_operator')->nullable()->after('salesperson');
            $table->string('account_kana')->nullable()->after('account_name');
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'name_kana',
                'date_of_birth',
                'prefecture',
                'city',
                'address_line',
                'building',
                'telephone_number',
                'mobile_number',
                'last_visit_date',
                'next_visit_date',
                'reception_date',
                'residence',
                'care_manager',
                'payment_method',
                'rental_fee',
                'assembly_delivery_costs',
                'district_court',
                'billable',
                'subject',
                'deposit_type',
                'salesperson',
                'address_operator',
                'account_kana',
            ]);
        });
    }
};


