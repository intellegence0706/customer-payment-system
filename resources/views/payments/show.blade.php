@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Payment Details</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Payments
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Payment Information</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Customer</label>
                        <p class="form-control-plaintext">{{ $payment->customer->name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Customer Number</label>
                        <p class="form-control-plaintext">{{ $payment->customer->customer_number ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Month</label>
                        <p class="form-control-plaintext">{{ $payment->payment_month }}</p>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Year</label>
                        <p class="form-control-plaintext">{{ $payment->payment_year }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Amount</label>
                        <p class="form-control-plaintext">GH₵ {{ number_format($payment->amount, 2) }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Payment Date</label>
                        <p class="form-control-plaintext">{{ $payment->payment_date ? $payment->payment_date->format('F d, Y') : 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Receipt Number</label>
                        <p class="form-control-plaintext">{{ $payment->receipt_number ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Status</label>
                        <p class="form-control-plaintext">
                            <span class="badge bg-{{ $payment->status == 'completed' ? 'success' : ($payment->status == 'pending' ? 'warning' : 'danger') }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </p>
                    </div>
                    @if($payment->notes)
                        <div class="col-12">
                            <label class="form-label fw-bold">Notes</label>
                            <p class="form-control-plaintext">{{ $payment->notes }}</p>
                        </div>
                    @endif
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Created At</label>
                        <p class="form-control-plaintext">{{ $payment->created_at->format('F d, Y g:i A') }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Updated At</label>
                        <p class="form-control-plaintext">{{ $payment->updated_at->format('F d, Y g:i A') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
