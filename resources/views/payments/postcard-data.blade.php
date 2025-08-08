@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Postcard Data Preview</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <div class="btn-group me-2">
                <a href="{{ route('payments.postcard-form') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to Form
                </a>
                <a href="{{ route('payments.export-csv') }}?month={{ $currentMonth }}&year={{ $currentYear }}" class="btn btn-outline-success">
                    <i class="fas fa-download me-1"></i> Export CSV
                </a>
                <a href="{{ route('payments.export-pdf') }}?month={{ $currentMonth }}&year={{ $currentYear }}" class="btn btn-outline-danger">
                    <i class="fas fa-file-pdf me-1"></i> Export PDF
                </a>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Postcard Data for {{ date('F Y', mktime(0, 0, 0, $currentMonth, 1, $currentYear)) }}</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th>Customer Name</th>
                            <th>Customer Number</th>
                            <th>Address</th>
                            <th>Postal Code</th>
                            <th>Current Month</th>
                            <th>Current Payment Amount</th>
                            <th>Current Payment Date</th>
                            <th>Previous Month</th>
                            <th>Previous Receipt Number</th>
                            <th>Previous Payment Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($postcardData as $data)
                            <tr>
                                <td>{{ $data['customer_name'] }}</td>
                                <td>{{ $data['customer_number'] }}</td>
                                <td>{{ $data['address'] }}</td>
                                <td>{{ $data['postal_code'] }}</td>
                                <td>{{ $data['current_month'] }}</td>
                                <td>{{ number_format($data['current_payment_amount'], 2) }}</td>
                                <td>{{ $data['current_payment_date'] ?? '-' }}</td>
                                <td>{{ $data['previous_month'] }}</td>
                                <td>{{ $data['previous_receipt_number'] ?? '-' }}</td>
                                <td>{{ number_format($data['previous_payment_amount'], 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <i class="fas fa-mail-bulk fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">No postcard data found for the selected month and year.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
