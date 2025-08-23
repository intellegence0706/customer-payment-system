<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('customers', function (Blueprint $table) {
            // Add only the most essential new fields that don't exist
            if (!Schema::hasColumn('customers', 'customer_code')) {
                $table->string('customer_code', 50)->nullable()->after('id'); // 顧客コード
            }
            if (!Schema::hasColumn('customers', 'name_kana')) {
                $table->string('name_kana', 100)->nullable()->after('name'); // 利用者カナ氏名
            }
            if (!Schema::hasColumn('customers', 'account_kana_name')) {
                $table->string('account_kana_name', 100)->nullable()->after('name_kana'); // 口座カナ氏名
            }
            if (!Schema::hasColumn('customers', 'account_holder_name')) {
                $table->string('account_holder_name', 100)->nullable()->after('account_kana_name'); // 口座人氏名
            }
            if (!Schema::hasColumn('customers', 'customer_number_new')) {
                $table->string('customer_number_new', 100)->nullable()->after('account_holder_name'); // 顧客番号 (new format)
            }
            if (!Schema::hasColumn('customers', 'payment_classification')) {
                $table->string('payment_classification', 10)->nullable()->after('customer_number_new'); // 支払区分
            }
            if (!Schema::hasColumn('customers', 'billing_amount')) {
                $table->decimal('billing_amount', 12, 2)->nullable()->after('payment_method'); // 請求金額
            }
            if (!Schema::hasColumn('customers', 'adjusted_billing_amount')) {
                $table->decimal('adjusted_billing_amount', 12, 2)->nullable()->after('billing_amount'); // 調整請求額
            }
            if (!Schema::hasColumn('customers', 'consumption_tax')) {
                $table->decimal('consumption_tax', 12, 2)->nullable()->after('adjusted_billing_amount'); // 消費税
            }
            if (!Schema::hasColumn('customers', 'billing_postal_code')) {
                $table->string('billing_postal_code', 10)->nullable()->after('postal_code'); // 請求先郵便番号
            }
            if (!Schema::hasColumn('customers', 'billing_prefecture')) {
                $table->string('billing_prefecture', 50)->nullable()->after('billing_postal_code'); // 請求先県名
            }
            if (!Schema::hasColumn('customers', 'billing_city')) {
                $table->string('billing_city', 100)->nullable()->after('billing_prefecture'); // 請求先市区町村
            }
            if (!Schema::hasColumn('customers', 'billing_street_address')) {
                $table->string('billing_street_address', 200)->nullable()->after('billing_city'); // 請求先番地
            }
            if (!Schema::hasColumn('customers', 'billing_building')) {
                $table->string('billing_building', 200)->nullable()->after('billing_street_address'); // 請求先建物
            }
        });
    }

    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            // Remove new columns if they exist
            $columnsToDrop = [
                'customer_code',
                'name_kana',
                'account_kana_name',
                'account_holder_name',
                'customer_number_new',
                'payment_classification',
                'billing_amount',
                'adjusted_billing_amount',
                'consumption_tax',
                'billing_postal_code',
                'billing_prefecture',
                'billing_city',
                'billing_street_address',
                'billing_building'
            ];
            
            foreach ($columnsToDrop as $column) {
                if (Schema::hasColumn('customers', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
