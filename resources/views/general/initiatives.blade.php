@extends('layouts.app')

@section('title', 'المبادرات المجتمعية - نظام حصاد')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="fa-solid fa-hands-holding-child text-primary me-2"></i> المبادرات المجتمعية والتنموية
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item active">المبادرات المجتمعية</li>
                </ol>
            </nav>
        </div>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAddInitiative">
            <i class="fa-solid fa-plus-circle me-1"></i> تسجيل مبادرة جديدة
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
                <span class="text-muted small fw-bold">إجمالي المبادرات</span>
                <h4 class="fw-bold text-dark mb-0">{{ number_format($stats['total']) }}</h4>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 text-center">
                <span class="text-muted small fw-bold">المبادرون والمتطوعون</span>
                <h4 class="fw-bold text-info mb-0">{{ number_format($stats['initiators']) }}</h4>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm p-3 text-center">
                <span class="text-muted small fw-bold">المستفيدون</span>
                <h4 class="fw-bold text-success mb-0">{{ number_format($stats['beneficiaries']) }}</h4>
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
            <h6 class="fw-bold mb-0 text-dark">سجل المبادرات المجتمعية المعتمدة</h6>
            <span class="badge bg-light text-dark border">صفحة {{ $initiatives->currentPage() }}</span>
        </div>
        <div class="card-body p-0">
            @if($initiatives->isEmpty())
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-folder-open fs-1 mb-2 d-block"></i>
                    لا توجد مبادرات مسجلة حالياً.
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 text-nowrap">
                        <thead class="table-light">
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
                                    <td><span class="badge bg-secondary">{{ $init->initiative_type ?: 'أخرى' }}</span></td>
                                    <td>{{ number_format($init->initiators_count) }}</td>
                                    <td><span class="badge bg-success bg-opacity-10 text-success fw-bold">{{ number_format($init->beneficiaries_count) }}</span></td>
                                    <td>{{ $init->governorate?->name_ar }} - {{ $init->directorate?->name_ar }} {{ $init->uzlah ? '('.$init->uzlah->name_ar.')' : '' }}</td>
                                    <td>
                                        @if($init->association)
                                            <a href="{{ route('associations.show', $init->association_id) }}" class="text-primary text-decoration-none fw-bold">
                                                {{ $init->association->name }}
                                            </a>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ $init->support_type ?: 'ذاتي مجتمعي' }}</td>
                                    <td class="fw-bold">{{ number_format($init->cost) }}</td>
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
    <div class="modal-dialog modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-plus-circle text-primary me-2"></i> تسجيل مبادرة مجتمعية جديدة</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
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
                            <h6 class="fw-bold border-bottom pb-2 text-primary">النطاق الجغرافي للمبادرة</h6>
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
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save me-1"></i> حفظ وتحديث البيانات</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
