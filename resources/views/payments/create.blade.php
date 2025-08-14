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
                            <label for="customer_id" class="form-label">顧客 <span class="text-danger">*</span></label>
                            <select class="form-select @error('customer_id') is-invalid @enderror" id="customer_id" name="customer_id" required>
                                <option value="">顧客を選択</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }} ({{ $customer->customer_number }})
                                    </option>
                                @endforeach
                            </select>
                            @error('customer_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
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

            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">明細（利用料金・その他徴収）</h5>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addRowBtn"><i class="fas fa-plus"></i> 行を追加</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="small text-muted mb-2">
                    </div>
                    <div class="table-responsive">
                        <table class="table table-sm align-middle" id="itemsTable">
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
                                    <th style="width: 160px;">区分</th>
                                    <th style="width: 220px;">備考</th>
                                    <th style="width: 120px;">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                    <div class="row g-3 mt-3 sticky-summary">
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
                            <label class="form-label">その他合計</label>
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
.sticky-summary { position: sticky; bottom: 0; background: rgba(255,255,255,.9); backdrop-filter: blur(4px); padding-top: .75rem; border-top: 1px solid #e9ecef; }
.table-sm input { height: 34px; }
.table-sm thead th { position: sticky; top: 0; z-index: 1; }
</style>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const tbody = document.querySelector('#itemsTable tbody');
  const addBtn = document.getElementById('addRowBtn');
  const quickAddEls = document.querySelectorAll('.quick-add');

  function toNumber(v){
    const n = parseFloat((v||'').toString().replace(/[^0-9.\-]/g,''));
    return isNaN(n) ? 0 : n;
  }
  function formatYen(n){
    return (n||0).toLocaleString('ja-JP', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
  }

  function recalcTotals(){
    let subtotal=0, taxTotal=0, otherTotal=0;
    tbody.querySelectorAll('tr').forEach((tr) => {
      const qty = toNumber(tr.querySelector('.item-qty').value);
      const unit = toNumber(tr.querySelector('.item-unit').value);
      const taxRate = toNumber(tr.querySelector('.item-taxrate').value);
      const category = tr.querySelector('.item-category').value;
      const amount = qty * unit;
      tr.querySelector('.item-amount').value = amount.toFixed(2);
      const tax = Math.round(((amount * taxRate) / 100) * 100) / 100; // round to 2dp
      tr.querySelector('.item-tax').value = tax.toFixed(2);
      subtotal += amount;
      taxTotal += tax;
      if(category === 'other_charges'){ otherTotal += amount + tax; }
    });
    document.getElementById('subtotal_amount').value = formatYen(subtotal);
    document.getElementById('tax_total').value = formatYen(taxTotal);
    document.getElementById('other_fees_total').value = formatYen(otherTotal);
    document.getElementById('grand_total').value = formatYen(subtotal + taxTotal + otherTotal);
    // set overall amount field to grand total for convenience
    const amountInput = document.getElementById('amount');
    if (amountInput) amountInput.value = (Math.round((subtotal + taxTotal + otherTotal) * 100) / 100).toFixed(2);
  }

  function addRow(data={}){
    const index = tbody.children.length;
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td><input type="number" class="form-control form-control-sm" name="items[${index}][row_no]" value="${index+1}"></td>
      <td><input type="date" class="form-control form-control-sm" name="items[${index}][item_date]" value="${data.item_date||''}" placeholder="mm/dd"></td>
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
      <td>
        <select class="form-select form-select-sm item-category" name="items[${index}][category]">
          <option value="">通常</option>
          <option value="other_charges">その他徴収</option>
          <option value="notice">お知らせ</option>
          <option value="previous_balance">前月繰越</option>
        </select>
      </td>
      <td><input type="text" class="form-control form-control-sm item-note" name="items[${index}][notes]" value="${data.notes||''}" placeholder="備考（任意）"></td>
      <td class="text-nowrap">
        <button type="button" class="btn btn-sm btn-outline-secondary me-1 dupRow" title="複製">⎘</button>
        <button type="button" class="btn btn-sm btn-outline-secondary me-1 moveUp" title="上へ">↑</button>
        <button type="button" class="btn btn-sm btn-outline-secondary me-1 moveDown" title="下へ">↓</button>
        <button type="button" class="btn btn-sm btn-outline-danger delRow" title="削除">×</button>
      </td>
    `;
    tbody.appendChild(tr);
    tr.addEventListener('input', (e)=>{
      if(e.target.matches('.item-qty, .item-unit, .item-taxrate')) recalcTotals();
    });
    tr.querySelector('.delRow').addEventListener('click', ()=>{ tr.remove(); recalcTotals(); });
    tr.querySelector('.dupRow').addEventListener('click', ()=>{
      const inputs = tr.querySelectorAll('input, select');
      const data = {};
      inputs.forEach(i=>{ const name=i.name.match(/items\[[0-9]+\]\[(.+)\]/); if(name){ data[name[1]] = i.value; }});
      addRow(data);
    });
    tr.querySelector('.moveUp').addEventListener('click', ()=>{ const prev = tr.previousElementSibling; if(prev){ tbody.insertBefore(tr, prev); }});
    tr.querySelector('.moveDown').addEventListener('click', ()=>{ const next = tr.nextElementSibling; if(next){ tbody.insertBefore(next, tr); }});
    recalcTotals();
  }

  addBtn.addEventListener('click', ()=> addRow());
  // start with one row
  addRow();

  quickAddEls.forEach(el => el.addEventListener('click', (e)=>{
    e.preventDefault();
    addRow({
      product_name: el.dataset.name || '',
      quantity: el.dataset.qty || 1,
      unit_price: el.dataset.unit || 0,
      tax_rate: el.dataset.tax || 10
    });
  }));
});
</script>
@endsection
