@extends('layouts.app')

@section('title', 'السجل العام للقروض التمويلية البيضاء - نظام حصاد')

@section('content')
<div class="container-fluid p-0">
    <!-- الترويسة الرئيسية والمسار الإرشادي -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                <span class="text-success fs-5"><i class="fa-solid fa-hand-holding-dollar"></i></span>
                السجل العام للقروض التمويلية البيضاء
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small text-muted">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">الرئيسية</a></li>
                    <li class="breadcrumb-item active text-success fw-bold" aria-current="page">القروض التمويلية البيضاء</li>
                </ol>
            </nav>
        </div>
        <div>
            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2 rounded-pill small fw-bold d-flex align-items-center gap-1 shadow-sm">
                <i class="fa-solid fa-shield-halved"></i>
                <span>جميع القروض بيضاء حسنة بدون أي فوائد (0% فائدة)</span>
            </span>
        </div>
    </div>

    <!-- بطاقات إحصائيات محفظة القروض -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="kpi-title">إجمالي القروض البيضاء</div>
                        <div class="kpi-value text-dark stat-num">{{ number_format($stats['total_loans']) }}</div>
                        <div class="kpi-subtitle">عقد تمويل معتمد</div>
                    </div>
                    <div class="kpi-icon bg-light text-primary">
                        <i class="fa-solid fa-file-contract"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="kpi-title">إجمالي مبالغ التمويل</div>
                        <div class="kpi-value text-primary stat-num">{{ number_format($stats['total_amount']) }} <small class="fs-6 text-muted">ر.ي</small></div>
                        <div class="kpi-subtitle">محفظة التمويل القائم</div>
                    </div>
                    <div class="kpi-icon bg-primary-subtle text-primary">
                        <i class="fa-solid fa-money-bill-wave"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="kpi-title">إجمالي المبالغ المسددة</div>
                        <div class="kpi-value text-success stat-num">{{ number_format($stats['total_paid']) }} <small class="fs-6 text-muted">ر.ي</small></div>
                        <div class="kpi-subtitle">أقساط محصلة بالفعل</div>
                    </div>
                    <div class="kpi-icon bg-success-subtle text-success">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="kpi-title">الرصيد المتبقي للتحصيل</div>
                        <div class="kpi-value text-danger stat-num">{{ number_format($stats['total_remaining']) }} <small class="fs-6 text-muted">ر.ي</small></div>
                        <div class="kpi-subtitle">أقساط جارية التحصيل</div>
                    </div>
                    <div class="kpi-icon bg-danger-subtle text-danger">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- جدول سجل القروض البيضاء -->
    <div class="card-hasad">
        <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-list-check text-success"></i>
                <h6 class="fw-bold mb-0 text-dark">سجل القروض البيضاء المعتمدة والمحفظة التمويلية</h6>
            </div>
            <span class="badge bg-light text-muted border px-3 py-2 rounded-pill font-monospace small">
                صفحة {{ $loans->currentPage() }} من {{ $loans->lastPage() }}
            </span>
        </div>
        <div class="card-body p-0">
            @if($loans->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-hand-holding-dollar fs-1 mb-2 d-block text-secondary opacity-50"></i>
                    <p class="mb-0 fw-medium">لا توجد قروض بيضاء مسجلة حالياً.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table-hasad align-middle mb-0 text-nowrap">
                        <thead>
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
                                    <td><span class="badge bg-light text-dark border font-monospace">{{ $loan->code }}</span></td>
                                    <td class="fw-bold text-dark">{{ $loan->beneficiary_name }}</td>
                                    <td>
                                        @if($loan->association)
                                            <a href="{{ route('associations.show', $loan->association_id) }}" class="text-success fw-bold text-decoration-none">
                                                {{ $loan->association->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $loan->funding_entity }}</td>
                                    <td class="stat-num fw-bold">{{ number_format($loan->loan_amount) }}</td>
                                    <td class="stat-num text-success fw-semibold">{{ number_format($loan->paid_amount) }}</td>
                                    <td class="stat-num text-danger fw-bold">{{ number_format($loan->remaining_balance) }}</td>
                                    <td>
                                        @if($loan->remaining_balance <= 0)
                                            <span class="badge-status-active">مسدد بالكامل</span>
                                        @else
                                            <span class="badge-status-pending">{{ $loan->loan_status }}</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">{{ $loan->purpose ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-top">
                    {{ $loans->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
