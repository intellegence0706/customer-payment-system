@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">入金を編集</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> 入金一覧に戻る
        </a>
    </div>
 </div>

 <div class="row">
    <div class="col-lg-12">
        <form method="POST" action="{{ route('payments.update', $payment) }}">
            @csrf
            @method('PUT')
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
                                    <option value="{{ $customer->id }}" {{ old('customer_id', $payment->customer_id) == $customer->id ? 'selected' : '' }}>
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
                            <input type="number" class="form-control @error('payment_month') is-invalid @enderror" id="payment_month" name="payment_month" min="1" max="12" value="{{ old('payment_month', $payment->payment_month) }}" required>
                            @error('payment_month')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="payment_year" class="form-label">年 <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('payment_year') is-invalid @enderror" id="payment_year" name="payment_year" min="2020" value="{{ old('payment_year', $payment->payment_year) }}" required>
                            @error('payment_year')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="amount" class="form-label">金額 <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" class="form-control @error('amount') is-invalid @enderror" id="amount" name="amount" value="{{ old('amount', $payment->amount) }}" required>
                            @error('amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="payment_date" class="form-label">入金日 <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('payment_date') is-invalid @enderror" id="payment_date" name="payment_date" value="{{ old('payment_date', optional($payment->payment_date)->format('Y-m-d')) }}" required>
                            @error('payment_date')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="receipt_number" class="form-label">領収書番号</label>
                            <input type="text" class="form-control @error('receipt_number') is-invalid @enderror" id="receipt_number" name="receipt_number" value="{{ old('receipt_number', $payment->receipt_number) }}">
                            @error('receipt_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="status" class="form-label">状態 <span class="text-danger">*</span></label>
                            <select class="form-select @error('status') is-invalid @enderror" id="status" name="status" required>
                                <option value="pending" {{ old('status', $payment->status) == 'pending' ? 'selected' : '' }}>保留</option>
                                <option value="completed" {{ old('status', $payment->status) == 'completed' ? 'selected' : '' }}>完了</option>
                                <option value="failed" {{ old('status', $payment->status) == 'failed' ? 'selected' : '' }}>失敗</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label for="notes" class="form-label">備考</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" id="notes" name="notes" rows="2">{{ old('notes', $payment->notes) }}</textarea>
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
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addRowBtn"><i class="fas fa-plus"></i> 行を追加</button>
                </div>
                <div class="card-body">
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
                                    <th style="width: 120px;">税率(%)</th>
                                    <th style="width: 140px;">税額</th>
                                    <th style="width: 160px;">区分</th>
                                    <th style="width: 60px;"></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($payment->items as $i => $item)
                                <tr>
                                    <td><input type="number" class="form-control form-control-sm" name="items[{{ $i }}][row_no]" value="{{ $item->row_no }}"></td>
                                    <td><input type="date" class="form-control form-control-sm" name="items[{{ $i }}][item_date]" value="{{ optional($item->item_date)->format('Y-m-d') }}"></td>
                                    <td><input type="text" class="form-control form-control-sm" name="items[{{ $i }}][product_code]" value="{{ $item->product_code }}"></td>
                                    <td><input type="text" class="form-control form-control-sm" name="items[{{ $i }}][product_name]" value="{{ $item->product_name }}" placeholder="例：車イスレンタル" required></td>
                                    <td><input type="number" step="0.01" class="form-control form-control-sm item-qty" name="items[{{ $i }}][quantity]" value="{{ $item->quantity }}"></td>
                                    <td><input type="number" step="0.01" class="form-control form-control-sm item-unit" name="items[{{ $i }}][unit_price]" value="{{ $item->unit_price }}"></td>
                                    <td><input type="number" step="0.01" class="form-control form-control-sm item-amount" name="items[{{ $i }}][amount]" value="{{ $item->amount }}" readonly></td>
                                    <td><input type="number" step="0.01" class="form-control form-control-sm item-taxrate" name="items[{{ $i }}][tax_rate]" value="{{ $item->tax_rate }}"></td>
                                    <td><input type="number" step="0.01" class="form-control form-control-sm item-tax" name="items[{{ $i }}][tax_amount]" value="{{ $item->tax_amount }}" readonly></td>
                                    <td>
                                        <select class="form-select form-select-sm item-category" name="items[{{ $i }}][category]">
                                            <option value="" {{ $item->category===''? 'selected':'' }}>通常</option>
                                            <option value="other_charges" {{ $item->category==='other_charges'? 'selected':'' }}>その他徴収</option>
                                            <option value="notice" {{ $item->category==='notice'? 'selected':'' }}>お知らせ</option>
                                            <option value="previous_balance" {{ $item->category==='previous_balance'? 'selected':'' }}>前月繰越</option>
                                        </select>
                                    </td>
                                    <td><button type="button" class="btn btn-sm btn-outline-danger delRow">×</button></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="row g-3 mt-3">
                        <div class="col-md-3 ms-auto">
                            <label class="form-label">小計</label>
                            <div class="input-group">
                                <span class="input-group-text">¥</span>
                                <input type="text" readonly class="form-control" id="subtotal_amount" name="subtotal_amount" value="{{ $payment->subtotal_amount }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">税額合計</label>
                            <div class="input-group">
                                <span class="input-group-text">¥</span>
                                <input type="text" readonly class="form-control" id="tax_total" name="tax_total" value="{{ $payment->tax_total }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">その他合計</label>
                            <div class="input-group">
                                <span class="input-group-text">¥</span>
                                <input type="text" readonly class="form-control" id="other_fees_total" name="other_fees_total" value="{{ $payment->other_fees_total }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">総合計</label>
                            <div class="input-group">
                                <span class="input-group-text">¥</span>
                                <input type="text" readonly class="form-control" id="grand_total" name="grand_total" value="{{ $payment->grand_total }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary me-md-2">キャンセル</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> 変更を保存
                </button>
            </div>
        </form>
    </div>
 </div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const tbody = document.querySelector('#itemsTable tbody');
  const addBtn = document.getElementById('addRowBtn');

  function toNumber(v){
    const n = parseFloat((v||'').toString().replace(/[^0-9.\-]/g,''));
    return isNaN(n) ? 0 : n;
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
      const tax = Math.round((amount * taxRate) )/100; // percentage
      tr.querySelector('.item-tax').value = tax.toFixed(2);
      subtotal += amount;
      taxTotal += tax;
      if(category === 'other_charges'){ otherTotal += amount + tax; }
    });
    document.getElementById('subtotal_amount').value = subtotal.toFixed(2);
    document.getElementById('tax_total').value = taxTotal.toFixed(2);
    document.getElementById('other_fees_total').value = otherTotal.toFixed(2);
    document.getElementById('grand_total').value = (subtotal + taxTotal + otherTotal).toFixed(2);
    const amountInput = document.getElementById('amount');
    if (amountInput) amountInput.value = (subtotal + taxTotal + otherTotal).toFixed(2);
  }

  function addRow(data={}){
    const index = tbody.children.length;
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td><input type=\"number\" class=\"form-control form-control-sm\" name=\"items[${index}][row_no]\" value=\"${index+1}\"></td>
      <td><input type=\"date\" class=\"form-control form-control-sm\" name=\"items[${index}][item_date]\" value=\"${data.item_date||''}\"></td>
      <td><input type=\"text\" class=\"form-control form-control-sm\" name=\"items[${index}][product_code]\" value=\"${data.product_code||''}\"></td>
      <td><input type=\"text\" class=\"form-control form-control-sm\" name=\"items[${index}][product_name]\" value=\"${data.product_name||''}\" placeholder=\"例：車イスレンタル\" required></td>
      <td><input type=\"number\" step=\"0.01\" class=\"form-control form-control-sm item-qty\" name=\"items[${index}][quantity]\" value=\"${data.quantity||1}\"></td>
      <td><input type=\"number\" step=\"0.01\" class=\"form-control form-control-sm item-unit\" name=\"items[${index}][unit_price]\" value=\"${data.unit_price||0}\"></td>
      <td><input type=\"number\" step=\"0.01\" class=\"form-control form-control-sm item-amount\" name=\"items[${index}][amount]\" value=\"${data.amount||0}\" readonly></td>
      <td><input type=\"number\" step=\"0.01\" class=\"form-control form-control-sm item-taxrate\" name=\"items[${index}][tax_rate]\" value=\"${data.tax_rate||0}\"></td>
      <td><input type=\"number\" step=\"0.01\" class=\"form-control form-control-sm item-tax\" name=\"items[${index}][tax_amount]\" value=\"${data.tax_amount||0}\" readonly></td>
      <td>
        <select class=\"form-select form-select-sm item-category\" name=\"items[${index}][category]\">
          <option value=\"\">通常</option>
          <option value=\"other_charges\">その他徴収</option>
          <option value=\"notice\">お知らせ</option>
          <option value=\"previous_balance\">前月繰越</option>
        </select>
      </td>
      <td><button type=\"button\" class=\"btn btn-sm btn-outline-danger delRow\">×</button></td>
    `;
    tbody.appendChild(tr);
    tr.addEventListener('input', (e)=>{
      if(e.target.matches('.item-qty, .item-unit, .item-taxrate')) recalcTotals();
    });
    tr.querySelector('.delRow').addEventListener('click', ()=>{ tr.remove(); recalcTotals(); });
    recalcTotals();
  }

  document.querySelectorAll('#itemsTable tbody tr').forEach(tr => {
    tr.addEventListener('input', (e)=>{
      if(e.target.matches('.item-qty, .item-unit, .item-taxrate')) recalcTotals();
    });
    tr.querySelector('.delRow').addEventListener('click', ()=>{ tr.remove(); recalcTotals(); });
  });

  addBtn.addEventListener('click', ()=> addRow());
  recalcTotals();
});
</script>
@endsection


