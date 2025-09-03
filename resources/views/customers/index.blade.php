@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">顧客管理</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('customers.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> 顧客を追加
            </a>
            <a href="{{ route('customers.import') }}" class="btn btn-success">
                <i class="fas fa-file-excel me-1"></i> XLSX一括登録
            </a>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-download me-1"></i> エクスポート
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#" onclick="exportCustomers('csv')">
                        <i class="fas fa-file-csv me-1"></i> CSV形式
                    </a></li>
                    <li><a class="dropdown-item" href="#" onclick="exportCustomers('xlsx')">
                        <i class="fas fa-file-excel me-1"></i> Excel形式 (XLSX)
                    </a></li>
                </ul>
            </div>
        </div>
    </div>  
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('customers.index') }}" class="row g-3">
            <div class="col-md-3">
                <label for="search" class="form-label">検索</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ request('search') }}" placeholder="氏名・顧客番号・口座番号など">
            </div>
            <div class="col-md-2">
                <label for="customer_code" class="form-label">顧客コード</label>
                <input type="text" class="form-control" id="customer_code" name="customer_code" 
                       value="{{ request('customer_code') }}" placeholder="顧客コード">
            </div>
            <div class="col-md-2">
                <label for="payment_classification" class="form-label">支払区分</label>
                <select class="form-select" id="payment_classification" name="payment_classification">
                    <option value="">すべて</option>
                    <option value="21" {{ request('payment_classification') == '21' ? 'selected' : '' }}>21</option>
                    <option value="22" {{ request('payment_classification') == '22' ? 'selected' : '' }}>22</option>
                    <option value="23" {{ request('payment_classification') == '23' ? 'selected' : '' }}>23</option>
                </select>
            </div>
            <div class="col-md-2">
                <label for="bank_name" class="form-label">銀行名</label>
                <input type="text" class="form-control" id="bank_name" name="bank_name" 
                       value="{{ request('bank_name') }}" placeholder="銀行名">
            </div>
            <div class="col-md-2">
                <label for="branch_name" class="form-label">支店名</label>
                <input type="text" class="form-control" id="branch_name" name="branch_name" 
                       value="{{ request('branch_name') }}" placeholder="支店名">
            </div>
            <div class="col-md-1">
                <label class="form-label">&nbsp;</label>
                <div class="d-grid">
                    <button type="submit" class="btn btn-outline-primary">
                        <i class="fas fa-search me-1"></i> 検索
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Customers Cards/Table -->
<div class="row">
    @forelse($customers as $customer)
        <div class="col-12 mb-4">
            <div class="card shadow-sm border-0 payment-status-{{ $customer->payment_classification ?? 'default' }}">
                <div class="card-body">
                    <div class="row align-items-center">
                        <!-- Main Customer Info -->
                        <div class="col-md-3">
                            <div class="d-flex align-items-center mb-2">
                                <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" 
                                     style="width: 50px; height: 50px; font-size: 18px; font-weight: bold;">
                                    {{ substr($customer->user_name ?? 'N', 0, 1) }}
                                </div>
                                <div>
                                    <h5 class="mb-1 text-dark">{{ $customer->user_name ?? 'N/A' }}</h5>
                                    <p class="text-muted small mb-0">{{ $customer->user_kana_name ?? 'N/A' }}</p>
                                    <small class="text-primary fw-bold">ID: {{ $customer->customer_code }}</small>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Information -->
                        <div class="col-md-3">
                            <div class="mb-2">
                                <small class="text-muted d-block">支払情報</small>
                                <div class="d-flex align-items-center mb-1">
                                    <i class="fas fa-credit-card text-success me-2"></i>
                                    <span class="fw-bold text-success">¥{{ number_format($customer->billing_amount ?? 0) }}</span>
                                </div>
                                <small class="text-muted">
                                    区分: {{ $customer->payment_classification ?? 'N/A' }} | 
                                    方法: {{ $customer->payment_method ?? 'N/A' }}
                                </small>
                            </div>
                        </div>

                        <!-- Bank Information -->
                        <div class="col-md-4">
                            <div class="mb-2">
                                <small class="text-muted d-block">銀行情報</small>
                                <div class="d-flex align-items-center mb-1">
                                    <i class="fas fa-university text-info me-2"></i>
                                    <div>
                                        <span class="fw-semibold">{{ $customer->bank_name ?? 'N/A' }}</span>
                                        <small class="text-muted">({{ $customer->bank_number ?? 'N/A' }})</small>
                                    </div>
                                </div>
                                <div class="text-muted small">
                                    支店: {{ $customer->branch_name ?? 'N/A' }} ({{ $customer->branch_number ?? 'N/A' }})
                                    <br>
                                    口座: {{ $customer->account_number ?? 'N/A' }}
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="col-md-2 text-end">
                            <div class="btn-group" role="group">
                                <a href="{{ route('customers.show', $customer) }}" 
                                   class="btn btn-outline-info btn-sm" title="詳細表示">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('customers.edit', $customer) }}" 
                                   class="btn btn-outline-warning btn-sm" title="編集">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" action="{{ route('customers.destroy', $customer) }}" 
                                      style="display: inline;" onsubmit="return confirm('削除してよろしいですか？')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-outline-danger btn-sm" title="削除">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                            
                            <!-- Expandable Details Toggle -->
                            <button class="btn btn-link btn-sm text-muted mt-2 p-0" 
                                    type="button" data-bs-toggle="collapse" 
                                    data-bs-target="#details-{{ $customer->id }}" 
                                    aria-expanded="false">
                                <small>詳細 <i class="fas fa-chevron-down"></i></small>
                            </button>
                        </div>
                    </div>

                    <!-- Expandable Details Section -->
                    <div class="collapse mt-3" id="details-{{ $customer->id }}">
                        <hr class="my-3">
                        <div class="row">
                            <!-- Account Details -->
                            <div class="col-md-4">
                                <h6 class="text-primary mb-2">
                                    <i class="fas fa-user me-1"></i> 口座情報
                                </h6>
                                <div class="small">
                                    <div class="mb-1">
                                        <strong>口座名義:</strong> {{ $customer->account_holder_name ?? 'N/A' }}
                                    </div>
                                    <div class="mb-1">
                                        <strong>口座カナ名:</strong> {{ $customer->account_kana_name ?? 'N/A' }}
                                    </div>
                                    <div class="mb-1">
                                        <strong>預金種目:</strong> {{ $customer->deposit_type ?? 'N/A' }}
                                    </div>
                                    <div class="mb-1">
                                        <strong>顧客番号:</strong> {{ $customer->customer_number ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>

                            <!-- Financial Details -->
                            <div class="col-md-4">
                                <h6 class="text-success mb-2">
                                    <i class="fas fa-yen-sign me-1"></i> 金額詳細
                                </h6>
                                <div class="small">
                                    <div class="mb-1">
                                        <strong>徴収請求額:</strong> 
                                        <span class="text-success">¥{{ number_format($customer->collection_request_amount ?? 0) }}</span>
                                    </div>
                                    <div class="mb-1">
                                        <strong>消費税:</strong> 
                                        <span class="text-info">¥{{ number_format($customer->consumption_tax ?? 0) }}</span>
                                    </div>
                                    <div class="mb-1">
                                        <strong>請求先差額:</strong> 
                                        <span class="text-warning">¥{{ number_format($customer->billing_difference ?? 0) }}</span>
                                    </div>
                                </div>
                            </div>

                            <!-- Billing Address -->
                            <div class="col-md-4">
                                <h6 class="text-info mb-2">
                                    <i class="fas fa-map-marker-alt me-1"></i> 請求先住所
                                </h6>
                                <div class="small">
                                    <div class="mb-1">
                                        <strong>郵便番号:</strong> {{ $customer->billing_postal_code ?? 'N/A' }}
                                    </div>
                                    <div class="mb-1">
                                        <strong>県名:</strong> {{ $customer->billing_prefecture ?? 'N/A' }}
                                    </div>
                                    <div class="mb-1">
                                        <strong>市区町村:</strong> {{ $customer->billing_city ?? 'N/A' }}
                                    </div>
                                    <div class="mb-1">
                                        <strong>番地:</strong> {{ $customer->billing_street ?? 'N/A' }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card border-0">
                <div class="card-body text-center py-5">
                    <i class="fas fa-users fa-4x text-muted mb-4"></i>
                    <h4 class="text-muted mb-2">顧客が見つかりません</h4>
                    <p class="text-muted">検索条件を変更するか、新しい顧客を追加してください。</p>
                    <a href="{{ route('customers.create') }}" class="btn btn-primary mt-3">
                        <i class="fas fa-plus me-1"></i> 顧客を追加
                    </a>
                </div>
            </div>
        </div>
    @endforelse
</div>

<!-- Pagination -->
@if($customers->hasPages())
<div class="d-flex justify-content-between align-items-center mt-4">
    <div class="text-muted">
        <small>全 {{ $customers->total() }} 件中 {{ $customers->firstItem() ?? 0 }} 〜 {{ $customers->lastItem() ?? 0 }} を表示</small>
    </div>
    <div>
        {{ $customers->links() }}
    </div>
</div>
@endif

<!-- Toggle All Details Button -->
<div class="position-fixed bottom-0 end-0 m-4">
    <div class="btn-group-vertical">
        <button class="btn btn-primary btn-sm shadow" onclick="toggleAllDetails(true)" title="すべて展開">
            <i class="fas fa-expand-alt"></i>
        </button>
        <button class="btn btn-secondary btn-sm shadow" onclick="toggleAllDetails(false)" title="すべて折りたたみ">
            <i class="fas fa-compress-alt"></i>
        </button>
    </div>
</div>
@endsection

@section('scripts')
<script>
function exportCustomers(format) {
    const params = new URLSearchParams(window.location.search);
    if (format === 'xlsx') {
        window.location.href = '{{ route("customers.export-xlsx") }}?' + params.toString();
    } else {
        params.set('export', 'csv');
        window.location.href = '{{ route("customers.export-csv") }}?' + params.toString();
    }
}

function toggleAllDetails(expand) {
    const collapseElements = document.querySelectorAll('[id^="details-"]');
    const toggleButtons = document.querySelectorAll('[data-bs-target^="#details-"] i');
    
    collapseElements.forEach((element, index) => {
        const bsCollapse = new bootstrap.Collapse(element, {toggle: false});
        const icon = toggleButtons[index];
        
        if (expand) {
            bsCollapse.show();
            icon.className = 'fas fa-chevron-up';
        } else {
            bsCollapse.hide();
            icon.className = 'fas fa-chevron-down';
        }
    });
}

// Handle individual toggle button icon changes
document.addEventListener('DOMContentLoaded', function() {
    const toggleButtons = document.querySelectorAll('[data-bs-target^="#details-"]');
    
    toggleButtons.forEach(button => {
        const targetId = button.getAttribute('data-bs-target');
        const targetElement = document.querySelector(targetId);
        const icon = button.querySelector('i');
        
        targetElement.addEventListener('shown.bs.collapse', function() {
            icon.className = 'fas fa-chevron-up';
        });
        
        targetElement.addEventListener('hidden.bs.collapse', function() {
            icon.className = 'fas fa-chevron-down';
        });
    });
});
</script>

<style>
/* Custom styles for enhanced visual appeal */
.card {
    transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.1) !important;
}

.btn-group .btn {
    transition: all 0.2s ease-in-out;
}

.btn-group .btn:hover {
    transform: scale(1.05);
}

.position-fixed .btn {
    transition: all 0.2s ease-in-out;
}

.position-fixed .btn:hover {
    transform: scale(1.1);
}

/* Responsive improvements */
@media (max-width: 768px) {
    .col-md-3, .col-md-4, .col-md-2 {
        margin-bottom: 1rem;
    }
    
    .btn-group {
        width: 100%;
    }
    
    .btn-group .btn {
        flex: 1;
    }
}

/* Color-coded payment status */
.payment-status-21 { border-left: 4px solid #28a745; }
.payment-status-22 { border-left: 4px solid #ffc107; }
.payment-status-23 { border-left: 4px solid #dc3545; }

/* Enhanced typography */
.fw-semibold {
    font-weight: 600;
}

/* Improved spacing for details section */
.collapse .row > div {
    padding: 0.5rem;
    background: rgba(248, 249, 250, 0.5);
    border-radius: 0.375rem;
    margin: 0 0.25rem;
}
</style>
@endsection
