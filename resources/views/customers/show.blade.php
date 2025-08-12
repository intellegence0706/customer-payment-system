@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-4 border-bottom">
        <div>
            <h1 class="h2 mb-1">顧客詳細</h1>
            <p class="text-muted mb-0">顧客番号 #{{ $customer->customer_number }}</p>
        </div>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary me-2">
                <i class="fas fa-arrow-left me-1"></i> 顧客一覧に戻る
            </a>
            <a href="{{ route('customers.edit', $customer) }}" class="btn btn-warning me-2">
                <i class="fas fa-edit me-1"></i> 顧客を編集
            </a>
            <form method="POST" action="{{ route('customers.destroy', $customer) }}" style="display: inline;"
                onsubmit="return confirm('この顧客を削除してよろしいですか？この操作は元に戻せません。')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash me-1"></i> 削除
                </button>
            </form>
        </div>
    </div>

    <div class="row">
        <!-- Customer Information -->
        <div class="col-lg-8">
            <!-- Personal Information -->
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-user me-2"></i>個人情報
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small text-uppercase">氏名</label>
                                <p class="form-control-plaintext h6 mb-0">{{ $customer->name }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small text-uppercase">ガーナ</label>
                                <p class="form-control-plaintext h6 mb-0">{{ $customer->ghana ?? 'Not specified' }}</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small text-uppercase">性別</label>
                                <div class="mt-1">
                                    <span
                                        class="badge rounded-pill bg-{{ $customer->gender == 'male' ? 'primary' : ($customer->gender == 'female' ? 'success' : 'secondary') }} px-3 py-2">
                                        <i
                                            class="fas fa-{{ $customer->gender == 'male' ? 'mars' : ($customer->gender == 'female' ? 'venus' : 'genderless') }} me-1"></i>
                                        {{ ucfirst($customer->gender) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small text-uppercase">郵便番号</label>
                                <p class="form-control-plaintext h6 mb-0">{{ $customer->postal_code ?? 'Not specified' }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small text-uppercase">電話番号</label>
                                <p class="form-control-plaintext h6 mb-0">
                                    @if ($customer->phone_number)
                                        <a href="tel:{{ $customer->phone_number }}" class="text-decoration-none">
                                            <i class="fas fa-phone me-1 text-primary"></i>{{ $customer->phone_number }}
                                        </a>
                                    @else
                                        Not specified
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small text-uppercase">住所</label>
                                <p class="form-control-plaintext h6 mb-0">
                                    @if ($customer->address)
                                        <i class="fas fa-map-marker-alt me-1 text-muted"></i>{{ $customer->address }}
                                    @else
                                        Not specified
                                    @endif
                                </p>
                            </div>
                        </div>
                        @if ($customer->note)
                            <div class="col-12">
                                <div class="info-item">
                                    <label class="form-label fw-bold text-muted small text-uppercase">備考</label>
                                    <div class="alert alert-light border-start border-3 border-info">
                                        <i class="fas fa-info-circle me-2 text-info"></i>{{ $customer->note }}
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Bank Information -->
            <div class="card shadow-sm mb-4 border-0">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-university me-2"></i>銀行情報
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small text-uppercase">顧客番号</label>
                                <p class="form-control-plaintext h6 mb-0">
                                    <span class="badge bg-dark px-3 py-2">{{ $customer->customer_number }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small text-uppercase">銀行コード</label>
                                <p class="form-control-plaintext h6 mb-0">{{ $customer->bank_code ?? 'Not specified' }}</p>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small text-uppercase">支店コード</label>
                                <p class="form-control-plaintext h6 mb-0">{{ $customer->branch_code ?? 'Not specified' }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small text-uppercase">銀行名</label>
                                <p class="form-control-plaintext h6 mb-0">
                                    @if ($customer->bank_name)
                                        <i class="fas fa-building me-1 text-success"></i>{{ $customer->bank_name }}
                                    @else
                                        Not specified
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small text-uppercase">支店名</label>
                                <p class="form-control-plaintext h6 mb-0">
                                    @if ($customer->branch_name)
                                        <i class="fas fa-map-pin me-1 text-success"></i>{{ $customer->branch_name }}
                                    @else
                                        Not specified
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small text-uppercase">口座名義</label>
                                <p class="form-control-plaintext h6 mb-0">{{ $customer->account_name ?? 'Not specified' }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small text-uppercase">口座ガーナ</label>
                                <p class="form-control-plaintext h6 mb-0">{{ $customer->account_ghana ?? 'Not specified' }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small text-uppercase">口座番号</label>
                                <p class="form-control-plaintext h6 mb-0">
                                    @if ($customer->account_number)
                                        <code class="bg-light px-2 py-1 rounded">{{ $customer->account_number }}</code>
                                    @else
                                        Not specified
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-item">
                                <label class="form-label fw-bold text-muted small text-uppercase">口座名義人</label>
                                <p class="form-control-plaintext h6 mb-0">
                                    {{ $customer->account_holder ?? 'Not specified' }}</p>
                            </div>
                        </div>
                        @if ($customer->bank_note)
                            <div class="col-12">
                                <div class="info-item">
                                    <label class="form-label fw-bold text-muted small text-uppercase">銀行メモ</label>
                                    <div class="alert alert-light border-start border-3 border-success">
                                        <i class="fas fa-sticky-note me-2 text-success"></i>{{ $customer->bank_note }}
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

    <style>
        .info-item {
            padding: 0.5rem 0;
        }

        .summary-item {
            transition: all 0.3s ease;
        }

        .summary-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .card {
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
        }

        .table tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.05);
        }

        .btn {
            transition: all 0.3s ease;
        }

        .btn:hover {
            transform: translateY(-1px);
        }

        .badge {
            font-weight: 500;
        }
    </style>
@endsection
