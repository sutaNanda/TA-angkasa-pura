<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MaintenancePlan;
use App\Models\Category;
use App\Models\ChecklistTemplate;
use App\Models\Location;
use App\Models\TechnicianGroup;
use Illuminate\Support\Facades\Artisan;

class MaintenancePlanController extends Controller
{
    /**
     * Display all maintenance plans
     */
    public function index()
    {
        $plans = MaintenancePlan::withCount('groups')
            ->orderBy('is_active', 'desc')
            ->orderBy('name')
            ->paginate(20);

        $stats = [
            'total'   => MaintenancePlan::count(),
            'active'  => MaintenancePlan::where('is_active', true)->count(),
            'daily'   => MaintenancePlan::where('frequency', 'daily')->where('is_active', true)->count(),
            'weekly'  => MaintenancePlan::where('frequency', 'weekly')->where('is_active', true)->count(),
            'monthly' => MaintenancePlan::where('frequency', 'monthly')->where('is_active', true)->count(),
            'yearly'  => MaintenancePlan::where('frequency', 'yearly')->where('is_active', true)->count(),
        ];

        return view('admin.plans.index', compact('plans', 'stats'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $templates  = ChecklistTemplate::orderBy('name')->get();
        $locations  = Location::whereHas('assets')->with('assets.category')->orderBy('name')->get();
        $groups     = TechnicianGroup::orderBy('name')->get(); // Ganti $shifts dengan $groups

        return view('admin.plans.create', compact('categories', 'templates', 'locations', 'groups'));
    }

    /**
     * Store new plan
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'target_type'           => 'required|in:asset,location',
            'configs'               => 'required|array|min:1',
            'configs.*.category_id' => 'required|exists:categories,id',
            'configs.*.template_id' => 'required|exists:checklist_templates,id',
            'frequency'             => 'required|in:daily,weekly,monthly,yearly',
            'start_date'            => 'required|date',
            'is_active'             => 'boolean',
            'notes'                 => 'nullable|string',
            // Array grup dengan jam mulai per-grup: groups[group_id] = start_time
            'groups'                => 'nullable|array',
            'groups.*.group_id'     => 'required|exists:technician_groups,id',
            'groups.*.start_time'   => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'asset_ids'             => 'nullable|array',
            'asset_ids.*'           => 'exists:assets,id',
            'location_ids'          => 'nullable|array',
            'location_ids.*'        => 'exists:locations,id',
        ]);

        $validated['is_active']       = $request->has('is_active');
        $validated['template_configs'] = $request->input('configs');

        $plan = MaintenancePlan::create($validated);

        // Sync target aset/lokasi
        if ($validated['target_type'] === 'asset') {
            $plan->assets()->sync($request->asset_ids ?? []);
            $plan->locations()->detach();
        } else {
            $plan->locations()->sync($request->location_ids ?? []);
            $plan->assets()->detach();
        }

        // Sync grup dengan membawa nilai pivot start_time
        // Format input: groups[] = [{group_id: X, start_time: 'HH:MM'}]
        $this->syncGroupsWithPivot($plan, $request->input('groups', []));

        return redirect()
            ->route('admin.plans.index')
            ->with('success', 'Aturan maintenance berhasil dibuat!');
    }

    /**
     * Show edit form
     */
    public function edit($id)
    {
        $plan = MaintenancePlan::with(['assets.category', 'assets.location', 'locations', 'groups'])
            ->findOrFail($id);
        $categories = Category::orderBy('name')->get();
        $templates  = ChecklistTemplate::orderBy('name')->get();
        $locations  = Location::whereHas('assets')->with('assets.category')->orderBy('name')->get();
        $groups     = TechnicianGroup::orderBy('name')->get();

        $categoryIds       = collect($plan->template_configs)->pluck('category_id')->unique()->toArray();
        $allCategoryAssets = \App\Models\Asset::whereIn('category_id', $categoryIds)
            ->with(['location', 'category'])
            ->orderBy('name')
            ->get();

        return view('admin.plans.edit', compact('plan', 'categories', 'templates', 'allCategoryAssets', 'locations', 'groups'));
    }

    /**
     * Update plan
     */
    public function update(Request $request, $id)
    {
        $plan = MaintenancePlan::findOrFail($id);

        $validated = $request->validate([
            'name'                  => 'required|string|max:255',
            'target_type'           => 'required|in:asset,location',
            'configs'               => 'required|array|min:1',
            'configs.*.category_id' => 'required|exists:categories,id',
            'configs.*.template_id' => 'required|exists:checklist_templates,id',
            'frequency'             => 'required|in:daily,weekly,monthly,yearly',
            'start_date'            => 'required|date',
            'is_active'             => 'boolean',
            'notes'                 => 'nullable|string',
            'groups'                => 'nullable|array',
            'groups.*.group_id'     => 'required|exists:technician_groups,id',
            'groups.*.start_time'   => ['nullable', 'regex:/^\d{2}:\d{2}(:\d{2})?$/'],
            'asset_ids'             => 'nullable|array',
            'asset_ids.*'           => 'exists:assets,id',
            'location_ids'          => 'nullable|array',
            'location_ids.*'        => 'exists:locations,id',
        ]);

        $validated['is_active']        = $request->has('is_active');
        $validated['template_configs'] = $request->input('configs');

        $plan->update($validated);

        if ($validated['target_type'] === 'asset') {
            $plan->assets()->sync($request->asset_ids ?? []);
            $plan->locations()->detach();
        } else {
            $plan->locations()->sync($request->location_ids ?? []);
            $plan->assets()->detach();
        }

        // Sync grup dengan pivot start_time
        $this->syncGroupsWithPivot($plan, $request->input('groups', []));

        return redirect()
            ->route('admin.plans.index')
            ->with('success', 'Aturan maintenance berhasil diupdate!');
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Sync grup ke MaintenancePlan beserta nilai pivot start_time.
     *
     * Format $groupRows yang diterima dari form:
     * [ ['group_id' => 1, 'start_time' => '08:00'], ['group_id' => 2, 'start_time' => '20:00'], ... ]
     */
    private function syncGroupsWithPivot(MaintenancePlan $plan, array $groupRows): void
    {
        $syncData = [];
        foreach ($groupRows as $row) {
            if (!empty($row['group_id'])) {
                // Normalisasi: potong detik jika browser mengirim HH:MM:SS
                $time = $row['start_time'] ?? null;
                if ($time && strlen($time) > 5) {
                    $time = substr($time, 0, 5); // "08:00:00" → "08:00"
                }
                $syncData[(int) $row['group_id']] = [
                    'start_time' => $time,
                ];
            }
        }

        $plan->groups()->sync($syncData);
    }

    /**
     * Delete plan
     */
    public function destroy(Request $request, $id)
    {
        $plan = MaintenancePlan::findOrFail($id);
        $plan->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Aturan maintenance berhasil dihapus!'
            ]);
        }

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
