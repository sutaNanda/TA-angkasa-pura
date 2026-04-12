<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$cmd = new \App\Console\Commands\GenerateDailyMaintenanceTasks();
$cmd->setLaravel($app);

$input = new \Symfony\Component\Console\Input\ArrayInput([]);
$output = new \Symfony\Component\Console\Output\ConsoleOutput();

try {
    $cmd->run($input, $output);
    echo "Done.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
