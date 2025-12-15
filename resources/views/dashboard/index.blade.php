@extends('dashboard.layout')

@section('title', 'Dashboard')

@section('content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-gradient-to-br from-purple-500 to-purple-700 rounded-lg shadow-lg p-6 text-white">
            <h3 class="text-sm font-semibold opacity-90 mb-2">Total Logs</h3>
            <p class="text-4xl font-bold">{{ number_format($stats['total_logs']) }}</p>
        </div>

        <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-lg shadow-lg p-6 text-white">
            <h3 class="text-sm font-semibold opacity-90 mb-2">Unique Sessions</h3>
            <p class="text-4xl font-bold">{{ number_format($stats['total_sessions']) }}</p>
        </div>

        <div class="bg-gradient-to-br from-red-500 to-red-700 rounded-lg shadow-lg p-6 text-white">
            <h3 class="text-sm font-semibold opacity-90 mb-2">Cloned Domains</h3>
            <p class="text-4xl font-bold">{{ number_format($stats['total_domains']) }}</p>
        </div>

        <div class="bg-gradient-to-br from-green-500 to-green-700 rounded-lg shadow-lg p-6 text-white">
            <h3 class="text-sm font-semibold opacity-90 mb-2">Unique IPs</h3>
            <p class="text-4xl font-bold">{{ number_format($stats['total_ips']) }}</p>
        </div>
    </div>

    <!-- Activity Chart -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Daily Activity (Last 30 Days)</h2>
        <canvas id="activityChart" height="80"></canvas>
    </div>

    <!-- Detected Domains -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Detected Clone Domains</h2>

        @if($domains->isEmpty())
            <p class="text-gray-500 text-center py-8">No clone domains detected yet.</p>
        @else
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Domain</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Logs</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($domains as $domain)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <span class="text-red-600 font-medium">⚠️ {{ $domain->domain }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    {{ number_format($domain->count) }} logs
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <a href="{{ route('domain.show', $domain->domain) }}" class="text-blue-600 hover:text-blue-900">
                                    View Details →
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <!-- Recent Activity -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-xl font-bold text-gray-800 mb-4">Recent Activity</h2>

        @if($recentLogs->isEmpty())
            <p class="text-gray-500 text-center py-8">No activity logged yet.</p>
        @else
            <div class="space-y-3">
                @foreach($recentLogs as $log)
                <div class="border-l-4 border-red-500 bg-gray-50 p-4 hover:bg-gray-100 transition">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center space-x-2 mb-2">
                                <span class="font-bold text-gray-900">{{ $log->domain }}</span>
                                <span class="text-xs px-2 py-1 bg-red-100 text-red-800 rounded">Clone Detected</span>
                            </div>
                            <div class="text-sm text-gray-600 space-y-1">
                                <div><span class="font-medium">IP:</span> {{ $log->client_ip }}</div>
                                <div><span class="font-medium">URL:</span> {{ Str::limit($log->url, 80) }}</div>
                                <div><span class="font-medium">Requests:</span> {{ $log->request_count }} captured</div>
                            </div>
                        </div>
                        <div class="text-right ml-4">
                            <div class="text-xs text-gray-500">{{ $log->created_at->diffForHumans() }}</div>
                            <div class="text-xs text-gray-400">{{ $log->created_at->format('Y-m-d H:i:s') }}</div>
                            <a href="{{ route('log.show', $log->id) }}" class="text-xs text-blue-600 hover:underline mt-2 inline-block">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    const ctx = document.getElementById('activityChart');
    const activityData = @json($dailyActivity);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: activityData.map(d => d.date),
            datasets: [{
                label: 'Clone Activity',
                data: activityData.map(d => d.count),
                borderColor: 'rgb(239, 68, 68)',
                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
</script>
@endsection
