@extends('layouts.app')

@section('title', 'السجل العام للقروض التمويلية البيضاء - نظام حصاد')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="fa-solid fa-hand-holding-dollar text-warning me-2"></i> السجل العام للقروض التمويلية البيضاء
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item active">التمويل والبنية التحتية</li>
                </ol>
            </nav>
        </div>
        <div>
            <span class="badge bg-warning bg-opacity-10 text-dark border border-warning p-2">
                <i class="fa-solid fa-shield-halved text-warning me-1"></i> جميع القروض بيضاء بدون أي فوائد (0% فائدة)
            </span>
        </div>
    </div>

    <!-- بطاقات إحصائيات القروض -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 text-center">
                <span class="text-muted small fw-bold">إجمالي القروض البيضاء</span>
                <h4 class="fw-bold text-dark mb-0">{{ number_format($stats['total_loans']) }}</h4>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 text-center">
                <span class="text-muted small fw-bold">إجمالي مبالغ التمويل (ر.ي)</span>
                <h4 class="fw-bold text-primary mb-0">{{ number_format($stats['total_amount']) }}</h4>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 text-center">
                <span class="text-muted small fw-bold">إجمالي المبالغ المسددة (ر.ي)</span>
                <h4 class="fw-bold text-success mb-0">{{ number_format($stats['total_paid']) }}</h4>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 text-center">
                <span class="text-muted small fw-bold">الرصيد المتبقي للتحصيل (ر.ي)</span>
                <h4 class="fw-bold text-danger mb-0">{{ number_format($stats['total_remaining']) }}</h4>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">سجل القروض البيضاء المعتمدة</h6>
            <span class="badge bg-light text-dark border">صفحة {{ $loans->currentPage() }}</span>
        </div>
        <div class="card-body p-0">
            @if($loans->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-hand-holding-dollar fs-1 mb-2 d-block"></i>
                    لا توجد قروض بيضاء مسجلة حالياً.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-nowrap">
                        <thead class="table-light">
                            <tr>
                                <th>رقم القرض</th>
                                <th>المستفيد</th>
                                <th>الجمعية المرتبطة</th>
                                <th>الجهة الممولة</th>
                                <th>المبلغ (ر.ي)</th>
                                <th>المسدد</th>
                                <th>المتبقي (تلقائي)</th>
                                <th>الحالة</th>
                                <th>الغرض</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($loans as $loan)
                                <tr>
                                    <td><span class="badge bg-secondary font-monospace">{{ $loan->code }}</span></td>
                                    <td class="fw-bold">{{ $loan->beneficiary_name }}</td>
                                    <td>
                                        @if($loan->association)
                                            <a href="{{ route('associations.show', $loan->association_id) }}" class="text-primary fw-bold text-decoration-none">
                                                {{ $loan->association->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $loan->funding_entity }}</td>
                                    <td class="font-monospace fw-bold">{{ number_format($loan->loan_amount) }}</td>
                                    <td class="font-monospace text-success">{{ number_format($loan->paid_amount) }}</td>
                                    <td class="font-monospace text-danger fw-bold">{{ number_format($loan->remaining_balance) }}</td>
                                    <td>
                                        @if($loan->remaining_balance <= 0)
                                            <span class="badge bg-success">مسدد بالكامل</span>
                                        @else
                                            <span class="badge bg-warning text-dark">{{ $loan->loan_status }}</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">{{ $loan->purpose ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($loans->hasPages())
                    <div class="card-footer bg-white border-0">{{ $loans->links('pagination::bootstrap-5') }}</div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
