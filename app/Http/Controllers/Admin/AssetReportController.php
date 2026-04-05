<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Category;
use App\Models\Location;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class AssetReportController extends Controller
{
    /**
     * Menampilkan halaman filter & tabel Laporan Inventaris Aset.
     */
    public function index(Request $request)
    {
        $query = $this->buildQuery($request);
        
        $assets = $query->paginate(15)->withQueryString();
        
        // Data untuk dropdown filter
        $categories = Category::orderBy('name')->get();
        // Hanya ambil lokasi yang bukan root/parent jika struktur tree, atau ambil semua
        $locations = Location::orderBy('name')->get(); 

        return view('admin.reports.assets.index', compact('assets', 'categories', 'locations'));
    }

    /**
     * Cetak Laporan ke format PDF.
     */
    public function printPdf(Request $request)
    {
        $query = $this->buildQuery($request);
        $assets = $query->get();

        $pdf = Pdf::loadView('admin.pdf.asset_location_report', compact('assets', 'request'))
                  ->setPaper('A4', 'landscape');

        return $pdf->stream('Laporan_Inventaris_Aset_' . now()->format('Ymd_His') . '.pdf');
    }

    /**
     * Helper Query & Filter (Eager Loading)
     */
    private function buildQuery(Request $request)
    {
        $query = Asset::with(['category', 'location']);

        // Filter Kategori
        if ($request->filled('category_id') && $request->category_id !== 'all') {
            $query->where('category_id', $request->category_id);
        }

        // Filter Lokasi
        if ($request->filled('location_id') && $request->location_id !== 'all') {
            $query->where('location_id', $request->location_id);
        }

        // Filter Status
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Sorting default: Lokasi lalu Nama Aset
        $query->orderBy('location_id')->orderBy('name');

        return $query;
    }
}
