@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">新規顧客の追加</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> 顧客一覧に戻る
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
        <form method="POST" action="{{ route('customers.store') }}">
            @csrf
            
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">個人情報</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">氏名 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                   id="name" name="name" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="name_kana" class="form-label">氏名（カナ）</label>
                            <input type="text" class="form-control @error('name_kana') is-invalid @enderror" 
                                   id="name_kana" name="name_kana" value="{{ old('name_kana') }}">
                            @error('name_kana')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="gender" class="form-label">性別 <span class="text-danger">*</span></label>
                            <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender" required>
                                <option value="">選択してください</option>
                                <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>男性</option>
                                <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>女性</option>
                                <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>その他</option>
                            </select>
                            @error('gender')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="date_of_birth" class="form-label">生年月日</label>
                            <input type="date" class="form-control @error('date_of_birth') is-invalid @enderror" 
                                   id="date_of_birth" name="date_of_birth" value="{{ old('date_of_birth') }}">
                            @error('date_of_birth')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="postal_code" class="form-label">郵便番号</label>
                            <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                   id="postal_code" name="postal_code" value="{{ old('postal_code') }}">
                            @error('postal_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="prefecture" class="form-label">都道府県</label>
                            <input type="text" class="form-control @error('prefecture') is-invalid @enderror" 
                                   id="prefecture" name="prefecture" value="{{ old('prefecture') }}">
                            @error('prefecture')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="city" class="form-label">市区町村</label>
                            <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                   id="city" name="city" value="{{ old('city') }}">
                            @error('city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="telephone_number" class="form-label">固定電話</label>
                            <input type="text" class="form-control @error('telephone_number') is-invalid @enderror" 
                                   id="telephone_number" name="telephone_number" value="{{ old('telephone_number') }}">
                            @error('telephone_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="mobile_number" class="form-label">携帯電話</label>
                            <input type="text" class="form-control @error('mobile_number') is-invalid @enderror" 
                                   id="mobile_number" name="mobile_number" value="{{ old('mobile_number') }}">
                            @error('mobile_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label for="address" class="form-label">住所</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" name="address" rows="3">{{ old('address') }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="address_line" class="form-label">番地</label>
                            <input type="text" class="form-control @error('address_line') is-invalid @enderror" 
                                   id="address_line" name="address_line" value="{{ old('address_line') }}">
                            @error('address_line')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="building" class="form-label">建物名</label>
                            <input type="text" class="form-control @error('building') is-invalid @enderror" 
                                   id="building" name="building" value="{{ old('building') }}">
                            @error('building')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label for="note" class="form-label">備考</label>
                            <textarea class="form-control @error('note') is-invalid @enderror" 
                                      id="note" name="note" rows="2">{{ old('note') }}</textarea>
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
                                   id="customer_number" name="customer_number" value="{{ old('customer_number') }}" required>
                            @error('customer_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="bank_code" class="form-label">銀行コード（4桁）</label>
                            <input type="text" class="form-control @error('bank_code') is-invalid @enderror" 
                                   id="bank_code" name="bank_code" value="{{ old('bank_code') }}" maxlength="4">
                            @error('bank_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="branch_code" class="form-label">支店コード（3桁）</label>
                            <input type="text" class="form-control @error('branch_code') is-invalid @enderror" 
                                   id="branch_code" name="branch_code" value="{{ old('branch_code') }}" maxlength="3">
                            @error('branch_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="bank_name" class="form-label">銀行名</label>
                            <input type="text" class="form-control" id="bank_name" name="bank_name" 
                                   value="{{ old('bank_name') }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="branch_name" class="form-label">支店名</label>
                            <input type="text" class="form-control" id="branch_name" name="branch_name" 
                                   value="{{ old('branch_name') }}" readonly>
                        </div>
                        <div class="col-md-6">
                            <label for="account_name" class="form-label">口座名義</label>
                            <input type="text" class="form-control @error('account_name') is-invalid @enderror" 
                                   id="account_name" name="account_name" value="{{ old('account_name') }}">
                            @error('account_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="account_kana" class="form-label">口座名義（カナ）</label>
                            <input type="text" class="form-control @error('account_kana') is-invalid @enderror" 
                                   id="account_kana" name="account_kana" value="{{ old('account_kana') }}">
                            @error('account_kana')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="account_ghana" class="form-label">口座ガーナ</label>
                            <input type="text" class="form-control @error('account_ghana') is-invalid @enderror" 
                                   id="account_ghana" name="account_ghana" value="{{ old('account_ghana') }}">
                            @error('account_ghana')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="account_number" class="form-label">口座番号</label>
                            <input type="text" class="form-control @error('account_number') is-invalid @enderror" 
                                   id="account_number" name="account_number" value="{{ old('account_number') }}">
                            @error('account_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="account_holder" class="form-label">口座名義人</label>
                            <input type="text" class="form-control @error('account_holder') is-invalid @enderror" 
                                   id="account_holder" name="account_holder" value="{{ old('account_holder') }}">
                            @error('account_holder')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="deposit_type" class="form-label">預金種別</label>
                            <input type="text" class="form-control @error('deposit_type') is-invalid @enderror" 
                                   id="deposit_type" name="deposit_type" value="{{ old('deposit_type') }}">
                            @error('deposit_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label for="bank_note" class="form-label">銀行メモ</label>
                            <textarea class="form-control @error('bank_note') is-invalid @enderror" 
                                      id="bank_note" name="bank_note" rows="2">{{ old('bank_note') }}</textarea>
                            @error('bank_note')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

	            <!-- 請求・法務情報 -->
	            <div class="card mb-4">
	                <div class="card-header">
	                    <h5 class="mb-0">請求・法務情報</h5>
	                </div>
	                <div class="card-body">
	                    <div class="row g-3">
	                        <div class="col-md-4">
	                            <label for="payment_method" class="form-label">支払方法</label>
	                            <select id="payment_method" name="payment_method" class="form-select @error('payment_method') is-invalid @enderror">
	                                <option value="">選択してください</option>
	                                <option value="bank_transfer" {{ old('payment_method') == 'bank_transfer' ? 'selected' : '' }}>銀行振込</option>
	                                <option value="cash_on_delivery" {{ old('payment_method') == 'cash_on_delivery' ? 'selected' : '' }}>代金引換</option>
	                                <option value="credit_card" {{ old('payment_method') == 'credit_card' ? 'selected' : '' }}>クレジットカード</option>
	                                <option value="cash" {{ old('payment_method') == 'cash' ? 'selected' : '' }}>現金</option>
	                            </select>
	                            @error('payment_method')
	                                <div class="invalid-feedback">{{ $message }}</div>
	                            @enderror
	                        </div>
	                        <div class="col-md-4">
	                            <label for="rental_fee" class="form-label">レンタル料金</label>
	                            <div class="input-group">
	                                <span class="input-group-text">¥</span>
	                                <input type="number" step="0.01" min="0" class="form-control @error('rental_fee') is-invalid @enderror" id="rental_fee" name="rental_fee" value="{{ old('rental_fee') }}">
	                            </div>
	                            @error('rental_fee')
	                                <div class="invalid-feedback d-block">{{ $message }}</div>
	                            @enderror
	                        </div>
	                        <div class="col-md-4">
	                            <label for="assembly_delivery_costs" class="form-label">組立/配送料</label>
	                            <div class="input-group">
	                                <span class="input-group-text">¥</span>
	                                <input type="number" step="0.01" min="0" class="form-control @error('assembly_delivery_costs') is-invalid @enderror" id="assembly_delivery_costs" name="assembly_delivery_costs" value="{{ old('assembly_delivery_costs') }}">
	                            </div>
	                            @error('assembly_delivery_costs')
	                                <div class="invalid-feedback d-block">{{ $message }}</div>
	                            @enderror
	                        </div>

	                        <div class="col-md-4">
	                            <label for="district_court" class="form-label">管轄裁判所</label>
	                            <input type="text" class="form-control @error('district_court') is-invalid @enderror" id="district_court" name="district_court" value="{{ old('district_court') }}">
	                            @error('district_court')
	                                <div class="invalid-feedback">{{ $message }}</div>
	                            @enderror
	                        </div>
	                        <div class="col-md-4">
	                            <label class="form-label d-block">請求有無</label>
	                            <div class="form-check form-switch">
	                                <input class="form-check-input" type="checkbox" id="billable" name="billable" value="1" {{ old('billable', true) ? 'checked' : '' }}>
	                                <label class="form-check-label" for="billable">請求対象</label>
	                            </div>
	                        </div>
	                        <div class="col-md-4">
	                            <label for="subject" class="form-label">件名</label>
	                            <input type="text" class="form-control @error('subject') is-invalid @enderror" id="subject" name="subject" value="{{ old('subject') }}">
	                            @error('subject')
	                                <div class="invalid-feedback">{{ $message }}</div>
	                            @enderror
	                        </div>

	                        <div class="col-md-6">
	                            <label for="salesperson" class="form-label">営業担当</label>
	                            <input type="text" class="form-control @error('salesperson') is-invalid @enderror" id="salesperson" name="salesperson" value="{{ old('salesperson') }}">
	                            @error('salesperson')
	                                <div class="invalid-feedback">{{ $message }}</div>
	                            @enderror
	                        </div>
	                        <div class="col-md-6">
	                            <label for="address_operator" class="form-label">住所担当者</label>
	                            <input type="text" class="form-control @error('address_operator') is-invalid @enderror" id="address_operator" name="address_operator" value="{{ old('address_operator') }}">
	                            @error('address_operator')
	                                <div class="invalid-feedback">{{ $message }}</div>
	                            @enderror
	                        </div>
	                    </div>
	                </div>
	            </div>

	            <!-- スケジュール / ケア -->
	            <div class="card mb-4">
	                <div class="card-header">
	                    <h5 class="mb-0">スケジュール / ケア</h5>
	                </div>
	                <div class="card-body">
	                    <div class="row g-3">
	                        <div class="col-md-4">
	                            <label for="last_visit_date" class="form-label">最終訪問日</label>
	                            <input type="date" class="form-control @error('last_visit_date') is-invalid @enderror" id="last_visit_date" name="last_visit_date" value="{{ old('last_visit_date') }}">
	                            @error('last_visit_date')
	                                <div class="invalid-feedback">{{ $message }}</div>
	                            @enderror
	                        </div>
	                        <div class="col-md-4">
	                            <label for="next_visit_date" class="form-label">次回訪問日</label>
	                            <input type="date" class="form-control @error('next_visit_date') is-invalid @enderror" id="next_visit_date" name="next_visit_date" value="{{ old('next_visit_date') }}">
	                            @error('next_visit_date')
	                                <div class="invalid-feedback">{{ $message }}</div>
	                            @enderror
	                        </div>
	                        <div class="col-md-4">
	                            <label for="reception_date" class="form-label">受付日</label>
	                            <input type="date" class="form-control @error('reception_date') is-invalid @enderror" id="reception_date" name="reception_date" value="{{ old('reception_date') }}">
	                            @error('reception_date')
	                                <div class="invalid-feedback">{{ $message }}</div>
	                            @enderror
	                        </div>
	                        <div class="col-md-6">
	                            <label for="residence" class="form-label">居住形態</label>
	                            <input type="text" class="form-control @error('residence') is-invalid @enderror" id="residence" name="residence" value="{{ old('residence') }}">
	                            @error('residence')
	                                <div class="invalid-feedback">{{ $message }}</div>
	                            @enderror
	                        </div>
	                        <div class="col-md-6">
	                            <label for="care_manager" class="form-label">ケアマネージャー</label>
	                            <input type="text" class="form-control @error('care_manager') is-invalid @enderror" id="care_manager" name="care_manager" value="{{ old('care_manager') }}">
	                            @error('care_manager')
	                                <div class="invalid-feedback">{{ $message }}</div>
	                            @enderror
	                        </div>
	                    </div>
	                </div>
	            </div>
            </div>

            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary me-md-2">キャンセル</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> 顧客を保存
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
                });
        } else {
            $('#branch_name').val('');
        }
    });

	    // 数字のみ許可（銀行/支店コード）
	    $('#bank_code, #branch_code').on('input', function() {
	        this.value = this.value.replace(/\D/g, '');
	    });

	    // 請求の切替で件名の有効/無効を制御
	    function toggleSubject() {
	        const isBillable = $('#billable').is(':checked');
	        $('#subject').prop('disabled', !isBillable);
	    }
	    $('#billable').on('change', toggleSubject);
	    toggleSubject();
});
</script>
@endsection
