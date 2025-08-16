<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>はがき印刷データ</title>
    @php
        use Carbon\Carbon;

        if (!function_exists('wareki_parts')) {
            function wareki_parts(Carbon $date): array {
                $y = (int) $date->year; $m = (int) $date->month; $d = (int) $date->day;
                // Era boundaries
                $reiwaStart = Carbon::create(2019, 5, 1);
                $heiseiStart = Carbon::create(1989, 1, 8);
                if ($date->greaterThanOrEqualTo($reiwaStart)) {
                    $ey = $y - 2018; // Reiwa 1 = 2019
                    return ['令和', $ey, $m, $d];
                } elseif ($date->greaterThanOrEqualTo($heiseiStart)) {
                    $ey = $y - 1988; // Heisei 1 = 1989
                    return ['平成', $ey, $m, $d];
                }
                // Fallback: Show Gregorian with no era
                return ['', $y, $m, $d];
            }
        }
        if (!function_exists('wareki_ymd')) {
            function wareki_ymd($date): string {
                if (!$date) { return ''; }
                $dt = $date instanceof Carbon ? $date : Carbon::parse($date);
                [$era, $ey, $m, $d] = wareki_parts($dt);
                // zero-pad era year to 2 digits to match example (e.g., 07)
                $eyStr = sprintf('%02d', (int) $ey);
                return trim(sprintf('%s%s年　%d月　%d日', $era, $eyStr, $m, $d));
            }
        }
        if (!function_exists('wareki_ym')) {
            function wareki_ym(int $year, int $month): string {
                $dt = Carbon::create($year, $month, 1);
                [$era, $ey, $m, $_] = wareki_parts($dt);
                $eyStr = sprintf('%02d', (int) $ey);
                return trim(sprintf('%s%s年　%d月分', $era, $eyStr, $m));
            }
        }
    @endphp
    <style>
        /* Embed JP font explicitly for Dompdf */
        @font-face {
            font-family: 'NotoSansJP';
            font-style: normal;
            font-weight: 400;
            src: url('{{ resource_path('fonts/NotoSansJP.ttf') }}') format('truetype');
        }
        html, body, h1, h2, h3, h4, h5, h6, th, td, strong,
        .amount, .customer-info, .payment-info, .footer, .logo {
            font-family: 'NotoSansJP', 'DejaVu Sans', sans-serif;
        }
        body { font-variant-numeric: tabular-nums; }

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
                <h3>{{ wareki_ym((int)$year, (int)$month) }}　御請求書</h3>
            </div>
            <div class="customer-info">
                <strong>{{ $row['recipient_name'] }}</strong>
                @if (!empty($row['customer_number']))<span> / No. {{ $row['customer_number'] }}</span>@endif
            </div>
            <div>
                <p>下記のとおり御請求いたします。</p>
                <div>
                    合計金額（税込）
                    <span class="amount">¥{{ number_format((float)($row['amount_total'] ?? 0)) }}</span>
                </div>
            </div>
            <div style="border:1px solid #000; border-radius:3px; padding:6px; margin:10px 0; display:flex; justify-content:space-between; align-items:center;">
                <div style="font-size:10px; color:#666;">※振替日</div>
                <div style="font-weight:bold;">{{ wareki_ymd($row['transfer_date'] ?? '') }}</div>
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
