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
            font-family: "NotoSansJP";
            font-weight: 400;
            font-style: normal;
            src: url("{{ storage_path('fonts/NotoSansJP.ttf') }}") format("truetype");
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
            margin-top: 35px;
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
            <div class="header">
                <h3>
                    @if (!empty($row['bill_title']))
                        {{ $row['bill_title'] }} 請求書
                    @else
                        {{ $year }}年{{ $month }}月分 請求書
                    @endif
                </h3>
            </div>
            <div class="customer-info">
                <strong>{{ $row['recipient_name'] }}</strong>
                @if (!empty($row['customer_number']))<span> / No. {{ $row['customer_number'] }}</span>@endif
            </div>
            <div>
                <p>下記のとおりご請求申し上げます。</p>
                <div>
                    ご請求金額（税込）
                    <span class="amount">¥{{ number_format((float)($row['amount_total'] ?? 0)) }}</span>
                </div>
            </div>
            <div style="border:1px solid #000; border-radius:3px; padding:6px; margin:10px 0; display:flex; justify-content:space-between; align-items:center;">
                <div style="font-size:10px; color:#666;">※振替日</div>
                <div style="font-weight:bold;">{{ $row['transfer_date'] }}</div>
            </div>
            <div class="payment-info">
                <strong>明細</strong>
                <table style="width:100%; border-collapse:collapse; font-variant-numeric: tabular-nums;">
                    <tbody>
                        @foreach (($row['items'] ?? []) as $it)
                            <tr>
                                <td style="width:20%; border-bottom:1px solid #ddd;">{{ $it['date'] }}</td>
                                <td style="border-bottom:1px solid #ddd;">{{ $it['name'] }}</td>
                                <td style="width:25%; text-align:right; border-bottom:1px solid #ddd;">¥{{ number_format((float)$it['amount']) }}</td>
                            </tr>
                        @endforeach
                        @if (($row['transfer_fee'] ?? 0) > 0)
                            <tr>
                                <td style="border-bottom:1px solid #ddd;">&nbsp;</td>
                                <td style="border-bottom:1px solid #ddd;">振替手数料</td>
                                <td style="text-align:right; border-bottom:1px solid #ddd;">¥{{ number_format((float)$row['transfer_fee']) }}</td>
                            </tr>
                        @endif
                        @php
                            $lineSum = 0.0;
                            foreach (($row['items'] ?? []) as $it) { $lineSum += (float)($it['amount'] ?? 0); }
                            $lineSum += (float)($row['transfer_fee'] ?? 0);
                        @endphp
                        @if ($lineSum > 0)
                            <tr>
                                <td style="border-top:1px solid #000;">&nbsp;</td>
                                <td style="border-top:1px solid #000; text-align:right; font-weight:bold;">合計</td>
                                <td style="border-top:1px solid #000; text-align:right; font-weight:bold;">¥{{ number_format($lineSum) }}</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
            <div class="footer">
                <p>{{ now()->format('Y-m-d') }}</p>
            </div>
        </div>
    @endforeach
</body>

</html>
