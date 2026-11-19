@extends('layouts.app')

@section('title', 'التقسيم الإداري والجغرافي المعتمد - نظام حصاد')

@section('content')
<div class="container-fluid p-0">

    <!-- شريط العنوان والمسار -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                <span class="text-success fs-5"><i class="fa-solid fa-map-location-dot"></i></span>
                التقسيم الإداري والجغرافي المعتمد
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">الرئيسية</a></li>
                    <li class="breadcrumb-item active" aria-current="page">التقسيم الجغرافي الرسمي (22 محافظة / 333 مديرية)</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-hasad-primary d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#addDistrictModal">
                <i class="fa-solid fa-plus-circle"></i>
                <span>إضافة مديرية</span>
            </button>
            <button type="button" class="btn btn-hasad-outline d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addSubDistrictModal">
                <i class="fa-solid fa-plus"></i>
                <span>إضافة عزلة</span>
            </button>
            <button type="button" class="btn btn-hasad-outline d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addVillageModal">
                <i class="fa-solid fa-plus"></i>
                <span>إضافة قرية</span>
            </button>
            <a href="{{ route('geography.manage') }}" class="btn btn-outline-secondary d-flex align-items-center gap-1">
                <i class="fa-solid fa-network-wired"></i>
                <span>إدارة القرى والجمعيات (المستوى 4)</span>
            </a>
        </div>
    </div>

    <!-- رسائل التنبيه والنجاح -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4 rounded-3 border-0 shadow-sm" role="alert">
            <i class="fa-solid fa-circle-check me-2 fs-5 text-success"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- بطاقات الإحصائيات السريعة -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card kpi-emerald h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-bold">إجمالي المحافظات</span>
                    <div class="kpi-icon-badge kpi-icon-emerald" style="width: 40px; height: 40px; font-size: 1.1rem;">
                        <i class="fa-solid fa-flag"></i>
                    </div>
                </div>
                <h4 class="stat-num text-dark mb-0">{{ number_format($stats['governorates_count']) }} <span class="small fs-6 text-muted">محافظة</span></h4>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card kpi-emerald h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-bold">إجمالي المديريات</span>
                    <div class="kpi-icon-badge kpi-icon-emerald" style="width: 40px; height: 40px; font-size: 1.1rem;">
                        <i class="fa-solid fa-city"></i>
                    </div>
                </div>
                <h4 class="stat-num text-dark mb-0">{{ number_format($stats['districts_count']) }} <span class="small fs-6 text-muted">مديرية</span></h4>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card kpi-amber h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-bold">إجمالي العزل والمراكز</span>
                    <div class="kpi-icon-badge kpi-icon-amber" style="width: 40px; height: 40px; font-size: 1.1rem;">
                        <i class="fa-solid fa-signs-post"></i>
                    </div>
                </div>
                <h4 class="stat-num text-dark mb-0">{{ number_format($stats['sub_districts_count']) }} <span class="small fs-6 text-muted">عزلة/مركز</span></h4>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card kpi-blue h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-bold">إجمالي القرى المسجلة</span>
                    <div class="kpi-icon-badge kpi-icon-blue" style="width: 40px; height: 40px; font-size: 1.1rem;">
                        <i class="fa-solid fa-house-chimney-window"></i>
                    </div>
                </div>
                <h4 class="stat-num text-dark mb-0">{{ number_format($stats['villages_count']) }} <span class="small fs-6 text-muted">قرية/محلة</span></h4>
            </div>
        </div>
    </div>

    <!-- متصفح التقسيم الإداري الرسمي -->
    <div class="row g-4">
        <!-- عمود المحافظات (22) -->
        <div class="col-lg-4 col-xl-3">
            <div class="card-hasad">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-1">
                        <i class="fa-solid fa-list text-success"></i>
                        <span>المحافظات ({{ $governorates->count() }})</span>
                    </h6>
                    <span class="badge bg-light text-secondary border font-monospace">الكود</span>
                </div>
                <div class="list-group list-group-flush" style="max-height: 650px; overflow-y: auto;">
                    @foreach($governorates as $gov)
                        <a href="{{ route('geography.index', ['gov_id' => $gov->id]) }}" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 px-3 border-bottom {{ ($selectedGov && $selectedGov->id === $gov->id) ? 'active bg-success border-success text-white' : '' }}">
                            <div>
                                <span class="badge {{ ($selectedGov && $selectedGov->id === $gov->id) ? 'bg-light text-success' : 'bg-light text-dark' }} me-2 font-monospace border">{{ $gov->code }}</span>
                                <span class="fw-bold">{{ $gov->name_ar ?: $gov->name }}</span>
                            </div>
                            <span class="badge {{ ($selectedGov && $selectedGov->id === $gov->id) ? 'bg-white text-success' : 'bg-light text-dark border' }} rounded-pill font-monospace">
                                {{ $gov->directorates_count }} مديرية
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- عمود تفاصيل المديريات والعزل والقرى -->
        <div class="col-lg-8 col-xl-9">
            <div class="card-hasad">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">
                            محافظة: <span class="text-success">{{ $selectedGov?->name_ar ?? $selectedGov?->name }}</span>
                            <span class="badge bg-light text-dark font-monospace border ms-2">{{ $selectedGov?->code }}</span>
                        </h5>
                        <small class="text-muted">المديريات والعزل والمراكز التابعة رسمياً للمحافظة</small>
                    </div>
                    <span class="badge bg-light text-dark border px-3 py-2 fs-6 font-monospace">
                        عدد المديريات: <strong>{{ $directorates->count() }}</strong>
                    </span>
                </div>
                <div class="card-body p-3">
                    @if($directorates->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="fa-solid fa-folder-open fs-1 text-muted opacity-50 mb-3 d-block"></i>
                            <p class="mb-2">لا توجد مديريات مسجلة لهذه المحافظة.</p>
                            <button type="button" class="btn btn-sm btn-hasad-primary" data-bs-toggle="modal" data-bs-target="#addDistrictModal">
                                <i class="fa-solid fa-plus me-1"></i> إضافة مديرية
                            </button>
                        </div>
                    @else
                        <div class="accordion" id="districtsAccordion">
                            @foreach($directorates as $index => $directorate)
                                <div class="accordion-item mb-2 border rounded-3 overflow-hidden shadow-none">
                                    <h2 class="accordion-header" id="heading-{{ $directorate->id }}">
                                        <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }} bg-light fw-bold text-dark py-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $directorate->id }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}">
                                            <span class="badge bg-dark text-white font-monospace me-2">{{ $directorate->code }}</span>
                                            <i class="fa-solid fa-location-dot text-success me-2"></i>
                                            مديرية {{ $directorate->name_ar ?: $directorate->name }}
                                            <span class="badge-status badge-status-success ms-2">
                                                {{ $directorate->uzlahs->count() }} عزلة / مركز
                                            </span>
                                        </button>
                                    </h2>
                                    <div id="collapse-{{ $directorate->id }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" data-bs-parent="#districtsAccordion">
                                        <div class="accordion-body bg-white p-3">
                                            @if($directorate->uzlahs->isEmpty())
                                                <div class="d-flex justify-content-between align-items-center alert alert-light text-muted small mb-0 py-2 rounded-3 border">
                                                    <span><i class="fa-solid fa-circle-info text-info me-1"></i> لم يتم ربط عزل تفصيلية لهذه المديرية بعد.</span>
                                                    <button type="button" class="btn btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSubDistrictModal" onclick="selectDirectorate('{{ $directorate->id }}')">
                                                        <i class="fa-solid fa-plus me-1"></i> إضافة عزلة
                                                    </button>
                                                </div>
                                            @else
                                                <div class="table-responsive">
                                                    <table class="table table-hasad align-middle mb-0">
                                                        <thead>
                                                            <tr>
                                                                <th style="width: 15%;">الكود</th>
                                                                <th style="width: 25%;">اسم العزلة / المركز</th>
                                                                <th style="width: 20%;">النوع</th>
                                                                <th>القرى والمحلات</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            @foreach($directorate->uzlahs as $uzlah)
                                                                <tr>
                                                                    <td class="font-monospace text-success fw-bold">{{ $uzlah->code }}</td>
                                                                    <td class="fw-bold text-dark">
                                                                        <i class="fa-regular fa-folder-open text-warning me-1"></i>
                                                                        {{ $uzlah->name_ar ?: $uzlah->name }}
                                                                    </td>
                                                                    <td>
                                                                        <span class="badge-status badge-status-info">
                                                                            {{ $uzlah->type }}
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        @if($uzlah->villages->isEmpty())
                                                                            <span class="text-muted small">لا توجد قرى مسجلة</span>
                                                                        @else
                                                                            <div class="d-flex flex-wrap gap-1">
                                                                                @foreach($uzlah->villages as $village)
                                                                                    <span class="badge bg-light text-dark border">
                                                                                        <i class="fa-solid fa-house-chimney text-muted me-1"></i> {{ $village->name }}
                                                                                    </span>
                                                                                @endforeach
                                                                            </div>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>

