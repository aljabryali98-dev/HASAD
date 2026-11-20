<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme-mode="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- PWA & Mobile Web App Meta Tags -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#064E3B">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="حصاد ERP">
    <link rel="apple-touch-icon" href="{{ asset('images/icons/apple-touch-icon.png') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('images/icons/icon-192x192.png') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    
    <title>@yield('title', 'نظام حصاد - المنظومة الوطنية للرصد والتقييم')</title>

    <!-- تسريع الاتصال بالنطاقات الخارجية (DNS Prefetch & Preconnect) -->
    <link rel="dns-prefetch" href="https://cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="https://code.jquery.com">
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>

    <!-- الخطوط المؤسسية الرسمية (Cairo & IBM Plex Sans Arabic & Tajawal) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 RTL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    
    <!-- الأيقونات (FontAwesome & Lucide) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- مكتبات المدخلات والجداول -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">

    <!-- ملف التنسيق المؤسسي الموحد (HASAD Emerald Corporate Theme) -->
    <link href="{{ asset('css/hasad-theme.css') }}" rel="stylesheet">

    <!-- قراءة وتطبيق تفضيل الوضع الليلي مبكراً لمنع الوميض (Anti-Flicker Early Theme Script) -->
    <script>
        (function() {
            try {
                const savedTheme = localStorage.getItem('hasad-theme-mode') || 'light';
                document.documentElement.setAttribute('data-theme-mode', savedTheme);
            } catch (e) {}
        })();
    </script>

    @stack('styles')
