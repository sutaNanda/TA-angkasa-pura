<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
// 👇 TAMBAHKAN DUA BARIS INI PENTING!
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
// 👆
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. Paksa HTTPS jika di Ngrok (Agar CSS tidak error mixed content)
        if($this->app->environment('production') || str_contains(request()->getHost(), 'ngrok-free.app')) {
            URL::forceScheme('https');
        }

        // 2. View Composer untuk Navigasi Bawah (Agar badge notifikasi muncul)
        View::composer('components.technician-bottom-nav', function ($view) {
            $count = 0;
            if (Auth::check()) {
                $count = WorkOrder::where('technician_id', Auth::id())
                    ->whereIn('status', ['open', 'in_progress', 'pending_part'])
                    ->count();
            }
            $view->with('pendingCount', $count);
        });
    }
}