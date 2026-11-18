<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// تهيئة مسارات التخزين المؤقت في مسار /tmp المتاح للكتابة في بيئة Vercel Serverless
$storagePath = '/tmp/storage';
$cacheDir = '/tmp/bootstrap/cache';

$dirs = [
    $storagePath . '/framework/views',
    $storagePath . '/framework/cache/data',
    $storagePath . '/framework/sessions',
    $storagePath . '/logs',
    $cacheDir,
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }
}

// توجيه كاش الحزم والخدمات والفيوز إلى مسار /tmp القابل للكتابة
$_ENV['APP_SERVICES_CACHE'] = $cacheDir . '/services.php';
$_ENV['APP_PACKAGES_CACHE'] = $cacheDir . '/packages.php';
$_ENV['APP_CONFIG_CACHE'] = $cacheDir . '/config.php';
$_ENV['APP_ROUTES_CACHE'] = $cacheDir . '/routes-v7.php';
$_ENV['APP_EVENTS_CACHE'] = $cacheDir . '/events.php';
$_ENV['VIEW_COMPILED_PATH'] = $storagePath . '/framework/views';

putenv('APP_SERVICES_CACHE=' . $cacheDir . '/services.php');
putenv('APP_PACKAGES_CACHE=' . $cacheDir . '/packages.php');
putenv('APP_CONFIG_CACHE=' . $cacheDir . '/config.php');
putenv('APP_ROUTES_CACHE=' . $cacheDir . '/routes-v7.php');
putenv('APP_EVENTS_CACHE=' . $cacheDir . '/events.php');
putenv('VIEW_COMPILED_PATH=' . $storagePath . '/framework/views');

// نسخ ملفات الكاش الجاهزة إلى /tmp إن وُجدت لتسريع الإقلاع وتفادي إعادة بنائها
foreach (['packages.php', 'services.php'] as $file) {
    $src = __DIR__ . '/../bootstrap/cache/' . $file;
    $dst = $cacheDir . '/' . $file;
    if (file_exists($src) && !file_exists($dst)) {
        @copy($src, $dst);
    }
}

// التقاط أي أخطاء إقلاع فادحة (Fatal Errors) وعرضها بدقة
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        http_response_code(500);
        echo "<div style='font-family:sans-serif;padding:30px;background:#fff1f0;border:2px solid #ff4d4f;margin:20px;border-radius:8px;direction:rtl;'>";
        echo "<h2 style='color:#cf1322;margin-top:0;'>⚠️ خطأ إقلاع فادح في خادم PHP (Vercel Serverless)</h2>";
        echo "<p><strong>الخطأ:</strong> " . htmlspecialchars($error['message']) . "</p>";
        echo "<p><strong>الملف:</strong> " . htmlspecialchars($error['file']) . " (السطر " . $error['line'] . ")</p>";
        echo "</div>";
    }
});

try {
    define('LARAVEL_START', microtime(true));

    if (!file_exists(__DIR__ . '/../vendor/autoload.php')) {
        throw new \Exception("لم يتم العثور على مجلد الحزم vendor/autoload.php. يرجى التأكد من تشغيل composer install.");
    }

    require __DIR__ . '/../vendor/autoload.php';

    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $app->useStoragePath($storagePath);

    $app->handleRequest(\Illuminate\Http\Request::capture());
} catch (\Throwable $e) {
    http_response_code(500);
    echo "<div style='font-family:sans-serif;padding:30px;background:#fffbe6;border:2px solid #ffe58f;margin:20px;border-radius:8px;direction:rtl;'>";
    echo "<h2 style='color:#d46b08;margin-top:0;'>⚠️ تشخيص خطأ السيرفر في نظام حصاد (Vercel)</h2>";
    echo "<p><strong>نوع الخطأ:</strong> <code style='background:#eee;padding:3px 8px;border-radius:4px;'>" . get_class($e) . "</code></p>";
    echo "<p><strong>رسالة الخطأ:</strong> <code style='background:#eee;padding:3px 8px;border-radius:4px;'>" . htmlspecialchars($e->getMessage()) . "</code></p>";
    echo "<p><strong>الملف:</strong> " . htmlspecialchars($e->getFile()) . " (السطر " . $e->getLine() . ")</p>";

    if ($prev = $e->getPrevious()) {
        echo "<div style='margin-top:15px;padding:10px;background:#fff;border:1px solid #ffd591;border-radius:4px;'>";
        echo "<p><strong>السبب الأصلي المقترن (Previous Exception):</strong></p>";
        echo "<p><strong>النوع:</strong> " . get_class($prev) . "</p>";
        echo "<p><strong>الرسالة:</strong> " . htmlspecialchars($prev->getMessage()) . "</p>";
        echo "<p><strong>الملف:</strong> " . htmlspecialchars($prev->getFile()) . " (" . $prev->getLine() . ")</p>";
        echo "</div>";
    }

    echo "<h4>تفاصيل التتبع (Stack Trace):</h4>";
    echo "<pre style='background:#1e1e1e;color:#d4d4d4;padding:15px;border-radius:6px;overflow-x:auto;direction:ltr;text-align:left;font-size:13px;line-height:1.5;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
    exit(1);
}
