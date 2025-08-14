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
        ])->whereHas('payments', function ($query) use ($currentMonth, $currentYear, $previousMonth, $previousYear) {
            $query->where(function ($q) use ($currentMonth, $currentYear) {
                $q->where('payment_month', $currentMonth)
                  ->where('payment_year', $currentYear);
            })->orWhere(function ($q) use ($previousMonth, $previousYear) {
                $q->where('payment_month', $previousMonth)
                  ->where('payment_year', $previousYear);
            });
        })->get();

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

        // Ensure DomPDF temp and font cache dirs exist
        $tempDir = config('dompdf.temp_dir', storage_path('app/dompdf_temp'));
        if (!is_dir($tempDir)) { @mkdir($tempDir, 0775, true); }
        $fontCacheDir = storage_path('fonts');
        if (!is_dir($fontCacheDir)) { @mkdir($fontCacheDir, 0775, true); }

        // Preload and cache JP font as base64 to avoid IO inside Blade
        $fontCandidates = [
            env('PDF_JP_FONT_PATH', resource_path('fonts/ipag.ttf')),
            resource_path('fonts/ipag.ttf'),
            public_path('fonts/ipag.ttf'),
            storage_path('app/fonts/ipag.ttf'),
        ];
        $resolvedFontPath = null;
        foreach ($fontCandidates as $candidate) {
            if (is_string($candidate) && file_exists($candidate)) {
                $resolvedFontPath = $candidate;
                break;
            }
        }
        $embeddedFontBase64 = $resolvedFontPath ? base64_encode(file_get_contents($resolvedFontPath)) : null;

        // Limit rows in one PDF to avoid huge memory usage; split per 200 postcards
        $chunks = array_chunk($postcardData, 200);

        $pdf = PDF::setOptions([
                    'defaultFont' => env('DOMPDF_DEFAULT_FONT', 'ipag'),
                    'dpi' => config('dompdf.dpi', 72),
                    'isHtml5ParserEnabled' => config('dompdf.isHtml5ParserEnabled', false),
                    'isRemoteEnabled' => config('dompdf.isRemoteEnabled', false),
                    'enable_font_subsetting' => true,
                    'fontCache' => $fontCacheDir,
                    'fontDir' => resource_path('fonts/'),
                    'chroot' => base_path(),
                    'tempDir' => $tempDir,
                ])
                ->loadView('postcards.pdf', [
                    'postcardData' => $chunks[0] ?? [],
                    'currentMonth' => $currentMonth,
                    'currentYear' => $currentYear,
                    'embeddedFontBase64' => $embeddedFontBase64,
                ])
                ->setPaper('A4', 'portrait');
        
        $filename = "はがき_{$currentYear}_{$currentMonth}_" . date('Y-m-d_H-i-s') . '.pdf';
        // If there are more than 200 postcards, inform user in filename; we can extend to zip later
        if (count($chunks) > 1) {
            $filename = "はがき_{$currentYear}_{$currentMonth}_part1_" . date('Y-m-d_H-i-s') . '.pdf';
        }

        return $pdf->download($filename);
    }
}
