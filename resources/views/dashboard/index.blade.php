@extends('layouts.app')

@section('content')
<div class="dashboard-container">
    <!-- Hero Header -->
    <div class="dashboard-hero" data-aos="fade-down">
        <div class="hero-content">
            <div class="hero-text">
                <h1 class="hero-title">
                    <span class="title-icon">
                        <i class="fas fa-chart-line"></i>
                    </span>
                    ダッシュボード
                </h1>
            </div>
            <div class="hero-actions">
                <button type="button" class="btn-modern btn-refresh" onclick="refreshDashboard()">
                    <div class="btn-icon">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <span class="btn-text">更新</span>
                    <div class="btn-shine"></div>
                </button>
            </div>
        </div>
        <div class="hero-decoration">
            <div class="floating-elements">
                <div class="floating-icon" style="--delay: 0s">
                    <i class="fas fa-chart-bar"></i>
                </div>
                <div class="floating-icon" style="--delay: 1s">
                    <i class="fas fa-users"></i>
                </div>
                <div class="floating-icon" style="--delay: 2s">
                    <i class="fas fa-yen-sign"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Date Filter Section -->
    <div class="filter-section" data-aos="fade-up" data-aos-delay="200">
        <div class="filter-card">
            <div class="filter-header">
                <div class="filter-icon">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="filter-title">
                    <h3>期間フィルター</h3>
                    <p>データ表示期間を選択</p>
                </div>
            </div>
            <form method="GET" action="{{ route('dashboard') }}" class="filter-form">
                <div class="date-inputs">
                    <div class="input-group">
                        <div class="input-icon">
                            <i class="fas fa-play"></i>
                        </div>
                        <div class="input-content">
                            <label for="from" class="input-label">開始日</label>
                            <input type="date" id="from" name="from" class="modern-input"
                                value="{{ isset($dateFrom) ? $dateFrom->toDateString() : '' }}">
                        </div>
                    </div>
                    
                    <div class="date-separator">
                        <i class="fas fa-arrow-right"></i>
                    </div>
                    
                    <div class="input-group">
                        <div class="input-icon">
                            <i class="fas fa-stop"></i>
                        </div>
                        <div class="input-content">
                            <label for="to" class="input-label">終了日</label>
                            <input type="date" id="to" name="to" class="modern-input"
                                value="{{ isset($dateTo) ? $dateTo->toDateString() : '' }}">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-filter">
                        <div class="btn-filter-icon">
                            <i class="fas fa-filter"></i>
                        </div>
                        <span class="btn-filter-text">反映</span>
                        <div class="btn-filter-ripple"></div>
                    </button>
                </div>
                
                <div class="filter-info">
                    <div class="info-icon">
                        <i class="fas fa-info-circle"></i>
                    </div>
                    <span class="info-text">
                        現在の期間: 
                        <strong>{{ isset($dateFrom) ? $dateFrom->format('Y年m月d日') : '未設定' }}</strong> 
                        〜 
                        <strong>{{ isset($dateTo) ? $dateTo->format('Y年m月d日') : '未設定' }}</strong>
                    </span>
                </div>
            </form>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="kpi-grid" data-aos="fade-up" data-aos-delay="400">
        <div class="kpi-card customers-card" data-aos="zoom-in" data-aos-delay="500">
            <div class="kpi-background">
                <div class="kpi-pattern customers-pattern"></div>
            </div>
            <div class="kpi-content">
                <div class="kpi-icon customers-icon">
                    <i class="fas fa-users"></i>
                    <div class="icon-glow"></div>
                </div>
                <div class="kpi-info">
                    <div class="kpi-label">総顧客数</div>
                    <div class="kpi-value" data-value="{{ $stats['total_customers'] }}">
                        {{ number_format($stats['total_customers']) }}
                    </div>
                    <div class="kpi-trend">
                        <i class="fas fa-arrow-up"></i>
                        <span>全顧客</span>
                    </div>
                </div>
            </div>
            <div class="kpi-sparkle">
                <div class="sparkle" style="--delay: 0s"></div>
                <div class="sparkle" style="--delay: 0.5s"></div>
                <div class="sparkle" style="--delay: 1s"></div>
            </div>
        </div>

        <div class="kpi-card active-card" data-aos="zoom-in" data-aos-delay="600">
            <div class="kpi-background">
                <div class="kpi-pattern active-pattern"></div>
            </div>
            <div class="kpi-content">
                <div class="kpi-icon active-icon">
                    <i class="fas fa-user-check"></i>
                    <div class="icon-glow"></div>
                </div>
                <div class="kpi-info">
                    <div class="kpi-label">期間内アクティブ</div>
                    <div class="kpi-value" data-value="{{ $stats['active_customers_in_range'] }}">
                        {{ number_format($stats['active_customers_in_range']) }}
                    </div>
                    <div class="kpi-trend">
                        <i class="fas fa-pulse"></i>
                        <span>アクティブ</span>
                    </div>
                </div>
            </div>
            <div class="kpi-sparkle">
                <div class="sparkle" style="--delay: 0.2s"></div>
                <div class="sparkle" style="--delay: 0.7s"></div>
                <div class="sparkle" style="--delay: 1.2s"></div>
            </div>
        </div>

        <div class="kpi-card revenue-card" data-aos="zoom-in" data-aos-delay="700">
            <div class="kpi-background">
                <div class="kpi-pattern revenue-pattern"></div>
            </div>
            <div class="kpi-content">
                <div class="kpi-icon revenue-icon">
                    <i class="fas fa-yen-sign"></i>
                    <div class="icon-glow"></div>
                </div>
                <div class="kpi-info">
                    <div class="kpi-label">期間売上合計</div>
                    <div class="kpi-value currency" data-value="{{ $stats['range_total_amount'] }}">
                        ¥{{ number_format($stats['range_total_amount']) }}
                    </div>
                    <div class="kpi-trend">
                        <i class="fas fa-coins"></i>
                        <span>売上</span>
                    </div>
                </div>
            </div>
            <div class="kpi-sparkle">
                <div class="sparkle" style="--delay: 0.3s"></div>
                <div class="sparkle" style="--delay: 0.8s"></div>
                <div class="sparkle" style="--delay: 1.3s"></div>
            </div>
        </div>

        <div class="kpi-card growth-card" data-aos="zoom-in" data-aos-delay="800">
            <div class="kpi-background">
                <div class="kpi-pattern growth-pattern"></div>
            </div>
            <div class="kpi-content">
                <div class="kpi-icon growth-icon">
                    <i class="fas fa-chart-line"></i>
                    <div class="icon-glow"></div>
                </div>
                <div class="kpi-info">
                    <div class="kpi-label">成長率</div>
                    <div class="kpi-value percentage" data-value="{{ $stats['growth_rate'] }}">
                        {{ $stats['growth_rate'] }}%
                    </div>
                    <div class="kpi-trend">
                        <i class="fas fa-trending-up"></i>
                        <span>成長</span>
                    </div>
                </div>
            </div>
            <div class="kpi-sparkle">
                <div class="sparkle" style="--delay: 0.4s"></div>
                <div class="sparkle" style="--delay: 0.9s"></div>
                <div class="sparkle" style="--delay: 1.4s"></div>
            </div>
        </div>
    </div>

    <!-- Secondary KPI Cards -->
    <div class="secondary-kpi-grid" data-aos="fade-up" data-aos-delay="900">
        <div class="secondary-kpi-card payments-card" data-aos="slide-right" data-aos-delay="1000">
            <div class="secondary-kpi-header">
                <div class="secondary-kpi-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="secondary-kpi-title">
                    <h4>期間内入金件数</h4>
                    <p>Payment Transactions</p>
                </div>
            </div>
            <div class="secondary-kpi-value">
                <span class="value-number" data-value="{{ $stats['range_payment_count'] }}">
                    {{ number_format($stats['range_payment_count']) }}
                </span>
                <span class="value-unit">件</span>
            </div>
            <div class="secondary-kpi-chart">
                <div class="mini-chart payments-chart">
                    <div class="chart-bar" style="height: 60%"></div>
                    <div class="chart-bar" style="height: 80%"></div>
                    <div class="chart-bar" style="height: 45%"></div>
                    <div class="chart-bar" style="height: 90%"></div>
                    <div class="chart-bar" style="height: 70%"></div>
                </div>
            </div>
        </div>

        <div class="secondary-kpi-card average-card" data-aos="slide-left" data-aos-delay="1100">
            <div class="secondary-kpi-header">
                <div class="secondary-kpi-icon">
                    <i class="fas fa-calculator"></i>
                </div>
                <div class="secondary-kpi-title">
                    <h4>平均入金額</h4>
                    <p>Average Payment</p>
                </div>
            </div>
            <div class="secondary-kpi-value">
                <span class="currency-symbol">¥</span>
                <span class="value-number" data-value="{{ $stats['avg_payment_amount'] }}">
                    {{ number_format($stats['avg_payment_amount']) }}
                </span>
            </div>
            <div class="secondary-kpi-chart">
                <div class="mini-chart average-chart">
                    <div class="chart-line">
                        <svg viewBox="0 0 100 40">
                            <path d="M0,30 Q25,10 50,20 T100,15" stroke="url(#averageGradient)" stroke-width="2" fill="none"/>
                            <defs>
                                <linearGradient id="averageGradient" x1="0%" y1="0%" x2="100%" y2="0%">
                                    <stop offset="0%" style="stop-color:#667eea"/>
                                    <stop offset="100%" style="stop-color:#764ba2"/>
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-section" data-aos="fade-up" data-aos-delay="1200">
        <div class="charts-grid">
            <div class="chart-card main-chart-card" data-aos="fade-right" data-aos-delay="1300">
                <div class="chart-header">
                    <div class="chart-title">
                        <div class="chart-icon">
                            <i class="fas fa-chart-area"></i>
                        </div>
                        <div class="chart-info">
                            <h3>月次入金トレンド</h3>
                            <p>Monthly Payment Trends</p>
                        </div>
                    </div>
                    <div class="chart-controls">
                        <button class="chart-control-btn active" data-chart="line">
                            <i class="fas fa-chart-line"></i>
                        </button>
                        <button class="chart-control-btn" data-chart="bar">
                            <i class="fas fa-chart-bar"></i>
                        </button>
                    </div>
                </div>
                <div class="chart-body">
                    <div class="chart-container">
                        <canvas id="monthlyChart"></canvas>
                    </div>
                    <div class="chart-legend">
                        <div class="legend-item">
                            <div class="legend-color" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%)"></div>
                            <span>入金額</span>
                        </div>
                        <div class="legend-item">
                            <div class="legend-color" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%)"></div>
                            <span>件数</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="chart-card donut-chart-card" data-aos="fade-left" data-aos-delay="1400">
                <div class="chart-header">
                    <div class="chart-title">
                        <div class="chart-icon">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <div class="chart-info">
                            <h3>銀行別トップ</h3>
                            <p>Top Banks Distribution</p>
                        </div>
                    </div>
                    <div class="chart-stats">
                        <div class="stat-item">
                            <span class="stat-value">{{ count($topBanks) }}</span>
                            <span class="stat-label">銀行</span>
                        </div>
                    </div>
                </div>
                <div class="chart-body">
                    <div class="chart-container donut-container">
                        <canvas id="banksChart"></canvas>
                        <div class="donut-center">
                            <div class="center-value">{{ $topBanks->sum('customer_count') }}</div>
                            <div class="center-label">総顧客数</div>
                        </div>
                    </div>
                    <div class="donut-legend">
                        @foreach($topBanks as $index => $bank)
                            <div class="donut-legend-item">
                                <div class="legend-dot" style="background: {{ ['#667eea', '#f093fb', '#4facfe', '#43e97b', '#fa709a'][$index % 5] }}"></div>
                                <span class="legend-text">{{ $bank->bank_name }}</span>
                                <span class="legend-value">{{ $bank->customer_count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Customers Section -->
    <div class="top-customers-section" data-aos="fade-up" data-aos-delay="1500">
        <div class="section-header">
            <div class="section-title">
                <div class="section-icon">
                    <i class="fas fa-crown"></i>
                </div>
                <div class="section-info">
                    <h3>トップ顧客（期間）</h3>
                    <p>Top Performing Customers</p>
                </div>
            </div>
            <div class="section-stats">
                <div class="stat-chip">
                    <i class="fas fa-trophy"></i>
                    <span>{{ count($topCustomers) }}名</span>
                </div>
            </div>
        </div>
        <div class="top-customers-chart-card">
            <div class="chart-container full-width">
                <canvas id="topCustomersChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Recent Payments Section -->
    <div class="recent-payments-section" data-aos="fade-up" data-aos-delay="1600">
        <div class="payments-header">
            <div class="payments-title">
                <div class="payments-icon">
                    <i class="fas fa-history"></i>
                </div>
                <div class="payments-info">
                    <h3>最近の入金</h3>
                    <p>Recent Payment Transactions</p>
                </div>
            </div>
            <div class="payments-actions">
                <a href="{{ route('payments.index') }}" class="btn-view-all">
                    <span>すべて表示</span>
                    <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
        <div class="payments-table-container">
            <div class="modern-table">
                <div class="table-header">
                    <div class="header-cell">顧客</div>
                    <div class="header-cell">金額</div>
                    <div class="header-cell">入金日</div>
                    <div class="header-cell">状態</div>
                    <div class="header-cell">領収書番号</div>
                </div>
                <div class="table-body">
                    @foreach ($recentPayments as $payment)
                        <div class="table-row" data-aos="fade-left" data-aos-delay="{{ 1700 + $loop->index * 50 }}">
                            <div class="table-cell customer-cell">
                                <div class="customer-avatar">
                                    <i class="fas fa-user"></i>
                                </div>
                                <div class="customer-info">
                                    <span class="customer-name">{{ $payment->customer->user_name ?? 'N/A' }}</span>
                                    <span class="customer-code">#{{ $payment->customer->customer_number ?? 'N/A' }}</span>
                                </div>
                            </div>
                            <div class="table-cell amount-cell">
                                <div class="amount-display">
                                    <span class="currency">¥</span>
                                    <span class="amount">{{ number_format($payment->amount) }}</span>
                                </div>
                            </div>
                            <div class="table-cell date-cell">
                                <div class="date-display">
                                    <i class="fas fa-calendar"></i>
                                    <span>{{ optional($payment->payment_date)->format('Y-m-d') ?? 'N/A' }}</span>
                                </div>
                            </div>
                            <div class="table-cell status-cell">
                                <div class="status-badge status-{{ $payment->status }}">
                                    <div class="status-dot"></div>
                                    <span>{{ ucfirst($payment->status) }}</span>
                                </div>
                            </div>
                            <div class="table-cell receipt-cell">
                                @if($payment->receipt_number)
                                    <div class="receipt-number">
                                        <i class="fas fa-receipt"></i>
                                        <span>{{ $payment->receipt_number }}</span>
                                    </div>
                                @else
                                    <span class="no-receipt">-</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AOS Animation Library -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<style>
/* Modern Dashboard Styling */
.dashboard-container {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    padding: 0;
    margin: -1.5rem -1.5rem 0 -1.5rem;
}

/* Hero Section */
.dashboard-hero {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    padding: 3rem 2rem 2rem 2rem;
    position: relative;
    overflow: hidden;
    margin-bottom: 2rem;
}

.dashboard-hero::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 20"><circle fill="rgba(255,255,255,0.1)" cx="10" cy="10" r="3"/><circle fill="rgba(255,255,255,0.05)" cx="90" cy="10" r="5"/></svg>');
    opacity: 0.3;
}

.hero-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    z-index: 2;
}

