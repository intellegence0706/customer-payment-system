<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>はがき印刷データ</title>
    <style>
        body,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        th,
        td,
        strong,
        .amount,
        .customer-info,
        .payment-info,
        .footer,
        .logo {
            font-family: 'IPAMincho', 'Noto Sans JP', 'IPAGothic', sans-serif !important;
        }

        .postcard {
            width: 148mm;
            height: 105mm;
            border: 1px solid #000;
            margin: 10mm;
            padding: 10mm;
            page-break-after: always;
            box-sizing: border-box;
        }

        .postcard:last-child {
            page-break-after: avoid;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
        }

        .customer-info {
            margin-bottom: 15px;
        }

        .payment-info {
            margin-bottom: 10px;
        }

        .amount {
            font-size: 18px;
            font-weight: bold;
            color: #2c5aa0;
        }

        .footer {
            text-align: center;
            margin-top: 15px;
            font-size: 10px;
        }

        .logo {
            text-align: center;
            margin-bottom: 10px;
        }
    </style>
</head>

<body>
    @foreach ($data as $row)
        <div class="postcard">
            <div class="logo">
                <h2>顧客管理システム</h2>
            </div>
            <div class="header">
                <h3>はがき印刷データ</h3>
            </div>
            <div class="customer-info">
                <strong>{{ $row['recipient_name'] }}</strong><br>
                顧客番号: {{ $row['customer_number'] }}<br>
                {{ $row['address'] }}<br>
                @if ($row['postal_code'])
                    郵便番号: {{ $row['postal_code'] }}
                @endif
            </div>
            <div class="payment-info">
                <strong>当月 ({{ $row['current_year'] }}年{{ $row['current_month'] }}月):</strong><br>
                @if ($row['current_amount'])
                    請求額: <span class="amount"> {{ number_format($row['current_amount'], 2) }}</span><br>
                    入金日: {{ $row['current_payment_date'] }}<br>
                    受付番号: {{ $row['current_receipt_number'] }}
                @else
                    <em>当月の請求記録はありません</em>
                @endif
            </div>
            <div class="payment-info">
                <strong>前月 ({{ $row['previous_year'] }}年{{ $row['previous_month'] }}月):</strong><br>
                @if ($row['previous_amount'])
                    受付番号: {{ $row['previous_receipt_number'] }}<br>
                    入金額: {{ number_format($row['previous_amount'], 2) }}
                @else
                    <em>前月の入金記録なし</em>
                @endif
            </div>
            <div class="footer">
                <p>ご不明点は事務所までお問い合わせください。</p>
                <p>{{ now()->format('Y年n月j日') }} 作成</p>
            </div>
        </div>
    @endforeach
</body>

</html>
