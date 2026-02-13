<?php

use Illuminate\Support\Facades\DB;

// Quick script to check and populate location codes

echo "=== Checking Location Codes ===\n\n";

// Check if code column exists
try {
    $columns = DB::select("SHOW COLUMNS FROM locations LIKE 'code'");
    
    if (empty($columns)) {
        echo "❌ Column 'code' does NOT exist!\n";
        echo "Run: php artisan migrate\n\n";
        exit;
    }
    
    echo "✅ Column 'code' exists\n\n";
    
} catch (\Exception $e) {
    echo "Error checking column: " . $e->getMessage() . "\n";
    exit;
}

// Check current location codes
$locations = DB::table('locations')->select('id', 'name', 'code')->get();

echo "Total locations: " . $locations->count() . "\n";
echo "Locations with code: " . $locations->whereNotNull('code')->count() . "\n";
echo "Locations without code: " . $locations->whereNull('code')->count() . "\n\n";

// Show first 5 locations
echo "First 5 locations:\n";
foreach ($locations->take(5) as $loc) {
    echo "  ID: {$loc->id} | Name: {$loc->name} | Code: " . ($loc->code ?? 'NULL') . "\n";
}
echo "\n";

// If codes are missing, populate them
$missingCodes = $locations->whereNull('code');

if ($missingCodes->count() > 0) {
    echo "🔧 Populating missing codes...\n";
    
    $index = 1;
    foreach ($missingCodes as $loc) {
        $code = 'LOC-' . str_pad($index, 3, '0', STR_PAD_LEFT);
        DB::table('locations')
            ->where('id', $loc->id)
            ->update(['code' => $code]);
        
        echo "  Updated ID {$loc->id} ({$loc->name}) -> {$code}\n";
        $index++;
    }
    
    echo "\n✅ All location codes populated!\n";
} else {
    echo "✅ All locations already have codes!\n";
}

echo "\n=== Final Check ===\n";
$finalLocations = DB::table('locations')->select('id', 'name', 'code')->get();
foreach ($finalLocations as $loc) {
    echo "  {$loc->code} - {$loc->name}\n";
}
