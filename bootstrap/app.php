<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();

// في بيئة Vercel Serverless يكون نظام الملفات للقراءة فقط، لذا نوجه مسار التخزين إلى /tmp
if (isset($_SERVER['VERCEL']) || env('VERCEL') || (!empty($_ENV['APP_ENV']) && $_ENV['APP_ENV'] === 'production' && !is_writable(storage_path()))) {
    $vercelStorage = '/tmp/storage';
    if (!is_dir($vercelStorage)) {
        @mkdir($vercelStorage . '/framework/views', 0755, true);
        @mkdir($vercelStorage . '/framework/cache/data', 0755, true);
        @mkdir($vercelStorage . '/framework/sessions', 0755, true);
        @mkdir($vercelStorage . '/logs', 0755, true);
    }
    $app->useStoragePath($vercelStorage);
}

return $app;
