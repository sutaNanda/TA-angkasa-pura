<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;

// Models yang akan diaudit
use App\Models\Asset;
use App\Models\Location;
use App\Models\Category;
use App\Models\User;
use App\Models\WorkOrder;
use App\Models\MaintenancePlan;
use App\Models\Maintenance;
use App\Models\PatrolLog;
use App\Models\ChecklistTemplate;

// Observer
use App\Observers\AuditableObserver;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Trik khusus untuk membersihkan cache otomatis ketika di-hosting
        \Illuminate\Support\Facades\Artisan::call('route:clear');

        // 1. Paksa HTTPS jika di Ngrok (Agar CSS tidak error mixed content)
        if ($this->app->environment('production') || str_contains(request()->getHost(), 'ngrok-free.app')) {
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

        // 3. Daftarkan Audit Observer untuk semua model penting
        //    Otomatis mencatat setiap CREATE / UPDATE / DELETE ke tabel audit_logs
        $modelsToAudit = [
            Asset::class,
            Location::class,
            Category::class,
            User::class,
            WorkOrder::class,
            MaintenancePlan::class,
            Maintenance::class,
            PatrolLog::class,
            ChecklistTemplate::class,
        ];

        foreach ($modelsToAudit as $model) {
            $model::observe(AuditableObserver::class);
        }
    }
}