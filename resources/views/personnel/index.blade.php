@extends('layouts.app')

@section('title', 'سجل الكوادر والفرسان والتدريب - نظام حصاد')

@php
    $categories = $categories ?? \App\Models\UnifiedPersonnel::CATEGORIES;
    $knightsTypes = $knightsTypes ?? \App\Models\UnifiedPersonnel::KNIGHTS_TYPES;
@endphp

@section('content')
<div class="container-fluid p-0">

    <!-- شريط العنوان والمسار والإجراءات -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                <span class="text-success fs-5"><i class="fa-solid fa-user-graduate"></i></span>
                سجل الكوادر والفرسان والتدريب الموحد
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">الرئيسية</a></li>
                    <li class="breadcrumb-item active" aria-current="page">قاعدة الكوادر والفرسان والمتدربين</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-hasad-primary d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddPersonnel">
                <i class="fa-solid fa-user-plus"></i>
                <span>تسجيل كادر / فارس جديد</span>
            </button>
            <a href="{{ route('associations.index') }}" class="btn btn-hasad-outline d-flex align-items-center gap-1">
                <i class="fa-solid fa-building-columns"></i>
                <span>الجمعيات</span>
            </a>
        </div>
    </div>

    <!-- رسائل التنبيه والنجاح -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4 rounded-3 border-0 shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2 fs-5 text-success"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- بطاقات الإحصائيات -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl">
            <div class="kpi-card kpi-emerald h-100">
                <span class="text-muted small fw-bold d-block mb-1">إجمالي الكوادر والفرسان</span>
                <h4 class="stat-num text-dark mb-0">{{ number_format($stats['total_all']) }}</h4>
            </div>
        </div>
        <div class="col-sm-6 col-xl">
            <div class="kpi-card kpi-emerald h-100">
                <span class="text-muted small fw-bold d-block mb-1">فرسان الجمعية</span>
                <h4 class="stat-num text-success mb-0">{{ number_format($stats['knights_count']) }}</h4>
            </div>
        </div>
        <div class="col-sm-6 col-xl">
            <div class="kpi-card kpi-blue h-100">
                <span class="text-muted small fw-bold d-block mb-1">الباحثين الميدانيين</span>
                <h4 class="stat-num text-primary mb-0">{{ number_format($stats['researchers_count']) }}</h4>
            </div>
        </div>
        <div class="col-sm-6 col-xl">
            <div class="kpi-card kpi-amber h-100">
                <span class="text-muted small fw-bold d-block mb-1">المدربون والمرشدون</span>
                <h4 class="stat-num text-dark mb-0">{{ number_format($stats['trainers_count']) }}</h4>
            </div>
        </div>
        <div class="col-sm-6 col-xl">
            <div class="kpi-card kpi-purple h-100">
                <span class="text-muted small fw-bold d-block mb-1">المفعلون بالميدان</span>
                <h4 class="stat-num text-success mb-0">{{ number_format($stats['activated_count']) }}</h4>
            </div>
        </div>
    </div>

    <!-- شريط التبويبات السريعة للفئات (Pills) -->
    <div class="card-hasad p-3 mb-4">
        <div class="nav-pills-hasad">
            @foreach($categories as $catKey => $catLabel)
                <a href="{{ route('personnel.index', ['category' => $catKey]) }}" 
                   class="pill-item {{ $category === $catKey ? 'active' : '' }}">
                    {{ $catLabel }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- بطاقة الفلترة والبحث -->
    <div class="filter-card">
        <div class="d-flex align-items-center gap-2 mb-3">
            <i class="fa-solid fa-filter text-success"></i>
            <h6 class="fw-bold mb-0 text-dark">تصفية وبحث في سجل الكوادر والفرسان</h6>
        </div>
        <form method="GET" action="{{ route('personnel.index') }}">
            <input type="hidden" name="category" value="{{ $category }}">
            <div class="row g-3">
                @if($category === 'فرسان الجمعية')
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-bold">التخصص الفرعي للفارس</label>
                        <select name="sub_specialty" class="form-select">
                            <option value="">-- جميع التخصصات (17 نوع) --</option>
                            @foreach($knightsTypes as $kType)
                                <option value="{{ $kType }}" {{ $subSpecialty === $kType ? 'selected' : '' }}>{{ $kType }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-12 col-md-3">
                    <label class="form-label small fw-bold">الجمعية المرتبطة</label>
                    <select name="association_id" class="form-select">
                        <option value="">-- جميع الجمعيات --</option>
                        @foreach($associations as $ass)
                            <option value="{{ $ass->id }}" {{ request('association_id') == $ass->id ? 'selected' : '' }}>
                                {{ $ass->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12 col-md-2">
                    <label class="form-label small fw-bold">التفعيل الميداني</label>
                    <select name="activated" class="form-select">
                        <option value="">-- الكل --</option>
                        <option value="1" {{ request('activated') == '1' ? 'selected' : '' }}>مفعل بالميدان</option>
                        <option value="0" {{ request('activated') == '0' ? 'selected' : '' }}>غير مفعل</option>
                    </select>
                </div>

                <div class="col-12 col-md-3">
                    <label class="form-label small fw-bold">بحث بالاسم أو الهاتف</label>
                    <input type="text" name="search" class="form-control" placeholder="اسم الكادر، الهاتف، التخصص..." value="{{ request('search') }}">
                </div>

                <div class="col-12 col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-hasad-primary w-100">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- جدول الكوادر والفرسان -->
    <div class="card-hasad">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="fa-solid fa-list text-success"></i>
                <span>القائمة: {{ $category }} ({{ $personnel->total() }})</span>
            </h6>
            <span class="badge bg-light text-dark border font-monospace">
                صفحة {{ $personnel->currentPage() }} من {{ $personnel->lastPage() ?: 1 }}
            </span>
        </div>
        <div class="card-body p-0">
            @if($personnel->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-user-group fs-1 mb-3 d-block text-muted"></i>
                    <h5 class="fw-bold text-dark">لا توجد سجلات مسجلة تحت فئة ({{ $category }}) حالياً</h5>
                    <button type="button" class="btn btn-hasad-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#modalAddPersonnel">
                        <i class="fa-solid fa-user-plus me-1"></i> تسجيل كادر الآن
                    </button>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hasad align-middle mb-0 text-nowrap">
                        <thead>
                            <tr>
                                <th style="width: 120px;">الكود</th>
                                <th>الاسم الرباعي</th>
                                <th>الفئة / التخصص</th>
                                <th>الموقع (المديرية / العزلة)</th>
                                <th>الجمعية المرتبطة</th>
                                <th>العمر / فصيلة الدم</th>
                                <th>المؤهل والتخصص</th>
                                <th>رقم الهاتف</th>
                                <th>الميدان</th>
                                <th class="text-center" style="width: 80px;">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($personnel as $p)
                                <tr>
                                    <td><span class="badge bg-light text-dark font-monospace border">{{ $p->code }}</span></td>
                                    <td class="fw-bold text-dark">{{ $p->full_name }}</td>
                                    <td>
                                        <span class="badge-status badge-status-info">
                                            {{ $p->sub_specialty ?: $p->category }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $p->governorate?->name_ar }} - {{ $p->directorate?->name_ar }}</div>
                                        <span class="small text-muted">{{ $p->uzlah?->name_ar }}</span>
                                    </td>
                                    <td>
                                        @if($p->association)
                                            <a href="{{ route('associations.show', $p->association_id) }}" class="small text-success text-decoration-none fw-bold">
                                                {{ $p->association->name }}
                                            </a>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($p->age)
                                            <span class="fw-bold text-dark stat-num">{{ $p->age }} سنة</span>
                                        @endif
                                        @if($p->blood_type)
                                            <span class="badge bg-danger ms-1">{{ $p->blood_type }}</span>
                                        @endif
                                    </td>
                                    <td>{{ $p->education_level ?: '-' }} {{ $p->specialization ? "({$p->specialization})" : '' }}</td>
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
                                    <td class="text-center">
                                        <form method="POST" action="{{ route('personnel.destroy', $p->id) }}" class="d-inline" onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action-icon btn-action-danger" title="حذف">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($personnel->hasPages())
                    <div class="card-footer bg-white py-3 border-0">
                        {{ $personnel->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

<!-- مودال إضافة كادر / فارس جديد -->
<div class="modal fade" id="modalAddPersonnel" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
            <div class="modal-header text-white py-3" style="background: linear-gradient(135deg, var(--hasad-primary) 0%, var(--hasad-primary-hover) 100%);">
                <h6 class="modal-title fw-bold">
                    <i class="fa-solid fa-user-plus me-2"></i> تسجيل كادر أو فارس جديد
                </h6>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <form method="POST" action="{{ route('personnel.store') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">الفئة الرئيسية <span class="text-danger">*</span></label>
                            <select name="category" class="form-select" id="selectCategory" required>
                                @foreach($categories as $cKey => $cLabel)
                                    <option value="{{ $cKey }}" {{ $category === $cKey ? 'selected' : '' }}>{{ $cKey }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">التخصص الفرعي لفرسان الجمعية (إن انطبق)</label>
                            <select name="sub_specialty" class="form-select">
                                <option value="">-- اختياري لفرسان الجمعية --</option>
                                @foreach($knightsTypes as $kType)
                                    <option value="{{ $kType }}">{{ $kType }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">الاسم الرباعي الكامل <span class="text-danger">*</span></label>
                            <input type="text" name="full_name" class="form-control" placeholder="الاسم الرباعي" required>
                        </div>

                        <div class="col-12 col-md-3">
                            <label class="form-label small fw-bold">الجنس <span class="text-danger">*</span></label>
                            <select name="gender" class="form-select" required>
                                <option value="ذكر">ذكر</option>
                                <option value="أنثى">أنثى</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-3">
                            <label class="form-label small fw-bold">فصيلة الدم</label>
                            <select name="blood_type" class="form-select">
                                <option value="">-- اختياري --</option>
                                <option value="O+">O+</option>
                                <option value="O-">O-</option>
                                <option value="A+">A+</option>
                                <option value="A-">A-</option>
                                <option value="B+">B+</option>
                                <option value="B-">B-</option>
                                <option value="AB+">AB+</option>
                                <option value="AB-">AB-</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold">تاريخ الميلاد (لحساب العمر تلقائياً)</label>
                            <input type="date" name="birth_date" class="form-control">
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold">رقم الهاتف <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control" placeholder="770000000" required>
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold">الجمعية المرتبطة (اختياري)</label>
                            <select name="association_id" class="form-select">
                                <option value="">-- اختياري لربط الفارس بالجمعية --</option>
                                @foreach($associations as $ass)
                                    <option value="{{ $ass->id }}">{{ $ass->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold">النطاق الجغرافي <span class="text-danger">*</span></label>
                            <x-location-hierarchy-selector :required="true" :showAddButton="false" />
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold">المؤهل</label>
                            <input type="text" name="education_level" class="form-control" placeholder="بكالوريوس، دبلوم، ثانوية...">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold">التخصص</label>
                            <input type="text" name="specialization" class="form-control" placeholder="زراعة، بيطرة، إدارة...">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold">جهة العمل</label>
                            <input type="text" name="employer" class="form-control" placeholder="جهة العمل الحالية">
                        </div>

                        <div class="col-12 col-md-6">
                            <div class="form-check form-switch mt-4">
                                <input class="form-check-input" type="checkbox" name="is_activated_in_field" value="1" id="checkActivated" checked>
                                <label class="form-check-label fw-bold small text-dark" for="checkActivated">
                                    هل تم تفعيله بالميدان؟
                                </label>
                            </div>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">جهة التفعيل الميداني</label>
                            <input type="text" name="activation_entity" class="form-control" value="اللجنة الزراعية والسمكية العليا">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-hasad-primary btn-sm">
                        <i class="fa-solid fa-check me-1"></i> حفظ وتسجيل الفارس/الكادر
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
