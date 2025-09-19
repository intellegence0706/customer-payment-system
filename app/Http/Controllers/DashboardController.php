<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        [$dateFrom, $dateTo] = $this->resolveDateRange($request);

        $stats = $this->getStatisticsForRange($dateFrom, $dateTo);
        $monthlyData = $this->getMonthlyPaymentDataForRange($dateFrom, $dateTo);
        $recentPayments = $this->getRecentPaymentsForRange($dateFrom, $dateTo);
        $topBanks = $this->getTopBanks();
        $topCustomers = $this->getTopCustomersForRange($dateFrom, $dateTo);

        return view('dashboard.index', [
            'stats' => $stats,
            'monthlyData' => $monthlyData,
            'recentPayments' => $recentPayments,
            'topBanks' => $topBanks,
            'topCustomers' => $topCustomers,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }

    private function getStatisticsForRange(Carbon $dateFrom, Carbon $dateTo): array
    {
        $totalCustomers = Customer::count();
        $activeCustomersInRange = Customer::whereHas('payments', function ($query) use ($dateFrom, $dateTo) {
            $query->whereBetween('payment_date', [$dateFrom, $dateTo]);
        })->count();

        $rangeTotalAmount = Payment::whereBetween('payment_date', [$dateFrom, $dateTo])
            ->where('status', 'completed')  
            ->sum('amount');
        $rangePaymentCount = Payment::whereBetween('payment_date', [$dateFrom, $dateTo])
            ->where('status', 'completed')
            ->count();
        $avgPaymentAmount = $rangePaymentCount > 0 ? round($rangeTotalAmount / $rangePaymentCount, 2) : 0;

        $growthRate = $this->calculateRangeGrowthRate($dateFrom, $dateTo);

        return [
            'total_customers' => $totalCustomers,
            'active_customers_in_range' => $activeCustomersInRange,
            'range_total_amount' => $rangeTotalAmount,
            'range_payment_count' => $rangePaymentCount,
            'avg_payment_amount' => $avgPaymentAmount,
            'growth_rate' => $growthRate,
        ];
    }

    private function getMonthlyPaymentDataForRange(Carbon $dateFrom, Carbon $dateTo)
    {
        return Payment::select(
            DB::raw('YEAR(payment_date) as year'),
            DB::raw('MONTH(payment_date) as month'),
            DB::raw('SUM(amount) as total_amount'),
            DB::raw('COUNT(*) as payment_count')
        )
        ->whereBetween('payment_date', [$dateFrom, $dateTo])
        ->where('status', 'completed')
        ->groupBy('year', 'month')
        ->orderBy('year', 'asc')
        ->orderBy('month', 'asc')
        ->get();
    }

    private function getRecentPaymentsForRange(Carbon $dateFrom, Carbon $dateTo)
    {
        return Payment::with('customer')
            ->whereBetween('payment_date', [$dateFrom, $dateTo])
            ->orderBy('payment_date', 'desc')
            ->limit(50)
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

    private function calculateRangeGrowthRate(Carbon $dateFrom, Carbon $dateTo): float
    {
        $current = Payment::whereBetween('payment_date', [$dateFrom, $dateTo])
            ->where('status', 'completed')
            ->sum('amount');

        $days = $dateFrom->diffInDays($dateTo) + 1;
        $previousFrom = (clone $dateFrom)->subDays($days);
        $previousTo = (clone $dateFrom)->subDay();

        $previous = Payment::whereBetween('payment_date', [$previousFrom, $previousTo])
            ->where('status', 'completed')
            ->sum('amount');

        if ($previous == 0) {
            return $current > 0 ? 100.0 : 0.0;
        }

        return round((($current - $previous) / $previous) * 100, 2);
    }

    private function getTopCustomersForRange(Carbon $dateFrom, Carbon $dateTo)
    {
        return Payment::select('customer_id', DB::raw('SUM(amount) as total_amount'))
            ->with('customer')
            ->whereBetween('payment_date', [$dateFrom, $dateTo])
            ->where('status', 'completed')
            ->groupBy('customer_id')
            ->orderByDesc('total_amount')
            ->limit(5)
            ->get();
    }

    private function resolveDateRange(Request $request): array
    {
        $to = $request->query('to') ? Carbon::parse($request->query('to'))->endOfDay() : now()->endOfDay();
        $from = $request->query('from') ? Carbon::parse($request->query('from'))->startOfDay() : (clone $to)->subMonths(11)->startOfMonth();
        return [$from, $to];
    }
}
