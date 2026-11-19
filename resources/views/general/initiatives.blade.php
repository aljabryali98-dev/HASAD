@extends('layouts.app')

@section('title', 'المبادرات المجتمعية والتنموية - نظام حصاد')

@section('content')
<div class="container-fluid p-0">
    <!-- الترويسة الرئيسية والمسار الإرشادي -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                <span class="text-success fs-5"><i class="fa-solid fa-hands-holding-child"></i></span>
                المبادرات المجتمعية والتنموية
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small text-muted">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">الرئيسية</a></li>
                    <li class="breadcrumb-item active text-success fw-bold" aria-current="page">المبادرات المجتمعية</li>
                </ol>
            </nav>
        </div>
        <div>
            <button type="button" class="btn btn-hasad-primary d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddInitiative">
                <i class="fa-solid fa-plus-circle"></i>
                <span>تسجيل مبادرة مجتمعية جديدة</span>
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
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="kpi-title">إجمالي المبادرات</div>
                        <div class="kpi-value text-dark stat-num">{{ number_format($stats['total']) }}</div>
                        <div class="kpi-subtitle">مبادرة تنموية معتمدة</div>
                    </div>
                    <div class="kpi-icon bg-light text-primary">
                        <i class="fa-solid fa-handshake-angle"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="kpi-title">المبادرون والمتطوعون</div>
                        <div class="kpi-value text-info stat-num">{{ number_format($stats['initiators']) }}</div>
                        <div class="kpi-subtitle">مشارك مجتمعي</div>
                    </div>
                    <div class="kpi-icon bg-info-subtle text-info">
                        <i class="fa-solid fa-people-carry-box"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="kpi-title">المستفيدون المباشرون</div>
                        <div class="kpi-value text-success stat-num">{{ number_format($stats['beneficiaries']) }}</div>
                        <div class="kpi-subtitle">مواطن ومستفيد محلي</div>
                    </div>
                    <div class="kpi-icon bg-success-subtle text-success">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="kpi-title">التكلفة الإجمالية</div>
                        <div class="kpi-value text-primary stat-num">{{ number_format($stats['total_cost']) }} <small class="fs-6 text-muted">ر.ي</small></div>
                        <div class="kpi-subtitle">مساهمات وتدخلات تنموية</div>
                    </div>
                    <div class="kpi-icon bg-primary-subtle text-primary">
                        <i class="fa-solid fa-vault"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- جدول سجل المبادرات المجتمعية -->
    <div class="card-hasad">
        <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-list-check text-success"></i>
                <h6 class="fw-bold mb-0 text-dark">سجل المبادرات المجتمعية المعتمدة</h6>
            </div>
            <span class="badge bg-light text-muted border px-3 py-2 rounded-pill font-monospace small">
                صفحة {{ $initiatives->currentPage() }} من {{ $initiatives->lastPage() }}
            </span>
        </div>
        <div class="card-body p-0">
            @if($initiatives->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-hands-holding-child fs-1 mb-2 d-block text-secondary opacity-50"></i>
                    <p class="mb-0 fw-medium">لا توجد مبادرات مسجلة حالياً.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table-hasad align-middle mb-0 text-nowrap">
                        <thead>
                            <tr>
                                <th>اسم المبادرة</th>
                                <th>النوع</th>
                                <th>المبادرون</th>
                                <th>المستفيدون</th>
                                <th>الموقع (المحافظة / المديرية)</th>
                                <th>الجمعية المرتبطة</th>
                                <th>نوع الدعم</th>
                                <th>التكلفة (ر.ي)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($initiatives as $init)
                                <tr>
                                    <td class="fw-bold text-dark">{{ $init->initiative_name }}</td>
                                    <td><span class="badge bg-light text-dark border">{{ $init->initiative_type ?: 'أخرى' }}</span></td>
                                    <td class="stat-num">{{ number_format($init->initiators_count) }}</td>
                                    <td><span class="badge-status-active stat-num fw-bold">{{ number_format($init->beneficiaries_count) }}</span></td>
                                    <td>{{ $init->governorate?->name_ar }} - {{ $init->directorate?->name_ar }} {{ $init->uzlah ? '('.$init->uzlah->name_ar.')' : '' }}</td>
                                    <td>
                                        @if($init->association)
                                            <a href="{{ route('associations.show', $init->association_id) }}" class="text-success text-decoration-none fw-bold">
                                                {{ $init->association->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $init->support_type ?: 'ذاتي مجتمعي' }}</td>
                                    <td class="fw-bold stat-num">{{ number_format($init->cost) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-top">
                    {{ $initiatives->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal: Add Initiative -->
<div class="modal fade" id="modalAddInitiative" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white p-3" style="background: linear-gradient(135deg, var(--hasad-primary) 0%, var(--hasad-primary-dark) 100%) !important;">
                <h5 class="modal-title fw-bold fs-6 text-white d-flex align-items-center gap-2">
                    <i class="fa-solid fa-plus-circle"></i>
                    <span>تسجيل مبادرة مجتمعية وتنموية جديدة</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('general.initiatives.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-bold">اسم المبادرة <span class="text-danger">*</span></label>
                            <input type="text" name="initiative_name" class="form-control" required placeholder="مثال: مبادرة رصف طريق عزلة بني عوير / صيانة السد">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">نوع المبادرة</label>
                            <select name="initiative_type" class="form-select">
                                <option value="زراعية">زراعية</option>
                                <option value="طرق ومياه">طرق ومياه</option>
                                <option value="خدمية تنموية">خدمية تنموية</option>
                                <option value="تأهيل قنوات">تأهيل قنوات وري</option>
                                <option value="أخرى">أخرى</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">عدد المبادرين / المتطوعين</label>
                            <input type="number" name="initiators_count" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">عدد الأسر / المستفيدين</label>
                            <input type="number" name="beneficiaries_count" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">التكلفة الإجمالية التقديرية (ر.ي)</label>
                            <input type="number" step="0.01" name="cost" class="form-control" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">نوع الدعم المقدم للمبادرة</label>
                            <input type="text" name="support_type" class="form-control" placeholder="ديزل، معدات، إسمنت، تمويل...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">الجهة المنفذة / المشرفة</label>
                            <input type="text" name="execution_entity" class="form-control" placeholder="اللجنة المجتمعية، الجمعية، فرسان التنمية...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">الجمعية التعاونية المرتبطة (اختياري)</label>
                            <select name="association_id" class="form-select">
                                <option value="">-- نشاط مجتمعي حر / بدون جمعية --</option>
                                @foreach($associations as $a)
                                    <option value="{{ $a->id }}">{{ $a->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">مصدر التمويل</label>
                            <input type="text" name="funding_source" class="form-control" placeholder="مساهمة مجتمعية، وحدة التدخلات، الجمعية...">
                        </div>

                        <div class="col-12 mt-3">
                            <h6 class="fw-bold border-bottom pb-2 text-success">النطاق الجغرافي للمبادرة</h6>
                            <x-location-hierarchy-selector 
                                :governorates="$governorates"
                                nameGov="governorate_id"
                                nameDir="directorate_id"
                                nameUzl="uzlah_id"
                                :required="true"
                            />
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold">ملاحظات ونتائج المبادرة</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="وصف موجز للمشروع أو التحديات"></textarea>
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
