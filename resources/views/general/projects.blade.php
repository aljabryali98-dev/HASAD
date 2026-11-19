@extends('layouts.app')

@section('title', 'مشاريع الجمعيات التعاونية - نظام حصاد')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                <span class="text-success fs-5"><i class="fa-solid fa-list-check"></i></span>
                مشاريع الجمعيات التعاونية
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">الرئيسية</a></li>
                    <li class="breadcrumb-item active">المشاريع والمبادرات</li>
                </ol>
            </nav>
        </div>
        <button type="button" class="btn btn-hasad-primary d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddProject">
            <i class="fa-solid fa-plus-circle"></i>
            <span>تسجيل مشروع جديد</span>
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4 rounded-3 border-0 shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2 fs-5 text-success"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card kpi-emerald h-100">
                <span class="text-muted small fw-bold d-block mb-1">إجمالي المشاريع</span>
                <h4 class="stat-num text-dark mb-0">{{ number_format($stats['total']) }}</h4>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card kpi-emerald h-100">
                <span class="text-muted small fw-bold d-block mb-1">المشاريع المنفذة</span>
                <h4 class="stat-num text-success mb-0">{{ number_format($stats['completed']) }}</h4>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card kpi-amber h-100">
                <span class="text-muted small fw-bold d-block mb-1">قيد التنفيذ</span>
                <h4 class="stat-num text-dark mb-0">{{ number_format($stats['ongoing']) }}</h4>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card kpi-blue h-100">
                <span class="text-muted small fw-bold d-block mb-1">التكلفة الإجمالية (ر.ي)</span>
                <h4 class="stat-num text-dark mb-0">{{ number_format($stats['total_cost']) }}</h4>
            </div>
        </div>
    </div>

    <div class="card-hasad">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="fa-solid fa-diagram-project text-success"></i>
                <span>سجل المشاريع التعاونية المعتمدة ({{ $projects->total() }})</span>
            </h6>
            <span class="badge bg-light text-dark border font-monospace">صفحة {{ $projects->currentPage() }}</span>
        </div>
        <div class="card-body p-0">
            @if($projects->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-folder-open fs-1 mb-2 d-block"></i>
                    لا توجد مشاريع مسجلة حالياً.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hasad align-middle mb-0 text-nowrap">
                        <thead>
                            <tr>
                                <th>اسم المشروع</th>
                                <th>الجمعية المرتبطة</th>
                                <th>النوع</th>
                                <th>الموقع (المحافظة / المديرية)</th>
                                <th>الحالة</th>
                                <th>التكلفة (ر.ي)</th>
                                <th>ملاحظات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($projects as $proj)
                                <tr>
                                    <td class="fw-bold text-dark">{{ $proj->project_name }}</td>
                                    <td>
                                        @if($proj->association)
                                            <a href="{{ route('associations.show', $proj->association_id) }}" class="text-success fw-bold text-decoration-none">
                                                {{ $proj->association->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td><span class="badge bg-light text-dark border">{{ $proj->project_type ?: '-' }}</span></td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $proj->governorate?->name_ar }}</div>
                                        <span class="small text-muted">{{ $proj->directorate?->name_ar }}</span>
                                    </td>
                                    <td>
                                        @if($proj->execution_status === 'منفذ')
                                            <span class="badge-status badge-status-success">
                                                <span class="badge-status-dot"></span> منفذ
                                            </span>
                                        @elseif($proj->execution_status === 'قيد التنفيذ')
                                            <span class="badge-status badge-status-warning">
                                                <span class="badge-status-dot"></span> قيد التنفيذ
                                            </span>
                                        @else
                                            <span class="badge-status badge-status-info">
                                                <span class="badge-status-dot"></span> {{ $proj->execution_status }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="stat-num text-success fw-bold">{{ number_format($proj->cost) }}</td>
                                    <td class="small text-muted text-truncate" style="max-width: 160px;">{{ $proj->notes ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($projects->hasPages())
                    <div class="card-footer bg-white border-0">{{ $projects->links('pagination::bootstrap-5') }}</div>
                @endif
            @endif
        </div>
    </div>
</div>

<!-- مودال تسجيل مشروع جديد -->
<div class="modal fade" id="modalAddProject" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
            <div class="modal-header text-white py-3" style="background: linear-gradient(135deg, var(--hasad-primary) 0%, var(--hasad-primary-hover) 100%);">
                <h6 class="modal-title fw-bold"><i class="fa-solid fa-plus-circle me-2"></i> تسجيل مشروع تعاوني جديد</h6>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('general.projects.store') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-7">
                            <label class="form-label small fw-bold">اسم المشروع <span class="text-danger">*</span></label>
                            <input type="text" name="project_name" class="form-control" placeholder="مشروع توزيع البذور، صيانة سد..." required>
                        </div>
                        <div class="col-12 col-md-5">
                            <label class="form-label small fw-bold">نوع المشروع</label>
                            <input type="text" name="project_type" class="form-control" placeholder="زراعي، مائي، طاقة، تسويقي...">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">الجمعية التعاونية التابعة لها (اختياري)</label>
                            <select name="association_id" class="form-select">
                                <option value="">-- اختياري للربط بالجمعية --</option>
                                @foreach($associations as $ass)
                                    <option value="{{ $ass->id }}">{{ $ass->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label small fw-bold">حالة التنفيذ <span class="text-danger">*</span></label>
                            <select name="execution_status" class="form-select" required>
                                <option value="قيد التنفيذ">قيد التنفيذ</option>
                                <option value="منفذ">منفذ</option>
                                <option value="مخطط له">مخطط له</option>
                                <option value="متعثر">متعثر</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label small fw-bold">التكلفة التقديرية (ر.ي)</label>
                            <input type="number" step="0.01" name="cost" class="form-control" value="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">النطاق الجغرافي <span class="text-danger">*</span></label>
                            <x-location-hierarchy-selector :required="true" :showAddButton="false" />
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">ملاحظات إضافية</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-hasad-primary btn-sm fw-bold"><i class="fa-solid fa-check me-1"></i> حفظ وتحديث ملف الجمعية</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
