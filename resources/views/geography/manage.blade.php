@extends('layouts.app')

@section('title', 'إدارة النطاق الجغرافي والجمعيات والقرى - نظام حصاد')

@section('content')
<div class="container-fluid p-0">

    <!-- شريط العنوان والمسار والأزرار العلوية -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                <span class="text-success fs-5"><i class="fa-solid fa-network-wired"></i></span>
                إدارة النطاق الجغرافي والجمعيات والقرى
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('geography.index') }}" class="text-decoration-none">التقسيم الإداري</a></li>
                    <li class="breadcrumb-item active" aria-current="page">المستوى الرابع (القرى والجمعيات التعاونية والمواقع)</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-hasad-primary d-flex align-items-center gap-1 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddSingle">
                <i class="fa-solid fa-plus-circle"></i>
                <span>إضافة مسمى جديد</span>
            </button>
            <button type="button" class="btn btn-hasad-outline d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalAddBulk">
                <i class="fa-solid fa-layer-group"></i>
                <span>إضافة بالجملة (Bulk)</span>
            </button>
            <button type="button" class="btn btn-hasad-outline d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalImportExcel">
                <i class="fa-solid fa-file-excel text-success"></i>
                <span>استيراد ملف إكسل</span>
            </button>
            <a href="{{ route('geography.download_template') }}" class="btn btn-outline-secondary d-flex align-items-center gap-1" title="تحميل ملف CSV نموذجي">
                <i class="fa-solid fa-download"></i>
                <span>قالب الإكسل</span>
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4 rounded-3 border-0 shadow-sm" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2 fs-5 text-danger"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- بطاقات الإحصائيات الأربع -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card kpi-emerald h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-bold">المحافظات الرسمية</span>
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
                    <span class="text-muted small fw-bold">المديريات المعتمدة</span>
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
                    <span class="text-muted small fw-bold">العزل والمراكز</span>
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
                    <span class="text-muted small fw-bold">المستوى الرابع (قرى وجمعيات)</span>
                    <div class="kpi-icon-badge kpi-icon-blue" style="width: 40px; height: 40px; font-size: 1.1rem;">
                        <i class="fa-solid fa-sitemap"></i>
                    </div>
                </div>
                <h4 class="stat-num text-dark mb-0">{{ number_format($stats['sub_locations_count']) }} <span class="small fs-6 text-muted">موقع</span></h4>
            </div>
        </div>
    </div>

    <!-- شريط الفلترة والبحث المتقدم -->
    <div class="filter-card">
        <div class="d-flex align-items-center gap-2 mb-3">
            <i class="fa-solid fa-filter text-success"></i>
            <h6 class="fw-bold mb-0 text-dark">تصفية وبحث سريع في المستوى الرابع</h6>
        </div>
        <form method="GET" action="{{ route('geography.manage') }}">
            <div class="row g-3">
                <div class="col-12">
                    <x-location-hierarchy-selector 
                        :governorateId="$selectedGovId"
                        :directorateId="$selectedDirId"
                        :uzlahId="$selectedUzlId"
                        :showAddButton="false"
                    />
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-bold">نوع الكيان</label>
                    <select name="type" class="form-select">
                        <option value="">-- جميع الأنواع --</option>
                        <option value="قرية" {{ $entityType === 'قرية' ? 'selected' : '' }}>قرية</option>
                        <option value="جمعية تعاونية" {{ $entityType === 'جمعية تعاونية' ? 'selected' : '' }}>جمعية تعاونية</option>
                        <option value="محلة" {{ $entityType === 'محلة' ? 'selected' : '' }}>محلة</option>
                        <option value="منطقة زراعية" {{ $entityType === 'منطقة زراعية' ? 'selected' : '' }}>منطقة زراعية</option>
                        <option value="حي/حارة" {{ $entityType === 'حي/حارة' ? 'selected' : '' }}>حي / حارة</option>
                    </select>
                </div>
                <div class="col-12 col-md-5">
                    <label class="form-label small fw-bold">بحث بالاسم أو الكود أو الملاحظات</label>
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="اكتب اسم القرية، الجمعية، أو كود الموقع..." value="{{ $search }}">
                        <button type="submit" class="btn btn-hasad-primary">
                            <i class="fa-solid fa-magnifying-glass me-1"></i> بحث
                        </button>
                    </div>
                </div>
                <div class="col-12 col-md-3 d-flex align-items-end">
                    <a href="{{ route('geography.manage') }}" class="btn btn-outline-secondary w-100">
                        <i class="fa-solid fa-rotate-right me-1"></i> إعادة التعيين
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- جدول استعراض المواقع الفرعية والجمعيات -->
    <div class="card-hasad">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="fa-solid fa-list-check text-success"></i>
                <span>قائمة القرى والجمعيات التعاونية والمواقع الفرعية ({{ $subLocations->total() }})</span>
            </h6>
            <span class="badge bg-light text-dark border font-monospace">
                صفحة {{ $subLocations->currentPage() }} من {{ $subLocations->lastPage() ?: 1 }}
            </span>
        </div>
        <div class="card-body p-0">
            @if($subLocations->isEmpty())
                <div class="text-center py-5">
                    <i class="fa-solid fa-folder-open text-muted fs-1 mb-3 d-block"></i>
                    <h5 class="text-dark fw-bold">لا توجد مواقع فرعية مسجلة تطابق خيارات البحث</h5>
                    <p class="text-muted small">يمكنك إضافة قرى أو جمعيات جديدة الآن عبر الأزرار بالأعلى أو رفع ملف إكسل.</p>
                    <button type="button" class="btn btn-hasad-primary btn-sm mt-2" data-bs-toggle="modal" data-bs-target="#modalAddSingle">
                        <i class="fa-solid fa-plus-circle me-1"></i> إضافة أول مسمى الآن
                    </button>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hasad align-middle mb-0 text-nowrap">
                        <thead>
                            <tr>
                                <th style="width: 120px;">الكود</th>
                                <th>المسمى الرسمي</th>
                                <th>نوع الكيان</th>
                                <th>العزلة / المركز</th>
                                <th>المديرية</th>
                                <th>المحافظة</th>
                                <th>الحالة</th>
                                <th>ملاحظات</th>
                                <th class="text-center" style="width: 100px;">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($subLocations as $item)
                                <tr>
                                    <td>
                                        <span class="badge bg-light text-dark font-monospace border">{{ $item->code }}</span>
                                    </td>
                                    <td class="fw-bold text-dark">
                                        {{ $item->name_ar }}
                                    </td>
                                    <td>
                                        @if($item->entity_type === 'جمعية تعاونية')
                                            <span class="badge-status badge-status-success">
                                                <i class="fa-solid fa-handshake-angle me-1"></i> جمعية تعاونية
                                            </span>
                                        @elseif($item->entity_type === 'منطقة زراعية')
                                            <span class="badge-status badge-status-warning">
                                                <i class="fa-solid fa-wheat-awn me-1"></i> منطقة زراعية
                                            </span>
                                        @elseif($item->entity_type === 'محلة')
                                            <span class="badge-status badge-status-info">
                                                <i class="fa-solid fa-location-dot me-1"></i> محلة
                                            </span>
                                        @else
                                            <span class="badge-status badge-status-info">
                                                <i class="fa-solid fa-house-chimney me-1"></i> {{ $item->entity_type }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>{{ $item->uzlah?->name_ar ?? '-' }}</td>
                                    <td>{{ $item->directorate?->name_ar ?? '-' }}</td>
                                    <td>{{ $item->governorate?->name_ar ?? '-' }}</td>
                                    <td>
                                        @if($item->is_active)
                                            <span class="badge-status badge-status-success">
                                                <span class="badge-status-dot"></span> نشط
                                            </span>
                                        @else
                                            <span class="badge-status badge-status-warning">
                                                <span class="badge-status-dot"></span> غير نشط
                                            </span>
                                        @endif
                                    </td>
                                    <td class="small text-muted text-truncate" style="max-width: 150px;" title="{{ $item->notes }}">
                                        {{ $item->notes ?: '-' }}
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn-action-icon btn-edit-sub"
                                                data-id="{{ $item->id }}"
                                                data-name="{{ $item->name_ar }}"
                                                data-type="{{ $item->entity_type }}"
                                                data-notes="{{ $item->notes }}"
                                                data-active="{{ $item->is_active }}"
                                                title="تعديل المسمى">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                        <button type="button" class="btn-action-icon btn-action-danger btn-delete-sub ms-1"
                                                data-id="{{ $item->id }}"
                                                data-name="{{ $item->name_ar }}"
                                                title="حذف">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if($subLocations->hasPages())
                    <div class="card-footer bg-white py-3 border-0">
                        {{ $subLocations->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

<!-- 1. نافذة إضافة مسمى فردي جديد -->
<div class="modal fade" id="modalAddSingle" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
            <div class="modal-header text-white py-3" style="background: linear-gradient(135deg, var(--hasad-primary) 0%, var(--hasad-primary-hover) 100%);">
                <h6 class="modal-title fw-bold">
                    <i class="fa-solid fa-plus-circle me-2"></i> إضافة مسمى جديد للمستوى الرابع
                </h6>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <form method="POST" action="{{ route('geography.sub_locations.store') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">النطاق الجغرافي التابع له <span class="text-danger">*</span></label>
                        <x-location-hierarchy-selector 
                            :required="true"
                            :showAddButton="false"
                        />
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-md-7">
                            <label class="form-label small fw-bold">اسم القرية أو الجمعية أو الموقع <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="مثال: قرية البركة أو جمعية سنحان التعاونية" required>
                        </div>
                        <div class="col-12 col-md-5">
                            <label class="form-label small fw-bold">نوع الكيان <span class="text-danger">*</span></label>
                            <select name="entity_type" class="form-select" required>
                                <option value="قرية">قرية</option>
                                <option value="جمعية تعاونية">جمعية تعاونية</option>
                                <option value="محلة">محلة</option>
                                <option value="منطقة زراعية">منطقة زراعية</option>
                                <option value="حي/حارة">حي / حارة</option>
                                <option value="أخرى">أخرى</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">ملاحظات أو وصف إضافي (اختياري)</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="أي تفاصيل أو معلومات تخص الموقع..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-hasad-primary btn-sm">
                        <i class="fa-solid fa-check me-1"></i> حفظ المسمى الجديد
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 2. نافذة الإضافة بالجملة (Bulk Addition) -->
<div class="modal fade" id="modalAddBulk" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
            <div class="modal-header text-white py-3" style="background: linear-gradient(135deg, var(--hasad-primary) 0%, var(--hasad-primary-hover) 100%);">
                <h6 class="modal-title fw-bold">
                    <i class="fa-solid fa-layer-group me-2"></i> إضافة مسميات وقرى بالجملة (Bulk)
                </h6>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <form method="POST" action="{{ route('geography.sub_locations.bulk') }}">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 small mb-3 rounded-3">
                        <i class="fa-solid fa-circle-info me-1"></i> يمكنك نسخ ولصق قائمة كاملة من القرى أو الجمعيات لإدراجها دفعة واحدة تحت العزلة المختارة دون تكرار.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small">حدد النطاق الجغرافي المستهدف <span class="text-danger">*</span></label>
                        <x-location-hierarchy-selector 
                            :required="true"
                            :showAddButton="false"
                        />
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">نوع الكيان لهذه القائمة</label>
                        <select name="entity_type" class="form-select">
                            <option value="قرية">قرية</option>
                            <option value="جمعية تعاونية">جمعية تعاونية</option>
                            <option value="محلة">محلة</option>
                            <option value="منطقة زراعية">منطقة زراعية</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold">قائمة الأسماء (اسم واحد في كل سطر أو مفصولة بفواصل) <span class="text-danger">*</span></label>
                        <textarea name="names_list" class="form-control font-monospace" rows="6" placeholder="قرية بيت النصر&#10;قرية دار السلام&#10;قرية وادي الذهب&#10;جمعية الخير التعاونية" required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-hasad-primary btn-sm">
                        <i class="fa-solid fa-check-double me-1"></i> إضافة القائمة بالكامل
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 3. نافذة استيراد الإكسل الذكي -->
<div class="modal fade" id="modalImportExcel" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
            <div class="modal-header text-white py-3" style="background: linear-gradient(135deg, var(--hasad-primary) 0%, var(--hasad-primary-hover) 100%);">
                <h6 class="modal-title fw-bold">
                    <i class="fa-solid fa-file-excel me-2"></i> استيراد ملف إكسل / CSV
                </h6>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <form method="POST" action="{{ route('geography.import_excel') }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-success py-2 small mb-3 rounded-3">
                        <i class="fa-solid fa-wand-magic-sparkles me-1"></i>
                        <strong>ميزة التكامل الذكي:</strong> إذا وجد النظام أي قرية أو جمعية في ملفك غير مسجلة مسبقاً، سيقوم بإنشائها وتوليد كود رسمي لها تلقائياً دون إيقاف عملية الاستيراد.
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">اختر ملف Excel (.xlsx) أو CSV (.csv) <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.csv,.txt" required>
                    </div>
                    <div class="p-3 bg-light rounded-3 small">
                        <span class="fw-bold d-block mb-1 text-dark">ترتيب وتسمية الأعمدة المدعومة:</span>
                        <code class="text-success d-block">المحافظة | المديرية | العزلة | القرية أو الجمعية | نوع الكيان</code>
                        <div class="mt-2 text-muted">
                            يمكنك تنزيل النموذج المعتمد بالنقر على: 
                            <a href="{{ route('geography.download_template') }}" class="fw-bold text-success text-decoration-none">تحميل قالب إكسل جاهز</a>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-hasad-primary btn-sm">
                        <i class="fa-solid fa-cloud-arrow-up me-1"></i> بدء المعالجة والاستيراد
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 4. نافذة تعديل موقع فرعي -->
<div class="modal fade" id="modalEditSub" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
            <div class="modal-header text-white py-3" style="background: linear-gradient(135deg, var(--hasad-primary) 0%, var(--hasad-primary-hover) 100%);">
                <h6 class="modal-title fw-bold">
                    <i class="fa-solid fa-pen-to-square me-2"></i> تعديل بيانات الموقع الفرعي
                </h6>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <form id="formEditSub" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">اسم المسمى الرسمي <span class="text-danger">*</span></label>
                        <input type="text" id="editName" name="name_ar" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">نوع الكيان <span class="text-danger">*</span></label>
                        <select id="editType" name="entity_type" class="form-select" required>
                            <option value="قرية">قرية</option>
                            <option value="جمعية تعاونية">جمعية تعاونية</option>
                            <option value="محلة">محلة</option>
                            <option value="منطقة زراعية">منطقة زراعية</option>
                            <option value="حي/حارة">حي / حارة</option>
                            <option value="أخرى">أخرى</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">الحالة</label>
                        <select id="editActive" name="is_active" class="form-select">
                            <option value="1">نشط</option>
                            <option value="0">غير نشط</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-bold">ملاحظات</label>
                        <textarea id="editNotes" name="notes" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-hasad-primary btn-sm">
                        <i class="fa-solid fa-floppy-disk me-1"></i> حفظ التعديلات
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 5. نافذة تأكيد الحذف -->
<div class="modal fade" id="modalDeleteSub" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
            <div class="modal-header bg-danger text-white py-3">
                <h6 class="modal-title fw-bold">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i> تأكيد الحذف
                </h6>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>
            <form id="formDeleteSub" method="POST" action="">
                @csrf
                @method('DELETE')
                <div class="modal-body p-4 text-center">
                    <i class="fa-solid fa-trash-can text-danger fs-1 mb-3"></i>
                    <h6 class="fw-bold mb-2">هل أنت متأكد من رغبتك في حذف هذا الموقع الفرعي؟</h6>
                    <p class="text-danger fw-bold fs-5 mb-0" id="deleteItemName"></p>
                    <p class="text-muted small mt-2">لا يمكن التراجع عن هذا الإجراء بعد تنفيذه.</p>
                </div>
                <div class="modal-footer bg-light py-2 justify-content-center">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fa-solid fa-trash-can me-1"></i> نعم، حذف نهائي
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // تجهيز نافذة التعديل
        const modalEdit = new bootstrap.Modal(document.getElementById('modalEditSub'));
        const formEdit = document.getElementById('formEditSub');
        const editName = document.getElementById('editName');
        const editType = document.getElementById('editType');
        const editActive = document.getElementById('editActive');
        const editNotes = document.getElementById('editNotes');

        document.querySelectorAll('.btn-edit-sub').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                formEdit.action = `/geography/sub-locations/${id}`;
                editName.value = this.dataset.name || '';
                editType.value = this.dataset.type || 'قرية';
                editActive.value = this.dataset.active == '1' ? '1' : '0';
                editNotes.value = this.dataset.notes || '';
                modalEdit.show();
            });
        });

        // تجهيز نافذة الحذف
        const modalDelete = new bootstrap.Modal(document.getElementById('modalDeleteSub'));
        const formDelete = document.getElementById('formDeleteSub');
        const deleteItemName = document.getElementById('deleteItemName');

        document.querySelectorAll('.btn-delete-sub').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.dataset.id;
                formDelete.action = `/geography/sub-locations/${id}`;
                deleteItemName.textContent = this.dataset.name || '';
                modalDelete.show();
            });
        });
    });
</script>
@endpush
@endsection
