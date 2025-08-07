<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $query = Payment::with('customer');

        // Filter by month/year
        if ($request->filled('payment_month')) {
            $query->where('payment_month', $request->get('payment_month'));
        }

        if ($request->filled('payment_year')) {
            $query->where('payment_year', $request->get('payment_year'));
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $payments = $query->orderBy('payment_date', 'desc')->paginate(20);

        return view('payments.index', compact('payments'));
    }

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        return view('payments.create', compact('customers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'payment_month' => 'required|integer|between:1,12',
            'payment_year' => 'required|integer|min:2020',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'receipt_number' => 'nullable|string|max:50',
            'status' => 'required|in:pending,completed,failed',
            'notes' => 'nullable|string',
        ]);

        Payment::create($validated);

        return redirect()->route('payments.index')
            ->with('success', 'Payment recorded successfully.');
    }

    public function uploadMonthEndData(Request $request)
    {
        $request->validate([
            'payment_file' => 'required|file|mimes:csv,txt',
            'payment_month' => 'required|integer|between:1,12',
            'payment_year' => 'required|integer|min:2020',
        ]);

        $file = $request->file('payment_file');
        $path = $file->store('uploads');
        
        $handle = fopen(storage_path('app/' . $path), 'r');
        $header = fgetcsv($handle);
        
        $imported = 0;
        $errors = [];

        while (($data = fgetcsv($handle)) !== false) {
            try {
                $customer = Customer::where('customer_number', $data[0])->first();
                
                if (!$customer) {
                    $errors[] = "Customer not found: {$data[0]}";
                    continue;
                }

                Payment::create([
                    'customer_id' => $customer->id,
                    'payment_month' => $request->payment_month,
                    'payment_year' => $request->payment_year,
                    'amount' => $data[1] ?? 0,
                    'payment_date' => Carbon::parse($data[2] ?? now()),
                    'receipt_number' => $data[3] ?? null,
                    'status' => 'completed',
                    'notes' => 'Imported from month-end data',
                ]);

                $imported++;
            } catch (\Exception $e) {
                $errors[] = "Error processing row: " . implode(',', $data) . " - " . $e->getMessage();
            }
        }

        fclose($handle);
        Storage::delete($path);

        $message = "Imported {$imported} payments.";
        if (!empty($errors)) {
            $message .= " Errors: " . implode('; ', array_slice($errors, 0, 5));
        }

        return redirect()->route('payments.index')->with('success', $message);
    }

    public function generatePostcardData(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020',
        ]);

        $currentMonth = $request->month;
        $currentYear = $request->year;
        
        // Get previous month
        $previousMonth = $currentMonth == 1 ? 12 : $currentMonth - 1;
        $previousYear = $currentMonth == 1 ? $currentYear - 1 : $currentYear;

        $customers = Customer::with([
            'payments' => function ($query) use ($currentMonth, $currentYear, $previousMonth, $previousYear) {
                $query->where(function ($q) use ($currentMonth, $currentYear) {
                    $q->where('payment_month', $currentMonth)
                      ->where('payment_year', $currentYear);
                })->orWhere(function ($q) use ($previousMonth, $previousYear) {
                    $q->where('payment_month', $previousMonth)
                      ->where('payment_year', $previousYear);
                });
            }
        ])->get();

        $postcardData = [];

        foreach ($customers as $customer) {
            $currentPayment = $customer->payments->where('payment_month', $currentMonth)
                                                ->where('payment_year', $currentYear)
                                                ->first();
            
            $previousPayment = $customer->payments->where('payment_month', $previousMonth)
                                                 ->where('payment_year', $previousYear)
                                                 ->first();

            $postcardData[] = [
                'customer_name' => $customer->name,
                'customer_number' => $customer->customer_number,
                'address' => $customer->address,
                'postal_code' => $customer->postal_code,
                'current_month' => date('F Y', mktime(0, 0, 0, $currentMonth, 1, $currentYear)),
                'current_payment_amount' => $currentPayment ? $currentPayment->amount : 0,
                'current_payment_date' => $currentPayment ? $currentPayment->payment_date->format('Y-m-d') : null,
                'previous_month' => date('F Y', mktime(0, 0, 0, $previousMonth, 1, $previousYear)),
                'previous_receipt_number' => $previousPayment ? $previousPayment->receipt_number : null,
                'previous_payment_amount' => $previousPayment ? $previousPayment->amount : 0,
            ];
        }


        return view('payments.postcard-data', compact('postcardData', 'currentMonth', 'currentYear'));
    }

    public function exportPostcardCsv(Request $request)
    {
        $month = $request->get('month');
        $year = $request->get('year');
        
        // Regenerate postcard data
        $currentMonth = $month;
        $currentYear = $year;
        $previousMonth = $currentMonth == 1 ? 12 : $currentMonth - 1;
        $previousYear = $currentMonth == 1 ? $currentYear - 1 : $currentYear;

        $customers = Customer::with([
            'payments' => function ($query) use ($currentMonth, $currentYear, $previousMonth, $previousYear) {
                $query->where(function ($q) use ($currentMonth, $currentYear) {
                    $q->where('payment_month', $currentMonth)
                      ->where('payment_year', $currentYear);
                })->orWhere(function ($q) use ($previousMonth, $previousYear) {
                    $q->where('payment_month', $previousMonth)
                      ->where('payment_year', $previousYear);
                });
            }
        ])->get();

        $filename = "postcard_data_{$year}_{$month}_" . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($customers, $currentMonth, $currentYear, $previousMonth, $previousYear) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'Customer Name', 'Customer Number', 'Address', 'Postal Code',
                'Current Month', 'Current Payment Amount', 'Current Payment Date',
                'Previous Month', 'Previous Receipt Number', 'Previous Payment Amount'
            ]);

            foreach ($customers as $customer) {
                $currentPayment = $customer->payments->where('payment_month', $currentMonth)
                                                    ->where('payment_year', $currentYear)
                                                    ->first();
                
                $previousPayment = $customer->payments->where('payment_month', $previousMonth)
                                                     ->where('payment_year', $previousYear)
                                                     ->first();

                fputcsv($file, [
                    $customer->name,
                    $customer->customer_number,
                    $customer->address,
                    $customer->postal_code,
                    date('F Y', mktime(0, 0, 0, $currentMonth, 1, $currentYear)),
                    $currentPayment ? $currentPayment->amount : 0,
                    $currentPayment ? $currentPayment->payment_date->format('Y-m-d') : '',
                    date('F Y', mktime(0, 0, 0, $previousMonth, 1, $previousYear)),
                    $previousPayment ? $previousPayment->receipt_number : '',
                    $previousPayment ? $previousPayment->amount : 0,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
