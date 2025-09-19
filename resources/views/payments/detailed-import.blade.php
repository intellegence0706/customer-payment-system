@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">請求情報キャプチャ</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> 入金一覧へ
            </a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-header">
            <i class="fas fa-file-excel me-2"></i> XLSXを選択
        </div>
        <div class="card-body">
            <form id="previewForm" enctype="multipart/form-data">
                @csrf
                <div class="row g-3">
                    <div class="col-md-6">
                        <input type="file" class="form-control" name="xlsx_file" accept=".xlsx" required>
                    </div>
                    <div class="col-md-6 text-md-end">
                        <button type="submit" class="btn btn-primary" id="btnPreview">
                            <i class="fas fa-eye me-1"></i> プレビュー
                        </button>
                        <button type="button" class="btn btn-success ms-2" id="btnCommit" disabled>
                            <i class="fas fa-database me-1"></i> 取込実行
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div id="previewArea" style="display:none;">
        <div class="card">
            <div class="card-header">
                プレビュー結果
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered" id="previewTable"></table>
                </div>
            </div>
        </div>
    </div>

    <script>
    const previewForm = document.getElementById('previewForm');
    const btnPreview = document.getElementById('btnPreview');
    const btnCommit = document.getElementById('btnCommit');
    const previewTable = document.getElementById('previewTable');
    const previewArea = document.getElementById('previewArea');

    let lastRows = [];

    previewForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        btnPreview.disabled = true; btnPreview.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> 読み込み中...';
        btnCommit.disabled = true;
        const formData = new FormData(previewForm);
        try {
            const res = await fetch('{{ route('payments.detailed.preview') }}', {
                method: 'POST', headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}, body: formData
            });
            const json = await res.json();
            if (!json.success) throw new Error(json.error || 'プレビューに失敗しました');
            renderPreview(json);
            lastRows = json.rows || [];
            btnCommit.disabled = lastRows.length === 0;
        } catch (err) {
            alert(err.message);
        } finally {
            btnPreview.disabled = false; btnPreview.innerHTML = '<i class="fas fa-eye me-1"></i> プレビュー';
        }
    });

    btnCommit.addEventListener('click', async () => {
        if (!lastRows.length) return;
        btnCommit.disabled = true; btnCommit.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> 取込中...';
        try {
            const res = await fetch('{{ route('payments.detailed.commit') }}', {
                method: 'POST', headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                body: JSON.stringify({ rows: lastRows })
            });
            const json = await res.json();
            if (!json.success) throw new Error((json.errors && json.errors.join('\n')) || '取込に失敗しました');
            alert(`取込完了: ${json.imported} 件 / バッチ ${json.batch_id}`);
            location.href = '{{ route('payments.index') }}';
        } catch (err) {
            alert(err.message);
        } finally {
            btnCommit.disabled = false; btnCommit.innerHTML = '<i class="fas fa-database me-1"></i> 取込実行';
        }
    });

    function renderPreview(json) {
        previewArea.style.display = 'block';
        const header = ['row','対象年月','顧客CD','氏名カナ','氏名','枝番','商品名','数量','単価','金額','消費税','支払区分','支払方法'];
        let html = '<thead><tr>' + header.map(h => `<th>${h}</th>`).join('') + '</tr></thead><tbody>';
        (json.rows || []).forEach(r => {
            html += '<tr>' + header.map(h => `<td>${(r[h] ?? '').toString()}</td>`).join('') + '</tr>';
        });
        html += '</tbody>';
        previewTable.innerHTML = html;
    }
    </script>
@endsection


