@extends('layouts.app')

@section('content')
<div class="pt-3 pb-2 mb-3">
    <div class="d-flex justify-content-between align-items-center bg-light rounded-3 p-3 shadow-sm">
        <div>
            <h1 class="h4 mb-1"><i class="fas fa-wallet me-2 text-primary"></i>請求管理</h1>
            <div class="text-muted small">入金データの確認・編集・取込を行えます。</div>
        </div>
        <div class="btn-toolbar mb-0">
            <div class="btn-group">
                <a href="{{ route('payments.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> 入金を追加
                </a>
                <div class="btn-group" role="group">
                    <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-upload me-1"></i> データ取込
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('payments.upload-form') }}">
                            <i class="fas fa-file-csv me-1"></i> 一括取込 (CSV/XLSX)
                        </a></li>
                        <li><a class="dropdown-item" href="{{ route('payments.xlsx-viewer') }}">
                            <i class="fas fa-file-excel me-1"></i> XLSXビューア
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light position-sticky top-0" style="z-index: 1;">
                    <tr>
                        <th class="text-nowrap">顧客</th>
                        <th class="text-center">月</th>
                        <th class="text-center">年</th>
                        <th class="text-end">金額</th>
                        <th class="text-nowrap">日付</th>
                        <th class="text-nowrap">領収書番号</th>
                        <th class="text-nowrap">状態</th>
                        <th class="text-nowrap text-center">操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    <span class="badge rounded-circle bg-primary bg-opacity-10 text-primary me-2" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                                        <i class="fas fa-user"></i>
                                    </span>
                                    <span class="fw-semibold">{{ $payment->customer->user_name ?? $payment->customer->name ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="text-center">{{ $payment->payment_month }}</td>
                            <td class="text-center">{{ $payment->payment_year }}</td>
                            <td class="text-end">
                                <span class="fw-bold">¥ {{ number_format($payment->amount, 2) }}</span>
                            </td>
                            <td>{{ $payment->payment_date ? $payment->payment_date->format('Y-m-d') : '-' }}</td>
                            <td>
                                @if($payment->receipt_number)
                                    <span class="badge bg-secondary-subtle text-secondary border">{{ $payment->receipt_number }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @php
                                    $status = $payment->status;
                                    $statusMap = [
                                        'completed' => ['label' => '完了', 'class' => 'success', 'icon' => 'check-circle'],
                                        'pending'   => ['label' => '保留', 'class' => 'warning', 'icon' => 'clock'],
                                        'failed'    => ['label' => '失敗', 'class' => 'danger', 'icon' => 'times-circle'],
                                    ];
                                    $meta = $statusMap[$status] ?? ['label' => $status, 'class' => 'secondary', 'icon' => 'question-circle'];
                                @endphp
                                <span class="badge bg-{{ $meta['class'] }}">
                                    <i class="fas fa-{{ $meta['icon'] }} me-1"></i>{{ $meta['label'] }}
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('payments.show', $payment) }}" class="btn btn-outline-info" data-bs-toggle="tooltip" title="詳細を表示">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('payments.edit', $payment) }}" class="btn btn-outline-warning" data-bs-toggle="tooltip" title="編集">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('payments.destroy', $payment) }}" style="display: inline;" onsubmit="return confirm('削除してもよろしいですか？この操作は取り消せません。')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger" data-bs-toggle="tooltip" title="削除">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <div class="d-flex flex-column align-items-center">
                                    <span class="bg-light rounded-circle d-inline-flex align-items-center justify-content-center mb-3" style="width: 72px; height: 72px;">
                                        <i class="fas fa-credit-card fa-2x text-muted"></i>
                                    </span>
                                    <p class="text-muted mb-3">入金が見つかりません。</p>
                                    <a href="{{ route('payments.create') }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-plus me-1"></i> 最初の入金を追加
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mt-3">
            <div class="text-muted small">
                全 {{ $payments->total() }} 件中 {{ $payments->firstItem() ?? 0 }} 〜 {{ $payments->lastItem() ?? 0 }} を表示
            </div>
            <div>
                {{ $payments->links('pagination::bootstrap-5') }}
            </div>  
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    })
</script>
@endpush
@endsection
    