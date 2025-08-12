@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">入金詳細</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> 入金一覧に戻る
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">入金情報</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">顧客</label>
                            <p class="form-control-plaintext">{{ $payment->customer->name ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">顧客番号</label>
                            <p class="form-control-plaintext">{{ $payment->customer->customer_number ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">月</label>
                            <p class="form-control-plaintext">{{ $payment->payment_month }}</p>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">年</label>
                            <p class="form-control-plaintext">{{ $payment->payment_year }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">金額</label>
                            <p class="form-control-plaintext">{{ number_format($payment->amount, 2) }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">入金日</label>
                            <p class="form-control-plaintext">
                                {{ $payment->payment_date ? $payment->payment_date->format('F d, Y') : 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">領収書番号</label>
                            <p class="form-control-plaintext">{{ $payment->receipt_number ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">状態</label>
                            <p class="form-control-plaintext">
                                <span
                                    class="badge bg-{{ $payment->status == 'completed' ? 'success' : ($payment->status == 'pending' ? 'warning' : 'danger') }}">
                                    {{ $payment->status == 'completed' ? '完了' : ($payment->status == 'pending' ? '保留' : '失敗') }}
                                </span>
                            </p>
                        </div>
                        @if ($payment->notes)
                            <div class="col-12">
                                <label class="form-label fw-bold">備考</label>
                                <p class="form-control-plaintext">{{ $payment->notes }}</p>
                            </div>
                        @endif
                        <div class="col-md-6">
                            <label class="form-label fw-bold">作成日時</label>
                            <p class="form-control-plaintext">{{ $payment->created_at->format('F d, Y g:i A') }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">更新日時</label>
                            <p class="form-control-plaintext">{{ $payment->updated_at->format('F d, Y g:i A') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
