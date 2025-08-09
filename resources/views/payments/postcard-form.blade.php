@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">はがきデータの作成</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> 入金一覧に戻る
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <form method="GET" action="{{ route('payments.postcard-data') }}">
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">月と年を選択</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="month" class="form-label">月 <span class="text-danger">*</span></label>
                            <select class="form-select @error('month') is-invalid @enderror" id="month" name="month" required>
                                <option value="">月を選択</option>
                                <option value="1" {{ request('month') == '1' ? 'selected' : '' }}>January</option>
                                <option value="2" {{ request('month') == '2' ? 'selected' : '' }}>February</option>
                                <option value="3" {{ request('month') == '3' ? 'selected' : '' }}>March</option>
                                <option value="4" {{ request('month') == '4' ? 'selected' : '' }}>April</option>
                                <option value="5" {{ request('month') == '5' ? 'selected' : '' }}>May</option>
                                <option value="6" {{ request('month') == '6' ? 'selected' : '' }}>June</option>
                                <option value="7" {{ request('month') == '7' ? 'selected' : '' }}>July</option>
                                <option value="8" {{ request('month') == '8' ? 'selected' : '' }}>August</option>
                                <option value="9" {{ request('month') == '9' ? 'selected' : '' }}>September</option>
                                <option value="10" {{ request('month') == '10' ? 'selected' : '' }}>October</option>
                                <option value="11" {{ request('month') == '11' ? 'selected' : '' }}>November</option>
                                <option value="12" {{ request('month') == '12' ? 'selected' : '' }}>December</option>
                            </select>
                            @error('month')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="year" class="form-label">年 <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('year') is-invalid @enderror" id="year" name="year" min="2020" value="{{ request('year') }}" required>
                            @error('year')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                <a href="{{ route('payments.index') }}" class="btn btn-outline-secondary me-md-2">キャンセル</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-eye me-1"></i> プレビュー
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
