@extends('layouts.app')

@section('title', 'التقسيم الإداري والجغرافي المعتمد - نظام حصاد')

@section('content')
<div class="container-fluid p-0">

    <!-- شريط العنوان والمسار -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1 text-dark">
                <i class="fa-solid fa-map-location-dot text-primary me-2"></i> التقسيم الإداري والجغرافي المعتمد
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">الرئيسية</a></li>
                    <li class="breadcrumb-item active" aria-current="page">التقسيم الجغرافي الرسمي (22 محافظة / 333 مديرية)</li>
                </ol>
            </nav>
        </div>
        <div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDistrictModal">
                <i class="fa-solid fa-plus me-1"></i> إضافة مديرية
            </button>
            <button type="button" class="btn btn-outline-primary ms-1" data-bs-toggle="modal" data-bs-target="#addSubDistrictModal">
                <i class="fa-solid fa-plus me-1"></i> إضافة عزلة
            </button>
            <button type="button" class="btn btn-outline-secondary ms-1" data-bs-toggle="modal" data-bs-target="#addVillageModal">
                <i class="fa-solid fa-plus me-1"></i> إضافة قرية
            </button>
            <a href="{{ route('geography.manage') }}" class="btn btn-outline-info ms-1">
                <i class="fa-solid fa-network-wired me-1"></i> إدارة القرى والجمعيات (المستوى 4)
            </a>
        </div>
    </div>

    <!-- رسائل التنبيه والنجاح -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4" role="alert">
            <i class="fa-solid fa-circle-check me-2 fs-5"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- بطاقات الإحصائيات السريعة -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-3 me-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                        <i class="fa-solid fa-flag fs-4"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-bold">إجمالي المحافظات</span>
                        <h4 class="fw-bold mb-0 text-dark">{{ number_format($stats['governorates_count']) }} <span class="small fs-6 text-muted">محافظة</span></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="rounded-circle bg-success bg-opacity-10 text-success p-3 me-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                        <i class="fa-solid fa-city fs-4"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-bold">إجمالي المديريات</span>
                        <h4 class="fw-bold mb-0 text-dark">{{ number_format($stats['districts_count']) }} <span class="small fs-6 text-muted">مديرية</span></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="rounded-circle bg-warning bg-opacity-10 text-warning p-3 me-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                        <i class="fa-solid fa-signs-post fs-4"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-bold">إجمالي العزل والمراكز</span>
                        <h4 class="fw-bold mb-0 text-dark">{{ number_format($stats['sub_districts_count']) }} <span class="small fs-6 text-muted">عزلة/مركز</span></h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body d-flex align-items-center p-3">
                    <div class="rounded-circle bg-info bg-opacity-10 text-info p-3 me-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                        <i class="fa-solid fa-house-chimney-window fs-4"></i>
                    </div>
                    <div>
                        <span class="text-muted small fw-bold">إجمالي القرى المسجلة</span>
                        <h4 class="fw-bold mb-0 text-dark">{{ number_format($stats['villages_count']) }} <span class="small fs-6 text-muted">قرية/محلة</span></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- متصفح التقسيم الإداري الرسمي -->
    <div class="row g-4">
        <!-- عمود المحافظات (22) -->
        <div class="col-lg-4 col-xl-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-list me-1 text-primary"></i> المحافظات ({{ $governorates->count() }})
                    </h6>
                    <span class="badge bg-light text-secondary border">كود المحافظة</span>
                </div>
                <div class="list-group list-group-flush" style="max-height: 650px; overflow-y: auto;">
                    @foreach($governorates as $gov)
                        <a href="{{ route('geography.index', ['gov_id' => $gov->id]) }}" 
                           class="list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 px-3 {{ ($selectedGov && $selectedGov->id === $gov->id) ? 'active bg-primary border-primary' : '' }}">
                            <div>
                                <span class="badge {{ ($selectedGov && $selectedGov->id === $gov->id) ? 'bg-light text-primary' : 'bg-secondary bg-opacity-10 text-secondary' }} me-2 font-monospace">{{ $gov->code }}</span>
                                <span class="fw-bold">{{ $gov->name_ar ?: $gov->name }}</span>
                            </div>
                            <span class="badge {{ ($selectedGov && $selectedGov->id === $gov->id) ? 'bg-white text-primary' : 'bg-primary text-white' }} rounded-pill">
                                {{ $gov->directorates_count }} مديرية
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- عمود تفاصيل المديريات والعزل والقرى -->
        <div class="col-lg-8 col-xl-9">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">
                            محافظة: <span class="text-primary">{{ $selectedGov?->name_ar ?? $selectedGov?->name }}</span>
                            <span class="badge bg-secondary bg-opacity-10 text-secondary font-monospace ms-2">{{ $selectedGov?->code }}</span>
                        </h5>
                        <small class="text-muted">المديريات والعزل والمراكز التابعة رسمياً للمحافظة</small>
                    </div>
                    <span class="badge bg-light text-dark border px-3 py-2 fs-6">
                        عدد المديريات: <strong>{{ $directorates->count() }}</strong>
                    </span>
                </div>
                <div class="card-body p-3">
                    @if($directorates->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="fa-solid fa-folder-open fs-1 text-muted opacity-50 mb-3 d-block"></i>
                            <p class="mb-2">لا توجد مديريات مسجلة لهذه المحافظة.</p>
                            <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addDistrictModal">
                                <i class="fa-solid fa-plus me-1"></i> إضافة مديرية
                            </button>
                        </div>
                    @else
                        <div class="accordion" id="districtsAccordion">
                            @foreach($directorates as $index => $directorate)
                                <div class="accordion-item mb-2 border rounded-2 overflow-hidden shadow-none">
                                    <h2 class="accordion-header" id="heading-{{ $directorate->id }}">
                                        <button class="accordion-button {{ $index > 0 ? 'collapsed' : '' }} bg-light fw-bold text-dark py-2" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $directorate->id }}" aria-expanded="{{ $index === 0 ? 'true' : 'false' }}">
                                            <span class="badge bg-dark bg-opacity-75 text-white font-monospace me-2">{{ $directorate->code }}</span>
                                            <i class="fa-solid fa-location-dot text-primary me-2"></i>
                                            مديرية {{ $directorate->name_ar ?: $directorate->name }}
                                            <span class="badge {{ $directorate->uzlahs->isNotEmpty() ? 'bg-success' : 'bg-secondary bg-opacity-25 text-dark' }} ms-2">
                                                {{ $directorate->uzlahs->count() }} عزلة / مركز
                                            </span>
                                        </button>
                                    </h2>
                                    <div id="collapse-{{ $directorate->id }}" class="accordion-collapse collapse {{ $index === 0 ? 'show' : '' }}" data-bs-parent="#districtsAccordion">
                                        <div class="accordion-body bg-white p-3">
                                            @if($directorate->uzlahs->isEmpty())
                                                <div class="d-flex justify-content-between align-items-center alert alert-light text-muted small mb-0 py-2">
                                                    <span><i class="fa-solid fa-circle-info text-info me-1"></i> لم يتم ربط عزل تفصيلية لهذه المديرية بعد.</span>
                                                    <button type="button" class="btn btn-xs btn-outline-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addSubDistrictModal" onclick="selectDirectorate('{{ $directorate->id }}')">
                                                        <i class="fa-solid fa-plus me-1"></i> إضافة عزلة
                                                    </button>
                                                </div>
                                            @else
                                                <div class="table-responsive">
                                                    <table class="table table-sm table-bordered align-middle mb-0">
                                                        <thead class="table-light">
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
                                                                    <td class="font-monospace text-primary fw-bold">{{ $uzlah->code }}</td>
                                                                    <td class="fw-bold text-dark">
                                                                        <i class="fa-regular fa-folder-open text-warning me-1"></i>
                                                                        {{ $uzlah->name_ar ?: $uzlah->name }}
                                                                    </td>
                                                                    <td>
                                                                        <span class="badge {{ $uzlah->type === 'مركز مديرية' ? 'bg-primary' : 'bg-secondary bg-opacity-25 text-dark' }}">
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
    <div class="modal-dialog">
        <form action="{{ route('geography.districts.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addDistrictModalLabel">
                        <i class="fa-solid fa-plus-circle text-primary me-1"></i> إضافة مديرية جديدة
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">المحافظة <span class="text-danger">*</span></label>
                        <select name="governorate_id" class="form-select" required>
                            @foreach($governorates as $gov)
                                <option value="{{ $gov->id }}" {{ ($selectedGov && $selectedGov->id === $gov->id) ? 'selected' : '' }}>
                                    [{{ $gov->code }}] {{ $gov->name_ar ?: $gov->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">رمز/كود المديرية <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control font-monospace" required placeholder="مثال: 0217">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">اسم المديرية <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="اسم المديرية">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ المديرية</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- نافذة إضافة عزلة (Modal) -->
<div class="modal fade" id="addSubDistrictModal" tabindex="-1" aria-labelledby="addSubDistrictModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('geography.sub_districts.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addSubDistrictModalLabel">
                        <i class="fa-solid fa-plus-circle text-primary me-1"></i> إضافة عزلة جديدة
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">المحافظة</label>
                        <select id="modal_subdist_gov" class="form-select">
                            @foreach($governorates as $gov)
                                <option value="{{ $gov->id }}" {{ ($selectedGov && $selectedGov->id === $gov->id) ? 'selected' : '' }}>
                                    [{{ $gov->code }}] {{ $gov->name_ar ?: $gov->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">المديرية <span class="text-danger">*</span></label>
                        <select name="district_id" id="modal_subdist_dist" class="form-select" required>
                            @foreach($directorates as $dir)
                                <option value="{{ $dir->id }}">[{{ $dir->code }}] {{ $dir->name_ar ?: $dir->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">كود العزلة <span class="text-danger">*</span></label>
                        <input type="text" name="code" class="form-control font-monospace" required placeholder="مثال: 110117">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">اسم العزلة / المركز <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="اسم العزلة">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">النوع</label>
                        <select name="type" class="form-select">
                            <option value="عزلة">عزلة</option>
                            <option value="مركز مديرية">مركز مديرية</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ العزلة</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- نافذة إضافة قرية (Modal) -->
<div class="modal fade" id="addVillageModal" tabindex="-1" aria-labelledby="addVillageModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <form action="{{ route('geography.villages.store') }}" method="POST">
            @csrf
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addVillageModalLabel">
                        <i class="fa-solid fa-plus-circle text-primary me-1"></i> إضافة قرية / محلة جديدة
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">المحافظة</label>
                        <select id="modal_village_gov" class="form-select">
                            @foreach($governorates as $gov)
                                <option value="{{ $gov->id }}" {{ ($selectedGov && $selectedGov->id === $gov->id) ? 'selected' : '' }}>
                                    [{{ $gov->code }}] {{ $gov->name_ar ?: $gov->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">المديرية</label>
                        <select id="modal_village_dist" class="form-select">
                            @foreach($directorates as $dir)
                                <option value="{{ $dir->id }}">[{{ $dir->code }}] {{ $dir->name_ar ?: $dir->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">العزلة / المركز <span class="text-danger">*</span></label>
                        <select name="sub_district_id" id="modal_village_subdist" class="form-select" required>
                            @if($directorates->isNotEmpty() && $directorates->first()->uzlahs->isNotEmpty())
                                @foreach($directorates->first()->uzlahs as $u)
                                    <option value="{{ $u->id }}">[{{ $u->code }}] {{ $u->name_ar ?: $u->name }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">اسم القرية / المحلة <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required placeholder="مثال: بيت بوس، ورقة...">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-primary">حفظ القرية</button>
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
