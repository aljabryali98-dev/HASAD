<header class="app-header">
    <div class="d-flex align-items-center gap-2 gap-md-3">
        <!-- زر فتح القائمة الجانبية للشاشات الصغيرة -->
        <button type="button" class="btn-sidebar-toggle d-inline-flex d-lg-none align-items-center gap-1" id="sidebarToggleBtn" aria-label="تبديل القائمة">
            <i class="fa-solid fa-bars fs-6"></i>
            <span class="small fw-bold">القائمة</span>
        </button>

        <!-- وسم النظام للشاشات الصغيرة -->
        <div class="d-flex d-lg-none align-items-center gap-1">
            <span class="badge bg-success-subtle text-success border px-2 py-1 font-monospace fw-bold" style="font-size: 0.72rem;">حصاد ERP</span>
        </div>

        <!-- حقل البحث السريع المؤسسي مع لوحة الأوامر الذكية -->
        <div class="header-search d-none d-md-block position-relative" onclick="openCommandPalette()" style="cursor: pointer;">
            <i class="fa-solid fa-magnifying-glass header-search-icon"></i>
            <input type="search" placeholder="ابحث أو نفذ أمراً سريعاً... (Ctrl + K)" aria-label="بحث" readonly style="cursor: pointer;">
            <span class="position-absolute start-0 top-50 translate-middle-y ms-2 badge bg-light text-muted border font-monospace" style="font-size: 0.65rem; pointer-events: none;">
                Ctrl + K
            </span>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2 gap-sm-3">
        <!-- مؤشر حالة الاتصال الميداني بالشبكة (Network Resilience) -->
        <div class="network-status-pill online d-none d-sm-inline-flex" id="networkStatusPill" title="حالة الاتصال الميداني">
            <i class="fa-solid fa-circle" style="font-size: 0.5rem;"></i>
            <span id="networkStatusText">متصل لحظياً</span>
        </div>

        <!-- التاريخ والتقويم الرسمي -->
        <div class="d-none d-xl-flex align-items-center gap-2 px-3 py-1 bg-light rounded-pill border text-muted small">
            <i class="fa-regular fa-calendar-check text-success"></i>
            <span class="font-monospace fw-semibold">{{ date('Y-m-d') }}</span>
        </div>

        <!-- زر الاستيراد الذكي الشامل (Smart Excel Import) -->
        @hasPermission('import.view')
        <a href="{{ route('import.smart') }}" class="btn-header-action" title="الاستيراد الذكي الشامل من ملفات الإكسل">
            <i class="fa-solid fa-file-excel text-success"></i>
        </a>
        @endhasPermission

        <!-- زر تثبيت تطبيق حصاد (PWA Universal Install) -->
        <button type="button" class="btn-header-action" id="btnInstallAppHeader" onclick="handlePwaInstallClick()" title="تثبيت التطبيق على جهازك (لابتوب / أندرويد / آيفون)">
            <i class="fa-solid fa-mobile-screen-button text-success"></i>
        </button>

        <!-- زر لوحة الأوامر الذكية (Command Palette Button) -->
        <button type="button" class="btn-header-action d-none d-md-inline-flex" onclick="openCommandPalette()" title="لوحة الأوامر الذكية (Ctrl + K)">
            <i class="fa-solid fa-terminal"></i>
        </button>

        <!-- زر التبديل بين الوضع الليلي والنهاري (Dark / Light Mode) -->
        <button type="button" class="btn-header-action" id="themeToggleBtn" onclick="toggleThemeMode()" title="تبديل الوضع الليلي / الفاتح">
            <i class="fa-solid fa-moon" id="themeIcon"></i>
        </button>

        <!-- زر الطباعة الرسمية (Official Print) -->
        <button type="button" class="btn-header-action d-none d-lg-inline-flex no-print" onclick="window.print()" title="طباعة رسمية A4">
            <i class="fa-solid fa-print"></i>
        </button>

        <!-- زر ملء الشاشة (Fullscreen Toggle) -->
        <button type="button" class="btn-header-action d-none d-lg-inline-flex" onclick="toggleFullScreen()" title="تبديل وضع ملء الشاشة">
            <i class="fa-solid fa-expand"></i>
        </button>

        <!-- زر التحديث الفوري للبيانات (Fresh Cache Bypass) -->
        <a href="{{ request()->fullUrlWithQuery(['fresh' => 1]) }}" class="btn-header-action d-none d-md-inline-flex" title="تحديث فوري لبيانات النظام">
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
                @hasPermission('users.view')
                <li>
                    <a class="dropdown-item py-2 small d-flex align-items-center" href="{{ route('users.index') }}">
                        <i class="fa-solid fa-users-gear text-muted me-2"></i> إدارة المستخدمين والصلاحيات
                    </a>
                </li>
                @endhasPermission
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
