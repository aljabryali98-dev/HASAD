@extends('layouts.app')

@section('title', 'جلسات التوعية المجتمعية والتطوع - نظام حصاد')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="fa-solid fa-bullhorn text-primary me-2"></i> التوعية بأهمية التطوع والجمعيات التعاونية
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item active">التوعية المجتمعية</li>
                </ol>
            </nav>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddAwareness">
            <i class="fa-solid fa-plus-circle me-1"></i> تسجيل جلسة توعوية جديدة
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
                <span class="text-muted small fw-bold">إجمالي الجلسات التوعوية</span>
                <h4 class="fw-bold text-dark mb-0">{{ number_format($stats['total_sessions']) }}</h4>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm p-3 text-center">
                <span class="text-muted small fw-bold">إجمالي المستفيدين</span>
                <h4 class="fw-bold text-success mb-0">{{ number_format($stats['beneficiaries']) }}</h4>
            </div>
        </div>
        <div class="col-sm-6 col-xl-4">
            <div class="card border-0 shadow-sm p-3 text-center">
                <span class="text-muted small fw-bold">إجمالي التكلفة التقديرية (ر.ي)</span>
                <h4 class="fw-bold text-primary mb-0">{{ number_format($stats['total_cost']) }}</h4>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark">سجل الفعاليات والجلسات التوعوية</h6>
            <span class="badge bg-light text-dark border">صفحة {{ $sessions->currentPage() }}</span>
        </div>
        <div class="card-body p-0">
            @if($sessions->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-folder-open fs-1 mb-2 d-block"></i>
                    لا توجد جلسات توعوية مسجلة حالياً.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-nowrap">
                        <thead class="table-light">
                            <tr>
                                <th>نوع التوعية</th>
                                <th>الفئة المستهدفة</th>
                                <th>عدد الجلسات</th>
                                <th>المستفيدين</th>
                                <th>الموقع الجغرافي</th>
                                <th>الجمعية</th>
                                <th>جهة التمويل</th>
                                <th>التكلفة (ر.ي)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sessions as $s)
                                <tr>
                                    <td>
                                        <span class="badge {{ $s->awareness_type === 'تطوع' ? 'bg-info text-dark' : 'bg-primary' }}">
                                            {{ $s->awareness_type === 'تطوع' ? 'أهمية التطوع' : 'أهمية الجمعيات' }}
                                        </span>
                                    </td>
                                    <td class="fw-bold text-dark">{{ $s->target_group ?: 'عام' }}</td>
                                    <td>{{ number_format($s->sessions_count) }}</td>
                                    <td><span class="badge bg-success bg-opacity-10 text-success fw-bold">{{ number_format($s->beneficiaries_count) }}</span></td>
                                    <td>{{ $s->governorate?->name_ar }} - {{ $s->directorate?->name_ar }} {{ $s->uzlah ? '('.$s->uzlah->name_ar.')' : '' }}</td>
                                    <td>
                                        @if($s->association)
                                            <a href="{{ route('associations.show', $s->association_id) }}" class="text-primary text-decoration-none fw-bold">
                                                {{ $s->association->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $s->funding_source ?: '-' }}</td>
                                    <td class="fw-bold">{{ number_format($s->cost) }}</td>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-plus-circle text-primary me-2"></i> إضافة جلسة / فعالية توعوية</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                            <h6 class="fw-bold border-bottom pb-2 text-primary">النطاق الجغرافي للجلسة</h6>
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
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i> حفظ وتحديث البيانات</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
