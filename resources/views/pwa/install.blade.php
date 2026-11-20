<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تثبيت تطبيق حصاد ERP - المنظومة الوطنية للرصد والتقييم</title>

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

    <!-- الخطوط الرسمية -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 RTL -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    
    <!-- الأيقونات -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <script src="https://unpkg.com/lucide@latest"></script>

    <!-- ملف التنسيق المؤسسي -->
    <link href="{{ asset('css/hasad-theme.css') }}" rel="stylesheet">

    <style>
        body {
            min-height: 100vh;
            background-color: var(--hasad-bg);
            padding: 30px 16px;
            font-family: 'Cairo', sans-serif;
            position: relative;
        }

        .install-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .install-card {
            background: rgba(255, 255, 255, 0.85);
            backdrop-filter: blur(28px) saturate(190%);
            -webkit-backdrop-filter: blur(28px) saturate(190%);
            border: 1px solid rgba(255, 255, 255, 0.95);
            border-radius: var(--hasad-radius-xl);
            box-shadow: 0 20px 45px -10px rgba(6, 78, 59, 0.1), inset 0 1px 0 rgba(255, 255, 255, 0.95);
            padding: 36px 30px;
        }

        .step-circle {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 1.1rem;
            margin: 0 auto 12px;
        }

        .device-badge {
            background: #ECFDF5;
            color: #065F46;
            border: 1px solid #A7F3D0;
            border-radius: 9999px;
            padding: 4px 14px;
            font-size: 0.8rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
    </style>
</head>
<body>

    <div class="install-container">
        
        <!-- الترويسة الرئيسية -->
        <div class="text-center mb-4">
            <div class="d-inline-flex p-3 rounded-4 shadow-sm mb-3" style="background: linear-gradient(135deg, #10B981 0%, #064E3B 100%);">
                <i class="fa-solid fa-seedling text-white fs-1"></i>
            </div>
            <h2 class="fw-bold text-dark mb-1">تثبيت تطبيق حصاد ERP</h2>
            <p class="text-muted small mb-2">المنظومة الوطنية للرصد والتقييم والتحليل التعاوني الزراعي</p>
            <div id="detectedPlatformBadge" class="device-badge">
                <i class="fa-solid fa-circle-notch fa-spin"></i> جاري فحص بيئة جهازك...
            </div>
        </div>

        <!-- البطاقة الرئيسية -->
        <div class="install-card shadow-lg mb-4">
            
            <!-- تنبيه التطبيق مثبت بالفعل -->
            <div id="standaloneStatusAlert" class="alert alert-success d-none mb-4 rounded-3 border-0 py-3 shadow-xs">
                <div class="d-flex align-items-center gap-3">
                    <i class="fa-solid fa-circle-check fs-3 text-success"></i>
                    <div>
                        <strong class="d-block text-dark">التطبيق مثبت ونشط بالفعل على هذا الجهاز!</strong>
                        <span class="small text-muted">أنت تتصفح نظام حصاد الآن من داخل نسخة التطبيق المستقلة.</span>
                    </div>
                </div>
            </div>

            <!-- زر التثبيت المباشر بنقرة واحدة (أندرويد / كمبيوتر) -->
            <div id="directInstallBox" class="p-4 rounded-4 border border-emerald-500/30 text-center mb-4 d-none" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(6, 78, 59, 0.05) 100%);">
                <div class="fw-bold text-success fs-5 mb-1">
                    <i class="fa-solid fa-bolt me-1"></i> جهازك متوافق مع التثبيت الفوري بنقرة واحدة!
                </div>
                <p class="text-muted small mb-3">اضغط على الزر أدناه لإضافة أيقونة تطبيق حصاد فوراً إلى شاشة هاتفك أو سطح المكتب</p>
                <button type="button" class="btn btn-success btn-lg px-5 py-3 rounded-pill fw-bold shadow-md" onclick="triggerNativePwaInstall()">
                    <i class="fa-solid fa-download me-2"></i> تثبيت التطبيق الآن
                </button>
            </div>

            <!-- تبويبات الأجهزة والمنصات -->
            <ul class="nav nav-pills nav-fill bg-light p-1 rounded-3 mb-4 border" id="installTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold small py-2 rounded-3 text-dark d-flex align-items-center justify-content-center gap-2" id="tab-android-btn" data-bs-toggle="pill" data-bs-target="#tab-android-view" type="button" role="tab">
                        <i class="fa-brands fa-android text-success fs-5"></i>
                        <span>هواتف أندرويد (Android)</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold small py-2 rounded-3 text-dark d-flex align-items-center justify-content-center gap-2" id="tab-ios-btn" data-bs-toggle="pill" data-bs-target="#tab-ios-view" type="button" role="tab">
                        <i class="fa-brands fa-apple text-dark fs-5"></i>
                        <span>آيفون وآيباد (iOS)</span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold small py-2 rounded-3 text-dark d-flex align-items-center justify-content-center gap-2" id="tab-pc-btn" data-bs-toggle="pill" data-bs-target="#tab-pc-view" type="button" role="tab">
                        <i class="fa-solid fa-laptop text-primary fs-5"></i>
                        <span>الكمبيوتر واللابتوب (PC / Mac)</span>
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="installTabsContent">
                
                <!-- 1. دليل أندرويد -->
                <div class="tab-pane fade show active" id="tab-android-view" role="tabpanel">
                    <div class="text-center mb-4">
                        <h5 class="fw-bold text-dark mb-1">خطوات تثبيت حصاد على أجهزة أندرويد</h5>
                        <p class="text-muted small">يعمل على سامسونج، شاومي، هواوي، أوبو، فيفو، وكافة أجهزة أندرويد</p>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 rounded-4 bg-white border text-center h-100 shadow-xs">
                                <div class="step-circle bg-emerald-50 text-success">1</div>
                                <h6 class="fw-bold mb-1">قائمة الخيارات</h6>
                                <p class="text-muted small mb-0" style="font-size: 0.8rem; line-height: 1.5;">
                                    افتح الرابط في متصفح Chrome أو متصفح الجهاز، ثم اضغط على النقاط الثلاث <i class="fa-solid fa-ellipsis-vertical text-muted"></i> في أعلى أو أسفل الزاوية.
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded-4 bg-white border text-center h-100 shadow-xs">
                                <div class="step-circle bg-emerald-50 text-success">2</div>
                                <h6 class="fw-bold mb-1">تثبيت التطبيق</h6>
                                <p class="text-muted small mb-0" style="font-size: 0.8rem; line-height: 1.5;">
                                    انقر على خيار <strong class="text-dark">"تثبيت التطبيق" (Install App)</strong> أو "إضافة إلى الشاشة الرئيسية".
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded-4 bg-white border text-center h-100 shadow-xs">
                                <div class="step-circle bg-emerald-50 text-success">3</div>
                                <h6 class="fw-bold mb-1">جاهز للاستخدام</h6>
                                <p class="text-muted small mb-0" style="font-size: 0.8rem; line-height: 1.5;">
                                    ستظهر أيقونة حصاد على شاشتك فوراً، افتحها لتجربة تطبيق كامل وسريع بدون إطار المتصفح.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 2. دليل آيفون وآيباد -->
                <div class="tab-pane fade" id="tab-ios-view" role="tabpanel">
                    <div class="text-center mb-3">
                        <h5 class="fw-bold text-dark mb-1">خطوات تثبيت حصاد على هواتف iPhone وأجهزة iPad</h5>
                        <p class="text-muted small">يرجى فتح الرابط عبر متصفح <strong>Safari</strong> الرسمي التابع لشركة آبل</p>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="p-3 rounded-4 bg-white border text-center h-100 shadow-xs">
                                <div class="step-circle bg-primary-subtle text-primary">1</div>
                                <h6 class="fw-bold mb-1">زر المشاركة (Share)</h6>
                                <p class="text-muted small mb-0" style="font-size: 0.8rem; line-height: 1.5;">
                                    اضغط على زر المشاركة <i class="fa-solid fa-arrow-up-from-bracket text-primary"></i> في شريط Safari أسفل الشاشة (أو بأعلى الشاشة في iPad).
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded-4 bg-white border text-center h-100 shadow-xs">
                                <div class="step-circle bg-primary-subtle text-primary">2</div>
                                <h6 class="fw-bold mb-1">إضافة للشاشة الرئيسية</h6>
                                <p class="text-muted small mb-0" style="font-size: 0.8rem; line-height: 1.5;">
                                    مرر قائمة الخيارات لأسفل قليلاً واضغط على <strong class="text-dark">"إضافة إلى الصفحة الرئيسية"</strong> (Add to Home Screen) <i class="fa-regular fa-square-plus text-primary"></i>.
                                </p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded-4 bg-white border text-center h-100 shadow-xs">
                                <div class="step-circle bg-primary-subtle text-primary">3</div>
                                <h6 class="fw-bold mb-1">تأكيد الإضافة</h6>
                                <p class="text-muted small mb-0" style="font-size: 0.8rem; line-height: 1.5;">
                                    اضغط على زر <strong class="text-dark">"إضافة" (Add)</strong> في أعلى الزاوية. سيتحول النظام فوراً إلى تطبيق مستقل على شاشة جهازك!
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- 3. دليل أجهزة اللابتوب والكمبيوتر -->
                <div class="tab-pane fade" id="tab-pc-view" role="tabpanel">
                    <div class="text-center mb-4">
                        <h5 class="fw-bold text-dark mb-1">تثبيت التطبيق على أجهزة الحاسوب واللابتوب</h5>
                        <p class="text-muted small">أنظمة ويندوز (Windows 10 / 11) وماك (macOS) ولينكس (Linux)</p>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-4 rounded-4 bg-white border h-100 shadow-xs">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <i class="fa-brands fa-chrome text-warning fs-3"></i>
                                    <div>
                                        <h6 class="fw-bold mb-0">Google Chrome</h6>
                                        <span class="text-muted small">شريط العنوان أو القائمة</span>
                                    </div>
                                </div>
                                <p class="text-muted small mb-0" style="line-height: 1.6;">
                                    1. في نهاية شريط العنوان بالأعلى، ستلاحظ أيقونة كمبيوتر صغيرة بها سهم <i class="fa-solid fa-download text-muted"></i>.
                                    <br>
                                    2. انقر عليها واختر <strong>"تثبيت" (Install)</strong>.
                                    <br>
                                    3. سيتم تثبيت حصاد ERP فوراً كنافذة برنامج مستقلة على سطح المكتب وقائمة Start.
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-4 rounded-4 bg-white border h-100 shadow-xs">
                                <div class="d-flex align-items-center gap-2 mb-3">
                                    <i class="fa-brands fa-edge text-primary fs-3"></i>
                                    <div>
                                        <h6 class="fw-bold mb-0">Microsoft Edge</h6>
                                        <span class="text-muted small">شريط المهام أو قائمة التطبيقات</span>
                                    </div>
                                </div>
                                <p class="text-muted small mb-0" style="line-height: 1.6;">
                                    1. انقر على أيقونة التطبيقات في شريط العنوان أو القائمة (•••).
                                    <br>
                                    2. اختر <strong>"تطبيقات" (Apps)</strong> ثم <strong>"تثبيت هذا الموقع كتطبيق"</strong>.
                                    <br>
                                    3. يمكنك تثبيته في شريط المهام (Taskbar) للوصول بنقرة واحدة دائماً.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- أزرار الإجراءات السريعة -->
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-4 pt-4 border-top">
                <a href="{{ route('dashboard') }}" class="btn btn-outline-success rounded-pill px-4 py-2 fw-semibold">
                    <i class="fa-solid fa-gauge me-1"></i> الانتقال إلى لوحة التحكم
                </a>
                <a href="{{ route('login') }}" class="btn btn-success rounded-pill px-4 py-2 fw-semibold shadow-sm">
                    <i class="fa-solid fa-arrow-right-to-bracket me-1"></i> شاشة تسجيل الدخول
                </a>
            </div>

        </div>

        <!-- تذييل الصفحة -->
        <div class="text-center text-muted small">
            <span>جميع الحقوق محفوظة © {{ date('Y') }} - نظام حصاد للرصد والتقييم المؤسسي</span>
        </div>

    </div>

    <!-- Bootstrap Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/pwa-install.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const device = window.getHasadDeviceType();
            const badge = document.getElementById('detectedPlatformBadge');
            const directBox = document.getElementById('directInstallBox');
            const standaloneAlert = document.getElementById('standaloneStatusAlert');

            if (device.isStandalone && standaloneAlert) {
                standaloneAlert.classList.remove('d-none');
            }

            if (badge) {
                if (device.isIOS) {
                    badge.innerHTML = '<i class="fa-brands fa-apple"></i> جهازك المكتشف: آيفون / آيباد (Apple iOS)';
                    const tabBtn = document.getElementById('tab-ios-btn');
                    if (tabBtn) new bootstrap.Tab(tabBtn).show();
                } else if (device.isAndroid) {
                    badge.innerHTML = '<i class="fa-brands fa-android"></i> جهازك المكتشف: هاتف أندرويد (Android)';
                    const tabBtn = document.getElementById('tab-android-btn');
                    if (tabBtn) new bootstrap.Tab(tabBtn).show();
                } else {
                    badge.innerHTML = '<i class="fa-solid fa-laptop"></i> جهازك المكتشف: كمبيوتر / لابتوب (PC / Mac)';
                    const tabBtn = document.getElementById('tab-pc-btn');
                    if (tabBtn) new bootstrap.Tab(tabBtn).show();
                }
            }

            window.addEventListener('beforeinstallprompt', function () {
                if (directBox) directBox.classList.remove('d-none');
            });
        });
    </script>
</body>
</html>
