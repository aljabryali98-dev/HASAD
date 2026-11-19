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
        });
    </script>

    @stack('scripts')
</body>
</html>
