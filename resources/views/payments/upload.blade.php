@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">月末入金データの取込</h1>
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

    <div class="row">
        <div class="col-lg-8">
            <form method="POST" action="{{ route('payments.upload') }}" enctype="multipart/form-data" id="uploadForm">
                @csrf
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-upload me-2"></i>CSVファイルをアップロード
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="payment_file" class="form-label">
                                    CSV File
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="file" class="form-control @error('payment_file') is-invalid @enderror"
                                    id="payment_file" name="payment_file" accept=".csv,.txt" required
                                    onchange="previewFile(this)" />
                                <div class="form-text">
                                    <small>
                                        <i class="fas fa-info-circle me-1"></i>
                                        最大ファイルサイズ: 2MB。形式: 顧客番号, 金額, 入金日, 受付番号(任意)
                                    </small>
                                </div>
                                @error('payment_file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="payment_month" class="form-label">
                                    月 
                                </label>
                                <select class="form-select @error('payment_month') is-invalid @enderror" id="payment_month"
                                    name="payment_month" required>
                                    <option value="">Select Month</option>
                                    @for ($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}"
                                            {{ old('payment_month', $currentMonth) == $i ? 'selected' : '' }}>
                                            {{ date('F', mktime(0, 0, 0, $i, 1)) }}
                                        </option>
                                    @endfor
                                </select>
                                @error('payment_month')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-3">
                                <label for="payment_year" class="form-label">
                                    年</label>
                                <select class="form-select @error('payment_year') is-invalid @enderror" id="payment_year"
                                    name="payment_year" required>
                                    <option value="">Select Year</option>
                                    @for ($year = date('Y') + 1; $year >= 2020; $year--)
                                        <option value="{{ $year }}"
                                            {{ old('payment_year', $currentYear) == $year ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endfor
                                </select>
                                @error('payment_year')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <!-- File Preview Section -->
                        <div id="filePreview" class="mt-3" style="display: none;">
                            <div class="card">
                                <div class="card-header">
                                    <h6 class="mb-0">
                                        <i class="fas fa-eye me-2"></i>ファイルプレビュー
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <div id="previewContent" class="table-responsive">
                                        <!-- Preview content will be loaded here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CSV Format Instructions -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h6 class="mb-0">
                            <i class="fas fa-question-circle me-2"></i>CSV 形式の説明
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>必須フォーマット:</h6>
                                <ul class="list-unstyled">
                                    <li><strong>1列目:</strong> 顧客番号</li>
                                    <li><strong>2列目:</strong> 金額（数値）</li>
                                    <li><strong>3列目:</strong> 入金日（YYYY-MM-DD）</li>
                                    <li><strong>4列目:</strong> 受付番号（任意）</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6>例:</h6>
                                <pre class="bg-light p-2 rounded">
                                     <code>Customer Number,Amount,Payment Date,Receipt Number
                                        CUST001,1500.00,2024-01-15,RCPT001
                                        CUST002,2500.50,2024-01-16,RCPT002
                                    </code>
                                </pre>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                    <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary me-md-2">
                        <i class="fas fa-times me-1"></i> キャンセル
                    </a>
                    <button type="submit" class="btn btn-primary" id="uploadBtn">
                        <i class="fas fa-upload me-1"></i> 入金を取込
                    </button>
                </div>
            </form>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h6 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>取込ガイドライン
                    </h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            ファイルはCSVまたはTXT形式
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            最大サイズ: 2MB
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            1行目はヘッダー
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            顧客番号はシステムに存在している必要があります
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            同一月/年の重複入金はスキップされます
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-check text-success me-2"></i>
                            無効な行は結果に表示されます
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        function previewFile(input) {
            const file = input.files[0];
            const previewDiv = document.getElementById('filePreview');
            const previewContent = document.getElementById('previewContent');

            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const content = e.target.result;
                    const lines = content.split('\n');
                    const previewLines = lines.slice(0, 6); // Show first 5 data rows + header

                    let html = '<table class="table table-sm table-bordered">';
                    previewLines.forEach((line, index) => {
                        if (line.trim()) {
                            const cells = line.split(',');
                            html += '<tr>';
                            cells.forEach(cell => {
                                const cellClass = index === 0 ? 'table-primary' : '';
                                html += `<td class="${cellClass}">${cell.trim()}</td>`;
                            });
                            html += '</tr>';
                        }
                    });
                    html += '</table>';

                    if (lines.length > 6) {
                        html += `<small class="text-muted">... and ${lines.length - 6} more rows</small>`;
                    }

                    previewContent.innerHTML = html;
                    previewDiv.style.display = 'block';
                };
                reader.readAsText(file);
            } else {
                previewDiv.style.display = 'none';
            }
        }

        // Form validation
        document.getElementById('uploadForm').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('payment_file');
            const monthSelect = document.getElementById('payment_month');
            const yearSelect = document.getElementById('payment_year');
            const uploadBtn = document.getElementById('uploadBtn');

            // Disable button to prevent double submission
            uploadBtn.disabled = true;
            uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> 取込中...';

            // Basic validation
            if (!fileInput.files[0]) {
                e.preventDefault();
                alert('取込むファイルを選択してください。');
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = '<i class="fas fa-upload me-1"></i> 入金を取込';
                return;
            }

            if (!monthSelect.value || !yearSelect.value) {
                e.preventDefault();
                alert('月と年を選択してください。');
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = '<i class="fas fa-upload me-1"></i> 入金を取込';
                return;
            }
        });
    </script>
@endsection