</head>
<body>

    <!-- خلفية الشفق الزمرّدي والنعناعي المتوهجة (Soft Aurora Mesh Gradients) -->
    <div class="aurora-bg-mesh" aria-hidden="true">
        <div class="aurora-blob aurora-blob-1"></div>
        <div class="aurora-blob aurora-blob-2"></div>
        <div class="aurora-blob aurora-blob-3"></div>
    </div>

    <!-- ترويسة الطباعة الرسمية للتقارير التنفيذية (@media print) -->
    <div class="hasad-print-header">
        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
            <div class="text-start">
                <span class="d-block fw-bold small">الجمهورية اليمنية</span>
                <span class="d-block small text-muted">اللجنة الزراعية والسمكية العليا</span>
            </div>
            <div class="text-center">
                <h4 class="fw-bold mb-0 text-success">نظام حصاد للرصد والتقييم والتحليل التعاوني</h4>
                <span class="small text-muted font-monospace">تقرير رسمي معتمد للبيانات والمعلومات</span>
            </div>
            <div class="text-end font-monospace small">
                <div>تاريخ الاستخراج: {{ date('Y-m-d H:i') }}</div>
                <div>المستخدم: {{ auth()->user()->name ?? 'مدير النظام' }}</div>
            </div>
        </div>
    </div>

    <!-- شريط تقدم التحميل اللحظي الفائق (Instant Top Loader) -->
    <div id="hasadTopLoader" style="position: fixed; top: 0; left: 0; width: 0%; height: 3px; background: linear-gradient(90deg, #10B981 0%, #065F46 100%); z-index: 999999; transition: width 0.25s ease, opacity 0.25s ease; opacity: 0; pointer-events: none;"></div>

    <!-- خلفية حجب للشاشات الصغيرة عند فتح القائمة -->
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

    <!-- القائمة الجانبية المؤسسية (Sidebar) -->
    @include('partials.sidebar')

    <!-- الشريط العلوي المؤسسي (Header) -->
    @include('partials.header')

    <!-- المحتوى الرئيسي (Main Content) -->
    <main class="main-content">
        @yield('content')
    </main>

    <!-- التذييل (Footer) -->
    @include('partials.footer')

    <!-- شريط التنقل السفلي الذكي للهواتف والشاشات الصغيرة (Mobile Bottom Nav) -->
    <nav class="hasad-mobile-bottom-nav d-lg-none" aria-label="شريط التنقل السفلي">
        <a href="{{ route('dashboard') }}" class="mobile-nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fa-solid fa-house"></i>
            <span>الرئيسية</span>
        </a>
        @hasPermission('associations.view')
        <a href="{{ route('associations.index') }}" class="mobile-nav-item {{ request()->routeIs('associations.*') ? 'active' : '' }}">
            <i class="fa-solid fa-building-columns"></i>
            <span>الجمعيات</span>
        </a>
        @endhasPermission
        @hasPermission('personnel.view')
        <a href="{{ route('personnel.index') }}" class="mobile-nav-item {{ request()->routeIs('personnel.*') ? 'active' : '' }}">
            <i class="fa-solid fa-user-graduate"></i>
            <span>الفرسان</span>
        </a>
        @endhasPermission
        @hasPermission('import.view')
        <a href="{{ route('import.smart') }}" class="mobile-nav-item {{ request()->routeIs('import.smart*') ? 'active' : '' }}">
            <i class="fa-solid fa-file-excel"></i>
            <span>استيراد</span>
        </a>
        @endhasPermission
        <button type="button" class="mobile-nav-item mobile-nav-menu-btn" id="mobileBottomMenuBtn" aria-label="فتح القائمة الكاملة">
            <i class="fa-solid fa-bars-staggered"></i>
            <span>القائمة</span>
        </button>
    </nav>

    <!-- الحزم البرمجية الأساسية (JS Libraries) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="{{ asset('js/geo-cascading.js') }}"></script>
    <script src="{{ asset('js/location-hierarchy-selector.js') }}"></script>

    <script>
        // إعداد رمز الحماية لجميع طلبات AJAX تلقائياً
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // دالة تأخير التنفيذ الذكي (Smart Debounce) لتحسين أداء الواجهة وتقليل الطلبات المتكررة
        function debounce(func, wait = 400) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }

        document.addEventListener('DOMContentLoaded', function() {
            // التحكم بالقائمة الجانبية على الشاشات الصغيرة والهواتف
            const sidebar = document.getElementById('appSidebar') || document.querySelector('.app-sidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            const toggleBtn = document.getElementById('sidebarToggleBtn');
            const closeBtn = document.getElementById('sidebarCloseBtn');
            const bottomMenuBtn = document.getElementById('mobileBottomMenuBtn');

            function openSidebar() {
                if (sidebar) sidebar.classList.add('sidebar-open');
                if (backdrop) backdrop.classList.add('active');
                document.body.style.overflow = 'hidden';
            }

            function closeSidebar() {
                if (sidebar) sidebar.classList.remove('sidebar-open');
                if (backdrop) backdrop.classList.remove('active');
                document.body.style.overflow = '';
            }

            if (toggleBtn) toggleBtn.addEventListener('click', openSidebar);
            if (bottomMenuBtn) bottomMenuBtn.addEventListener('click', openSidebar);
            if (closeBtn) closeBtn.addEventListener('click', closeSidebar);
            if (backdrop) backdrop.addEventListener('click', closeSidebar);

            // إغلاق القائمة تلقائياً عند النقر على أي رابط تنقل
            if (sidebar) {
                sidebar.querySelectorAll('.side-menu__item, .side-menu__sub-item').forEach(function(link) {
                    if (!link.hasAttribute('data-bs-toggle') && link.getAttribute('href') !== 'javascript:void(0)') {
                        link.addEventListener('click', function() {
                            if (window.innerWidth < 992) {
                                closeSidebar();
                            }
                        });
                    }
                });

                // دعم السحب لليمين لإغلاق القائمة على شاشات اللمس (Touch Swipe to Close)
                let touchStartX = 0;
                sidebar.addEventListener('touchstart', function(e) {
                    touchStartX = e.changedTouches[0].clientX;
                }, { passive: true });

                sidebar.addEventListener('touchend', function(e) {
                    const touchEndX = e.changedTouches[0].clientX;
                    if (touchEndX - touchStartX > 50) {
                        closeSidebar();
                    }
                }, { passive: true });
            }

            // تفعيل البحث اللحظي الخفيف مع Debounce لحقول البحث
            const searchInputs = document.querySelectorAll('input[name="search"].live-search, input[data-debounce]');
            searchInputs.forEach(input => {
                input.addEventListener('input', debounce(function() {
                    const form = this.closest('form');
                    if (form) {
                        form.submit();
                    }
                }, 450));
            });

            // منع إرسال النماذج المتكرر لحماية السيرفر وتفادي تجميد الصفحة
            document.querySelectorAll('form:not(.no-debounce)').forEach(form => {
                form.addEventListener('submit', function() {
                    const btn = this.querySelector('button[type="submit"]:not(.no-disable)');
                    if (btn && !btn.disabled) {
                        btn.dataset.originalHtml = btn.innerHTML;
                        btn.disabled = true;
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> جارِ المعالجة...';
                        setTimeout(() => {
                            btn.disabled = false;
                            if (btn.dataset.originalHtml) btn.innerHTML = btn.dataset.originalHtml;
                        }, 8000);
                    }
                });
            });

            // 1. تفعيل شريط التقدم اللحظي عند النقر على أي رابط داخلي
            const loader = document.getElementById('hasadTopLoader');
            document.addEventListener('click', function(e) {
                const link = e.target.closest('a');
                if (link && link.href && !link.target && !link.href.startsWith('#') && !link.href.includes('javascript:') && !link.hasAttribute('download') && link.origin === window.location.origin) {
                    if (link.closest('form') || link.dataset.bsToggle || link.href.includes('logout')) return;
                    if (loader) {
                        loader.style.opacity = '1';
                        loader.style.width = '35%';
                        setTimeout(() => { if (loader.style.opacity === '1') loader.style.width = '75%'; }, 120);
                    }
                }
            });

            // 2. التحميل الاستباقي الذكي (Instant Prefetching on Hover / Touch)
            // يجلب كود الصفحة في ذاكرة المتصفح فور مرور المؤشر على الرابط قبل النقر الفعلي
            const prefetchedUrls = new Set();
            function prefetchLink(url) {
                if (!url || prefetchedUrls.has(url) || url.includes('logout') || url.includes('#') || url.includes('javascript:')) return;
                prefetchedUrls.add(url);
                const prefetch = document.createElement('link');
                prefetch.rel = 'prefetch';
                prefetch.href = url;
                prefetch.as = 'document';
                document.head.appendChild(prefetch);
            }

            document.addEventListener('mouseover', function(e) {
                const link = e.target.closest('a[href^="/"], a[href^="' + window.location.origin + '"]');
                if (link && !link.dataset.bsToggle && !link.hasAttribute('download')) {
                    prefetchLink(link.href);
                }
            }, { passive: true });

            document.addEventListener('touchstart', function(e) {
                const link = e.target.closest('a[href^="/"], a[href^="' + window.location.origin + '"]');
                if (link && !link.dataset.bsToggle && !link.hasAttribute('download')) {
                    prefetchLink(link.href);
                }
            }, { passive: true });
        });
    </script>

    <!-- محرك التصفح اللحظي الفائق (Instant.page Engine) -->
    <script src="https://cdn.jsdelivr.net/npm/instant.page@5.2.0/dist/instantpage.min.js" type="module" defer></script>

    <!-- مكتبة الرسوم البيانية التفاعلية الفائقة (ApexCharts) -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <!-- وظائف التفاعل الفاخرة، الوضع الليلي، ولوحة الأوامر الذكية (Enterprise Polish Engine) -->
    <script>
        // 1. إدارة الوضع الليلي المؤسسي (Dark / Light Theme Toggle)
        function updateThemeUI() {
            const currentTheme = document.documentElement.getAttribute('data-theme-mode') || 'light';
            const themeIcon = document.getElementById('themeIcon');
            if (themeIcon) {
                if (currentTheme === 'dark') {
                    themeIcon.className = 'fa-solid fa-sun text-warning';
                } else {
                    themeIcon.className = 'fa-solid fa-moon text-muted';
                }
            }
        }

        function toggleThemeMode() {
            const current = document.documentElement.getAttribute('data-theme-mode') || 'light';
            const nextTheme = current === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-theme-mode', nextTheme);
            try {
                localStorage.setItem('hasad-theme-mode', nextTheme);
            } catch (e) {}
            updateThemeUI();
            showToast(nextTheme === 'dark' ? 'تم تفعيل الوضع الليلي الفاخر' : 'تم تفعيل الوضع النهاري القياسي', 'info');
        }

        document.addEventListener('DOMContentLoaded', updateThemeUI);

        // 2. مؤشر حالة الشبكة الميدانية (Network Resilience)
        function updateNetworkStatus() {
            const pill = document.getElementById('networkStatusPill');
            const text = document.getElementById('networkStatusText');
            if (!pill || !text) return;

            if (navigator.onLine) {
                pill.className = 'network-status-pill online d-none d-sm-inline-flex';
                text.textContent = 'متصل لحظياً';
            } else {
                pill.className = 'network-status-pill offline d-inline-flex';
                text.textContent = 'انقطع الاتصال بالشبكة';
                showToast('تنبيه ميداني: انقطع الاتصال بالإنترنت! يرجى عدم إرسال البيانات حتى استقرار الشبكة.', 'danger');
            }
        }

        window.addEventListener('online', function() {
            updateNetworkStatus();
            showToast('تمت استعادة الاتصال بالشبكة السحابية بنجاح.', 'success');
        });
        window.addEventListener('offline', updateNetworkStatus);
        document.addEventListener('DOMContentLoaded', updateNetworkStatus);

        // 3. نسخ سريع إلى الحافظة (One-Click Clipboard Copy)
        function copyToClipboard(text, btnElement) {
            if (!text) return;
            navigator.clipboard.writeText(text).then(function() {
                showToast('تم النسخ إلى الحافظة: ' + text, 'success');
                if (btnElement) {
                    const originalHtml = btnElement.innerHTML;
                    btnElement.innerHTML = '<i class="fa-solid fa-check text-success"></i>';
                    setTimeout(() => { btnElement.innerHTML = originalHtml; }, 1600);
                }
            }).catch(function() {
                showToast('تعذر النسخ التلقائي', 'danger');
            });
        }

        // 4. تبديل وضع ملء الشاشة (Fullscreen Toggle)
        function toggleFullScreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(() => {});
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen().catch(() => {});
                }
            }
        }

        // 5. نظام الإشعارات العائمة التفاعلية (Floating Toast Notifications)
        function showToast(message, type = 'success') {
            const container = document.getElementById('hasadToastContainer');
            if (!container) return;
            const toast = document.createElement('div');
            toast.className = `hasad-toast hasad-toast-${type} shadow-lg`;
            const icon = type === 'success' ? 'fa-circle-check text-success' : (type === 'danger' ? 'fa-circle-exclamation text-danger' : 'fa-circle-info text-primary');
            const title = type === 'success' ? 'إشعار فوري' : (type === 'danger' ? 'تنبيه' : 'معلومة');
            toast.innerHTML = `
                <div class="d-flex align-items-center gap-3">
                    <div class="fs-4"><i class="fa-solid ${icon}"></i></div>
                    <div>
                        <strong class="d-block text-dark small">${title}</strong>
                        <span class="text-muted small">${message}</span>
                    </div>
                </div>
                <button type="button" class="btn-close small" onclick="this.closest('.hasad-toast').remove()"></button>
            `;
            container.appendChild(toast);
            setTimeout(() => {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-10px)';
                setTimeout(() => toast.remove(), 350);
            }, 4500);
        }

        // 6. لوحة الأوامر الذكية العائمة (Command Palette)
        const systemCommands = [
            @hasPermission('dashboard.view')
            { title: 'لوحة الإحصائيات والمتابعة المركزية', category: 'الرئيسية', icon: 'fa-gauge-high', url: '{{ route('dashboard') }}' },
            @endhasPermission
            @hasPermission('associations.view')
            { title: 'سجل الجمعيات التعاونية (الكيانات المركزية)', category: 'الجمعيات', icon: 'fa-building-columns', url: '{{ route('associations.index') }}' },
            @endhasPermission
            @hasPermission('associations.create')
            { title: 'تسجيل وتأسيس جمعية جديدة', category: 'الجمعيات', icon: 'fa-plus-circle', url: '{{ route('associations.create') }}' },
            @endhasPermission
            @hasPermission('productive_groups.view')
            { title: 'المجاميع الإنتاجية الميدانية', category: 'المجاميع', icon: 'fa-people-roof', url: '{{ route('productive-groups.index') }}' },
            @endhasPermission
            @hasPermission('personnel.view')
            { title: 'بنك الكوادر والفرسان والتدريب', category: 'الكوادر', icon: 'fa-user-graduate', url: '{{ route('personnel.index') }}' },
            @endhasPermission
            @hasPermission('loans.view')
            { title: 'محفظة القروض البيضاء (0% فوائد)', category: 'التمويل', icon: 'fa-hand-holding-dollar', url: '{{ route('general.loans.index') }}' },
            @endhasPermission
            @hasPermission('geography.view')
            { title: 'المستكشف الجغرافي الشامل', category: 'الجغرافيا', icon: 'fa-earth-asia', url: '{{ route('geo-explorer.index') }}' },
            @endhasPermission
            @hasPermission('geography.manage')
            { title: 'إدارة النطاق الجغرافي والقرى', category: 'الجغرافيا', icon: 'fa-network-wired', url: '{{ route('geography.manage') }}' },
            @endhasPermission
            @hasPermission('import.view')
            { title: 'الاستيراد الذكي الشامل من ملفات الإكسل (Smart Excel Import)', category: 'الأدوات', icon: 'fa-file-excel', url: '{{ route('import.smart') }}' },
            @endhasPermission
            @hasPermission('projects.view')
            { title: 'مشاريع الجمعيات التنموية', category: 'المشاريع', icon: 'fa-diagram-project', url: '{{ route('general.projects.index') }}' },
            { title: 'المبادرات المجتمعية الميدانية', category: 'المبادرات', icon: 'fa-handshake', url: '{{ route('general.initiatives.index') }}' },
            @endhasPermission
            @hasPermission('crops.view')
            { title: 'سجل المزارعين والحيازات الزراعية', category: 'الزراعة', icon: 'fa-tractor', url: '{{ route('general.farmers.index') }}' },
            { title: 'المشاتل الزراعية المعتمدة', category: 'الزراعة', icon: 'fa-tree', url: '{{ route('general.nurseries.index') }}' },
            @endhasPermission
            @hasPermission('users.view')
            { title: 'إدارة المستخدمين وضبط الصلاحيات المؤسسية', category: 'الأمان', icon: 'fa-users-gear', url: '{{ route('users.index') }}' },
            @endhasPermission
            { title: 'تفعيل الوضع الليلي / الفاتح', category: 'المظهر', icon: 'fa-circle-half-stroke', action: 'toggleTheme' },
            { title: 'طباعة رسمية A4 للصفحة الحالية', category: 'أدوات', icon: 'fa-print', action: 'print' },
            { title: 'تثبيت تطبيق حصاد على جهازك (لابتوب / أندرويد / آيفون)', category: 'تطبيق', icon: 'fa-mobile-screen-button', action: 'installPwa' }
        ];

        function openCommandPalette() {
            const backdrop = document.getElementById('commandPaletteBackdrop');
            const input = document.getElementById('commandPaletteInput');
            if (backdrop && input) {
                backdrop.classList.add('active');
                input.value = '';
                renderCommandResults('');
                setTimeout(() => input.focus(), 80);
            }
        }

        function closeCommandPalette() {
            const backdrop = document.getElementById('commandPaletteBackdrop');
            if (backdrop) {
                backdrop.classList.remove('active');
            }
        }

        function renderCommandResults(query) {
            const list = document.getElementById('commandPaletteResults');
            if (!list) return;
            const q = query.trim().toLowerCase();
            const filtered = systemCommands.filter(c => !q || c.title.toLowerCase().includes(q) || c.category.toLowerCase().includes(q));

            if (filtered.length === 0) {
                list.innerHTML = `<li class="p-4 text-center text-muted small"><i class="fa-solid fa-magnifying-glass mb-2 d-block fs-4"></i>لا توجد أوامر أو صفحات تطابق: "${query}"</li>`;
                return;
            }

            list.innerHTML = filtered.map((c, idx) => `
                <li>
                    <div class="command-palette-item ${idx === 0 ? 'active' : ''}" data-idx="${idx}" onclick="executeCommand(${idx})">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fa-solid ${c.icon} text-success" style="width: 20px;"></i>
                            <span class="fw-semibold small">${c.title}</span>
                        </div>
                        <span class="badge bg-light text-muted border font-monospace" style="font-size: 0.7rem;">${c.category}</span>
                    </div>
                </li>
            `).join('');
        }

        function executeCommand(idx) {
            const input = document.getElementById('commandPaletteInput');
            const q = input ? input.value.trim().toLowerCase() : '';
            const filtered = systemCommands.filter(c => !q || c.title.toLowerCase().includes(q) || c.category.toLowerCase().includes(q));
            const cmd = filtered[idx];
            if (!cmd) return;

            closeCommandPalette();
            if (cmd.url) {
                window.location.href = cmd.url;
            } else if (cmd.action === 'toggleTheme') {
                toggleThemeMode();
            } else if (cmd.action === 'print') {
                window.print();
            } else if (cmd.action === 'installPwa') {
                handlePwaInstallClick();
            }
        }

        document.addEventListener('keydown', function(e) {
            // فتح لوحة الأوامر عبر Ctrl + K أو Cmd + K
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                const backdrop = document.getElementById('commandPaletteBackdrop');
                if (backdrop && backdrop.classList.contains('active')) {
                    closeCommandPalette();
                } else {
                    openCommandPalette();
                }
            }
            // إغلاق لوحة الأوامر عبر ESC
            if (e.key === 'Escape') {
                closeCommandPalette();
            }
        });

        // متابعة الكتابة في لوحة الأوامر
        document.addEventListener('DOMContentLoaded', function() {
            const input = document.getElementById('commandPaletteInput');
            if (input) {
                input.addEventListener('input', function() {
                    renderCommandResults(this.value);
                });
                input.addEventListener('keydown', function(e) {
                    const list = document.getElementById('commandPaletteResults');
                    if (!list) return;
                    const items = list.querySelectorAll('.command-palette-item');
                    let currentActive = list.querySelector('.command-palette-item.active');
                    let activeIdx = currentActive ? parseInt(currentActive.dataset.idx) : 0;

                    if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        activeIdx = (activeIdx + 1) % items.length;
                        items.forEach(el => el.classList.remove('active'));
                        if (items[activeIdx]) items[activeIdx].classList.add('active');
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        activeIdx = (activeIdx - 1 + items.length) % items.length;
                        items.forEach(el => el.classList.remove('active'));
                        if (items[activeIdx]) items[activeIdx].classList.add('active');
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        executeCommand(activeIdx);
                    }
                });
            }
        });

        // 7. العدادات الرقمية التفاعلية الحركية (Animated Count-Up on Load)
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.stat-num').forEach(function(el) {
                const text = el.textContent.trim().replace(/,/g, '');
                const target = parseFloat(text);
                if (!isNaN(target) && target > 0 && target <= 10000000) {
                    let start = 0;
                    const duration = 750;
                    const startTime = performance.now();
                    function updateCounter(currentTime) {
                        const elapsed = currentTime - startTime;
                        const progress = Math.min(elapsed / duration, 1);
                        const easeOut = 1 - Math.pow(1 - progress, 3);
                        const current = Math.floor(start + (target - start) * easeOut);
                        el.textContent = current.toLocaleString();
                        if (progress < 1) {
                            requestAnimationFrame(updateCounter);
                        } else {
                            el.textContent = target.toLocaleString();
                        }
                    }
                    requestAnimationFrame(updateCounter);
                }
            });

            // إخفاء إشعارات الجلسة التلقائية بعد 5 ثوانٍ
            setTimeout(function() {
                document.querySelectorAll('#hasadToastContainer .hasad-toast').forEach(function(toast) {
                    toast.style.opacity = '0';
                    toast.style.transform = 'translateY(-10px)';
                    setTimeout(() => toast.remove(), 350);
                });
            }, 5500);

            // تفعيل أيقونات Lucide الرشيقة بدقة وحجم 1.5 Stroke
            if (typeof lucide !== 'undefined' && lucide.createIcons) {
                lucide.createIcons({
                    attrs: {
                        'stroke-width': 1.5
                    }
                });
            }
        });

        // تأثير كشاف الضوء الأخضر اللحظي على البطاقات الزجاجية (Cursor-tracking Soft Green Spotlight)
        document.addEventListener('pointermove', function(e) {
            const cards = document.querySelectorAll('.spotlight-card');
            for (let i = 0; i < cards.length; i++) {
                const card = cards[i];
                const rect = card.getBoundingClientRect();
                if (e.clientX >= rect.left - 40 && e.clientX <= rect.right + 40 &&
                    e.clientY >= rect.top - 40 && e.clientY <= rect.bottom + 40) {
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;
                    card.style.setProperty('--mouse-x', x + 'px');
                    card.style.setProperty('--mouse-y', y + 'px');
                }
            }
        }, { passive: true });
    </script>

    <!-- نافذة لوحة الأوامر الذكية العائمة (Command Palette Backdrop & Modal) -->
    <div class="command-palette-backdrop" id="commandPaletteBackdrop" onclick="if(event.target === this) closeCommandPalette()">
        <div class="command-palette-card">
            <div class="command-palette-input-wrapper">
                <i class="fa-solid fa-terminal text-success fs-5"></i>
                <input type="text" class="command-palette-input" id="commandPaletteInput" placeholder="اكتب أمراً أو ابحث عن صفحة... (مثال: جمعية، قروض، فرسان)" autocomplete="off">
                <span class="badge bg-light text-muted border font-monospace small">ESC للإغلاق</span>
            </div>
            <ul class="command-palette-results" id="commandPaletteResults">
                <!-- يتم توليد النتائج تفاعلياً -->
            </ul>
        </div>
    </div>

    <!-- حاوية الإشعارات العائمة التفاعلية (Floating Toast Notifications) -->
    <div class="hasad-toast-container" id="hasadToastContainer">
        @if(session('success'))
            <div class="hasad-toast hasad-toast-success shadow-lg">
                <div class="d-flex align-items-center gap-3">
                    <div class="text-success fs-4"><i class="fa-solid fa-circle-check"></i></div>
                    <div>
                        <strong class="d-block text-dark small">تم الإجراء بنجاح</strong>
                        <span class="text-muted small">{{ session('success') }}</span>
                    </div>
                </div>
                <button type="button" class="btn-close small" onclick="this.closest('.hasad-toast').remove()"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="hasad-toast hasad-toast-danger shadow-lg">
                <div class="d-flex align-items-center gap-3">
                    <div class="text-danger fs-4"><i class="fa-solid fa-circle-exclamation"></i></div>
                    <div>
                        <strong class="d-block text-dark small">تنبيه بالنظام</strong>
                        <span class="text-muted small">{{ session('error') }}</span>
                    </div>
                </div>
                <button type="button" class="btn-close small" onclick="this.closest('.hasad-toast').remove()"></button>
            </div>
        @endif
    </div>

    <!-- نافذة تثبيت التطبيق PWA Modal -->
    @include('partials.pwa-modal')

    <!-- سكريبت تثبيت وتفعيل PWA لكافة الأجهزة -->
    <script src="{{ asset('js/pwa-install.js') }}"></script>

    @stack('scripts')
</body>
</html>
