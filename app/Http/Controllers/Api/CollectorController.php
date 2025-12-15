<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CloneLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CollectorController extends Controller
{
    public function store(Request $request)
    {
        try {
            $data = [
                'session_id' => $request->input('sessionId'),
                'domain' => $request->input('domain'),
                'url' => $request->input('url'),
                'client_ip' => $request->ip(),
                'client_user_agent' => $request->userAgent(),
                'referrer' => $request->input('referrer'),
                'screen_resolution' => $request->input('screenResolution'),
                'language' => $request->input('language'),
                'requests' => $request->input('requests', []),
                'client_timestamp' => $request->input('timestamp'),
            ];

            $cloneLog = CloneLog::create($data);

            Log::channel('daily')->info('Clone activity detected', [
                'id' => $cloneLog->id,
                'domain' => $data['domain'],
                'ip' => $data['client_ip'],
            ]);

            return response()->json([
                'status' => 'success',
                'id' => $cloneLog->id,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error storing clone log', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Internal server error',
            ], 500);
        }
    }
}
