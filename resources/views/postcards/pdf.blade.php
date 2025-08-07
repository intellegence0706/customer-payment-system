<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Postcards</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .postcard { 
            width: 148mm; 
            height: 105mm; 
            border: 1px solid #000; 
            margin: 10mm; 
            padding: 10mm; 
            page-break-after: always; 
            box-sizing: border-box;
        }
        .postcard:last-child { page-break-after: avoid; }
        .header { text-align: center; margin-bottom: 15px; }
        .customer-info { margin-bottom: 15px; }
        .payment-info { margin-bottom: 10px; }
        .amount { font-size: 18px; font-weight: bold; color: #2c5aa0; }
        .footer { text-align: center; margin-top: 15px; font-size: 10px; }
        .logo { text-align: center; margin-bottom: 10px; }
    </style>
</head>
<body>
    @foreach($postcardData as $data)
        <div class="postcard">
            <div class="logo">
                <h2>CUSTOMER MANAGEMENT SYSTEM</h2>
            </div>
            
            <div class="header">
                <h3>Payment Notification</h3>
            </div>

            <div class="customer-info">
                <strong>{{ $data['customer']->name }}</strong><br>
                Customer #: {{ $data['customer']->customer_number }}<br>
                {{ $data['customer']->address }}<br>
                @if($data['customer']->postal_code)
                    Postal Code: {{ $data['customer']->postal_code }}
                @endif
            </div>

            <div class="payment-info">
                <strong>Current Month ({{ $data['current_month_name'] }}):</strong><br>
                @if($data['current_payment'])
                    Payment Amount: <span class="amount">GH₵ {{ number_format($data['current_payment']->amount, 2) }}</span><br>
                    Payment Date: {{ $data['current_payment']->payment_date->format('F d, Y') }}<br>
                    Receipt #: {{ $data['current_payment']->receipt_number }}
                @else
                    <em>No payment recorded for this month</em>
                @endif
            </div>

            <div class="payment-info">
                <strong>Previous Month ({{ $data['previous_month_name'] }}):</strong><br>
                @if($data['previous_payment'])
                    Amount: GH₵ {{ number_format($data['previous_payment']->amount, 2) }}<br>
                    Receipt #: {{ $data['previous_payment']->receipt_number }}
                @else
                    <em>No payment recorded</em>
                @endif
            </div>

            <div class="footer">
                <p>Thank you for your payment. For inquiries, please contact our office.</p>
                <p>Generated on {{ now()->format('F d, Y') }}</p>
            </div>
        </div>
    @endforeach
</body>
</html>
