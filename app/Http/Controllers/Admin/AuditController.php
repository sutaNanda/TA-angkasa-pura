<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    public function index(Request $request)
    {
        $query = AuditLog::with('user')->latest();

        // Filter by date
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action type
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Search keyword
        if ($request->filled('search')) {
            $keyword = $request->search;
            $query->where(function ($q) use ($keyword) {
                $q->where('description', 'like', "%$keyword%")
                  ->orWhere('module', 'like', "%$keyword%");
            });
        }

        $logs   = $query->paginate(20)->withQueryString();
        $users  = User::orderBy('name')->get();

        // Stats for summary cards
        $stats = [
            'today'   => AuditLog::whereDate('created_at', today())->count(),
            'logins'  => AuditLog::whereDate('created_at', today())->where('action', 'login')->count(),
            'creates' => AuditLog::whereDate('created_at', today())->where('action', 'create')->count(),
            'deletes' => AuditLog::whereDate('created_at', today())->where('action', 'delete')->count(),
        ];

        return view('admin.audit.index', compact('logs', 'users', 'stats'));
    }
}
