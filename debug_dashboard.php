<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Maintenance;
use App\Models\User;
use Carbon\Carbon;

$now = Carbon::now();
$currentTime = $now->format('H:i:s');
echo "=== DEBUG DASHBOARD ===" . PHP_EOL;
echo "Waktu sekarang: " . $now->format('Y-m-d H:i:s') . PHP_EOL;
echo "Current time filter: " . $currentTime . PHP_EOL . PHP_EOL;

// List semua user
echo "=== SEMUA USER ===" . PHP_EOL;
$allUsers = User::all();
foreach ($allUsers as $u) {
    echo "  ID:{$u->id} | {$u->name} | role:{$u->role} | shift_id:" . ($u->shift_id ?? 'NULL') . PHP_EOL;
}
echo PHP_EOL;

// Cari user teknisi (coba beberapa kemungkinan role)
$user = User::where('role', 'technician')->first()
    ?? User::where('role', 'teknisi')->first()
    ?? User::where('name', 'like', '%Suta%')->first();

if (!$user) {
    echo "ERROR: Tidak ada user teknisi ditemukan!" . PHP_EOL;
    exit;
}

echo "=== Testing dengan: {$user->name} (shift_id: " . ($user->shift_id ?? 'NULL') . ") ===" . PHP_EOL . PHP_EOL;

// Step 1: Semua maintenance hari ini
$allToday = Maintenance::whereDate('scheduled_date', $now->toDateString())
    ->where('type', 'preventive')
    ->whereIn('status', ['pending', 'in_progress'])
    ->get();
echo "1. SEMUA tiket preventive hari ini: " . $allToday->count() . PHP_EOL;
foreach ($allToday as $m) {
    echo "   - ID:{$m->id} | plan:{$m->maintenance_plan_id} | time:" . ($m->scheduled_time ?? 'NULL') . " | status:{$m->status} | loc:" . ($m->location_id ?? 'NULL') . PHP_EOL;
}

// Step 2: Setelah filter shift
echo PHP_EOL . "2. Setelah FILTER SHIFT (shift_id=" . ($user->shift_id ?? 'NULL') . "):" . PHP_EOL;
$query2 = Maintenance::with(['maintenancePlan'])
    ->whereDate('scheduled_date', $now->toDateString())
    ->where('type', 'preventive')
    ->whereIn('status', ['pending', 'in_progress']);

if ($user->shift_id) {
    $query2->where(function($q) use ($user) {
        $q->whereHas('maintenancePlan', function($sub) use ($user) {
            $sub->where('shift_id', $user->shift_id);
        })->orWhereHas('maintenancePlan', function($sub) {
            $sub->whereNull('shift_id');
        })->orWhereNull('maintenance_plan_id');
    });
}
$afterShift = $query2->get();
echo "   Jumlah: " . $afterShift->count() . PHP_EOL;
foreach ($afterShift as $m) {
    $planShift = $m->maintenancePlan ? ($m->maintenancePlan->shift_id ?? 'NULL') : 'NO_PLAN';
    echo "   - ID:{$m->id} | plan_shift:{$planShift} | time:" . ($m->scheduled_time ?? 'NULL') . " | status:{$m->status}" . PHP_EOL;
}

// Step 3: Setelah filter waktu (hidden mode)
echo PHP_EOL . "3. Setelah HIDDEN MODE (time <= {$currentTime}):" . PHP_EOL;
$query3 = Maintenance::with(['maintenancePlan'])
    ->whereDate('scheduled_date', $now->toDateString())
    ->where('type', 'preventive')
    ->whereIn('status', ['pending', 'in_progress']);

if ($user->shift_id) {
    $query3->where(function($q) use ($user) {
        $q->whereHas('maintenancePlan', function($sub) use ($user) {
            $sub->where('shift_id', $user->shift_id);
        })->orWhereHas('maintenancePlan', function($sub) {
            $sub->whereNull('shift_id');
        })->orWhereNull('maintenance_plan_id');
    });
}
$query3->where(function($q) use ($currentTime) {
    $q->whereNull('scheduled_time')
      ->orWhere('scheduled_time', '<=', $currentTime)
      ->orWhere('status', 'in_progress');
});
$afterHidden = $query3->get();
echo "   Jumlah: " . $afterHidden->count() . PHP_EOL;
foreach ($afterHidden as $m) {
    echo "   - ID:{$m->id} | time:" . ($m->scheduled_time ?? 'NULL') . " | status:{$m->status}" . PHP_EOL;
}
