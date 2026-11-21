@extends('layouts.app')

@section('title', 'إضافة مستخدم جديد وتحديد الصلاحيات - نظام حصاد')

@section('content')
<div class="container-fluid p-0">

    <!-- الترويسة الرئيسية والمسار والأزرار -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                <span class="text-success fs-5"><i class="fa-solid fa-user-plus"></i></span>
                إضافة مستخدم جديد وتحديد الصلاحيات
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('users.index') }}" class="text-decoration-none">إدارة المستخدمين</a></li>
                    <li class="breadcrumb-item active" aria-current="page">مستخدم جديد</li>
                </ol>
            </nav>
        </div>
        <div>
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary d-flex align-items-center gap-1">
                <i class="fa-solid fa-arrow-right"></i>
                <span>العودة لقائمة المستخدمين</span>
            </a>
        </div>
    </div>

    <!-- أخطاء التحقق إن وجدت -->
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4 rounded-3 border-0 shadow-sm" role="alert">
            <div class="fw-bold mb-1"><i class="fa-solid fa-circle-exclamation me-1"></i> يرجى تصحيح الأخطاء التالية:</div>
            <ul class="mb-0 small ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form method="POST" action="{{ route('users.store') }}" id="userForm">
        @csrf

        <div class="row g-4">
            
            <!-- العمود الأيمن: البيانات الأساسية والدور المؤسسي -->
            <div class="col-lg-4">
                <div class="card-hasad p-4 mb-4">
                    <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                        <i class="fa-solid fa-id-card text-success"></i>
                        <span>البيانات الأساسية للحساب</span>
                    </h5>

                    <!-- الاسم الكامل -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted required">اسم المستخدم الكامل</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name') }}" placeholder="مثال: م. علي صالح" required>
                    </div>

                    <!-- البريد الإلكتروني -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted required">البريد الإلكتروني (اسم الدخول)</label>
                        <input type="email" name="email" class="form-control font-monospace" value="{{ old('email') }}" placeholder="user@hasad.local" required>
                    </div>

                    <!-- كلمة المرور -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted required">كلمة المرور الأولية</label>
                        <input type="password" name="password" class="form-control font-monospace" placeholder="••••••••" required minlength="6">
                        <span class="text-muted small" style="font-size: 0.72rem;">الحد الأدنى 6 خانات</span>
                    </div>

                    <!-- رقم الهاتف -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">رقم الهاتف / الواتساب</label>
                        <input type="text" name="phone" class="form-control font-monospace" value="{{ old('phone') }}" placeholder="777000000">
                    </div>

                    <!-- حالة التفعيل -->
                    <div class="mb-4 pt-2 border-top">
                        <div class="form-check form-switch d-flex align-items-center justify-content-between p-0">
                            <label class="form-check-label fw-bold small text-dark" for="isActiveSwitch">
                                حالة تفعيل الحساب
                            </label>
                            <input class="form-check-input ms-0" type="checkbox" role="switch" id="isActiveSwitch" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}>
                        </div>
                        <span class="text-muted small d-block" style="font-size: 0.75rem;">الحسابات المعطلة لا تستطيع تسجيل الدخول إطلاقاً</span>
                    </div>

                    <!-- اختيار الدور المؤسسي (Role Preset) -->
                    <div class="mb-3 pt-2 border-top">
                        <label class="form-label small fw-bold text-muted required">الدور المؤسسي المعتمد</label>
                        <select name="role" id="roleSelect" class="form-select fw-semibold" onchange="applyRolePreset(this.value)">
                            @foreach($rolePresets as $key => $preset)
                                <option value="{{ $key }}" {{ old('role', 'custom') === $key ? 'selected' : '' }}>
                                    {{ $preset['name'] }}
                                </option>
                            @endforeach
                        </select>
                        <div id="roleDescription" class="p-2 rounded-2 bg-light mt-2 text-muted small border" style="font-size: 0.78rem;">
                            {{ $rolePresets[old('role', 'custom')]['description'] }}
                        </div>
                    </div>

                    <!-- زر الحفظ -->
                    <div class="mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-hasad-primary w-100 py-2 fw-bold shadow-sm">
                            <i class="fa-solid fa-floppy-disk me-1"></i> حفظ وتفعيل الحساب
                        </button>
                    </div>

                </div>
            </div>

            <!-- العمود الأيسر: النطاق الجغرافي ومصفوفة الصلاحيات التفصيلية -->
            <div class="col-lg-8">

                <!-- بطاقة النطاق الجغرافي والمحافظات المصرح بها (Emerald Glassmorphism) -->
                <div class="card-hasad p-4 mb-4 emerald-glass-panel">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom gap-2">
                        <div>
                            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                <i class="fa-solid fa-map-location-dot text-emerald-custom fs-5"></i>
                                <span>النطاق الجغرافي والمحافظات المصرح بها</span>
                            </h5>
                            <span class="text-muted small">حدد المحافظات التي يُسمح لهذا المستخدم بالوصول إليها وإدارتها (تقييد الصلاحيات جغرافياً)</span>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="toggleAllGovs(true)">
                                <i class="fa-solid fa-check-double me-1"></i> تحديد الكل
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="toggleAllGovs(false)">
                                <i class="fa-solid fa-ban me-1"></i> إلغاء الكل
                            </button>
                        </div>
                    </div>

                    <!-- تنبيه مدير النظام المركزي -->
                    <div id="adminGovNotice" class="alert alert-info py-2 px-3 small d-none rounded-3 border-0 bg-info-subtle text-info-emphasis mb-3">
                        <i class="fa-solid fa-earth-americas me-1"></i>
                        <strong>مدير النظام المركزي:</strong> يمتلك وصولاً سيادياً شاملاً لكافة المحافظات دون قيود تلقائياً (*).
                    </div>

                    <div class="row g-2" id="governoratesGrid">
                        @foreach($governorates as $gov)
                            @php
                                $isGovChecked = is_array(old('governorate_ids')) && in_array($gov->id, old('governorate_ids'));
                            @endphp
                            <div class="col-sm-6 col-md-4 col-lg-3">
                                <label class="gov-card-checkbox d-flex align-items-center gap-2 p-2 rounded-3 border h-100 cursor-pointer" for="gov_{{ $gov->id }}">
                                    <input class="form-check-input gov-check m-0" 
                                           type="checkbox" 
                                           name="governorate_ids[]" 
                                           value="{{ $gov->id }}" 
                                           id="gov_{{ $gov->id }}"
                                           {{ $isGovChecked ? 'checked' : '' }}>
                                    <span class="fw-bold small text-dark flex-grow-1">{{ $gov->name }}</span>
                                    <span class="badge bg-light text-muted border font-monospace" style="font-size: 0.65rem;">{{ $gov->code }}</span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- مصفوفة الصلاحيات والتحكم بالأقسام -->
                <div class="card-hasad p-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom gap-2">
                        <div>
                            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                <i class="fa-solid fa-key text-success"></i>
                                <span>مصفوفة الصلاحيات والتحكم بالأقسام</span>
                            </h5>
                            <span class="text-muted small">حدد بدقة ما يستطيع هذا المستخدم رؤيته والقيام به في كافة محاور النظام</span>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="toggleAllPermissions(true)">
                                <i class="fa-solid fa-check-double me-1"></i> تحديد الكل
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="toggleAllPermissions(false)">
                                <i class="fa-solid fa-ban me-1"></i> إلغاء الكل
                            </button>
                        </div>
                    </div>

                    <!-- تنبيه مدير النظام -->
                    <div id="adminNotice" class="alert alert-info py-2 px-3 small d-none rounded-3 border-0 bg-info-subtle text-info-emphasis mb-3">
                        <i class="fa-solid fa-shield-halved me-1"></i> 
                        <strong>حساب مدير النظام المركزي:</strong> يملك صلاحية سيادية شاملة على كافة المحاور والوظائف والعمليات بدون قيود تلقائياً (*).
                    </div>

                    <!-- شبكة بطاقات الأقسام والصلاحيات -->
                    <div class="row g-3" id="permissionsGrid">
                        @foreach($availablePermissions as $moduleKey => $module)
                            <div class="col-md-6">
                                <div class="p-3 rounded-3 bg-white border h-100 shadow-xs permission-module-card">
                                    <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fa-solid {{ $module['icon'] }} text-success"></i>
                                            <h6 class="fw-bold small mb-0 text-dark">{{ $module['title'] }}</h6>
                                        </div>
                                        <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 text-success fw-semibold" style="font-size: 0.72rem;" onclick="toggleModuleCheckboxes('{{ $moduleKey }}')">
                                            تبديل الكل
                                        </button>
                                    </div>
                                    <div class="permission-checkboxes-group" data-module="{{ $moduleKey }}">
                                        @foreach($module['permissions'] as $permKey => $permLabel)
                                            <div class="form-check my-2">
                                                <input class="form-check-input perm-check" 
                                                       type="checkbox" 
                                                       name="permissions[]" 
                                                       value="{{ $permKey }}" 
                                                       id="perm_{{ str_replace('.', '_', $permKey) }}"
                                                       {{ (is_array(old('permissions')) && in_array($permKey, old('permissions'))) ? 'checked' : '' }}
                                                       onchange="onIndividualPermChange()">
                                                <label class="form-check-label small text-dark cursor-pointer" for="perm_{{ str_replace('.', '_', $permKey) }}">
                                                    {{ $permLabel }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                </div>
            </div>

        </div>
    </form>

</div>

@push('styles')
<style>
.emerald-glass-panel {
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.95), rgba(236, 253, 245, 0.65));
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border: 1px solid rgba(16, 185, 129, 0.25) !important;
    box-shadow: 0 4px 20px -2px rgba(16, 185, 129, 0.08);
}
.text-emerald-custom {
    color: #059669 !important;
}
.gov-card-checkbox {
    background: rgba(255, 255, 255, 0.85);
    border-color: rgba(209, 213, 219, 0.8) !important;
    transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    user-select: none;
}
.gov-card-checkbox:hover {
    border-color: #10b981 !important;
    background: rgba(236, 253, 245, 0.9);
    transform: translateY(-1px);
    box-shadow: 0 2px 8px rgba(16, 185, 129, 0.12);
}
.gov-card-checkbox:has(.gov-check:checked) {
    border-color: #10b981 !important;
    background: #ecfdf5 !important;
    box-shadow: 0 0 0 1px #10b981, 0 2px 8px rgba(16, 185, 129, 0.15);
}
.gov-card-checkbox:has(.gov-check:checked) .text-dark {
    color: #065f46 !important;
}
</style>
@endpush

