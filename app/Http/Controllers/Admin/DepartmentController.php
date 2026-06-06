<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use Illuminate\Validation\Rule;

class DepartmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Department::query();

        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
        }

        $departments = $query->orderBy('name')->paginate(10);

        return view('admin.departments.index', compact('departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:departments,name',
            'description' => 'nullable|string'
        ]);

        Department::create($request->only(['name', 'description']));

        return back()->with('success', 'Departemen berhasil ditambahkan.');
    }

    public function update(Request $request, $id)
    {
        $department = Department::findOrFail($id);

        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('departments')->ignore($department->id),
            ],
            'description' => 'nullable|string'
        ]);

        $department->update($request->only(['name', 'description']));

        return back()->with('success', 'Data Departemen berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $department = Department::findOrFail($id);

        if ($department->user()->exists()) {
            return back()->with('error', 'Departemen ini tidak dapat dihapus karena sudah memiliki akun User/Pelapor yang terhubung.');
        }

        $department->delete();

        return back()->with('success', 'Departemen berhasil dihapus.');
    }
}
