@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Upload Month-End Payment Data</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Back to Payments
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <form method="POST" action="{{ route('payments.upload') }}" enctype="multipart/form-data">
            @csrf
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Upload CSV File</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="payment_file" class="form-label">CSV File <span class="text-danger">*</span></label>
                            <input type="file" class="form-control @error('payment_file') is-invalid @enderror" id="payment_file" name="payment_file" accept=".csv,.txt" required>
                            @error('payment_file')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="payment_month" class="form-label">Month <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('payment_month') is-invalid @enderror" id="payment_month" name="payment_month" min="1" max="12" value="{{ old('payment_month') }}" required>
                            @error('payment_month')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="payment_year" class="form-label">Year <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('payment_year') is-invalid @enderror" id="payment_year" name="payment_year" min="2020" value="{{ old('payment_year') }}" required>
                            @error('payment_year')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary me-md-2">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-upload me-1"></i> Upload Payments
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
