@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2 neon-title" data-aos="fade-right">ダッシュボード</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <button type="button" class="btn btn-neon" onclick="refreshDashboard()" data-aos="zoom-in">
                    <i class="fas fa-sync-alt me-1"></i> 更新
                </button>
            </div>
        </div>
    </div>

    <div class="card mb-4" data-aos="fade-up">
        <div class="card-body">
            <form method="GET" action="{{ route('dashboard') }}" class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label for="from" class="form-label">開始日</label>
                    <input type="date" id="from" name="from" class="form-control"
                        value="{{ isset($dateFrom) ? $dateFrom->toDateString() : '' }}">
                </div>
                <div class="col-md-3">
                    <label for="to" class="form-label">終了日</label>
                    <input type="date" id="to" name="to" class="form-control"
                        value="{{ isset($dateTo) ? $dateTo->toDateString() : '' }}">
                </div>
                <div class="col-md-3">
                    <div class="d-grid">
                        <button type="submit" class="btn btn-neon">
                            <i class="fas fa-filter me-1"></i> 反映
                        </button>
                    </div>
                </div>
            </form>
        </div>
        <div class="card-footer small text-muted">
            期間: {{ isset($dateFrom) ? $dateFrom->format('Y-m-d') : '' }} 〜
            {{ isset($dateTo) ? $dateTo->format('Y-m-d') : '' }}
        </div>

    </div>

    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-primary shadow h-100 py-2" data-aos="fade-up" data-aos-delay="0">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                総顧客数
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['total_customers']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2" data-aos="fade-up" data-aos-delay="100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                期間内アクティブ
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['active_customers_in_range']) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2" data-aos="fade-up" data-aos-delay="200">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                期間売上合計
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['range_total_amount'], 2) }}
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fa-solid fa-yen-sign" style="font-size: 28px"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2" data-aos="fade-up" data-aos-delay="300">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                成長率
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $stats['growth_rate'] }}%
                            </div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-chart-line fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary KPIs -->
    <div class="row mb-4">
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-secondary shadow h-100 py-2" data-aos="fade-up">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">期間内入金件数</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['range_payment_count']) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-receipt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-md-6 mb-4">
            <div class="card border-left-dark shadow h-100 py-2" data-aos="fade-up" data-aos-delay="100">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-dark text-uppercase mb-1">平均入金額</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ number_format($stats['avg_payment_amount'], 2) }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calculator fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4" data-aos="fade-up">
        <div class="col-xl-8 col-lg-7">
            <div class="card shadow mb-4" data-aos="fade-up">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">月次入金トレンド</h6>
                </div>
                <div class="card-body">
                    <canvas id="monthlyChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>

        <div class="col-xl-4 col-lg-5">
            <div class="card shadow mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">銀行別トップ</h6>
                </div>
                <div class="card-body">
                    <canvas id="banksChart" width="400" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Customers -->
    <div class="row mb-4" data-aos="fade-up">
        <div class="col-xl-12">
            <div class="card shadow mb-4" data-aos="fade-up">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">トップ顧客（期間）</h6>
                </div>
                <div class="card-body">
                    <canvas id="topCustomersChart" height="120"></canvas>
                </div>
            </div>
        </div>

    </div>

    <!-- Recent Payments -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex justify-content-between align-items-center">
            <h6 class="m-0 font-weight-bold text-primary">最近の入金</h6>
            <a href="{{ route('payments.index') }}" class="btn btn-sm btn-outline-primary">すべて表示</a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>顧客</th>
                            <th>金額</th>
                            <th>入金日</th>
                            <th>状態</th>
                            <th>領収書番号</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recentPayments as $payment)
                            <tr>
                                <td>{{ $payment->customer->name }}</td>
                                <td>{{ number_format($payment->amount, 2) }}</td>
                                <td>{{ optional($payment->payment_date)->format('Y-m-d') }}</td>
                                <td>
                                    <span
                                        class="badge bg-{{ $payment->status == 'completed' ? 'success' : ($payment->status == 'pending' ? 'warning' : 'danger') }}">
                                        {{ ucfirst($payment->status) }}
                                    </span>
                                </td>
                                <td>{{ $payment->receipt_number }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Monthly Payment Trends Chart
        const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
        const monthlyChart = new Chart(monthlyCtx, {
            type: 'line',
            data: {
                labels: [
                    @foreach ($monthlyData as $data)
                        '{{ date('M Y', mktime(0, 0, 0, $data->month, 1, $data->year)) }}',
                    @endforeach
                ],
                datasets: [{
                    label: 'Payment Amount',
                    data: [
                        @foreach ($monthlyData as $data)
                            {{ $data->total_amount }},
                        @endforeach
                    ],
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }, {
                    label: 'Payment Count',
                    data: [
                        @foreach ($monthlyData as $data)
                            {{ $data->payment_count }},
                        @endforeach
                    ],
                    borderColor: 'rgb(255, 99, 132)',
                    backgroundColor: 'rgba(255, 99, 132, 0.2)',
                    tension: 0.1,
                    yAxisID: 'y1'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: {
                            drawOnChartArea: false,
                        },
                    }
                }
            }
        });

        // Top Banks Chart
        const banksCtx = document.getElementById('banksChart').getContext('2d');
        const banksChart = new Chart(banksCtx, {
            type: 'doughnut',
            data: {
                labels: [
                    @foreach ($topBanks as $bank)
                        '{{ $bank->bank_name }}',
                    @endforeach
                ],
                datasets: [{
                    data: [
                        @foreach ($topBanks as $bank)
                            {{ $bank->customer_count }},
                        @endforeach
                    ],
                    backgroundColor: [
                        '#FF6384',
                        '#36A2EB',
                        '#FFCE56',
                        '#4BC0C0',
                        '#9966FF'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false
            }
        });

        // Top Customers Chart
        const topCustomersCtx = document.getElementById('topCustomersChart').getContext('2d');
        const topCustomersChart = new Chart(topCustomersCtx, {
            type: 'bar',
            data: {
                labels: [
                    @foreach ($topCustomers as $tc)
                        '{{ $tc->customer ? $tc->customer->name : 'Unknown' }}',
                    @endforeach
                ],
                datasets: [{
                    label: '合計入金額',
                    data: [
                        @foreach ($topCustomers as $tc)
                            {{ $tc->total_amount }},
                        @endforeach
                    ],
                    backgroundColor: 'rgba(54, 162, 235, 0.6)'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        function refreshDashboard() {
            const url = new URL(window.location.href);
            // bump a cache-buster to ensure fresh data
            url.searchParams.set('t', Date.now());
            window.location.href = url.toString();
        }
    </script>
@endsection
