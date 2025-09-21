@extends('layouts.app')

@section('content')
<div class="customer-detail-container">
    <!-- Hero Header -->
    <div class="hero-header mb-5">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <div class="hero-content">
                        <div class="d-flex align-items-center mb-3">
                            <div class="customer-avatar me-3">
                                <i class="fas fa-user-circle fa-3x text-primary"></i>
                            </div>
                            <div>
                                <h1 class="hero-title mb-1">
                                    {{ $customer->user_name ?? '名前未設定' }}
                                    <span class="hero-badge">
                                        <i class="fas fa-id-card me-1"></i>#{{ $customer->customer_number }}
                                    </span>
                                </h1>
                                <p class="hero-subtitle mb-0">
                                    <i class="fas fa-calendar-plus me-2"></i>
                                    登録日: {{ $customer->created_at->format('Y年m月d日') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="hero-actions">
                        <div class="btn-group-modern">
                            <a href="{{ route('customers.index') }}" class="btn btn-modern btn-secondary">
                                <i class="fas fa-arrow-left me-2"></i>
                                <span>一覧に戻る</span>
                            </a>
                            <a href="{{ route('customers.edit', $customer) }}" class="btn btn-modern btn-warning">
                                <i class="fas fa-edit me-2"></i>
                                <span>編集</span>
                            </a>
                            <form method="POST" action="{{ route('customers.destroy', $customer) }}" style="display: inline;" data-confirm-message="顧客情報を本当に削除しますか？">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-modern btn-danger">
                                    <i class="fas fa-trash me-2"></i>
                                    <span>削除</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Customer Information -->
        <div class="col-lg-8">
            <!-- Personal Information -->
            <div class="modern-card personal-info-card mb-4" data-aos="fade-up" data-aos-delay="100">
                <div class="modern-card-header">
                    <div class="card-header-content">
                        <div class="card-icon">
                            <i class="fas fa-user"></i>
                        </div>
                        <div class="card-title-group">
                            <h5 class="card-title">個人情報</h5>
                            <p class="card-subtitle">Personal Information</p>
                        </div>
                    </div>
                    <div class="card-header-action">
                        <span class="status-indicator status-active"></span>
                    </div>
                </div>
                <div class="modern-card-body">
                    <div class="info-grid">
                        <div class="info-item-modern">
                            <div class="info-icon">
                                <i class="fas fa-id-badge text-primary"></i>
                            </div>
                            <div class="info-content">
                                <label class="info-label">利用者氏名</label>
                                <p class="info-value">{{ $customer->user_name ?? '指定されていない' }}</p>
                            </div>
                        </div>
                        <div class="info-item-modern">
                            <div class="info-icon">
                                <i class="fas fa-font text-info"></i>
                            </div>
                            <div class="info-content">
                                <label class="info-label">利用者カナ氏名</label>
                                <p class="info-value">{{ $customer->user_kana_name ?? '指定されていない' }}</p>
                            </div>
                        </div>
                        <div class="info-item-modern">
                            <div class="info-icon">
                                <i class="fas fa-barcode text-success"></i>
                            </div>
                            <div class="info-content">
                                <label class="info-label">顧客コード</label>
                                <p class="info-value">{{ $customer->customer_code ?? '指定されていない' }}</p>
                            </div>
                        </div>
                        <div class="info-item-modern">
                            <div class="info-icon">
                                <i class="fas fa-user-tie text-warning"></i>
                            </div>
                            <div class="info-content">
                                <label class="info-label">口座人氏名</label>
                                <p class="info-value">{{ $customer->account_holder_name ?? '指定されていない' }}</p>
                            </div>
                        </div>
                        <div class="info-item-modern">
                            <div class="info-icon">
                                <i class="fas fa-signature text-secondary"></i>
                            </div>
                            <div class="info-content">
                                <label class="info-label">口座カナ氏名</label>
                                <p class="info-value">{{ $customer->account_kana_name ?? '指定されていない' }}</p>
                            </div>
                        </div>
                        <div class="info-item-modern">
                            <div class="info-icon">
                                <i class="fas fa-sticky-note text-primary"></i>
                            </div>
                            <div class="info-content">
                                <label class="info-label">備考</label>
                                <p class="info-value">{{ $customer->note ?? '未設定' }}</p>
                            </div>
                        </div>
                        <div class="info-item-modern">
                            <div class="info-icon">
                                <i class="fas fa-clipboard text-info"></i>
                            </div>
                            <div class="info-content">
                                <label class="info-label">メモ</label>
                                <p class="info-value">{{ $customer->memo ?? '未設定' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bank Information -->
            <div class="modern-card banking-info-card mb-4" data-aos="fade-up" data-aos-delay="200">
                <div class="modern-card-header">
                    <div class="card-header-content">
                        <div class="card-icon banking-icon">
                            <i class="fas fa-university"></i>
                        </div>
                        <div class="card-title-group">
                            <h5 class="card-title">銀行情報</h5>
                            <p class="card-subtitle">Banking Information</p>
                        </div>
                    </div>
                    <div class="card-header-action">
                        <div class="customer-number-badge">
                            <i class="fas fa-hashtag me-1"></i>{{ $customer->customer_number }}
                        </div>
                    </div>
                </div>
                <div class="modern-card-body">
                    <div class="info-grid banking-grid">
                        <div class="info-item-modern bank-code-item">
                            <div class="info-icon">
                                <i class="fas fa-building text-success"></i>
                            </div>
                            <div class="info-content">
                                <label class="info-label">銀行番号</label>
                                <p class="info-value bank-code">{{ $customer->bank_number ?? '指定されていない' }}</p>
                            </div>
                        </div>
                        <div class="info-item-modern">
                            <div class="info-icon">
                                <i class="fas fa-code-branch text-info"></i>
                            </div>
                            <div class="info-content">
                                <label class="info-label">支店番号</label>
                                <p class="info-value">{{ $customer->branch_number ?? '指定されていない' }}</p>
                            </div>
                        </div>
                        <div class="info-item-modern bank-name-item">
                            <div class="info-icon">
                                <i class="fas fa-landmark text-primary"></i>
                            </div>
                            <div class="info-content">
                                <label class="info-label">銀行名</label>
                                <p class="info-value bank-name">
                                    {{ $customer->bank_name ?? '指定されていない' }}
                                </p>
                            </div>
                        </div>
                        <div class="info-item-modern">
                            <div class="info-icon">
                                <i class="fas fa-map-marker-alt text-warning"></i>
                            </div>
                            <div class="info-content">
                                <label class="info-label">支店名</label>
                                <p class="info-value">{{ $customer->branch_name ?? '指定されていない' }}</p>
                            </div>
                        </div>
                        <div class="info-item-modern">
                            <div class="info-icon">
                                <i class="fas fa-piggy-bank text-secondary"></i>
                            </div>
                            <div class="info-content">
                                <label class="info-label">預金種目</label>
                                <p class="info-value">{{ $customer->deposit_type_label ?? '指定されていない' }}</p>
                            </div>
                        </div>
                        <div class="info-item-modern account-number-item">
                            <div class="info-icon">
                                <i class="fas fa-credit-card text-danger"></i>
                            </div>
                            <div class="info-content">
                                <label class="info-label">口座番号</label>
                                <p class="info-value account-number">
                                    @if ($customer->account_number)
                                    <span class="account-number-display">{{ $customer->account_number }}</span>
                                    @else
                                    指定されていない
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment Information -->
            <div class="modern-card payment-info-card mb-4" data-aos="fade-up" data-aos-delay="300">
                <div class="modern-card-header">
                    <div class="card-header-content">
                        <div class="card-icon payment-icon">
                            <i class="fas fa-credit-card"></i>
                        </div>
                        <div class="card-title-group">
                            <h5 class="card-title">支払情報</h5>
                            <p class="card-subtitle">Payment Information</p>
                        </div>
                    </div>
                    <div class="card-header-action">
                        <span class="status-indicator status-payment"></span>
                    </div>
                </div>
                <div class="modern-card-body">
                    <div class="payment-summary-row mb-4">
                        <div class="payment-method-display">
                            <div class="payment-method-icon">
                                <i class="fas fa-wallet"></i>
                            </div>
                            <div class="payment-method-info">
                                <span class="payment-method-label">支払方法</span>
                                <span class="payment-method-value">{{ $customer->payment_method ?? '未設定' }}</span>
                            </div>
                        </div>
                        @if ($customer->payment_classification)
                        <div class="payment-classification-badge">
                            <span class="classification-label">区分</span>
                            <span class="classification-value">{{ $customer->payment_classification }}</span>
                        </div>
                        @endif
                    </div>

                    <div class="payment-amounts-grid">
                        <div class="amount-item billing-amount">
                            <div class="amount-icon">
                                <i class="fas fa-file-invoice-dollar"></i>
                            </div>
                            <div class="amount-content">
                                <label class="amount-label">請求金額</label>
                                <div class="amount-value">
                                    @if ($customer->billing_amount)
                                    <span class="currency-symbol">¥</span>
                                    <span class="amount-number">{{ number_format($customer->billing_amount) }}</span>
                                    @else
                                    <span class="amount-placeholder">未設定</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="amount-item collection-amount">
                            <div class="amount-icon">
                                <i class="fas fa-hand-holding-usd"></i>
                            </div>
                            <div class="amount-content">
                                <label class="amount-label">徴収請求額</label>
                                <div class="amount-value">
                                    @if ($customer->collection_request_amount)
                                    <span class="currency-symbol">¥</span>
                                    <span class="amount-number">{{ number_format($customer->collection_request_amount) }}</span>
                                    @else
                                    <span class="amount-placeholder">未設定</span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="amount-item tax-amount">
                            <div class="amount-icon">
                                <i class="fas fa-percentage"></i>
                            </div>
                            <div class="amount-content">
                                <label class="amount-label">消費税</label>
                                <div class="amount-value">
                                    @if ($customer->consumption_tax)
                                    <span class="currency-symbol">¥</span>
                                    <span class="amount-number">{{ number_format($customer->consumption_tax) }}</span>
                                    @else
                                    <span class="amount-placeholder">未設定</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Billing Address -->
            <div class="modern-card address-info-card mb-4" data-aos="fade-up" data-aos-delay="400">
                <div class="modern-card-header">
                    <div class="card-header-content">
                        <div class="card-icon address-icon">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="card-title-group">
                            <h5 class="card-title">請求先住所</h5>
                            <p class="card-subtitle">Billing Address</p>
                        </div>
                    </div>
                    <div class="card-header-action">
                        <span class="status-indicator status-address"></span>
                    </div>
                </div>
                <div class="modern-card-body">
                    <div class="address-display">
                        <div class="address-row">
                            <div class="address-item postal-code">
                                <div class="address-icon">
                                    <i class="fas fa-mail-bulk text-primary"></i>
                                </div>
                                <div class="address-content">
                                    <label class="address-label">郵便番号</label>
                                    <p class="address-value">{{ $customer->billing_postal_code ?? '未設定' }}</p>
                                </div>
                            </div>

                            <div class="address-item prefecture">
                                <div class="address-icon">
                                    <i class="fas fa-map text-success"></i>
                                </div>
                                <div class="address-content">
                                    <label class="address-label">県名</label>
                                    <p class="address-value">{{ $customer->billing_prefecture ?? '未設定' }}</p>
                                </div>
                            </div>

                            <div class="address-item city">
                                <div class="address-icon">
                                    <i class="fas fa-city text-info"></i>
                                </div>
                                <div class="address-content">
                                    <label class="address-label">市区町村</label>
                                    <p class="address-value">{{ $customer->billing_city ?? '未設定' }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="address-street">
                            <div class="address-icon">
                                <i class="fas fa-road text-warning"></i>
                            </div>
                            <div class="address-content">
                                <label class="address-label">番地</label>
                                <p class="address-value street-value">{{ $customer->billing_street ?? '未設定' }}</p>
                            </div>
                            <div class="address-content">
                                <label class="address-label">建物名</label>
                                <p class="address-value building-value">{{ $customer->billing_building ?? '未設定' }}</p>
                            </div>
                        </div>

                        @if ($customer->billing_difference)
                        <div class="billing-difference-display">
                            <div class="difference-icon">
                                <i class="fas fa-calculator text-secondary"></i>
                            </div>
                            <div class="difference-content">
                                <label class="difference-label">請求先差額</label>
                                <div class="difference-value {{ $customer->billing_difference >= 0 ? 'positive' : 'negative' }}">
                                    <span class="currency-symbol">¥</span>
                                    <span class="difference-amount">{{ number_format(abs($customer->billing_difference)) }}</span>
                                    @if ($customer->billing_difference >= 0)
                                    <i class="fas fa-arrow-up ms-1"></i>
                                    @else
                                    <i class="fas fa-arrow-down ms-1"></i>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Summary -->
        <div class="col-lg-4">
            <!-- Customer Summary -->
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-pie me-2"></i>顧客サマリー
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="summary-item text-center p-3 bg-light rounded">
                                <label class="form-label fw-bold text-muted small text-uppercase">入金回数</label>
                                <p class="h3 text-primary mb-0">{{ $customer->payments->count() }}</p>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="summary-item text-center p-3 bg-light rounded">
                                <label class="form-label fw-bold text-muted small text-uppercase">入金合計</label>
                                <p class="h3 text-success mb-0">
                                    {{ number_format($customer->payments->sum('amount'), 2) }}
                                </p>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="summary-item text-center p-3 bg-light rounded">
                                <label class="form-label fw-bold text-muted small text-uppercase">最終入金</label>
                                <p class="h6 mb-0">
                                    @if ($customer->payments->count() > 0)
                                    <i class="fas fa-calendar-check me-1 text-info"></i>
                                    {{ $customer->payments->sortByDesc('payment_date')->first()->payment_date->format('M d, Y') }}
                                    @else
                                    <span class="text-muted">まだ入金がありません</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="summary-item text-center p-3 bg-light rounded">
                                <label class="form-label fw-bold text-muted small text-uppercase">登録日</label>
                                <p class="h6 mb-0">
                                    <i class="fas fa-user-plus me-1 text-info"></i>
                                    {{ $customer->created_at->format('M d, Y') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0">
                        <i class="fas fa-bolt me-2"></i>クイック操作
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="d-grid gap-3">
                        <a href="{{ route('payments.create') }}?customer_id={{ $customer->id }}"
                            class="btn btn-primary btn-lg">
                            <i class="fas fa-plus me-2"></i>入金を追加
                        </a>
                        <a href="{{ route('payments.index') }}?customer_id={{ $customer->id }}"
                            class="btn btn-outline-info">
                            <i class="fas fa-list me-2"></i>すべての入金を見る
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment History -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-secondary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-history me-2"></i>入金履歴
                    </h5>
                    <a href="{{ route('payments.create') }}?customer_id={{ $customer->id }}"
                        class="btn btn-light btn-sm">
                        <i class="fas fa-plus me-1"></i>Add Payment
                    </a>
                </div>
                <div class="card-body p-0">
                    @if ($customer->payments->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="border-0 px-4 py-3">日付</th>
                                    <th class="border-0 px-4 py-3">月/年</th>
                                    <th class="border-0 px-4 py-3">金額</th>
                                    <th class="border-0 px-4 py-3">領収書番号</th>
                                    <th class="border-0 px-4 py-3">状態</th>
                                    <th class="border-0 px-4 py-3 text-center">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($customer->payments->sortByDesc('payment_date') as $payment)
                                <tr class="border-bottom">
                                    <td class="px-4 py-3">
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-calendar-day me-2 text-muted"></i>
                                            {{ $payment->payment_date->format('M d, Y') }}
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="badge bg-light text-dark px-3 py-2">
                                            {{ date('F Y', mktime(0, 0, 0, $payment->payment_month, 1, $payment->payment_year)) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="fw-bold text-success h6 mb-0">
                                            {{ number_format($payment->amount, 2) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if ($payment->receipt_number)
                                        <code
                                            class="bg-light px-2 py-1 rounded">{{ $payment->receipt_number }}</code>
                                        @else
                                        <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="badge rounded-pill bg-{{ $payment->status == 'completed' ? 'success' : ($payment->status == 'pending' ? 'warning' : 'danger') }} px-3 py-2">
                                            <i
                                                class="fas fa-{{ $payment->status == 'completed' ? 'check-circle' : ($payment->status == 'pending' ? 'clock' : 'times-circle') }} me-1"></i>
                                            {{ ucfirst($payment->status) }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <a href="{{ route('payments.show', $payment) }}"
                                                class="btn btn-outline-info" title="入金を見る">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('payments.edit', $payment) }}"
                                                class="btn btn-outline-warning" title="入金を編集">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <i class="fas fa-credit-card fa-4x text-muted"></i>
                        </div>
                        <h5 class="text-muted mb-3">入金記録がありません</h5>
                        <p class="text-muted mb-4">この顧客はまだ入金していません。</p>
                        <a href="{{ route('payments.create') }}?customer_id={{ $customer->id }}"
                            class="btn btn-primary btn-lg">
                            <i class="fas fa-plus me-2"></i>最初の入金を追加
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- AOS Animation Library -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
<script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>

<style>
    /* Modern Customer Detail Styling */
    .customer-detail-container {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
        padding: 0;
        margin: -1.5rem -1.5rem 0 -1.5rem;
    }

    /* Hero Header */
    .hero-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 3rem 0 2rem 0;
        position: relative;
        overflow: hidden;
    }

    .hero-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 20"><defs><radialGradient id="a" cx="50%" cy="50%"><stop offset="0%" stop-color="rgba(255,255,255,.1)"/><stop offset="100%" stop-color="rgba(255,255,255,0)"/></radialGradient></defs><circle fill="url(%23a)" cx="10" cy="10" r="10"/><circle fill="url(%23a)" cx="90" cy="10" r="10"/></svg>');
        opacity: 0.1;
    }

    .hero-title {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .hero-badge {
        background: rgba(255, 255, 255, 0.2);
        padding: 0.5rem 1rem;
        border-radius: 25px;
        font-size: 0.9rem;
        font-weight: 600;
        margin-left: 1rem;
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .hero-subtitle {
        font-size: 1.1rem;
        opacity: 0.9;
        font-weight: 400;
    }

    .customer-avatar {
        background: rgba(255, 255, 255, 0.1);
        border-radius: 50%;
        padding: 1rem;
        backdrop-filter: blur(10px);
    }

    /* Modern Button Styling */
    .btn-group-modern {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }

    .btn-modern {
        padding: 0.75rem 1.5rem;
        border-radius: 25px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: none;
        position: relative;
        overflow: hidden;
        backdrop-filter: blur(10px);
    }

    .btn-modern::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
        transition: left 0.5s;
    }

    .btn-modern:hover::before {
        left: 100%;
    }

    .btn-modern.btn-secondary {
        background: rgba(108, 117, 125, 0.9);
        color: white;
    }

    .btn-modern.btn-warning {
        background: rgba(255, 193, 7, 0.9);
        color: #212529;
    }

    .btn-modern.btn-danger {
        background: rgba(220, 53, 69, 0.9);
        color: white;
    }

    .btn-modern:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
    }

    /* Modern Card Styling */
    .modern-card {
        background: white;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid rgba(255, 255, 255, 0.2);
        backdrop-filter: blur(10px);
    }

    .modern-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }

    .modern-card-header {
        padding: 1.5rem 2rem;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-header-content {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .card-icon {
        width: 50px;
        height: 50px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .banking-icon {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }

    .payment-icon {
        background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
        color: #333;
    }

    .address-icon {
        background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
        color: #333;
    }

    .card-title-group h5 {
        margin: 0;
        font-weight: 700;
        color: #2c3e50;
        font-size: 1.3rem;
    }

    .card-subtitle {
        margin: 0;
        font-size: 0.85rem;
        color: #6c757d;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Status Indicators */
    .status-indicator {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        position: relative;
    }

    .status-indicator::after {
        content: '';
        position: absolute;
        top: -2px;
        left: -2px;
        right: -2px;
        bottom: -2px;
        border-radius: 50%;
        animation: pulse 2s infinite;
    }

    .status-active {
        background: #28a745;
    }

    .status-active::after {
        background: rgba(40, 167, 69, 0.3);
    }

    .status-payment {
        background: #ffc107;
    }

    .status-payment::after {
        background: rgba(255, 193, 7, 0.3);
    }

    .status-address {
        background: #17a2b8;
    }

    .status-address::after {
        background: rgba(23, 162, 184, 0.3);
    }

    @keyframes pulse {
        0% {
            transform: scale(1);
            opacity: 1;
        }

        100% {
            transform: scale(1.4);
            opacity: 0;
        }
    }

    /* Modern Card Body */
    .modern-card-body {
        padding: 2rem;
    }

    /* Info Grid */
    .info-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
    }

    .info-item-modern {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: rgba(248, 249, 250, 0.5);
        border-radius: 15px;
        transition: all 0.3s ease;
    }

    .info-item-modern:hover {
        background: rgba(248, 249, 250, 0.8);
        transform: translateX(5px);
    }

    .info-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        background: rgba(255, 255, 255, 0.8);
        flex-shrink: 0;
    }

    .info-content {
        flex: 1;
    }

    .info-label {
        display: block;
        font-size: 0.8rem;
        color: #6c757d;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.25rem;
    }

    .info-value {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 600;
        color: #2c3e50;
    }

    /* Special Banking Styling */
    .customer-number-badge {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.9rem;
    }

    .bank-code,
    .account-number-display {
        font-family: 'Courier New', monospace;
        background: rgba(0, 123, 255, 0.1);
        padding: 0.25rem 0.5rem;
        border-radius: 5px;
        color: #0056b3;
        font-weight: bold;
    }

    /* Payment Information Styling */
    .payment-summary-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 1rem;
        background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
        border-radius: 15px;
        margin-bottom: 2rem;
    }

    .payment-method-display {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .payment-method-icon {
        width: 50px;
        height: 50px;
        background: rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: #333;
    }

    .payment-method-info {
        display: flex;
        flex-direction: column;
    }

    .payment-method-label {
        font-size: 0.8rem;
        color: #666;
        font-weight: 600;
    }

    .payment-method-value {
        font-size: 1.1rem;
        font-weight: 700;
        color: #333;
    }

    .payment-classification-badge {
        background: rgba(255, 255, 255, 0.3);
        padding: 0.5rem 1rem;
        border-radius: 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
    }

    .classification-label {
        font-size: 0.7rem;
        color: #666;
        font-weight: 600;
    }

    .classification-value {
        font-size: 1.2rem;
        font-weight: 700;
        color: #333;
    }

    /* Payment Amounts Grid */
    .payment-amounts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
    }

    .amount-item {
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        text-align: center;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
        border-left: 4px solid;
    }

    .billing-amount {
        border-left-color: #28a745;
    }

    .collection-amount {
        border-left-color: #007bff;
    }

    .tax-amount {
        border-left-color: #ffc107;
    }

    .amount-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .amount-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin: 0 auto 1rem;
        background: rgba(248, 249, 250, 0.8);
    }

    .amount-label {
        display: block;
        font-size: 0.8rem;
        color: #6c757d;
        font-weight: 600;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .amount-value {
        font-size: 1.5rem;
        font-weight: 700;
        color: #2c3e50;
    }

    .currency-symbol {
        font-size: 1rem;
        opacity: 0.7;
        margin-right: 0.25rem;
    }

    .amount-placeholder {
        color: #6c757d;
        font-style: italic;
        font-size: 1rem;
    }

    /* Address Display */
    .address-display {
        background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%);
        border-radius: 15px;
        padding: 2rem;
    }

    .address-row {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-bottom: 1.5rem;
    }

    .address-item {
        background: rgba(255, 255, 255, 0.7);
        border-radius: 10px;
        padding: 1rem;
        text-align: center;
        backdrop-filter: blur(10px);
    }

    .address-street {
        background: rgba(255, 255, 255, 0.7);
        border-radius: 10px;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
        backdrop-filter: blur(10px);
    }

    .address-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        background: rgba(255, 255, 255, 0.8);
        flex-shrink: 0;
    }

    .address-label {
        display: block;
        font-size: 0.7rem;
        color: #666;
        font-weight: 600;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .address-value {
        margin: 0;
        font-size: 1rem;
        font-weight: 600;
        color: #333;
    }

    .street-value {
        font-size: 1.1rem;
    }

    /* Billing Difference */
    .billing-difference-display {
        background: rgba(255, 255, 255, 0.7);
        border-radius: 10px;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        backdrop-filter: blur(10px);
    }

    .difference-value.positive {
        color: #28a745;
    }

    .difference-value.negative {
        color: #dc3545;
    }

    .difference-label {
        display: block;
        font-size: 0.8rem;
        color: #666;
        font-weight: 600;
        margin-bottom: 0.5rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .difference-value {
        font-size: 1.3rem;
        font-weight: 700;
        display: flex;
        align-items: center;
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .hero-title {
            font-size: 1.8rem;
        }

        .hero-badge {
            display: block;
            margin-left: 0;
            margin-top: 0.5rem;
            text-align: center;
        }

        .btn-group-modern {
            justify-content: center;
        }

        .info-grid {
            grid-template-columns: 1fr;
        }

        .payment-summary-row {
            flex-direction: column;
            gap: 1rem;
            text-align: center;
        }

        .address-row {
            grid-template-columns: 1fr;
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

    /* Summary and other existing styles */
    .summary-item {
        transition: all 0.3s ease;
        background: white;
        border-radius: 15px;
        padding: 1.5rem;
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
    }

    .summary-item:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
    }

    .table tbody tr:hover {
        background-color: rgba(0, 123, 255, 0.05);
    }

    .badge {
        font-weight: 600;
        padding: 0.5rem 1rem;
        border-radius: 20px;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize AOS
        AOS.init({
            duration: 800,
            easing: 'ease-out-cubic',
            once: true,
            offset: 50
        });

        // Add fade-in animation to main container
        document.querySelector('.customer-detail-container').classList.add('fade-in');
    });
</script>
@endsection