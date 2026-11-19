@extends('layouts.app')

@section('title', 'جلسات التوعية المجتمعية والتطوع - نظام حصاد')

@section('content')
<div class="container-fluid p-0">
    <!-- الترويسة الرئيسية والمسار الإرشادي -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                <span class="text-success fs-5"><i class="fa-solid fa-bullhorn"></i></span>
                التوعية المجتمعية بأهمية التطوع والجمعيات التعاونية
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small text-muted">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none text-muted">الرئيسية</a></li>
                    <li class="breadcrumb-item active text-success fw-bold" aria-current="page">التوعية المجتمعية</li>
                </ol>
            </nav>
        </div>
        <div>
            <button type="button" class="btn btn-hasad-primary d-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddAwareness">
                <i class="fa-solid fa-plus-circle"></i>
                <span>تسجيل جلسة توعوية جديدة</span>
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
                        <div class="kpi-title">إجمالي الجلسات والفعاليات</div>
                        <div class="kpi-value text-dark stat-num">{{ number_format($stats['total_sessions']) }}</div>
                        <div class="kpi-subtitle">جلسة توعوية مسجلة</div>
                    </div>
                    <div class="kpi-icon bg-light text-primary">
                        <i class="fa-solid fa-chalkboard-user"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="kpi-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="kpi-title">إجمالي المستفيدين</div>
                        <div class="kpi-value text-success stat-num">{{ number_format($stats['beneficiaries']) }}</div>
                        <div class="kpi-subtitle">مشارك ومستفيد ميداني</div>
                    </div>
                    <div class="kpi-icon bg-success-subtle text-success">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="kpi-card h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="kpi-title">إجمالي التكلفة التقديرية</div>
                        <div class="kpi-value text-primary stat-num">{{ number_format($stats['total_cost']) }} <small class="fs-6 text-muted">ر.ي</small></div>
                        <div class="kpi-subtitle">تمويل ذاتي ومجتمعي</div>
                    </div>
                    <div class="kpi-icon bg-primary-subtle text-primary">
                        <i class="fa-solid fa-coins"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- جدول سجل الفعاليات والجلسات -->
    <div class="card-hasad">
        <div class="card-header bg-white py-3 px-4 d-flex justify-content-between align-items-center border-bottom">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-list-check text-success"></i>
                <h6 class="fw-bold mb-0 text-dark">سجل الفعاليات والجلسات التوعوية</h6>
            </div>
            <span class="badge bg-light text-muted border px-3 py-2 rounded-pill font-monospace small">
                صفحة {{ $sessions->currentPage() }} من {{ $sessions->lastPage() }}
            </span>
        </div>
        <div class="card-body p-0">
            @if($sessions->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-folder-open fs-1 mb-2 d-block text-secondary opacity-50"></i>
                    <p class="mb-0 fw-medium">لا توجد جلسات توعوية مسجلة حالياً.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table-hasad align-middle mb-0 text-nowrap">
                        <thead>
                            <tr>
                                <th>نوع التوعية</th>
                                <th>الفئة المستهدفة</th>
                                <th>عدد الجلسات</th>
                                <th>المستفيدين</th>
                                <th>الموقع الجغرافي</th>
                                <th>الجمعية المرتبطة</th>
                                <th>جهة التمويل</th>
                                <th>التكلفة (ر.ي)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sessions as $s)
                                <tr>
                                    <td>
                                        <span class="badge {{ $s->awareness_type === 'تطوع' ? 'badge-status-active' : 'bg-primary-subtle text-primary border' }}">
                                            {{ $s->awareness_type === 'تطوع' ? 'أهمية التطوع' : 'أهمية الجمعيات' }}
                                        </span>
                                    </td>
                                    <td class="fw-bold text-dark">{{ $s->target_group ?: 'عام' }}</td>
                                    <td class="stat-num">{{ number_format($s->sessions_count) }}</td>
                                    <td><span class="badge-status-active stat-num fw-bold">{{ number_format($s->beneficiaries_count) }}</span></td>
                                    <td>{{ $s->governorate?->name_ar }} - {{ $s->directorate?->name_ar }} {{ $s->uzlah ? '('.$s->uzlah->name_ar.')' : '' }}</td>
                                    <td>
                                        @if($s->association)
                                            <a href="{{ route('associations.show', $s->association_id) }}" class="text-success text-decoration-none fw-bold">
                                                {{ $s->association->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $s->funding_source ?: '-' }}</td>
                                    <td class="fw-bold stat-num">{{ number_format($s->cost) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-top">
                    {{ $sessions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Modal: Add Awareness Session -->
<div class="modal fade" id="modalAddAwareness" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white p-3" style="background: linear-gradient(135deg, var(--hasad-primary) 0%, var(--hasad-primary-dark) 100%) !important;">
                <h5 class="modal-title fw-bold fs-6 text-white d-flex align-items-center gap-2">
                    <i class="fa-solid fa-plus-circle"></i>
                    <span>إضافة جلسة / فعالية توعوية جديدة</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('general.awareness.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">نوع التوعية <span class="text-danger">*</span></label>
                            <select name="awareness_type" class="form-select" required>
                                <option value="تطوع">التوعية بأهمية التطوع</option>
                                <option value="جمعيات">التوعية بأهمية الجمعيات التعاونية</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">الفئة المستهدفة</label>
                            <input type="text" name="target_group" class="form-control" placeholder="مثل: المزارعون، الشباب، قادة المجتمع">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">عدد الجلسات <span class="text-danger">*</span></label>
                            <input type="number" name="sessions_count" class="form-control" value="1" min="1" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">عدد المستفيدين</label>
                            <input type="number" name="beneficiaries_count" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">التكلفة (ريال يمني)</label>
                            <input type="number" step="0.01" name="cost" class="form-control" value="0">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">الجهة الممولة / الداعمة</label>
                            <input type="text" name="funding_source" class="form-control" placeholder="ذاتي، مساهمة مجتمعية، جهة مانحة...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold">الجمعية التعاونية المرتبطة (اختياري)</label>
                            <select name="association_id" class="form-select">
                                <option value="">-- نشاط عام / غير مرتبط بجمعية --</option>
                                @foreach($associations as $a)
                                    <option value="{{ $a->id }}">{{ $a->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 mt-3">
                            <h6 class="fw-bold border-bottom pb-2 text-success">النطاق الجغرافي للجلسة</h6>
                            <x-location-hierarchy-selector 
                                :governorates="$governorates"
                                nameGov="governorate_id"
                                nameDir="directorate_id"
                                nameUzl="uzlah_id"
                                :required="true"
                            />
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold">ملاحظات إضافية</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="مخرجات الجلسة، القرارات المتخذة..."></textarea>
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
