<x-layouts.app :title="__('Integrity Check')">
    <div class="p-6 space-y-6">
        <div class="bg-white rounded-lg shadow p-6">
            <h1 class="text-2xl font-semibold text-gray-900">Integrity Check Dashboard</h1>
            <p class="text-sm text-gray-600 mt-1">Operational safety view for financial data consistency.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-500">Negative Loan Balances</p>
                <p class="text-2xl font-bold {{ $summary['negative_loans'] > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $summary['negative_loans'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-500">Missing Payment Links</p>
                <p class="text-2xl font-bold {{ ($summary['payments_missing_fund'] + $summary['payments_missing_transaction']) > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $summary['payments_missing_fund'] + $summary['payments_missing_transaction'] }}</p>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <p class="text-sm text-gray-500">Failed Jobs</p>
                <p class="text-2xl font-bold {{ $summary['failed_jobs'] > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $summary['failed_jobs'] }}</p>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Latest Reconciliation Reports</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Loan Principal</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Payments</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Fund Net</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Mismatches</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($reports as $report)
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-700">{{ $report->period_start->format('M d, Y') }} - {{ $report->period_end->format('M d, Y') }}</td>
                                <td class="px-4 py-2 text-sm text-gray-700">{{ number_format($report->loan_principal_total, 2) }}</td>
                                <td class="px-4 py-2 text-sm text-gray-700">{{ number_format($report->payments_total, 2) }}</td>
                                <td class="px-4 py-2 text-sm text-gray-700">{{ number_format($report->calculated_fund_net, 2) }}</td>
                                <td class="px-4 py-2 text-sm font-semibold {{ $report->mismatch_count > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $report->mismatch_count }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-4 text-center text-sm text-gray-500">No reconciliation reports yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Recent Audit Logs</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">When</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Event</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Model</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Record ID</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($recentAuditLogs as $log)
                            <tr>
                                <td class="px-4 py-2 text-sm text-gray-700">{{ $log->created_at?->format('Y-m-d H:i:s') }}</td>
                                <td class="px-4 py-2 text-sm text-gray-700">{{ $log->user?->name ?? 'System' }}</td>
                                <td class="px-4 py-2 text-sm text-gray-700">{{ ucfirst($log->event) }}</td>
                                <td class="px-4 py-2 text-sm text-gray-700">{{ class_basename($log->auditable_type) }}</td>
                                <td class="px-4 py-2 text-sm text-gray-700">{{ $log->auditable_id }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-4 text-center text-sm text-gray-500">No audit logs yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-layouts.app>
