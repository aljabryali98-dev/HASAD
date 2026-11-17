<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>تسجيل الدخول - نظام حصاد</title>

    <!-- خط تجوال -->
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 RTL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    
    <!-- الأيقونات (FontAwesome) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --primary-color: #0162e8;
            --bg-light: #eef1f6;
        }
        body, html {
            height: 100%;
            font-family: 'Tajawal', sans-serif !important;
            background-color: var(--bg-light);
            direction: rtl;
        }
        .login-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 8px 24px rgba(1, 98, 232, 0.08);
            background: #ffffff;
        }
        .btn-primary-custom {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: #ffffff;
            font-weight: 700;
            padding: 10px;
            transition: all 0.2s ease-in-out;
        }
        .btn-primary-custom:hover {
            background-color: #0152c2;
            border-color: #0152c2;
            color: #ffffff;
        }
        .brand-icon {
            font-size: 2.2rem;
            color: var(--primary-color);
        }
        .form-floating > label {
            right: 0;
            left: auto;
            padding: 1rem 0.85rem;
        }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center py-5">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card login-card p-4">
                    
                    <div class="text-center mb-4">
                        <div class="brand-icon mb-2">
                            <i class="fa-solid fa-seedling"></i>
                        </div>
                        <h4 class="fw-bold mb-1">نظام حصاد</h4>
                        <p class="text-muted small">تسجيل الدخول إلى لوحة التحكم</p>
                    </div>

                    @if ($errors->any())
                        <div class="alert alert-danger py-2 small mb-3">
                            <i class="fa-solid fa-circle-exclamation me-1"></i>
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label fw-bold small text-secondary">البريد الإلكتروني</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-envelope"></i></span>
                                <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus placeholder="name@hasad.local">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label fw-bold small text-secondary">كلمة المرور</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-lock"></i></span>
                                <input type="password" name="password" id="password" class="form-control @error('password') is-invalid @enderror" required placeholder="••••••••">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="form-check-label small text-secondary" for="remember">
                                    تذكرني على هذا الجهاز
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary-custom w-100 rounded">
                            <i class="fa-solid fa-right-to-bracket me-1"></i> دخول
                        </button>
                    </form>

                    <div class="text-center mt-4 pt-3 border-top">
                        <span class="text-muted small">جميع الحقوق محفوظة © {{ date('Y') }} - تطبيق حصاد</span>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
