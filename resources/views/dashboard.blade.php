<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $isClient ? __('Client Billing & Project Overview') : __('Agency Executive Dashboard') }}
            </h2>
            @can('create', App\Models\Invoice::class)
                <a href="{{ route('invoices.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-md">
                    + New Invoice
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <!-- KPI Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-green-500">
                    <p class="text-xs font-medium text-gray-500 uppercase">
                        {{ $isClient ? 'Total Paid' : 'Total Revenue' }}
                    </p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">
                        ${{ number_format($totalRevenue, 2) }}
                    </p>
                </div>

                <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-yellow-500">
                    <p class="text-xs font-medium text-gray-500 uppercase">Pending Receivables</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">
                        ${{ number_format($pendingAmount, 2) }}
                    </p>
                    @if ($overdueCount > 0)
                        <p class="mt-1 text-xs text-red-600 font-medium">{{ $overdueCount }} overdue invoice(s)</p>
                    @endif
                </div>

                <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-indigo-500">
                    <p class="text-xs font-medium text-gray-500 uppercase">Active Projects</p>
                    <p class="mt-2 text-2xl font-bold text-gray-900">{{ $activeProjects }}</p>
                </div>

                @if (! $isClient)
                    <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-purple-500">
                        <p class="text-xs font-medium text-gray-500 uppercase">Total Clients</p>
                        <p class="mt-2 text-2xl font-bold text-gray-900">{{ $totalClients }}</p>
                    </div>
                @else
                    <div class="bg-white p-6 rounded-lg shadow-sm border-l-4 border-blue-500">
                        <p class="text-xs font-medium text-gray-500 uppercase">Account Status</p>
                        <p class="mt-2 text-lg font-bold text-green-600">Active Partner</p>
                    </div>
                @endif
            </div>

            <!-- Revenue Chart + Recent Invoices -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Chart Card -->
                <div class="bg-white p-6 rounded-lg shadow-sm lg:col-span-1">
                    <h3 class="text-md font-semibold text-gray-800 mb-4">
                        {{ $isClient ? 'Payments (Last 6 Months)' : 'Monthly Revenue (Last 6 Months)' }}
                    </h3>
                    <canvas id="revenueChart" height="220"></canvas>
                </div>

                <!-- Recent Invoices Table -->
                <div class="bg-white p-6 rounded-lg shadow-sm lg:col-span-2">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-md font-semibold text-gray-800">Recent Invoices</h3>
                        <a href="{{ route('invoices.index') }}" class="text-sm text-indigo-600 hover:underline">View All</a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Invoice</th>
                                    @if (! $isClient)
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                                    @endif
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Due Date</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @forelse ($recentInvoices as $invoice)
                                    <tr>
                                        <td class="px-4 py-3 font-medium text-indigo-600">
                                            <a href="{{ route('invoices.show', $invoice) }}">{{ $invoice->invoice_number }}</a>
                                        </td>
                                        @if (! $isClient)
                                            <td class="px-4 py-3 text-gray-800">{{ $invoice->client->company_name }}</td>
                                        @endif
                                        <td class="px-4 py-3 text-gray-500">{{ $invoice->due_date->format('d M Y') }}</td>
                                        <td class="px-4 py-3 font-semibold text-gray-900">${{ number_format($invoice->total_amount, 2) }}</td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-0.5 text-xs font-semibold rounded-full
                                                {{ $invoice->status === 'paid' ? 'bg-green-100 text-green-800' : ($invoice->status === 'overdue' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                                {{ ucfirst($invoice->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-6 text-center text-gray-500">No recent invoices found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('revenueChart').getContext('2d');
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: @json($chartLabels),
                    datasets: [{
                        label: 'Paid Revenue ($)',
                        data: @json($chartValues),
                        backgroundColor: 'rgba(79, 70, 229, 0.8)',
                        borderColor: 'rgba(79, 70, 229, 1)',
                        borderWidth: 1,
                        borderRadius: 6
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function (value) {
                                    return '$' + value;
                                }
                            }
                        }
                    }
                }
            });
        });
    </script>
</x-app-layout>