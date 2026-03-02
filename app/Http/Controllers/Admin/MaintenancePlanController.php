<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MaintenancePlan;
use App\Models\Category;
use App\Models\ChecklistTemplate;
use Illuminate\Support\Facades\Artisan;

class MaintenancePlanController extends Controller
{
    /**
     * Display all maintenance plans
     */
    public function index()
    {
        $plans = MaintenancePlan::with(['category', 'checklistTemplate'])
            ->orderBy('is_active', 'desc')
            ->orderBy('category_id')
            ->paginate(20);
        
        $stats = [
            'total' => MaintenancePlan::count(),
            'active' => MaintenancePlan::where('is_active', true)->count(),
            'daily' => MaintenancePlan::where('frequency', 'daily')->where('is_active', true)->count(),
            'weekly' => MaintenancePlan::where('frequency', 'weekly')->where('is_active', true)->count(),
            'monthly' => MaintenancePlan::where('frequency', 'monthly')->where('is_active', true)->count(),
            'yearly' => MaintenancePlan::where('frequency', 'yearly')->where('is_active', true)->count(),
        ];
        
        return view('admin.plans.index', compact('plans', 'stats'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $templates = ChecklistTemplate::orderBy('name')->get();
        
        return view('admin.plans.create', compact('categories', 'templates'));
    }

    /**
     * Store new plan
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'checklist_template_id' => 'required|exists:checklist_templates,id',
            'frequency' => 'required|in:daily,weekly,monthly,yearly',
            'start_date' => 'required|date',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
            'asset_ids' => 'nullable|array',
            'asset_ids.*' => 'exists:assets,id',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $plan = MaintenancePlan::create($validated);

        if ($request->has('asset_ids')) {
            $plan->assets()->sync($request->asset_ids);
        }

        return redirect()->route('admin.plans.index')
            ->with('success', 'Aturan maintenance berhasil dibuat!');
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $plan = MaintenancePlan::with(['category', 'checklistTemplate', 'assets'])->findOrFail($id);
        $categories = Category::orderBy('name')->get();
        $templates = ChecklistTemplate::orderBy('name')->get();
        
        return view('admin.plans.edit', compact('plan', 'categories', 'templates'));
    }

    /**
     * Update plan
     */
    public function update(Request $request, $id)
    {
        $plan = MaintenancePlan::findOrFail($id);
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'checklist_template_id' => 'required|exists:checklist_templates,id',
            'frequency' => 'required|in:daily,weekly,monthly,yearly',
            'start_date' => 'required|date',
            'is_active' => 'boolean',
            'notes' => 'nullable|string',
            'asset_ids' => 'nullable|array',
            'asset_ids.*' => 'exists:assets,id',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $plan->update($validated);

        if ($request->has('asset_ids')) {
            $plan->assets()->sync($request->asset_ids);
        } else {
            $plan->assets()->detach();
        }

        return redirect()->route('admin.plans.index')
            ->with('success', 'Aturan maintenance berhasil diupdate!');
    }

    /**
     * Delete plan
     */
    public function destroy($id)
    {
        $plan = MaintenancePlan::findOrFail($id);
        $plan->delete();

        return redirect()->route('admin.plans.index')
            ->with('success', 'Aturan maintenance berhasil dihapus!');
    }

    /**
     * Toggle active status
     */
    public function toggleActive($id)
    {
        $plan = MaintenancePlan::findOrFail($id);
        $plan->update(['is_active' => !$plan->is_active]);

        return response()->json([
            'success' => true,
            'is_active' => $plan->is_active,
            'message' => $plan->is_active ? 'Aturan diaktifkan' : 'Aturan dinonaktifkan'
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
            
            return redirect()->route('admin.plans.index')
                ->with('success', 'Task generation berhasil! ' . $output);
        } catch (\Exception $e) {
            return redirect()->route('admin.plans.index')
                ->with('error', 'Gagal generate tasks: ' . $e->getMessage());
        }
    }
}
