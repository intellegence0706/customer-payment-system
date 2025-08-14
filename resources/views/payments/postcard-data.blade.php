@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">はがきデータ プレビュー</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="{{ route('payments.postcard-form') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> フォームに戻る
                </a>
                <a href="{{ route('payments.export-csv') }}?month={{ $currentMonth }}&year={{ $currentYear }}" class="btn btn-outline-success">
                    <i class="fas fa-download me-1"></i> CSVエクスポート
                </a>
                <a href="{{ route('postcards.print.pdf') }}?month={{ $currentMonth }}&year={{ $currentYear }}" class="btn btn-danger">
                    <i class="fas fa-print me-1"></i> 印刷用PDF
                </a>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">{{ date('Y年n月', mktime(0, 0, 0, $currentMonth, 1, $currentYear)) }} のはがきデータ</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>氏名</th>
                            <th>顧客番号</th>
                            <th>住所</th>
                            <th>郵便番号</th>
                            <th>当月</th>
                            <th>当月入金額</th>
                            <th>当月入金日</th>
                            <th>前月</th>
                            <th>前月領収書番号</th>
                            <th>前月入金額</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($postcardData as $data)
                            <tr>
                                <td>{{ $data['顧客']->name ?? '' }}</td>
                                <td>{{ $data['顧客']->customer_number ?? '' }}</td>
                                <td>{{ $data['顧客']->address ?? '' }}</td>
                                <td>{{ $data['顧客']->postal_code ?? '' }}</td>
                                <td>{{ $data['当月名'] ?? '' }}</td>
                                <td>{{ isset($data['当月入金']) && $data['当月入金'] ? number_format($data['当月入金']->amount, 2) : '0.00' }}</td>
                                <td>{{ (isset($data['当月入金']) && $data['当月入金'] && $data['当月入金']->payment_date) ? $data['当月入金']->payment_date->format('Y-m-d') : '-' }}</td>
                                <td>{{ $data['前月名'] ?? '' }}</td>
                                <td>{{ isset($data['前月入金']) && $data['前月入金'] ? $data['前月入金']->receipt_number : '-' }}</td>
                                <td>{{ isset($data['前月入金']) && $data['前月入金'] ? number_format($data['前月入金']->amount, 2) : '0.00' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <i class="fas fa-mail-bulk fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">選択した月と年のデータがありません。</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
