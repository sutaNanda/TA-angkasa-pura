<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Location;

class LocationCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * Generate QR codes for existing locations
     */
    public function run(): void
    {
        $locations = Location::whereNull('code')->get();
        
        foreach ($locations as $index => $location) {
            $location->update([
                'code' => 'LOC-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT)
            ]);
        }
        
        $this->command->info('Generated codes for ' . $locations->count() . ' locations.');
    }
}
