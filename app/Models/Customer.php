<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_code',           // 顧客コード
        'user_kana_name',          // 利用者カナ氏名
        'user_name',               // 利用者氏名
        'account_kana_name',       // 口座カナ氏名
        'account_holder_name',     // 口座人氏名
        'payment_classification',  // 支払区分
        'payment_method',          // 支払方法
        'billing_amount',          // 請求金額
        'collection_request_amount', // 徴収請求額
        'consumption_tax',         // 消費税
        'bank_number',             // 銀行番号
        'bank_name',               // 銀行名
        'branch_number',           // 支店番号
        'branch_name',             // 支店名
        'deposit_type',            // 預金種目
        'account_number',          // 口座番号
        'customer_number',         // 顧客番号
        'billing_postal_code',     // 請求先郵便番号
        'billing_prefecture',      // 請求先県名
        'billing_city',            // 請求先市区町村
        'billing_street',          // 請求先番地
        'billing_difference',      // 請求先差額
    ];

    protected $casts = [
        'billing_amount' => 'decimal:2',
        'collection_request_amount' => 'decimal:2',
        'consumption_tax' => 'decimal:2',
        'billing_difference' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Auto-populate bank name when bank number is set
    public function setBankNumberAttribute($value)
    {
        $this->attributes['bank_number'] = $value;
        if ($value && strlen($value) === 4) {
            $this->attributes['bank_name'] = $this->getBankNameByAPI($value);
        }
    }

    // Auto-populate branch name when branch number is set
    public function setBranchNumberAttribute($value)
    {
        $this->attributes['branch_number'] = $value;
        if ($value && strlen($value) === 3) {
            $this->attributes['branch_name'] = $this->getBranchNameByAPI($value);
        }
    }

    // Get bank name from cloud API
    private function getBankNameByAPI($code)
    {
        $cacheKey = "bank_name_{$code}";

        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }
        try {
            $bankName = $this->callBankAPI($code);            
            if ($bankName) {
                Cache::put($cacheKey, $bankName, now()->addDays(7));
                return $bankName;
            }
        } catch (\Exception $e) {
            Log::warning("Bank API call failed for code: {$code}", ['error' => $e->getMessage()]);
        }

        return null;
    }

    // Get branch name from cloud API
    private function getBranchNameByAPI($code)
    {
        $cacheKey = "branch_name_{$code}";
        
        // Check cache first
        if (Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        try {
            // Try multiple banking APIs for redundancy
            $branchName = $this->callBranchAPI($code);
            
            if ($branchName) {
                // Cache for 7 days
                Cache::put($cacheKey, $branchName, now()->addDays(7));
                return $branchName;
            }
        } catch (\Exception $e) {
            Log::warning("Branch API call failed for code: {$code}", ['error' => $e->getMessage()]);
        }

        return null;
    }

    // Call bank API to get bank name by code
    private function callBankAPI($code)
    {
        $config = config('banking.bankcode_jp');
        
        if (!$config['enabled']) {
            return null;
        }

        try {
            $endpoint = str_replace('{code}', $code, $config['endpoints']['banks']);
            $url = $config['base_url'] . $endpoint . '?apiKey=' . $config['api_key'];

            $response = Http::timeout($config['timeout'])->get($url);
            
            if ($response->successful()) {
                $data = $response->json();
                
                // API returns 'name' field
                if (isset($data['name'])) {
                    return $data['name'];
                }
            }
        } catch (\Exception $e) {
            Log::debug("Bank API failed for code {$code}: " . $e->getMessage());
        }

        return null;
    }

    // Call branch API to get branch name by code
    private function callBranchAPI($branchCode)
    {
        $config = config('banking.bankcode_jp');
        
        if (!$config['enabled'] || !$this->bank_number) {
            return null;
        }

        try {
            
            $endpoint = str_replace(['{bankCode}', '{branchCode}'], [$this->bank_number, $branchCode], $config['endpoints']['branches']);
            $url = $config['base_url'] . $endpoint . '?apiKey=' . $config['api_key'];

            $response = Http::timeout($config['timeout'])->get($url);
            
            if ($response->successful()) {
                $data = $response->json();
                
                if (is_array($data) && count($data) > 0 && isset($data[0]['name'])) {
                    return $data[0]['name'];
                }
            }
        } catch (\Exception $e) {
            Log::debug("Branch API failed for bank {$this->bank_number} branch {$branchCode}: " . $e->getMessage());
        }

        return null;
    }
}
