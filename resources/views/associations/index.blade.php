@extends('layouts.app')

@section('title', 'سجل الجمعيات التعاونية - نظام حصاد')

@section('content')
<div class="container-fluid p-0">

    <!-- شريط العنوان والمسار والأزرار -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                <span class="text-success fs-5"><i class="fa-solid fa-building-columns"></i></span>
                سجل الجمعيات التعاونية
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">الرئيسية</a></li>
                    <li class="breadcrumb-item active" aria-current="page">الجمعيات التعاونية (الكيان المركزي)</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('associations.create') }}" class="btn btn-hasad-primary d-flex align-items-center gap-1 shadow-sm">
                <i class="fa-solid fa-plus-circle"></i>
                <span>تسجيل جمعية جديدة</span>
            </a>
            <a href="{{ route('geography.manage') }}" class="btn btn-hasad-outline d-flex align-items-center gap-1">
                <i class="fa-solid fa-network-wired"></i>
                <span>النطاق الجغرافي والقرى</span>
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

    <!-- بطاقات الإحصائيات المركزية -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card kpi-emerald h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-bold">إجمالي الجمعيات المسجلة</span>
                    <div class="kpi-icon-badge kpi-icon-emerald" style="width: 40px; height: 40px; font-size: 1.1rem;">
                        <i class="fa-solid fa-building-columns"></i>
                    </div>
                </div>
                <h3 class="stat-num text-dark mb-0">{{ number_format($stats['total_associations']) }} <span class="small fs-6 text-muted">جمعية</span></h3>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card kpi-emerald h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-bold">الجمعيات النشطة</span>
                    <div class="kpi-icon-badge kpi-icon-emerald" style="width: 40px; height: 40px; font-size: 1.1rem;">
                        <i class="fa-solid fa-circle-check"></i>
                    </div>
                </div>
                <h3 class="stat-num text-dark mb-0">{{ number_format($stats['active_associations']) }} <span class="small fs-6 text-muted">جمعية نشطة</span></h3>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card kpi-amber h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-bold">إجمالي المنتسبين والأعضاء</span>
                    <div class="kpi-icon-badge kpi-icon-amber" style="width: 40px; height: 40px; font-size: 1.1rem;">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
                <h3 class="stat-num text-dark mb-0">{{ number_format($stats['total_members']) }} <span class="small fs-6 text-muted">عضو</span></h3>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card kpi-blue h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-bold">إجمالي رأس المال الحالي</span>
                    <div class="kpi-icon-badge kpi-icon-blue" style="width: 40px; height: 40px; font-size: 1.1rem;">
                        <i class="fa-solid fa-coins"></i>
                    </div>
                </div>
                <h3 class="stat-num text-dark mb-0">{{ number_format($stats['total_capital']) }} <span class="small fs-6 text-muted">ر.ي</span></h3>
            </div>
        </div>
    </div>

    <!-- بطاقة البحث والفلترة المؤسسية (قابلة للطي) -->
    <div class="filter-card">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-filter text-success"></i>
                <h6 class="fw-bold mb-0 text-dark">تصفية وبحث متقدم في الجمعيات</h6>
                @if(request()->hasAny(['gov_id', 'dir_id', 'uzl_id', 'status', 'type', 'search']))
                    <span class="badge bg-success-subtle text-success border px-2 py-1 small">تصفية نشطة</span>
                @endif
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#filterFormCollapse" aria-expanded="true" aria-controls="filterFormCollapse">
                <i class="fa-solid fa-chevron-down"></i>
            </button>
        </div>
        <div class="collapse show" id="filterFormCollapse">
            <form method="GET" action="{{ route('associations.index') }}">
                <div class="row g-3">
                    <div class="col-12">
                        <x-location-hierarchy-selector 
                            :governorateId="request('gov_id')"
                            :directorateId="request('dir_id')"
                            :uzlahId="request('uzl_id')"
                            :showAddButton="false"
                        />
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-bold">حالة الجمعية</label>
                        <select name="status" class="form-select">
                            <option value="">-- جميع الحالات --</option>
                            <option value="نشطة" {{ request('status') === 'نشطة' ? 'selected' : '' }}>نشطة</option>
                            <option value="متوقفة" {{ request('status') === 'متوقفة' ? 'selected' : '' }}>متوقفة</option>
                            <option value="تحت التأسيس" {{ request('status') === 'تحت التأسيس' ? 'selected' : '' }}>تحت التأسيس</option>
                            <option value="متعثرة" {{ request('status') === 'متعثرة' ? 'selected' : '' }}>متعثرة</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-bold">نوع الجمعية</label>
                        <select name="type" class="form-select">
                            <option value="">-- جميع الأنواع --</option>
                            <option value="زراعية" {{ request('type') === 'زراعية' ? 'selected' : '' }}>زراعية</option>
                            <option value="متعددة الأغراض" {{ request('type') === 'متعددة الأغراض' ? 'selected' : '' }}>متعددة الأغراض</option>
                            <option value="تسويقية" {{ request('type') === 'تسويقية' ? 'selected' : '' }}>تسويقية</option>
                            <option value="حيوانية ونحلية" {{ request('type') === 'حيوانية ونحلية' ? 'selected' : '' }}>حيوانية ونحلية</option>
                            <option value="استهلاكية" {{ request('type') === 'استهلاكية' ? 'selected' : '' }}>استهلاكية</option>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-bold">بحث بالاسم أو الكود أو الرئيس</label>
                        <input type="text" name="search" class="form-control" placeholder="اسم الجمعية، الكود، رئيس الجمعية..." value="{{ request('search') }}">
                    </div>
                    <div class="col-12 col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-hasad-primary w-100">
                            <i class="fa-solid fa-magnifying-glass me-1"></i> بحث
                        </button>
                        <a href="{{ route('associations.index') }}" class="btn btn-outline-secondary" title="إلغاء التصفية">
                            <i class="fa-solid fa-rotate-right"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- جدول استعراض الجمعيات -->
    <div class="card-hasad">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="fa-solid fa-list-check text-success"></i>
                <span>قائمة الجمعيات التعاونية ({{ $associations->total() }})</span>
            </h6>
            <span class="badge bg-light text-dark border font-monospace">
                صفحة {{ $associations->currentPage() }} من {{ $associations->lastPage() ?: 1 }}
            </span>
        </div>
        <div class="card-body p-0">
            @if($associations->isEmpty())
                <div class="text-center py-5">
                    <i class="fa-solid fa-building-ngo text-muted fs-1 mb-3 d-block"></i>
                    <h5 class="text-dark fw-bold">لا توجد جمعيات تعاونية مسجلة تطابق معايير البحث</h5>
                    <p class="text-muted small">يمكنك تأسيس وتسجيل جمعية جديدة الآن بالنقر على الزر أدناه.</p>
                    <a href="{{ route('associations.create') }}" class="btn btn-hasad-primary btn-sm mt-2">
                        <i class="fa-solid fa-plus-circle me-1"></i> تسجيل جمعية جديدة الآن
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hasad align-middle mb-0 text-nowrap">
                        <thead>
                            <tr>
                                <th style="width: 40px;" class="text-center no-print">
                                    <i class="fa-solid fa-code-compare text-muted" title="تحديد للمقارنة"></i>
                                </th>
                                <th style="width: 120px;">الكود</th>
                                <th>اسم الجمعية الرسمي</th>
                                <th>النوع</th>
                                <th>المحافظة والمديرية</th>
                                <th>العزلة / المقر</th>
                                <th>رئيس الجمعية</th>
                                <th>الأعضاء</th>
                                <th>المشاريع والمجاميع</th>
                                <th>الحالة</th>
                                <th class="text-center" style="width: 120px;">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($associations as $item)
                                <tr>
                                    <td class="text-center no-print">
                                        <input type="checkbox" class="form-check-input assoc-compare-check" 
                                               data-id="{{ $item->id }}" 
                                               data-name="{{ $item->name }}" 
                                               data-code="{{ $item->code }}" 
                                               data-gov="{{ $item->governorate?->name_ar ?? '' }}" 
                                               data-capital="{{ number_format($item->current_capital) }}" 
                                               data-status="{{ $item->status }}"
                                               data-type="{{ $item->type }}"
                                               onchange="updateCompareDrawer()"
                                               title="تحديد للمقارنة السريعة">
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark font-monospace border copyable-badge" 
                                              onclick="copyToClipboard('{{ $item->code }}', this)" 
                                              title="انقر لنسخ الكود">
                                            {{ $item->code }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('associations.show', $item->id) }}" class="fw-bold text-dark text-decoration-none">
                                            {{ $item->name }}
                                        </a>
                                        @if($item->license_number)
                                            <span class="d-block small text-muted">ترخيص: {{ $item->license_number }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $item->type }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $item->governorate?->name_ar }}</div>
                                        <span class="small text-muted">{{ $item->directorate?->name_ar }}</span>
                                    </td>
                                    <td>
                                        <div>{{ $item->uzlah?->name_ar }}</div>
                                        <span class="small text-muted">{{ $item->subLocation?->name_ar ?? $item->address ?? '-' }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $item->president_name ?: '-' }}</div>
                                        @if($item->president_phone)
                                            <span class="small text-muted font-monospace"><i class="fa-solid fa-phone me-1 small"></i>{{ $item->president_phone }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge-status badge-status-info stat-num">
                                            <i class="fa-solid fa-users me-1"></i> {{ number_format($item->total_members) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1">
                                            <span class="badge-status badge-status-success stat-num" style="padding: 2px 8px; font-size: 0.72rem;">
                                                {{ $item->projects_count }} مشاريع
                                            </span>
                                            <span class="badge-status badge-status-info stat-num" style="padding: 2px 8px; font-size: 0.72rem;">
                                                {{ $item->productive_groups_count }} مجاميع
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($item->status === 'نشطة')
                                            <span class="badge-status badge-status-success">
                                                <span class="badge-status-dot"></span> نشطة
                                            </span>
                                        @elseif($item->status === 'تحت التأسيس')
                                            <span class="badge-status badge-status-warning">
                                                <span class="badge-status-dot"></span> تحت التأسيس
                                            </span>
                                        @elseif($item->status === 'متعثرة')
                                            <span class="badge-status badge-status-danger">
                                                <span class="badge-status-dot"></span> متعثرة
                                            </span>
                                        @else
                                            <span class="badge-status badge-status-info">
                                                <span class="badge-status-dot"></span> {{ $item->status }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <a href="{{ route('associations.show', $item->id) }}" class="btn-action-icon" title="عرض الملف الشامل (360° Profile)">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                            <a href="{{ route('associations.edit', $item->id) }}" class="btn-action-icon" title="تعديل">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($associations->hasPages())
                    <div class="card-footer bg-white py-3 border-0">
                        {{ $associations->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            @endif
        </div>
    </div>
    <!-- درج المقارنة السريعة للجمعيات (Quick Comparison Drawer) -->
    <div class="compare-drawer" id="assocCompareDrawer">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-code-compare text-success fs-5"></i>
                <h6 class="fw-bold mb-0 text-dark">مقارنة أداء الجمعيات المختارة (<span id="compareCount">0</span>)</h6>
                <span class="text-muted small">مقارنة المؤشرات التشغيلية والمالية جنباً إلى جنب</span>
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="clearComparison()">
                    <i class="fa-solid fa-xmark me-1"></i> إلغاء التحديد
                </button>
            </div>
        </div>
        <div class="row g-3" id="compareCardsContainer">
            <!-- كروت المقارنة يتم توليدها ديناميكياً -->
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function updateCompareDrawer() {
        const checks = document.querySelectorAll('.assoc-compare-check:checked');
        const drawer = document.getElementById('assocCompareDrawer');
        const countSpan = document.getElementById('compareCount');
        const container = document.getElementById('compareCardsContainer');
        if (!drawer || !countSpan || !container) return;

        countSpan.textContent = checks.length;
        if (checks.length >= 2) {
            drawer.classList.add('active');
            let html = '';
            checks.forEach(chk => {
                html += `
                    <div class="col-md-${checks.length === 2 ? '6' : '4'}">
                        <div class="card p-3 border shadow-sm h-100">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">${chk.dataset.name}</h6>
                                    <span class="badge bg-light text-dark border font-monospace small">${chk.dataset.code}</span>
                                </div>
                                <span class="badge bg-success-subtle text-success border small">${chk.dataset.status}</span>
                            </div>
                            <div class="small text-muted mb-1"><i class="fa-solid fa-location-dot me-1 text-success"></i>المحافظة: <strong class="text-dark">${chk.dataset.gov || '-'}</strong></div>
                            <div class="small text-muted mb-1"><i class="fa-solid fa-tags me-1 text-primary"></i>النوع: <strong class="text-dark">${chk.dataset.type || '-'}</strong></div>
                            <div class="small text-muted"><i class="fa-solid fa-coins me-1 text-warning"></i>رأس المال: <strong class="text-success">${chk.dataset.capital} ر.ي</strong></div>
                        </div>
                    </div>
                `;
            });
            container.innerHTML = html;
        } else {
            drawer.classList.remove('active');
        }
    }

    function clearComparison() {
        document.querySelectorAll('.assoc-compare-check:checked').forEach(c => c.checked = false);
        updateCompareDrawer();
    }
</script>
@endpush
