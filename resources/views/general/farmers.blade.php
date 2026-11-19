@extends('layouts.app')

@section('title', 'سجل المزارعين والأراضي المزروعة - نظام حصاد')

@section('content')
<div class="container-fluid p-0">
    <!-- الترويسة الرئيسية والمسار الإرشادي -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                <span class="text-success fs-5"><i class="fa-solid fa-person-digging"></i></span>
                سجل المزارعين والأراضي المزروعة
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small text-muted">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">الرئيسية</a></li>
                    <li class="breadcrumb-item active text-success fw-bold" aria-current="page">سجل المزارعين</li>
                </ol>
            </nav>
        </div>
        <div>
            <button type="button" class="btn btn-hasad-primary d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddFarmer">
                <i class="fa-solid fa-user-plus"></i>
                <span>تسجيل مزارع جديد</span>
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
                        <div class="kpi-title">إجمالي المزارعين</div>
                        <div class="kpi-value text-dark stat-num">{{ number_format($stats['total_farmers']) }}</div>
                        <div class="kpi-subtitle">مزارع مسجل في المنظومة</div>
                    </div>
                    <div class="kpi-icon bg-light text-primary">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="kpi-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="kpi-title">إجمالي المساحة المسجلة</div>
                        <div class="kpi-value text-primary stat-num">{{ number_format($stats['total_area']) }} <small class="fs-6 text-muted">معاد/هكتار</small></div>
                        <div class="kpi-subtitle">حيازات زراعية موثقة</div>
                    </div>
                    <div class="kpi-icon bg-primary-subtle text-primary">
                        <i class="fa-solid fa-chart-area"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="kpi-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="kpi-title">المساحة المزروعة فعلياً</div>
                        <div class="kpi-value text-success stat-num">{{ number_format($stats['cultivated_area']) }} <small class="fs-6 text-muted">معاد/هكتار</small></div>
                        <div class="kpi-subtitle">أراضٍ تحت الإنتاج النشط</div>
                    </div>
                    <div class="kpi-icon bg-success-subtle text-success">
                        <i class="fa-solid fa-seedling"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- جدول قائمة المزارعين -->
    <div class="card-hasad">
        <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-list-check text-success"></i>
                <h6 class="fw-bold mb-0 text-dark">قائمة المزارعين والحيازات الزراعية</h6>
            </div>
            <span class="badge bg-light text-muted border px-3 py-2 rounded-pill font-monospace small">
                صفحة {{ $farmers->currentPage() }} من {{ $farmers->lastPage() }}
            </span>
        </div>
        <div class="card-body p-0">
            @if($farmers->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-person-digging fs-1 mb-2 d-block text-secondary opacity-50"></i>
                    <p class="mb-0 fw-medium">لا يوجد مزارعون مسجلون حالياً.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table-hasad align-middle mb-0 text-nowrap">
                        <thead>
                            <tr>
                                <th>اسم المزارع الرباعي</th>
                                <th>الجمعية المرتبطة</th>
                                <th>الموقع (المديرية / العزلة)</th>
                                <th>رقم الهاتف</th>
                                <th>المساحة المزروعة</th>
                                <th>المحصول الرئيسي</th>
                                <th>مصدر الري</th>
                                <th>حالة العضوية</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($farmers as $f)
                                <tr>
                                    <td class="fw-bold text-dark">{{ $f->farmer_name }}</td>
                                    <td>
                                        @if($f->association)
                                            <a href="{{ route('associations.show', $f->association_id) }}" class="text-success fw-bold text-decoration-none">
                                                {{ $f->association->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $f->governorate?->name_ar }} - {{ $f->directorate?->name_ar }}</td>
                                    <td class="font-monospace text-muted">{{ $f->phone ?: '-' }}</td>
                                    <td><span class="fw-bold stat-num">{{ $f->cultivated_area }}</span> {{ $f->area_unit }}</td>
                                    <td>{{ $f->main_crop_type ?: '-' }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $f->irrigation_source }}</span></td>
                                    <td>
                                        <span class="badge {{ $f->membership_status === 'عضو' ? 'badge-status-active' : 'badge-status-pending' }}">
                                            {{ $f->membership_status }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-top">
                    {{ $farmers->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal: Add Farmer -->
<div class="modal fade" id="modalAddFarmer" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white p-3" style="background: linear-gradient(135deg, var(--hasad-primary) 0%, var(--hasad-primary-dark) 100%) !important;">
                <h5 class="modal-title fw-bold fs-6 text-white d-flex align-items-center gap-2">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>تسجيل مزارع جديد وحيازة زراعية</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('general.farmers.store') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-muted">اسم المزارع الرباعي <span class="text-danger">*</span></label>
                            <input type="text" name="farmer_name" class="form-control" placeholder="الاسم الرباعي" required>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label small fw-bold text-muted">رقم الهاتف</label>
                            <input type="text" name="phone" class="form-control" placeholder="770000000">
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label small fw-bold text-muted">رقم الهوية / البطاقة</label>
                            <input type="text" name="national_id" class="form-control">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold text-muted">الجمعية التابع لها (اختياري)</label>
                            <select name="association_id" class="form-select">
                                <option value="">-- اختياري للربط بالجمعية --</option>
                                @foreach($associations as $ass)
                                    <option value="{{ $ass->id }}">{{ $ass->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label small fw-bold text-muted">مصدر الري <span class="text-danger">*</span></label>
                            <select name="irrigation_source" class="form-select" required>
                                <option value="أمطار">أمطار (مطري)</option>
                                <option value="آبار">آبار ارتوازية</option>
                                <option value="سدود وحواجز">سدود وحواجز</option>
                                <option value="سيول">سيول</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-3">
                            <label class="form-label small fw-bold text-muted">حالة العضوية <span class="text-danger">*</span></label>
                            <select name="membership_status" class="form-select" required>
                                <option value="عضو">عضو مساهم</option>
                                <option value="منتسب">منتسب مستفيد</option>
                                <option value="غير عضو">غير عضو</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">النطاق الجغرافي <span class="text-danger">*</span></label>
                            <x-location-hierarchy-selector :required="true" :showAddButton="false" />
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-muted">المساحة المزروعة</label>
                            <input type="number" step="0.01" name="cultivated_area" class="form-control" value="0">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-muted">المساحة الإجمالية</label>
                            <input type="number" step="0.01" name="total_land_area" class="form-control" value="0">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-muted">وحدة المساحة <span class="text-danger">*</span></label>
                            <select name="area_unit" class="form-select" required>
                                <option value="معاد">معاد</option>
                                <option value="هكتار">هكتار</option>
                                <option value="فدان">فدان</option>
                                <option value="قصبة">قصبة</option>
                                <option value="لبنة">لبنة</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-hasad-primary px-4">
                        <i class="fa-solid fa-check me-1"></i> حفظ وتسجيل المزارع
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
