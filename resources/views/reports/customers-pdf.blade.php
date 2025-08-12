<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>顧客レポート</title>
    <style>
        @php $fontData = $embeddedFontBase64 ?? null; @endphp
        /* Embed Japanese font directly to avoid path/chroot issues */
        @if($fontData)
        /* Register same JP font for normal & bold to avoid fallback to non-CJK fonts */
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
        body, h1, h2, h3, h4, h5, h6, th, td, strong { font-family: 'jpfont', 'DejaVu Sans', sans-serif; }
        body { font-size: 11px; }
        .currency { font-family: 'DejaVu Sans', 'jpfont', sans-serif; }
        @else
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 12px; }
        @endif
        .header { text-align: center; margin-bottom: 30px; }
        .info { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        /* Ensure unicode is preserved */
        * { unicode-bidi: plaintext; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>顧客レポート</h1>
        <p>{{ now()->format('Y年n月j日 g:i A') }} 時点</p>
    </div>

    <div class="info">
        <strong>レポート条件:</strong><br>
        @if(isset($parameters['date_from']))
            期間開始: {{ $parameters['date_from'] }}<br>
        @endif
        @if(isset($parameters['date_to']))
            期間終了: {{ $parameters['date_to'] }}<br>
        @endif
        @if(isset($parameters['gender']))
            性別: {{ ucfirst($parameters['gender']) }}<br>
        @endif
        @if(isset($parameters['bank_name']))
            銀行: {{ $parameters['bank_name'] }}<br>
        @endif
        合計件数: {{ $customers->count() }}
    </div>

    <table>
        <thead>
            <tr>
                <th>顧客番号</th>
                <th>氏名</th>
                <th>性別</th>
                <th>電話番号</th>
                <th>銀行</th>
                <th>口座番号</th>
                <th>作成日</th>
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
        <p>顧客管理システム - 機密レポート</p>
    </div>
</body>
</html>
