<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\CheckPermission;
use App\Http\Middleware\CekStokBuku;
use App\Http\Middleware\CekPeminjamanAktif;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Registrasi middleware alias
        $middleware->alias([
            'check.role' => CheckRole::class,
            'check.permission' => CheckPermission::class,
            'cek.stok' => CekStokBuku::class,        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();