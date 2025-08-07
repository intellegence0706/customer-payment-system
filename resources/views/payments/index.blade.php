@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Payments</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('payments.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Add Payment
            </a>
            <a href="{{ route('payments.upload-form') }}" class="btn btn-outline-secondary">
                <i class="fas fa-upload me-1"></i> Upload Data
            </a>
        </div>
    </div>
</div>

<!-- Payments Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>Customer</th>
                        <th>Month</th>
                        <th>Year</th>
                        <th>Amount</th>
                        <th>Date</th>
                        <th>Receipt #</th>
                        <th>Status</th>
                        <th>Actions</th>
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
                                    {{ ucfirst($payment->status) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="#" class="btn btn-outline-warning disabled">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="#" style="display: inline;" onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger disabled">
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
                                <p class="text-muted">No payments found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                Showing {{ $payments->firstItem() ?? 0 }} to {{ $payments->lastItem() ?? 0 }} of {{ $payments->total() }} results
            </div>
            {{ $payments->links() }}
        </div>
    </div>
</div>
@endsection