<!-- نافذة إضافة مديرية (Modal) -->
<div class="modal fade" id="addDistrictModal" tabindex="-1" aria-labelledby="addDistrictModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('geography.districts.store') }}" method="POST">
            @csrf
            <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="modal-header text-white py-3" style="background: linear-gradient(135deg, var(--hasad-primary) 0%, var(--hasad-primary-hover) 100%);">
                    <h6 class="modal-title fw-bold" id="addDistrictModalLabel">
                        <i class="fa-solid fa-plus-circle me-1"></i> إضافة مديرية جديدة
                    </h6>
                    <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">المحافظة <span class="text-danger">*</span></label>
                        <select name="governorate_id" class="form-select" required>
                            @foreach($governorates as $gov)
                                <option value="{{ $gov->id }}" {{ ($selectedGov && $selectedGov->id === $gov->id) ? 'selected' : '' }}>
                                    [{{ $gov->code }}] {{ $gov->name_ar ?: $gov->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">رمز/كود المديرية <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control font-monospace" required placeholder="مثال: 0217">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">اسم المديرية <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="اسم المديرية">
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-hasad-primary btn-sm">حفظ المديرية</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- نافذة إضافة عزلة (Modal) -->
<div class="modal fade" id="addSubDistrictModal" tabindex="-1" aria-labelledby="addSubDistrictModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('geography.sub_districts.store') }}" method="POST">
            @csrf
            <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="modal-header text-white py-3" style="background: linear-gradient(135deg, var(--hasad-primary) 0%, var(--hasad-primary-hover) 100%);">
                    <h6 class="modal-title fw-bold" id="addSubDistrictModalLabel">
                        <i class="fa-solid fa-plus-circle me-1"></i> إضافة عزلة جديدة
                    </h6>
                    <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">المحافظة</label>
                        <select id="modal_subdist_gov" class="form-select">
                            @foreach($governorates as $gov)
                                <option value="{{ $gov->id }}" {{ ($selectedGov && $selectedGov->id === $gov->id) ? 'selected' : '' }}>
                                    [{{ $gov->code }}] {{ $gov->name_ar ?: $gov->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">المديرية <span class="text-danger">*</span></label>
                        <select name="district_id" id="modal_subdist_dist" class="form-select" required>
                            @foreach($directorates as $dir)
                                <option value="{{ $dir->id }}">[{{ $dir->code }}] {{ $dir->name_ar ?: $dir->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">كود العزلة <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control font-monospace" required placeholder="مثال: 110117">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">اسم العزلة / المركز <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="اسم العزلة">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">النوع</label>
                        <select name="type" class="form-select">
                            <option value="عزلة">عزلة</option>
                            <option value="مركز مديرية">مركز مديرية</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-hasad-primary btn-sm">حفظ العزلة</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- نافذة إضافة قرية (Modal) -->
<div class="modal fade" id="addVillageModal" tabindex="-1" aria-labelledby="addVillageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('geography.villages.store') }}" method="POST">
            @csrf
            <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
                <div class="modal-header text-white py-3" style="background: linear-gradient(135deg, var(--hasad-primary) 0%, var(--hasad-primary-hover) 100%);">
                    <h6 class="modal-title fw-bold" id="addVillageModalLabel">
                        <i class="fa-solid fa-plus-circle me-1"></i> إضافة قرية / محلة جديدة
                    </h6>
                    <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">المحافظة</label>
                        <select id="modal_village_gov" class="form-select">
                            @foreach($governorates as $gov)
                                <option value="{{ $gov->id }}" {{ ($selectedGov && $selectedGov->id === $gov->id) ? 'selected' : '' }}>
                                    [{{ $gov->code }}] {{ $gov->name_ar ?: $gov->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">المديرية</label>
                        <select id="modal_village_dist" class="form-select">
                            @foreach($directorates as $dir)
                                <option value="{{ $dir->id }}">[{{ $dir->code }}] {{ $dir->name_ar ?: $dir->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">العزلة / المركز <span class="text-danger">*</span></label>
                        <select name="sub_district_id" id="modal_village_subdist" class="form-select" required>
                            @if($directorates->isNotEmpty() && $directorates->first()->uzlahs->isNotEmpty())
                                @foreach($directorates->first()->uzlahs as $u)
                                    <option value="{{ $u->id }}">[{{ $u->code }}] {{ $u->name_ar ?: $u->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">اسم القرية / المحلة <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="مثال: بيت بوس، ورقة...">
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-hasad-primary btn-sm">حفظ القرية</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function selectDirectorate(dirId) {
    $('#modal_subdist_dist').val(dirId);
}

$(document).ready(function() {
    initCascadingGeography({
        governorate: '#modal_subdist_gov',
        district: '#modal_subdist_dist'
    });

    initCascadingGeography({
        governorate: '#modal_village_gov',
        district: '#modal_village_dist',
        subDistrict: '#modal_village_subdist'
    });
});
</script>
@endpush
