<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PostcardController extends Controller
{
    public function generatePostcardPDF(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|between:1,12',
            'year' => 'required|integer|min:2020',
        ]);

        $currentMonth = $request->month;
        $currentYear = $request->year;
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
                'customer' => $customer,
                'current_payment' => $currentPayment,
                'previous_payment' => $previousPayment,
                'current_month_name' => date('F Y', mktime(0, 0, 0, $currentMonth, 1, $currentYear)),
                'previous_month_name' => date('F Y', mktime(0, 0, 0, $previousMonth, 1, $previousYear)),
            ];
        }

        $pdf = PDF::loadView('postcards.pdf', compact('postcardData', 'currentMonth', 'currentYear'))
                 ->setPaper('A4', 'portrait');
        
        $filename = "postcards_{$currentYear}_{$currentMonth}_" . date('Y-m-d_H-i-s') . '.pdf';
        
        return $pdf->download($filename);
    }
}
