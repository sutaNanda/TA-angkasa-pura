<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Location;

$count = Location::count();
$filled = Location::whereNotNull('path')->count();
$types = Location::select('type', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
    ->groupBy('type')
    ->pluck('total', 'type');

echo "Total Locations: " . $count . "\n";
echo "Filled Path: " . $filled . "\n";
echo "Types Distribution: " . json_encode($types) . "\n";
