<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        $reports = Report::with('user')->orderBy('created_at', 'desc')->paginate(20);
        return view('reports.index', compact('reports'));
    }

    public function create()
    {
        return view('reports.create');
    }

    public function generateCustomerReport(Request $request)
    {
        $validated = $request->validate([
            'format' => 'required|in:pdf,csv',
            'date_from' => 'nullable|date',
            'date_to' => 'nullable|date|after_or_equal:date_from',
            'gender' => 'nullable|in:male,female,other',
            'bank_name' => 'nullable|string',
        ]);

        $query = Customer::query();

        if ($validated['date_from']) {
            $query->where('created_at', '>=', $validated['date_from']);
        }

        if ($validated['date_to']) {
            $query->where('created_at', '<=', $validated['date_to']);
        }

        if ($validated['gender']) {
            $query->where('gender', $validated['gender']);
        }

        if ($validated['bank_name']) {
            $query->where('bank_name', 'like', "%{$validated['bank_name']}%");
        }

        $customers = $query->get();

        if ($validated['format'] === 'pdf') {
            return $this->generateCustomerPDF($customers, $validated);
        } else {
            return $this->generateCustomerCSV($customers, $validated);
        }
    }

    public function generatePaymentReport(Request $request)
    {
        $validated = $request->validate([
            'format' => 'required|in:pdf,csv',
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020',
            'status' => 'nullable|in:pending,completed,failed',
        ]);

        $payments = Payment::with('customer')
            ->where('payment_month', $validated['month'])
            ->where('payment_year', $validated['year']);

        if ($validated['status']) {
            $payments->where('status', $validated['status']);
        }

        $payments = $payments->get();

        if ($validated['format'] === 'pdf') {
            return $this->generatePaymentPDF($payments, $validated);
        } else {
            return $this->generatePaymentCSV($payments, $validated);
        }
    }

    private function generateCustomerPDF($customers, $parameters)
    {
        $pdf = PDF::loadView('reports.customers-pdf', compact('customers', 'parameters'));
        
        $filename = 'customer_report_' . date('Y-m-d_H-i-s') . '.pdf';
        
        // Save report record
        Report::create([
            'name' => 'Customer Report',
            'type' => 'customer_pdf',
            'parameters' => $parameters,
            'generated_by' => auth()->id(),
            'file_path' => $filename,
            'status' => 'completed',
        ]);

        return $pdf->download($filename);
    }

    private function generateCustomerCSV($customers, $parameters)
    {
        $filename = 'customer_report_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        // Save report record
        Report::create([
            'name' => 'Customer Report',
            'type' => 'customer_csv',
            'parameters' => $parameters,
            'generated_by' => auth()->id(),
            'file_path' => $filename,
            'status' => 'completed',
        ]);

        $callback = function() use ($customers) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'Customer Number', 'Name', 'Gender', 'Phone', 'Address',
                'Bank Name', 'Branch Name', 'Account Number', 'Created Date'
            ]);

            foreach ($customers as $customer) {
                fputcsv($file, [
                    $customer->customer_number,
                    $customer->name,
                    $customer->gender,
                    $customer->phone_number,
                    $customer->address,
                    $customer->bank_name,
                    $customer->branch_name,
                    $customer->account_number,
                    $customer->created_at->format('Y-m-d'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function generatePaymentPDF($payments, $parameters)
    {
        $pdf = PDF::loadView('reports.payments-pdf', compact('payments', 'parameters'));
        
        $filename = 'payment_report_' . $parameters['year'] . '_' . $parameters['month'] . '_' . date('Y-m-d_H-i-s') . '.pdf';
        
        Report::create([
            'name' => 'Payment Report',
            'type' => 'payment_pdf',
            'parameters' => $parameters,
            'generated_by' => auth()->id(),
            'file_path' => $filename,
            'status' => 'completed',
        ]);

        return $pdf->download($filename);
    }

    private function generatePaymentCSV($payments, $parameters)
    {
        $filename = 'payment_report_' . $parameters['year'] . '_' . $parameters['month'] . '_' . date('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        Report::create([
            'name' => 'Payment Report',
            'type' => 'payment_csv',
            'parameters' => $parameters,
            'generated_by' => auth()->id(),
            'file_path' => $filename,
            'status' => 'completed',
        ]);

        $callback = function() use ($payments) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, [
                'Customer Number', 'Customer Name', 'Payment Month', 'Payment Year',
                'Amount', 'Payment Date', 'Receipt Number', 'Status'
            ]);

            foreach ($payments as $payment) {
                fputcsv($file, [
                    $payment->customer->customer_number,
                    $payment->customer->name,
                    $payment->payment_month,
                    $payment->payment_year,
                    $payment->amount,
                    $payment->payment_date->format('Y-m-d'),
                    $payment->receipt_number,
                    $payment->status,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
