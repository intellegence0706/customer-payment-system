<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="utf-8" />
  <title>入金はがき</title>

  {{-- Page margin for Dompdf --}}
  <style>@page { margin: 6mm; }</style>

  {{-- Base fonts (file + optional Base64) --}}
  @php $fontData = $embeddedFontBase64 ?? null; @endphp
  <style>
    @font-face {
      font-family: "NotoSansJP";
      font-weight: 400;
      font-style: normal;
      src: url("{{ storage_path('fonts/NotoSansJP.ttf') }}") format("truetype");
    }
    @font-face {
      font-family: "NotoSansJP";
      font-weight: 700;
      font-style: normal;
      src: url("{{ storage_path('fonts/NotoSansJP.ttf') }}") format("truetype");
    }
    {{ $fontData ? "
    @font-face{
      font-family:'jpfont';font-weight:400;font-style:normal;
      src:url(\"data:font/ttf;base64,$fontData\") format('truetype');
    }
    @font-face{
      font-family:'jpfont';font-weight:700;font-style:normal;
      src:url(\"data:font/ttf;base64,$fontData\") format('truetype');
    }" : "" }}
    html,body{
      margin:0;padding:0;
      color:#111827; /* gray-900 */
      font-size:12px; line-height:1.55;
      font-family: {{ $fontData ? "'jpfont'," : "" }}"NotoSansJP","DejaVu Sans",sans-serif;
    }
    .pc {
      width:148mm; height:105mm; box-sizing:border-box;
      border:0.3mm solid #e5e7eb; border-radius:2mm;
      padding:8mm; page-break-after:always; position:relative; background:#fff;
    }
    .pc:last-child{ page-break-after:avoid; }

    /* Header (logo + date) */
    .top{ width:100%; margin-bottom:4mm; }
    .brand{ font-weight:700; font-size:14px; letter-spacing:.04em; }
    .date{ text-align:right; color:#6b7280; font-size:11px; } /* gray-500 */

    /* Amount ribbon */
    .ribbon{
      margin:2mm 0 5mm 0; padding:2.5mm 3.5mm;
      background:#111827; color:#ffffff; border-radius:1.5mm;
      display:inline-block; min-width:60mm;
    }
    .ribbon .label{ font-size:10px; letter-spacing:.08em; opacity:.85; }
    .ribbon .value{ font-size:19px; font-weight:700; letter-spacing:.02em; }
    .ribbon .value .unit{ font-size:85%; opacity:.9; margin-left:0.8mm; }

    /* Two easy blocks */
    .block{ border:0.3mm solid #f1f5f9; border-radius:1.5mm; padding:3mm; margin-bottom:4mm; }
    .title{ margin:0 0 2mm 0; font-weight:700; font-size:12px; letter-spacing:.04em; }

    .kv{ margin:0; }
    .kv span{ color:#6b7280; } /* keys muted */
    .name{ font-size:12.5px; }
    .name .sama{ color:#6b7280; font-weight:400; margin-left:1mm; }
    table.pay{ width:100%; border-collapse:collapse; font-variant-numeric:tabular-nums; }
    table.pay th, table.pay td{ border:0.3mm solid #f3f4f6; padding:2mm; vertical-align:top; }
    table.pay th{ width:30mm; text-align:left; background:#fafafa; font-weight:700; }
    .num{ text-align:right; }
    .amount{ font-weight:700; letter-spacing:.02em; }
    .unit{ font-size:85%; color:#6b7280; margin-left:0.6mm; }

    .foot{
      position:absolute; left:8mm; right:8mm; bottom:6mm;
      text-align:center; color:#6b7280; font-size:10px;
      border-top:0.3mm solid #f3f4f6; padding-top:2mm;
    }

    .row{ width:100%; }
    .left{ float:left; width:56%; }
    .right{ float:right; width:40%; }
    .clearfix:after{ content:""; display:table; clear:both; }
  </style>
</head>
<body>

@foreach ($postcardData as $data)
  <div class="pc">
    <table class="top">
      <tr>
        <td class="brand">顧客管理システム</td>
        <td class="date">{{ now()->format('Y年n月j日') }}</td>
      </tr>
    </table>

    @php
      $hasAmt = !empty($data['当月入金']);
      $amtValue = $hasAmt ? number_format($data['当月入金']->amount) : null;
      $label = '当月（'. $data['当月名'] .'）入金額';
    @endphp
    <div class="ribbon">
      <div class="label">{{ $label }}</div>
      <div class="value">
        @if ($hasAmt)
          <span class="amount">{{ $amtValue }}</span><span class="unit">円</span>
        @else
          —
        @endif
      </div>
    </div>

    <div class="row clearfix">
      <div class="left">
        <div class="block">
          <p class="title">ご住所・ご氏名</p>
          <p class="kv name"><strong>{{ $data['顧客']->name }}</strong><span class="sama">様</span></p>
          <p class="kv">
            @if (!empty($data['顧客']->postal_code))
              <span>〒</span>{{ $data['顧客']->postal_code }}
            @endif
            {{ $data['顧客']->address }}
          </p>
          @if (!empty($data['顧客']->customer_number))
            <p class="kv"><span>顧客番号：</span>{{ $data['顧客']->customer_number }}</p>
          @endif
        </div>
      </div>

      <div class="right">
        <div class="block">
          <p class="title">入金情報の詳細</p>
          <table class="pay">
            <tr>
              <th>当月（{{ $data['当月名'] }}）</th>
              <td>
                @if ($data['当月入金'])
                  <div class="num">入金額：<span class="amount">{{ number_format($data['当月入金']->amount) }}</span><span class="unit">円</span></div>
                  <div>入金日：{{ $data['当月入金']->payment_date->format('Y年n月j日') }}</div>
                  @if (!empty($data['当月入金']->receipt_number))
                    <div>受付番号：{{ $data['当月入金']->receipt_number }}</div>
                  @endif
                @else
                  <em>当月の入金記録はありません</em>
                @endif
              </td>
            </tr>
            <tr>
              <th>前月（{{ $data['前月名'] }}）</th>
              <td>
                @if ($data['前月入金'])
                  <div class="num">入金額：<span class="amount">{{ number_format($data['前月入金']->amount) }}</span><span class="unit">円</span></div>
                  @if (!empty($data['前月入金']->receipt_number))
                    <div>受付番号：{{ $data['前月入金']->receipt_number }}</div>
                  @endif
                @else
                  <em>入金記録なし</em>
                @endif
              </td>
            </tr>
          </table>
        </div>
      </div>
    </div>

    <div class="foot">
      いつもご入金ありがとうございます。ご不明点は事務所までお問い合わせください。
    </div>
  </div>
@endforeach

</body>
</html>
