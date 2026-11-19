@extends('layouts.app')

@section('title', 'المشاتل الزراعية وإنتاج الشتلات - نظام حصاد')

@section('content')
<div class="container-fluid p-0">
    <!-- الترويسة الرئيسية والمسار الإرشادي -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                <span class="text-success fs-5"><i class="fa-solid fa-tree"></i></span>
                المشاتل الزراعية وإنتاج الشتلات
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small text-muted">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">الرئيسية</a></li>
                    <li class="breadcrumb-item active text-success fw-bold" aria-current="page">المشاتل الزراعية</li>
                </ol>
            </nav>
        </div>
        <div>
            <button type="button" class="btn btn-hasad-primary d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddNursery">
                <i class="fa-solid fa-plus-circle"></i>
                <span>تسجيل مشتل زراعي جديد</span>
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
                        <div class="kpi-title">إجمالي المشاتل الزراعية</div>
                        <div class="kpi-value text-dark stat-num">{{ number_format($stats['total_nurseries']) }}</div>
                        <div class="kpi-subtitle">مشتل مسجل بالنظام</div>
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
                        <div class="kpi-title">المشاتل المفعلة والمنتجة</div>
                        <div class="kpi-value text-success stat-num">{{ number_format($stats['active_nurseries']) }}</div>
                        <div class="kpi-subtitle">جاهزية تشغيلية كاملة</div>
                    </div>
                    <div class="kpi-icon bg-success-subtle text-success">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="kpi-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="kpi-title">الطاقة الإنتاجية السنوية</div>
                        <div class="kpi-value text-primary stat-num">{{ number_format($stats['total_capacity']) }} <small class="fs-6 text-muted">شتلة</small></div>
                        <div class="kpi-subtitle">قدرة الإكثار الموسمي</div>
                    </div>
                    <div class="kpi-icon bg-primary-subtle text-primary">
                        <i class="fa-solid fa-arrows-spin"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- جدول سجل المشاتل الزراعية -->
    <div class="card-hasad">
        <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-list-check text-success"></i>
                <h6 class="fw-bold mb-0 text-dark">سجل المشاتل الزراعية المعتمدة</h6>
            </div>
            <span class="badge bg-light text-muted border px-3 py-2 rounded-pill font-monospace small">
                صفحة {{ $nurseries->currentPage() }} من {{ $nurseries->lastPage() }}
            </span>
        </div>
        <div class="card-body p-0">
            @if($nurseries->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-tree fs-1 mb-2 d-block text-secondary opacity-50"></i>
                    <p class="mb-0 fw-medium">لا توجد مشاتل زراعية مسجلة حالياً.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table-hasad align-middle mb-0 text-nowrap">
                        <thead>
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
                                    <td><span class="badge bg-light text-dark border">{{ $n->ownership_type }}</span></td>
                                    <td class="fw-bold text-primary stat-num">{{ number_format($n->capacity) }} {{ $n->capacity_unit }}</td>
                                    <td>
                                        <span class="badge {{ $n->status === 'مفعل' ? 'badge-status-active' : 'badge-status-pending' }}">
                                            {{ $n->status }}
                                        </span>
                                    </td>
                                    <td>{{ $n->governorate?->name_ar }} - {{ $n->directorate?->name_ar }} {{ $n->uzlah ? '('.$n->uzlah->name_ar.')' : '' }}</td>
                                    <td>
                                        @if($n->association)
                                            <a href="{{ route('associations.show', $n->association_id) }}" class="text-success text-decoration-none fw-bold">
                                                {{ $n->association->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">{{ $n->notes ?: '-' }}</td>
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
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white p-3" style="background: linear-gradient(135deg, var(--hasad-primary) 0%, var(--hasad-primary-dark) 100%) !important;">
                <h5 class="modal-title fw-bold fs-6 text-white d-flex align-items-center gap-2">
                    <i class="fa-solid fa-plus-circle"></i>
                    <span>تسجيل مشتل زراعي جديد</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
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
                            <h6 class="fw-bold border-bottom pb-2 text-success">الموقع الجغرافي للمشتل</h6>
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
