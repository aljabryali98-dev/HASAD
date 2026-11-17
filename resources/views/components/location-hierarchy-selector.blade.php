@props([
    'namePrefix' => '',
    'governorateId' => null,
    'directorateId' => null,
    'uzlahId' => null,
    'subLocationId' => null,
    'required' => false,
    'showAddButton' => true,
    'entityType' => null,
    'id' => 'location-selector-' . uniqid(),
])

@php
    $govName = $namePrefix ? "{$namePrefix}[governorate_id]" : 'governorate_id';
    $dirName = $namePrefix ? "{$namePrefix}[directorate_id]" : 'directorate_id';
    $uzlName = $namePrefix ? "{$namePrefix}[uzlah_id]" : 'uzlah_id';
    $subName = $namePrefix ? "{$namePrefix}[sub_location_id]" : 'sub_location_id';
    $allGovs = \App\Models\Governorate::where('is_active', true)->orderBy('id')->get();
@endphp

<div id="{{ $id }}" class="location-hierarchy-selector" 
     data-entity-type="{{ $entityType ?? '' }}"
     data-selected-gov="{{ $governorateId ?? '' }}"
     data-selected-dir="{{ $directorateId ?? '' }}"
     data-selected-uzl="{{ $uzlahId ?? '' }}"
     data-selected-sub="{{ $subLocationId ?? '' }}">

    <div class="row g-3">
        <!-- 1. المحافظة -->
        <div class="col-12 col-md-3">
            <label class="form-label small fw-bold text-muted">
                <i class="fa-solid fa-flag text-primary me-1"></i> المحافظة @if($required)<span class="text-danger">*</span>@endif
            </label>
            <select name="{{ $govName }}" class="form-select selector-gov" @if($required) required @endif>
                <option value="">-- اختر المحافظة --</option>
                @foreach($allGovs as $gov)
                    <option value="{{ $gov->id }}" {{ (string)$governorateId === (string)$gov->id ? 'selected' : '' }}>
                        {{ $gov->name_ar ?? $gov->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- 2. المديرية -->
        <div class="col-12 col-md-3">
            <label class="form-label small fw-bold text-muted">
                <i class="fa-solid fa-city text-success me-1"></i> المديرية @if($required)<span class="text-danger">*</span>@endif
            </label>
            <select name="{{ $dirName }}" class="form-select selector-dir" @if($required) required @endif {{ empty($governorateId) ? 'disabled' : '' }}>
                <option value="">-- اختر المديرية --</option>
            </select>
        </div>

        <!-- 3. العزلة / المركز -->
        <div class="col-12 col-md-3">
            <label class="form-label small fw-bold text-muted">
                <i class="fa-solid fa-signs-post text-warning me-1"></i> العزلة / المركز @if($required)<span class="text-danger">*</span>@endif
            </label>
            <select name="{{ $uzlName }}" class="form-select selector-uzl" @if($required) required @endif {{ empty($directorateId) ? 'disabled' : '' }}>
                <option value="">-- اختر العزلة --</option>
            </select>
        </div>

        <!-- 4. القرية / الجمعية / الموقع الفرعي -->
        <div class="col-12 col-md-3">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <label class="form-label small fw-bold text-muted mb-0">
                    <i class="fa-solid fa-house-chimney-window text-info me-1"></i> القرية / الجمعية @if($required)<span class="text-danger">*</span>@endif
                </label>
                @if($showAddButton)
                    <button type="button" class="btn btn-sm btn-link text-primary p-0 text-decoration-none fw-bold btn-open-quick-add" style="display: none;">
                        <i class="fa-solid fa-plus-circle me-1"></i> إضافة جديد
                    </button>
                @endif
            </div>
            <div class="input-group">
                <select name="{{ $subName }}" class="form-select selector-sub" @if($required) required @endif {{ empty($uzlahId) ? 'disabled' : '' }}>
                    <option value="">-- اختر القرية / الجمعية --</option>
                </select>
                @if($showAddButton)
                    <button type="button" class="btn btn-outline-primary btn-open-quick-add" title="إضافة سريعة" style="display: none;">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                @endif
            </div>
        </div>
    </div>

    @if($showAddButton)
        <!-- النافذة المصغرة للإضافة السريعة (Inline Modal) -->
        <div class="modal fade modal-quick-add-sub" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content shadow border-0 rounded-3">
                    <div class="modal-header bg-light py-2">
                        <h6 class="modal-title fw-bold text-dark">
                            <i class="fa-solid fa-plus-circle text-primary me-2"></i>
                            إضافة مسمى جديد (المستوى الرابع)
                        </h6>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                    </div>
                    <form class="form-quick-add-sub">
                        <div class="modal-body p-3">
                            <div class="alert alert-danger py-2 small mb-3 error-feedback d-none"></div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">اسم المسمى (القرية أو الجمعية أو المحلة) <span class="text-danger">*</span></label>
                                <input type="text" class="form-control input-sub-name" placeholder="مثال: قرية الأمل أو جمعية النصر التعاونية" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-bold text-muted">نوع الكيان</label>
                                <select class="form-select select-sub-type">
                                    <option value="قرية">قرية</option>
                                    <option value="جمعية تعاونية">جمعية تعاونية</option>
                                    <option value="محلة">محلة</option>
                                    <option value="منطقة زراعية">منطقة زراعية</option>
                                    <option value="حي/حارة">حي / حارة</option>
                                    <option value="أخرى">أخرى</option>
                                </select>
                            </div>

                            <div class="mb-2">
                                <label class="form-label small fw-bold text-muted">ملاحظات (اختياري)</label>
                                <textarea class="form-control input-sub-notes" rows="2" placeholder="أي معلومات إضافية..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer bg-light py-2">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">إلغاء</button>
                            <button type="submit" class="btn btn-primary btn-sm btn-submit-sub">
                                <i class="fa-solid fa-check me-1"></i> حفظ وتحديد تلقائي
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
