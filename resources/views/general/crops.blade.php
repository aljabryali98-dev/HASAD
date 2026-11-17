@extends('layouts.app')

@section('title', 'سجل المحاصيل الزراعية - نظام حصاد')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="fa-solid fa-wheat-awn text-warning me-2"></i> سجل المحاصيل والإنتاج الزراعي
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item active">الزراعة والإنتاج الحيواني</li>
                </ol>
            </nav>
        </div>
        <button type="button" class="btn btn-warning fw-bold text-dark" data-bs-toggle="modal" data-bs-target="#modalAddCrop">
            <i class="fa-solid fa-plus-circle me-1"></i> تسجيل محصول زراعي جديد
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
                <span class="text-muted small fw-bold">إجمالي السجلات</span>
                <h4 class="fw-bold text-dark mb-0">{{ number_format($stats['total_records']) }}</h4>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm p-3 text-center">
                <span class="text-muted small fw-bold">إجمالي كمية الإنتاج المسجلة</span>
                <h4 class="fw-bold text-success mb-0">{{ number_format($stats['total_production']) }} طن</h4>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm p-3 text-center">
                <span class="text-muted small fw-bold">المحاصيل المتوفرة حالياً</span>
                <h4 class="fw-bold text-primary mb-0">{{ number_format($stats['available_count']) }}</h4>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">سجل المحاصيل وأصنافها المعتمدة</h6>
            <span class="badge bg-light text-dark border">صفحة {{ $crops->currentPage() }}</span>
        </div>
        <div class="card-body p-0">
            @if($crops->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-wheat-awn fs-1 mb-2 d-block"></i>
                    لا توجد محاصيل مسجلة حالياً.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-nowrap">
                        <thead class="table-light">
                            <tr>
                                <th>نوع المحصول</th>
                                <th>الصنف</th>
                                <th>الجمعية المرتبطة</th>
                                <th>الموقع (المديرية / العزلة)</th>
                                <th>كمية الإنتاج</th>
                                <th>الوفرة</th>
                                <th>الحالة</th>
                                <th>تاريخ الحصاد</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($crops as $crop)
                                <tr>
                                    <td class="fw-bold text-dark">{{ $crop->crop_type }}</td>
                                    <td>{{ $crop->crop_variety ?: '-' }}</td>
                                    <td>
                                        @if($crop->association)
                                            <a href="{{ route('associations.show', $crop->association_id) }}" class="text-primary fw-bold text-decoration-none">
                                                {{ $crop->association->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $crop->governorate?->name_ar }} - {{ $crop->directorate?->name_ar }}</td>
                                    <td class="font-monospace fw-bold">{{ number_format($crop->production_quantity) }} {{ $crop->quantity_unit }}</td>
                                    <td><span class="badge bg-success">{{ $crop->availability_status }}</span></td>
                                    <td><span class="badge bg-info bg-opacity-10 text-info">{{ $crop->execution_status }}</span></td>
                                    <td>{{ $crop->harvest_date?->format('Y-m-d') ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($crops->hasPages())
                    <div class="card-footer bg-white border-0">{{ $crops->links('pagination::bootstrap-5') }}</div>
                @endif
            @endif
        </div>
    </div>
</div>

<!-- مودال تسجيل محصول جديد -->
<div class="modal fade" id="modalAddCrop" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow border-0 rounded-3">
            <div class="modal-header bg-warning py-3">
                <h6 class="modal-title fw-bold text-dark"><i class="fa-solid fa-plus-circle me-2"></i> تسجيل محصول زراعي جديد</h6>
                <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('general.crops.store') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-muted">نوع المحصول <span class="text-danger">*</span></label>
                            <input type="text" name="crop_type" class="form-control" placeholder="ذرة، قمح، بن، خضار..." required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-muted">الصنف</label>
                            <input type="text" name="crop_variety" class="form-control" placeholder="بيضاء، حمراء، دويري...">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-muted">كمية الإنتاج <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="production_quantity" class="form-control" required>
                                <select name="quantity_unit" class="form-select" style="max-width: 100px;">
                                    <option value="طن">طن</option>
                                    <option value="كجم">كجم</option>
                                    <option value="كيس">كيس</option>
                                    <option value="قدح">قدح</option>
                                </select>
                            </div>
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
                            <label class="form-label small fw-bold text-muted">حالة الوفرة <span class="text-danger">*</span></label>
                            <select name="availability_status" class="form-select" required>
                                <option value="متوفرة">متوفرة حالياً</option>
                                <option value="متوقع إنتاجها">متوقع إنتاجها</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label small fw-bold text-muted">حالة التنفيذ <span class="text-danger">*</span></label>
                            <select name="execution_status" class="form-select" required>
                                <option value="منفذ">منفذ</option>
                                <option value="قيد التنفيذ">قيد التنفيذ</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">النطاق الجغرافي <span class="text-danger">*</span></label>
                            <x-location-hierarchy-selector :required="true" :showAddButton="false" />
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-muted">تاريخ الزراعة</label>
                            <input type="date" name="planting_date" class="form-control">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-muted">تاريخ الحصاد المتوقع / الفعلي</label>
                            <input type="date" name="harvest_date" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-warning btn-sm fw-bold"><i class="fa-solid fa-check me-1"></i> حفظ وتحديث ملف الجمعية</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
