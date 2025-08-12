<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>入金レポート</title>
    <style>
        @php $fontData =$embeddedFontBase64 ?? null;
        @endphp
        @if ($fontData)
            @font-face {
                font-family: 'jpfont';
                font-style: normal;
                font-weight: normal;
                src: url('data:font/ttf;base64,{{ $fontData }}') format('truetype');
            }

            @font-face {
                font-family: 'jpfont';
                font-style: normal;
                font-weight: bold;
                src: url('data:font/ttf;base64,{{ $fontData }}') format('truetype');
            }

            body,
            h1,
            h2,
            h3,
            h4,
            h5,
            h6,
            th,
            td,
            strong {
                font-family: 'jpfont', 'DejaVu Sans', sans-serif;
            }
        @else
            body {
                font-family: 'DejaVu Sans', sans-serif;
            }
        @endif
        body {
            font-size: 11px;
        }

        .currency {
            font-family: 'DejaVu Sans', 'jpfont', sans-serif;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        * {
            unicode-bidi: plaintext;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>入金レポート</h1>
        <p>{{ now()->format('Y年n月j日 g:i A') }} 時点</p>
    </div>

    @if (isset($parameters['year']) && isset($parameters['month']))
        <p><strong>対象期間:</strong> {{ $parameters['year'] }}年 {{ $parameters['month'] }}月</p>
    @endif

    <table>
        <thead>
            <tr>
                <th>顧客番号</th>
                <th>顧客名</th>
                <th>月</th>
                <th>年</th>
                <th>金額</th>
                <th>入金日</th>
                <th>受付番号</th>
                <th>ステータス</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($payments as $payment)
                <tr>
                    <td>{{ $payment->customer->customer_number }}</td>
                    <td>{{ $payment->customer->name }}</td>
                    <td>{{ $payment->payment_month }}</td>
                    <td>{{ $payment->payment_year }}</td>
                    <td class="currency">{{ number_format($payment->amount, 2) }}</td>
                    <td>{{ optional($payment->payment_date)->format('Y-m-d') }}</td>
                    <td>{{ $payment->receipt_number }}</td>
                    <td>{{ ucfirst($payment->status) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
