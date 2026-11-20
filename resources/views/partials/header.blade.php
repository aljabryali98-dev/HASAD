<header class="app-header">
    <div class="d-flex align-items-center gap-3">
        <!-- زر فتح القائمة الجانبية للشاشات الصغيرة -->
        <button type="button" class="btn-sidebar-toggle" id="sidebarToggleBtn" aria-label="تبديل القائمة">
            <i class="fa-solid fa-bars"></i>
        </button>

        <!-- حقل البحث السريع المؤسسي مع اختصار لوحة المفاتيح -->
        <div class="header-search d-none d-md-block position-relative">
            <i class="fa-solid fa-magnifying-glass header-search-icon"></i>
            <input type="search" placeholder="بحث سريع في سجلات حصاد..." aria-label="بحث">
            <span class="position-absolute start-0 top-50 translate-middle-y ms-2 badge bg-light text-muted border font-monospace" style="font-size: 0.65rem; pointer-events: none;">
                Ctrl + K
            </span>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2 gap-sm-3">
        <!-- التاريخ والتقويم الرسمي -->
        <div class="d-none d-lg-flex align-items-center gap-2 px-3 py-1 bg-light rounded-pill border text-muted small">
            <i class="fa-regular fa-calendar-check text-success"></i>
            <span class="font-monospace fw-semibold">{{ date('Y-m-d') }}</span>
        </div>

        <!-- زر ملء الشاشة (Fullscreen Toggle) -->
        <button type="button" class="btn-header-action" onclick="toggleFullScreen()" title="تبديل وضع ملء الشاشة">
            <i class="fa-solid fa-expand"></i>
        </button>

        <!-- زر التحديث الفوري للبيانات (Fresh Cache Bypass) -->
        <a href="{{ request()->fullUrlWithQuery(['fresh' => 1]) }}" class="btn-header-action" title="تحديث فوري لبيانات النظام">
            <i class="fa-solid fa-arrows-rotate"></i>
        </a>

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
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 mt-2 py-2" style="min-width: 220px;">
                <li class="px-3 py-2 border-bottom mb-1">
                    <div class="fw-bold small text-dark d-flex align-items-center justify-content-between">
                        <span>{{ auth()->user()->name ?? 'المستخدم' }}</span>
                        <span class="badge bg-success-subtle text-success border px-2 py-0" style="font-size: 0.65rem;">نشط</span>
                    </div>
                    <div class="text-muted small" style="font-size: 0.75rem;">{{ auth()->user()->email ?? '' }}</div>
                </li>
                <li>
                    <a class="dropdown-item py-2 small d-flex align-items-center" href="{{ route('dashboard') }}">
                        <i class="fa-solid fa-gauge text-muted me-2"></i> لوحة التحكم المركزية
                    </a>
                </li>
                <li>
                    <a class="dropdown-item py-2 small d-flex align-items-center" href="{{ route('geo-explorer.index') }}">
                        <i class="fa-solid fa-earth-asia text-muted me-2"></i> المستكشف الجغرافي
                    </a>
                </li>
                <li><hr class="dropdown-divider my-1"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}" id="logout-form">
                        @csrf
                        <button type="submit" class="dropdown-item py-2 small text-danger d-flex align-items-center">
                            <i class="fa-solid fa-right-from-bracket me-2"></i> تسجيل الخروج الآمن
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</header>
