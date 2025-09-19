@extends('layouts.app')

@push('styles')
<style>
  :root{ --brand1:#4CB8E0; --brand2:#DC5D9D; }
  /* Hero */
  .card-hero{
    background:linear-gradient(135deg,var(--brand1),var(--brand2));
    color:#fff; border:0;
  }
  .card-hero .amount{font-weight:800; letter-spacing:.02em; font-size:clamp(1.6rem,2.8vw,2.4rem)}
  .card-hero .sub{opacity:.9}
  .badge-soft{background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.35); color:#fff}
  .shadow-soft{box-shadow:0 10px 30px rgba(0,0,0,.06)}

  /* Table */
  .table-sticky thead th{position:sticky; top:0; z-index:2; background:#f8f9fa}
  .font-mono{font-family: ui-monospace,SFMono-Regular,Menlo,Monaco,Consolas,"Liberation Mono","Courier New",monospace;}

  /* XLSX table */
  .xlsx-card .table{font-size:.875rem}
  .xlsx-table thead th{position:sticky; top:0; z-index:1; background:#f8f9fa; box-shadow:inset 0 -1px 0 #e9ecef}
  .xlsx-table th,.xlsx-table td{white-space:nowrap}
  .xlsx-table tbody tr:nth-child(odd){background:#fcfdff}
  .scroll-x{overflow-x:auto}
  .scroll-x::-webkit-scrollbar{height:8px}
  .scroll-x::-webkit-scrollbar-thumb{background:#c7d2fe; border-radius:4px}

  /* Ribbon */
  .ribbon{position:absolute; top:14px; right:-8px; background:#fff; color:#000; padding:.35rem .75rem;
    border-radius:.5rem 0 0 .5rem; box-shadow:0 4px 16px rgba(0,0,0,.08)}

  /* List */
  .list-faint .list-group-item{background:transparent}
  .list-faint .label{width:7rem; color:#64748b}
  .icon{width:1.25rem; display:inline-flex; justify-content:center}

  /* Metrics */
  .metric{border-radius:1rem}

  /* Print */
  @media print{
    .btn, .navbar, .breadcrumb, .ribbon {display:none!important;}
    .card, .shadow-soft{box-shadow:none!important;}
    body{background:#fff}
    .position-sticky{position:static!important}
  }
</style>
@endpush

@section('content')
  @php
    $__items     = $payment->items ?? collect();
    $__subtotal  = $payment->subtotal_amount ?? $__items->sum(fn($it)=> (float)($it->amount ?? 0));
    $__tax       = $payment->tax_total ?? $__items->sum(fn($it)=> (float)($it->tax_amount ?? 0));
    $__others    = $payment->other_fees_total ?? $__items->where('category','other_charges')->sum(fn($it)=> (float)($it->amount ?? 0) + (float)($it->tax_amount ?? 0));
    $__grand     = $payment->grand_total ?? ($__subtotal + $__tax + $__others);

    $baseMonth   = \Carbon\Carbon::create((int)$payment->payment_year, (int)$payment->payment_month, 1);
    $defaultTransfer = $baseMonth->copy()->addMonthNoOverflow()->day(10);
    $transferDateDisplay = $payment->payment_date
        ? ($payment->payment_date instanceof \Carbon\Carbon ? $payment->payment_date->format('Y年n月j日') : date('Y年n月j日', strtotime($payment->payment_date)))
        : $defaultTransfer->format('Y年n月j日');

    $status = $payment->status ?? 'pending';
    $statusColor = $status === 'completed' ? 'success' : ($status === 'pending' ? 'warning text-dark' : 'danger');
    $statusLabel = $status === 'completed' ? '完了' : ($status === 'pending' ? '保留' : '失敗');
  @endphp

  <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-2 pb-3 mb-3 border-bottom">
    <div>
      <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-1">
          <li class="breadcrumb-item"><a href="{{ route('payments.index') }}">入金一覧</a></li>
          <li class="breadcrumb-item active" aria-current="page">入金詳細</li>
        </ol>
      </nav>
      <h1 class="h3 mb-0">入金詳細</h1>
    </div>
    <div class="btn-toolbar mb-2 mb-md-0 gap-2">
      <div class="btn-group" role="group">
        <button type="button" class="btn btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
          <i class="fas fa-file-export me-1"></i> エクスポート
        </button>
        <ul class="dropdown-menu">
          <li><a class="dropdown-item" href="{{ route('postcards.print.pdf',['payment_id'=>$payment->id]) }}" target="_blank">
            <i class="fas fa-file-pdf me-1"></i> PDF形式
          </a></li>
          <li><a class="dropdown-item" href="{{ route('postcards.print.xlsx') }}?payment_id={{ $payment->id }}">
            <i class="fas fa-file-excel me-1"></i> Excel形式 (XLSX)
          </a></li>
        </ul>
      </div>
      <a href="{{ route('payments.edit',$payment) }}" class="btn btn-outline-secondary">
        <i class="fas fa-edit me-1"></i>編集
      </a>
      <a href="{{ route('payments.index') }}" class="btn btn-outline-dark">
        <i class="fas fa-arrow-left me-1"></i>一覧に戻る
      </a>
    </div>
  </div>

  <!-- Hero / Summary -->
  <div class="card card-hero shadow-soft mb-4 position-relative">
    <div class="ribbon small">{{ $payment->payment_year }}年{{ $payment->payment_month }}月分</div>
    <div class="card-body d-flex flex-column flex-xl-row gap-4 align-items-start align-items-xl-center">
      <div class="me-xl-auto">
        <div class="sub small">ご請求金額（税込）</div>
        <div class="amount">¥{{ number_format((float)$__grand) }}</div>
        <div class="sub small">{{ $payment->customer->user_name ?? $payment->customer->name ?? 'N/A' }}（顧客番号: {{ $payment->customer->customer_number ?? 'N/A' }}）</div>
      </div>
      <div class="d-flex flex-wrap align-items-center gap-2">
        <span class="badge badge-soft rounded-pill"><i class="fas fa-calendar-day me-1"></i> 振替日: {{ $transferDateDisplay }}</span>
        <button class="badge badge-soft rounded-pill border-0" type="button"
                onclick="navigator.clipboard?.writeText('{{ $payment->receipt_number ?? '' }}')">
          <i class="fas fa-receipt me-1"></i> No. {{ $payment->receipt_number ?? '-' }}
          <span class="ms-1 text-white-50">(コピー)</span>
        </button>
        <span class="badge bg-{{ $statusColor }} rounded-pill"><i class="fas fa-circle me-1"></i>{{ $statusLabel }}</span>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-xl-8">
      <!-- 入金情報 -->
      <div class="card shadow-soft mb-4">
        <div class="card-header bg-white"><h5 class="mb-0">入金情報</h5></div>
        <div class="card-body">
          <ul class="list-group list-group-flush list-faint">
            <li class="list-group-item d-flex justify-content-between align-items-start">
              <div class="label"><span class="icon me-2"><i class="fas fa-user"></i></span>顧客</div>
              <div class="fw-semibold">{{ $payment->customer->user_name ?? $payment->customer->name ?? 'N/A' }}</div>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-start">
              <div class="label"><span class="icon me-2"><i class="fas fa-map-marker-alt"></i></span>住所</div>
              <div>{{ $payment->customer->postal_code ? '〒'.$payment->customer->postal_code.' ' : '' }}{{ $payment->customer->address ?? 'N/A' }}</div>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-start">
              <div class="label"><span class="icon me-2"><i class="far fa-calendar"></i></span>対象</div>
              <div>{{ $payment->payment_year }}年 {{ $payment->payment_month }}月</div>
            </li>
            <li class="list-group-item d-flex justify-content-between align-items-start">
              <div class="label"><span class="icon me-2"><i class="fas fa-yen-sign"></i></span>入金日</div>
              <div>{{ $payment->payment_date ? ($payment->payment_date instanceof \Carbon\Carbon ? $payment->payment_date->format('Y年n月j日') : date('Y年n月j日', strtotime($payment->payment_date))) : 'N/A' }}</div>
            </li>
            @if ($payment->notes)
            <li class="list-group-item d-flex justify-content-between align-items-start">
              <div class="label"><span class="icon me-2"><i class="far fa-sticky-note"></i></span>備考</div>
              <div class="text-break">{{ $payment->notes }}</div>
            </li>
            @endif
            <li class="list-group-item d-flex justify-content-between align-items-start">
              <div class="label"><span class="icon me-2"><i class="far fa-clock"></i></span>作成 / 更新</div>
              <div>{{ $payment->created_at->format('Y/m/d H:i') }} ／ {{ $payment->updated_at->format('Y/m/d H:i') }}</div>
            </li>
          </ul>
        </div>
      </div>

      <!-- 取込明細（XLSXの列をそのまま表示） -->
      <div class="card shadow-soft mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
          <h5 class="mb-0">取込明細（XLSX）</h5>
          <small class="text-muted">アップロード時の列構成に合わせて表示</small>
        </div>
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-sm table-bordered align-middle">
              <thead class="table-light">
                <tr>
                  <th class="text-nowrap">row</th>
                  <th class="text-nowrap">対象年月</th>
                  <!-- <th class="text-nowrap">顧客CD</th> -->
                  <th class="text-nowrap">氏名カナ</th>
                  <th class="text-nowrap">氏名</th>
                  <th class="text-nowrap">枝番</th>
                  <th class="text-nowrap">商品名</th>
                  <th class="text-end text-nowrap">数量</th>
                  <th class="text-end text-nowrap">単価</th>
                  <th class="text-end text-nowrap">金額</th>
                  <th class="text-end text-nowrap">消費税</th>
                  <th class="text-nowrap">支払区分</th>
                  <th class="text-nowrap">支払方法</th>
                </tr>
              </thead>
              <tbody>
                @php
                  $dispYm = sprintf('%04d/%02d', (int)$payment->payment_year, (int)$payment->payment_month);
                  $customerCode = $payment->customer->customer_number ?? $payment->customer->customer_code ?? '';
                  $customerKana = $payment->customer->user_kana_name ?? '';
                  $customerName = $payment->customer->user_name ?? $payment->customer->name ?? '';
                @endphp
                @forelse(($payment->items ?? collect()) as $it)
                  @php
                    $payMethod = '';
                    if (!empty($it->notes) && preg_match('/支払方法:\s*(.+)/u', (string)$it->notes, $m)) { $payMethod = $m[1]; }
                  @endphp
                  <tr>
                    <td>{{ $it->row_no ?? '' }}</td>
                    <td>{{ $dispYm }}</td>
                    <!-- <td>{{ $customerCode }}</td> -->
                    <td>{{ $customerKana }}</td>
                    <td>{{ $customerName }}</td>
                    <td>{{ $it->row_no ?? '' }}</td>
                    <td>{{ $it->product_name ?? '' }}</td>
                    <td class="text-end">{{ number_format((float)($it->quantity ?? 0), 0) }}</td>
                    <td class="text-end">{{ number_format((float)($it->unit_price ?? 0), 0) }}</td>
                    <td class="text-end">{{ number_format((float)($it->amount ?? 0), 0) }}</td>
                    <td class="text-end">{{ number_format((float)($it->tax_amount ?? 0), 0) }}</td>
                    <td>{{ $it->category ?? '' }}</td>
                    <td>{{ $payMethod }}</td>
                  </tr>
                @empty
                  <tr><td colspan="13" class="text-center text-muted">明細がありません。</td></tr>
                @endforelse
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- 明細 -->
      <div class="card shadow-soft">
        <div class="card-header d-flex justify-content-between align-items-center bg-white">
          <h5 class="mb-0">明細</h5>
          <span class="text-muted small">{{ $payment->items ? $payment->items->count() : 0 }} 件</span>
        </div>
        <div class="card-body">
          @php
            $sections = [
              'current'  => ['title' => '当月レンタル請求'],
              'previous' => ['title' => '先月請求'],
              'other'    => ['title' => 'その他徴収'],
              'notice'   => ['title' => 'お知らせ'],
            ];
          @endphp

          @if ($payment->items && $payment->items->count())
            @foreach ($sections as $key => $meta)
              @php
                $rows = isset($grouped) && isset($grouped[$key]) ? $grouped[$key] : collect();
                $totals = isset($sectionTotals) && isset($sectionTotals[$key]) ? $sectionTotals[$key] : ['subtotal'=>0,'tax_total'=>0,'gross_total'=>0];
              @endphp
              @if ($rows->count())
                <h6 class="mt-2 mb-2 d-flex justify-content-between align-items-center">
                  <span class="fw-semibold">{{ $meta['title'] }}</span>
                  <span class="badge bg-light text-dark border">小計: ¥{{ number_format((float)$totals['gross_total']) }}</span>
                </h6>
                <div class="table-responsive mb-3">
                  <table class="table table-sm table-striped table-hover table-bordered align-middle table-sticky">
                    <thead class="table-light">
                      <tr>
                        <th style="width:60px">No</th>
                        <th style="width:110px">日付</th>
                        <th style="width:140px">商品コード</th>
                        <th>商品名</th>
                        <th class="text-end" style="width:120px">数量</th>
                        <th class="text-end" style="width:140px">単価</th>
                        <th class="text-end" style="width:140px">金額</th>
                        <th class="text-end" style="width:120px">税率(%)</th>
                        <th class="text-end" style="width:140px">税額</th>
                        <th style="width:160px">区分</th>
                        <th style="width:220px">備考</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($rows as $item)
                        <tr>
                          <td>{{ $item->row_no }}</td>
                          <td>{{ $item->item_date ? (is_string($item->item_date) ? date('Y-m-d', strtotime($item->item_date)) : $item->item_date->format('Y-m-d')) : '' }}</td>
                          <td class="font-mono">{{ $item->product_code }}</td>
                          <td>{{ $item->product_name }}</td>
                          <td class="text-end">{{ number_format((float)($item->quantity ?? 0), 2) }}</td>
                          <td class="text-end">{{ number_format((float)($item->unit_price ?? 0), 2) }}</td>
                          <td class="text-end fw-semibold">{{ number_format((float)($item->amount ?? 0), 2) }}</td>
                          <td class="text-end">{{ number_format((float)($item->tax_rate ?? 0), 2) }}</td>
                          <td class="text-end">{{ number_format((float)($item->tax_amount ?? 0), 2) }}</td>
                          <td>
                            @php $cat=$item->category ?? ''; @endphp
                            <span class="badge {{ $cat==='other_charges' ? 'bg-secondary' : 'bg-info' }}">{{ $cat }}</span>
                          </td>
                          <td class="text-break">{{ $item->notes ?? '' }}</td>
                        </tr>
                      @endforeach
                    </tbody>
                    <tfoot>
                      <tr class="table-light">
                        <th colspan="6" class="text-end">小計</th>
                        <th class="text-end">{{ number_format((float)$totals['subtotal'], 2) }}</th>
                        <th class="text-end">&nbsp;</th>
                        <th class="text-end">{{ number_format((float)$totals['tax_total'], 2) }}</th>
                        <th colspan="2" class="text-end fw-semibold">合計: ¥{{ number_format((float)$totals['gross_total'], 2) }}</th>
                      </tr>
                    </tfoot>
                  </table>
                </div>
              @endif
            @endforeach
          @else
            <div class="alert alert-info mb-0"><i class="fas fa-info-circle me-1"></i>明細は登録されていません。</div>
          @endif
        </div>
      </div>
    </div>

    <!-- 集計（サイド） -->
    <div class="col-xl-4">
      @php
        $items    = $payment->items ?? collect();
        $subtotal = $payment->subtotal_amount ?? $items->sum(fn($it)=> (float)($it->amount ?? 0));
        $taxTotal = $payment->tax_total ?? $items->sum(fn($it)=> (float)($it->tax_amount ?? 0));
        $otherFees= $payment->other_fees_total ?? $items->where('category','other_charges')->sum(fn($it)=> (float)($it->amount ?? 0) + (float)($it->tax_amount ?? 0));
        $grandTotal = $payment->grand_total ?? ($subtotal + $taxTotal + $otherFees);
      @endphp

      <div class="position-sticky" style="top:1rem;">
        <div class="card shadow-soft">
          <div class="card-header bg-white"><h5 class="mb-0">集計</h5></div>
          <div class="card-body">
            <div class="vstack gap-3">
              <div class="d-flex justify-content-between metric border p-3">
                <div class="text-muted">小計</div>
                <div class="fw-semibold">¥ {{ number_format((float)$subtotal, 2) }}</div>
              </div>
              <div class="d-flex justify-content-between metric border p-3">
                <div class="text-muted">税額合計</div>
                <div class="fw-semibold">¥ {{ number_format((float)$taxTotal, 2) }}</div>
              </div>
              <div class="d-flex justify-content-between metric border p-3">
                <div class="text-muted">その他合計</div>
                <div class="fw-semibold">¥ {{ number_format((float)$otherFees, 2) }}</div>
              </div>
              <div class="d-flex justify-content-between metric border p-3 bg-light rounded-3">
                <div class="fw-bold">総合計</div>
                <div class="fw-bold">¥ {{ number_format((float)$grandTotal, 2) }}</div>
              </div>
              <div class="d-grid">
                <a href="{{ route('postcards.print.pdf',['payment_id'=>$payment->id]) }}" target="_blank" class="btn btn-primary">
                  <i class="fas fa-print me-1"></i>PDFとして印刷
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
<script>
  // (Optional) Show a toast if you have Bootstrap JS and toast markup enabled below.
  document.querySelectorAll('button[onclick*="clipboard"]').forEach(btn=>{
    btn.addEventListener('click',()=>{
      if(!window.bootstrap) return;
      const toastEl = document.getElementById('copyToast');
      if(toastEl){ new bootstrap.Toast(toastEl).show(); }
    });
  });
</script>

@if(false)
<div class="toast-container position-fixed bottom-0 end-0 p-3">
  <div id="copyToast" class="toast align-items-center text-bg-dark border-0" role="status" aria-live="polite" aria-atomic="true">
    <div class="d-flex">
      <div class="toast-body">コピーしました。</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
    </div>
  </div>
</div>


@endif
@endpush
