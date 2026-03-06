<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$locationId = 7;
$location = \App\Models\Location::with('childrenRecursive')->find($locationId);
if(!$location) {
    echo "Location not found";
    exit;
}

function getAllLocationIds($loc) {
    $ids = [$loc->id];
    foreach ($loc->childrenRecursive as $child) {
        $ids = array_merge($ids, getAllLocationIds($child));
    }
    return $ids;
}

$ids = getAllLocationIds($location);
echo "Location IDs:\n";
print_r($ids);

$assets = \App\Models\Asset::whereIn('location_id', $ids)->get();
echo "\nAssets Found:\n";
print_r($assets->pluck('name')->toArray());
?>
