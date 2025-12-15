<?php

namespace App\Http\Controllers;

use App\Models\CloneLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_logs' => CloneLog::count(),
            'total_sessions' => CloneLog::distinct('session_id')->count('session_id'),
            'total_domains' => CloneLog::distinct('domain')->count('domain'),
            'total_ips' => CloneLog::distinct('client_ip')->count('client_ip'),
        ];

        $domains = CloneLog::select('domain', DB::raw('count(*) as count'))
            ->groupBy('domain')
            ->orderByDesc('count')
            ->get();

        $recentLogs = CloneLog::with([])
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        $dailyActivity = CloneLog::select(
            DB::raw('DATE(created_at) as date'),
            DB::raw('count(*) as count')
        )
            ->where('created_at', '>=', now()->subDays(30))
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return view('dashboard.index', compact('stats', 'domains', 'recentLogs', 'dailyActivity'));
    }

    public function show($id)
    {
        $log = CloneLog::findOrFail($id);

        return view('dashboard.show', compact('log'));
    }

    public function domain($domain)
    {
        $logs = CloneLog::where('domain', $domain)
            ->orderByDesc('created_at')
            ->paginate(50);

        $stats = [
            'total_logs' => $logs->total(),
            'unique_sessions' => CloneLog::where('domain', $domain)->distinct('session_id')->count('session_id'),
            'unique_ips' => CloneLog::where('domain', $domain)->distinct('client_ip')->count('client_ip'),
        ];

        return view('dashboard.domain', compact('logs', 'domain', 'stats'));
    }

    public function api()
    {
        $stats = [
            'total_logs' => CloneLog::count(),
            'total_sessions' => CloneLog::distinct('session_id')->count('session_id'),
            'total_domains' => CloneLog::distinct('domain')->count('domain'),
            'total_ips' => CloneLog::distinct('client_ip')->count('client_ip'),
        ];

        $domains = CloneLog::select('domain', DB::raw('count(*) as count'))
            ->groupBy('domain')
            ->orderByDesc('count')
            ->get();

        return response()->json([
            'stats' => $stats,
            'domains' => $domains,
        ]);
    }

    public function export()
    {
        $logs = CloneLog::orderByDesc('created_at')->get();

        $filename = 'clone-logs-' . now()->format('Y-m-d-His') . '.json';

        return response()->json($logs)
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }
}
