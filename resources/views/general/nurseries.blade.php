@extends('layouts.app')

@section('title', 'المشاتل الزراعية - نظام حصاد')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="fa-solid fa-tree text-success me-2"></i> المشاتل الزراعية وإنتاج الشتلات
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item active">المشاتل الزراعية</li>
                </ol>
            </nav>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddNursery">
            <i class="fa-solid fa-plus-circle me-1"></i> تسجيل مشتل جديد
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm p-3 text-center">
                <span class="text-muted small fw-bold">إجمالي المشاتل</span>
                <h4 class="fw-bold text-dark mb-0">{{ number_format($stats['total_nurseries']) }}</h4>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm p-3 text-center">
                <span class="text-muted small fw-bold">المشاتل المفعلة</span>
                <h4 class="fw-bold text-success mb-0">{{ number_format($stats['active_nurseries']) }}</h4>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm p-3 text-center">
                <span class="text-muted small fw-bold">إجمالي الطاقة الإنتاجية السنوية (شتلة)</span>
                <h4 class="fw-bold text-primary mb-0">{{ number_format($stats['total_capacity']) }}</h4>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">سجل المشاتل الزراعية المسجلة</h6>
            <span class="badge bg-light text-dark border">صفحة {{ $nurseries->currentPage() }}</span>
        </div>
        <div class="card-body p-0">
            @if($nurseries->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-folder-open fs-1 mb-2 d-block"></i>
                    لا توجد مشاتل مسجلة حالياً.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-nowrap">
                        <thead class="table-light">
                            <tr>
                                <th>اسم المشتل</th>
                                <th>الملكية</th>
                                <th>الطاقة الإنتاجية</th>
                                <th>الحالة</th>
                                <th>الموقع (المحافظة / المديرية)</th>
                                <th>الجمعية المرتبطة</th>
                                <th>ملاحظات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($nurseries as $n)
                                <tr>
                                    <td class="fw-bold text-dark">{{ $n->nursery_name }}</td>
                                    <td><span class="badge bg-secondary">{{ $n->ownership_type }}</span></td>
                                    <td class="fw-bold text-primary">{{ number_format($n->capacity) }} {{ $n->capacity_unit }}</td>
                                    <td>
                                        <span class="badge {{ $n->status === 'مفعل' ? 'bg-success' : 'bg-warning text-dark' }}">
                                            {{ $n->status }}
                                        </span>
                                    </td>
                                    <td>{{ $n->governorate?->name_ar }} - {{ $n->directorate?->name_ar }} {{ $n->uzlah ? '('.$n->uzlah->name_ar.')' : '' }}</td>
                                    <td>
                                        @if($n->association)
                                            <a href="{{ route('associations.show', $n->association_id) }}" class="text-primary text-decoration-none fw-bold">
                                                {{ $n->association->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $n->notes ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-top">
                    {{ $nurseries->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal: Add Nursery -->
<div class="modal fade" id="modalAddNursery" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-plus-circle text-primary me-2"></i> تسجيل مشتل زراعي جديد</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('general.nurseries.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">اسم المشتل <span class="text-danger">*</span></label>
                            <input type="text" name="nursery_name" class="form-control" required placeholder="مثال: مشتل الجمعية لشتلات البن والفواكه">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">نوع الملكية <span class="text-danger">*</span></label>
                            <select name="ownership_type" class="form-select" required>
                                <option value="تابع لجمعية">تابع لجمعية تعاونية</option>
                                <option value="حكومي">حكومي</option>
                                <option value="خاص">خاص</option>
                                <option value="مشترك">مشترك</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">الطاقة الإنتاجية <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="capacity" class="form-control" required placeholder="مثال: 50000">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">وحدة القياس <span class="text-danger">*</span></label>
                            <input type="text" name="capacity_unit" class="form-control" value="شتلة/موسم" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">حالة المشتل <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="مفعل">مفعل / منتج</option>
                                <option value="متوقف">متوقف مؤقتاً</option>
                                <option value="قيد التأسيس">قيد التأسيس</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold">الجمعية التعاونية التابع لها (اختياري)</label>
                            <select name="association_id" class="form-select">
                                <option value="">-- غير مرتبط بجمعية محددة --</option>
                                @foreach($associations as $a)
                                    <option value="{{ $a->id }}">{{ $a->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 mt-3">
                            <h6 class="fw-bold border-bottom pb-2 text-primary">الموقع الجغرافي للمشتل</h6>
                            <x-location-hierarchy-selector 
                                :governorates="$governorates"
                                nameGov="governorate_id"
                                nameDir="directorate_id"
                                nameUzl="uzlah_id"
                                :required="true"
                            />
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold">أنواع الشتلات / ملاحظات</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="أصناف الشتلات المنتجة: بن، لوز، فواكه، سياج..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i> حفظ وتحديث البيانات</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