.hero-title {
    font-size: 3rem;
    font-weight: 800;
    color: white;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.title-icon {
    width: 70px;
    height: 70px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    backdrop-filter: blur(10px);
}

.title-accent {
    font-size: 1.5rem;
    opacity: 0.8;
    font-weight: 400;
}

.hero-subtitle {
    color: rgba(255, 255, 255, 0.9);
    font-size: 1.2rem;
    margin: 0.5rem 0 0 0;
}

/* Modern Button */
.btn-modern {
    position: relative;
    padding: 1rem 2rem;
    background: rgba(255, 255, 255, 0.2);
    border: 1px solid rgba(255, 255, 255, 0.3);
    border-radius: 25px;
    color: white;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 1px;
    cursor: pointer;
    overflow: hidden;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.btn-modern:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    background: rgba(255, 255, 255, 0.3);
}

.btn-shine {
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
    transition: left 0.5s;
}

.btn-modern:hover .btn-shine {
    left: 100%;
}

/* Floating Elements */
.floating-elements {
    position: absolute;
    top: 20%;
    right: 10%;
    z-index: 1;
}

.floating-icon {
    position: absolute;
    width: 60px;
    height: 60px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: rgba(255, 255, 255, 0.7);
    animation: float 3s ease-in-out infinite;
    animation-delay: var(--delay);
    backdrop-filter: blur(10px);
}

.floating-icon:nth-child(1) { top: 0; left: 0; }
.floating-icon:nth-child(2) { top: 80px; left: 60px; }
.floating-icon:nth-child(3) { top: 40px; left: 120px; }

@keyframes float {
    0%, 100% { transform: translateY(0px); }
    50% { transform: translateY(-20px); }
}

/* Filter Section */
.filter-section {
    margin: 2rem;
}

.filter-card {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 20px;
    padding: 2rem;
    backdrop-filter: blur(20px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.filter-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 2rem;
}

.filter-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.filter-title h3 {
    margin: 0;
    font-weight: 700;
    color: #2c3e50;
}

.filter-title p {
    margin: 0;
    color: #6c757d;
    font-size: 0.9rem;
}

.date-inputs {
    display: flex;
    align-items: center;
    gap: 2rem;
    flex-wrap: wrap;
}

.input-group {
    display: flex;
    align-items: center;
    gap: 1rem;
    background: rgba(248, 249, 250, 0.8);
    padding: 1rem;
    border-radius: 15px;
    flex: 1;
    min-width: 200px;
}

.input-icon {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
}

.input-label {
    font-size: 0.8rem;
    color: #6c757d;
    font-weight: 600;
    margin-bottom: 0.25rem;
    display: block;
}

.modern-input {
    border: none;
    background: transparent;
    font-size: 1rem;
    font-weight: 600;
    color: #2c3e50;
    width: 100%;
}

.modern-input:focus {
    outline: none;
}

.date-separator {
    color: #667eea;
    font-size: 1.5rem;
    margin: 0 1rem;
}

.btn-filter {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
    padding: 1rem 2rem;
    border-radius: 25px;
    font-weight: 600;
    cursor: pointer;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
}

.btn-filter:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
}

.filter-info {
    margin-top: 1.5rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    background: rgba(102, 126, 234, 0.1);
    border-radius: 10px;
}

.info-icon {
    color: #667eea;
    font-size: 1.2rem;
}

.info-text {
    color: #2c3e50;
    font-weight: 500;
}

/* KPI Grid */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 2rem;
    margin: 2rem;
}

