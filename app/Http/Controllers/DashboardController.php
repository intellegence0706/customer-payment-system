<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = $this->getStatistics();
        $monthlyData = $this->getMonthlyPaymentData();
        $recentPayments = $this->getRecentPayments();
        $topBanks = $this->getTopBanks();

        return view('dashboard.index', compact('stats', 'monthlyData', 'recentPayments', 'topBanks'));
    }

    private function getStatistics()
    {
        $currentMonth = now()->month;
        $currentYear = now()->year;
        $previousMonth = $currentMonth == 1 ? 12 : $currentMonth - 1;
        $previousYear = $currentMonth == 1 ? $currentYear - 1 : $currentYear;

        return [
            'total_customers' => Customer::count(),
            'active_customers' => Customer::whereHas('payments', function ($query) use ($currentMonth, $currentYear) {
                $query->where('payment_month', $currentMonth)
                      ->where('payment_year', $currentYear);
            })->count(),
            'current_month_payments' => Payment::where('payment_month', $currentMonth)
                                              ->where('payment_year', $currentYear)
                                              ->sum('amount'),
            'current_month_count' => Payment::where('payment_month', $currentMonth)
                                           ->where('payment_year', $currentYear)
                                           ->count(),
            'previous_month_payments' => Payment::where('payment_month', $previousMonth)
                                               ->where('payment_year', $previousYear)
                                               ->sum('amount'),
            'growth_rate' => $this->calculateGrowthRate($currentMonth, $currentYear, $previousMonth, $previousYear),
        ];
    }

    private function getMonthlyPaymentData()
    {
        return Payment::select(
            DB::raw('YEAR(payment_date) as year'),
            DB::raw('MONTH(payment_date) as month'),
            DB::raw('SUM(amount) as total_amount'),
            DB::raw('COUNT(*) as payment_count')
        )
        ->where('payment_date', '>=', now()->subMonths(12))
        ->groupBy('year', 'month')
        ->orderBy('year', 'desc')
        ->orderBy('month', 'desc')
        ->get();
    }

    private function getRecentPayments()
    {
        return Payment::with('customer')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
    }

    private function getTopBanks()
    {
        return Customer::select('bank_name', DB::raw('COUNT(*) as customer_count'))
            ->whereNotNull('bank_name')
            ->groupBy('bank_name')
            ->orderBy('customer_count', 'desc')
            ->limit(5)
            ->get();
    }

    private function calculateGrowthRate($currentMonth, $currentYear, $previousMonth, $previousYear)
    {
        $current = Payment::where('payment_month', $currentMonth)
                         ->where('payment_year', $currentYear)
                         ->sum('amount');
        
        $previous = Payment::where('payment_month', $previousMonth)
                          ->where('payment_year', $previousYear)
                          ->sum('amount');

        if ($previous == 0) return 0;
        
        return round((($current - $previous) / $previous) * 100, 2);
    }
}
