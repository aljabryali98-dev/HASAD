@extends('layouts.app')

@section('title', 'سجل المجاميع الإنتاجية - نظام حصاد')

@section('content')
<div class="container-fluid p-0">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="fa-solid fa-people-roof text-success me-2"></i> سجل المجاميع الإنتاجية
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">الرئيسية</a></li>
                    <li class="breadcrumb-item active" aria-current="page">المجاميع الإنتاجية التعاونية</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAddGroup">
                <i class="fa-solid fa-plus-circle me-1"></i> تأسيس مجموعة إنتاجية جديدة
            </button>
            <a href="{{ route('associations.index') }}" class="btn btn-outline-primary">
                <i class="fa-solid fa-building-columns me-1"></i> الجمعيات التعاونية
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2 fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- بطاقات الإحصائيات الخمس -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3 text-center">
                    <span class="text-muted small fw-bold d-block">إجمالي المجاميع</span>
                    <h4 class="fw-bold text-dark mb-0">{{ number_format($stats['total_groups']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3 text-center">
                    <span class="text-muted small fw-bold d-block">المجاميع النشطة</span>
                    <h4 class="fw-bold text-success mb-0">{{ number_format($stats['active_groups']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3 text-center">
                    <span class="text-muted small fw-bold d-block">إجمالي الأعضاء</span>
                    <h4 class="fw-bold text-primary mb-0">{{ number_format($stats['total_members']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3 text-center">
                    <span class="text-muted small fw-bold d-block">قيمة الإنتاج (ر.ي)</span>
                    <h4 class="fw-bold text-info mb-0">{{ number_format($stats['total_production_value']) }}</h4>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3 text-center">
                    <span class="text-muted small fw-bold d-block">قيمة المبيعات (ر.ي)</span>
                    <h4 class="fw-bold text-warning mb-0">{{ number_format($stats['total_sales_value']) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <!-- بطاقة الفلترة -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white py-3 border-0">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="fa-solid fa-filter text-success me-2"></i> تصفية وبحث في المجاميع الإنتاجية
            </h6>
        </div>
        <div class="card-body pt-0">
            <form method="GET" action="{{ route('productive-groups.index') }}">
                <div class="row g-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-bold text-muted">الجمعية التعاونية التابعة لها</label>
                        <select name="association_id" class="form-select">
                            <option value="">-- جميع الجمعيات --</option>
                            @foreach($associations as $ass)
                                <option value="{{ $ass->id }}" {{ request('association_id') == $ass->id ? 'selected' : '' }}>
                                    {{ $ass->name }} ({{ $ass->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-bold text-muted">نوع المجموعة</label>
                        <select name="group_type" class="form-select">
                            <option value="">-- جميع الأنواع --</option>
                            <option value="زراعية" {{ request('group_type') == 'زراعية' ? 'selected' : '' }}>زراعية</option>
                            <option value="حيوانية" {{ request('group_type') == 'حيوانية' ? 'selected' : '' }}>حيوانية</option>
                            <option value="نحلية" {{ request('group_type') == 'نحلية' ? 'selected' : '' }}>نحلية</option>
                            <option value="سمكية" {{ request('group_type') == 'سمكية' ? 'selected' : '' }}>سمكية</option>
                            <option value="حرفية" {{ request('group_type') == 'حرفية' ? 'selected' : '' }}>حرفية</option>
                            <option value="صناعية" {{ request('group_type') == 'صناعية' ? 'selected' : '' }}>صناعية</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-bold text-muted">بحث بالاسم أو الكود</label>
                        <input type="text" name="search" class="form-control" placeholder="اسم المجموعة، المنتج، المسؤول..." value="{{ request('search') }}">
                    </div>
                    <div class="col-12 col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fa-solid fa-magnifying-glass me-1"></i> بحث
                        </button>
                        <a href="{{ route('productive-groups.index') }}" class="btn btn-outline-secondary">
                            <i class="fa-solid fa-rotate-right"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- جدول استعراض المجاميع الإنتاجية -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="fa-solid fa-list-check text-success me-2"></i> قائمة المجاميع الإنتاجية ({{ $groups->total() }})
            </h6>
            <span class="badge bg-light text-dark border">
                صفحة {{ $groups->currentPage() }} من {{ $groups->lastPage() ?: 1 }}
            </span>
        </div>
        <div class="card-body p-0">
            @if($groups->isEmpty())
                <div class="text-center py-5">
                    <i class="fa-solid fa-people-roof text-muted fs-1 mb-3"></i>
                    <h5 class="text-muted fw-bold">لا توجد مجاميع إنتاجية مسجلة حالياً</h5>
                    <p class="text-muted small">ابدأ بتأسيس أول مجموعة إنتاجية لربطها بالجمعية والميسر الميداني.</p>
                    <button type="button" class="btn btn-success btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#modalAddGroup">
                        <i class="fa-solid fa-plus-circle me-1"></i> تأسيس مجموعة جديدة الآن
                    </button>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-nowrap">
                        <thead class="table-light">
                            <tr>
                                <th>الكود</th>
                                <th>اسم المجموعة الإنتاجية</th>
                                <th>النوع</th>
                                <th>الجمعية المرتبطة</th>
                                <th>الموقع (المديرية / العزلة)</th>
                                <th>الميسّر المسؤول</th>
                                <th>الأعضاء</th>
                                <th>المنتج الرئيسي</th>
                                <th>الحالة</th>
                                <th class="text-center">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($groups as $grp)
                                <tr>
                                    <td><span class="badge bg-dark font-monospace">{{ $grp->code }}</span></td>
                                    <td>
                                        <a href="{{ route('productive-groups.show', $grp->id) }}" class="fw-bold text-success text-decoration-none">
                                            {{ $grp->name }}
                                        </a>
                                    </td>
                                    <td><span class="badge bg-light text-dark border">{{ $grp->group_type }}</span></td>
                                    <td>
                                        @if($grp->association)
                                            <a href="{{ route('associations.show', $grp->association_id) }}" class="small text-primary text-decoration-none fw-bold">
                                                {{ $grp->association->name }}
                                            </a>
                                        @else
                                            <span class="text-muted small">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>{{ $grp->governorate?->name_ar }} - {{ $grp->directorate?->name_ar }}</div>
                                        <span class="small text-muted">{{ $grp->uzlah?->name_ar }}</span>
                                    </td>
                                    <td>
                                        @if($grp->facilitators->isNotEmpty())
                                            <span class="badge bg-info bg-opacity-10 text-info">
                                                <i class="fa-solid fa-user-tie me-1"></i> {{ $grp->facilitators->first()->full_name }}
                                            </span>
                                        @else
                                            <span class="text-muted small">غير محدد</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary">
                                            {{ $grp->total_members }} عضو
                                        </span>
                                    </td>
                                    <td>{{ $grp->main_product ?: '-' }}</td>
                                    <td>
                                        <span class="badge {{ $grp->status === 'نشطة' ? 'bg-success' : 'bg-secondary' }}">
                                            {{ $grp->status }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('productive-groups.show', $grp->id) }}" class="btn btn-sm btn-outline-success">
                                            <i class="fa-solid fa-eye me-1"></i> التفاصيل
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($groups->hasPages())
                    <div class="card-footer bg-white py-3 border-0">
                        {{ $groups->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

<!-- مودال تأسيس مجموعة إنتاجية جديدة -->
<div class="modal fade" id="modalAddGroup" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow border-0 rounded-3">
            <div class="modal-header bg-success text-white py-3">
                <h6 class="modal-title fw-bold">
                    <i class="fa-solid fa-plus-circle me-2"></i> تأسيس مجموعة إنتاجية جديدة
                </h6>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <form method="POST" action="{{ route('productive-groups.store') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-muted">اسم المجموعة الإنتاجية <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="مثال: مجموعة البركة لإنتاج البذور" required>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label small fw-bold text-muted">نوع المجموعة <span class="text-danger">*</span></label>
                            <select name="group_type" class="form-select" required>
                                <option value="زراعية">زراعية</option>
                                <option value="حيوانية">حيوانية</option>
                                <option value="نحلية">نحلية</option>
                                <option value="سمكية">سمكية</option>
                                <option value="حرفية">حرفية</option>
                                <option value="صناعية">صناعية</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label small fw-bold text-muted">حالة المجموعة <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="نشطة">نشطة</option>
                                <option value="تحت التأسيس">تحت التأسيس</option>
                                <option value="موسمية">موسمية</option>
                                <option value="متوقفة">متوقفة</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-muted">الجمعية التعاونية التابعة لها (اختياري)</label>
                            <select name="association_id" class="form-select">
                                <option value="">-- اختر الجمعية إن وُجدت --</option>
                                @foreach($associations as $ass)
                                    <option value="{{ $ass->id }}">{{ $ass->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-muted">الميسّر المسؤول (اختياري)</label>
                            <select name="facilitator_id" class="form-select">
                                <option value="">-- اختر الميسّر إن وُجد --</option>
                                @foreach($facilitators as $fac)
                                    <option value="{{ $fac->id }}">{{ $fac->full_name }} ({{ $fac->phone }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">النطاق الجغرافي للمجموعة <span class="text-danger">*</span></label>
                            <x-location-hierarchy-selector :required="true" :showAddButton="false" />
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-muted">المنتج الرئيسي</label>
                            <input type="text" name="main_product" class="form-control" placeholder="ذرة، عسل، حليب، ألبان...">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-muted">اسم مسؤول المجموعة</label>
                            <input type="text" name="leader_name" class="form-control" placeholder="الاسم الرباعي">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-muted">رقم هاتف المسؤول</label>
                            <input type="text" name="leader_phone" class="form-control" placeholder="770000000">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-muted">إجمالي عدد الأعضاء</label>
                            <input type="number" name="total_members" class="form-control" value="0">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-muted">المساحة الإنتاجية</label>
                            <input type="number" step="0.01" name="productive_area" class="form-control" value="0">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-muted">وحدة المساحة</label>
                            <select name="area_unit" class="form-select">
                                <option value="هكتار">هكتار</option>
                                <option value="معاد">معاد</option>
                                <option value="فدان">فدان</option>
                                <option value="قصبة">قصبة</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success btn-sm fw-bold">
                        <i class="fa-solid fa-check me-1"></i> تأسيس المجموعة الإنتاجية
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
