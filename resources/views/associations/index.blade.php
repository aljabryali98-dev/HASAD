@extends('layouts.app')

@section('title', 'سجل الجمعيات التعاونية - نظام حصاد')

@section('content')
<div class="container-fluid p-0">

    <!-- شريط العنوان والمسار والأزرار -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="fa-solid fa-handshake-angle text-primary me-2"></i> سجل الجمعيات التعاونية
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">الرئيسية</a></li>
                    <li class="breadcrumb-item active" aria-current="page">الجمعيات التعاونية (الكيان المركزي)</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('associations.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus-circle me-1"></i> تسجيل جمعية جديدة
            </a>
            <a href="{{ route('geography.manage') }}" class="btn btn-outline-secondary">
                <i class="fa-solid fa-network-wired me-1"></i> النطاق الجغرافي والقرى
            </a>
        </div>
    </div>

    <!-- رسائل التنبيه والنجاح -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2 fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- بطاقات الإحصائيات المركزية -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 me-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="fa-solid fa-building-columns fs-4"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-bold">إجمالي الجمعيات المسجلة</span>
                        <h4 class="fw-bold mb-0 text-dark">{{ number_format($stats['total_associations']) }} <span class="small fs-6 text-muted">جمعية</span></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 me-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="fa-solid fa-circle-check fs-4"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-bold">الجمعيات النشطة</span>
                        <h4 class="fw-bold mb-0 text-dark">{{ number_format($stats['active_associations']) }} <span class="small fs-6 text-muted">جمعية نشطة</span></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3 me-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="fa-solid fa-users fs-4"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-bold">إجمالي المنتسبين والأعضاء</span>
                        <h4 class="fw-bold mb-0 text-dark">{{ number_format($stats['total_members']) }} <span class="small fs-6 text-muted">عضو</span></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="rounded-circle bg-info bg-opacity-10 text-info p-3 me-3 d-flex align-items-center justify-content-center" style="width: 52px; height: 52px;">
                        <i class="fa-solid fa-coins fs-4"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-bold">إجمالي رأس المال الحالي</span>
                        <h4 class="fw-bold mb-0 text-dark">{{ number_format($stats['total_capital']) }} <span class="small fs-6 text-muted">ر.ي</span></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- بطاقة البحث والفلترة -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white py-3 border-0">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="fa-solid fa-filter text-primary me-2"></i> تصفية وبحث متقدم في الجمعيات
            </h6>
        </div>
        <div class="card-body pt-0">
            <form method="GET" action="{{ route('associations.index') }}">
                <div class="row g-3">
                    <div class="col-12">
                        <x-location-hierarchy-selector 
                            :governorateId="request('gov_id')"
                            :directorateId="request('dir_id')"
                            :uzlahId="request('uzl_id')"
                            :showAddButton="false"
                        />
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-bold text-muted">حالة الجمعية</label>
                        <select name="status" class="form-select">
                            <option value="">-- جميع الحالات --</option>
                            <option value="نشطة" {{ request('status') === 'نشطة' ? 'selected' : '' }}>نشطة</option>
                            <option value="متوقفة" {{ request('status') === 'متوقفة' ? 'selected' : '' }}>متوقفة</option>
                            <option value="تحت التأسيس" {{ request('status') === 'تحت التأسيس' ? 'selected' : '' }}>تحت التأسيس</option>
                            <option value="متعثرة" {{ request('status') === 'متعثرة' ? 'selected' : '' }}>متعثرة</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-bold text-muted">نوع الجمعية</label>
                        <select name="type" class="form-select">
                            <option value="">-- جميع الأنواع --</option>
                            <option value="زراعية" {{ request('type') === 'زراعية' ? 'selected' : '' }}>زراعية</option>
                            <option value="متعددة الأغراض" {{ request('type') === 'متعددة الأغراض' ? 'selected' : '' }}>متعددة الأغراض</option>
                            <option value="تسويقية" {{ request('type') === 'تسويقية' ? 'selected' : '' }}>تسويقية</option>
                            <option value="حيوانية ونحلية" {{ request('type') === 'حيوانية ونحلية' ? 'selected' : '' }}>حيوانية ونحلية</option>
                            <option value="استهلاكية" {{ request('type') === 'استهلاكية' ? 'selected' : '' }}>استهلاكية</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-bold text-muted">بحث بالاسم أو الكود أو الرئيس</label>
                        <input type="text" name="search" class="form-control" placeholder="اسم الجمعية، الكود، رئيس الجمعية..." value="{{ request('search') }}">
                    </div>
                    <div class="col-12 col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fa-solid fa-magnifying-glass me-1"></i> بحث
                        </button>
                        <a href="{{ route('associations.index') }}" class="btn btn-outline-secondary" title="إلغاء التصفية">
                            <i class="fa-solid fa-rotate-right"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- جدول استعراض الجمعيات -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">
                <i class="fa-solid fa-list-check text-primary me-2"></i> قائمة الجمعيات التعاونية ({{ $associations->total() }})
            </h6>
            <span class="badge bg-light text-dark border">
                صفحة {{ $associations->currentPage() }} من {{ $associations->lastPage() ?: 1 }}
            </span>
        </div>
        <div class="card-body p-0">
            @if($associations->isEmpty())
                <div class="text-center py-5">
                    <i class="fa-solid fa-handshake-angle text-muted fs-1 mb-3"></i>
                    <h5 class="text-muted fw-bold">لا توجد جمعيات تعاونية مسجلة تطابق معايير البحث</h5>
                    <p class="text-muted small">يمكنك تأسيس وتسجيل جمعية جديدة الآن بالنقر على الزر أدناه.</p>
                    <a href="{{ route('associations.create') }}" class="btn btn-primary btn-sm mt-2">
                        <i class="fa-solid fa-plus-circle me-1"></i> تسجيل جمعية جديدة الآن
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-nowrap">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 130px;">الكود التعريفي</th>
                                <th>اسم الجمعية الرسمي</th>
                                <th>النوع</th>
                                <th>المحافظة والمديرية</th>
                                <th>العزلة / المقر</th>
                                <th>رئيس الجمعية</th>
                                <th>الأعضاء</th>
                                <th>المشاريع / المجاميع</th>
                                <th>الحالة</th>
                                <th class="text-center" style="width: 140px;">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($associations as $item)
                                <tr>
                                    <td>
                                        <span class="badge bg-dark font-monospace">{{ $item->code }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('associations.show', $item->id) }}" class="fw-bold text-primary text-decoration-none fs-6">
                                            {{ $item->name }}
                                        </a>
                                        @if($item->license_number)
                                            <span class="d-block small text-muted">ترخيص: {{ $item->license_number }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $item->type }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $item->governorate?->name_ar }}</div>
                                        <span class="small text-muted">{{ $item->directorate?->name_ar }}</span>
                                    </td>
                                    <td>
                                        <div>{{ $item->uzlah?->name_ar }}</div>
                                        <span class="small text-muted">{{ $item->subLocation?->name_ar ?? $item->address ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $item->president_name ?: '-' }}</div>
                                        @if($item->president_phone)
                                            <span class="small text-muted font-monospace"><i class="fa-solid fa-phone me-1 small"></i>{{ $item->president_phone }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-primary bg-opacity-10 text-primary">
                                            <i class="fa-solid fa-users me-1"></i> {{ number_format($item->total_members) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="small">
                                            <span class="badge bg-success bg-opacity-10 text-success me-1">
                                                {{ $item->projects_count }} مشاريع
                                            </span>
                                            <span class="badge bg-info bg-opacity-10 text-info">
                                                {{ $item->productive_groups_count }} مجاميع
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($item->status === 'نشطة')
                                            <span class="badge bg-success">نشطة</span>
                                        @elseif($item->status === 'تحت التأسيس')
                                            <span class="badge bg-warning text-dark">تحت التأسيس</span>
                                        @elseif($item->status === 'متعثرة')
                                            <span class="badge bg-danger">متعثرة</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $item->status }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('associations.show', $item->id) }}" class="btn btn-sm btn-outline-primary" title="عرض الملف الشامل (360° Profile)">
                                            <i class="fa-solid fa-eye me-1"></i> الملف
                                        </a>
                                        <a href="{{ route('associations.edit', $item->id) }}" class="btn btn-sm btn-outline-secondary ms-1" title="تعديل">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($associations->hasPages())
                    <div class="card-footer bg-white py-3 border-0">
                        {{ $associations->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
@endsection