@push('scripts')
<script>
    const rolePresetsData = @json($rolePresets);

    function toggleAllGovs(checked) {
        const cbs = document.querySelectorAll('.gov-check:not(:disabled)');
        cbs.forEach(cb => cb.checked = checked);
    }

    function applyRolePreset(roleKey) {
        const descEl = document.getElementById('roleDescription');
        const adminNotice = document.getElementById('adminNotice');
        const adminGovNotice = document.getElementById('adminGovNotice');
        const permChecks = document.querySelectorAll('.perm-check');
        const govChecks = document.querySelectorAll('.gov-check');

        if (rolePresetsData[roleKey]) {
            descEl.textContent = rolePresetsData[roleKey].description;
        }

        const isSuperAdmin = (roleKey === 'admin' || roleKey === 'super_admin');

        if (isSuperAdmin) {
            adminNotice.classList.remove('d-none');
            if (adminGovNotice) adminGovNotice.classList.remove('d-none');
            permChecks.forEach(cb => {
                cb.checked = true;
                cb.disabled = true;
            });
            govChecks.forEach(cb => {
                cb.checked = true;
                cb.disabled = true;
            });
        } else {
            adminNotice.classList.add('d-none');
            if (adminGovNotice) adminGovNotice.classList.add('d-none');
            permChecks.forEach(cb => cb.disabled = false);
            govChecks.forEach(cb => cb.disabled = false);

            if (roleKey !== 'custom') {
                const allowed = rolePresetsData[roleKey]?.permissions || [];
                permChecks.forEach(cb => {
                    cb.checked = allowed.includes(cb.value);
                });
            }
        }
    }

    function onIndividualPermChange() {
        const roleSelect = document.getElementById('roleSelect');
        if (roleSelect.value === 'admin' || roleSelect.value === 'super_admin') return;
        // تحويل الدور تلقائياً إلى "مخصص" عند تعديل أي خيار فردي
        if (roleSelect.value !== 'custom') {
            roleSelect.value = 'custom';
            document.getElementById('roleDescription').textContent = rolePresetsData['custom'].description;
        }
    }

    function toggleModuleCheckboxes(moduleKey) {
        const group = document.querySelector(`.permission-checkboxes-group[data-module="${moduleKey}"]`);
        if (!group) return;
        const cbs = group.querySelectorAll('.perm-check:not(:disabled)');
        const allChecked = Array.from(cbs).every(cb => cb.checked);
        cbs.forEach(cb => cb.checked = !allChecked);
        onIndividualPermChange();
    }

    function toggleAllPermissions(checked) {
        const cbs = document.querySelectorAll('.perm-check:not(:disabled)');
        cbs.forEach(cb => cb.checked = checked);
        onIndividualPermChange();
    }

    document.addEventListener('DOMContentLoaded', function() {
        applyRolePreset(document.getElementById('roleSelect').value);
    });
</script>
@endpush
@endsection
