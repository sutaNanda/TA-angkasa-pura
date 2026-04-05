<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Shift;
use Illuminate\Support\Facades\DB;

// Handle Update Shifts

// Shift 1: Pagi (08:00 - 20:00)
$s1 = Shift::find(1);
if ($s1) {
    $s1->update(['name' => 'Pagi', 'start_time' => '08:00:00', 'end_time' => '20:00:00', 'color' => 'yellow']);
} else {
    Shift::create(['id' => 1, 'name' => 'Pagi', 'start_time' => '08:00:00', 'end_time' => '20:00:00', 'color' => 'yellow']);
}

// Shift 2: Malam (20:00 - 08:00)
$s2 = Shift::find(2);
if ($s2) {
    $s2->update(['name' => 'Malam', 'start_time' => '20:00:00', 'end_time' => '08:00:00', 'color' => 'purple']);
} else {
    Shift::create(['id' => 2, 'name' => 'Malam', 'start_time' => '20:00:00', 'end_time' => '08:00:00', 'color' => 'purple']);
}

// Safely move foreign keys from shift 3 & 4 (if any exist) to shift 2 (Malam) or null
DB::table('users')->whereIn('shift_id', [3, 4])->update(['shift_id' => null]);
DB::table('maintenance_plans')->whereIn('shift_id', [3, 4])->update(['shift_id' => null]);
DB::table('patrol_logs')->whereIn('shift_id', [3, 4])->update(['shift_id' => null]);

// Delete extra shifts
Shift::whereIn('id', [3, 4])->delete();

// Any other shift > 2? Delete if exist
$extraShifts = Shift::where('id', '>', 2)->get();
foreach ($extraShifts as $es) {
    DB::table('users')->where('shift_id', $es->id)->update(['shift_id' => null]);
    DB::table('maintenance_plans')->where('shift_id', $es->id)->update(['shift_id' => null]);
    DB::table('patrol_logs')->where('shift_id', $es->id)->update(['shift_id' => null]);
    $es->delete();
}

echo "Shifts updated successfully to Pagi and Malam.\n";