.kpi-card {
    position: relative;
    background: white;
    border-radius: 25px;
    padding: 2rem;
    overflow: hidden;
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

.kpi-card:hover {
    transform: translateY(-10px) scale(1.02);
    box-shadow: 0 25px 50px rgba(0,0,0,0.15);
}

.kpi-background {
    position: absolute;
    top: 0;
    right: 0;
    width: 100px;
    height: 100px;
    opacity: 0.1;
    overflow: hidden;
}

.kpi-pattern {
    width: 100%;
    height: 100%;
    border-radius: 50%;
}

.customers-pattern { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.active-pattern { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
.revenue-pattern { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
.growth-pattern { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }

.kpi-content {
    position: relative;
    z-index: 2;
    display: flex;
    align-items: center;
    gap: 1.5rem;
}

.kpi-icon {
    position: relative;
    width: 70px;
    height: 70px;
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
    flex-shrink: 0;
}

.customers-icon { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
.active-icon { background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); }
.revenue-icon { background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); }
.growth-icon { background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); }

.icon-glow {
    position: absolute;
    top: -5px;
    left: -5px;
    right: -5px;
    bottom: -5px;
    border-radius: 25px;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.kpi-card:hover .icon-glow {
    opacity: 0.3;
    animation: pulse-glow 2s infinite;
}

@keyframes pulse-glow {
    0%, 100% { transform: scale(1); opacity: 0.3; }
    50% { transform: scale(1.1); opacity: 0.1; }
}

.kpi-info {
    flex: 1;
}

.kpi-label {
    font-size: 0.9rem;
    color: #6c757d;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 0.5rem;
}

.kpi-value {
    font-size: 2.5rem;
    font-weight: 800;
    color: #2c3e50;
    margin-bottom: 0.5rem;
    line-height: 1;
}

.kpi-trend {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.8rem;
    color: #28a745;
    font-weight: 600;
}

/* Sparkle Animation */
.kpi-sparkle {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    pointer-events: none;
}

.sparkle {
    position: absolute;
    width: 4px;
    height: 4px;
    background: #667eea;
    border-radius: 50%;
    animation: sparkle 2s linear infinite;
    animation-delay: var(--delay);
}

.sparkle:nth-child(1) { top: 20%; left: 20%; }
.sparkle:nth-child(2) { top: 60%; left: 80%; }
.sparkle:nth-child(3) { top: 80%; left: 30%; }

@keyframes sparkle {
    0%, 100% { opacity: 0; transform: scale(0); }
    50% { opacity: 1; transform: scale(1); }
}

/* Secondary KPI Grid */
.secondary-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 2rem;
    margin: 2rem;
}

.secondary-kpi-card {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.secondary-kpi-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.secondary-kpi-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.secondary-kpi-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.3rem;
}

.secondary-kpi-title h4 {
    margin: 0;
    font-weight: 700;
    color: #2c3e50;
}

.secondary-kpi-title p {
    margin: 0;
    color: #6c757d;
    font-size: 0.85rem;
}

.secondary-kpi-value {
    font-size: 2rem;
    font-weight: 800;
    color: #2c3e50;
    margin-bottom: 1rem;
    display: flex;
    align-items: baseline;
    gap: 0.5rem;
}

.value-unit {
    font-size: 1rem;
    color: #6c757d;
    font-weight: 600;
}

.currency-symbol {
    font-size: 1.2rem;
    opacity: 0.7;
}

/* Mini Charts */
.mini-chart {
    height: 60px;
    display: flex;
    align-items: end;
    gap: 4px;
}

.chart-bar {
    flex: 1;
    background: linear-gradient(to top, #667eea, #764ba2);
    border-radius: 2px;
    animation: growBar 1s ease-out;
    animation-delay: calc(var(--index, 0) * 0.1s);
    animation-fill-mode: both;
}

@keyframes growBar {
    from { height: 0; }
    to { height: var(--height, 100%); }
}

.chart-line svg {
    width: 100%;
    height: 100%;
}

/* Charts Section */
.charts-section {
    margin: 2rem;
}

.charts-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 2rem;
}

.chart-card {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.chart-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.chart-header {
    padding: 2rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.chart-title {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.chart-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.3rem;
}

.chart-info h3 {
    margin: 0;
    font-weight: 700;
    color: #2c3e50;
}

.chart-info p {
    margin: 0;
    color: #6c757d;
    font-size: 0.85rem;
}

.chart-controls {
    display: flex;
    gap: 0.5rem;
}

.chart-control-btn {
    width: 40px;
    height: 40px;
    border: none;
    background: rgba(102, 126, 234, 0.1);
    border-radius: 10px;
    color: #667eea;
    cursor: pointer;
    transition: all 0.3s ease;
}

.chart-control-btn.active,
.chart-control-btn:hover {
    background: #667eea;
    color: white;
}

.chart-body {
    padding: 2rem;
}

.chart-container {
    position: relative;
    height: 300px;
    margin-bottom: 1rem;
}

.donut-container {
    position: relative;
}

.donut-center {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
}

.center-value {
    font-size: 2rem;
    font-weight: 800;
    color: #2c3e50;
}

.center-label {
    font-size: 0.8rem;
    color: #6c757d;
    font-weight: 600;
}

.chart-legend {
    display: flex;
    justify-content: center;
    gap: 2rem;
}

.legend-item {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    font-weight: 600;
    color: #2c3e50;
}

.legend-color {
    width: 20px;
    height: 12px;
    border-radius: 6px;
}

.donut-legend {
    margin-top: 1rem;
    space-y: 0.5rem;
}

.donut-legend-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.5rem 0;
}

.legend-dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.legend-text {
    flex: 1;
    font-weight: 500;
    color: #2c3e50;
}

.legend-value {
    font-weight: 700;
    color: #667eea;
}

/* Top Customers Section */
.top-customers-section {
    margin: 2rem;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.section-title {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.section-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.3rem;
}

.section-info h3 {
    margin: 0;
    font-weight: 700;
    color: white;
}

.section-info p {
    margin: 0;
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.9rem;
}

.stat-chip {
    background: rgba(255, 255, 255, 0.2);
    padding: 0.5rem 1rem;
    border-radius: 20px;
    color: white;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    backdrop-filter: blur(10px);
}

.top-customers-chart-card {
    background: white;
    border-radius: 20px;
    padding: 2rem;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.full-width {
    height: 400px;
}

/* Recent Payments Section */
.recent-payments-section {
    margin: 2rem;
}

.payments-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
}

.payments-title {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.payments-icon {
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.3rem;
}

.payments-info h3 {
    margin: 0;
    font-weight: 700;
    color: white;
}

.payments-info p {
    margin: 0;
    color: rgba(255, 255, 255, 0.8);
    font-size: 0.9rem;
}

.btn-view-all {
    background: rgba(255, 255, 255, 0.2);
    color: white;
    text-decoration: none;
    padding: 0.75rem 1.5rem;
    border-radius: 20px;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.3s ease;
    backdrop-filter: blur(10px);
}

.btn-view-all:hover {
    background: rgba(255, 255, 255, 0.3);
    transform: translateX(5px);
}

.payments-table-container {
    background: white;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.modern-table {
    width: 100%;
}

.table-header {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr 1fr;
    gap: 1rem;
    padding: 1.5rem 2rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    font-weight: 700;
    color: #2c3e50;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.table-body {
    padding: 1rem 0;
}

.table-row {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr 1fr 1fr;
    gap: 1rem;
    padding: 1rem 2rem;
    border-bottom: 1px solid rgba(0,0,0,0.05);
    transition: all 0.3s ease;
}

.table-row:hover {
    background: rgba(102, 126, 234, 0.05);
    transform: translateX(5px);
}

.table-cell {
    display: flex;
    align-items: center;
}

.customer-cell {
    gap: 1rem;
}

.customer-avatar {
    width: 40px;
    height: 40px;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    flex-shrink: 0;
}

.customer-info {
    display: flex;
    flex-direction: column;
}

.customer-name {
    font-weight: 600;
    color: #2c3e50;
}

.customer-code {
    font-size: 0.8rem;
    color: #6c757d;
}

.amount-display {
    display: flex;
    align-items: baseline;
    gap: 0.25rem;
    font-weight: 700;
    color: #28a745;
}

.currency {
    font-size: 0.9rem;
    opacity: 0.7;
}

.date-display {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #2c3e50;
    font-weight: 500;
}

.date-display i {
    color: #667eea;
}

.status-badge {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.status-completed {
    background: rgba(40, 167, 69, 0.1);
    color: #28a745;
}

.status-pending {
    background: rgba(255, 193, 7, 0.1);
    color: #ffc107;
}

.status-failed {
    background: rgba(220, 53, 69, 0.1);
    color: #dc3545;
}

.status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: currentColor;
}

.receipt-number {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    color: #2c3e50;
    font-weight: 500;
}

.receipt-number i {
    color: #667eea;
}

.no-receipt {
    color: #6c757d;
    font-style: italic;
}

/* Responsive Design */
@media (max-width: 1200px) {
    .charts-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .hero-content {
        flex-direction: column;
        text-align: center;
        gap: 2rem;
    }
    
    .hero-title {
        font-size: 2rem;
        flex-direction: column;
    }
    
    .date-inputs {
        flex-direction: column;
        align-items: stretch;
    }
    
    .kpi-grid {
        grid-template-columns: 1fr;
    }
    
    .secondary-kpi-grid {
        grid-template-columns: 1fr;
    }
    
    .table-header,
    .table-row {
        grid-template-columns: 1fr;
        gap: 0.5rem;
    }
    
    .table-cell {
        justify-content: space-between;
        padding: 0.5rem 0;
        border-bottom: 1px solid rgba(0,0,0,0.1);
    }
}

/* Animation Classes */
.fade-in {
    animation: fadeIn 0.8s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Number Animation */
.kpi-value[data-value] {
    animation: countUp 2s ease-out;
}

@keyframes countUp {
    from {
        opacity: 0;
        transform: scale(0.5);
    }
    to {
        opacity: 1;
        transform: scale(1);
    }
}
</style>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize AOS
            AOS.init({
                duration: 800,
                easing: 'ease-out-cubic',
                once: true,
                offset: 50
            });

            // Modern Chart Defaults
            Chart.defaults.font.family = "'Inter', system-ui, -apple-system, sans-serif";
            Chart.defaults.font.size = 12;
            Chart.defaults.color = '#6c757d';

            // Monthly Payment Trends Chart with Modern Styling
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
                        label: '入金額',
                        data: [
                            @foreach ($monthlyData as $data)
                                {{ $data->total_amount }},
                            @endforeach
                        ],
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#667eea',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 3,
                        pointRadius: 6,
                        pointHoverRadius: 8
                    }, {
                        label: '件数',
                        data: [
                            @foreach ($monthlyData as $data)
                                {{ $data->payment_count }},
                            @endforeach
                        ],
                        borderColor: '#f093fb',
                        backgroundColor: 'rgba(240, 147, 251, 0.1)',
                        borderWidth: 3,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#f093fb',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 3,
                        pointRadius: 6,
                        pointHoverRadius: 8,
                        yAxisID: 'y1'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        intersect: false,
                        mode: 'index'
                    },
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(255, 255, 255, 0.95)',
                            titleColor: '#2c3e50',
                            bodyColor: '#2c3e50',
                            borderColor: 'rgba(102, 126, 234, 0.2)',
                            borderWidth: 1,
                            cornerRadius: 10,
                            padding: 12,
                            displayColors: true,
                            boxPadding: 6
                        }
                    },
                    scales: {
                        x: {
                            grid: {
                                display: false
                            },
                            border: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    weight: '600'
                                },
                                color: '#6c757d'
                            }
                        },
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            grid: {
                                color: 'rgba(0,0,0,0.05)',
                                borderDash: [5, 5]
                            },
                            border: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    weight: '600'
                                },
                                color: '#6c757d',
                                callback: function(value) {
                                    return '¥' + value.toLocaleString();
                                }
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            grid: {
                                drawOnChartArea: false,
                            },
                            border: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    weight: '600'
                                },
                                color: '#6c757d'
                            }
                        }
                    },
                    animation: {
                        duration: 2000,
                        easing: 'easeOutQuart'
                    }
                }
            });

            // Modern Doughnut Chart for Banks
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
                            '#667eea',
                            '#f093fb', 
                            '#4facfe',
                            '#43e97b',
                            '#fa709a'
                        ],
                        borderWidth: 0,
                        hoverBorderWidth: 3,
                        hoverBorderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '60%',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(255, 255, 255, 0.95)',
                            titleColor: '#2c3e50',
                            bodyColor: '#2c3e50',
                            borderColor: 'rgba(102, 126, 234, 0.2)',
                            borderWidth: 1,
                            cornerRadius: 10,
                            padding: 12,
                            displayColors: true,
                            boxPadding: 6,
                            callbacks: {
                                label: function(context) {
                                    const label = context.label || '';
                                    const value = context.parsed;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = ((value / total) * 100).toFixed(1);
                                    return `${label}: ${value}名 (${percentage}%)`;
                                }
                            }
                        }
                    },
                    animation: {
                        animateRotate: true,
                        duration: 2000,
                        easing: 'easeOutQuart'
                    }
                }
            });

            // Modern Bar Chart for Top Customers
            const topCustomersCtx = document.getElementById('topCustomersChart').getContext('2d');
            const topCustomersChart = new Chart(topCustomersCtx, {
                type: 'bar',
                data: {
                    labels: [
                        @foreach ($topCustomers as $tc)
                            '{{ $tc->customer ? ($tc->customer->user_name ?? $tc->customer->name ?? 'Unknown') : 'Unknown' }}',
                        @endforeach
                    ],
                    datasets: [{
                        label: '合計入金額',
                        data: [
                            @foreach ($topCustomers as $tc)
                                {{ $tc->total_amount }},
                            @endforeach
                        ],
                        backgroundColor: function(context) {
                            const gradient = context.chart.ctx.createLinearGradient(0, 0, 0, 400);
                            gradient.addColorStop(0, '#667eea');
                            gradient.addColorStop(1, '#764ba2');
                            return gradient;
                        },
                        borderRadius: 8,
                        borderSkipped: false,
                        barThickness: 40
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            backgroundColor: 'rgba(255, 255, 255, 0.95)',
                            titleColor: '#2c3e50',
                            bodyColor: '#2c3e50',
                            borderColor: 'rgba(102, 126, 234, 0.2)',
                            borderWidth: 1,
                            cornerRadius: 10,
                            padding: 12,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return '¥' + context.parsed.x.toLocaleString();
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: {
                                color: 'rgba(0,0,0,0.05)',
                                borderDash: [5, 5]
                            },
                            border: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    weight: '600'
                                },
                                color: '#6c757d',
                                callback: function(value) {
                                    return '¥' + value.toLocaleString();
                                }
                            }
                        },
                        y: {
                            grid: {
                                display: false
                            },
                            border: {
                                display: false
                            },
                            ticks: {
                                font: {
                                    weight: '600'
                                },
                                color: '#2c3e50'
                            }
                        }
                    },
                    animation: {
                        duration: 2000,
                        easing: 'easeOutQuart'
                    }
                }
            });

            // Add dashboard container fade-in animation
            document.querySelector('.dashboard-container').classList.add('fade-in');

            // Animate KPI values with counting effect
            const kpiValues = document.querySelectorAll('.kpi-value[data-value]');
            kpiValues.forEach(element => {
                const targetValue = parseInt(element.getAttribute('data-value'));
                animateValue(element, 0, targetValue, 2000);
            });

            // Chart control buttons functionality
            const chartControlBtns = document.querySelectorAll('.chart-control-btn');
            chartControlBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    // Remove active class from all buttons
                    chartControlBtns.forEach(b => b.classList.remove('active'));
                    // Add active class to clicked button
                    this.classList.add('active');
                    
                    const chartType = this.getAttribute('data-chart');
                    if (chartType === 'bar') {
                        monthlyChart.config.type = 'bar';
                        monthlyChart.update('active');
                    } else {
                        monthlyChart.config.type = 'line';
                        monthlyChart.update('active');
                    }
                });
            });
        });

        // Number animation function
        function animateValue(element, start, end, duration) {
            const range = end - start;
            const increment = range / (duration / 16);
            let current = start;
            
            const timer = setInterval(() => {
                current += increment;
                if (current >= end) {
                    element.textContent = formatNumber(end, element);
                    clearInterval(timer);
                } else {
                    element.textContent = formatNumber(Math.floor(current), element);
                }
            }, 16);
        }

        // Format numbers based on element type
        function formatNumber(value, element) {
            if (element.classList.contains('currency')) {
                return '¥' + value.toLocaleString();
            } else if (element.classList.contains('percentage')) {
                return value + '%';
            } else {
                return value.toLocaleString();
            }
        }

        // Refresh dashboard function
        function refreshDashboard() {
            // Add loading animation
            const refreshBtn = document.querySelector('.btn-refresh');
            const icon = refreshBtn.querySelector('i');
            
            icon.style.animation = 'spin 1s linear infinite';
            
            setTimeout(() => {
                const url = new URL(window.location.href);
                url.searchParams.set('t', Date.now());
                window.location.href = url.toString();
            }, 500);
        }

        // Add spin animation for refresh button
        const style = document.createElement('style');
        style.textContent = `
            @keyframes spin {
                from { transform: rotate(0deg); }
                to { transform: rotate(360deg); }
            }
        `;
        document.head.appendChild(style);
    </script>
@endsection
