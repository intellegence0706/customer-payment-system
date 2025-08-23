<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_code',           // 顧客コード
        'name',                    // 利用者氏名
        'name_kana',               // 利用者カナ氏名
        'account_kana_name',       // 口座カナ氏名
        'account_holder_name',     // 口座人氏名
        'customer_number_new',     // 顧客番号 (new format)
        'ghana',
        'gender',
        'date_of_birth',
        'postal_code',
        'billing_postal_code',     // 請求先郵便番号
        'prefecture',
        'billing_prefecture',      // 請求先県名
        'city',
        'billing_city',            // 請求先市区町村
        'address_line',
        'billing_street_address',  // 請求先番地
        'billing_building',        // 請求先建物
        'address',
        'phone_number',
        'telephone_number',
        'mobile_number',
        'note',
        'payment_classification',  // 支払区分
        'payment_method',          // 支払方法
        'billing_amount',          // 請求金額
        'adjusted_billing_amount', // 調整請求額
        'consumption_tax',         // 消費税
        'bank_name',
        'branch_name',
        'account_name',
        'account_kana',
        'account_ghana',
        'account_number',
        'account_holder',
        'customer_number',
        'deposit_type',            // 預金種目
        'bank_note',
        'bank_code',
        'branch_code',
        'last_visit_date',
        'next_visit_date',
        'reception_date',
        'residence',
        'care_manager',
        'rental_fee',
        'assembly_delivery_costs',
        'district_court',
        'billable',
        'subject',
        'salesperson',
        'address_operator'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function setBankCodeAttribute($value)
    {
        $this->attributes['bank_code'] = $value;
        if ($value && strlen($value) === 4) {
            $this->attributes['bank_name'] = $this->getBankNameByCode($value);
        }
    }

    public function setBranchCodeAttribute($value)
    {
        $this->attributes['branch_code'] = $value;
        if ($value && strlen($value) === 3) {
            $this->attributes['branch_name'] = $this->getBranchNameByCode($value);
        }
    }

    private function getBankNameByCode($code)
    {
        $banks = [
            '0001' => 'みずほ銀行',
            '0005' => '三菱UFJ銀行',
            '0009' => '三井住友銀行',
            '0010' => 'りそな銀行',
            '0011' => '埼玉りそな銀行',
            '0012' => '千葉銀行',
            '0013' => '横浜銀行',
            '0014' => '静岡銀行',
            '0015' => '北陸銀行',
            '0016' => '山陰合同銀行',
            '0017' => '中国銀行',
            '0018' => '広島銀行',
            '0019' => '西日本シティ銀行',
            '0020' => '十八銀行',
            '0021' => '親和銀行',
            '0022' => '肥後銀行',
            '0023' => '鹿児島銀行',
            '0024' => '沖縄銀行',
            '0025' => '新生銀行',
            '0026' => 'あおぞら銀行',
            '0027' => '日本政策投資銀行',
            '0028' => '商工組合中央金庫',
            '0029' => '農林中央金庫',
            '0030' => 'ゆうちょ銀行'
        ];

        return $banks[$code] ?? null;
    }

    private function getBranchNameByCode($code)
    {
        $branches = [
            '001' => '東京本店',
            '002' => '大阪支店',
            '003' => '名古屋支店',
            '004' => '福岡支店',
            '005' => '札幌支店',
            '006' => '仙台支店',
            '007' => '広島支店',
            '008' => '新宿支店',
            '009' => '渋谷支店',
            '010' => '池袋支店',
            '011' => '横浜支店',
            '012' => '京都支店',
            '013' => '神戸支店',
            '014' => '千葉支店',
            '015' => 'さいたま支店',
            '016' => '静岡支店',
            '017' => '岡山支店',
            '018' => '熊本支店',
            '019' => '鹿児島支店',
            '020' => '沖縄支店'
        ];

        return $branches[$code] ?? null;
    }
}
