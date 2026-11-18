<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

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
    // تهيئة مسارات التخزين المؤقت في مسار /tmp المتاح للكتابة في بيئة Vercel Serverless
    $storagePath = '/tmp/storage';
    $dirs = [
        $storagePath . '/framework/views',
        $storagePath . '/framework/cache/data',
        $storagePath . '/framework/sessions',
        $storagePath . '/logs',
        '/tmp/bootstrap/cache',
    ];

    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
    }

    putenv('VIEW_COMPILED_PATH=' . $storagePath . '/framework/views');

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
    echo "<h4>تفاصيل التتبع (Stack Trace):</h4>";
    echo "<pre style='background:#1e1e1e;color:#d4d4d4;padding:15px;border-radius:6px;overflow-x:auto;direction:ltr;text-align:left;font-size:13px;line-height:1.5;'>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    echo "</div>";
    exit(1);
}
