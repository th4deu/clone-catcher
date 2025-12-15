@extends('dashboard.layout')

@section('title', 'Domain: ' . $domain)

@section('content')
<div class="space-y-6">
    <!-- Back Button -->
    <div>
        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800 flex items-center space-x-2">
            <span>←</span>
            <span>Back to Dashboard</span>
        </a>
    </div>

    <!-- Domain Header -->
    <div class="bg-gradient-to-r from-red-600 to-red-800 rounded-lg shadow-lg p-8 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-4xl font-bold mb-2">⚠️ {{ $domain }}</h1>
                <p class="text-red-100">Clone Domain Analysis</p>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="text-sm font-medium text-gray-600 mb-2">Total Logs</div>
            <div class="text-3xl font-bold text-gray-900">{{ number_format($stats['total_logs']) }}</div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="text-sm font-medium text-gray-600 mb-2">Unique Sessions</div>
            <div class="text-3xl font-bold text-gray-900">{{ number_format($stats['unique_sessions']) }}</div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="text-sm font-medium text-gray-600 mb-2">Unique IPs</div>
            <div class="text-3xl font-bold text-gray-900">{{ number_format($stats['unique_ips']) }}</div>
        </div>
    </div>

    <!-- Activity Logs -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-6">
            <h2 class="text-xl font-bold text-gray-800">Activity Logs</h2>
            <div class="text-sm text-gray-600">
                Showing {{ $logs->firstItem() ?? 0 }} - {{ $logs->lastItem() ?? 0 }} of {{ number_format($logs->total()) }}
            </div>
        </div>

        @if($logs->isEmpty())
            <p class="text-gray-500 text-center py-8">No activity logged for this domain.</p>
        @else
            <div class="space-y-3 mb-6">
                @foreach($logs as $log)
                <div class="border rounded-lg p-4 hover:bg-gray-50 transition">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3 mb-2">
                                <span class="font-bold text-gray-900">Session: {{ Str::limit($log->session_id, 20) }}</span>
                                <span class="text-xs px-2 py-1 bg-blue-100 text-blue-800 rounded">
                                    {{ $log->request_count }} requests
                                </span>
                            </div>
                            <div class="text-sm text-gray-600 space-y-1">
                                <div><span class="font-medium">IP:</span> {{ $log->client_ip }}</div>
                                <div><span class="font-medium">URL:</span> {{ Str::limit($log->url, 100) }}</div>
                                @if($log->referrer)
                                    <div><span class="font-medium">Referrer:</span> {{ Str::limit($log->referrer, 60) }}</div>
                                @endif
                                <div class="flex items-center space-x-4">
                                    @if($log->screen_resolution)
                                        <span><span class="font-medium">Screen:</span> {{ $log->screen_resolution }}</span>
                                    @endif
                                    @if($log->language)
                                        <span><span class="font-medium">Language:</span> {{ $log->language }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="text-right ml-4">
                            <div class="text-xs text-gray-500 mb-1">{{ $log->created_at->diffForHumans() }}</div>
                            <div class="text-xs text-gray-400 mb-3">{{ $log->created_at->format('Y-m-d H:i:s') }}</div>
                            <a href="{{ route('log.show', $log->id) }}" class="text-sm px-3 py-1 bg-blue-600 text-white rounded hover:bg-blue-700 transition inline-block">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="border-t pt-4">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
