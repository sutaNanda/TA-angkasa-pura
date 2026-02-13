<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $count = \App\Models\MaintenancePlan::count();
    $first = \App\Models\MaintenancePlan::first();
    file_put_contents('test_output.txt', "COUNT: " . $count . "\n");
    if ($first) {
        file_put_contents('test_output.txt', "FIRST NAME: " . $first->name . "\n", FILE_APPEND);
    }
} catch (\Exception $e) {
    file_put_contents('test_output.txt', "ERROR: " . $e->getMessage() . "\n");
}
