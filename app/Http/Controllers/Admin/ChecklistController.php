<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ChecklistTemplate;
use App\Models\ChecklistItem;
use App\Models\Category;
use App\Http\Requests\Admin\StoreChecklistItemRequest;
use App\Http\Requests\Admin\UpdateChecklistItemRequest;
use Illuminate\Support\Facades\DB;

class ChecklistController extends Controller
{
    /**
     * Menampilkan Halaman Utama
     * FIX ERROR: Di sini kita kirim variabel $templates dan $categories
     */
    public function index()
    {
        // Ambil data template, urutkan terbaru, dan load relasinya (supaya tidak berat/N+1)
        $templates = ChecklistTemplate::with(['category', 'items'])
                        ->latest()
                        ->paginate(10);

        // Ambil kategori untuk dropdown di modal
        $categories = Category::orderBy('name', 'asc')->get();

        // Kirim data ke View menggunakan compact
        return view('admin.checklists.index', compact('templates', 'categories'));
    }

    /**
     * API: Ambil Detail Template (Dipakai saat tombol Edit diklik)
     */
    public function show($id)
    {
        $template = ChecklistTemplate::with(['items', 'category'])->findOrFail($id);
        return response()->json(['status' => 'success', 'data' => $template]);
    }

    /**
     * Simpan Template Baru
     */
    public function store(StoreChecklistItemRequest $request)
    {
        DB::beginTransaction();
        try {
            // 1. Simpan Header
            $template = ChecklistTemplate::create([
                'name' => $request->name,
                'frequency' => 'daily', // Hardcoded as frequency is now handled by maintenance plans
                'category_id' => $request->category_id, // Bisa null
                'description' => $request->description,
            ]);

            // 2. Simpan Item Pertanyaan
            // Kita loop manual karena ada logic input types & units
            if ($request->has('questions')) {
                foreach ($request->questions as $index => $q) {
                    if (empty($q)) continue;

                    ChecklistItem::create([
                        'checklist_template_id' => $template->id,
                        'question' => $q,
                        'type' => $request->types[$index] ?? 'pass_fail',
                        'unit' => $request->units[$index] ?? null, // Simpan null jika tidak ada unit
                        'order' => $index + 1
                    ]);
                }
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Template SOP berhasil dibuat']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update Template
     */
    public function update(UpdateChecklistItemRequest $request, $id)
    {
        $template = ChecklistTemplate::findOrFail($id);

        DB::beginTransaction();
        try {
            // 1. Update Header
            $template->update([
                'name' => $request->name,
                'frequency' => 'daily', // Hardcoded as frequency is now handled by maintenance plans
                'category_id' => $request->category_id,
                'description' => $request->description,
            ]);

            // 2. Update Items (Strategy: Hapus lama, pasang baru)
            $template->items()->delete();

            if ($request->has('questions')) {
                foreach ($request->questions as $index => $q) {
                    if (empty($q)) continue;

                    ChecklistItem::create([
                        'checklist_template_id' => $template->id,
                        'question' => $q,
                        'type' => $request->types[$index] ?? 'pass_fail',
                        'unit' => $request->units[$index] ?? null,
                        'order' => $index + 1
                    ]);
                }
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Template berhasil diperbarui']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Gagal: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Hapus Template
     */
    public function destroy($id)
    {
        $template = ChecklistTemplate::findOrFail($id);
        $template->delete(); // Items akan terhapus otomatis (cascade di database) atau soft delete
        return response()->json(['status' => 'success', 'message' => 'Template dihapus']);
    }
}
