<?php

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

putenv('APP_CONFIG_CACHE=/tmp/bootstrap/cache/config.php');
putenv('APP_EVENTS_CACHE=/tmp/bootstrap/cache/events.php');
putenv('APP_PACKAGES_CACHE=/tmp/bootstrap/cache/packages.php');
putenv('APP_ROUTES_CACHE=/tmp/bootstrap/cache/routes.php');
putenv('APP_SERVICES_CACHE=/tmp/bootstrap/cache/services.php');
putenv('VIEW_COMPILED_PATH=' . $storagePath . '/framework/views');

define('LARAVEL_START', microtime(true));

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$app->useStoragePath($storagePath);

$app->handleRequest(\Illuminate\Http\Request::capture());
