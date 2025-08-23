@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">XLSXファイルビューア</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> 入金一覧に戻る
        </a>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        {{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-times-circle me-2"></i>
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- File Upload Section -->
<div class="row">
    <div class="col-lg-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-file-excel me-2"></i>XLSXファイルを選択
                </h5>
            </div>
            <div class="card-body">
                <form id="uploadForm" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label for="xlsx_file" class="form-label">
                            XLSXファイル <span class="text-danger">*</span>
                        </label>
                        <input type="file" class="form-control" id="xlsx_file" name="xlsx_file" 
                               accept=".xlsx" required>
                        <div class="form-text">
                            <small>
                                <i class="fas fa-info-circle me-1"></i>
                                最大ファイルサイズ: 5MB。XLSX形式のみ対応。
                            </small>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary" id="previewBtn">
                        <i class="fas fa-eye me-1"></i> プレビュー
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>XLSXビューアーについて
                </h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        ファイル内容を事前にプレビュー
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        日付範囲でフィルタリング
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        選択した行のみ取込可能
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        日本語文字完全対応
                    </li>
                    <li class="mb-2">
                        <i class="fas fa-check text-success me-2"></i>
                        データ検証機能付き
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Data Preview Section -->
<div id="dataPreviewSection" style="display: none;">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fas fa-table me-2"></i>データプレビュー
                <span id="fileInfo" class="text-muted ms-2"></span>
            </h5>
            <div>
                <button type="button" class="btn btn-sm btn-outline-primary" onclick="selectAllRows()">
                    <i class="fas fa-check-square me-1"></i> 全選択
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearSelection()">
                    <i class="fas fa-square me-1"></i> 選択解除
                </button>
            </div>
        </div>
        <div class="card-body">
            <!-- Date Filter Controls -->
            <div class="row mb-3">
                <div class="col-md-3">
                    <label for="dateFilterStart" class="form-label">開始日</label>
                    <input type="date" class="form-control form-control-sm" id="dateFilterStart" onchange="applyDateFilter()">
                </div>
                <div class="col-md-3">
                    <label for="dateFilterEnd" class="form-label">終了日</label>
                    <input type="date" class="form-control form-control-sm" id="dateFilterEnd" onchange="applyDateFilter()">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearDateFilter()">
                        <i class="fas fa-times me-1"></i> フィルタクリア
                    </button>
                </div>
                <div class="col-md-3 d-flex align-items-end justify-content-end">
                    <span id="rowCount" class="text-muted small"></span>
                </div>
            </div>
            
            <!-- Data Table -->
            <div class="table-responsive">
                <table class="table table-sm table-hover" id="dataTable">
                    <thead class="table-dark">
                        <tr>
                            <th width="50">
                                <input type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll()">
                            </th>
                            <th>行</th>
                            <th>顧客番号</th>
                            <th>金額</th>
                            <th>入金日</th>
                            <th>受付番号</th>
                            <th>状態</th>
                        </tr>
                    </thead>
                    <tbody id="dataTableBody">
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Import Section -->
    <div class="card mt-3">
        <div class="card-header">
            <h5 class="mb-0">
                <i class="fas fa-download me-2"></i>選択データの取込
            </h5>
        </div>
        <div class="card-body">
            <form id="importForm" method="POST" action="{{ route('payments.xlsx-import') }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="payment_month" class="form-label">
                            取込対象月 <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="payment_month" name="payment_month" required>
                            <option value="">月を選択</option>
                            @for ($i = 1; $i <= 12; $i++)
                                <option value="{{ $i }}">{{ $i }}月</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="payment_year" class="form-label">
                            取込対象年 <span class="text-danger">*</span>
                        </label>
                        <select class="form-select" id="payment_year" name="payment_year" required>
                            <option value="">年を選択</option>
                            @for ($year = date('Y') + 1; $year >= 2020; $year--)
                                <option value="{{ $year }}">{{ $year }}年</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-success" id="importBtn" disabled>
                            <i class="fas fa-upload me-1"></i> 選択データを取込
                        </button>
                    </div>
                </div>
                <div id="selectedRowsContainer"></div>
            </form>
        </div>
    </div>
</div>

<script>
let allData = [];
let filteredData = [];

document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    previewFile();
});

