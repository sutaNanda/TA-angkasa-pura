<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\WorkOrder;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Fix for existing data:
        // Find all WorkOrders with status 'open' and source 'patrol' (or created today/yesterday)
        // Set technician_id to NULL to ensure they appear in the Pool.
        
        $count = WorkOrder::where('status', 'open')
            ->where('source', 'patrol')
            ->whereNotNull('technician_id')
            ->update(['technician_id' => null]);
            
        \Log::info("Fixed $count patrol tasks to be available in pool.");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed for data fix
    }
};
