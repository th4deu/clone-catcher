@extends('dashboard.layout')

@section('title', 'Log Details')

@section('content')
<div class="space-y-6">
    <!-- Back Button -->
    <div>
        <a href="{{ route('dashboard') }}" class="text-blue-600 hover:text-blue-800 flex items-center space-x-2">
            <span>←</span>
            <span>Back to Dashboard</span>
        </a>
    </div>

    <!-- Log Header -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-start mb-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-2">Clone Activity Details</h1>
                <p class="text-gray-600">Log ID: #{{ $log->id }}</p>
            </div>
            <div class="text-right">
                <div class="text-sm text-gray-500">Detected</div>
                <div class="text-lg font-semibold">{{ $log->created_at->format('Y-m-d H:i:s') }}</div>
                <div class="text-xs text-gray-400">{{ $log->created_at->diffForHumans() }}</div>
            </div>
        </div>

        <!-- Basic Info Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="border-l-4 border-red-500 bg-red-50 p-4">
                <div class="text-sm font-medium text-gray-600">Clone Domain</div>
                <div class="text-lg font-bold text-red-600">{{ $log->domain }}</div>
            </div>

            <div class="border-l-4 border-blue-500 bg-blue-50 p-4">
                <div class="text-sm font-medium text-gray-600">Session ID</div>
                <div class="text-sm font-mono">{{ $log->session_id }}</div>
            </div>

            <div class="border-l-4 border-green-500 bg-green-50 p-4">
                <div class="text-sm font-medium text-gray-600">Client IP</div>
                <div class="text-lg font-semibold">{{ $log->client_ip }}</div>
            </div>

            <div class="border-l-4 border-purple-500 bg-purple-50 p-4">
                <div class="text-sm font-medium text-gray-600">Screen Resolution</div>
                <div class="text-lg font-semibold">{{ $log->screen_resolution ?? 'N/A' }}</div>
            </div>

            <div class="border-l-4 border-yellow-500 bg-yellow-50 p-4">
                <div class="text-sm font-medium text-gray-600">Language</div>
                <div class="text-lg font-semibold">{{ $log->language ?? 'N/A' }}</div>
            </div>

            <div class="border-l-4 border-pink-500 bg-pink-50 p-4">
                <div class="text-sm font-medium text-gray-600">Referrer</div>
                <div class="text-sm truncate">{{ $log->referrer ?? 'Direct' }}</div>
            </div>
        </div>
    </div>

    <!-- Full URL -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-3">Full URL</h2>
        <div class="bg-gray-100 p-4 rounded font-mono text-sm break-all">
            {{ $log->url }}
        </div>
    </div>

    <!-- User Agent -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-3">User Agent</h2>
        <div class="bg-gray-100 p-4 rounded font-mono text-xs break-all">
            {{ $log->client_user_agent }}
        </div>
    </div>

    <!-- Captured Requests -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Captured Requests ({{ $log->request_count }})</h2>

        @if($log->request_count === 0)
            <p class="text-gray-500 text-center py-8">No requests captured in this session.</p>
        @else
            <div class="space-y-4">
                @foreach($log->requests as $index => $request)
                <div class="border rounded-lg overflow-hidden">
                    <div class="bg-gray-50 px-4 py-3 border-b flex justify-between items-center">
                        <div class="flex items-center space-x-3">
                            <span class="font-bold text-gray-700">#{{ $index + 1 }}</span>
                            <span class="px-2 py-1 text-xs font-semibold rounded
                                @if($request['type'] === 'fetch' || $request['type'] === 'xhr') bg-blue-100 text-blue-800
                                @elseif($request['type'] === 'click') bg-yellow-100 text-yellow-800
                                @elseif($request['type'] === 'form_submit') bg-red-100 text-red-800
                                @else bg-gray-100 text-gray-800
                                @endif">
                                {{ strtoupper($request['type'] ?? 'unknown') }}
                            </span>
                            @if(isset($request['method']))
                                <span class="px-2 py-1 text-xs font-semibold rounded bg-green-100 text-green-800">
                                    {{ $request['method'] }}
                                </span>
                            @endif
                        </div>
                        <span class="text-xs text-gray-500">{{ $request['timestamp'] ?? '' }}</span>
                    </div>
                    <div class="p-4 bg-gray-900 text-green-400 font-mono text-xs overflow-x-auto">
                        <pre>{{ json_encode($request, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Raw JSON Data -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-800">Raw JSON Data</h2>
            <button onclick="copyToClipboard()" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition">
                Copy JSON
            </button>
        </div>
        <div id="jsonData" class="bg-gray-900 text-green-400 p-4 rounded font-mono text-xs overflow-x-auto max-h-96">
            <pre>{{ json_encode($log->toArray(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function copyToClipboard() {
        const jsonData = @json($log->toArray());
        navigator.clipboard.writeText(JSON.stringify(jsonData, null, 2))
            .then(() => {
                alert('JSON copied to clipboard!');
            })
            .catch(err => {
                console.error('Failed to copy: ', err);
            });
    }
</script>
@endsection
