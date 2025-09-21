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
  return ['令和',$ey,$m,$d];
  } elseif ($date->greaterThanOrEqualTo($heiseiStart)) {
  $ey = $y - 1988;
  return ['平成',$ey,$m,$d];
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
  return trim(sprintf('%s%s年%d月%d日', $era, $eyStr, $m, $d));
  }
  }
  if (!function_exists('wareki_ym')) {
  function wareki_ym(int $year, int $month): string {
  $dt = Carbon::create($year, $month, 1);
  [$era, $ey, $m, $_] = wareki_parts($dt);
  $eyStr = sprintf('%02d', (int) $ey);
  return trim(sprintf('%s%s年%d月分', $era, $eyStr, $m));
  }
  }
  @endphp
  <style>
    /* Embed JP font explicitly for Dompdf */
    @font-face {
      font-family: 'NotoSansJP';
      font-style: normal;
      font-weight: 400;
      src: url("{{ resource_path('fonts/NotoSansJP.ttf') }}") format('truetype');
    }

    html,
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
      font-family: 'NotoSansJP', 'DejaVu Sans', sans-serif;
    }

    body {
      font-variant-numeric: tabular-nums;
    }

    .postcard {
      width: 148mm;
      height: 105mm;
      padding: 6mm;
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
    <div style="display:flex; gap:6mm; height:100%; box-sizing:border-box;">
      <!-- Left side: address face -->
      <div style="flex:1; display:flex; flex-direction:column;">
        <!-- Front: address face -->
        <div class="block" style="margin-bottom:3mm; min-height:40mm; border:1px solid #d1d5db;">
          <div style="font-size:11px; color:#6b7280;">郵便 はがき</div>
          <div style="margin-top:3mm;">〒{{ $row['postal_code'] ?? '' }}</div>
          <div style="white-space:pre-line; line-height:1.6;">{{ $row['address'] ?? '' }}</div>
          <div style="margin-top:3mm; font-size:16px; letter-spacing:2px;">{{ $row['recipient_name'] }} 様</div>
        </div>

        <div class="block" style="margin-top:auto;">
          <div>フォーユー福祉用具貸与事業所</div>
          @if (!empty($row['company_postal_code']) || !empty($row['company_address']))
          <div style="margin-top:1mm;">〒{{ $row['company_postal_code'] ?? '' }} {{ $row['company_address'] ?? '' }}</div>
          @endif
        </div>
      </div>

      <div style="flex:1;">

        <div style="width:49%; float:left; box-sizing:border-box; padding-right:3mm;">
          <div class="header" style="text-align:left; margin-bottom:2mm;">
            <div class="brand">{{ '令和' . sprintf('%02d', ((int)$year - 2018)) . '年 ' . (int)$month . '月分　御請求書' }}</div>
          </div>
          <div class="block" style="margin-bottom:3mm;">
            <div style="font-size:11px; color:#6b7280;">合計金額（税込）</div>
            <div>¥{{ number_format((float)($row['current_amount'] ?? 0)) }}</div>
          </div>
          <div class="block" style="margin-bottom:3mm; display:flex; justify-content:space-between;">
            <div style="font-size:11px; color:#6b7280;">※振替日</div>
            <div>{{ wareki_ymd($row['scheduled_debit_date'] ?? '') }}</div>
          </div>
          <div class="block" style="flex:1;">
            <div style="margin-bottom:2mm;">当月明細</div>
            <div style="width:100%; font-variant-numeric: tabular-nums;">
              @foreach (($row['current_items'] ?? []) as $it)
              <div style="display:flex; border-bottom:1px solid #e5e7eb; padding:2px 0;">
                <div style="width:22%;">{{ $it['date'] }}</div>
                <div style="flex:1;">
                  <div style="display:flex; justify-content:space-between; gap:6px;">
                    <span>{{ $it['name'] }}</span>
                    <span>¥{{ number_format((float)($it['amount'] ?? 0)) }}</span>
                  </div>
                </div>
              </div>
              @endforeach
            </div>
          </div>
        </div>
        <div style="width:49%; float:left; box-sizing:border-box; padding-left:3mm;">
          <div class="header" style="text-align:left; margin:4mm 0 3mm;">
            <div class="brand">{{ '令和' . sprintf('%02d', ((int)($row['previous_year'] ?? $year) - 2018)) . '年 ' . (int)($row['previous_month'] ?? 0) . '月分　領収書' }}</div>
          </div>
          <div class="block" style="margin-bottom:3mm;">
            <div style="font-size:11px; color:#6b7280;">対象</div>
            <div>{{ (int)($row['previous_year'] ?? 0) }}年 {{ (int)($row['previous_month'] ?? 0) }}月</div>
          </div>
          <div class="block" style="flex:1;">
            <div style="margin-bottom:2mm;">明細</div>
            <div style="width:100%; font-variant-numeric: tabular-nums;">
              @foreach (($row['previous_items'] ?? []) as $it)
              <div style="display:flex; border-bottom:1px solid #e5e7eb; padding:2px 0;">
                <div style="width:22%;">{{ $it['date'] }}</div>
                <div style="flex:1;">
                  <div style="display:flex; justify-content:space-between; gap:6px;">
                    <span>{{ $it['name'] }}</span>
                    <span>¥{{ number_format((float)($it['amount'] ?? 0)) }}</span>
                  </div>
                </div>
              </div>
              @endforeach
            </div>
            <div style="text-align:right; margin-top:2mm;">合計: ¥{{ number_format((float)($row['previous_amount'] ?? 0)) }}</div>
          </div>
        </div>
        <div style="clear:both;"></div>
      </div>
    </div>
  </div>
  @endforeach
</body>

</html>