<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::query();

        // Search functionality
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

        // Filter by bank
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

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'ghana' => 'nullable|string|max:255',
            'gender' => 'required|in:male,female,other',
            'postal_code' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'phone_number' => 'nullable|string|max:20',
            'note' => 'nullable|string',
            'bank_code' => 'nullable|digits:4',
            'branch_code' => 'nullable|digits:3',
            'account_name' => 'nullable|string|max:255',
            'account_ghana' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'account_holder' => 'nullable|string|max:255',
            'customer_number' => 'required|string|max:50|unique:customers',
            'bank_note' => 'nullable|string',
        ]);

        Log::info("Customer information", $validated);
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
            'ghana' => 'nullable|string|max:255',
            'gender' => 'required|in:male,female,other',
            'postal_code' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'phone_number' => 'nullable|string|max:20',
            'note' => 'nullable|string',
            'bank_code' => 'nullable|digits:4',
            'branch_code' => 'nullable|digits:3',
            'account_name' => 'nullable|string|max:255',
            'account_ghana' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'account_holder' => 'nullable|string|max:255',
            'customer_number' => 'required|string|max:50|unique:customers,customer_number,' . $customer->id,
            'bank_note' => 'nullable|string',
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

        $cacheKey = "bank-name:{$bankCode}";
        if (Cache::has($cacheKey)) {
            return response()->json(['bank_name' => Cache::get($cacheKey), 'cached' => true]);
        }

        try {
            $response = Http::acceptJson()
                ->withHeaders(['Authorization' => 'Bearer ' . env('BANK_API_KEY')])
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
        $cacheKey = "branch-name:{$branchCode}";
        if (Cache::has($cacheKey)) {
            return response()->json(['branch_name' => Cache::get($cacheKey), 'cached' => true]);
        }
        try {
            $response = Http::acceptJson()
                ->withHeaders(['Authorization' => 'Bearer ' . env('BANK_API_KEY')])
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
}
