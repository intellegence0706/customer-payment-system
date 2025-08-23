<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        // Search functional
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('customer_number', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhere('account_number', 'like', "%{$search}%");
            });
        }

        // Filter by gender
        if ($request->filled('gender')) {
            $query->where('gender', $request->get('gender'));
        }

        if ($request->filled('bank_name')) {
            $query->where('bank_name', 'like', "%{$request->get('bank_name')}%");
        }

        $customers = $query->paginate(20);

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function showImportForm()
    {  
        Log::info("Hello!");
        return view('customers.import');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_kana' => 'nullable|string|max:255',
            'ghana' => 'nullable|string|max:255',
            'gender' => 'required|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'postal_code' => 'nullable|string|max:20',
            'prefecture' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'address_line' => 'nullable|string|max:255',
            'building' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'phone_number' => 'nullable|string|max:20',
            'telephone_number' => 'nullable|string|max:20',
            'mobile_number' => 'nullable|string|max:20',
            'note' => 'nullable|string',
            'bank_code' => 'nullable|digits:4',
            'branch_code' => 'nullable|digits:3',
            'account_name' => 'nullable|string|max:255',
            'account_kana' => 'nullable|string|max:255',
            'account_ghana' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'account_holder' => 'nullable|string|max:255',
            'deposit_type' => 'nullable|string|max:50',
            'customer_number' => 'required|string|max:50|unique:customers',
            'bank_note' => 'nullable|string',
            'last_visit_date' => 'nullable|date',
            'next_visit_date' => 'nullable|date',
            'reception_date' => 'nullable|date',
            'residence' => 'nullable|string|max:255',
            'care_manager' => 'nullable|string|max:255',
            'payment_method' => 'nullable|string|max:255',
            'rental_fee' => 'nullable|numeric',
            'assembly_delivery_costs' => 'nullable|numeric',
            'district_court' => 'nullable|string|max:255',
            'billable' => 'nullable|boolean',
            'subject' => 'nullable|string|max:255',
            'salesperson' => 'nullable|string|max:255',
            'address_operator' => 'nullable|string|max:255',
        ]);
        
        Customer::create($validated);
        return redirect()->route('customers.index')
            ->with('success', '顧客情報が正常に追加されました。');
    }

    public function show(Customer $customer)
    {
        $customer->load('payments');
        return view('customers.show', compact('customer'));
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'name_kana' => 'nullable|string|max:255',
            'ghana' => 'nullable|string|max:255',
            'gender' => 'required|in:male,female,other',
            'date_of_birth' => 'nullable|date',
            'postal_code' => 'nullable|string|max:20',
            'prefecture' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'address_line' => 'nullable|string|max:255',
            'building' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'phone_number' => 'nullable|string|max:20',
            'telephone_number' => 'nullable|string|max:20',
            'mobile_number' => 'nullable|string|max:20',
            'note' => 'nullable|string',
            'bank_code' => 'nullable|digits:4',
            'branch_code' => 'nullable|digits:3',
            'account_name' => 'nullable|string|max:255',
            'account_kana' => 'nullable|string|max:255',
            'account_ghana' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'account_holder' => 'nullable|string|max:255',
            'deposit_type' => 'nullable|string|max:50',
            'customer_number' => 'required|string|max:50|unique:customers,customer_number,' . $customer->id,
            'bank_note' => 'nullable|string',
            'last_visit_date' => 'nullable|date',
            'next_visit_date' => 'nullable|date',
            'reception_date' => 'nullable|date',
            'residence' => 'nullable|string|max:255',
            'care_manager' => 'nullable|string|max:255',
            'payment_method' => 'nullable|string|max:255',
            'rental_fee' => 'nullable|numeric',
            'assembly_delivery_costs' => 'nullable|numeric',
            'district_court' => 'nullable|string|max:255',
            'billable' => 'nullable|boolean',
            'subject' => 'nullable|string|max:255',
            'salesperson' => 'nullable|string|max:255',
            'address_operator' => 'nullable|string|max:255',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')
            ->with('success', '顧客情報が成果的に更新されました。');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')
            ->with('success', 'お客様の情報が成果的に削除されました。');
    }

    public function exportCsv(Request $request)
    {
        $query = Customer::query();

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('customer_number', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%")
                  ->orWhere('account_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('gender')) {
            $query->where('gender', $request->get('gender'));
        }
        if ($request->filled('bank_name')) {
            $query->where('bank_name', 'like', "%{$request->get('bank_name')}%");
        }

        $customers = $query->get();
        $filename = '顧客_' . date('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($customers) {
            
            echo "\xEF\xBB\xBF";
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'お客様番号', '氏名', 'ガーナ', '性別', '郵便番号', 
                '住所', '電話番号', 'メモ', '銀行名', '支店名',
                '口座名義', 'ガーナ口座', '口座番号', '口座名義人', '銀行メモ'
            ]);
            foreach ($customers as $customer) {
                fputcsv($file, [
                    $customer->customer_number,
                    $customer->name,
                    $customer->ghana,
                    $customer->gender,
                    $customer->postal_code,
                    $customer->address,
                    $customer->phone_number,
                    $customer->note,
                    $customer->bank_name,
                    $customer->branch_name,
                    $customer->account_name,
                    $customer->account_ghana,
                    $customer->account_number,
                    $customer->account_holder,
                    $customer->bank_note,
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function getBankName(Request $request)
    {
      
        $bankCode = $request->string('bank_code')->toString();
        if ($bankCode === '' || !ctype_digit($bankCode) || strlen($bankCode) !== 4) {
            return response()->json(['error' => 'bank_code must be 4 digits'], 422);
        }

        $apiKey = env('BANK_API_KEY');
        if (empty($apiKey)) {
            return response()->json(['bank_name' => null, 'cached' => false, 'disabled' => true], 200);
        }

        $cacheKey = "bank-name:{$bankCode}";
        if (Cache::has($cacheKey)) {
            return response()->json(['bank_name' => Cache::get($cacheKey), 'cached' => true]);
        }

        try {
            $response = Http::acceptJson()
                ->withHeaders(['Authorization' => 'Bearer ' . $apiKey])
                ->connectTimeout(1)
                ->timeout(3)
                ->retry(0, 0)
                ->get("https://api.bankcode-jp.com/v1/banks/{$bankCode}");

            if ($response->successful()) {
                $bankData = $response->json();
                $name = $bankData['bank_name'] ?? null;
                if ($name) {
                    Cache::put($cacheKey, $name, now()->addDays(7));
                    return response()->json(['bank_name' => $name, 'cached' => false]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Bank API failed', ['code' => $bankCode, 'error' => $e->getMessage()]);
        }
        return response()->json(['error' => 'Bank code not found or unavailable'], 404);
    }

    public function getBranchName(Request $request)
    {
        $branchCode = $request->string('branch_code')->toString();
        if ($branchCode === '' || !ctype_digit($branchCode) || strlen($branchCode) !== 3) {
            return response()->json(['error' => 'branch_code must be 3 digits'], 422);
        }
        
        $apiKey = env('BANK_API_KEY');
        if (empty($apiKey)) {
            return response()->json(['branch_name' => null, 'cached' => false, 'disabled' => true], 200);
        }
        $cacheKey = "branch-name:{$branchCode}";
        if (Cache::has($cacheKey)) {
            return response()->json(['branch_name' => Cache::get($cacheKey), 'cached' => true]);
        }
        try {
            $response = Http::acceptJson()
                ->withHeaders(['Authorization' => 'Bearer ' . $apiKey])
                ->connectTimeout(1)
                ->timeout(3)
                ->retry(0, 0)
                ->get("https://api.bankcode-jp.com/v1/branches/{$branchCode}");

            if ($response->successful()) {
                $branchData = $response->json();
                $name = $branchData['branch_name'] ?? null;
                if ($name) {
                    Cache::put($cacheKey, $name, now()->addDays(7));
                    return response()->json(['branch_name' => $name, 'cached' => false]);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Branch API failed', ['code' => $branchCode, 'error' => $e->getMessage()]);
        }
        return response()->json(['error' => 'Branch code not found or unavailable'], 404);
    }

    /**
     * Import customers from XLSX file
     * Maps XLSX columns to customer database fields
     */
    public function importXlsxCustomers(Request $request)
    {
        // Validate the request
        $request->validate([
            'customer_file' => 'required|file|mimes:xlsx|max:5120', // 5MB max
        ], [
            'customer_file.required' => '顧客データファイルを選択してください。',
            'customer_file.mimes' => 'ファイルはXLSX形式である必要があります。',
            'customer_file.max' => 'ファイルサイズは5MB以下である必要があります。',
        ]);

        try {
            $file = $request->file('customer_file');
            $path = $file->store('customer_imports');
            
            $spreadsheet = IOFactory::load(storage_path('app/' . $path));
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            if (empty($rows) || count($rows) < 2) {
                throw new \Exception('XLSXファイルが空であるか、データが不足しています。');
            }
            
            $header = array_shift($rows); // Remove header row
            $data = $rows; // Data rows only
            
            $imported = 0;
            $errors = [];
            $rowNumber = 1;
            
            // Log import attempt
            Log::info('Customer XLSX Import Started', [
                'file_path' => $path,
                'total_rows' => count($data),
                'header' => $header
            ]);
            
            DB::beginTransaction();
            
            try {
                foreach ($data as $rowData) {
                    $rowNumber++;
                    
                    // Skip empty rows
                    if (empty(array_filter($rowData))) {
                        continue;
                    }

                    $customerData = $this->mapXlsxToCustomerData($rowData, $header);
                    
                    if (empty($customerData['name'])) {
                        $errors[] = "{$rowNumber}行目: 利用者氏名が必要です。";
                        continue;
                    }
                    
                    if (empty($customerData['customer_number'])) {
                        $errors[] = "{$rowNumber}行目: 顧客番号が必要です。";
                        continue;
                    }
                    
                    $existingCustomer = Customer::where('customer_number', $customerData['customer_number'])->first();
                    if ($existingCustomer) {
                        $errors[] = "{$rowNumber}行目: 顧客番号'{$customerData['customer_number']}'は既に存在します。";
                        continue;
                    }
                    
                    Customer::create($customerData);
                    $imported++;
                }
                
                DB::commit();
                
                // Log successful import
                Log::info('Customer XLSX Import Completed', [
                    'imported' => $imported,
                    'error_count' => count($errors),
                    'errors' => array_slice($errors, 0, 5)
                ]);
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
            Storage::delete($path);
            
            // Generate result message
            $message = "{$imported}件の顧客データを正常に取り込みました。";
            if (!empty($errors)) {
                $message .= " エラー: " . implode('; ', array_slice($errors, 0, 10));
                if (count($errors) > 10) {
                    $message .= " (他" . (count($errors) - 10) . "件のエラー)";
                }
            }
            
            $alertType = !empty($errors) ? 'warning' : 'success';
            
            return redirect()->route('customers.index')
                ->with($alertType, $message);
                
        } catch (\Exception $e) {
            if (isset($path) && Storage::exists($path)) {
                Storage::delete($path);
            }
            
            Log::error('Customer XLSX Import Failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->withInput()
                ->withErrors(['customer_file' => 'インポートに失敗しました: ' . $e->getMessage()]);
        }
    }
    

    private function mapXlsxToCustomerData($rowData, $header)
    {
       
        $columnMap = [];
        foreach ($header as $index => $columnName) {
            $columnMap[$columnName] = $index;
        }
        
        $customerData = [
            'name' => $rowData[$columnMap['利用者氏名'] ?? 0] ?? null,                    // 利用者氏名
            'name_kana' => $rowData[$columnMap['利用者カナ氏名'] ?? 1] ?? null,           // 利用者カナ氏名
            'account_kana_name' => $rowData[$columnMap['口座カナ氏名'] ?? 2] ?? null,     // 口座カナ氏名
            'account_holder_name' => $rowData[$columnMap['口座人氏名'] ?? 3] ?? null,     // 口座人氏名
            'customer_number' => $rowData[$columnMap['顧客番号'] ?? 4] ?? null,           // 顧客番号
            'customer_code' => $rowData[$columnMap['顧客コード'] ?? 5] ?? null,           // 顧客コード
            'payment_classification' => $rowData[$columnMap['支払区分'] ?? 6] ?? null,     // 支払区分
            'payment_method' => $rowData[$columnMap['支払方法'] ?? 7] ?? null,            // 支払方法
            'billing_amount' => $this->parseAmount($rowData[$columnMap['請求金額'] ?? 8] ?? null), // 請求金額
            'adjusted_billing_amount' => $this->parseAmount($rowData[$columnMap['調整請求額'] ?? 9] ?? null), // 調整請求額
            'consumption_tax' => $this->parseAmount($rowData[$columnMap['消費税'] ?? 10] ?? null), // 消費税
            'bank_code' => $rowData[$columnMap['銀行番号'] ?? 11] ?? null,                // 銀行番号
            'bank_name' => $rowData[$columnMap['銀行名'] ?? 12] ?? null,                  // 銀行名
            'branch_code' => $rowData[$columnMap['支店番号'] ?? 13] ?? null,              // 支店番号
            'branch_name' => $rowData[$columnMap['支店名'] ?? 14] ?? null,                // 支店名
            'deposit_type' => $rowData[$columnMap['預金種目'] ?? 15] ?? null,             // 預金種目
            'account_number' => $rowData[$columnMap['口座番号'] ?? 16] ?? null,           // 口座番号
            'billing_postal_code' => $rowData[$columnMap['請求先郵便番号'] ?? 17] ?? null, // 請求先郵便番号
            'billing_prefecture' => $rowData[$columnMap['請求先県名'] ?? 18] ?? null,      // 請求先県名
            'billing_city' => $rowData[$columnMap['請求先市区町村'] ?? 19] ?? null,        // 請求先市区町村
            'billing_street_address' => $rowData[$columnMap['請求先番地'] ?? 20] ?? null,  // 請求先番地
            'billing_building' => $rowData[$columnMap['請求先建物'] ?? 21] ?? null,        // 請求先建物
            
          
            'gender' => 'other', 
            'ghana' => null,
            'date_of_birth' => null,
            'postal_code' => null,
            'prefecture' => null,
            'city' => null,
            'address_line' => null,
            'building' => null,
            'address' => null,
            'phone_number' => null,
            'telephone_number' => null,
            'mobile_number' => null,
            'note' => null,
            'account_name' => null,
            'account_kana' => null,
            'account_ghana' => null,
            'account_holder' => null,
            'bank_note' => null,
            'last_visit_date' => null,
            'next_visit_date' => null,
            'reception_date' => null,
            'residence' => null,
            'care_manager' => null,
            'rental_fee' => null,
            'assembly_delivery_costs' => null,
            'district_court' => null,
            'billable' => null,
            'subject' => null,
            'salesperson' => null,
            'address_operator' => null,
        ];
        
        return $customerData;
    }
    
 
    private function parseAmount($amount)
    {
        if (empty($amount)) {
            return null;
        }

        $cleanAmount = preg_replace('/[^0-9.\-]/', '', (string) $amount);
        return is_numeric($cleanAmount) ? (float) $cleanAmount : null;
    }
}
