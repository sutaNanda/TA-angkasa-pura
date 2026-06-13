<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TechnicianGroup;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TechnicianGroupController extends Controller
{
    /**
     * Daftar semua grup beserta jumlah anggota.
     */
    public function index(): View
    {
        $groups = TechnicianGroup::withCount('members')
            ->orderBy('name')
            ->paginate(15);

        return view('admin.groups.index', compact('groups'));
    }

    /**
     * Form tambah grup baru.
     */
    public function create(): View
    {
        // Daftar semua admin & teknisi beserta grup mereka saat ini
        $availableTechnicians = User::whereIn('role', ['admin', 'teknisi'])
            ->with('group')
            ->orderBy('name')
            ->get();

        return view('admin.groups.create', compact('availableTechnicians'));
    }

    /**
     * Simpan grup baru beserta anggota awalnya.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:255|unique:technician_groups,name',
            'description' => 'nullable|string|max:500',
            'color'       => 'required|in:blue,green,red,yellow,purple,orange,gray',
            // member_ids: array ID teknisi yang akan dimasukkan ke grup ini
            'member_ids'  => 'nullable|array',
            'member_ids.*' => 'exists:users,id',
        ]);

        $group = TechnicianGroup::create([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'color'       => $validated['color'],
        ]);

        // Assign teknisi ke grup ini dengan update FK di tabel users
        // (One-to-Many: cukup update technician_group_id di baris users)
        if (!empty($validated['member_ids'])) {
            User::whereIn('id', $validated['member_ids'])
                ->whereIn('role', ['admin', 'teknisi'])
                ->update(['technician_group_id' => $group->id]);
        }

        $count = empty($validated['member_ids']) ? 0 : count($validated['member_ids']);
        
        return redirect()
            ->route('admin.groups.index')
            ->with('success', "Grup {$group->name} berhasil dibuat ({$count} anggota).");
    }

    /**
     * Form edit grup.
     */
    public function edit(TechnicianGroup $group): View
    {
        // Muat anggota saat ini
        $group->load('members');

        // Daftar semua admin & teknisi beserta grup mereka saat ini
        $availableTechnicians = User::whereIn('role', ['admin', 'teknisi'])
            ->with('group')
            ->orderBy('name')
            ->get();

        return view('admin.groups.edit', compact('group', 'availableTechnicians'));
    }

    /**
     * Update data grup beserta keanggotaannya.
     *
     * Strategi update anggota (One-to-Many yang disimulaikan seperti sync):
     * 1. Lepas semua anggota lama dari grup ini (set NULL).
     * 2. Set grup baru ke semua ID yang dikirim.
     */
    public function update(Request $request, TechnicianGroup $group): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => "required|string|max:255|unique:technician_groups,name,{$group->id}",
            'description' => 'nullable|string|max:500',
            'color'       => 'required|in:blue,green,red,yellow,purple,orange,gray',
            'member_ids'  => 'nullable|array',
            'member_ids.*' => 'exists:users,id',
        ]);

        $group->update([
            'name'        => $validated['name'],
            'description' => $validated['description'] ?? null,
            'color'       => $validated['color'],
        ]);

        // Sync keanggotaan: lepas semua dulu, lalu assign yang baru
        User::where('technician_group_id', $group->id)
            ->update(['technician_group_id' => null]);

        if (!empty($validated['member_ids'])) {
            User::whereIn('id', $validated['member_ids'])
                ->whereIn('role', ['admin', 'teknisi']) // Guard: admin & teknisi bisa digrup
                ->update(['technician_group_id' => $group->id]);
        }

        $count = empty($validated['member_ids']) ? 0 : count($validated['member_ids']);

        return redirect()
            ->route('admin.groups.index')
            ->with('success', "Grup {$group->name} berhasil diperbarui ({$count} anggota).");
    }

    /**
     * Hapus grup (anggota akan ter-NULL-kan secara otomatis via nullOnDelete FK).
     */
    public function destroy(TechnicianGroup $group): RedirectResponse
    {
        $groupName = $group->name;
        $group->delete();

        return redirect()
            ->route('admin.groups.index')
            ->with('success', "Grup \"{$groupName}\" berhasil dihapus. Anggota telah dilepas dari grup.");
    }
}
