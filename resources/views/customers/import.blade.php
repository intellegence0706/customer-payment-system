@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">顧客データ一括取込 (XLSX)</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> 顧客一覧に戻る
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

            <form method="POST" action="{{ route('customers.import-xlsx') }}" enctype="multipart/form-data" id="importForm">
                @csrf
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">
                            <i class="fas fa-upload me-2"></i>XLSXファイルをアップロード
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="customer_file" class="form-label">
                                    顧客データファイル (XLSX)
                                    <span class="text-danger">*</span>
                                </label>
                                <input type="file" class="form-control @error('customer_file') is-invalid @error"
                                    id="customer_file" name="customer_file" accept=".xlsx" required
                                    onchange="previewFile(this)" />
                                <div class="form-text">
                                    <small>
                                        <i class="fas fa-info-circle me-1"></i>
                                        最大ファイルサイズ: 5MB。形式: XLSXのみ
                                    </small>
                                </div>
                                @error('customer_file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <button type="submit" class="btn btn-primary" id="importBtn">
                                <i class="fas fa-upload me-1"></i> 顧客データを取込
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle me-2"></i>XLSXファイル形式
                    </h5>
                </div>
                <div class="card-body">
                    <h6>必須列 (Required Columns):</h6>
                    <ul class="list-unstyled">
                        <li><strong>利用者氏名</strong> - 顧客の氏名</li>
                        <li><strong>顧客番号</strong> - 一意の顧客番号</li>
                    </ul>
                    
                    <h6>推奨列 (Recommended Columns):</h6>
                    <ul class="list-unstyled">
                        <li><strong>利用者カナ氏名</strong> - カタカナ氏名</li>
                        <li><strong>口座カナ氏名</strong> - 口座のカタカナ名義</li>
                        <li><strong>口座人氏名</strong> - 口座の漢字名義</li>
                        <li><strong>顧客コード</strong> - 内部顧客コード</li>
                        <li><strong>支払区分</strong> - 支払いの分類</li>
                        <li><strong>支払方法</strong> - 支払い方法</li>
                        <li><strong>請求金額</strong> - 請求額</li>
                        <li><strong>銀行番号</strong> - 4桁の銀行コード</li>
                        <li><strong>銀行名</strong> - 銀行名</li>
                        <li><strong>支店番号</strong> - 3桁の支店コード</li>
                        <li><strong>支店名</strong> - 支店名</li>
                        <li><strong>口座番号</strong> - 口座番号</li>
                        <li><strong>請求先郵便番号</strong> - 請求先の郵便番号</li>
                        <li><strong>請求先県名</strong> - 請求先の都道府県</li>
                        <li><strong>請求先市区町村</strong> - 請求先の市区町村</li>
                    </ul>
                    
                    <div class="alert alert-info mt-3">
                        <small>
                            <i class="fas fa-lightbulb me-1"></i>
                            <strong>注意:</strong> 顧客番号は一意である必要があります。重複する顧客番号がある行はスキップされます。
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-table me-2"></i>XLSXファイル例
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>顧客コード</th>
                                    <th>利用者カナ氏名</th>
                                    <th>利用者氏名</th>
                                    <th>口座カナ氏名</th>
                                    <th>口座人氏名</th>
                                    <th>顧客番号</th>
                                    <th>支払区分</th>
                                    <th>支払方法</th>
                                    <th>請求金額</th>
                                    <th>銀行番号</th>
                                    <th>銀行名</th>
                                    <th>支店番号</th>
                                    <th>支店名</th>
                                    <th>口座番号</th>
                                    <th>請求先県名</th>
                                    <th>請求先市区町村</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1234</td>
                                    <td>ヤマダ タロウ</td>
                                    <td>山田 太郎</td>
                                    <td>ヤマダ タロウ</td>
                                    <td>山田太郎</td>
                                    <td>15430000000000001111</td>
                                    <td>21</td>
                                    <td>銀行,集金代行</td>
                                    <td>¥500</td>
                                    <td>0001</td>
                                    <td>ミズホギンコウ</td>
                                    <td>558</td>
                                    <td>トコロザワシテン</td>
                                    <td>1234567</td>
                                    <td>埼玉県</td>
                                    <td>所沢市</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function previewFile(input) {
    const file = input.files[0];
    if (file) {
        // Show file info
        const fileInfo = document.createElement('div');
        fileInfo.className = 'alert alert-info mt-2';
        fileInfo.innerHTML = `
            <i class="fas fa-file-excel me-2"></i>
            <strong>選択されたファイル:</strong> ${file.name}<br>
            <small>サイズ: ${(file.size / 1024 / 1024).toFixed(2)} MB</small>
        `;
        
        const existingInfo = input.parentNode.querySelector('.alert');
        if (existingInfo) {
            existingInfo.remove();
        }
        
        input.parentNode.appendChild(fileInfo);
    }
}

document.getElementById('importForm').addEventListener('submit', function(e) {
    const fileInput = document.getElementById('customer_file');
    const importBtn = document.getElementById('importBtn');
    
    if (!fileInput.files[0]) {
        alert('顧客データファイルを選択してください。');
        e.preventDefault();
        return;
    }
    
    // Disable button and show loading state
    importBtn.disabled = true;
    importBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> 取込中...';
});
</script>
@endpush
