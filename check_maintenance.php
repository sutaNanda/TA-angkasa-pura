<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Maintenance;
use App\Models\MaintenancePlan;

echo '=== MAINTENANCE PLANS AKTIF ===' . PHP_EOL;
MaintenancePlan::where('is_active', true)->with('shift')->get()->each(function($p) {
    $shiftLabel = $p->shift ? $p->shift->name . ' (' . $p->shift->start_time . ')' : 'SEMUA SHIFT';
    echo '- [' . $p->id . '] ' . $p->name . ' | Shift: ' . $shiftLabel . ' | start_time: ' . ($p->start_time ?? 'null') . PHP_EOL;
});

echo PHP_EOL . '=== TIKET MAINTENANCE HARI INI ===' . PHP_EOL;
Maintenance::whereDate('scheduled_date', today())->get()->each(function($m) {
    echo '- [ID:' . $m->id . '] Plan:' . $m->maintenance_plan_id . ' | scheduled_time: ' . ($m->scheduled_time ?? 'null') . ' | status: ' . $m->status . PHP_EOL;
});

echo PHP_EOL . 'Total tiket hari ini: ' . Maintenance::whereDate('scheduled_date', today())->count() . PHP_EOL;
