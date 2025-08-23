@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">入金</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('payments.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> 入金を追加
            </a>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-upload me-1"></i> データ取込
                </button>
                <ul class="dropdown-menu">
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


<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>顧客</th>
                        <th>月</th>
                        <th>年</th>
                        <th>金額</th>
                        <th>日付</th>
                        <th>領収書番号</th>
                        <th>状態</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
                        <tr>
                            <td>{{ $payment->customer->name ?? '-' }}</td>
                            <td>{{ $payment->payment_month }}</td>
                            <td>{{ $payment->payment_year }}</td>
                            <td>{{ number_format($payment->amount, 2) }}</td>
                            <td>{{ $payment->payment_date ? $payment->payment_date->format('Y-m-d') : '-' }}</td>
                            <td>{{ $payment->receipt_number }}</td>
                            <td>
                                <span class="badge bg-{{ $payment->status == 'completed' ? 'success' : ($payment->status == 'pending' ? 'warning' : 'danger') }}">
                                    {{ $payment->status == 'completed' ? '完了' : ($payment->status == 'pending' ? '保留' : '失敗') }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('payments.show', $payment) }}" class="btn btn-outline-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('payments.edit', $payment) }}" class="btn btn-outline-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('payments.destroy', $payment) }}" style="display: inline;" onsubmit="return confirm('よろしいですか？')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-credit-card fa-3x text-muted mb-3"></i>
                                <p class="text-muted">入金が見つかりません。</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                全 {{ $payments->total() }} 件中 {{ $payments->firstItem() ?? 0 }} 〜 {{ $payments->lastItem() ?? 0 }} を表示
            </div>
            {{ $payments->links() }}
        </div>
    </div>
</div>
@endsection
    