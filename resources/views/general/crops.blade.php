@extends('layouts.app')

@section('title', 'سجل المحاصيل الزراعية والإنتاج - نظام حصاد')

@section('content')
<div class="container-fluid p-0">
    <!-- الترويسة الرئيسية والمسار الإرشادي -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                <span class="text-success fs-5"><i class="fa-solid fa-wheat-awn"></i></span>
                سجل المحاصيل والإنتاج الزراعي
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small text-muted">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">الرئيسية</a></li>
                    <li class="breadcrumb-item active text-success fw-bold" aria-current="page">سجل المحاصيل</li>
                </ol>
            </nav>
        </div>
        <div>
            <button type="button" class="btn btn-hasad-primary d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddCrop">
                <i class="fa-solid fa-plus-circle"></i>
                <span>تسجيل محصول زراعي جديد</span>
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4 shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2 fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- بطاقات المؤشرات الإحصائية -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-4">
            <div class="kpi-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="kpi-title">إجمالي السجلات المسجلة</div>
                        <div class="kpi-value text-dark stat-num">{{ number_format($stats['total_records']) }}</div>
                        <div class="kpi-subtitle">محصول زراعي مسجل</div>
                    </div>
                    <div class="kpi-icon bg-light text-primary">
                        <i class="fa-solid fa-seedling"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="kpi-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="kpi-title">إجمالي الإنتاج المسجل</div>
                        <div class="kpi-value text-success stat-num">{{ number_format($stats['total_production']) }} <small class="fs-6 text-muted">طن</small></div>
                        <div class="kpi-subtitle">إنتاج فعلي ومتوقع</div>
                    </div>
                    <div class="kpi-icon bg-success-subtle text-success">
                        <i class="fa-solid fa-boxes-stacked"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="kpi-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="kpi-title">المحاصيل المتوفرة حالياً</div>
                        <div class="kpi-value text-primary stat-num">{{ number_format($stats['available_count']) }}</div>
                        <div class="kpi-subtitle">محصول جاهز للتسويق</div>
                    </div>
                    <div class="kpi-icon bg-primary-subtle text-primary">
                        <i class="fa-solid fa-basket-shopping"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- جدول سجل المحاصيل وأصنافها -->
    <div class="card-hasad">
        <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-list-check text-success"></i>
                <h6 class="fw-bold mb-0 text-dark">سجل المحاصيل وأصنافها المعتمدة</h6>
            </div>
            <span class="badge bg-light text-muted border px-3 py-2 rounded-pill font-monospace small">
                صفحة {{ $crops->currentPage() }} من {{ $crops->lastPage() }}
            </span>
        </div>
        <div class="card-body p-0">
            @if($crops->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-wheat-awn fs-1 mb-2 d-block text-secondary opacity-50"></i>
                    <p class="mb-0 fw-medium">لا توجد محاصيل زراعية مسجلة حالياً.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table-hasad align-middle mb-0 text-nowrap">
                        <thead>
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
                                            <a href="{{ route('associations.show', $crop->association_id) }}" class="text-success fw-bold text-decoration-none">
                                                {{ $crop->association->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $crop->governorate?->name_ar }} - {{ $crop->directorate?->name_ar }}</td>
                                    <td class="stat-num fw-bold">{{ number_format($crop->production_quantity) }} {{ $crop->quantity_unit }}</td>
                                    <td>
                                        <span class="badge {{ $crop->availability_status === 'متوفرة' ? 'badge-status-active' : 'badge-status-pending' }}">
                                            {{ $crop->availability_status }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge {{ $crop->execution_status === 'منفذ' ? 'badge-status-active' : 'bg-light text-muted border' }}">
                                            {{ $crop->execution_status }}
                                        </span>
                                    </td>
                                    <td class="stat-num text-muted">{{ $crop->harvest_date?->format('Y-m-d') ?: '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-top">
                    {{ $crops->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal: Add Crop -->
<div class="modal fade" id="modalAddCrop" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white p-3" style="background: linear-gradient(135deg, var(--hasad-primary) 0%, var(--hasad-primary-dark) 100%) !important;">
                <h5 class="modal-title fw-bold fs-6 text-white d-flex align-items-center gap-2">
                    <i class="fa-solid fa-plus-circle"></i>
                    <span>تسجيل محصول زراعي جديد</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-hasad-primary px-4">
                        <i class="fa-solid fa-save me-1"></i> حفظ وتحديث البيانات
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
