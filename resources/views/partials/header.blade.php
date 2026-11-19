<header class="app-header">
    <div class="d-flex align-items-center gap-3">
        <!-- زر فتح القائمة الجانبية للشاشات الصغيرة -->
        <button type="button" class="btn-sidebar-toggle" id="sidebarToggleBtn" aria-label="تبديل القائمة">
            <i class="fa-solid fa-bars"></i>
        </button>

        <!-- حقل البحث السريع المؤسسي -->
        <div class="header-search d-none d-md-block">
            <i class="fa-solid fa-magnifying-glass header-search-icon"></i>
            <input type="search" placeholder="بحث سريع في سجلات حصاد..." aria-label="بحث">
        </div>
    </div>

    <div class="d-flex align-items-center gap-3">
        <!-- التاريخ الحالي والتقويم -->
        <div class="d-none d-sm-flex align-items-center gap-2 px-3 py-1 bg-light rounded-pill border text-muted small">
            <i class="fa-regular fa-calendar-check text-success"></i>
            <span>{{ date('Y-m-d') }}</span>
        </div>

        <!-- ملف المستخدم والقائمة المنسدلة -->
        <div class="dropdown">
            <a href="#" class="user-pill dropdown-toggle text-decoration-none" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="user-avatar-circle">
                    {{ mb_substr(auth()->user()->name ?? 'م', 0, 1) }}
                </div>
                <div class="d-none d-md-block text-end">
                    <span class="d-block fw-bold text-dark small leading-tight">{{ auth()->user()->name ?? 'المستخدم' }}</span>
                    <span class="d-block text-muted" style="font-size: 0.7rem;">
                        {{ auth()->user()->role === 'admin' ? 'مدير النظام المركزي' : 'مستخدم مصرح' }}
                    </span>
                </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 mt-2 py-2" style="min-width: 200px;">
                <li class="px-3 py-2 border-bottom mb-1">
                    <div class="fw-bold small text-dark">{{ auth()->user()->name ?? 'المستخدم' }}</div>
                    <div class="text-muted small" style="font-size: 0.75rem;">{{ auth()->user()->email ?? '' }}</div>
                </li>
                <li>
                    <a class="dropdown-item py-2 small d-flex align-items-center" href="{{ route('dashboard') }}">
                        <i class="fa-solid fa-gauge text-muted me-2"></i> لوحة التحكم
                    </a>
                </li>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}" id="logout-form">
                        @csrf
                        <button type="submit" class="dropdown-item py-2 small text-danger d-flex align-items-center">
                            <i class="fa-solid fa-right-from-bracket me-2"></i> تسجيل الخروج
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
