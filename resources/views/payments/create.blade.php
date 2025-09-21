@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">新規入金</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> 入金一覧に戻る
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <form method="POST" action="{{ route('payments.store') }}">
            @csrf
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">入金情報</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="customer_code_input" class="form-label">顧客コード <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="customer_code_input" placeholder="顧客コードを入力">
                                <button type="button" class="btn btn-outline-secondary" id="customer_code_search_btn">検索</button>
                            </div>
                            <input type="hidden" name="customer_id" id="customer_id" value="{{ old('customer_id') }}">
                            <div id="customer_info_display" class="form-text mt-1">未選択</div>
                            @error('customer_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div id="customerDataset" class="d-none">
                            @foreach($customers as $c)
                            <span class="cust" data-id="{{ $c->id }}" data-code="{{ $c->customer_number }}" data-name="{{ $c->name }}"></span>
                            @endforeach
                        </div>
                        <div class="col-md-3">
                            <label for="payment_month" class="form-label">月 <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('payment_month') is-invalid @enderror" id="payment_month" name="payment_month" min="1" max="12" value="{{ old('payment_month') }}" required>
                            @error('payment_month')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="payment_year" class="form-label">年 <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('payment_year') is-invalid @enderror" id="payment_year" name="payment_year" min="2020" value="{{ old('payment_year') }}" required>
                            @error('payment_year')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="amount" class="form-label">金額 <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" value="{{ old('amount') }}" required>
                            @error('amount')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="payment_date" class="form-label">入金日 <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('payment_date') is-invalid @enderror" id="payment_date" name="payment_date" value="{{ old('payment_date') }}" required>
                            @error('payment_date')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="receipt_number" class="form-label">領収書番号</label>
                            <input type="text" class="form-control @error('receipt_number') is-invalid @enderror" id="receipt_number" name="receipt_number" value="{{ old('receipt_number') }}">
                            @error('receipt_number')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">状態 <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>保留</option>
                                <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>完了</option>
                                <option value="failed" {{ old('status') == 'failed' ? 'selected' : '' }}>失敗</option>
                            </select>
                            @error('status')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label for="notes" class="form-label">備考</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
                            @error('notes')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- お知らせ -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="far fa-bell me-1"></i> お知らせ</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary text-white" id="addNoticeRowBtn"><i class="fas fa-plus"></i> 行を追加</button>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle" id="noticeItemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 60px;">No</th>
                                    <th style="width: 130px;">日付</th>
                                    <th style="width: 240px;">内容</th>
                                    <th style="width: 120px;">数量</th>
                                    <th style="width: 140px;">単価</th>
                                    <th style="width: 140px;">金額</th>
                                    <th style="width: 130px;">税率(%)</th>
                                    <th style="width: 140px;">税額</th>
                                    <th style="width: 220px;">備考</th>
                                    <th style="width: 120px;">操作</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 当月レンタル請求 -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="far fa-calendar-check me-1"></i> 当月レンタル請求</h5>
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-nowrap small">請求合計: <span id="total-current" class="section-total">¥0</span></div>
                        <button type="button" class="btn btn-sm btn-outline-primary text-white" id="addCurrentRowBtn"><i class="fas fa-plus"></i> 行を追加</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle" id="currentItemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 60px;">No</th>
                                    <th style="width: 130px;">日付</th>
                                    <th style="width: 140px;">商品コード</th>
                                    <th style="width: 240px;">商品名</th>
                                    <th style="width: 120px;">数量</th>
                                    <th style="width: 140px;">単価</th>
                                    <th style="width: 140px;">金額</th>
                                    <th style="width: 130px;">税率(%)</th>
                                    <th style="width: 140px;">税額</th>
                                    <th style="width: 220px;">備考</th>
                                    <th style="width: 120px;">操作</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 先月請求 -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="far fa-calendar-minus me-1"></i> 先月請求</h5>
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-nowrap small">請求合計: <span id="total-previous" class="section-total">¥0</span></div>
                        <button type="button" class="btn btn-sm btn-outline-primary text-white" id="addPreviousRowBtn"><i class="fas fa-plus"></i> 行を追加</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle" id="previousItemsTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 60px;">No</th>
                                    <th style="width: 130px;">日付</th>
                                    <th style="width: 140px;">商品コード</th>
                                    <th style="width: 240px;">商品名</th>
                                    <th style="width: 120px;">数量</th>
                                    <th style="width: 140px;">単価</th>
                                    <th style="width: 140px;">金額</th>
                                    <th style="width: 130px;">税率(%)</th>
                                    <th style="width: 140px;">税額</th>
                                    <th style="width: 220px;">備考</th>
                                    <th style="width: 120px;">操作</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- その他請求 -->
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="far fa-receipt me-1"></i> その他請求</h5>
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-nowrap small">請求合計: <span id="total-other-charges" class="section-total">¥0</span></div>
                        <button type="button" class="btn btn-sm btn-outline-primary text-white" id="addOtherChargeRowBtn"><i class="fas fa-plus"></i>行を追加</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle" id="otherChargesTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 60px;">No</th>
                                    <th style="width: 130px;">日付</th>
                                    <th style="width: 140px;">商品コード</th>
                                    <th style="width: 240px;">商品名</th>
                                    <th style="width: 120px;">数量</th>
                                    <th style="width: 140px;">単価</th>
                                    <th style="width: 140px;">金額</th>
                                    <th style="width: 130px;">税率(%)</th>
                                    <th style="width: 140px;">税額</th>
                                    <th style="width: 220px;">備考</th>
                                    <th style="width: 120px;">操作</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- 集計エリア -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">集計</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3 sticky-summary">
                        <div class="col-md-3 ms-auto">
                            <label class="form-label">小計</label>
                            <div class="input-group">
                                <span class="input-group-text">¥</span>
                                <input type="text" readonly class="form-control text-end" id="subtotal_amount" name="subtotal_amount">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">税額合計</label>
                            <div class="input-group">
                                <span class="input-group-text">¥</span>
                                <input type="text" readonly class="form-control text-end" id="tax_total" name="tax_total">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">その他請求合計</label>
                            <div class="input-group">
                                <span class="input-group-text">¥</span>
                                <input type="text" readonly class="form-control text-end" id="other_fees_total" name="other_fees_total">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">総合計</label>
                            <div class="input-group">
                                <span class="input-group-text">¥</span>
                                <input type="text" readonly class="form-control text-end" id="grand_total" name="grand_total">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="far fa-hand-holding-usd me-1"></i> その他入金</h5>
                    <div class="d-flex align-items-center gap-3">
                        <div class="text-nowrap small">入金合計: <span id="total-other-payments" class="section-total">¥0</span></div>
                        <button type="button" class="btn btn-sm btn-outline-primary text-white" id="addOtherPaymentBtn"><i class="fas fa-plus"></i> 入金行を追加</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle" id="otherPaymentsTable">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 60px;">No</th>
                                    <th style="width: 160px;">入金日</th>
                                    <th style="width: 200px;">入金金額</th>
                                    <th>摘要</th>
                                    <th style="width: 120px;">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary me-md-2">キャンセル</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> 入金を保存
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<style>
    /* Layout polish */
    .card {
        box-shadow: 0 3px 12px rgba(17, 24, 39, .06);
        border-radius: 12px;
        overflow: hidden;
    }

    .card-header {
        background: linear-gradient(90deg, #6c5ce7 0%, #a66cff 100%);
        color: #fff;
    }

    .card-header h5 {
        display: flex;
        align-items: center;
        gap: .5rem;
        margin: 0;
    }

    .card-header h5::before {
        content: "";
        display: inline-block;
        width: 10px;
        height: 10px;
        border-radius: 50%;
        background: rgba(255, 255, 255, .85);
    }

    /* Tables */
    .table-sm thead th {
        position: sticky;
        top: 0;
        z-index: 1;
        background: #f8fafc !important;
    }

    .table-sm tbody tr:hover {
        background: #f9f9ff;
    }

    .table-sm input,
    .table-sm select {
        height: 34px;
    }

    .table-sm input[type="number"],
    .text-end {
        text-align: right;
    }

    .section-total {
        background: #fff;
        color: #6c5ce7;
        border: 1px solid #eae7ff;
        padding: .25rem .6rem;
        border-radius: 999px;
        font-weight: 700;
        box-shadow: 0 1px 4px rgba(108, 92, 231, .15);
    }

    .sticky-summary {
        position: sticky;
        bottom: 0;
        background: rgba(255, 255, 255, .95);
        backdrop-filter: blur(6px);
        padding-top: .75rem;
        border-top: 1px solid #ececf6;
        box-shadow: 0 -6px 20px rgba(0, 0, 0, .04);
    }

    .sticky-summary .input-group-text {
        background: #f3f0ff;
        color: #6c5ce7;
        font-weight: 600;
    }

    .btn-outline-primary {
        --bs-btn-color: #6c5ce7;
        --bs-btn-border-color: #6c5ce7;
        --bs-btn-hover-bg: #6c5ce7;
        --bs-btn-hover-border-color: #6c5ce7;
    }

    .btn-primary {
        background: #6c5ce7;
        border-color: #6c5ce7;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // 顧客一覧（軽量データ）: data-* 属性から配列化
        const customerList = Array.from(document.querySelectorAll('#customerDataset .cust')).map(function(el) {
            return {
                id: el.getAttribute('data-id'),
                code: el.getAttribute('data-code') || '',
                name: el.getAttribute('data-name') || ''
            };
        });
        const sections = {
            notice: {
                tbody: document.querySelector('#noticeItemsTable tbody'),
                addBtn: document.getElementById('addNoticeRowBtn'),
                totalEl: null,
                category: 'notice'
            },
            current: {
                tbody: document.querySelector('#currentItemsTable tbody'),
                addBtn: document.getElementById('addCurrentRowBtn'),
                totalEl: document.getElementById('total-current'),
                category: ''
            },
            previous: {
                tbody: document.querySelector('#previousItemsTable tbody'),
                addBtn: document.getElementById('addPreviousRowBtn'),
                totalEl: document.getElementById('total-previous'),
                category: 'previous_balance'
            },
            otherCharges: {
                tbody: document.querySelector('#otherChargesTable tbody'),
                addBtn: document.getElementById('addOtherChargeRowBtn'),
                totalEl: document.getElementById('total-other-charges'),
                category: 'other_charges'
            }
        };

        const otherPayments = {
            tbody: document.querySelector('#otherPaymentsTable tbody'),
            addBtn: document.getElementById('addOtherPaymentBtn'),
            totalEl: document.getElementById('total-other-payments')
        };

        function toNumber(v) {
            const n = parseFloat((v || '').toString().replace(/[^0-9.\-]/g, ''));
            return isNaN(n) ? 0 : n;
        }

        function formatYen(n) {
            return (n || 0).toLocaleString('ja-JP', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function nextItemIndex() {
            return sections.notice.tbody.children.length +
                sections.current.tbody.children.length +
                sections.previous.tbody.children.length +
                sections.otherCharges.tbody.children.length;
        }

        function recalcTotals() {
            let subtotal = 0,
                taxTotal = 0,
                otherFeesTotal = 0;
            const bySection = {
                notice: 0,
                current: 0,
                previous: 0,
                otherCharges: 0
            };

            Object.entries(sections).forEach(([key, sec]) => {
                sec.tbody.querySelectorAll('tr').forEach((tr) => {
                    const qty = toNumber(tr.querySelector('.item-qty').value);
                    const unit = toNumber(tr.querySelector('.item-unit').value);
                    const taxRate = toNumber(tr.querySelector('.item-taxrate').value);
                    const amount = qty * unit;
                    tr.querySelector('.item-amount').value = amount.toFixed(2);
                    const tax = Math.round(((amount * taxRate) / 100) * 100) / 100;
                    tr.querySelector('.item-tax').value = tax.toFixed(2);
                    subtotal += amount;
                    taxTotal += tax;
                    if (key === 'otherCharges') {
                        otherFeesTotal += amount + tax;
                    }
                    bySection[key] += amount + tax;
                });
                if (sec.totalEl) sec.totalEl.textContent = '¥' + formatYen(bySection[key]);
            });

            document.getElementById('subtotal_amount').value = formatYen(subtotal);
            document.getElementById('tax_total').value = formatYen(taxTotal);
            document.getElementById('other_fees_total').value = formatYen(otherFeesTotal);
            document.getElementById('grand_total').value = formatYen(subtotal + taxTotal + otherFeesTotal);

            const amountInput = document.getElementById('amount');
            if (amountInput) amountInput.value = (Math.round((subtotal + taxTotal + otherFeesTotal) * 100) / 100).toFixed(2);

            // Other payments total
            let otherPaySum = 0;
            otherPayments.tbody.querySelectorAll('tr').forEach((tr) => {
                const opEl = tr.querySelector('.op-amount');
                otherPaySum += toNumber(opEl ? opEl.value : '');
            });
            if (otherPayments.totalEl) otherPayments.totalEl.textContent = '¥' + formatYen(otherPaySum);
        }

        function rowTemplate(index, data) {
            return `
      <td><input type="number" class="form-control form-control-sm" name="items[${index}][row_no]" value="${index+1}"></td>
      <td><input type="date" class="form-control form-control-sm" name="items[${index}][item_date]" value="${data.item_date||''}"></td>
      <td><input type="text" class="form-control form-control-sm" name="items[${index}][product_code]" value="${data.product_code||''}" placeholder="商品コード"></td>
      <td><input type="text" class="form-control form-control-sm" name="items[${index}][product_name]" value="${data.product_name||''}" placeholder="例：車イスレンタル" required></td>
      <td><input type="number" step="0.01" class="form-control form-control-sm item-qty" name="items[${index}][quantity]" value="${data.quantity||1}"></td>
      <td><input type="number" step="0.01" class="form-control form-control-sm item-unit" name="items[${index}][unit_price]" value="${data.unit_price||0}"></td>
      <td><input type="number" step="0.01" class="form-control form-control-sm item-amount" name="items[${index}][amount]" value="${data.amount||0}" readonly></td>
      <td>
        <div class="input-group input-group-sm">
          <input type="number" step="0.01" class="form-control item-taxrate" name="items[${index}][tax_rate]" value="${data.tax_rate||10}">
          <button class="btn btn-outline-secondary" type="button" onclick="this.previousElementSibling.value=0; this.previousElementSibling.dispatchEvent(new Event('input',{bubbles:true}));">0%</button>
          <button class="btn btn-outline-secondary" type="button" onclick="this.previousElementSibling.previousElementSibling.value=8; this.previousElementSibling.previousElementSibling.dispatchEvent(new Event('input',{bubbles:true}));">8%</button>
          <button class="btn btn-outline-secondary" type="button" onclick="this.previousElementSibling.previousElementSibling.previousElementSibling.value=10; this.previousElementSibling.previousElementSibling.previousElementSibling.dispatchEvent(new Event('input',{bubbles:true}));">10%</button>
        </div>
      </td>
      <td><input type="number" step="0.01" class="form-control form-control-sm item-tax" name="items[${index}][tax_amount]" value="${data.tax_amount||0}" readonly></td>
      <td><input type="text" class="form-control form-control-sm item-note" name="items[${index}][notes]" value="${data.notes||''}" placeholder="備考（任意）"></td>
      <td class="text-nowrap">
        <button type="button" class="btn btn-sm btn-outline-secondary me-1 dupRow" title="複製">⎘</button>
        <button type="button" class="btn btn-sm btn-outline-secondary me-1 moveUp" title="上へ">↑</button>
        <button type="button" class="btn btn-sm btn-outline-secondary me-1 moveDown" title="下へ">↓</button>
        <button type="button" class="btn btn-sm btn-outline-danger delRow" title="削除">×</button>
      </td>
      <input type="hidden" name="items[${index}][category]" value="${data.category||''}">
    `;
        }

        function attachRowHandlers(tr, tbody) {
            tr.addEventListener('input', (e) => {
                if (e.target.matches('.item-qty, .item-unit, .item-taxrate')) recalcTotals();
            });
            tr.querySelector('.delRow').addEventListener('click', () => {
                tr.remove();
                recalcTotals();
            });
            tr.querySelector('.dupRow').addEventListener('click', () => {
                const inputs = tr.querySelectorAll('input');
                const data = {};
                inputs.forEach(i => {
                    const m = i.name && i.name.match(/items\[[0-9]+\]\[(.+)\]/);
                    if (m) {
                        data[m[1]] = i.value;
                    }
                });
                addRow(tbody.dataset.section, data);
            });
            tr.querySelector('.moveUp').addEventListener('click', () => {
                const prev = tr.previousElementSibling;
                if (prev) {
                    tbody.insertBefore(tr, prev);
                }
            });
            tr.querySelector('.moveDown').addEventListener('click', () => {
                const next = tr.nextElementSibling;
                if (next) {
                    tbody.insertBefore(next, tr);
                }
            });
        }

        function addRow(sectionKey, data = {}) {
            const sec = sections[sectionKey];
            const index = nextItemIndex();
            const tr = document.createElement('tr');
            sec.tbody.dataset.section = sectionKey;
            tr.innerHTML = rowTemplate(index, {
                ...data,
                category: sec.category
            });
            sec.tbody.appendChild(tr);
            attachRowHandlers(tr, sec.tbody);
            recalcTotals();
        }

        function addOtherPaymentRow(data = {}) {
            const index = otherPayments.tbody.children.length;
            const tr = document.createElement('tr');
            tr.innerHTML = `
      <td><input type="number" class="form-control form-control-sm" name="other_payments[${index}][row_no]" value="${index+1}"></td>
      <td><input type="date" class="form-control form-control-sm" name="other_payments[${index}][item_date]" value="${data.item_date||''}"></td>
      <td>
        <div class="input-group input-group-sm">
          <span class="input-group-text">¥</span>
          <input type="number" step="0.01" class="form-control op-amount" name="other_payments[${index}][amount]" value="${data.amount||''}">
        </div>
        <input type="hidden" name="other_payments[${index}][category]" value="other_payment">
      </td>
      <td><input type="text" class="form-control form-control-sm" name="other_payments[${index}][notes]" value="${data.notes||''}" placeholder="摘要"></td>
      <td class="text-nowrap">
        <button type="button" class="btn btn-sm btn-outline-danger delRow">×</button>
      </td>
    `;
            otherPayments.tbody.appendChild(tr);
            tr.querySelector('.delRow').addEventListener('click', () => {
                tr.remove();
                recalcTotals();
            });
            tr.addEventListener('input', () => recalcTotals());
            recalcTotals();
        }

        sections.notice.addBtn.addEventListener('click', () => addRow('notice'));
        sections.current.addBtn.addEventListener('click', () => addRow('current'));
        sections.previous.addBtn.addEventListener('click', () => addRow('previous'));
        sections.otherCharges.addBtn.addEventListener('click', () => addRow('otherCharges'));
        otherPayments.addBtn.addEventListener('click', () => addOtherPaymentRow());

        // 初期行
        addRow('current');

        // 顧客コード検索（正規化して一致）
        const codeInput = document.getElementById('customer_code_input');
        const codeBtn = document.getElementById('customer_code_search_btn');
        const customerIdHidden = document.getElementById('customer_id');
        const infoEl = document.getElementById('customer_info_display');

        function toHalfWidthDigits(str) {
            return (str || '').replace(/[０-９]/g, c => String.fromCharCode(c.charCodeAt(0) - 65248));
        }

        function normalizeCode(str) {
            const half = toHalfWidthDigits(String(str || ''));
            return half.replace(/[^0-9A-Za-z]/g, '').toUpperCase().replace(/^0+/, '');
        }

        function findCustomerByCode(input) {
            const target = normalizeCode(input);
            if (!target) return null;
            // 厳密一致 → 末尾一致（ゼロ埋め差異吸収）
            let found = customerList.find(c => normalizeCode(c.code) === target);
            if (!found) {
                found = customerList.find(c => normalizeCode(c.code).endsWith(target) || target.endsWith(normalizeCode(c.code)));
            }
            return found || null;
        }

        function renderCustomerInfo(cust) {
            if (!infoEl) return;
            infoEl.textContent = cust ? `${cust.name} (${cust.code})` : '未選択';
        }

        function setCustomer(cust) {
            if (cust) {
                customerIdHidden.value = cust.id;
                renderCustomerInfo(cust);
            } else {
                customerIdHidden.value = '';
                renderCustomerInfo(null);
            }
        }

        function performLookup() {
            const value = codeInput ? codeInput.value : '';
            const local = findCustomerByCode(value);
            if (local) {
                setCustomer(local);
                return;
            }
            fetch(`{{ route('api.customers.by-code') }}?code=${encodeURIComponent(value)}`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (data && data.found && data.customer) {
                        setCustomer(data.customer);
                    } else {
                        alert('該当の顧客コードが見つかりません');
                        setCustomer(null);
                    }
                })
                .catch(() => {
                    alert('検索に失敗しました');
                });
        }
        if (codeBtn) {
            codeBtn.addEventListener('click', performLookup);
        }
        if (codeInput) {
            codeInput.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    performLookup();
                }
            });
        }
        // ページ初期表示で既存選択があれば表示
        (function initFromHidden() {
            const currentId = customerIdHidden.value;
            if (currentId) {
                const cust = customerList.find(c => String(c.id) === String(currentId));
                renderCustomerInfo(cust || null);
            }
        })();
    });
</script>
@endsection