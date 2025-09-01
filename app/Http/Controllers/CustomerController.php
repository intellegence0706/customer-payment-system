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

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                  ->orWhere('user_kana_name', 'like', "%{$search}%")
                  ->orWhere('customer_number', 'like', "%{$search}%")
                  ->orWhere('customer_code', 'like', "%{$search}%")
                  ->orWhere('account_number', 'like', "%{$search}%");
            });
        }

        // Filter by customer code
        if ($request->filled('customer_code')) {
            $query->where('customer_code', 'like', "%{$request->get('customer_code')}%");
        }

        // Filter by payment classification
        if ($request->filled('payment_classification')) {
            $query->where('payment_classification', $request->get('payment_classification'));
        }

        // Filter by bank name
        if ($request->filled('bank_name')) {
            $query->where('bank_name', 'like', "%{$request->get('bank_name')}%");
        }

        if ($request->filled('branch_name')) {
            $query->where('branch_name', 'like', "%{$request->get('branch_name')}%");
        }

        $customers = $query->orderBy('user_name', 'asc')->paginate(20);

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
            // Basic customer information
            'customer_code' => 'nullable|string|max:50',
            'user_kana_name' => 'nullable|string|max:100',
            'user_name' => 'nullable|string|max:100',
            'account_kana_name' => 'nullable|string|max:100',
            'account_holder_name' => 'nullable|string|max:100',
            
            // Payment information
            'payment_classification' => 'nullable|string|max:10',
            'payment_method' => 'nullable|string|max:255',
            'billing_amount' => 'nullable|numeric|between:0,999999999.99',
            'collection_request_amount' => 'nullable|numeric|between:0,999999999.99',
            'consumption_tax' => 'nullable|numeric|between:0,999999999.99',
            
            // Banking information
            'bank_number' => 'nullable|string|max:10',
            'bank_name' => 'nullable|string|max:255',
            'branch_number' => 'nullable|string|max:10',
            'branch_name' => 'nullable|string|max:255',
            'deposit_type' => 'nullable|string|max:50',
            'account_number' => 'nullable|string|max:50',
            'customer_number' => 'required|string|max:50|unique:customers',
            
            // Billing address
            'billing_postal_code' => 'nullable|string|max:10',
            'billing_prefecture' => 'nullable|string|max:50',
            'billing_city' => 'nullable|string|max:100',
            'billing_street' => 'nullable|string|max:200',
            'billing_difference' => 'nullable|numeric|between:-999999999.99,999999999.99',
        ]);
        
        try {
            Customer::create($validated);
            
            return redirect()->route('customers.index')
                ->with('success', '顧客情報が正常に追加されました。');
                
        } catch (\Exception $e) {
            Log::error('Customer creation failed', [
                'error' => $e->getMessage(),
                'data' => $validated
            ]);
            
            return back()
                ->withInput()
                ->with('error', '顧客情報の追加に失敗しました。再度お試しください。');
        }
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
            // Basic customer information
            'customer_code' => 'nullable|string|max:50',
            'user_kana_name' => 'nullable|string|max:100',
            'user_name' => 'nullable|string|max:100',
            'account_kana_name' => 'nullable|string|max:100',
            'account_holder_name' => 'nullable|string|max:100',
            
            // Payment information
            'payment_classification' => 'nullable|string|max:10',
            'payment_method' => 'nullable|string|max:255',
            'billing_amount' => 'nullable|numeric|between:0,999999999.99',
            'collection_request_amount' => 'nullable|numeric|between:0,999999999.99',
            'consumption_tax' => 'nullable|numeric|between:0,999999999.99',
            
            // Banking information
            'bank_number' => 'nullable|string|max:10',
            'bank_name' => 'nullable|string|max:255',
            'branch_number' => 'nullable|string|max:10',
            'branch_name' => 'nullable|string|max:255',
            'deposit_type' => 'nullable|string|max:50',
            'account_number' => 'nullable|string|max:50',
            'customer_number' => 'required|string|max:50|unique:customers,customer_number,' . $customer->id,
            
            // Billing address
            'billing_postal_code' => 'nullable|string|max:10',
            'billing_prefecture' => 'nullable|string|max:50',
            'billing_city' => 'nullable|string|max:100',
            'billing_street' => 'nullable|string|max:200',
            'billing_difference' => 'nullable|numeric|between:-999999999.99,999999999.99',
        ]);

        try {
            $customer->update($validated);
            
            return redirect()->route('customers.index')
                ->with('success', '顧客情報が正常に更新されました。');
                
        } catch (\Exception $e) {
            Log::error('Customer update failed', [
                'customer_id' => $customer->id,
                'error' => $e->getMessage(),
                'data' => $validated
            ]);
            
            return back()
                ->withInput()
                ->with('error', '顧客情報の更新に失敗しました。再度お試しください。');
        }
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')
            ->with('success', '顧客情報が正常に削除されました。');
    }

    public function exportCsv(Request $request)
    {
        $query = Customer::query();

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                  ->orWhere('user_kana_name', 'like', "%{$search}%")
                  ->orWhere('customer_number', 'like', "%{$search}%")
                  ->orWhere('customer_code', 'like', "%{$search}%")
                  ->orWhere('account_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('customer_code')) {
            $query->where('customer_code', 'like', "%{$request->get('customer_code')}%");
        }

        if ($request->filled('payment_classification')) {
            $query->where('payment_classification', $request->get('payment_classification'));
        }

        if ($request->filled('bank_name')) {
            $query->where('bank_name', 'like', "%{$request->get('bank_name')}%");
        }

        if ($request->filled('branch_name')) {
            $query->where('branch_name', 'like', "%{$request->get('branch_name')}%");
        }

        $customers = $query->get();
        $filename = '顧客_' . date('Y-m-d_H-i-s') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($customers) {
            $file = fopen('php://output', 'w');
            
            // CSV header with all 22 parameters
            fputcsv($file, [
                '顧客コード',
                '利用者カナ氏名',
                '利用者氏名',
                '口座カナ氏名',
                '口座人氏名',
                '支払区分',
                '支払方法',
                '請求金額',
                '徴収請求額',
                '消費税',
                '銀行番号',
                '銀行名',
                '支店番号',
                '支店名',
                '預金種目',
                '口座番号',
                '顧客番号',
                '請求先郵便番号',
                '請求先県名',
                '請求先市区町村',
                '請求先番地',
                '請求先差額'
            ]);

            foreach ($customers as $customer) {
                fputcsv($file, [
                    $customer->customer_code,
                    $customer->user_kana_name,
                    $customer->user_name,
                    $customer->account_kana_name,
                    $customer->account_holder_name,
                    $customer->payment_classification,
                    $customer->payment_method,
                    $customer->billing_amount,
                    $customer->collection_request_amount,
                    $customer->consumption_tax,
                    $customer->bank_number,
                    $customer->bank_name,
                    $customer->branch_number,
                    $customer->branch_name,
                    $customer->deposit_type,
                    $customer->account_number,
                    $customer->customer_number,
                    $customer->billing_postal_code,
                    $customer->billing_prefecture,
                    $customer->billing_city,
                    $customer->billing_street,
                    $customer->billing_difference
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportXlsx(Request $request)
    {
        $query = Customer::query();

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('user_name', 'like', "%{$search}%")
                  ->orWhere('user_kana_name', 'like', "%{$search}%")
                  ->orWhere('customer_number', 'like', "%{$search}%")
                  ->orWhere('customer_code', 'like', "%{$search}%")
                  ->orWhere('account_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('customer_code')) {
            $query->where('customer_code', 'like', "%{$request->get('customer_code')}%");
        }

        if ($request->filled('payment_classification')) {
            $query->where('payment_classification', $request->get('payment_classification'));
        }

        if ($request->filled('bank_name')) {
            $query->where('bank_name', 'like', "%{$request->get('bank_name')}%");
        }

        if ($request->filled('branch_name')) {
            $query->where('branch_name', 'like', "%{$request->get('branch_name')}%");
        }

        $customers = $query->get();
        $filename = '顧客_' . date('Y-m-d_H-i-s') . '.xlsx';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set headers with all 22 parameters
        $headers = [
            'A1' => '顧客コード',
            'B1' => '利用者カナ氏名',
            'C1' => '利用者氏名',
            'D1' => '口座カナ氏名',
            'E1' => '口座人氏名',
            'F1' => '支払区分',
            'G1' => '支払方法',
            'H1' => '請求金額',
            'I1' => '徴収請求額',
            'J1' => '消費税',
            'K1' => '銀行番号',
            'L1' => '銀行名',
            'M1' => '支店番号',
            'N1' => '支店名',
            'O1' => '預金種目',
            'P1' => '口座番号',
            'Q1' => '顧客番号',
            'R1' => '請求先郵便番号',
            'S1' => '請求先県名',
            'T1' => '請求先市区町村',
            'U1' => '請求先番地',
            'V1' => '請求先差額'
        ];

        foreach ($headers as $cell => $value) {
            $sheet->setCellValue($cell, $value);
        }

        // Add data rows
        $row = 2;
        foreach ($customers as $customer) {
            $sheet->setCellValue('A' . $row, $customer->customer_code);
            $sheet->setCellValue('B' . $row, $customer->user_kana_name);
            $sheet->setCellValue('C' . $row, $customer->user_name);
            $sheet->setCellValue('D' . $row, $customer->account_kana_name);
            $sheet->setCellValue('E' . $row, $customer->account_holder_name);
            $sheet->setCellValue('F' . $row, $customer->payment_classification);
            $sheet->setCellValue('G' . $row, $customer->payment_method);
            $sheet->setCellValue('H' . $row, $customer->billing_amount);
            $sheet->setCellValue('I' . $row, $customer->collection_request_amount);
            $sheet->setCellValue('J' . $row, $customer->consumption_tax);
            $sheet->setCellValue('K' . $row, $customer->bank_number);
            $sheet->setCellValue('L' . $row, $customer->bank_name);
            $sheet->setCellValue('M' . $row, $customer->branch_number);
            $sheet->setCellValue('N' . $row, $customer->branch_name);
            $sheet->setCellValue('O' . $row, $customer->deposit_type);
            $sheet->setCellValue('P' . $row, $customer->account_number);
            $sheet->setCellValue('Q' . $row, $customer->customer_number);
            $sheet->setCellValue('R' . $row, $customer->billing_postal_code);
            $sheet->setCellValue('S' . $row, $customer->billing_prefecture);
            $sheet->setCellValue('T' . $row, $customer->billing_city);
            $sheet->setCellValue('U' . $row, $customer->billing_street);
            $sheet->setCellValue('V' . $row, $customer->billing_difference);
            $row++;
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempFile = tempnam(sys_get_temp_dir(), 'customers_export');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename)->deleteFileAfterSend();
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        try {
            $file = $request->file('file');
            $extension = $file->getClientOriginalExtension();

            if ($extension === 'csv') {
                $this->importCsv($file);
            } else {
                $this->importExcel($file);
            }

            return redirect()->route('customers.index')
                ->with('success', '顧客データのインポートが完了しました。');

        } catch (\Exception $e) {
            Log::error('Customer import error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'インポート中にエラーが発生しました: ' . $e->getMessage());
        }
    }

    private function importCsv($file)
    {
        $handle = fopen($file->getPathname(), 'r');
        $headers = fgetcsv($handle); // Skip header row
        
        while (($data = fgetcsv($handle)) !== false) {
            if (count($data) >= 22) {
                $this->createCustomerFromArray($data);
            }
        }
        
        fclose($handle);
    }

    private function importExcel($file)
    {
        $spreadsheet = IOFactory::load($file->getPathname());
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
        
        // Skip header row
        array_shift($rows);
        
        foreach ($rows as $row) {
            if (count($row) >= 22) {
                $this->createCustomerFromArray($row);
            }
        }
    }

    private function createCustomerFromArray($data)
    {
        $customerData = [
            'customer_code' => $data[0] ?? null,
            'user_kana_name' => $data[1] ?? null,
            'user_name' => $data[2] ?? null,
            'account_kana_name' => $data[3] ?? null,
            'account_holder_name' => $data[4] ?? null,
            'payment_classification' => $data[5] ?? null,
            'payment_method' => $data[6] ?? null,
            'billing_amount' => $data[7] ?? null,
            'collection_request_amount' => $data[8] ?? null,
            'consumption_tax' => $data[9] ?? null,
            'bank_number' => $data[10] ?? null,
            'bank_name' => $data[11] ?? null,
            'branch_number' => $data[12] ?? null,
            'branch_name' => $data[13] ?? null,
            'deposit_type' => $data[14] ?? null,
            'account_number' => $data[15] ?? null,
            'customer_number' => $data[16] ?? null,
            'billing_postal_code' => $data[17] ?? null,
            'billing_prefecture' => $data[18] ?? null,
            'billing_city' => $data[19] ?? null,
            'billing_street' => $data[20] ?? null,
            'billing_difference' => $data[21] ?? null,
        ];

        // Only create if customer_number exists and is unique
        if (!empty($customerData['customer_number'])) {
            Customer::updateOrCreate(
                ['customer_number' => $customerData['customer_number']],
                $customerData
            );
        }
    }

    /**
     * Get bank name from cloud API
     */
    public function getBankName(Request $request)
    {
        $request->validate([
            'bank_code' => 'required|string|size:4|regex:/^\d{4}$/'
        ]);

        $bankCode = $request->get('bank_code');
        $cacheKey = "bank_name_{$bankCode}";
        
        if (Cache::has($cacheKey)) {
            return response()->json([
                'bank_name' => Cache::get($cacheKey),
                'cached' => true,
                'source' => 'cache'
            ]);
        }

        try {
            $bankName = $this->callBankAPI($bankCode);
            
            if ($bankName) {
                // Cache for 7 days
                Cache::put($cacheKey, $bankName, now()->addDays(7));
                
                return response()->json([
                    'bank_name' => $bankName,
                    'cached' => false,
                    'source' => 'api'
                ]);
            }

            return response()->json([
                'bank_name' => null,
                'error' => 'Bank code not found',
                'cached' => false,
                'source' => 'api'
            ], 404);

        } catch (\Exception $e) {
            Log::warning("Bank API call failed for code: {$bankCode}", ['error' => $e->getMessage()]);
            
            return response()->json([
                'bank_name' => null,
                'error' => 'API service unavailable',
                'cached' => false,
                'source' => 'api'
            ], 503);
        }
    }

    /**
     * Get branch name from cloud API
     */
    public function getBranchName(Request $request)
    {
        $request->validate([
            'bank_code' => 'required|string|size:4|regex:/^\d{4}$/',
            'branch_code' => 'required|string|size:3|regex:/^\d{3}$/'
        ]);

        $bankCode = $request->get('bank_code');
        $branchCode = $request->get('branch_code');
        $cacheKey = "branch_name_{$bankCode}_{$branchCode}";

        // Check cache first
        if (Cache::has($cacheKey)) {
            return response()->json([
                'branch_name' => Cache::get($cacheKey),
                'cached' => true,
                'source' => 'cache'
            ]);
        }

        try {
            $branchName = $this->callBranchAPI($bankCode, $branchCode);
            
            if ($branchName) {
                // Cache for 7 days
                Cache::put($cacheKey, $branchName, now()->addDays(7));
                
                return response()->json([
                    'branch_name' => $branchName,
                    'cached' => false,
                    'source' => 'api'
                ]);
            }

            return response()->json([
                'branch_name' => null,
                'error' => 'Branch code not found',
                'cached' => false,
                'source' => 'api'
            ], 404);
                
        } catch (\Exception $e) {
            Log::warning("Branch API call failed for bank: {$bankCode}, branch: {$branchCode}", ['error' => $e->getMessage()]);
            
            return response()->json([
                'branch_name' => null,
                'error' => 'API service unavailable',
                'cached' => false,
                'source' => 'api'
            ], 503);
        }
    }


    /**
     * Call bank API to get bank name by code
     */
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

    /**
     * Call branch API to get branch name by code
     */
    private function callBranchAPI($bankCode, $branchCode)
    {
        $config = config('banking.bankcode_jp');
        
        if (!$config['enabled']) {
            return null;
        }
        try {
            // Branch API requires both bank code and branch code
            $endpoint = str_replace(['{bankCode}', '{branchCode}'], [$bankCode, $branchCode], $config['endpoints']['branches']);
            $url = $config['base_url'] . $endpoint . '?apiKey=' . $config['api_key'];

            $response = Http::timeout($config['timeout'])->get($url);
            
            if ($response->successful()) {
                $data = $response->json();
                
                // API returns an array, get the first item's name
                if (is_array($data) && count($data) > 0 && isset($data[0]['name'])) {
                    return $data[0]['name'];
                }
            }
        } catch (\Exception $e) {
            Log::debug("Branch API failed for bank {$bankCode} branch {$branchCode}: " . $e->getMessage());
        }

        return null;
    }
}
