<?php
$output = shell_exec('composer require barryvdh/laravel-dompdf --no-interaction 2>&1');
echo "<pre>$output</pre>";
?>