function previewFile() {
    const fileInput = document.getElementById('xlsx_file');
    const previewBtn = document.getElementById('previewBtn');
    
    if (!fileInput.files[0]) {
        alert('ファイルを選択してください。');
        return;
    }
    
    const formData = new FormData();
    formData.append('xlsx_file', fileInput.files[0]);
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').getAttribute('content'));
    
    previewBtn.disabled = true;
    previewBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> 処理中...';
    
    fetch('{{ route("payments.xlsx-preview") }}', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            allData = data.data;
            filteredData = [...allData];
            displayData();
            document.getElementById('dataPreviewSection').style.display = 'block';
            document.getElementById('fileInfo').textContent = `(${data.filename} - ${data.total_rows}行)`;
        } else {
            alert('エラー: ' + data.error);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('ファイル処理中にエラーが発生しました。');
    })
    .finally(() => {
        previewBtn.disabled = false;
        previewBtn.innerHTML = '<i class="fas fa-eye me-1"></i> プレビュー';
    });
}

function displayData() {
    const tbody = document.getElementById('dataTableBody');
    tbody.innerHTML = '';
    
    filteredData.forEach((row, index) => {
        const tr = document.createElement('tr');
        const status = validateRow(row);
        const statusClass = status.valid ? 'text-success' : 'text-danger';
        const statusIcon = status.valid ? 'fas fa-check' : 'fas fa-exclamation-triangle';
        
        tr.innerHTML = `
            <td>
                <input type="checkbox" class="row-checkbox" data-index="${index}" 
                       onchange="updateImportButton()" ${status.valid ? '' : 'disabled'}>
            </td>
            <td>${row.index}</td>
            <td>${row.customer_number}</td>
            <td>${row.amount}</td>
            <td>${row.payment_date}</td>
            <td>${row.receipt_number}</td>
            <td>
                <i class="${statusIcon} ${statusClass}" title="${status.message}"></i>
                <small class="${statusClass}">${status.message}</small>
            </td>
        `;
        
        tbody.appendChild(tr);
    });
    
    updateRowCount();
}

function validateRow(row) {
    if (!row.customer_number || row.customer_number.trim() === '') {
        return { valid: false, message: '顧客番号が必要' };
    }
    
    const amount = parseFloat(row.amount);
    if (isNaN(amount) || amount <= 0) {
        return { valid: false, message: '有効な金額が必要' };
    }
    
    if (!row.payment_date || row.payment_date.trim() === '') {
        return { valid: false, message: '入金日が必要' };
    }
    
    return { valid: true, message: '有効' };
}

function applyDateFilter() {
    const startDate = document.getElementById('dateFilterStart').value;
    const endDate = document.getElementById('dateFilterEnd').value;
    
    if (!startDate && !endDate) {
        filteredData = [...allData];
    } else {
        filteredData = allData.filter(row => {
            if (!row.payment_date) return false;
            
            const rowDate = new Date(row.payment_date);
            if (isNaN(rowDate)) return false;
            
            if (startDate && rowDate < new Date(startDate)) return false;
            if (endDate && rowDate > new Date(endDate)) return false;
            
            return true;
        });
    }
    
    displayData();
    clearSelection();
}

function clearDateFilter() {
    document.getElementById('dateFilterStart').value = '';
    document.getElementById('dateFilterEnd').value = '';
    applyDateFilter();
}

function selectAllRows() {
    const checkboxes = document.querySelectorAll('.row-checkbox:not(:disabled)');
    checkboxes.forEach(cb => cb.checked = true);
    updateImportButton();
}

function clearSelection() {
    const checkboxes = document.querySelectorAll('.row-checkbox');
    checkboxes.forEach(cb => cb.checked = false);
    document.getElementById('selectAllCheckbox').checked = false;
    updateImportButton();
}

function toggleSelectAll() {
    const selectAll = document.getElementById('selectAllCheckbox');
    const checkboxes = document.querySelectorAll('.row-checkbox:not(:disabled)');
    checkboxes.forEach(cb => cb.checked = selectAll.checked);
    updateImportButton();
}

function updateImportButton() {
    const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
    const importBtn = document.getElementById('importBtn');
    
    importBtn.disabled = checkedBoxes.length === 0;
    
    // Update selected rows data
    const container = document.getElementById('selectedRowsContainer');
    container.innerHTML = '';
    
    checkedBoxes.forEach(cb => {
        const index = cb.dataset.index;
        const row = filteredData[index];
        
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = `selected_rows[${index}][]`;
        input.value = JSON.stringify(row.raw_data);
        container.appendChild(input);
    });
}

function updateRowCount() {
    const total = filteredData.length;
    const valid = filteredData.filter(row => validateRow(row).valid).length;
    document.getElementById('rowCount').textContent = `${total}行表示 (有効: ${valid}行)`;
}
</script>
@endsection
