@extends('layouts.app')

@section('title', 'مستكشف النطاق الجغرافي الشامل (Geo-Explorer) - نظام حصاد')

@section('content')
<div class="container-fluid p-0">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                <span class="text-success fs-5"><i class="fa-solid fa-earth-asia"></i></span>
                مستكشف النطاق الجغرافي الشامل (360° Geo-Explorer)
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">الرئيسية</a></li>
                    <li class="breadcrumb-item active">المستكشف الجغرافي الموحد (34 قسماً)</li>
                </ol>
            </nav>
        </div>
        <div>
            <span class="badge bg-white text-success border border-success border-opacity-25 p-2 rounded-pill shadow-sm small">
                <i class="fa-solid fa-cubes-stacked me-1"></i> تجميع لحظي لكافة أنشطة النطاق الجغرافي
            </span>
        </div>
    </div>

    <!-- شريط اختيار النطاق الجغرافي المستكشف -->
    <div class="filter-card">
        <form method="GET" action="{{ route('geo-explorer.index') }}">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-10">
                    <x-location-hierarchy-selector 
                        :governorateId="$selectedGovId"
                        :directorateId="$selectedDirId"
                        :uzlahId="$selectedUzlId"
                        :showAddButton="false"
                    />
                </div>
                <div class="col-12 col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-hasad-primary w-100">
                        <i class="fa-solid fa-binoculars me-1"></i> استكشاف
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- بطاقة عنوان النطاق المختار -->
    <div class="alert alert-light border rounded-3 p-3 mb-4 d-flex justify-content-between align-items-center bg-white shadow-sm">
        <div>
            <span class="text-muted small d-block">الملف الإحصائي والتشغيلي الموحد للنطاق:</span>
            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="fa-solid fa-location-dot text-danger"></i>
                <span>{{ $currentGov?->name_ar ?? 'الجمهورية اليمنية' }}</span>
                @if($currentDir) <span class="text-muted">/</span> <span>مديرية {{ $currentDir->name_ar }}</span> @endif
                @if($currentUzl) <span class="text-muted">/</span> <span>عزلة {{ $currentUzl->name_ar }}</span> @endif
            </h5>
        </div>
        <span class="badge bg-light text-dark font-monospace border p-2">
            كود النطاق: {{ $currentUzl?->code ?? $currentDir?->code ?? $currentGov?->code ?? 'ALL' }}
        </span>
    </div>

    <!-- بطاقات الإحصائيات المجمعة الست -->
    <div class="row g-3 mb-4 text-center">
        <div class="col-sm-6 col-xl-2">
            <div class="kpi-card kpi-emerald h-100 p-3">
                <span class="text-muted small fw-bold d-block mb-1">الجمعيات التعاونية</span>
                <h4 class="stat-num text-dark mb-0">{{ number_format($stats['associations_count']) }}</h4>
            </div>
        </div>
        <div class="col-sm-6 col-xl-2">
            <div class="kpi-card kpi-emerald h-100 p-3">
                <span class="text-muted small fw-bold d-block mb-1">المجاميع الإنتاجية</span>
                <h4 class="stat-num text-success mb-0">{{ number_format($stats['productive_groups_count']) }}</h4>
            </div>
        </div>
        <div class="col-sm-6 col-xl-2">
            <div class="kpi-card kpi-blue h-100 p-3">
                <span class="text-muted small fw-bold d-block mb-1">الكوادر والفرسان</span>
                <h4 class="stat-num text-primary mb-0">{{ number_format($stats['personnel_count']) }}</h4>
            </div>
        </div>
        <div class="col-sm-6 col-xl-2">
            <div class="kpi-card kpi-amber h-100 p-3">
                <span class="text-muted small fw-bold d-block mb-1">المشاريع والمبادرات</span>
                <h4 class="stat-num text-dark mb-0">{{ number_format($stats['projects_count'] + $stats['initiatives_count']) }}</h4>
            </div>
        </div>
        <div class="col-sm-6 col-xl-2">
            <div class="kpi-card kpi-purple h-100 p-3">
                <span class="text-muted small fw-bold d-block mb-1">المزارعون المسجلون</span>
                <h4 class="stat-num text-dark mb-0">{{ number_format($stats['farmers_count']) }}</h4>
            </div>
        </div>
        <div class="col-sm-6 col-xl-2">
            <div class="kpi-card kpi-amber h-100 p-3">
                <span class="text-muted small fw-bold d-block mb-1">القروض البيضاء</span>
                <h4 class="stat-num text-danger mb-0">{{ number_format($stats['loans_count']) }}</h4>
            </div>
        </div>
    </div>

    <!-- تبويبات استعراض السجلات الميدانية في هذا النطاق -->
    <div class="card-hasad">
        <div class="card-header bg-white p-2 border-bottom">
            <ul class="nav nav-pills nav-fill flex-column flex-md-row gap-1" id="geoExplorerTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active fw-bold py-2 small" data-bs-toggle="tab" data-bs-target="#geo-associations" type="button">
                        <i class="fa-solid fa-building-columns me-1"></i> الجمعيات ({{ $associations->count() }})
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold py-2 small" data-bs-toggle="tab" data-bs-target="#geo-groups" type="button">
                        <i class="fa-solid fa-people-roof me-1"></i> المجاميع الإنتاجية ({{ $productiveGroups->count() }})
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold py-2 small" data-bs-toggle="tab" data-bs-target="#geo-personnel" type="button">
                        <i class="fa-solid fa-graduation-cap me-1"></i> الكوادر والفرسان ({{ $personnel->count() }})
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold py-2 small" data-bs-toggle="tab" data-bs-target="#geo-projects" type="button">
                        <i class="fa-solid fa-list-check me-1"></i> المشاريع ({{ $projects->count() }})
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold py-2 small" data-bs-toggle="tab" data-bs-target="#geo-crops" type="button">
                        <i class="fa-solid fa-wheat-awn me-1"></i> المحاصيل ({{ $crops->count() }})
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold py-2 small" data-bs-toggle="tab" data-bs-target="#geo-farmers" type="button">
                        <i class="fa-solid fa-person-digging me-1"></i> المزارعون ({{ $farmers->count() }})
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold py-2 small" data-bs-toggle="tab" data-bs-target="#geo-loans" type="button">
                        <i class="fa-solid fa-hand-holding-dollar me-1"></i> القروض البيضاء ({{ $loans->count() }})
                    </button>
                </li>
            </ul>
        </div>
        <div class="card-body p-4">
            <div class="tab-content" id="geoExplorerTabsContent">

                <!-- 1. الجمعيات -->
                <div class="tab-pane fade show active" id="geo-associations">
                    @if($associations->isEmpty())
                        <div class="text-center py-4 text-muted small">لا توجد جمعيات مسجلة في هذا النطاق بعد.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hasad align-middle mb-0 text-nowrap">
                                <thead>
                                    <tr>
                                        <th>الكود</th>
                                        <th>اسم الجمعية</th>
                                        <th>النوع</th>
                                        <th>العزلة</th>
                                        <th>الرئيس</th>
                                        <th>الأعضاء</th>
                                        <th>الحالة</th>
                                        <th>ملف الجمعية</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($associations as $a)
                                        <tr>
                                            <td><span class="badge bg-light text-dark font-monospace border">{{ $a->code }}</span></td>
                                            <td class="fw-bold">{{ $a->name }}</td>
                                            <td><span class="badge bg-light text-dark border">{{ $a->type }}</span></td>
                                            <td>{{ $a->uzlah?->name_ar }}</td>
                                            <td>{{ $a->president_name ?: '-' }}</td>
                                            <td><span class="stat-num">{{ number_format($a->total_members) }}</span></td>
                                            <td>
                                                <span class="badge-status badge-status-success">
                                                    <span class="badge-status-dot"></span> {{ $a->status }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('associations.show', $a->id) }}" class="btn btn-sm btn-hasad-outline py-1 px-2" style="font-size: 0.8rem;">
                                                    عرض الملف الشامل
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <!-- 2. المجاميع الإنتاجية -->
                <div class="tab-pane fade" id="geo-groups">
                    @if($productiveGroups->isEmpty())
                        <div class="text-center py-4 text-muted small">لا توجد مجاميع إنتاجية مسجلة في هذا النطاق.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hasad align-middle mb-0 text-nowrap">
                                <thead>
                                    <tr>
                                        <th>الكود</th>
                                        <th>اسم المجموعة</th>
                                        <th>النوع</th>
                                        <th>المنتج</th>
                                        <th>الأعضاء</th>
                                        <th>الجمعية</th>
                                        <th>الحالة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($productiveGroups as $grp)
                                        <tr>
                                            <td><span class="badge bg-light text-dark font-monospace border">{{ $grp->code }}</span></td>
                                            <td class="fw-bold">{{ $grp->name }}</td>
                                            <td><span class="badge bg-light text-dark border">{{ $grp->group_type }}</span></td>
                                            <td>{{ $grp->main_product ?: '-' }}</td>
                                            <td><span class="stat-num">{{ $grp->total_members }}</span></td>
                                            <td>{{ $grp->association?->name ?: '-' }}</td>
                                            <td>
                                                <span class="badge-status badge-status-success">
                                                    <span class="badge-status-dot"></span> {{ $grp->status }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <!-- 3. الكوادر والفرسان -->
                <div class="tab-pane fade" id="geo-personnel">
                    @if($personnel->isEmpty())
                        <div class="text-center py-4 text-muted small">لا يوجد كوادر مسجلون في هذا النطاق.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hasad align-middle mb-0 text-nowrap">
                                <thead>
                                    <tr>
                                        <th>الكود</th>
                                        <th>الاسم الرباعي</th>
                                        <th>الفئة</th>
                                        <th>التخصص</th>
                                        <th>الهاتف</th>
                                        <th>التفعيل الميداني</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($personnel as $p)
                                        <tr>
                                            <td><span class="badge bg-light text-dark font-monospace border">{{ $p->code }}</span></td>
                                            <td class="fw-bold">{{ $p->full_name }}</td>
                                            <td><span class="badge-status badge-status-info">{{ $p->category }}</span></td>
                                            <td>{{ $p->sub_specialty ?: $p->specialization ?: '-' }}</td>
                                            <td class="font-monospace">{{ $p->phone ?: '-' }}</td>
                                            <td>
                                                @if($p->is_activated_in_field)
                                                    <span class="badge-status badge-status-success">
                                                        <span class="badge-status-dot"></span> مفعل
                                                    </span>
                                                @else
                                                    <span class="badge-status badge-status-warning">
                                                        <span class="badge-status-dot"></span> غير مفعل
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

                <!-- 4. المشاريع -->
                <div class="tab-pane fade" id="geo-projects">
                    @if($projects->isEmpty())
                        <div class="text-center py-4 text-muted small">لا توجد مشاريع مسجلة في هذا النطاق.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hasad align-middle mb-0 text-nowrap">
                                <thead>
                                    <tr>
                                        <th>اسم المشروع</th>
                                        <th>النوع</th>
                                        <th>الحالة</th>
                                        <th>التكلفة (ر.ي)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($projects as $pr)
                                        <tr>
                                            <td class="fw-bold">{{ $pr->project_name }}</td>
                                            <td>{{ $pr->project_type ?: '-' }}</td>
                                            <td>
                                                <span class="badge-status badge-status-success">
                                                    <span class="badge-status-dot"></span> {{ $pr->execution_status }}
                                                </span>
                                            </td>
                                            <td class="stat-num text-success fw-bold">{{ number_format($pr->cost) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <!-- 5. المحاصيل -->
                <div class="tab-pane fade" id="geo-crops">
                    @if($crops->isEmpty())
                        <div class="text-center py-4 text-muted small">لا توجد محاصيل مسجلة في هذا النطاق.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hasad align-middle mb-0 text-nowrap">
                                <thead>
                                    <tr>
                                        <th>نوع المحصول</th>
                                        <th>الصنف</th>
                                        <th>الكمية</th>
                                        <th>الوفرة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($crops as $cr)
                                        <tr>
                                            <td class="fw-bold">{{ $cr->crop_type }}</td>
                                            <td>{{ $cr->crop_variety ?: '-' }}</td>
                                            <td class="stat-num">{{ number_format($cr->production_quantity) }} {{ $cr->quantity_unit }}</td>
                                            <td>
                                                <span class="badge-status badge-status-success">
                                                    <span class="badge-status-dot"></span> {{ $cr->availability_status }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <!-- 6. المزارعون -->
                <div class="tab-pane fade" id="geo-farmers">
                    @if($farmers->isEmpty())
                        <div class="text-center py-4 text-muted small">لا يوجد مزارعون مسجلون في هذا النطاق.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hasad align-middle mb-0 text-nowrap">
                                <thead>
                                    <tr>
                                        <th>اسم المزارع</th>
                                        <th>الهاتف</th>
                                        <th>المساحة المزروعة</th>
                                        <th>المحصول الرئيسي</th>
                                        <th>مصدر الري</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($farmers as $fm)
                                        <tr>
                                            <td class="fw-bold">{{ $fm->farmer_name }}</td>
                                            <td class="font-monospace">{{ $fm->phone ?: '-' }}</td>
                                            <td class="stat-num">{{ $fm->cultivated_area }} {{ $fm->area_unit }}</td>
                                            <td>{{ $fm->main_crop_type ?: '-' }}</td>
                                            <td><span class="badge-status badge-status-info">{{ $fm->irrigation_source }}</span></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <!-- 7. القروض البيضاء -->
                <div class="tab-pane fade" id="geo-loans">
                    @if($loans->isEmpty())
                        <div class="text-center py-4 text-muted small">لا توجد قروض بيضاء مسجلة في هذا النطاق.</div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hasad align-middle mb-0 text-nowrap">
                                <thead>
                                    <tr>
                                        <th>رقم القرض</th>
                                        <th>المستفيد</th>
                                        <th>المبلغ</th>
                                        <th>المسدد</th>
                                        <th>الرصيد المتبقي</th>
                                        <th>الحالة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($loans as $ln)
                                        <tr>
                                            <td><span class="badge bg-light text-dark font-monospace border">{{ $ln->code }}</span></td>
                                            <td class="fw-bold">{{ $ln->beneficiary_name }}</td>
                                            <td class="stat-num">{{ number_format($ln->loan_amount) }}</td>
                                            <td class="stat-num text-success">{{ number_format($ln->paid_amount) }}</td>
                                            <td class="stat-num text-danger fw-bold">{{ number_format($ln->remaining_balance) }}</td>
                                            <td>
                                                <span class="badge-status badge-status-warning">
                                                    <span class="badge-status-dot"></span> {{ $ln->loan_status }}
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
