<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>入金はがき</title>
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
                <h2>顧客管理システム</h2>
            </div>
            
            <div class="header">
                <h3>入金のお知らせ</h3>
            </div>

            <div class="customer-info">
                <strong>{{ $data['customer']->name }}</strong><br>
                顧客番号: {{ $data['customer']->customer_number }}<br>
                {{ $data['customer']->address }}<br>
                @if($data['customer']->postal_code)
                    郵便番号: {{ $data['customer']->postal_code }}
                @endif
            </div>

            <div class="payment-info">
                <strong>当月（{{ $data['current_month_name'] }}）:</strong><br>
                @if($data['current_payment'])
                    入金額: <span class="amount">GH₵ {{ number_format($data['current_payment']->amount, 2) }}</span><br>
                    入金日: {{ $data['current_payment']->payment_date->format('Y年n月j日') }}<br>
                    受付番号: {{ $data['current_payment']->receipt_number }}
                @else
                    <em>当月の入金記録はありません</em>
                @endif
            </div>

            <div class="payment-info">
                <strong>前月（{{ $data['previous_month_name'] }}）:</strong><br>
                @if($data['previous_payment'])
                    入金額: GH₵ {{ number_format($data['previous_payment']->amount, 2) }}<br>
                    受付番号: {{ $data['previous_payment']->receipt_number }}
                @else
                    <em>入金記録なし</em>
                @endif
            </div>

            <div class="footer">
                <p>いつもご入金ありがとうございます。ご不明点は事務所までお問い合わせください。</p>
                <p>{{ now()->format('Y年n月j日') }} 作成</p>
            </div>
        </div>
    @endforeach
</body>
</html>
