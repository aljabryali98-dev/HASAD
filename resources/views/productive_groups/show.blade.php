@extends('layouts.app')

@section('title', "ملف المجموعة الإنتاجية: {$group->name} - نظام حصاد")

@section('content')
<div class="container-fluid p-0">

    <!-- شريط الرأس للمجموعة الإنتاجية -->
    <div class="card border-0 shadow-sm rounded-3 mb-4 bg-white">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 me-3 d-flex align-items-center justify-content-center" style="width: 64px; height: 64px;">
                        <i class="fa-solid fa-people-roof fs-2"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h4 class="fw-bold mb-0 text-dark">{{ $group->name }}</h4>
                            <span class="badge bg-dark font-monospace">{{ $group->code }}</span>
                            <span class="badge {{ $group->status === 'نشطة' ? 'bg-success' : 'bg-secondary' }}">{{ $group->status }}</span>
                        </div>
                        <div class="small text-muted mt-1">
                            <i class="fa-solid fa-location-dot text-danger me-1"></i>
                            {{ $group->governorate?->name_ar }} - {{ $group->directorate?->name_ar }} - {{ $group->uzlah?->name_ar }}
                            | نوع المجموعة: <span class="badge bg-light text-dark border">{{ $group->group_type }}</span>
                            @if($group->association)
                                | تابعة لـ: <a href="{{ route('associations.show', $group->association_id) }}" class="fw-bold text-primary text-decoration-none">{{ $group->association->name }}</a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('productive-groups.index') }}" class="btn btn-outline-secondary">
                        <i class="fa-solid fa-arrow-right me-1"></i> العودة للمجاميع
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2 fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- التبويبات الخمسة الشاملة للمجموعة الإنتاجية -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white p-2 border-bottom">
            <ul class="nav nav-pills nav-fill flex-column flex-md-row gap-1" id="groupTabs" role="tablist">
                <li class="nav-item">
                    <button class="nav-link active fw-bold py-2 small" id="tab-identity-tab" data-bs-toggle="tab" data-bs-target="#tab-identity" type="button">
                        <i class="fa-solid fa-id-card me-1"></i> 1. البيانات التعريفية والتنظيمية
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold py-2 small" id="tab-facilitator-tab" data-bs-toggle="tab" data-bs-target="#tab-facilitator" type="button">
                        <i class="fa-solid fa-user-tie me-1"></i> 2. بيانات الميسّر المسؤول
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold py-2 small" id="tab-members-tab" data-bs-toggle="tab" data-bs-target="#tab-members" type="button">
                        <i class="fa-solid fa-users me-1"></i> 3. قيادة المجموعة وأعضاؤها
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold py-2 small" id="tab-production-tab" data-bs-toggle="tab" data-bs-target="#tab-production" type="button">
                        <i class="fa-solid fa-wheat-awn me-1"></i> 4. النشاط والإنتاج والتسويق
                    </button>
                </li>
                <li class="nav-item">
                    <button class="nav-link fw-bold py-2 small" id="tab-monitoring-tab" data-bs-toggle="tab" data-bs-target="#tab-monitoring" type="button">
                        <i class="fa-solid fa-clipboard-check me-1"></i> 5. المتابعة والتيسير الميداني
                    </button>
                </li>
            </ul>
        </div>

        <div class="card-body p-4">
            <div class="tab-content" id="groupTabsContent">

                <!-- 1. البيانات التعريفية والتنظيمية -->
                <div class="tab-pane fade show active" id="tab-identity">
                    <h6 class="fw-bold text-success mb-3"><i class="fa-solid fa-id-card me-2"></i> البيانات التعريفية والتنظيمية للمجموعة</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">الرقم التعريفي للمجموعة:</span>
                                <strong class="fs-6 font-monospace text-success">{{ $group->code }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">اسم المجموعة:</span>
                                <strong class="fs-6 text-dark">{{ $group->name }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">الجمعية التعاونية التابعة:</span>
                                @if($group->association)
                                    <strong class="text-primary">{{ $group->association->name }}</strong>
                                @else
                                    <span class="text-muted">مستقلة / غير مرتبطة بجمعية</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">نوع المجموعة:</span>
                                <strong>{{ $group->group_type }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">تاريخ التأسيس:</span>
                                <strong>{{ $group->founded_date?->format('Y-m-d') ?: '-' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">رقم محضر التأسيس:</span>
                                <strong>{{ $group->founding_minutes_number ?: '-' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">حالة المجموعة:</span>
                                <strong class="badge bg-success">{{ $group->status }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">المحافظة:</span>
                                <strong>{{ $group->governorate?->name_ar }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">المديرية:</span>
                                <strong>{{ $group->directorate?->name_ar }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">العزلة:</span>
                                <strong>{{ $group->uzlah?->name_ar }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">القرية / المحلة:</span>
                                <strong>{{ $group->subLocation?->name_ar ?: '-' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. بيانات الميسّر المسؤول -->
                <div class="tab-pane fade" id="tab-facilitator">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-primary mb-0"><i class="fa-solid fa-user-tie me-2"></i> بيانات الميسّر الميداني المسؤول</h6>
                        <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#modalAttachFacilitator">
                            <i class="fa-solid fa-user-plus me-1"></i> تغيير / تعيين ميسّر
                        </button>
                    </div>

                    @if($group->facilitators->isEmpty())
                        <div class="p-4 text-center bg-light rounded-3 text-muted">
                            <i class="fa-solid fa-user-slash fs-2 mb-2 d-block"></i>
                            لم يتم تعيين ميسّر ميداني مسؤول لهذه المجموعة بعد. اضغط على الزر أعلاه لربط ميسّر معتمد.
                        </div>
                    @else
                        @foreach($group->facilitators as $fac)
                            <div class="card border mb-3">
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <span class="text-muted small d-block">اسم الميسّر الرباعي:</span>
                                            <h5 class="fw-bold text-dark mb-0">{{ $fac->full_name }}</h5>
                                            <span class="badge bg-secondary font-monospace mt-1">{{ $fac->code }}</span>
                                        </div>
                                        <div class="col-md-4">
                                            <span class="text-muted small d-block">رقم الهاتف:</span>
                                            <strong class="font-monospace">{{ $fac->phone ?: '-' }}</strong>
                                        </div>
                                        <div class="col-md-4">
                                            <span class="text-muted small d-block">المؤهل والتخصص:</span>
                                            <strong>{{ $fac->education_level ?: '-' }} {{ $fac->specialization ? "({$fac->specialization})" : '' }}</strong>
                                        </div>
                                        <div class="col-md-4">
                                            <span class="text-muted small d-block">نوع التكليف:</span>
                                            <span class="badge bg-light text-dark border">{{ $fac->assignment_type }}</span>
                                        </div>
                                        <div class="col-md-4">
                                            <span class="text-muted small d-block">جهة التكليف:</span>
                                            <strong>{{ $fac->assigning_entity ?: 'اللجنة الزراعية والسمكية العليا' }}</strong>
                                        </div>
                                        <div class="col-md-4">
                                            <span class="text-muted small d-block">إجمالي المجموعات التي ييسّرها (تلقائي):</span>
                                            <span class="badge bg-success fs-6">{{ $fac->productiveGroups->count() }} مجاميع إنتاجية</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <!-- 3. قيادة المجموعة وأعضاؤها -->
                <div class="tab-pane fade" id="tab-members">
                    <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-users me-2"></i> قيادة المجموعة وأعضاؤها</h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">اسم مسؤول المجموعة:</span>
                                <h5 class="fw-bold text-dark mb-0">{{ $group->leader_name ?: 'غير محدد' }}</h5>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">رقم هاتف مسؤول المجموعة:</span>
                                <h5 class="fw-bold text-dark mb-0 font-monospace">{{ $group->leader_phone ?: '-' }}</h5>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3 text-center">
                                <span class="text-muted small d-block">إجمالي الأعضاء</span>
                                <h3 class="fw-bold text-primary mb-0">{{ $group->total_members }}</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3 text-center">
                                <span class="text-muted small d-block">الأعضاء الذكور</span>
                                <h3 class="fw-bold text-dark mb-0">{{ $group->male_members }}</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3 text-center">
                                <span class="text-muted small d-block">العضوات الإناث</span>
                                <h3 class="fw-bold text-dark mb-0">{{ $group->female_members }}</h3>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3 text-center">
                                <span class="text-muted small d-block">فئة الشباب</span>
                                <h3 class="fw-bold text-success mb-0">{{ $group->youth_members }}</h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 4. النشاط والإنتاج والتسويق -->
                <div class="tab-pane fade" id="tab-production">
                    <h6 class="fw-bold text-warning mb-3"><i class="fa-solid fa-wheat-awn me-2"></i> بيانات النشاط والإنتاج والتسويق</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">المنتج الرئيسي:</span>
                                <strong class="fs-6 text-dark">{{ $group->main_product ?: '-' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">المساحة الإنتاجية:</span>
                                <strong class="fs-6 text-dark">{{ $group->productive_area }} {{ $group->area_unit }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">حجم الإنتاج الدوري:</span>
                                <strong class="fs-6 text-dark">{{ $group->production_volume }} {{ $group->measurement_unit }}</strong>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">قيمة الإنتاج التقديرية (ر.ي):</span>
                                <h5 class="fw-bold text-success mb-0">{{ number_format($group->production_value) }} ر.ي</h5>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">قيمة المبيعات المحققة (ر.ي):</span>
                                <h5 class="fw-bold text-primary mb-0">{{ number_format($group->sales_value) }} ر.ي</h5>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">طريقة التسويق المعتمدة:</span>
                                <strong class="badge bg-light text-dark border fs-6">{{ $group->marketing_method }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 5. المتابعة والتيسير الميداني -->
                <div class="tab-pane fade" id="tab-monitoring">
                    <h6 class="fw-bold text-info mb-3"><i class="fa-solid fa-clipboard-check me-2"></i> مؤشرات المتابعة والتيسير الميداني</h6>
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">عدد الزيارات الميدانية:</span>
                                <strong class="fs-5 text-dark">{{ $group->field_visits_count }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">تاريخ آخر زيارة ميدانية:</span>
                                <strong>{{ $group->last_visit_date?->format('Y-m-d') ?: '-' }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">عدد الاجتماعات المنعقدة:</span>
                                <strong class="fs-5 text-dark">{{ $group->meetings_count }}</strong>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 bg-light rounded-3">
                                <span class="text-muted small d-block">مستوى التفاعل والأداء:</span>
                                <strong class="badge bg-success fs-6">{{ $group->performance_level }}</strong>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3">
                                <span class="fw-bold text-dark d-block mb-1">أبرز الإنجازات:</span>
                                <p class="mb-0 text-muted small">{{ $group->key_achievements ?: 'لا توجد إنجازات مسجلة بعد.' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 bg-light rounded-3">
                                <span class="fw-bold text-dark d-block mb-1">الاحتياجات والتحديات:</span>
                                <p class="mb-0 text-muted small">{{ $group->needs ?: 'لا توجد احتياجات مسجلة.' }}</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- مودال ربط ميسّر بالمجموعة -->
<div class="modal fade" id="modalAttachFacilitator" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0 rounded-3">
            <div class="modal-header bg-light py-3">
                <h6 class="modal-title fw-bold text-dark">
                    <i class="fa-solid fa-user-plus text-primary me-2"></i> تعيين ميسّر ميداني للمجموعة
                </h6>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <form method="POST" action="{{ route('productive-groups.attach_facilitator', $group->id) }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">اختر الميسّر الميداني المعتمد <span class="text-danger">*</span></label>
                        <select name="facilitator_id" class="form-select" required>
                            <option value="">-- اختر من قائمة الميسرين --</option>
                            @foreach($facilitators as $fac)
                                <option value="{{ $fac->id }}">{{ $fac->full_name }} - {{ $fac->phone }} ({{ $fac->specialization }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-check me-1"></i> تعيين الميسّر
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
