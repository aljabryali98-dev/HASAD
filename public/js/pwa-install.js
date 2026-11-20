/**
 * Hasad ERP - Progressive Web App (PWA) Universal Installer
 * Supports: Android, iOS (iPhone & iPad), Windows, macOS, Linux, ChromeOS
 */

(function () {
    'use strict';

    let deferredPrompt = null;

    // 1. تسجيل الـ Service Worker
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js', { scope: '/' })
                .then(function (registration) {
                    console.log('[Hasad PWA] Service Worker مسجل بنجاح بنطاق:', registration.scope);
                })
                .catch(function (error) {
                    console.warn('[Hasad PWA] تعذر تسجيل Service Worker:', error);
                });
        });
    }

    // 2. التقاط حدث التثبيت التلقائي (Chrome / Edge / Android)
    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        deferredPrompt = e;
        window.hasadDeferredPrompt = e;

        // إظهار أزرار التثبيت المباشرة في واجهة المستخدم
        document.querySelectorAll('.hasad-pwa-badge').forEach(function (el) {
            el.classList.remove('d-none');
        });

        const nativeBtn = document.getElementById('pwaNativeDirectInstallBtn');
        if (nativeBtn) {
            nativeBtn.classList.remove('d-none');
        }
    });

    // 3. الاستماع لنجاح التثبيت
    window.addEventListener('appinstalled', function () {
        deferredPrompt = null;
        window.hasadDeferredPrompt = null;
        console.log('[Hasad PWA] تم تثبيت تطبيق حصاد بنجاح على الجهاز!');
        
        // إغلاق النافذة وتحديث الشارة إذا كانت مفتوحة
        const modalEl = document.getElementById('hasadPwaInstallModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();
        }

        const successNotice = document.getElementById('pwaInstalledNotice');
        if (successNotice) {
            successNotice.classList.remove('d-none');
        }
    });

    // 4. دالة فحص بيئة ونوع جهاز المستخدم
    window.getHasadDeviceType = function () {
        const userAgent = navigator.userAgent || navigator.vendor || window.opera;
        const isIOS = /iPad|iPhone|iPod/.test(userAgent) && !window.MSStream || 
                      (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
        const isAndroid = /android/i.test(userAgent);
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches || 
                             window.navigator.standalone === true;

        return {
            isIOS: isIOS,
            isAndroid: isAndroid,
            isDesktop: !isIOS && !isAndroid,
            isStandalone: isStandalone
        };
    };

    // 5. المشغل الرئيسي لنافذة التثبيت
    window.handlePwaInstallClick = function () {
        const device = window.getHasadDeviceType();

        // إذا كان النظام يعمل بالفعل كتطبيق مستقل
        if (device.isStandalone) {
            const installedNotice = document.getElementById('pwaAlreadyInstalledAlert');
            if (installedNotice) installedNotice.classList.remove('d-none');
        }

        // فتح النافذة المنبثقة
        const modalEl = document.getElementById('hasadPwaInstallModal');
        if (modalEl && typeof bootstrap !== 'undefined') {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();

            // تفعيل التبويب الأنسب لنوع الجهاز تلقائياً
            setTimeout(function () {
                let targetTabBtn = null;
                if (device.isIOS) {
                    targetTabBtn = document.getElementById('tab-btn-ios');
                } else if (device.isAndroid) {
                    targetTabBtn = document.getElementById('tab-btn-android');
                } else {
                    targetTabBtn = document.getElementById('tab-btn-desktop');
                }

                if (targetTabBtn && typeof bootstrap !== 'undefined' && bootstrap.Tab) {
                    const tab = new bootstrap.Tab(targetTabBtn);
                    tab.show();
                }
            }, 150);
        }
    };

    // 6. تشغيل التثبيت المباشر بنقرة واحدة (للأندرويد والكمبيوتر عند توفر الـ prompt)
    window.triggerNativePwaInstall = function () {
        const promptEvent = deferredPrompt || window.hasadDeferredPrompt;
        if (!promptEvent) {
            // التوجيه للخطوات اليدوية إن لم يكن الحدث جاهزاً
            alert('يرجى النقر على قائمة المتصفح (⋮) ثم اختيار "تثبيت التطبيق" أو "إضافة إلى الشاشة الرئيسية"');
            return;
        }

        promptEvent.prompt();
        promptEvent.userChoice.then(function (choiceResult) {
            if (choiceResult.outcome === 'accepted') {
                console.log('[Hasad PWA] وافق المستخدم على تثبيت التطبيق');
            } else {
                console.log('[Hasad PWA] رفض المستخدم تثبيت التطبيق');
            }
            deferredPrompt = null;
            window.hasadDeferredPrompt = null;
        });
    };

})();
