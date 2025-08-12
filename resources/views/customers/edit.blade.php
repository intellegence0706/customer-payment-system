@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">顧客の編集</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> 顧客一覧に戻る
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <form method="POST" action="{{ route('customers.update', $customer) }}">
            @csrf
            @method('PUT')
            
            <!-- Personal Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">個人情報</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">氏名 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name', $customer->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="ghana" class="form-label">ガーナ</label>
                            <input type="text" class="form-control @error('ghana') is-invalid @enderror" 
                                   id="ghana" name="ghana" value="{{ old('ghana', $customer->ghana) }}">
                            @error('ghana')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="gender" class="form-label">性別 <span class="text-danger">*</span></label>
                            <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender" required>
                                <option value="">選択してください</option>
                                <option value="male" {{ old('gender', $customer->gender) == 'male' ? 'selected' : '' }}>男性</option>
                                <option value="female" {{ old('gender', $customer->gender) == 'female' ? 'selected' : '' }}>女性</option>
                                <option value="other" {{ old('gender', $customer->gender) == 'other' ? 'selected' : '' }}>その他</option>
                            </select>
                            @error('gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="postal_code" class="form-label">郵便番号</label>
                            <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                   id="postal_code" name="postal_code" value="{{ old('postal_code', $customer->postal_code) }}">
                            @error('postal_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="phone_number" class="form-label">電話番号</label>
                            <input type="text" class="form-control @error('phone_number') is-invalid @enderror" 
                                   id="phone_number" name="phone_number" value="{{ old('phone_number', $customer->phone_number) }}">
                            @error('phone_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label for="address" class="form-label">住所</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" name="address" rows="3">{{ old('address', $customer->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label for="note" class="form-label">備考</label>
                            <textarea class="form-control @error('note') is-invalid @enderror" 
                                      id="note" name="note" rows="2">{{ old('note', $customer->note) }}</textarea>
                            @error('note')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bank Information -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">銀行情報</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="customer_number" class="form-label">顧客番号 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('customer_number') is-invalid @enderror" 
                                   id="customer_number" name="customer_number" value="{{ old('customer_number', $customer->customer_number) }}" required>
                            @error('customer_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="bank_code" class="form-label">銀行コード（4桁）</label>
                            <input type="text" class="form-control @error('bank_code') is-invalid @enderror" 
                                   id="bank_code" name="bank_code" value="{{ old('bank_code', $customer->bank_code) }}" maxlength="4">
                            @error('bank_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="branch_code" class="form-label">支店コード（3桁）</label>
                            <input type="text" class="form-control @error('branch_code') is-invalid @enderror" 
                                   id="branch_code" name="branch_code" value="{{ old('branch_code', $customer->branch_code) }}" maxlength="3">
                            @error('branch_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="bank_name" class="form-label">銀行名</label>
                            <input type="text" class="form-control" id="bank_name" name="bank_name" 
                                   value="{{ old('bank_name', $customer->bank_name) }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="branch_name" class="form-label">支店名</label>
                            <input type="text" class="form-control" id="branch_name" name="branch_name" 
                                   value="{{ old('branch_name', $customer->branch_name) }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="account_name" class="form-label">口座名義</label>
                            <input type="text" class="form-control @error('account_name') is-invalid @enderror" 
                                   id="account_name" name="account_name" value="{{ old('account_name', $customer->account_name) }}">
                            @error('account_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="account_ghana" class="form-label">口座ガーナ</label>
                            <input type="text" class="form-control @error('account_ghana') is-invalid @enderror" 
                                   id="account_ghana" name="account_ghana" value="{{ old('account_ghana', $customer->account_ghana) }}">
                            @error('account_ghana')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="account_number" class="form-label">口座番号</label>
                            <input type="text" class="form-control @error('account_number') is-invalid @enderror" 
                                   id="account_number" name="account_number" value="{{ old('account_number', $customer->account_number) }}">
                            @error('account_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="account_holder" class="form-label">口座名義人</label>
                            <input type="text" class="form-control @error('account_holder') is-invalid @enderror" 
                                   id="account_holder" name="account_holder" value="{{ old('account_holder', $customer->account_holder) }}">
                            @error('account_holder')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label for="bank_note" class="form-label">銀行メモ</label>
                            <textarea class="form-control @error('bank_note') is-invalid @enderror" 
                                      id="bank_note" name="bank_note" rows="2">{{ old('bank_note', $customer->bank_note) }}</textarea>
                            @error('bank_note')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary me-md-2">キャンセル</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> 顧客を更新
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Auto-populate bank name when bank code is entered
    $('#bank_code').on('input', function() {
        const bankCode = $(this).val();
        if (bankCode.length === 4) {
            $.get('{{ route("customers.get-bank-name") }}', { bank_code: bankCode })
                .done(function(data) {
                    $('#bank_name').val(data.bank_name || '');
                })
                .fail(function(xhr) {
                    $('#bank_name').val('');
                    var msg = '銀行名の取得に失敗しました。しばらくしてから再試行してください。';
                    if (xhr && xhr.responseJSON && xhr.responseJSON.error) {
                        msg = xhr.responseJSON.error;
                    }
                    if (window.showToast) window.showToast('error', msg);
                });
        } else {
            $('#bank_name').val('');
        }
    });

    // Auto-populate branch name when branch code is entered
    $('#branch_code').on('input', function() {
        const branchCode = $(this).val();
        if (branchCode.length === 3) {
            $.get('{{ route("customers.get-branch-name") }}', { branch_code: branchCode })
                .done(function(data) {
                    $('#branch_name').val(data.branch_name || '');
                })
                .fail(function(xhr) {
                    $('#branch_name').val('');
                    var msg = '支店名の取得に失敗しました。しばらくしてから再試行してください。';
                    if (xhr && xhr.responseJSON && xhr.responseJSON.error) {
                        msg = xhr.responseJSON.error;
                    }
                    if (window.showToast) window.showToast('error', msg);
                });
        } else {
            $('#branch_name').val('');
        }
    });
});
</script>
@endsection
