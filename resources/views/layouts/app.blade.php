<!DOCTYPE html>
<html lang="ar" dir="rtl" data-nav-layout="vertical" data-theme-mode="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>@yield('title', 'تطبيق حصاد - لوحة التحكم')</title>

    <!-- خط تجوال (Tajawal) -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 RTL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    
    <!-- الأيقونات (FontAwesome) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    
    <!-- مكتبات الجداول والمدخلات (DataTables, Select2, Flatpickr) -->
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">

    <!-- التنسيقات المستخلصة من البوابة (Valex Style) -->
    <style>
        :root {
            --primary-color: #0162e8;
            --bg-light: #eef1f6;
            --sidebar-width: 240px;
        }
        body, html {
            font-family: 'Tajawal', sans-serif !important;
            background-color: var(--bg-light);
            direction: rtl;
        }
        /* تصميم القائمة الجانبية */
        .app-sidebar {
            width: var(--sidebar-width);
            position: fixed;
            top: 0;
            right: 0;
            height: 100vh;
            background: #fff;
            box-shadow: -2px 0 10px rgba(0,0,0,0.05);
            z-index: 1000;
            padding-top: 15px;
            overflow-y: auto;
        }
        .side-menu__sub {
            list-style: none;
            padding-right: 25px;
            padding-left: 0;
            margin-top: 4px;
            margin-bottom: 8px;
        }
        .side-menu__sub-item {
            display: flex;
            align-items: center;
            padding: 7px 12px;
            color: #556270;
            text-decoration: none;
            border-radius: 5px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 2px;
            transition: all 0.2s;
        }
        .side-menu__sub-item:hover, .side-menu__sub-item.active {
            background-color: rgba(1, 98, 232, 0.08);
            color: var(--primary-color);
            font-weight: 700;
        }
        .sidebar-brand {
            text-align: center;
            padding: 15px;
            border-bottom: 1px solid #eee;
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        .side-menu {
            list-style: none;
            padding: 15px 10px;
            margin: 0;
        }
        .side-menu__category {
            font-size: 0.78rem;
            color: #7b8191;
            font-weight: 700;
            padding: 10px 15px 5px;
        }
        .side-menu__item {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            color: #334151;
            text-decoration: none;
            border-radius: 6px;
            margin-bottom: 4px;
            font-weight: 500;
            transition: all 0.2s;
        }
        .side-menu__item:hover, .side-menu__item.active {
            background-color: rgba(1, 98, 232, 0.1);
            color: var(--primary-color);
        }
        .side-menu__item i {
            margin-left: 10px;
            font-size: 1.1rem;
        }
        /* منطقة المحتوى الرئيسي والشريط العلوي */
        .main-content {
            margin-right: var(--sidebar-width);
            padding: 20px;
            min-height: calc(100vh - 60px);
        }
        .app-header {
            margin-right: var(--sidebar-width);
            background: #fff;
            height: 65px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 25px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.04);
        }
        /* البطاقات والجداول */
        .custom-card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            background: #fff;
        }
        .custom-card .card-header {
            background: transparent;
            border-bottom: 1px solid #f0f2f8;
            padding: 15px 20px;
            font-weight: 700;
            font-size: 1.1rem;
        }
        table.dataTable {
            text-align: center;
            vertical-align: middle;
        }
        div#HasadTable_filter {
            display: none;
        }
        #filterFields {
            display: none;
            transition: all 0.3s ease;
        }
        #filterFields.visible {
            display: block;
        }
        .btn-filter-toggle {
            background-color: var(--primary-color) !important;
            color: #fff !important;
            border: none !important;
        }
        label {
            font-weight: 700;
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- القائمة الجانبية (Sidebar) -->
    @include('partials.sidebar')

    <!-- الشريط العلوي (Header) -->
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
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
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
    </script>

    @stack('scripts')
</body>
</html>
