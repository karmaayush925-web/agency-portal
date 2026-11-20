<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            System Activity & Audit Logs
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead>
                        <tr class="text-left text-xs font-medium text-gray-500 uppercase">
                            <th class="py-3">Timestamp</th>
                            <th class="py-3">Actor</th>
                            <th class="py-3">Action</th>
                            <th class="py-3">Description</th>
                            <th class="py-3">IP Address</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @forelse ($logs as $log)
                            <tr>
                                <td class="py-3 text-gray-500">{{ $log->created_at->format('d M Y, h:i A') }}</td>
                                <td class="py-3 font-medium text-gray-800">{{ $log->user?->name ?? 'System Scheduler' }}</td>
                                <td class="py-3">
                                    <span class="px-2 py-1 text-xs font-semibold rounded bg-indigo-100 text-indigo-800">
                                        {{ $log->action }}
                                    </span>
                                </td>
                                <td class="py-3 text-gray-700">{{ $log->description }}</td>
                                <td class="py-3 text-gray-400 text-xs">{{ $log->ip_address ?? 'CLI' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-6 text-center text-gray-500">No audit logs recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="mt-4">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>