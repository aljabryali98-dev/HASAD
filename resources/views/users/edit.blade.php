@extends('layouts.app')

@section('title', 'تعديل المستخدم والصلاحيات - ' . $user->name)

@section('content')
<div class="container-fluid p-0">

    <!-- الترويسة الرئيسية والمسار والأزرار -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                <span class="text-success fs-5"><i class="fa-solid fa-user-pen"></i></span>
                تعديل بيانات وصلاحيات المستخدم: <span class="text-success">{{ $user->name }}</span>
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('users.index') }}" class="text-decoration-none">إدارة المستخدمين</a></li>
                    <li class="breadcrumb-item active" aria-current="page">تعديل: {{ $user->name }}</li>
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

    <form method="POST" action="{{ route('users.update', $user->id) }}" id="userForm">
        @csrf
        @method('PUT')

        <div class="row g-4">
            
            <!-- العمود الأيمن: البيانات الأساسية والدور المؤسسي -->
            <div class="col-lg-4">
                <div class="card-hasad p-4 mb-4">
                    <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                        <i class="fa-solid fa-id-card text-success"></i>
                        <span>بيانات الحساب</span>
                    </h5>

                    <!-- الاسم الكامل -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted required">اسم المستخدم الكامل</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                    </div>

                    <!-- البريد الإلكتروني -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted required">البريد الإلكتروني (اسم الدخول)</label>
                        <input type="email" name="email" class="form-control font-monospace" value="{{ old('email', $user->email) }}" required>
                    </div>

                    <!-- كلمة المرور (اختيارية) -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">تغيير كلمة المرور (اتركه فارغاً للإبقاء على الحالية)</label>
                        <input type="password" name="password" class="form-control font-monospace" placeholder="••••••••" minlength="6">
                        <span class="text-muted small" style="font-size: 0.72rem;">أدخل كلمة مرور جديدة فقط في حال الرغبة بتغييرها</span>
                    </div>

                    <!-- رقم الهاتف -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-muted">رقم الهاتف / الواتساب</label>
                        <input type="text" name="phone" class="form-control font-monospace" value="{{ old('phone', $user->phone) }}" placeholder="777000000">
                    </div>

                    <!-- حالة التفعيل -->
                    <div class="mb-4 pt-2 border-top">
                        <div class="form-check form-switch d-flex align-items-center justify-content-between p-0">
                            <label class="form-check-label fw-bold small text-dark" for="isActiveSwitch">
                                حالة تفعيل الحساب
                            </label>
                            <input class="form-check-input ms-0" type="checkbox" role="switch" id="isActiveSwitch" name="is_active" value="1" 
                                   {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                                   {{ $user->id === auth()->id() ? 'disabled title="لا يمكنك تعطيل حسابك الخاص"' : '' }}>
                        </div>
                        @if($user->id === auth()->id())
                            <span class="badge bg-light text-muted border mt-1" style="font-size: 0.7rem;">حسابك الحالي نشط دائماً</span>
                        @else
                            <span class="text-muted small d-block" style="font-size: 0.75rem;">الحسابات المعطلة لا تستطيع تسجيل الدخول</span>
                        @endif
                    </div>

                    <!-- اختيار الدور المؤسسي (Role Preset) -->
                    <div class="mb-3 pt-2 border-top">
                        <label class="form-label small fw-bold text-muted required">الدور المؤسسي المعتمد</label>
                        <select name="role" id="roleSelect" class="form-select fw-semibold" onchange="applyRolePreset(this.value)">
                            @foreach($rolePresets as $key => $preset)
                                <option value="{{ $key }}" {{ old('role', $user->role) === $key ? 'selected' : '' }}>
                                    {{ $preset['name'] }}
                                </option>
                            @endforeach
                        </select>
                        <div id="roleDescription" class="p-2 rounded-2 bg-light mt-2 text-muted small border" style="font-size: 0.78rem;">
                            {{ $rolePresets[old('role', $user->role)]['description'] ?? '' }}
                        </div>
                    </div>

                    <!-- زر التحديث -->
                    <div class="mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-hasad-primary w-100 py-2 fw-bold shadow-sm">
                            <i class="fa-solid fa-floppy-disk me-1"></i> حفظ التحديثات
                        </button>
                    </div>

                </div>
            </div>

            <!-- العمود الأيسر: مصفوفة الصلاحيات التفصيلية لكامل أقسام النظام -->
            <div class="col-lg-8">
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
                    <div id="adminNotice" class="alert alert-info py-2 px-3 small {{ old('role', $user->role) === 'admin' ? '' : 'd-none' }} rounded-3 border-0 bg-info-subtle text-info-emphasis mb-3">
                        <i class="fa-solid fa-shield-halved me-1"></i> 
                        <strong>حساب مدير النظام المركزي:</strong> يملك صلاحية سيادية شاملة على كافة المحاور والوظائف والعمليات بدون قيود تلقائياً (*).
                    </div>

                    <!-- شبكة بطاقات الأقسام والصلاحيات -->
                    @php
                        $userPerms = old('permissions', $user->permissions ?? []);
                        if (!is_array($userPerms)) $userPerms = [];
                        $isSuperAdmin = ($user->role === 'admin' || in_array('*', $userPerms));
                    @endphp
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
                                            @php
                                                $checked = $isSuperAdmin || in_array($permKey, $userPerms) || in_array(explode('.', $permKey)[0] . '.*', $userPerms);
                                            @endphp
                                            <div class="form-check my-2">
                                                <input class="form-check-input perm-check" 
                                                       type="checkbox" 
                                                       name="permissions[]" 
                                                       value="{{ $permKey }}" 
                                                       id="perm_{{ str_replace('.', '_', $permKey) }}"
                                                       {{ $checked ? 'checked' : '' }}
                                                       {{ $user->role === 'admin' ? 'disabled' : '' }}
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

@push('scripts')
<script>
    const rolePresetsData = @json($rolePresets);

    function applyRolePreset(roleKey) {
        const descEl = document.getElementById('roleDescription');
        const adminNotice = document.getElementById('adminNotice');
        const permChecks = document.querySelectorAll('.perm-check');

        if (rolePresetsData[roleKey]) {
            descEl.textContent = rolePresetsData[roleKey].description;
        }

        if (roleKey === 'admin') {
            adminNotice.classList.remove('d-none');
            permChecks.forEach(cb => {
                cb.checked = true;
                cb.disabled = true;
            });
        } else {
            adminNotice.classList.add('d-none');
            permChecks.forEach(cb => cb.disabled = false);

            if (roleKey !== 'custom') {
                const allowed = rolePresetsData[roleKey].permissions || [];
                permChecks.forEach(cb => {
                    cb.checked = allowed.includes(cb.value);
                });
            }
        }
    }

    function onIndividualPermChange() {
        const roleSelect = document.getElementById('roleSelect');
        if (roleSelect.value === 'admin') return;
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
</script>
@endpush
@endsection
