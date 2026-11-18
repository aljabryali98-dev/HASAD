<?php

// تهيئة مسارات التخزين المؤقت في مسار /tmp المتاح للكتابة في بيئة Vercel Serverless
$storagePath = '/tmp/storage';
$dirs = [
    $storagePath . '/framework/views',
    $storagePath . '/framework/cache/data',
    $storagePath . '/framework/sessions',
    $storagePath . '/logs',
];

foreach ($dirs as $dir) {
    if (!is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

putenv('VIEW_COMPILED_PATH=' . $storagePath . '/framework/views');

define('LARAVEL_START', microtime(true));

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->useStoragePath($storagePath);

$app->handleRequest(\Illuminate\Http\Request::capture());
