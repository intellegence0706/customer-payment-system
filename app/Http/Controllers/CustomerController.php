<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

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
            'bank_code' => 'nullable|string|size:4',
            'branch_code' => 'nullable|string|size:3',
            'account_name' => 'nullable|string|max:255',
            'account_ghana' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'account_holder' => 'nullable|string|max:255',
            'customer_number' => 'required|string|max:50|unique:customers',
            'bank_note' => 'nullable|string',
        ]);

        Customer::create($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Customer created successfully.');
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
            'bank_code' => 'nullable|string|size:4',
            'branch_code' => 'nullable|string|size:3',
            'account_name' => 'nullable|string|max:255',
            'account_ghana' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:50',
            'account_holder' => 'nullable|string|max:255',
            'customer_number' => 'required|string|max:50|unique:customers,customer_number,' . $customer->id,
            'bank_note' => 'nullable|string',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')
            ->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    public function exportCsv(Request $request)
    {
        $query = Customer::query();

        // Apply same filters as index
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

        $filename = 'customers_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($customers) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'Customer Number', 'Name', 'Ghana', 'Gender', 'Postal Code', 
                'Address', 'Phone Number', 'Note', 'Bank Name', 'Branch Name',
                'Account Name', 'Account Ghana', 'Account Number', 'Account Holder', 'Bank Note'
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
        $bankCode = $request->get('bank_code');
        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('BANK_API_KEY'),
        ])->get("https://api.bankcode-jp.com/v1/banks/{$bankCode}");

        if ($response->successful()) {
            $bankData = $response->json();
            return response()->json(['bank_name' => $bankData['bank_name']]);
        }
        return response()->json(['error' => 'Bank code not found'], 404);
    }

    public function getBranchName(Request $request)
    {
       $branchCode = $request->get("branch_code");
       $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . env('BANK_API_KEY'),
        ])->get("https://api.bankcode-jp.com/v1/branches/{$branchCode}");

        if ($response->successful()) {
            $branchData = $response->json();
            return response()->json(['branch_name' => $branchData['branch_name']]);

        }
        return response()->json(['error' => 'Branch code not found'], 404);
    }
}
