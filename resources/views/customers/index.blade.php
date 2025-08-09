@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">顧客</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="{{ route('customers.create') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> 顧客を追加
            </a>
            <button type="button" class="btn btn-outline-secondary" onclick="exportCustomers()">
                <i class="fas fa-download me-1"></i> CSVエクスポート
            </button>
        </div>
    </div>
</div>

<!-- Search and Filter Form -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('customers.index') }}" class="row g-3">
            <div class="col-md-4">
                <label for="search" class="form-label">検索</label>
                <input type="text" class="form-control" id="search" name="search" 
                       value="{{ request('search') }}" placeholder="氏名・顧客番号・電話番号・口座など">
            </div>
            <div class="col-md-3">
                <label for="gender" class="form-label">性別</label>
                <select class="form-select" id="gender" name="gender">
                    <option value="">すべて</option>
                    <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>男性</option>
                    <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>女性</option>
                    <option value="other" {{ request('gender') == 'other' ? 'selected' : '' }}>その他</option>
                </select>
            </div>
            <div class="col-md-3">
                <label for="bank_name" class="form-label">銀行</label>
                <input type="text" class="form-control" id="bank_name" name="bank_name" 
                       value="{{ request('bank_name') }}" placeholder="銀行名">
            </div>
            <div class="col-md-2">
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

<!-- Customers Table -->
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>顧客番号</th>
                        <th>氏名</th>
                        <th>性別</th>
                        <th>電話番号</th>
                        <th>銀行</th>
                        <th>口座番号</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr>
                            <td>{{ $customer->customer_number }}</td>
                            <td>{{ $customer->name }}</td>
                            <td>
                                <span class="badge bg-{{ $customer->gender == 'male' ? 'primary' : ($customer->gender == 'female' ? 'success' : 'secondary') }}">
                                    {{ ucfirst($customer->gender) }}
                                </span>
                            </td>
                            <td>{{ $customer->phone_number }}</td>
                            <td>{{ $customer->bank_name }}</td>
                            <td>{{ $customer->account_number }}</td>
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('customers.show', $customer) }}" class="btn btn-outline-info">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-outline-warning">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                     <form method="POST" action="{{ route('customers.destroy', $customer) }}" 
                                          style="display: inline;" onsubmit="return confirm('削除してよろしいですか？')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                 <p class="text-muted">顧客が見つかりません。</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-between align-items-center mt-3">
            <div>
                全 {{ $customers->total() }} 件中 {{ $customers->firstItem() ?? 0 }} 〜 {{ $customers->lastItem() ?? 0 }} を表示
            </div>
            {{ $customers->links() }}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function exportCustomers() {
    const params = new URLSearchParams(window.location.search);
    params.set('export', 'csv');
    window.location.href = '{{ route("customers.export-csv") }}?' + params.toString();
}
</script>
@endsection
