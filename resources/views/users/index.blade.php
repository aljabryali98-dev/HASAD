@extends('layouts.app')

@section('title', 'إدارة المستخدمين والصلاحيات - نظام حصاد')

@section('content')
<div class="container-fluid p-0">

    <!-- الترويسة الرئيسية والمسار والأزرار -->
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-1 text-dark d-flex align-items-center gap-2">
                <span class="text-success fs-5"><i class="fa-solid fa-users-gear"></i></span>
                إدارة المستخدمين والصلاحيات المؤسسية
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0 small">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}" class="text-decoration-none">الرئيسية</a></li>
                    <li class="breadcrumb-item active" aria-current="page">إدارة المستخدمين والصلاحيات</li>
                </ol>
            </nav>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('users.create') }}" class="btn btn-hasad-primary d-flex align-items-center gap-1 shadow-sm">
                <i class="fa-solid fa-user-plus"></i>
                <span>إضافة مستخدم جديد</span>
            </a>
        </div>
    </div>

    <!-- بطاقات الإحصائيات المركزية للمستخدمين -->
    <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card kpi-emerald h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-bold">إجمالي المستخدمين</span>
                    <div class="kpi-icon-badge kpi-icon-emerald" style="width: 40px; height: 40px; font-size: 1.1rem;">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
                <h3 class="stat-num text-dark mb-0">{{ number_format($stats['total']) }} <span class="small fs-6 text-muted">حساب</span></h3>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card kpi-emerald h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-bold">الحسابات النشطة</span>
                    <div class="kpi-icon-badge kpi-icon-emerald" style="width: 40px; height: 40px; font-size: 1.1rem;">
                        <i class="fa-solid fa-user-check"></i>
                    </div>
                </div>
                <h3 class="stat-num text-dark mb-0">{{ number_format($stats['active']) }} <span class="small fs-6 text-muted">نشط</span></h3>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card kpi-blue h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-bold">مدراء النظام المركزي</span>
                    <div class="kpi-icon-badge kpi-icon-blue" style="width: 40px; height: 40px; font-size: 1.1rem;">
                        <i class="fa-solid fa-user-shield"></i>
                    </div>
                </div>
                <h3 class="stat-num text-dark mb-0">{{ number_format($stats['admins']) }} <span class="small fs-6 text-muted">مدير</span></h3>
            </div>
        </div>
        <div class="col-sm-6 col-xl-3">
            <div class="kpi-card kpi-amber h-100">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-bold">الحسابات المعطلة</span>
                    <div class="kpi-icon-badge kpi-icon-amber" style="width: 40px; height: 40px; font-size: 1.1rem;">
                        <i class="fa-solid fa-user-lock"></i>
                    </div>
                </div>
                <h3 class="stat-num text-dark mb-0">{{ number_format($stats['inactive']) }} <span class="small fs-6 text-muted">معطل</span></h3>
            </div>
        </div>
    </div>

    <!-- بطاقة البحث والفلترة -->
    <div class="filter-card mb-4">
        <form method="GET" action="{{ route('users.index') }}" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label small fw-bold text-muted">البحث بالاسم أو البريد الإلكتروني أو الهاتف</label>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0" placeholder="اكتب اسم المستخدم أو بريده...">
                </div>
            </div>
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted">الدور / الرتبة</label>
                <select name="role" class="form-select">
                    <option value="">كافة الأدوار</option>
                    @foreach($rolePresets as $key => $preset)
                        <option value="{{ $key }}" {{ request('role') === $key ? 'selected' : '' }}>{{ $preset['name'] }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted">حالة الحساب</label>
                <select name="status" class="form-select">
                    <option value="">الكل</option>
                    <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>نشط ومفعل</option>
                    <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>معطل ومجمد</option>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-hasad-primary w-100">
                    <i class="fa-solid fa-filter me-1"></i> تصفية
                </button>
                @if(request()->hasAny(['search', 'role', 'status']))
                    <a href="{{ route('users.index') }}" class="btn btn-outline-secondary" title="إلغاء الفلاتر">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- جدول عرض المستخدمين -->
    <div class="card-hasad">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <i class="fa-solid fa-users text-success"></i>
                <span>قائمة مستخدمي النظام ({{ $users->total() }})</span>
            </h6>
            <span class="badge bg-light text-dark border font-monospace">
                صفحة {{ $users->currentPage() }} من {{ $users->lastPage() ?: 1 }}
            </span>
        </div>
        <div class="card-body p-0">
            @if($users->isEmpty())
                <div class="text-center py-5">
                    <i class="fa-solid fa-user-xmark text-muted fs-1 mb-3 d-block"></i>
                    <h5 class="text-dark fw-bold">لا يوجد مستخدمون يطابقون خيارات البحث</h5>
                    <p class="text-muted small">يمكنك إضافة مستخدم جديد بالنقر على الزر أدناه.</p>
                    <a href="{{ route('users.create') }}" class="btn btn-hasad-primary btn-sm mt-2">
                        <i class="fa-solid fa-user-plus me-1"></i> إضافة مستخدم جديد
                    </a>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hasad align-middle mb-0 text-nowrap">
                        <thead>
                            <tr>
                                <th style="width: 60px;">#</th>
                                <th>المستخدم</th>
                                <th>البريد الإلكتروني</th>
                                <th>الهاتف</th>
                                <th>الدور المؤسسي</th>
                                <th>نطاق الصلاحيات</th>
                                <th>الحالة</th>
                                <th>تاريخ الإنشاء</th>
                                <th class="text-center" style="width: 140px;">الإجراءات</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $index => $u)
                                <tr>
                                    <td>{{ $users->firstItem() + $index }}</td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="user-avatar-circle" style="width: 38px; height: 38px; font-size: 0.9rem;">
                                                {{ mb_substr($u->name, 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">{{ $u->name }}</div>
                                                @if($u->id === auth()->id())
                                                    <span class="badge bg-success-subtle text-success border px-1 py-0" style="font-size: 0.65rem;">أنت (الحساب الحالي)</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="font-monospace text-muted small">{{ $u->email }}</span>
                                    </td>
                                    <td>
                                        <span class="font-monospace small">{{ $u->phone ?: '—' }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $preset = $rolePresets[$u->role] ?? null;
                                        @endphp
                                        <span class="badge {{ $preset['badge_class'] ?? 'bg-light text-dark border' }} px-2 py-1">
                                            {{ $preset['name'] ?? $u->role }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($u->isAdmin())
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 small">
                                                <i class="fa-solid fa-shield-halved me-1"></i> كامل النظام (*)
                                            </span>
                                        @else
                                            @php
                                                $permCount = is_array($u->permissions) ? count($u->permissions) : 0;
                                            @endphp
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small font-monospace">
                                                <i class="fa-solid fa-key me-1"></i> {{ $permCount }} صلاحية محددة
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($u->is_active)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                <i class="fa-solid fa-circle-check me-1"></i> نشط
                                            </span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                                <i class="fa-solid fa-circle-xmark me-1"></i> معطل
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="text-muted small font-monospace">{{ $u->created_at ? $u->created_at->format('Y-m-d') : '—' }}</span>
                                    </td>
                                    <td class="text-center">
                                        <div class="d-inline-flex align-items-center gap-1">
                                            <!-- زر التعديل -->
                                            <a href="{{ route('users.edit', $u->id) }}" class="btn-action-icon text-primary" title="تعديل المستخدم والصلاحيات">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </a>

                                            <!-- زر تفعيل / تجميد -->
                                            @if($u->id !== auth()->id())
                                                <form method="POST" action="{{ route('users.toggle-status', $u->id) }}" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn-action-icon {{ $u->is_active ? 'text-warning' : 'text-success' }}" 
                                                            title="{{ $u->is_active ? 'تعطيل الحساب' : 'تفعيل الحساب' }}"
                                                            onclick="return confirm('هل أنت متأكد من {{ $u->is_active ? 'تعطيل' : 'تفعيل' }} حساب {{ $u->name }}؟')">
                                                        <i class="fa-solid {{ $u->is_active ? 'fa-user-slash' : 'fa-user-check' }}"></i>
                                                    </button>
                                                </form>

                                                <!-- زر الحذف -->
                                                <form method="POST" action="{{ route('users.destroy', $u->id) }}" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn-action-icon text-danger" title="حذف الحساب"
                                                            onclick="return confirm('تحذير: هل أنت متأكد من حذف حساب {{ $u->name }} نهائياً؟')">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- ترقيم الصفحات -->
                <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <span class="text-muted small">عرض {{ $users->firstItem() }} إلى {{ $users->lastItem() }} من إجمالي {{ $users->total() }} مستخدم</span>
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>

</div>
@endsection
