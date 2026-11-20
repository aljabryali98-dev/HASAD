<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme-mode="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
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
    
    <!-- الأيقونات (FontAwesome) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    
    <!-- مكتبات المدخلات والجداول -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">

    <!-- ملف التنسيق المؤسسي الموحد (HASAD Emerald Corporate Theme) -->
    <link href="{{ asset('css/hasad-theme.css') }}" rel="stylesheet">

    @stack('styles')
</head>
<body>

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
            // التحكم بالقائمة الجانبية على الشاشات الصغيرة
            const sidebar = document.querySelector('.app-sidebar');
            const backdrop = document.getElementById('sidebarBackdrop');
            const toggleBtn = document.getElementById('sidebarToggleBtn');

            if (toggleBtn && sidebar && backdrop) {
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('sidebar-open');
                    backdrop.classList.toggle('active');
                });

                backdrop.addEventListener('click', function() {
                    sidebar.classList.remove('sidebar-open');
                    backdrop.classList.remove('active');
                });
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

    <!-- وظائف التفاعل الفاخرة (Micro-interactions & Polish) -->
    <script>
        // نسخ سريع إلى الحافظة (One-Click Clipboard Copy)
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

        // تبديل وضع ملء الشاشة (Fullscreen Toggle)
        function toggleFullScreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(() => {});
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen().catch(() => {});
                }
            }
        }

        // نظام الإشعارات العائمة التفاعلية (Toast System)
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

        // اختصار لوحة المفاتيح للبحث السريع (Ctrl + K أو Cmd + K)
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                e.preventDefault();
                const searchInput = document.querySelector('.header-search input');
                if (searchInput) {
                    searchInput.focus();
                    searchInput.select();
                }
            }
        });

        // إخفاء تنبيهات الجلسة تلقائياً بعد 5 ثوانٍ
        setTimeout(function() {
            document.querySelectorAll('#hasadToastContainer .hasad-toast').forEach(function(toast) {
                toast.style.opacity = '0';
                toast.style.transform = 'translateY(-10px)';
                setTimeout(() => toast.remove(), 350);
            });
        }, 5500);
    </script>

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

    @stack('scripts')
</body>
</html>
