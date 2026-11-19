@extends('layouts.app')

@section('title', 'لوحة المؤشرات المركزية - نظام حصاد')

@section('content')
<div class="container-fluid p-0">
    <!-- الترويسة الرئيسية والترحيب -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                <span class="text-success fs-5"><i class="fa-solid fa-gauge-high"></i></span>
                لوحة الإحصائيات والمتابعة المركزية
            </h4>
            <p class="text-muted small mb-0">نظام الإحصاء والمتابعة والتحليل التنموي والتعاوني والزراعي - الجمهورية اليمنية</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-white text-dark border px-3 py-2 shadow-sm rounded-pill font-monospace small">
                <i class="fa-regular fa-clock text-success me-1"></i> {{ date('Y-m-d') }}
            </span>
            <a href="{{ route('geo-explorer.index') }}" class="btn btn-hasad-primary btn-sm d-flex align-items-center gap-1 shadow-sm">
                <i class="fa-solid fa-earth-asia"></i>
                <span>المستكشف الجغرافي الشامل</span>
            </a>
        </div>
    </div>

    <!-- بطاقات الإجراءات والانتقال السريع -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-3">
            <a href="{{ route('associations.create') }}" class="action-card h-100">
                <div class="action-icon kpi-icon-emerald">
                    <i class="fa-solid fa-building-ngo"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">تسجيل جمعية</h6>
                <span class="text-muted small d-block">إضافة وتأسيس جمعية جديدة</span>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="{{ route('productive-groups.index') }}" class="action-card h-100">
                <div class="action-icon kpi-icon-emerald">
                    <i class="fa-solid fa-people-roof"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">المجاميع الإنتاجية</h6>
                <span class="text-muted small d-block">المتابعة وتكليف الميسرين</span>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="{{ route('personnel.index') }}" class="action-card h-100">
                <div class="action-icon kpi-icon-blue">
                    <i class="fa-solid fa-user-graduate"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">بنك الفرسان والكوادر</h6>
                <span class="text-muted small d-block">17 تخصصاً للفرسان والباحثين</span>
            </a>
        </div>
        <div class="col-6 col-lg-3">
            <a href="{{ route('general.loans.index') }}" class="action-card h-100">
                <div class="action-icon kpi-icon-amber">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">محفظة القروض البيضاء</h6>
                <span class="text-muted small d-block">تمويل تمكيني 0% فوائد</span>
            </a>
        </div>
    </div>

    <!-- المؤشرات المركزية الأربعة (Core Pillars) -->
    <div class="row g-3 mb-4">
        <!-- 1. الجمعيات التعاونية -->
        <div class="col-md-6 col-xl-3">
            <div class="kpi-card kpi-emerald h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted fw-bold small">الجمعيات التعاونية</span>
                    <div class="kpi-icon-badge kpi-icon-emerald">
                        <i class="fa-solid fa-building-columns"></i>
                    </div>
                </div>
                <h3 class="stat-num text-dark mb-2">{{ number_format($kpis['associations_count']) }}</h3>
                <div class="d-flex justify-content-between align-items-center small pt-2 border-top">
                    <span class="text-muted">نشطة: <strong class="text-success stat-num">{{ number_format($kpis['active_associations']) }}</strong></span>
                    <a href="{{ route('associations.index') }}" class="text-success text-decoration-none fw-bold">عرض السجل &larr;</a>
                </div>
            </div>
        </div>

        <!-- 2. المجاميع الإنتاجية -->
        <div class="col-md-6 col-xl-3">
            <div class="kpi-card kpi-emerald h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted fw-bold small">المجاميع الإنتاجية</span>
                    <div class="kpi-icon-badge kpi-icon-emerald">
                        <i class="fa-solid fa-people-group"></i>
                    </div>
                </div>
                <h3 class="stat-num text-dark mb-2">{{ number_format($kpis['groups_count']) }}</h3>
                <div class="d-flex justify-content-between align-items-center small pt-2 border-top">
                    <span class="text-muted">الأعضاء: <strong class="text-success stat-num">{{ number_format($kpis['group_members']) }}</strong></span>
                    <a href="{{ route('productive-groups.index') }}" class="text-success text-decoration-none fw-bold">عرض السجل &larr;</a>
                </div>
            </div>
        </div>

        <!-- 3. الكوادر وفرسان التنمية -->
        <div class="col-md-6 col-xl-3">
            <div class="kpi-card kpi-blue h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted fw-bold small">الكوادر والفرسان الميدانيين</span>
                    <div class="kpi-icon-badge kpi-icon-blue">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                </div>
                <h3 class="stat-num text-dark mb-2">{{ number_format($kpis['personnel_count']) }}</h3>
                <div class="d-flex justify-content-between align-items-center small pt-2 border-top">
                    <span class="text-muted">فرسان الجمعية: <strong class="text-primary stat-num">{{ number_format($kpis['knights_count']) }}</strong></span>
                    <a href="{{ route('personnel.index') }}" class="text-primary text-decoration-none fw-bold">عرض السجل &larr;</a>
                </div>
            </div>
        </div>

        <!-- 4. محفظة القروض البيضاء -->
        <div class="col-md-6 col-xl-3">
            <div class="kpi-card kpi-amber h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted fw-bold small">القروض البيضاء (0% فوائد)</span>
                    <div class="kpi-icon-badge kpi-icon-amber">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                    </div>
                </div>
                <h3 class="stat-num text-dark mb-2">
                    {{ number_format($kpis['loans_amount']) }} <span class="fs-6 fw-normal text-muted">ر.ي</span>
                </h3>
                <div class="d-flex justify-content-between align-items-center small pt-2 border-top">
                    <span class="text-muted">المسدد: <strong class="text-success stat-num">{{ number_format($kpis['loans_paid']) }}</strong></span>
                    <span class="text-muted">المتبقي: <strong class="text-danger stat-num">{{ number_format($kpis['loans_remaining']) }}</strong></span>
                </div>
            </div>
        </div>
    </div>

    <!-- المؤشرات القطاعية الثانوية -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card-hasad p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="kpi-icon-badge kpi-icon-blue" style="width: 42px; height: 42px; font-size: 1.1rem;">
                        <i class="fa-solid fa-list-check"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-bold d-block">المشاريع التنموية</span>
                        <h5 class="stat-num text-dark mb-0">{{ number_format($kpis['projects_count']) }}</h5>
                        <span class="text-muted small stat-num">{{ number_format($kpis['projects_cost']) }} ر.ي</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-hasad p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="kpi-icon-badge kpi-icon-emerald" style="width: 42px; height: 42px; font-size: 1.1rem;">
                        <i class="fa-solid fa-seedling"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-bold d-block">سجل المزارعين</span>
                        <h5 class="stat-num text-dark mb-0">{{ number_format($kpis['farmers_count']) }}</h5>
                        <span class="text-muted small stat-num">{{ number_format($kpis['cultivated_area']) }} مساحة مزروعة</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-hasad p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="kpi-icon-badge kpi-icon-emerald" style="width: 42px; height: 42px; font-size: 1.1rem;">
                        <i class="fa-solid fa-tree"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-bold d-block">المشاتل الزراعية</span>
                        <h5 class="stat-num text-dark mb-0">{{ number_format($kpis['nurseries_count']) }}</h5>
                        <span class="text-muted small">مشاتل مفعلة</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card-hasad p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="kpi-icon-badge kpi-icon-amber" style="width: 42px; height: 42px; font-size: 1.1rem;">
                        <i class="fa-solid fa-handshake"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-bold d-block">المبادرات المجتمعية</span>
                        <h5 class="stat-num text-dark mb-0">{{ number_format($kpis['initiatives_count']) }}</h5>
                        <span class="text-muted small">مبادرات ميدانية</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- جداول أحدث العمليات والبيانات -->
    <div class="row g-4">
        <!-- الجمعيات التعاونية الحديثة -->
        <div class="col-lg-6">
            <div class="card-hasad h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-building-columns text-success"></i>
                        <span class="fw-bold">أحدث الجمعيات التعاونية</span>
                    </div>
                    <a href="{{ route('associations.index') }}" class="btn btn-hasad-outline btn-sm py-1 px-2" style="font-size: 0.8rem;">عرض السجل الكامل</a>
                </div>
                <div class="card-body p-0">
                    @if($recentAssociations->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="fa-solid fa-folder-open fs-2 mb-2 d-block"></i>
                            لا توجد جمعيات مسجلة حديثاً
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hasad align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>الكود</th>
                                        <th>اسم الجمعية</th>
                                        <th>النطاق</th>
                                        <th>الحالة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentAssociations as $assoc)
                                        <tr>
                                            <td>
                                                <span class="badge bg-light text-dark font-monospace border">{{ $assoc->code }}</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('associations.show', $assoc->id) }}" class="fw-bold text-dark text-decoration-none">
                                                    {{ $assoc->name }}
                                                </a>
                                            </td>
                                            <td>
                                                <span class="text-muted small">{{ $assoc->governorate?->name_ar ?? $assoc->governorate?->name }}</span>
                                            </td>
                                            <td>
                                                @if($assoc->status === 'نشطة')
                                                    <span class="badge-status badge-status-success">
                                                        <span class="badge-status-dot"></span> نشطة
                                                    </span>
                                                @elseif($assoc->status === 'تحت التأسيس')
                                                    <span class="badge-status badge-status-warning">
                                                        <span class="badge-status-dot"></span> تحت التأسيس
                                                    </span>
                                                @else
                                                    <span class="badge-status badge-status-danger">
                                                        <span class="badge-status-dot"></span> {{ $assoc->status }}
                                                    </span>
                                                @endif
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
            <div class="card-hasad h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-people-roof text-success"></i>
                        <span class="fw-bold">أحدث المجاميع الإنتاجية</span>
                    </div>
                    <a href="{{ route('productive-groups.index') }}" class="btn btn-hasad-outline btn-sm py-1 px-2" style="font-size: 0.8rem;">عرض السجل الكامل</a>
                </div>
                <div class="card-body p-0">
                    @if($recentGroups->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="fa-solid fa-users-slash fs-2 mb-2 d-block"></i>
                            لا توجد مجاميع إنتاجية مسجلة حديثاً
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hasad align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>الكود</th>
                                        <th>اسم المجموعة</th>
                                        <th>الجمعية</th>
                                        <th>الأعضاء</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentGroups as $grp)
                                        <tr>
                                            <td>
                                                <span class="badge bg-light text-dark font-monospace border">{{ $grp->code }}</span>
                                            </td>
                                            <td>
                                                <a href="{{ route('productive-groups.show', $grp->id) }}" class="fw-bold text-dark text-decoration-none">
                                                    {{ $grp->name }}
                                                </a>
                                            </td>
                                            <td>
                                                <span class="text-muted small">{{ $grp->association?->name ?: 'مجموعة مستقلة' }}</span>
                                            </td>
                                            <td>
                                                <span class="badge-status badge-status-success stat-num">
                                                    {{ $grp->total_members ?? 0 }} عضو
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
    </div>
</div>
@endsection
