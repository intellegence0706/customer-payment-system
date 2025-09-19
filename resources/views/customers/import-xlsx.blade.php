@extends('layouts.app')

@section('title', '顧客情報XLSX一括登録')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow-lg border-0">
                <div class="card-header bg-gradient-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">
                            <i class="fas fa-file-excel me-2"></i>顧客情報XLSX一括登録
                        </h4>
                        <a href="{{ route('customers.index') }}" class="btn btn-light btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>戻る
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
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

                    <!-- Import Instructions -->
                    <div class="alert alert-info mb-4">
                        <h5><i class="fas fa-info-circle me-2"></i>XLSXファイル形式について</h5>
                        <p class="mb-2">以下の列順でデータを準備してください：</p>
                        <div class="row">
                            <div class="col-md-6">
                                <ol class="mb-0">
                                    <li>顧客コード</li>
                                    <li>賃借者カナ氏名</li>
                                    <li>賃借者氏名</li>
                                    <li>口座カナ氏名</li>
                                    <li>口座人氏名</li>
                                    <li>支払区分</li>
                                    <li>支払方法</li>
                                    <li>請求金額</li>
                                    <li>徴収請求額</li>
                                    <li>消費税</li>
                                    <li>銀行番号</li>
                                </ol>
                            </div>
                            <div class="col-md-6">
                                <ol start="12" class="mb-0">
                                    <li>銀行名</li>
                                    <li>支店番号</li>
                                    <li>支店名</li>
                                    <li>預金種目</li>
                                    <li>口座番号</li>
                                    <li>顧客番号</li>
                                    <li>請求先郵便番号</li>
                                    <li>請求先県名</li>
                                    <li>請求先市区町村</li>
                                    <li>請求先番地</li>
                                    <li>請求先建物名</li>
                                </ol>
                            </div>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-lightbulb me-1"></i>
                                1行目はヘッダーとして自動的にスキップされます。銀行コードが入力されている場合、銀行名・支店名は自動で補完されます。
                            </small>
                        </div>
                    </div>

                    <form id="importForm" method="POST" action="{{ route('customers.import-xlsx') }}" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="xlsx_file" class="form-label">
                                        <i class="fas fa-file-excel me-1"></i>XLSXファイルを選択
                                    </label>
                                    <input type="file" 
                                           class="form-control @error('xlsx_file') is-invalid @enderror" 
                                           id="xlsx_file" 
                                           name="xlsx_file" 
                                           accept=".xlsx,.xls" 
                                           required>
                                    @error('xlsx_file')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">
                                        <i class="fas fa-info-circle me-1"></i>最大ファイルサイズ: 10MB
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-center">
                            <button type="submit" class="btn btn-success btn-lg" id="importBtn">
                                <i class="fas fa-upload me-2"></i>インポート開始
                            </button>
                        </div>
                    </form>

                    <!-- Progress Bar (Hidden by default) -->
                    <div id="progressContainer" class="mt-4" style="display: none;">
                        <h5>インポート進行状況</h5>
                        <div class="progress mb-2">
                            <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" style="width: 0%"></div>
                        </div>
                        <div id="progressText" class="text-center">
                            <small class="text-muted">処理中...</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="resultsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fas fa-chart-bar me-2"></i>インポート結果
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="resultsContent">
               
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">閉じる</button>
                <a href="{{ route('customers.index') }}" class="btn btn-primary">
                    <i class="fas fa-list me-1"></i>顧客一覧へ
                </a>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('importForm');
    const importBtn = document.getElementById('importBtn');
    const progressContainer = document.getElementById('progressContainer');
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const fileInput = document.getElementById('xlsx_file');
        if (!fileInput.files[0]) {
            alert('ファイルを選択してください。');
            return;
        }
     
        importBtn.disabled = true;
        importBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>処理中...';
        progressContainer.style.display = 'block';

        const formData = new FormData(form);
        
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => {
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            return response.json();
        })
        .then(data => {
            // Hide progress
            progressContainer.style.display = 'none';
            importBtn.disabled = false;
            importBtn.innerHTML = '<i class="fas fa-upload me-2"></i>インポート開始';
            
            // Show results
            displayResults(data);
        })
        .catch(error => {
            console.error('Error:', error);
            progressContainer.style.display = 'none';
            importBtn.disabled = false;
            importBtn.innerHTML = '<i class="fas fa-upload me-2"></i>インポート開始';
            
            alert('インポート中にエラーが発生しました。');
        });
    });

    function displayResults(data) {
        const resultsContent = document.getElementById('resultsContent');
        
        let html = `
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="card text-center border-success">
                        <div class="card-body">
                            <h4 class="text-success">${data.success_count || 0}</h4>
                            <small class="text-muted">成功</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-danger">
                        <div class="card-body">
                            <h4 class="text-danger">${data.error_count || 0}</h4>
                            <small class="text-muted">エラー</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-warning">
                        <div class="card-body">
                            <h4 class="text-warning">${data.skipped_count || 0}</h4>
                            <small class="text-muted">スキップ</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center border-info">
                        <div class="card-body">
                            <h4 class="text-info">${data.total_processed || 0}</h4>
                            <small class="text-muted">総処理数</small>
                        </div>
                    </div>
                </div>
            </div>
        `;

        if (data.errors && data.errors.length > 0) {
            html += `
                <h6><i class="fas fa-exclamation-triangle me-2"></i>エラー詳細</h6>
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>行</th>
                                <th>エラー内容</th>
                            </tr>
                        </thead>
                        <tbody>
            `;
            
            data.errors.forEach(error => {
                html += `
                    <tr>
                        <td>${error.row}</td>
                        <td>${error.message}</td>
                    </tr>
                `;
            });
            
            html += `
                        </tbody>
                    </table>
                </div>
            `;
        }

        resultsContent.innerHTML = html;
        
        // Show modal without backdrop
        const modalElement = document.getElementById('resultsModal');
        const modal = new bootstrap.Modal(modalElement, {
            backdrop: false,
            keyboard: true,
            focus: true
        });
        modal.show();
        
        // Re-enable button when modal is closed
        modalElement.addEventListener('hidden.bs.modal', function () {
            importBtn.disabled = false;
            importBtn.innerHTML = '<i class="fas fa-upload me-2"></i>インポート開始';
        });
    }
});
</script>
@endsection
