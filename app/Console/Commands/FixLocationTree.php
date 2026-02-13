<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Location;
use Illuminate\Support\Facades\DB;

class FixLocationTree extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'locations:fix-tree';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix hierarchy path, level, and type for all locations';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting Location Tree Fix...');

        DB::transaction(function () {
            // Ambil Root (yang tidak punya parent)
            $roots = Location::whereNull('parent_id')->get();

            foreach ($roots as $root) {
                $this->processNode($root, null, 0);
            }
        });

        $this->info('Location Tree Fixed Successfully!');
    }

    private function processNode($node, $parentPath, $level)
    {
        // 1. Tentukan Path
        // Jika Root: path = id
        // Jika Child: path = parent_path/id
        $currentPath = $parentPath ? $parentPath . '/' . $node->id : (string)$node->id;

        // 2. Determinis Type (Heuristic)
        $type = 'area'; // Default fallback

        if ($level === 0) {
            $type = 'building';
        } elseif (stripos($node->name, 'Lantai') !== false || stripos($node->name, 'Floor') !== false) {
            $type = 'floor';
        } elseif ($node->children()->count() === 0) {
            // Leaf node usually a room
            $type = 'room';
        }

        // 3. Update Node (Quietly agar tidak mentrigger event lagi)
        $node->path = $currentPath;
        $node->level = $level;
        $node->type = $type;
        $node->saveQuietly();

        $this->line("Processed: {$node->name} ({$type}) -> Path: {$currentPath}");

        // 4. Rekursif ke Children
        foreach ($node->children as $child) {
            $this->processNode($child, $currentPath, $level + 1);
        }
    }
}
