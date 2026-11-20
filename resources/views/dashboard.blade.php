@extends('layouts.app')

@section('title', 'لوحة المؤشرات المركزية - نظام حصاد')

@section('content')
<div class="container-fluid p-0">
    <!-- الترويسة الرئيسية والترحيب المؤسسي -->
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                <span class="text-success fs-5"><i class="fa-solid fa-gauge-high"></i></span>
                لوحة الإحصائيات والمتابعة المركزية
            </h4>
            <p class="text-muted small mb-0">نظام الإحصاء والمتابعة والتحليل التنموي والتعاوني والزراعي - الجمهورية اليمنية</p>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <span class="badge bg-white text-dark border px-3 py-2 shadow-sm rounded-pill font-monospace small">
                <i class="fa-regular fa-clock text-success me-1"></i> {{ date('Y-m-d') }}
            </span>
            <a href="{{ route('geo-explorer.index') }}" class="btn btn-hasad-primary btn-sm d-flex align-items-center gap-1 shadow-sm">
                <i class="fa-solid fa-earth-asia"></i>
                <span>المستكشف الجغرافي الشامل</span>
            </a>
            <button type="button" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-1" id="btnToggleDetailedView">
                <i class="fa-solid fa-sliders"></i>
                <span id="toggleViewText">العرض المختصر</span>
            </button>
        </div>
    </div>

    <!-- بطاقات الإجراءات والانتقال السريع الفاخرة -->
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

    <!-- المؤشرات السيادية الكبرى الأربعة (Executive Master Pillars) -->
    <div class="row g-3 mb-4">
        <!-- 1. الجمعيات التعاونية -->
        <div class="col-md-6 col-xl-3">
            <div class="kpi-master-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted fw-bold small">الجمعيات التعاونية</span>
                    <div class="kpi-icon-badge kpi-icon-emerald">
                        <i class="fa-solid fa-building-columns"></i>
                    </div>
                </div>
                <h3 class="stat-num text-dark mb-1">{{ number_format($kpis['associations_count']) }}</h3>
                <div class="text-muted small mb-3">
                    رأس المال: <strong class="text-success stat-num">{{ number_format($kpis['total_capital']) }}</strong> ر.ي
                </div>
                <div class="d-flex justify-content-between align-items-center small pt-2 border-top">
                    <span class="text-muted">نشطة: <strong class="text-success stat-num">{{ number_format($kpis['active_associations']) }}</strong></span>
                    <a href="{{ route('associations.index') }}" class="text-success text-decoration-none fw-bold">عرض السجل &larr;</a>
                </div>
            </div>
        </div>

        <!-- 2. المجاميع الإنتاجية -->
        <div class="col-md-6 col-xl-3">
            <div class="kpi-master-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted fw-bold small">المجاميع الإنتاجية</span>
                    <div class="kpi-icon-badge kpi-icon-emerald">
                        <i class="fa-solid fa-people-group"></i>
                    </div>
                </div>
                <h3 class="stat-num text-dark mb-1">{{ number_format($kpis['groups_count']) }}</h3>
                <div class="text-muted small mb-3">
                    المساحة: <strong class="text-dark stat-num">{{ number_format($kpis['group_area']) }}</strong> معاد / هكتار
                </div>
                <div class="d-flex justify-content-between align-items-center small pt-2 border-top">
                    <span class="text-muted">الأعضاء: <strong class="text-success stat-num">{{ number_format($kpis['group_members']) }}</strong></span>
                    <a href="{{ route('productive-groups.index') }}" class="text-success text-decoration-none fw-bold">عرض السجل &larr;</a>
                </div>
            </div>
        </div>

        <!-- 3. الكوادر وفرسان التنمية -->
        <div class="col-md-6 col-xl-3">
            <div class="kpi-master-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted fw-bold small">الكوادر والفرسان الميدانيين</span>
                    <div class="kpi-icon-badge kpi-icon-blue">
                        <i class="fa-solid fa-user-graduate"></i>
                    </div>
                </div>
                <h3 class="stat-num text-dark mb-1">{{ number_format($kpis['personnel_count']) }}</h3>
                <div class="text-muted small mb-3">
                    باحثين ومدربين: <strong class="text-primary stat-num">{{ number_format($kpis['researchers_count'] + $kpis['trainers_count']) }}</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center small pt-2 border-top">
                    <span class="text-muted">فرسان الجمعية: <strong class="text-primary stat-num">{{ number_format($kpis['knights_count']) }}</strong></span>
                    <a href="{{ route('personnel.index') }}" class="text-primary text-decoration-none fw-bold">عرض السجل &larr;</a>
                </div>
            </div>
        </div>

        <!-- 4. محفظة القروض البيضاء -->
        <div class="col-md-6 col-xl-3">
            <div class="kpi-master-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted fw-bold small">القروض البيضاء (0% فوائد)</span>
                    <div class="kpi-icon-badge kpi-icon-amber">
                        <i class="fa-solid fa-hand-holding-dollar"></i>
                    </div>
                </div>
                <h3 class="stat-num text-dark mb-1">
                    {{ number_format($kpis['loans_amount']) }} <span class="fs-6 fw-normal text-muted">ر.ي</span>
                </h3>
                <div class="text-muted small mb-3">
                    المستفيدين: <strong class="text-warning stat-num">{{ number_format($kpis['loans_count']) }}</strong> قرض تمكيني
                </div>
                <div class="d-flex justify-content-between align-items-center small pt-2 border-top">
                    <span class="text-muted">المسدد: <strong class="text-success stat-num">{{ number_format($kpis['loans_paid']) }}</strong></span>
                    <span class="text-muted">المتبقي: <strong class="text-danger stat-num">{{ number_format($kpis['loans_remaining']) }}</strong></span>
                </div>
            </div>
        </div>
    </div>

    <!-- لوحة التحليلات البيانية التفاعلية (Interactive Visual Analytics) -->
    <div class="row g-4 mb-4">
        <!-- مخطط 1: مؤشرات أداء وسداد محفظة القروض البيضاء -->
        <div class="col-lg-6">
            <div class="chart-container-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                            <i class="fa-solid fa-chart-pie text-success"></i>
                            <span>محفظة القروض البيضاء ونسب التحصيل</span>
                        </h6>
                        <span class="text-muted small" style="font-size: 0.75rem;">توزيع نسب المبالغ المسددة مقابل الأرصدة المتبقية</span>
                    </div>
                    <span class="badge bg-light text-dark border font-monospace">0% فوائد</span>
                </div>
                <div id="chartLoansDonut" style="min-height: 250px;"></div>
            </div>
        </div>

        <!-- مخطط 2: تصنيف القوى البشرية والفرسان والكوادر الميدانية -->
        <div class="col-lg-6">
            <div class="chart-container-card h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                            <i class="fa-solid fa-chart-column text-primary"></i>
                            <span>توزيع القوى البشرية وبنك الفرسان</span>
                        </h6>
                        <span class="text-muted small" style="font-size: 0.75rem;">إحصاءات الفرسان التنمويين والباحثين والمدربين المعتمدين</span>
                    </div>
                    <span class="badge bg-light text-primary border font-monospace">17 تخصصاً</span>
                </div>
                <div id="chartPersonnelBar" style="min-height: 250px;"></div>
            </div>
        </div>
    </div>

    <!-- المؤشرات القطاعية التفصيلية (قابلة للطي والتوسيع) -->
    <div id="detailedKPISection">
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
                                                <span class="badge bg-light text-dark font-monospace border copyable-badge" 
                                                      onclick="copyToClipboard('{{ $assoc->code }}', this)" 
                                                      title="انقر لنسخ الكود">
                                                    {{ $assoc->code }}
                                                </span>
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
                                                <span class="badge bg-light text-dark font-monospace border copyable-badge" 
                                                      onclick="copyToClipboard('{{ $grp->code }}', this)" 
                                                      title="انقر لنسخ الكود">
                                                    {{ $grp->code }}
                                                </span>
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

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. تبديل وضع العرض (مختصر / مفصل)
    const toggleBtn = document.getElementById('btnToggleDetailedView');
    const detailedSection = document.getElementById('detailedKPISection');
    const toggleText = document.getElementById('toggleViewText');

    if (toggleBtn && detailedSection) {
        toggleBtn.addEventListener('click', function() {
            if (detailedSection.style.display === 'none') {
                detailedSection.style.display = 'block';
                toggleText.textContent = 'العرض المختصر';
            } else {
                detailedSection.style.display = 'none';
                toggleText.textContent = 'العرض المفصل';
            }
        });
    }

    // 2. تهيئة مخطط القروض البيضاء (Donut Chart)
    const loansPaid = {{ (float)($kpis['loans_paid'] ?? 0) }};
    const loansRemaining = {{ (float)($kpis['loans_remaining'] ?? 0) }};
    const loansTotal = {{ (float)($kpis['loans_amount'] ?? 0) }};

    if (document.querySelector("#chartLoansDonut")) {
        const hasLoanData = (loansPaid > 0 || loansRemaining > 0);
        const donutOptions = {
            chart: {
                type: 'donut',
                height: 250,
                fontFamily: 'Cairo, sans-serif',
                toolbar: { show: false }
            },
            series: hasLoanData ? [loansPaid, loansRemaining] : [1, 0],
            labels: hasLoanData ? ['المسدد فعلياً', 'الرصيد المتبقي'] : ['لا توجد أرصدة قروض مسجلة', ''],
            colors: hasLoanData ? ['#065F46', '#D97706'] : ['#E2E8F0', '#CBD5E1'],
            legend: {
                position: 'bottom',
                fontSize: '12px',
                fontFamily: 'Cairo, sans-serif',
                markers: { radius: 12 }
            },
            dataLabels: {
                enabled: hasLoanData,
                formatter: function(val) {
                    return val.toFixed(1) + "%";
                }
            },
            stroke: {
                width: 2,
                colors: ['#ffffff']
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            name: {
                                show: true,
                                fontSize: '13px',
                                fontFamily: 'Cairo, sans-serif'
                            },
                            value: {
                                show: true,
                                fontSize: '16px',
                                fontFamily: 'IBM Plex Sans Arabic, sans-serif',
                                fontWeight: 700,
                                formatter: function(val) {
                                    return Number(val).toLocaleString() + ' ر.ي';
                                }
                            },
                            total: {
                                show: true,
                                label: 'إجمالي المحفظة',
                                fontSize: '12px',
                                color: '#64748B',
                                formatter: function() {
                                    return Number(loansTotal).toLocaleString() + ' ر.ي';
                                }
                            }
                        }
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return Number(val).toLocaleString() + ' ر.ي';
                    }
                }
            }
        };

        const chartLoans = new ApexCharts(document.querySelector("#chartLoansDonut"), donutOptions);
        chartLoans.render();
    }

    // 3. تهيئة مخطط الكوادر والفرسان (Bar Chart)
    const knightsCount = {{ (int)($kpis['knights_count'] ?? 0) }};
    const researchersCount = {{ (int)($kpis['researchers_count'] ?? 0) }};
    const trainersCount = {{ (int)($kpis['trainers_count'] ?? 0) }};

    if (document.querySelector("#chartPersonnelBar")) {
        const barOptions = {
            chart: {
                type: 'bar',
                height: 250,
                fontFamily: 'Cairo, sans-serif',
                toolbar: { show: false }
            },
            series: [{
                name: 'إجمالي الكوادر',
                data: [knightsCount, researchersCount, trainersCount]
            }],
            xaxis: {
                categories: ['فرسان التنمية', 'الباحثين الميدانيين', 'المدربين'],
                labels: {
                    style: {
                        fontFamily: 'Cairo, sans-serif',
                        fontSize: '12px',
                        colors: ['#065F46', '#2563EB', '#7C3AED']
                    }
                }
            },
            colors: ['#065F46', '#2563EB', '#7C3AED'],
            plotOptions: {
                bar: {
                    borderRadius: 6,
                    columnWidth: '45%',
                    distributed: true
                }
            },
            dataLabels: {
                enabled: true,
                style: {
                    fontFamily: 'IBM Plex Sans Arabic, sans-serif',
                    fontSize: '13px',
                    fontWeight: 700
                }
            },
            legend: { show: false },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return Number(val).toLocaleString() + ' كادر';
                    }
                }
            }
        };

        const chartPersonnel = new ApexCharts(document.querySelector("#chartPersonnelBar"), barOptions);
        chartPersonnel.render();
    }
});
</script>
@endpush
