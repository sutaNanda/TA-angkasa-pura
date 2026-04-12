<?php

// Script darurat untuk men-trigger schedule task secara langsung secara lokal!

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

// Bangun console environment
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$cmd = new \App\Console\Commands\GenerateDailyMaintenanceTasks();
$cmd->setLaravel($app);

$input = new \Symfony\Component\Console\Input\ArrayInput([]);
$output = new \Symfony\Component\Console\Output\ConsoleOutput();

echo "Memulai paksa Task Generator...\n=================================\n";
try {
    $cmd->run($input, $output);
    echo "=================================\n✅ Selsesai dijalankan secara manual.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
