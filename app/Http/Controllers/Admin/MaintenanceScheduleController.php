<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MaintenanceSchedule;
use App\Models\Asset;
use App\Models\ChecklistTemplate;
use Illuminate\Support\Facades\Artisan;

class MaintenanceScheduleController extends Controller
{
    /**
     * Display all maintenance schedules
     */
    public function index()
    {
        $schedules = MaintenanceSchedule::with(['asset.category', 'checklistTemplate'])
            ->orderBy('is_active', 'desc')
            ->orderBy('asset_id')
            ->paginate(20);
        
        $stats = [
            'total' => MaintenanceSchedule::count(),
            'active' => MaintenanceSchedule::where('is_active', true)->count(),
            'daily' => MaintenanceSchedule::where('frequency', 'daily')->where('is_active', true)->count(),
            'weekly' => MaintenanceSchedule::where('frequency', 'weekly')->where('is_active', true)->count(),
            'monthly' => MaintenanceSchedule::where('frequency', 'monthly')->where('is_active', true)->count(),
        ];
        
        return view('admin.schedules.index', compact('schedules', 'stats'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $assets = Asset::with('category')->orderBy('name')->get();
        $templates = ChecklistTemplate::orderBy('name')->get();
        
        return view('admin.schedules.create', compact('assets', 'templates'));
    }

    /**
     * Store new schedule
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'checklist_template_id' => 'required|exists:checklist_templates,id',
            'frequency' => 'required|in:daily,weekly,monthly',
            'day_of_week' => 'nullable|integer|min:1|max:7',
            'day_of_month' => 'nullable|integer|min:1|max:31',
            'preferred_time' => 'nullable|date_format:H:i',
            'is_active' => 'boolean',
        ]);

        MaintenanceSchedule::create($validated);

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Jadwal maintenance berhasil dibuat!');
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $schedule = MaintenanceSchedule::with(['asset', 'checklistTemplate'])->findOrFail($id);
        $assets = Asset::with('category')->orderBy('name')->get();
        $templates = ChecklistTemplate::orderBy('name')->get();
        
        return view('admin.schedules.edit', compact('schedule', 'assets', 'templates'));
    }

    /**
     * Update schedule
     */
    public function update(Request $request, $id)
    {
        $schedule = MaintenanceSchedule::findOrFail($id);
        
        $validated = $request->validate([
            'asset_id' => 'required|exists:assets,id',
            'checklist_template_id' => 'required|exists:checklist_templates,id',
            'frequency' => 'required|in:daily,weekly,monthly',
            'day_of_week' => 'nullable|integer|min:1|max:7',
            'day_of_month' => 'nullable|integer|min:1|max:31',
            'preferred_time' => 'nullable|date_format:H:i',
            'is_active' => 'boolean',
        ]);

        $schedule->update($validated);

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Jadwal maintenance berhasil diupdate!');
    }

    /**
     * Delete schedule
     */
    public function destroy($id)
    {
        $schedule = MaintenanceSchedule::findOrFail($id);
        $schedule->delete();

        return redirect()->route('admin.schedules.index')
            ->with('success', 'Jadwal maintenance berhasil dihapus!');
    }

    /**
     * Toggle active status
     */
    public function toggleActive($id)
    {
        $schedule = MaintenanceSchedule::findOrFail($id);
        $schedule->update(['is_active' => !$schedule->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $schedule->is_active,
            'message' => $schedule->is_active ? 'Jadwal diaktifkan' : 'Jadwal dinonaktifkan'
        ]);
    }

    /**
     * Manual trigger: Generate tasks now
     */
    public function generateNow()
    {
        try {
            Artisan::call('maintenance:generate-daily');
            $output = Artisan::output();
            
            return redirect()->route('admin.schedules.index')
                ->with('success', 'Task generation berhasil! ' . $output);
        } catch (\Exception $e) {
            return redirect()->route('admin.schedules.index')
                ->with('error', 'Gagal generate tasks: ' . $e->getMessage());
        }
    }
}
