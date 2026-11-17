@extends('layouts.app')

@section('title', 'سجل المزارعين والأراضي المزروعة - نظام حصاد')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="fa-solid fa-person-digging text-success me-2"></i> سجل المزارعين والأراضي المزروعة
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item active">المزارعون والأراضي</li>
                </ol>
            </nav>
        </div>
        <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalAddFarmer">
            <i class="fa-solid fa-user-plus me-1"></i> تسجيل مزارع جديد
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
                <span class="text-muted small fw-bold">إجمالي المزارعين</span>
                <h4 class="fw-bold text-dark mb-0">{{ number_format($stats['total_farmers']) }}</h4>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm p-3 text-center">
                <span class="text-muted small fw-bold">إجمالي المساحة المسجلة</span>
                <h4 class="fw-bold text-primary mb-0">{{ number_format($stats['total_area']) }} معاد/هكتار</h4>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm p-3 text-center">
                <span class="text-muted small fw-bold">المساحة المزروعة فعلياً</span>
                <h4 class="fw-bold text-success mb-0">{{ number_format($stats['cultivated_area']) }} معاد/هكتار</h4>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">قائمة المزارعين والأراضي التابعة</h6>
            <span class="badge bg-light text-dark border">صفحة {{ $farmers->currentPage() }}</span>
        </div>
        <div class="card-body p-0">
            @if($farmers->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-person-digging fs-1 mb-2 d-block"></i>
                    لا يوجد مزارعون مسجلون حالياً.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-nowrap">
                        <thead class="table-light">
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
                                            <a href="{{ route('associations.show', $f->association_id) }}" class="text-primary fw-bold text-decoration-none">
                                                {{ $f->association->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $f->governorate?->name_ar }} - {{ $f->directorate?->name_ar }}</td>
                                    <td class="font-monospace">{{ $f->phone ?: '-' }}</td>
                                    <td><span class="fw-bold">{{ $f->cultivated_area }}</span> {{ $f->area_unit }}</td>
                                    <td>{{ $f->main_crop_type ?: '-' }}</td>
                                    <td><span class="badge bg-info bg-opacity-10 text-info">{{ $f->irrigation_source }}</span></td>
                                    <td><span class="badge bg-light text-dark border">{{ $f->membership_status }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if($farmers->hasPages())
                    <div class="card-footer bg-white border-0">{{ $farmers->links('pagination::bootstrap-5') }}</div>
                @endif
            @endif
        </div>
    </div>
</div>

<!-- مودال تسجيل مزارع جديد -->
<div class="modal fade" id="modalAddFarmer" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow border-0 rounded-3">
            <div class="modal-header bg-success text-white py-3">
                <h6 class="modal-title fw-bold"><i class="fa-solid fa-user-plus me-2"></i> تسجيل مزارع جديد</h6>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal"></button>
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
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success btn-sm fw-bold"><i class="fa-solid fa-check me-1"></i> حفظ وتسجيل المزارع</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
