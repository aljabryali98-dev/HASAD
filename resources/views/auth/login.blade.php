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
    
    <!-- الأيقونات (FontAwesome) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">

    <!-- ملف التنسيق المؤسسي -->
    <link href="{{ asset('css/hasad-theme.css') }}" rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at 50% 20%, rgba(6, 95, 70, 0.05) 0%, rgba(248, 250, 252, 1) 70%);
            padding: 24px;
        }

        .login-card-container {
            max-width: 440px;
            width: 100%;
        }

        .login-card-box {
            background: #FFFFFF;
            border: 1px solid var(--hasad-card-border);
            border-radius: var(--hasad-radius-xl);
            box-shadow: 0 10px 30px -5px rgba(0, 0, 0, 0.06), 0 4px 12px -2px rgba(0, 0, 0, 0.03);
            padding: 36px 32px;
            position: relative;
            overflow: hidden;
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
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, var(--hasad-primary-light) 0%, #D1FAE5 100%);
            border: 2px solid var(--hasad-primary-border);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            color: var(--hasad-primary);
            font-size: 1.8rem;
            box-shadow: 0 4px 14px rgba(6, 95, 70, 0.12);
            animation: pulse-brand 3s infinite ease-in-out;
        }

        @keyframes pulse-brand {
            0%, 100% { transform: scale(1); box-shadow: 0 4px 14px rgba(6, 95, 70, 0.12); }
            50% { transform: scale(1.04); box-shadow: 0 6px 20px rgba(6, 95, 70, 0.2); }
        }

        .login-input-group .input-group-text {
            background-color: #F8FAFC;
            border-color: var(--hasad-card-border);
            color: var(--hasad-text-muted);
            border-top-right-radius: var(--hasad-radius) !important;
            border-bottom-right-radius: var(--hasad-radius) !important;
            padding: 10px 14px;
        }

        .login-input-group .form-control {
            border-top-left-radius: var(--hasad-radius) !important;
            border-bottom-left-radius: var(--hasad-radius) !important;
            padding: 11px 14px;
        }

        .login-input-group .form-control:focus {
            background-color: #FFFFFF;
        }

        .btn-login-submit {
            background: linear-gradient(135deg, var(--hasad-primary) 0%, var(--hasad-primary-hover) 100%);
            border: none;
            color: #FFFFFF;
            font-weight: 700;
            font-size: 1rem;
            padding: 12px;
            border-radius: var(--hasad-radius);
            box-shadow: 0 4px 12px rgba(6, 95, 70, 0.25);
            transition: var(--hasad-transition);
        }

        .btn-login-submit:hover {
            background: linear-gradient(135deg, var(--hasad-primary-hover) 0%, var(--hasad-primary-active) 100%);
            box-shadow: 0 6px 18px rgba(6, 95, 70, 0.35);
            transform: translateY(-1px);
            color: #FFFFFF;
        }
    </style>
</head>
<body>

    <div class="login-card-container">
        <div class="login-card-box">
            
            <!-- الهوية والشعار -->
            <div class="text-center mb-4">
                <div class="login-brand-icon-wrapper">
                    <i class="fa-solid fa-seedling"></i>
                </div>
                <h4 class="fw-bold mb-1 text-dark">نظام حصاد</h4>
                <p class="text-muted small mb-0">المنظومة الوطنية للرصد والتقييم والتحليل التعاوني</p>
                <span class="badge bg-light text-success border px-2 py-1 mt-2 small">
                    <i class="fa-solid fa-shield-halved me-1"></i> بوابة الدخول المركزية الموحدة
                </span>
            </div>

            <!-- رسائل التنبيه والأخطاء -->
            @if ($errors->any())
                <div class="alert alert-danger py-2 px-3 small rounded-3 d-flex align-items-center mb-3">
                    <i class="fa-solid fa-circle-exclamation fs-5 me-2 flex-shrink-0"></i>
                    <div>{{ $errors->first() }}</div>
                </div>
            @endif

            <!-- نموذج الدخول -->
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <!-- البريد الإلكتروني -->
                <div class="mb-3">
                    <label for="email" class="form-label small fw-bold">البريد الإلكتروني</label>
                    <div class="input-group login-input-group">
                        <span class="input-group-text"><i class="fa-solid fa-envelope"></i></span>
                        <input type="email" name="email" id="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               value="{{ old('email') }}" required autofocus 
                               placeholder="admin@hasad.local">
                    </div>
                </div>

                <!-- كلمة المرور -->
                <div class="mb-3">
                    <label for="password" class="form-label small fw-bold">كلمة المرور</label>
                    <div class="input-group login-input-group">
                        <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                        <input type="password" name="password" id="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               required placeholder="••••••••">
                    </div>
                </div>

                <!-- تذكرني -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                        <label class="form-check-label small text-muted" for="remember">
                            تذكر تسجيل دخولي
                        </label>
                    </div>
                </div>

                <!-- زر الإرسال -->
                <button type="submit" class="btn btn-login-submit w-100">
                    <i class="fa-solid fa-arrow-right-to-bracket me-2"></i> دخول إلى النظام
                </button>
            </form>

            <!-- التذييل -->
            <div class="text-center mt-4 pt-3 border-top">
                <span class="text-muted" style="font-size: 0.78rem;">
                    جميع الحقوق محفوظة © {{ date('Y') }} - نظام حصاد للرصد والتقييم
                </span>
            </div>

        </div>
    </div>

    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
