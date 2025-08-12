@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">入金を編集</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> 入金一覧に戻る
        </a>
    </div>
 </div>

 <div class="row">
    <div class="col-lg-8">
        <form method="POST" action="{{ route('payments.update', $payment) }}">
            @csrf
            @method('PUT')
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">入金情報</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="customer_id" class="form-label">顧客 <span class="text-danger">*</span></label>
                            <select class="form-select @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id" required>
                                <option value="">顧客を選択</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id', $payment->customer_id) == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }} ({{ $customer->customer_number }})
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="payment_month" class="form-label">月 <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('payment_month') is-invalid @enderror" id="payment_month" name="payment_month" min="1" max="12" value="{{ old('payment_month', $payment->payment_month) }}" required>
                            @error('payment_month')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="payment_year" class="form-label">年 <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('payment_year') is-invalid @enderror" id="payment_year" name="payment_year" min="2020" value="{{ old('payment_year', $payment->payment_year) }}" required>
                            @error('payment_year')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="amount" class="form-label">金額 <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" value="{{ old('amount', $payment->amount) }}" required>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="payment_date" class="form-label">入金日 <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('payment_date') is-invalid @enderror" id="payment_date" name="payment_date" value="{{ old('payment_date', optional($payment->payment_date)->format('Y-m-d')) }}" required>
                            @error('payment_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="receipt_number" class="form-label">領収書番号</label>
                            <input type="text" class="form-control @error('receipt_number') is-invalid @enderror" id="receipt_number" name="receipt_number" value="{{ old('receipt_number', $payment->receipt_number) }}">
                            @error('receipt_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">状態 <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="pending" {{ old('status', $payment->status) == 'pending' ? 'selected' : '' }}>保留</option>
                                <option value="completed" {{ old('status', $payment->status) == 'completed' ? 'selected' : '' }}>完了</option>
                                <option value="failed" {{ old('status', $payment->status) == 'failed' ? 'selected' : '' }}>失敗</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label for="notes" class="form-label">備考</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="2">{{ old('notes', $payment->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary me-md-2">キャンセル</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> 変更を保存
                </button>
            </div>
        </form>
    </div>
 </div>
@endsection


