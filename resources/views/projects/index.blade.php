<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Projects') }}
            </h2>
            @if (auth()->user()->isAdmin() || auth()->user()->isManager())
                <a href="{{ route('projects.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-md">
                    + New Project
                </a>
            @endif
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Project</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Client</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Budget</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Billed</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            @if (auth()->user()->isAdmin() || auth()->user()->isManager())
                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Update Status</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse ($projects as $project)
                            <tr>
                                <td class="px-6 py-4 font-medium text-gray-900">{{ $project->name }}</td>
                                <td class="px-6 py-4 text-gray-600">{{ $project->client->company_name }}</td>
                                <td class="px-6 py-4 font-semibold">${{ number_format($project->budget, 2) }}</td>
                                <td class="px-6 py-4 text-gray-600">${{ number_format($project->invoices_sum_total_amount ?? 0, 2) }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-0.5 text-xs font-semibold rounded-full
                                        {{ $project->status === 'completed' ? 'bg-green-100 text-green-800' : ($project->status === 'in_progress' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                        {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                    </span>
                                </td>
                                @if (auth()->user()->isAdmin() || auth()->user()->isManager())
                                    <td class="px-6 py-4 text-right">
                                        <form method="POST" action="{{ route('projects.status', $project) }}" class="inline-flex items-center space-x-2">
                                            @csrf
                                            @method('PATCH')
                                            <select name="status" onchange="this.form.submit()" class="text-xs rounded-md border-gray-300">
                                                <option value="pending" @selected($project->status === 'pending')>Pending</option>
                                                <option value="in_progress" @selected($project->status === 'in_progress')>In Progress</option>
                                                <option value="completed" @selected($project->status === 'completed')>Completed</option>
                                            </select>
                                        </form>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-8 text-center text-gray-500">No projects found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="p-4">
                    {{ $projects->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>