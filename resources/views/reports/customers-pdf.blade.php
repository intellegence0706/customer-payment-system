<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Customer Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; }
        .info { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Customer Report</h1>
        <p>Generated on {{ now()->format('F d, Y \a\t g:i A') }}</p>
    </div>

    <div class="info">
        <strong>Report Parameters:</strong><br>
        @if(isset($parameters['date_from']))
            Date From: {{ $parameters['date_from'] }}<br>
        @endif
        @if(isset($parameters['date_to']))
            Date To: {{ $parameters['date_to'] }}<br>
        @endif
        @if(isset($parameters['gender']))
            Gender: {{ ucfirst($parameters['gender']) }}<br>
        @endif
        @if(isset($parameters['bank_name']))
            Bank: {{ $parameters['bank_name'] }}<br>
        @endif
        Total Records: {{ $customers->count() }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Customer #</th>
                <th>Name</th>
                <th>Gender</th>
                <th>Phone</th>
                <th>Bank</th>
                <th>Account #</th>
                <th>Created</th>
            </tr>
        </thead>
        <tbody>
            @foreach($customers as $customer)
                <tr>
                    <td>{{ $customer->customer_number }}</td>
                    <td>{{ $customer->name }}</td>
                    <td>{{ ucfirst($customer->gender) }}</td>
                    <td>{{ $customer->phone_number }}</td>
                    <td>{{ $customer->bank_name }}</td>
                    <td>{{ $customer->account_number }}</td>
                    <td>{{ $customer->created_at->format('Y-m-d') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Customer Management System - Confidential Report</p>
    </div>
</body>
</html>
