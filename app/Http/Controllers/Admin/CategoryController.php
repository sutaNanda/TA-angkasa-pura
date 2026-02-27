<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Http\Requests\Admin\StoreCategoryRequest;
use App\Http\Requests\Admin\UpdateCategoryRequest;

class CategoryController extends Controller
{
    /**
     * Menampilkan halaman utama + Data Awal
     */
    public function index(Request $request)
    {
        // PENTING: Bagian ini tetap JSON karena dipakai oleh AJAX
        // saat user mengklik kategori di panel kiri.
        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'success',
                'data' => Category::withCount('assets')->get()
            ]);
        }

        // Tampilan awal (Blade)
        $categories = Category::withCount('assets')->get();
        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Simpan Kategori Baru
     */
    public function store(StoreCategoryRequest $request)
    {
        // Slug di-generate otomatis dari name — tidak diinput user
        $category = Category::create([
            'name'        => $request->name,
            'slug'        => Str::slug($request->name),
            'icon'        => $request->input('icon') ?: 'fa-box', // default jika kosong
            'description' => $request->description,
        ]);

        return redirect()->back()->with('success', 'Kategori berhasil ditambahkan.');
    }

    /**
     * Update Kategori
     */
    public function update(UpdateCategoryRequest $request, $id)
    {
        $category = Category::findOrFail($id);

        // Slug di-regenerate dari name terbaru — tidak diinput user
        $category->update([
            'name'        => $request->name,
            'slug'        => Str::slug($request->name),
            'icon'        => $request->input('icon') ?: 'fa-box', // default jika dikosongkan
            'description' => $request->description,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Kategori berhasil diperbarui.',
                'data'    => $category->fresh()->loadCount('assets'),
            ]);
        }

        return redirect()->back()->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Hapus Kategori
     */
    public function destroy($id)
    {
        $category = Category::findOrFail($id);

        // Cek apakah kategori masih punya aset
        if ($category->assets()->count() > 0) {
            // Return JSON untuk AJAX request
            if (request()->wantsJson() || request()->ajax()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Gagal! Kategori ini masih memiliki aset. Hapus asetnya terlebih dahulu.'
                ], 400);
            }
            return redirect()->back()->with('error', 'Gagal! Kategori ini masih memiliki aset. Hapus asetnya terlebih dahulu.');
        }

        $category->delete();

        // Return JSON untuk AJAX request
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'status' => 'success',
                'message' => 'Kategori berhasil dihapus'
            ]);
        }

        return redirect()->back()->with('success', 'Kategori berhasil dihapus');
    }
}
