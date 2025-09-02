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

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row">
    <div class="col-lg-12">
        <form method="POST" action="{{ route('customers.update', $customer) }}" id="customerForm">
            @csrf
            @method('PUT')
            
            <!-- 基本情報 -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user me-2"></i>基本情報</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="user_name" class="form-label">利用者氏名</label>
                            <input type="text" class="form-control @error('user_name') is-invalid @enderror" 
                                   id="user_name" name="user_name" 
                                   value="{{ old('user_name', $customer->user_name) }}" 
                                   placeholder="例: 田中太郎">
                            @error('user_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="user_kana_name" class="form-label">利用者カナ氏名</label>
                            <input type="text" class="form-control @error('user_kana_name') is-invalid @enderror" 
                                   id="user_kana_name" name="user_kana_name" 
                                   value="{{ old('user_kana_name', $customer->user_kana_name) }}" 
                                   placeholder="例: タナカタロウ">
                            @error('user_kana_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="customer_code" class="form-label">顧客コード</label>
                            <input type="text" class="form-control @error('customer_code') is-invalid @enderror" 
                                   id="customer_code" name="customer_code" 
                                   value="{{ old('customer_code', $customer->customer_code) }}" 
                                   placeholder="例: CUST001">
                            @error('customer_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="account_holder_name" class="form-label">口座人氏名</label>
                            <input type="text" class="form-control @error('account_holder_name') is-invalid @enderror" 
                                   id="account_holder_name" name="account_holder_name" 
                                   value="{{ old('account_holder_name', $customer->account_holder_name) }}" 
                                   placeholder="例: 田中太郎">
                            @error('account_holder_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="account_kana_name" class="form-label">口座カナ氏名</label>
                            <input type="text" class="form-control @error('account_kana_name') is-invalid @enderror" 
                                   id="account_kana_name" name="account_kana_name" 
                                   value="{{ old('account_kana_name', $customer->account_kana_name) }}" 
                                   placeholder="例: タナカタロウ">
                            @error('account_kana_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="customer_number" class="form-label">顧客番号 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('customer_number') is-invalid @enderror" 
                                   id="customer_number" name="customer_number" 
                                   value="{{ old('customer_number', $customer->customer_number) }}" 
                                   placeholder="例: 12345678901234567890" required>
                            @error('customer_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label for="address" class="form-label">住所</label>
                            <textarea class="form-control @error('address') is-invalid @enderror" 
                                      id="address" name="address" rows="3" 
                                      placeholder="例: 東京都新宿区西新宿1-2-3">{{ old('address', $customer->address) }}</textarea>
                            @error('address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-12">
                            <label for="note" class="form-label">備考</label>
                            <textarea class="form-control @error('note') is-invalid @enderror" 
                                      id="note" name="note" rows="2" 
                                      placeholder="備考があれば記入してください">{{ old('note', $customer->note) }}</textarea>
                            @error('note')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- 支払情報 -->
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-credit-card me-2"></i>支払情報</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="payment_classification" class="form-label">支払区分</label>
                            <select class="form-select @error('payment_classification') is-invalid @enderror" 
                                    id="payment_classification" name="payment_classification">
                                <option value="">選択してください</option>
                                <option value="21" {{ old('payment_classification', $customer->payment_classification) == '21' ? 'selected' : '' }}>21</option>
                                <option value="22" {{ old('payment_classification', $customer->payment_classification) == '22' ? 'selected' : '' }}>22</option>
                                <option value="23" {{ old('payment_classification', $customer->payment_classification) == '23' ? 'selected' : '' }}>23</option>
                            </select>
                            @error('payment_classification')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="payment_method" class="form-label">支払方法</label>
                            <select class="form-select @error('payment_method') is-invalid @enderror" 
                                    id="payment_method" name="payment_method">
                                <option value="">選択してください</option>
                                <option value="bank_transfer" {{ old('payment_method', $customer->payment_method) == 'bank_transfer' ? 'selected' : '' }}>銀行振込</option>
                                <option value="direct_debit" {{ old('payment_method', $customer->payment_method) == 'direct_debit' ? 'selected' : '' }}>口座振替</option>
                                <option value="cash" {{ old('payment_method', $customer->payment_method) == 'cash' ? 'selected' : '' }}>現金</option>
                                <option value="credit_card" {{ old('payment_method', $customer->payment_method) == 'credit_card' ? 'selected' : '' }}>クレジットカード</option>
                            </select>
                            @error('payment_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="billing_amount" class="form-label">請求金額</label>
                            <div class="input-group">
                                <span class="input-group-text">¥</span>
                                <input type="number" step="0.01" min="0" 
                                       class="form-control @error('billing_amount') is-invalid @enderror" 
                                       id="billing_amount" name="billing_amount" 
                                       value="{{ old('billing_amount', $customer->billing_amount) }}">
                            </div>
                            @error('billing_amount')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="collection_request_amount" class="form-label">徴収請求額</label>
                            <div class="input-group">
                                <span class="input-group-text">¥</span>
                                <input type="number" step="0.01" min="0" 
                                       class="form-control @error('collection_request_amount') is-invalid @enderror" 
                                       id="collection_request_amount" name="collection_request_amount" 
                                       value="{{ old('collection_request_amount', $customer->collection_request_amount) }}">
                            </div>
                            @error('collection_request_amount')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="consumption_tax" class="form-label">消費税</label>
                            <div class="input-group">
                                <span class="input-group-text">¥</span>
                                <input type="number" step="0.01" min="0" 
                                       class="form-control @error('consumption_tax') is-invalid @enderror" 
                                       id="consumption_tax" name="consumption_tax" 
                                       value="{{ old('consumption_tax', $customer->consumption_tax) }}">
                            </div>
                            @error('consumption_tax')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- 請求先情報 -->
            <div class="card mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="mb-0"><i class="fas fa-map-marker-alt me-2"></i>請求先情報</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="billing_postal_code" class="form-label">請求先郵便番号</label>
                            <input type="text" class="form-control @error('billing_postal_code') is-invalid @enderror" 
                                   id="billing_postal_code" name="billing_postal_code" 
                                   value="{{ old('billing_postal_code', $customer->billing_postal_code) }}" 
                                   placeholder="例: 123-4567">
                            @error('billing_postal_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="billing_prefecture" class="form-label">請求先県名</label>
                            <input type="text" class="form-control @error('billing_prefecture') is-invalid @enderror" 
                                   id="billing_prefecture" name="billing_prefecture" 
                                   value="{{ old('billing_prefecture', $customer->billing_prefecture) }}" 
                                   placeholder="例: 東京都">
                            @error('billing_prefecture')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="billing_city" class="form-label">請求先市区町村</label>
                            <input type="text" class="form-control @error('billing_city') is-invalid @enderror" 
                                   id="billing_city" name="billing_city" 
                                   value="{{ old('billing_city', $customer->billing_city) }}" 
                                   placeholder="例: 新宿区">
                            @error('billing_city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-8">
                            <label for="billing_street" class="form-label">請求先番地</label>
                            <input type="text" class="form-control @error('billing_street') is-invalid @enderror" 
                                   id="billing_street" name="billing_street" 
                                   value="{{ old('billing_street', $customer->billing_street) }}" 
                                   placeholder="例: 西新宿1-2-3 ABCビル4F">
                            @error('billing_street')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="billing_difference" class="form-label">請求先差額</label>
                            <div class="input-group">
                                <span class="input-group-text">¥</span>
                                <input type="number" step="0.01" 
                                       class="form-control @error('billing_difference') is-invalid @enderror" 
                                       id="billing_difference" name="billing_difference" 
                                       value="{{ old('billing_difference', $customer->billing_difference) }}">
                            </div>
                            @error('billing_difference')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- 銀行情報 -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-university me-2"></i>銀行情報</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                                                <div class="col-md-3">
                            <label for="bank_number" class="form-label">銀行番号</label>
                            <input type="text" class="form-control @error('bank_number') is-invalid @enderror" 
                                   id="bank_number" name="bank_number" 
                                   value="{{ old('bank_number', $customer->bank_number) }}" 
                                   maxlength="4" placeholder="例: 0001">
                            @error('bank_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="bank_name" class="form-label">銀行名</label>
                            <input type="text" class="form-control @error('bank_name') is-invalid @enderror" 
                                   id="bank_name" name="bank_name" 
                                   value="{{ old('bank_name', $customer->bank_name) }}" 
                                   placeholder="例: みずほ銀行">
                            @error('bank_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="branch_number" class="form-label">支店番号</label>
                            <input type="text" class="form-control @error('branch_number') is-invalid @enderror" 
                                   id="branch_number" name="branch_number" 
                                   value="{{ old('branch_number', $customer->branch_number) }}" 
                                   maxlength="3" placeholder="例: 001">
                            @error('branch_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="branch_name" class="form-label">支店名</label>
                            <input type="text" class="form-control @error('branch_name') is-invalid @enderror" 
                                   id="branch_name" name="branch_name" 
                                   value="{{ old('branch_name', $customer->branch_name) }}" 
                                   placeholder="例: 東京営業部">
                            @error('branch_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="deposit_type" class="form-label">預金種目</label>
                            <select class="form-select @error('deposit_type') is-invalid @enderror" 
                                    id="deposit_type" name="deposit_type">
                                <option value="">選択してください</option>
                                <option value="普通" {{ old('deposit_type', $customer->deposit_type) == '普通' ? 'selected' : '' }}>普通預金</option>
                                <option value="当座" {{ old('deposit_type', $customer->deposit_type) == '当座' ? 'selected' : '' }}>当座預金</option>
                                <option value="定期" {{ old('deposit_type', $customer->deposit_type) == '定期' ? 'selected' : '' }}>定期預金</option>
                            </select>
                            @error('deposit_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-4">
                            <label for="account_number" class="form-label">口座番号</label>
                            <input type="text" class="form-control @error('account_number') is-invalid @enderror" 
                                   id="account_number" name="account_number" 
                                   value="{{ old('account_number', $customer->account_number) }}" 
                                   placeholder="例: 1234567">
                            @error('account_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
	                </div>
	            </div>
            </div>



            <!-- ボタン -->
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary me-md-2">
                    <i class="fas fa-times me-1"></i> キャンセル
                </a>
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
document.addEventListener('DOMContentLoaded', function() {
    // Auto-populate bank name when bank number is entered
    const bankNumberInput = document.getElementById('bank_number');
    const bankNameInput = document.getElementById('bank_name');
    
    if (bankNumberInput && bankNameInput) {
        bankNumberInput.addEventListener('input', function() {
            const bankNumber = this.value.trim();
            
            // Only allow numeric input
            this.value = bankNumber.replace(/\D/g, '');
            
            if (this.value.length === 4) {
                // Show loading state
                bankNameInput.value = '取得中...';
                bankNameInput.disabled = true;
                
                fetch(`{{ route("customers.get-bank-name") }}?bank_code=${this.value}`)
                    .then(response => response.json())
                    .then(data => {
                        bankNameInput.value = data.bank_name || '';
                        bankNameInput.disabled = false;
                        
                        if (!data.bank_name) {
                            showToast('warning', '銀行名が見つかりませんでした。手動で入力してください。');
                        }
                    })
                    .catch(error => {
                        console.error('Bank API error:', error);
                        bankNameInput.value = '';
                        bankNameInput.disabled = false;
                        showToast('error', '銀行名の取得に失敗しました。手動で入力してください。');
                    });
            } else {
                bankNameInput.value = '';
                bankNameInput.disabled = false;
            }
        });
    }

    // Auto-populate branch name when branch number is entered
    const branchNumberInput = document.getElementById('branch_number');
    const branchNameInput = document.getElementById('branch_name');
    
    if (branchNumberInput && branchNameInput) {
        branchNumberInput.addEventListener('input', function() {
            const branchNumber = this.value.trim();
            const bankNumber = bankNumberInput ? bankNumberInput.value.trim() : '';
            
            // Only allow numeric input
            this.value = branchNumber.replace(/\D/g, '');
            
            // Clear branch name if bank number is not complete
            if (bankNumber.length !== 4) {
                branchNameInput.value = '';
                branchNameInput.disabled = false;
                if (this.value.length > 0) {
                    showToast('warning', '先に銀行コードを正しく入力してください。');
                }
                return;
            }
            
            if (this.value.length === 3 && bankNumber.length === 4) {
                // Show loading state
                branchNameInput.value = '取得中...';
                branchNameInput.disabled = true;
                
                fetch(`{{ route("customers.get-branch-name") }}?bank_code=${bankNumber}&branch_code=${this.value}`)
                    .then(response => response.json())
                    .then(data => {
                        branchNameInput.value = data.branch_name || '';
                        branchNameInput.disabled = false;
                        
                        if (!data.branch_name) {
                            showToast('warning', '支店名が見つかりませんでした。手動で入力してください。');
                        }
                    })
                    .catch(error => {
                        console.error('Branch API error:', error);
                        branchNameInput.value = '';
                        branchNameInput.disabled = false;
                        showToast('error', '支店名の取得に失敗しました。手動で入力してください。');
                    });
            } else {
                branchNameInput.value = '';
                branchNameInput.disabled = false;
            }
        });
    }

    // Form validation
    const form = document.getElementById('customerForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            const customerNumber = document.getElementById('customer_number').value.trim();
            
            if (!customerNumber) {
                e.preventDefault();
                showToast('error', '顧客番号は必須です。');
                document.getElementById('customer_number').focus();
                return false;
            }
        });
    }

    // Toast notification function
    function showToast(type, message) {
        // Create toast element
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed`;
        toast.style.top = '20px';
        toast.style.right = '20px';
        toast.style.zIndex = '9999';
        toast.style.minWidth = '300px';
        
        toast.innerHTML = `
            <i class="fas fa-${type === 'error' ? 'exclamation-circle' : type === 'warning' ? 'exclamation-triangle' : 'info-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(toast);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 5000);
    }
});
</script>
@endsection