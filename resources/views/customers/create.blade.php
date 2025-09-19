@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">顧客を追加</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> 戻る
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('customers.store') }}">
            @csrf
            
            <div class="row">
                <!-- Customer Identification Section -->
                <div class="col-md-12 mb-4">
                    <h5 class="border-bottom pb-2">顧客識別情報</h5>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="customer_code" class="form-label">顧客コード</label>
                            <input type="text" class="form-control @error('customer_code') is-invalid @enderror" 
                                   id="customer_code" name="customer_code" value="{{ old('customer_code') }}">
                            @error('customer_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="user_kana_name" class="form-label">利用者カナ氏名</label>
                            <input type="text" class="form-control @error('user_kana_name') is-invalid @enderror" 
                                   id="user_kana_name" name="user_kana_name" value="{{ old('user_kana_name') }}">
                            @error('user_kana_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="user_name" class="form-label">利用者氏名</label>
                            <input type="text" class="form-control @error('user_name') is-invalid @enderror" 
                                   id="user_name" name="user_name" value="{{ old('user_name') }}">
                            @error('user_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="customer_number" class="form-label">顧客番号 <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('customer_number') is-invalid @enderror" 
                                   id="customer_number" name="customer_number" value="{{ old('customer_number') }}" required>
                            @error('customer_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Account Information Section -->
                <div class="col-md-12 mb-4">
                    <h5 class="border-bottom pb-2">口座情報</h5>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="account_kana_name" class="form-label">口座カナ氏名</label>
                            <input type="text" class="form-control @error('account_kana_name') is-invalid @enderror" 
                                   id="account_kana_name" name="account_kana_name" value="{{ old('account_kana_name') }}">
                            @error('account_kana_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="account_holder_name" class="form-label">口座人氏名</label>
                            <input type="text" class="form-control @error('account_holder_name') is-invalid @enderror" 
                                   id="account_holder_name" name="account_holder_name" value="{{ old('account_holder_name') }}">
                            @error('account_holder_name')
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
                            <label for="deposit_type" class="form-label">預金種目</label>
                            <input type="text" class="form-control @error('deposit_type') is-invalid @enderror" 
                                   id="deposit_type" name="deposit_type" value="{{ old('deposit_type') }}">
                            @error('deposit_type')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Payment Information Section -->
                <div class="col-md-12 mb-4">
                    <h5 class="border-bottom pb-2">支払情報</h5>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="payment_classification" class="form-label">支払区分</label>
                            <select class="form-select @error('payment_classification') is-invalid @enderror" 
                                    id="payment_classification" name="payment_classification">
                                <option value="">選択してください</option>
                                <option value="21" {{ old('payment_classification') == '21' ? 'selected' : '' }}>21</option>
                                <option value="22" {{ old('payment_classification') == '22' ? 'selected' : '' }}>22</option>
                                <option value="23" {{ old('payment_classification') == '23' ? 'selected' : '' }}>23</option>
                            </select>
                            @error('payment_classification')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="payment_method" class="form-label">支払方法</label>
                            <input type="text" class="form-control @error('payment_method') is-invalid @enderror" 
                                   id="payment_method" name="payment_method" value="{{ old('payment_method') }}">
                            @error('payment_method')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="billing_amount" class="form-label">請求金額</label>
                            <input type="number" step="0.01" class="form-control @error('billing_amount') is-invalid @enderror" 
                                   id="billing_amount" name="billing_amount" value="{{ old('billing_amount') }}">
                            @error('billing_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="collection_request_amount" class="form-label">徴収請求額</label>
                            <input type="number" step="0.01" class="form-control @error('collection_request_amount') is-invalid @enderror" 
                                   id="collection_request_amount" name="collection_request_amount" value="{{ old('collection_request_amount') }}">
                            @error('collection_request_amount')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="consumption_tax" class="form-label">消費税</label>
                            <input type="number" step="0.01" class="form-control @error('consumption_tax') is-invalid @enderror" 
                                   id="consumption_tax" name="consumption_tax" value="{{ old('consumption_tax') }}">
                            @error('consumption_tax')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Banking Information Section -->
                <div class="col-md-12 mb-4">
                    <h5 class="border-bottom pb-2">銀行情報</h5>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="bank_number" class="form-label">銀行番号 (4桁)</label>
                            <div class="input-group">
                                <input type="text" class="form-control @error('bank_number') is-invalid @enderror" 
                                       id="bank_number" name="bank_number" value="{{ old('bank_number') }}" 
                                       maxlength="4" placeholder="例: 0001">
                                <button type="button" class="btn btn-outline-secondary" id="fetchBankName" disabled>
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            @error('bank_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="bank_name" class="form-label">銀行名 (API取得)</label>
                            <input type="text" class="form-control @error('bank_name') is-invalid @enderror" 
                                   id="bank_name" name="bank_name" value="{{ old('bank_name') }}" readonly>
                            @error('bank_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="branch_number" class="form-label">支店番号 (3桁)</label>
                            <div class="input-group">
                                <input type="text" class="form-control @error('branch_number') is-invalid @enderror" 
                                       id="branch_number" name="branch_number" value="{{ old('branch_number') }}" 
                                       maxlength="3" placeholder="例: 001">
                                <button type="button" class="btn btn-outline-secondary" id="fetchBranchName" disabled>
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                            @error('branch_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="branch_name" class="form-label">支店名 (API取得)</label>
                            <input type="text" class="form-control @error('branch_name') is-invalid @enderror" 
                                   id="branch_name" name="branch_name" value="{{ old('branch_name') }}" readonly>
                            @error('branch_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Billing Address Section -->
                <div class="col-md-12 mb-4">
                    <h5 class="border-bottom pb-2">請求先住所</h5>
                    <div class="row g-3">
                        <div class="col-md-2">
                            <label for="billing_postal_code" class="form-label">郵便番号</label>
                            <input type="text" class="form-control @error('billing_postal_code') is-invalid @enderror" 
                                   id="billing_postal_code" name="billing_postal_code" value="{{ old('billing_postal_code') }}">
                            @error('billing_postal_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <label for="billing_prefecture" class="form-label">県名</label>
                            <input type="text" class="form-control @error('billing_prefecture') is-invalid @enderror" 
                                   id="billing_prefecture" name="billing_prefecture" value="{{ old('billing_prefecture') }}">
                            @error('billing_prefecture')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="billing_city" class="form-label">市区町村</label>
                            <input type="text" class="form-control @error('billing_city') is-invalid @enderror" 
                                   id="billing_city" name="billing_city" value="{{ old('billing_city') }}">
                            @error('billing_city')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="billing_street" class="form-label">番地</label>
                            <input type="text" class="form-control @error('billing_street') is-invalid @enderror" 
                                   id="billing_street" name="billing_street" value="{{ old('billing_street') }}">
                            @error('billing_street')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-3">
                            <label for="billing_building" class="form-label">建物名</label>
                            <input type="text" class="form-control @error('billing_building') is-invalid @enderror" 
                                   id="billing_building" name="billing_building" value="{{ old('billing_building') }}">
                            @error('billing_building')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-2">
                            <label for="billing_difference" class="form-label">差額</label>
                            <input type="number" step="0.01" class="form-control @error('billing_difference') is-invalid @enderror" 
                                   id="billing_difference" name="billing_difference" value="{{ old('billing_difference') }}">
                            @error('billing_difference')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">キャンセル</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-1"></i> 保存
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Bank number input handler
    const bankNumberInput = document.getElementById('bank_number');
    const bankNameInput = document.getElementById('bank_name');
    const fetchBankBtn = document.getElementById('fetchBankName');

    // Branch number input handler
    const branchNumberInput = document.getElementById('branch_number');
    const branchNameInput = document.getElementById('branch_name');
    const fetchBranchBtn = document.getElementById('fetchBranchName');

    // Bank number input validation and API call
    bankNumberInput.addEventListener('input', function() {
        const value = this.value;
        
        // Only allow digits
        this.value = value.replace(/\D/g, '');
        
        // Enable/disable fetch button based on length
        if (this.value.length === 4) {
            fetchBankBtn.disabled = false;
            fetchBankBtn.classList.remove('btn-outline-secondary');
            fetchBankBtn.classList.add('btn-outline-primary');
        } else {
            fetchBankBtn.disabled = true;
            fetchBankBtn.classList.remove('btn-outline-primary');
            fetchBankBtn.classList.add('btn-outline-secondary');
            bankNameInput.value = '';
        }
    });

    // Branch number input validation and API call
    branchNumberInput.addEventListener('input', function() {
        const value = this.value;
        
        // Only allow digits
        this.value = value.replace(/\D/g, '');
        
        // Enable/disable fetch button based on length
        if (this.value.length === 3) {
            fetchBranchBtn.disabled = false;
            fetchBranchBtn.classList.remove('btn-outline-secondary');
            fetchBranchBtn.classList.add('btn-outline-primary');
        } else {
            fetchBranchBtn.disabled = true;
            fetchBranchBtn.classList.remove('btn-outline-primary');
            fetchBranchBtn.classList.add('btn-outline-secondary');
            branchNameInput.value = '';
        }
    });

    // Fetch bank name from API
    fetchBankBtn.addEventListener('click', function() {
        const bankCode = bankNumberInput.value;
        if (bankCode.length !== 4) return;

        // Show loading state
        fetchBankBtn.disabled = true;
        fetchBankBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        bankNameInput.value = '取得中...';

        fetch(`{{ route('customers.get-bank-name') }}?bank_code=${bankCode}`)
            .then(response => response.json())
            .then(data => {
                if (data.bank_name) {
                    bankNameInput.value = data.bank_name;
                    bankNameInput.classList.remove('is-invalid');
                    bankNameInput.classList.add('is-valid');
                    
                    // Show success indicator
                    fetchBankBtn.classList.remove('btn-outline-primary');
                    fetchBankBtn.classList.add('btn-success');
                    fetchBankBtn.innerHTML = '<i class="fas fa-check"></i>';
                    
                    setTimeout(() => {
                        fetchBankBtn.classList.remove('btn-success');
                        fetchBankBtn.classList.add('btn-outline-primary');
                        fetchBankBtn.innerHTML = '<i class="fas fa-search"></i>';
                        fetchBankBtn.disabled = false;
                    }, 2000);
                } else {
                    bankNameInput.value = '見つかりません';
                    bankNameInput.classList.remove('is-valid');
                    bankNameInput.classList.add('is-invalid');
                    
                    // Show error indicator
                    fetchBankBtn.classList.remove('btn-outline-primary');
                    fetchBankBtn.classList.add('btn-danger');
                    fetchBankBtn.innerHTML = '<i class="fas fa-times"></i>';
                    
                    setTimeout(() => {
                        fetchBankBtn.classList.remove('btn-danger');
                        fetchBankBtn.classList.add('btn-outline-primary');
                        fetchBankBtn.innerHTML = '<i class="fas fa-search"></i>';
                        fetchBankBtn.disabled = false;
                    }, 2000);
                }
            })
            .catch(error => {
                console.error('Error fetching bank name:', error);
                bankNameInput.value = 'エラーが発生しました';
                bankNameInput.classList.remove('is-valid');
                bankNameInput.classList.add('is-invalid');
                
                // Show error indicator
                fetchBankBtn.classList.remove('btn-outline-primary');
                fetchBankBtn.classList.add('btn-danger');
                fetchBankBtn.innerHTML = '<i class="fas fa-times"></i>';
                
                setTimeout(() => {
                    fetchBankBtn.classList.remove('btn-danger');
                    fetchBankBtn.classList.add('btn-outline-primary');
                    fetchBankBtn.innerHTML = '<i class="fas fa-search"></i>';
                    fetchBankBtn.disabled = true;
                }, 2000);
            });
    });

    // Fetch branch name from API
    fetchBranchBtn.addEventListener('click', function() {
        const bankCode = bankNumberInput.value;
        const branchCode = branchNumberInput.value;
        
        if (bankCode.length !== 4 || branchCode.length !== 3) {
            alert('銀行コード（4桁）と支店コード（3桁）の両方を入力してください。');
            return;
        }

        // Show loading state
        fetchBranchBtn.disabled = true;
        fetchBranchBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        branchNameInput.value = '取得中...';

        fetch(`{{ route('customers.get-branch-name') }}?bank_code=${bankCode}&branch_code=${branchCode}`)
            .then(response => response.json())
            .then(data => {
                if (data.branch_name) {
                    branchNameInput.value = data.branch_name;
                    branchNameInput.classList.remove('is-invalid');
                    branchNameInput.classList.add('is-valid');
                    
                    // Show success indicator
                    fetchBranchBtn.classList.remove('btn-outline-primary');
                    fetchBranchBtn.classList.add('btn-success');
                    fetchBranchBtn.innerHTML = '<i class="fas fa-check"></i>';
                    
                    setTimeout(() => {
                        fetchBranchBtn.classList.remove('btn-success');
                        fetchBranchBtn.classList.add('btn-outline-primary');
                        fetchBranchBtn.innerHTML = '<i class="fas fa-search"></i>';
                        fetchBranchBtn.disabled = false;
                    }, 2000);
                } else {
                    branchNameInput.value = '見つかりません';
                    branchNameInput.classList.remove('is-valid');
                    branchNameInput.classList.add('is-invalid');
                    
                    // Show error indicator
                    fetchBranchBtn.classList.remove('btn-outline-primary');
                    fetchBranchBtn.classList.add('btn-danger');
                    fetchBranchBtn.innerHTML = '<i class="fas fa-times"></i>';
                    
                    setTimeout(() => {
                        fetchBranchBtn.classList.remove('btn-danger');
                        fetchBranchBtn.classList.add('btn-outline-primary');
                        fetchBranchBtn.innerHTML = '<i class="fas fa-search"></i>';
                        fetchBranchBtn.disabled = false;
                    }, 2000);
                }
            })
            .catch(error => {
                console.error('Error fetching branch name:', error);
                branchNameInput.value = 'エラーが発生しました';
                branchNameInput.classList.remove('is-valid');
                branchNameInput.classList.add('is-invalid');
                
                // Show error indicator
                fetchBranchBtn.classList.remove('btn-outline-primary');
                fetchBranchBtn.classList.add('btn-danger');
                fetchBranchBtn.innerHTML = '<i class="fas fa-times"></i>';
                
                setTimeout(() => {
                    fetchBranchBtn.classList.remove('btn-danger');
                    fetchBranchBtn.classList.add('btn-outline-primary');
                    fetchBranchBtn.innerHTML = '<i class="fas fa-search"></i>';
                    fetchBranchBtn.disabled = true;
                }, 2000);
            });
    });

    // Initialize with old values if they exist
    if (bankNumberInput.value.length === 4) {
        fetchBankBtn.disabled = false;
        fetchBankBtn.classList.remove('btn-outline-secondary');
        fetchBankBtn.classList.add('btn-outline-primary');
    }

    if (branchNumberInput.value.length === 3) {
        fetchBranchBtn.disabled = false;
        fetchBranchBtn.classList.remove('btn-outline-secondary');
        fetchBranchBtn.classList.add('btn-outline-primary');
    }
});
</script>
@endsection
