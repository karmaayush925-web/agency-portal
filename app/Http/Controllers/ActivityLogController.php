<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    /**
     * Display paginated audit logs for Admins.
     */
    public function index(): View
    {
        $logs = ActivityLog::with('user')
            ->latest()
            ->paginate(15);

        return view('audit-logs.index', compact('logs'));
    }
}