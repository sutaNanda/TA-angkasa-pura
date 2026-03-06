<?php
include 'vendor/autoload.php';
$app = include_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Asset;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

$categories = Category::all(['id', 'name'])->toArray();
$counts = Asset::select('category_id', DB::raw('count(*) as total'))
    ->groupBy('category_id')
    ->get()
    ->keyBy('category_id')
    ->toArray();

$output = "Categories:\n";
foreach ($categories as $cat) {
    $count = isset($counts[$cat['id']]) ? $counts[$cat['id']]['total'] : 0;
    $output .= "ID: {$cat['id']}, Name: {$cat['name']}, Assets: {$count}\n";
}

file_put_contents('debug_counts.txt', $output);
echo "Done\n";
