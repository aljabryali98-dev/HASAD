<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>تسجيل الدخول - نظام حصاد المركزي</title>

    <!-- الخطوط الرسمية -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 RTL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    
    <!-- الأيقونات (FontAwesome & Lucide) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- ملف التنسيق المؤسسي -->
    <link href="{{ asset('css/hasad-theme.css') }}" rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background-color: var(--hasad-bg);
            padding: 24px;
            position: relative;
            overflow-x: hidden;
        }

        /* نمط هندسي خافت في الخلفية */
        body::before {
            content: '';
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background-image: radial-gradient(#065F46 0.75px, transparent 0.75px);
            background-size: 24px 24px;
            opacity: 0.03;
            pointer-events: none;
        }

        .login-card-container {
            max-width: 450px;
            width: 100%;
            position: relative;
            z-index: 2;
        }

        .login-card-box {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(28px) saturate(190%);
            -webkit-backdrop-filter: blur(28px) saturate(190%);
            border: 1px solid rgba(255, 255, 255, 0.9);
            border-radius: var(--hasad-radius-xl);
            box-shadow: 0 20px 45px -10px rgba(6, 78, 59, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.95);
            padding: 38px 34px;
            position: relative;
            overflow: hidden;
            transition: var(--hasad-transition);
        }

        .login-card-box::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            left: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--hasad-primary) 0%, var(--hasad-accent) 100%);
        }

        .login-brand-icon-wrapper {
            width: 68px;
            height: 68px;
            background: linear-gradient(135deg, #ECFDF5 0%, #D1FAE5 100%);
            border: 2px solid var(--hasad-primary-border);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            color: var(--hasad-primary);
            font-size: 1.9rem;
            box-shadow: 0 6px 18px rgba(6, 95, 70, 0.15);
            animation: pulse-brand 3.5s infinite ease-in-out;
        }

        @keyframes pulse-brand {
            0%, 100% { transform: scale(1); box-shadow: 0 6px 18px rgba(6, 95, 70, 0.15); }
            50% { transform: scale(1.05); box-shadow: 0 8px 24px rgba(6, 95, 70, 0.25); }
        }

        .login-input-group .input-group-text {
            background-color: #F8FAFC;
            border-color: var(--hasad-card-border);
            color: var(--hasad-text-muted);
            border-top-right-radius: var(--hasad-radius) !important;
            border-bottom-right-radius: var(--hasad-radius) !important;
            padding: 11px 14px;
        }

        .login-input-group .form-control {
            border-radius: 0 !important;
            padding: 11px 14px;
        }

        .login-input-group .form-control:focus {
            background-color: #FFFFFF;
        }

        .btn-toggle-password {
            background-color: #F8FAFC;
            border-color: var(--hasad-card-border);
            color: var(--hasad-text-muted);
            border-top-left-radius: var(--hasad-radius) !important;
            border-bottom-left-radius: var(--hasad-radius) !important;
            border-top-right-radius: 0 !important;
            border-bottom-right-radius: 0 !important;
            padding: 11px 14px;
            cursor: pointer;
            transition: var(--hasad-transition);
        }

        .btn-toggle-password:hover {
            color: var(--hasad-primary);
            background-color: var(--hasad-primary-light);
        }

        .btn-login-submit {
            background: linear-gradient(135deg, #10B981 0%, #0D9488 100%);
            border: 1px solid rgba(255, 255, 255, 0.4);
            color: #FFFFFF;
            font-weight: 700;
            font-size: 1.02rem;
            padding: 12px;
            border-radius: var(--hasad-radius);
            box-shadow: 0 4px 14px rgba(16, 185, 129, 0.28);
            transition: var(--hasad-transition);
        }

        .btn-login-submit:hover {
            background: linear-gradient(135deg, #059669 0%, #0F766E 100%);
            box-shadow: 0 8px 24px -2px rgba(16, 185, 129, 0.45);
            transform: translateY(-2px);
            color: #FFFFFF;
        }

        .btn-login-submit:active {
            transform: scale(0.96);
        }

        .security-badge {
            background: #F8FAFC;
            border: 1px dashed #CBD5E1;
            border-radius: var(--hasad-radius);
            padding: 10px 14px;
            font-size: 0.76rem;
            color: var(--hasad-text-muted);
        }
    </style>
</head>
<body>

    <!-- خلفية الشفق الزمرّدي والنعناعي المتوهجة (Soft Aurora Mesh Gradients) -->
    <div class="aurora-bg-mesh" aria-hidden="true">
        <div class="aurora-blob aurora-blob-1"></div>
        <div class="aurora-blob aurora-blob-2"></div>
        <div class="aurora-blob aurora-blob-3"></div>
    </div>

    <div class="login-card-container">
        <div class="login-card-box spotlight-card">
            
            <!-- الهوية والشعار المؤسسي -->
            <div class="text-center mb-4">
                <div class="login-brand-icon-wrapper">
                    <i class="fa-solid fa-seedling" data-lucide="sprout"></i>
                </div>
                <h4 class="fw-bold mb-1 text-dark">نظام حصاد</h4>
                <p class="text-muted small mb-1">المنظومة الوطنية للرصد والتقييم والتحليل التعاوني</p>
                <div class="badge bg-light text-success border px-2 py-1 small">
                    <i class="fa-solid fa-building-flag me-1"></i> الجمهورية اليمنية - اللجنة الزراعية العليا
                </div>
            </div>

            <!-- رسائل التنبيه والأخطاء -->
            @if ($errors->any())
                <div class="alert alert-danger py-2 px-3 small rounded-3 d-flex align-items-center mb-3">
                    <i class="fa-solid fa-circle-exclamation fs-5 me-2 flex-shrink-0"></i>
                    <div>{{ $errors->first() }}</div>
                </div>
            @endif

            <!-- نموذج الدخول -->
            <form method="POST" action="{{ route('login') }}" id="loginForm">
                @csrf

                <!-- البريد الإلكتروني -->
                <div class="mb-3">
                    <label for="email" class="form-label small fw-bold">البريد الإلكتروني</label>
                    <div class="input-group login-input-group">
                        <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                        <input type="email" name="email" id="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               value="{{ old('email') }}" required autofocus 
                               placeholder="admin@hasad.local"
                               style="border-top-left-radius: var(--hasad-radius) !important; border-bottom-left-radius: var(--hasad-radius) !important;">
                    </div>
                </div>

                <!-- كلمة المرور مع زر إظهار/إخفاء -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label for="password" class="form-label small fw-bold mb-0">كلمة المرور</label>
                    </div>
                    <div class="input-group login-input-group">
                        <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" name="password" id="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               required placeholder="••••••••">
                        <button type="button" class="btn btn-toggle-password" id="btnTogglePassword" title="إظهار/إخفاء كلمة المرور">
                            <i class="fa-regular fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <!-- تذكرني ومساعد تسجيل الدخول السريع -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label small text-muted" for="remember">
                            تذكر تسجيل دخولي
                        </label>
                    </div>
                    <button type="button" class="btn btn-link p-0 text-decoration-none small text-success fw-semibold" id="btnFillDemo">
                        <i class="fa-solid fa-key me-1"></i> تعبئة تجريبية
                    </button>
                </div>

                <!-- زر الإرسال -->
                <button type="submit" class="btn btn-login-submit w-100">
                    <i class="fa-solid fa-arrow-right-to-bracket me-2"></i> دخول إلى النظام
                </button>
            </form>

            <!-- شارة الأمان السيبراني -->
            <div class="security-badge text-center mt-4">
                <i class="fa-solid fa-shield-halved text-success me-1"></i>
                <span>نظام مشفر ومحمي وفق معايير الأمان المؤسسي | 256-Bit TLS</span>
            </div>

            <!-- التذييل -->
            <div class="text-center mt-3 pt-2 border-top">
                <span class="text-muted" style="font-size: 0.78rem;">
                    جميع الحقوق محفوظة © {{ date('Y') }} - نظام حصاد للرصد والتقييم
                </span>
            </div>

        </div>
    </div>

    <!-- Bootstrap Bundle & Login Interactions -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // إظهار / إخفاء كلمة المرور
        const toggleBtn = document.getElementById('btnTogglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        if (toggleBtn && passwordInput && eyeIcon) {
            toggleBtn.addEventListener('click', function() {
                const isPassword = passwordInput.getAttribute('type') === 'password';
                passwordInput.setAttribute('type', isPassword ? 'text' : 'password');
                eyeIcon.className = isPassword ? 'fa-regular fa-eye-slash text-success' : 'fa-regular fa-eye';
            });
        }

        // زر التعبئة التجريبية السريعة
        const fillDemoBtn = document.getElementById('btnFillDemo');
        if (fillDemoBtn) {
            fillDemoBtn.addEventListener('click', function() {
                const emailInput = document.getElementById('email');
                if (emailInput && passwordInput) {
                    emailInput.value = 'admin@hasad.local';
                    passwordInput.value = 'password';
                    passwordInput.focus();
                }
            });
        }

        // تفعيل أيقونات Lucide
        if (typeof lucide !== 'undefined' && lucide.createIcons) {
            lucide.createIcons({ attrs: { 'stroke-width': 1.5 } });
        }

        // تأثير كشاف الضوء الأخضر اللحظي (Cursor-tracking Soft Green Spotlight)
        document.addEventListener('pointermove', function(e) {
            const card = document.querySelector('.spotlight-card');
            if (card) {
                const rect = card.getBoundingClientRect();
                const x = e.clientX - rect.left;
                const y = e.clientY - rect.top;
                card.style.setProperty('--mouse-x', x + 'px');
                card.style.setProperty('--mouse-y', y + 'px');
            }
        }, { passive: true });
    </script>
</body>
</html>
