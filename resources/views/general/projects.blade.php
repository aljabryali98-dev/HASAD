@extends('layouts.app')

@section('title', 'مشاريع الجمعيات التعاونية - نظام حصاد')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="fa-solid fa-list-check text-primary me-2"></i> مشاريع الجمعيات التعاونية
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item active">المشاريع والمبادرات</li>
                </ol>
            </nav>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddProject">
            <i class="fa-solid fa-plus-circle me-1"></i> تسجيل مشروع جديد
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 text-center">
                <span class="text-muted small fw-bold">إجمالي المشاريع</span>
                <h4 class="fw-bold text-dark mb-0">{{ number_format($stats['total']) }}</h4>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 text-center">
                <span class="text-muted small fw-bold">المشاريع المنفذة</span>
                <h4 class="fw-bold text-success mb-0">{{ number_format($stats['completed']) }}</h4>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 text-center">
                <span class="text-muted small fw-bold">قيد التنفيذ</span>
                <h4 class="fw-bold text-warning mb-0">{{ number_format($stats['ongoing']) }}</h4>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 text-center">
                <span class="text-muted small fw-bold">التكلفة الإجمالية (ر.ي)</span>
                <h4 class="fw-bold text-primary mb-0">{{ number_format($stats['total_cost']) }}</h4>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">سجل المشاريع التعاونية المعتمدة</h6>
            <span class="badge bg-light text-dark border">صفحة {{ $projects->currentPage() }}</span>
        </div>
        <div class="card-body p-0">
            @if($projects->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-folder-open fs-1 mb-2 d-block"></i>
                    لا توجد مشاريع مسجلة حالياً.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-nowrap">
                        <thead class="table-light">
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
                                            <a href="{{ route('associations.show', $proj->association_id) }}" class="text-primary fw-bold text-decoration-none">
                                                {{ $proj->association->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $proj->project_type ?: '-' }}</td>
                                    <td>{{ $proj->governorate?->name_ar }} - {{ $proj->directorate?->name_ar }}</td>
                                    <td>
                                        <span class="badge {{ $proj->execution_status === 'منفذ' ? 'bg-success' : 'bg-warning text-dark' }}">
                                            {{ $proj->execution_status }}
                                        </span>
                                    </td>
                                    <td class="font-monospace fw-bold">{{ number_format($proj->cost) }}</td>
                                    <td class="small text-muted">{{ $proj->notes ?: '-' }}</td>
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
        <div class="modal-content shadow border-0 rounded-3">
            <div class="modal-header bg-primary text-white py-3">
                <h6 class="modal-title fw-bold"><i class="fa-solid fa-plus-circle me-2"></i> تسجيل مشروع تعاوني جديد</h6>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('general.projects.store') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-7">
                            <label class="form-label small fw-bold text-muted">اسم المشروع <span class="text-danger">*</span></label>
                            <input type="text" name="project_name" class="form-control" placeholder="مشروع توزيع البذور، صيانة سد..." required>
                        </div>
                        <div class="col-12 col-md-5">
                            <label class="form-label small fw-bold text-muted">نوع المشروع</label>
                            <input type="text" name="project_type" class="form-control" placeholder="زراعي، مائي، طاقة، تسويقي...">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-muted">الجمعية التعاونية التابعة لها (اختياري)</label>
                            <select name="association_id" class="form-select">
                                <option value="">-- اختياري للربط بالجمعية --</option>
                                @foreach($associations as $ass)
                                    <option value="{{ $ass->id }}">{{ $ass->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label small fw-bold text-muted">حالة التنفيذ <span class="text-danger">*</span></label>
                            <select name="execution_status" class="form-select" required>
                                <option value="قيد التنفيذ">قيد التنفيذ</option>
                                <option value="منفذ">منفذ</option>
                                <option value="مخطط له">مخطط له</option>
                                <option value="متعثر">متعثر</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label small fw-bold text-muted">التكلفة التقديرية (ر.ي)</label>
                            <input type="number" step="0.01" name="cost" class="form-control" value="0">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">النطاق الجغرافي <span class="text-danger">*</span></label>
                            <x-location-hierarchy-selector :required="true" :showAddButton="false" />
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">ملاحظات إضافية</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold"><i class="fa-solid fa-check me-1"></i> حفظ وتحديث ملف الجمعية</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
