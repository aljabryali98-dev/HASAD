@extends('layouts.app')

@section('title', 'لوحة التحكم المركزية - نظام حصاد')

@section('content')
<div class="container-fluid p-0">
    <!-- Header & Welcome Banner -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="fa-solid fa-gauge-high text-primary me-2"></i> لوحة الإحصائيات والمتابعة المركزية (حصاد ERP)
            </h4>
            <p class="text-muted small mb-0">نظام الإحصاء والمتابعة والتحليل الشامل للعمل التعاوني والتنموي والزراعي في الجمهورية اليمنية</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-white text-secondary border px-3 py-2 shadow-sm fs-6">
                <i class="fa-regular fa-calendar-alt text-primary me-1"></i> {{ date('Y-m-d') }}
            </span>
            <a href="{{ route('geo-explorer.index') }}" class="btn btn-primary shadow-sm">
                <i class="fa-solid fa-earth-asia me-1"></i> المستكشف الجغرافي الشامل
            </a>
        </div>
    </div>

    <!-- Quick Action Bar -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <a href="{{ route('associations.create') }}" class="card border-0 shadow-sm text-decoration-none h-100 hover-card p-3 text-center border-start border-primary border-4">
                <i class="fa-solid fa-building-ngo text-primary fs-3 mb-2"></i>
                <h6 class="fw-bold text-dark mb-0">إضافة جمعية جديدة</h6>
                <span class="text-muted small">تأسيس ملف جمعية متكامل</span>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('productive-groups.index') }}" class="card border-0 shadow-sm text-decoration-none h-100 hover-card p-3 text-center border-start border-success border-4">
                <i class="fa-solid fa-users-line text-success fs-3 mb-2"></i>
                <h6 class="fw-bold text-dark mb-0">المجاميع الإنتاجية</h6>
                <span class="text-muted small">إدارة المجاميع وتوزيع الميسرين</span>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('personnel.index') }}" class="card border-0 shadow-sm text-decoration-none h-100 hover-card p-3 text-center border-start border-info border-4">
                <i class="fa-solid fa-user-ninja text-info fs-3 mb-2"></i>
                <h6 class="fw-bold text-dark mb-0">بنك الفرسان والكوادر</h6>
                <span class="text-muted small">17 تخصصاً للفرسان والباحثين</span>
            </a>
        </div>
        <div class="col-6 col-md-3">
            <a href="{{ route('general.loans.index') }}" class="card border-0 shadow-sm text-decoration-none h-100 hover-card p-3 text-center border-start border-warning border-4">
                <i class="fa-solid fa-hand-holding-dollar text-warning fs-3 mb-2"></i>
                <h6 class="fw-bold text-dark mb-0">محفظة القروض البيضاء</h6>
                <span class="text-muted small">تمويل حسن بدون فوائد 0%</span>
            </a>
        </div>
    </div>

    <!-- Main Metric Cards (4 Pillars) -->
    <div class="row g-3 mb-4">
        <!-- 1. الجمعيات التعاونية -->
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted fw-bold small">الجمعيات التعاونية</span>
                        <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2">
                            <i class="fa-solid fa-building-ngo fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-dark mb-1">{{ number_format($kpis['associations_count']) }}</h3>
                    <div class="d-flex justify-content-between align-items-center text-muted small mt-2">
                        <span>الجمعيات النشطة: <strong class="text-success">{{ number_format($kpis['active_associations']) }}</strong></span>
                        <a href="{{ route('associations.index') }}" class="text-primary text-decoration-none fw-bold">عرض الكل &larr;</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. المجاميع الإنتاجية -->
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted fw-bold small">المجاميع الإنتاجية</span>
                        <div class="bg-success bg-opacity-10 text-success rounded-circle p-2">
                            <i class="fa-solid fa-people-group fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-dark mb-1">{{ number_format($kpis['groups_count']) }}</h3>
                    <div class="d-flex justify-content-between align-items-center text-muted small mt-2">
                        <span>إجمالي الأعضاء: <strong class="text-success">{{ number_format($kpis['group_members']) }}</strong></span>
                        <a href="{{ route('productive-groups.index') }}" class="text-success text-decoration-none fw-bold">عرض الكل &larr;</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. الكوادر وفرسان التنمية -->
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted fw-bold small">الكوادر والفرسان الميدانيين</span>
                        <div class="bg-info bg-opacity-10 text-info rounded-circle p-2">
                            <i class="fa-solid fa-graduation-cap fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-dark mb-1">{{ number_format($kpis['personnel_count']) }}</h3>
                    <div class="d-flex justify-content-between align-items-center text-muted small mt-2">
                        <span>فرسان التنمية: <strong class="text-info">{{ number_format($kpis['knights_count']) }}</strong></span>
                        <a href="{{ route('personnel.index') }}" class="text-info text-decoration-none fw-bold">عرض الكل &larr;</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. محفظة القروض البيضاء -->
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted fw-bold small">محفظة القروض البيضاء (0% فوائد)</span>
                        <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-2">
                            <i class="fa-solid fa-hand-holding-dollar fs-5"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold text-dark mb-1">{{ number_format($kpis['loans_amount']) }} <span class="fs-6 fw-normal text-muted">ر.ي</span></h3>
                    <div class="d-flex justify-content-between align-items-center text-muted small mt-2">
                        <span>المسدد: <strong class="text-success">{{ number_format($kpis['loans_paid']) }}</strong></span>
                        <span>المتبقي: <strong class="text-danger">{{ number_format($kpis['loans_remaining']) }}</strong></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Metric Cards (Sector Summaries) -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 bg-light rounded-3 text-primary"><i class="fa-solid fa-list-check fs-4"></i></div>
                    <div>
                        <span class="text-muted small fw-bold d-block">المشاريع التعاونية</span>
                        <h5 class="fw-bold text-dark mb-0">{{ number_format($kpis['projects_count']) }}</h5>
                        <span class="text-muted small">{{ number_format($kpis['projects_cost']) }} ر.ي</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 bg-light rounded-3 text-success"><i class="fa-solid fa-person-digging fs-4"></i></div>
                    <div>
                        <span class="text-muted small fw-bold d-block">سجل المزارعين</span>
                        <h5 class="fw-bold text-dark mb-0">{{ number_format($kpis['farmers_count']) }}</h5>
                        <span class="text-muted small">{{ number_format($kpis['cultivated_area']) }} مساحة مزروعة</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 bg-light rounded-3 text-info"><i class="fa-solid fa-tree fs-4"></i></div>
                    <div>
                        <span class="text-muted small fw-bold d-block">المشاتل الزراعية</span>
                        <h5 class="fw-bold text-dark mb-0">{{ number_format($kpis['nurseries_count']) }}</h5>
                        <span class="text-muted small">شتلات وأصول زراعية</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-3 bg-light rounded-3 text-secondary"><i class="fa-solid fa-hands-holding-child fs-4"></i></div>
                    <div>
                        <span class="text-muted small fw-bold d-block">المبادرات والتوعية</span>
                        <h5 class="fw-bold text-dark mb-0">{{ number_format($kpis['initiatives_count']) }}</h5>
                        <span class="text-muted small">مبادرات مجتمعية نشطة</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Recent Activity Tables (Bi-directional Synchronization Proof) -->
    <div class="row g-4">
        <!-- الجمعيات الحديثة -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-building-ngo text-primary me-2"></i> أحدث الجمعيات التعاونية المسجلة
                    </h6>
                    <a href="{{ route('associations.index') }}" class="btn btn-sm btn-outline-primary">عرض السجل الكامل</a>
                </div>
                <div class="card-body p-0">
                    @if($recentAssociations->isEmpty())
                        <div class="text-center py-4 text-muted">لا توجد جمعيات مسجلة حالياً</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 text-nowrap">
                                <thead class="table-light small">
                                    <tr>
                                        <th>الكود</th>
                                        <th>اسم الجمعية</th>
                                        <th>المحافظة / المديرية</th>
                                        <th>الحالة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentAssociations as $assoc)
                                        <tr>
                                            <td><span class="badge bg-light text-dark font-monospace border">{{ $assoc->code }}</span></td>
                                            <td>
                                                <a href="{{ route('associations.show', $assoc->id) }}" class="fw-bold text-primary text-decoration-none">
                                                    {{ $assoc->name }}
                                                </a>
                                            </td>
                                            <td>{{ $assoc->governorate?->name_ar }} - {{ $assoc->directorate?->name_ar }}</td>
                                            <td>
                                                <span class="badge {{ $assoc->status === 'نشطة' ? 'bg-success' : 'bg-warning text-dark' }}">
                                                    {{ $assoc->status }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- المجاميع الإنتاجية الحديثة -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-users-line text-success me-2"></i> أحدث المجاميع الإنتاجية
                    </h6>
                    <a href="{{ route('productive-groups.index') }}" class="btn btn-sm btn-outline-success">عرض كل المجاميع</a>
                </div>
                <div class="card-body p-0">
                    @if($recentGroups->isEmpty())
                        <div class="text-center py-4 text-muted">لا توجد مجاميع إنتاجية مسجلة حالياً</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0 text-nowrap">
                                <thead class="table-light small">
                                    <tr>
                                        <th>الكود</th>
                                        <th>اسم المجموعة</th>
                                        <th>الجمعية التابعة</th>
                                        <th>الأعضاء</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentGroups as $grp)
                                        <tr>
                                            <td><span class="badge bg-light text-dark font-monospace border">{{ $grp->code }}</span></td>
                                            <td>
                                                <a href="{{ route('productive-groups.show', $grp->id) }}" class="fw-bold text-success text-decoration-none">
                                                    {{ $grp->name }}
                                                </a>
                                            </td>
                                            <td>{{ $grp->association?->name ?: 'مجموعة مستقلة' }}</td>
                                            <td><span class="badge bg-success bg-opacity-10 text-success fw-bold">{{ $grp->members_count }} عضو</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
