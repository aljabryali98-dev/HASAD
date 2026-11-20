<!-- Modal تثبيت تطبيق حصاد على كافة الأجهزة (Android, iOS, Windows, Mac) -->
<div class="modal fade" id="hasadPwaInstallModal" tabindex="-1" aria-labelledby="hasadPwaInstallModalLabel" aria-hidden="true" dir="rtl">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-2xl rounded-4 overflow-hidden" style="background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px);">
            
            <!-- ترويسة النافذة المنبثقة الفاخرة -->
            <div class="modal-header border-0 pb-0 pt-4 px-4 position-relative">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-3 p-2 d-flex align-items-center justify-content-center shadow-sm" style="width: 52px; height: 52px; background: linear-gradient(135deg, #10B981 0%, #064E3B 100%);">
                        <i class="fa-solid fa-seedling text-white fs-3"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="modal-title fw-bold text-dark mb-0" id="hasadPwaInstallModalLabel">تثبيت تطبيق حصاد ERP</h5>
                            <span class="badge bg-success-subtle text-success border border-success-subtle font-monospace" style="font-size: 0.7rem;">PWA App</span>
                        </div>
                        <p class="text-muted small mb-0 mt-1">تطبيق مستقل وسريع يعمل على الهواتف واللابتوب بدون الحاجة لمتجر تطبيقات</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
            </div>

            <!-- تنبيه في حال كان التطبيق مثبتاً ويعمل كـ Standalone بالفعل -->
            <div id="pwaAlreadyInstalledAlert" class="alert alert-success mx-4 my-3 d-none d-flex align-items-center gap-2 border-0 rounded-3 shadow-sm py-2">
                <i class="fa-solid fa-circle-check fs-5 text-success"></i>
                <div class="small fw-semibold">
                    التطبيق مثبت بالفعل ويعمل كنسخة تطبيق مستقلة على هذا الجهاز!
                </div>
            </div>

            <div class="modal-body px-4 pt-3 pb-4">
                
                <!-- زر التثبيت المباشر الفوري (لأندرويد ولابتوب عند دعم المتصفح) -->
                <div id="pwaNativeDirectInstallBtn" class="mb-4 d-none">
                    <div class="p-3 rounded-4 border border-emerald-500/30 text-center shadow-sm" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.08) 0%, rgba(6, 78, 59, 0.04) 100%);">
                        <div class="fw-bold text-success mb-1">
                            <i class="fa-solid fa-bolt me-1"></i> جهازك جاهز للتثبيت الفوري بنقرة واحدة!
                        </div>
                        <p class="text-muted small mb-3">اضغط على الزر أدناه لتثبيت نظام حصاد مباشرة على شاشة جهازك كـ تطبيق مستقل</p>
                        <button type="button" class="btn btn-success px-4 py-2 rounded-pill fw-bold shadow-sm" onclick="triggerNativePwaInstall()">
                            <i class="fa-solid fa-download me-2"></i> تثبيت التطبيق الآن على جهازي
                        </button>
                    </div>
                </div>

                <!-- تبويبات اختيار نوع الجهاز -->
                <ul class="nav nav-pills nav-fill bg-light p-1 rounded-3 mb-3 border" id="pwaDeviceTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold small py-2 rounded-3 text-dark d-flex align-items-center justify-content-center gap-2" id="tab-btn-android" data-bs-toggle="pill" data-bs-target="#tab-content-android" type="button" role="tab">
                            <i class="fa-brands fa-android text-success fs-5"></i>
                            <span>هواتف أندرويد (Android)</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold small py-2 rounded-3 text-dark d-flex align-items-center justify-content-center gap-2" id="tab-btn-ios" data-bs-toggle="pill" data-bs-target="#tab-content-ios" type="button" role="tab">
                            <i class="fa-brands fa-apple text-dark fs-5"></i>
                            <span>آيفون وآيباد (iOS Safari)</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold small py-2 rounded-3 text-dark d-flex align-items-center justify-content-center gap-2" id="tab-btn-desktop" data-bs-toggle="pill" data-bs-target="#tab-content-desktop" type="button" role="tab">
                            <i class="fa-solid fa-laptop text-primary fs-5"></i>
                            <span>الكمبيوتر واللابتوب (PC / Mac)</span>
                        </button>
                    </li>
                </ul>

                <!-- محتويات التبويبات -->
                <div class="tab-content" id="pwaDeviceTabsContent">
                    
                    <!-- 1. تبويب هواتف أندرويد -->
                    <div class="tab-pane fade show active" id="tab-content-android" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="p-3 rounded-3 bg-white border h-100 text-center shadow-xs">
                                    <div class="rounded-circle bg-emerald-50 text-success fw-bold mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">1</div>
                                    <h6 class="fw-bold small mb-1">افتح القائمة</h6>
                                    <p class="text-muted small mb-0" style="font-size: 0.78rem;">
                                        في متصفح Chrome أو سامسونج، اضغط على زر الخيارات <i class="fa-solid fa-ellipsis-vertical text-muted"></i> أعلى أو أسفل الشاشة.
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 rounded-3 bg-white border h-100 text-center shadow-xs">
                                    <div class="rounded-circle bg-emerald-50 text-success fw-bold mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">2</div>
                                    <h6 class="fw-bold small mb-1">اختر تثبيت التطبيق</h6>
                                    <p class="text-muted small mb-0" style="font-size: 0.78rem;">
                                        اضغط على <span class="fw-semibold text-dark">"تثبيت التطبيق"</span> (Install App) أو <span class="fw-semibold text-dark">"إضافة إلى الشاشة الرئيسية"</span>.
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 rounded-3 bg-white border h-100 text-center shadow-xs">
                                    <div class="rounded-circle bg-emerald-50 text-success fw-bold mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">3</div>
                                    <h6 class="fw-bold small mb-1">تأكيد التثبيت</h6>
                                    <p class="text-muted small mb-0" style="font-size: 0.78rem;">
                                        اضغط تأكيد، وسيظهر تطبيق حصاد فوراً على شاشة هاتفك الرئيسية ويعمل بسرعة فائقة وبملء الشاشة.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 2. تبويب أجهزة آبل (آيفون وآيباد) -->
                    <div class="tab-pane fade" id="tab-content-ios" role="tabpanel">
                        <div class="alert alert-info py-2 px-3 small d-flex align-items-center gap-2 mb-3 rounded-3 border-0 bg-info-subtle text-info-emphasis">
                            <i class="fa-brands fa-safari fs-5"></i>
                            <span>على أجهزة آيفون وآيباد، يرجى فتح الرابط عبر متصفح <strong>Safari</strong> الرسمي.</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="p-3 rounded-3 bg-white border h-100 text-center shadow-xs">
                                    <div class="rounded-circle bg-primary-subtle text-primary fw-bold mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">1</div>
                                    <h6 class="fw-bold small mb-1">زر المشاركة (Share)</h6>
                                    <p class="text-muted small mb-0" style="font-size: 0.78rem;">
                                        اضغط على زر المشاركة <i class="fa-solid fa-arrow-up-from-bracket text-primary"></i> الموجود في شريط متصفح Safari السفلي (أو العلوي في الآيباد).
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 rounded-3 bg-white border h-100 text-center shadow-xs">
                                    <div class="rounded-circle bg-primary-subtle text-primary fw-bold mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">2</div>
                                    <h6 class="fw-bold small mb-1">إضافة للشاشة الرئيسية</h6>
                                    <p class="text-muted small mb-0" style="font-size: 0.78rem;">
                                        مرر القائمة للأسفل قليلاً واختر <span class="fw-semibold text-dark">"إضافة إلى الصفحة الرئيسية"</span> (Add to Home Screen) <i class="fa-regular fa-square-plus text-primary"></i>.
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 rounded-3 bg-white border h-100 text-center shadow-xs">
                                    <div class="rounded-circle bg-primary-subtle text-primary fw-bold mx-auto mb-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">3</div>
                                    <h6 class="fw-bold small mb-1">اضغط إضافة (Add)</h6>
                                    <p class="text-muted small mb-0" style="font-size: 0.78rem;">
                                        اضغط على زر <span class="fw-semibold text-dark">"إضافة"</span> في أعلى الزاوية، وسيظهر تطبيق حصاد كأيقونة مستقلة فوراً في شاشتك!
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 3. تبويب الكمبيوتر واللابتوب -->
                    <div class="tab-pane fade" id="tab-content-desktop" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="p-3 rounded-3 bg-white border h-100 shadow-xs">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="fa-brands fa-chrome text-warning fs-4"></i>
                                        <h6 class="fw-bold small mb-0">متصفح Google Chrome</h6>
                                    </div>
                                    <p class="text-muted small mb-2" style="font-size: 0.78rem; line-height: 1.5;">
                                        ستجد أيقونة تثبيت صغيرة <i class="fa-solid fa-download text-muted"></i> في نهاية شريط العنوان (URL bar) بالأعلى، انقر عليها ثم اختر <strong>"تثبيت" (Install)</strong>. أو افتح القائمة (⋮) ثم "حفظ ومشاركة" ثم "تثبيت تطبيق حصاد".
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="p-3 rounded-3 bg-white border h-100 shadow-xs">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="fa-brands fa-edge text-primary fs-4"></i>
                                        <h6 class="fw-bold small mb-0">متصفح Microsoft Edge</h6>
                                    </div>
                                    <p class="text-muted small mb-2" style="font-size: 0.78rem; line-height: 1.5;">
                                        انقر على أيقونة التطبيقات <i class="fa-solid fa-table-cells text-muted"></i> أو أيقونة التثبيت في شريط العنوان، ثم اختر <strong>"تثبيت هذا الموقع كتطبيق"</strong> ليعمل كنافذة مستقلة على سطح المكتب وقائمة ابدأ.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <!-- مزايا التطبيق المستقل PWA -->
                <div class="mt-4 pt-3 border-top">
                    <div class="row g-2 text-center">
                        <div class="col-4">
                            <div class="d-flex align-items-center justify-content-center gap-1 text-muted small">
                                <i class="fa-solid fa-gauge-high text-success"></i>
                                <span style="font-size: 0.75rem;">سرعة استجابة فائقة</span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="d-flex align-items-center justify-content-center gap-1 text-muted small">
                                <i class="fa-solid fa-cloud-arrow-down text-success"></i>
                                <span style="font-size: 0.75rem;">دعم العمل دون انقطاع</span>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="d-flex align-items-center justify-content-center gap-1 text-muted small">
                                <i class="fa-solid fa-shield-halved text-success"></i>
                                <span style="font-size: 0.75rem;">أمان مشفر 256-Bit</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ذيل النافذة -->
            <div class="modal-footer border-0 bg-light py-2 px-4 d-flex justify-content-between align-items-center">
                <span class="text-muted small" style="font-size: 0.75rem;">
                    نظام حصاد للرصد والتقييم المؤسسي | PWA v1.0
                </span>
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">إغلاق</button>
            </div>

        </div>
    </div>
</div>
