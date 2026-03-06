<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'force.reset' => \App\Http\Middleware\ForcePasswordReset::class,
            'read_only_manager' => \App\Http\Middleware\ReadOnlyManager::class,
        ]);
        
        $middleware->web(append: [
            \App\Http\Middleware\ForcePasswordReset::class,
            \App\Http\Middleware\NgrokBypass::class,
        ]);

        $middleware->redirectUsersTo(function () {
            $user = Auth::user();
            if ($user->role === 'admin' || $user->role === 'manajer') {
                return route('admin.dashboard');
            } elseif ($user->role === 'teknisi') {
                return route('technician.dashboard');
            } elseif ($user->role === 'user') {
                return route('user.tickets.index');
            }
            return '/';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Http\Exceptions\PostTooLargeException $e, $request) {
            return redirect()->back()->with('error', 'Ukuran file terlalu besar! Gagal mengupload.');
        });
    })->create();
