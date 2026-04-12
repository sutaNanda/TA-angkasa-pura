<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';

$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// List all command names
$all = \Illuminate\Support\Facades\Artisan::all();

// Check our custom commands
$found = false;
foreach ($all as $name => $cmd) {
    if (str_contains($name, 'maint') || str_contains($name, 'test-cmd') || str_contains($name, 'fix') || str_contains($name, 'purge')) {
        echo "FOUND: $name => " . get_class($cmd) . "\n";
        $found = true;
    }
}
if (!$found) {
    echo "NO CUSTOM COMMANDS FOUND\n";
}

echo "Total commands: " . count($all) . "\n";

// Check if the class can be instantiated
try {
    $cmd = new \App\Console\Commands\GenerateDailyMaintenanceTasks();
    echo "Class instantiated OK, signature: " . $cmd->getName() . "\n";
} catch (\Exception $e) {
    echo "Class ERROR: " . $e->getMessage() . "\n";
}
