<?php
$logFile = 'storage/logs/laravel.log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    foreach(array_slice($lines, -30) as $line) {
        echo $line;
    }
} else {
    echo "Log file not found.";
}
