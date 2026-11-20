<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $client->company_name }}
            </h2>
            <a href="{{ route('clients.index') }}" class="text-sm text-gray-600 hover:underline">&larr; Back to Clients</a>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white p-6 rounded-lg shadow-sm grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <p class="text-gray-500">Primary Contact</p>
                    <p class="font-semibold text-gray-900">{{ $client->user->name }} ({{ $client->user->email }})</p>
                </div>
                <div>
                    <p class="text-gray-500">Phone & Tax ID</p>
                    <p class="font-semibold text-gray-900">{{ $client->phone ?? 'N/A' }} | {{ $client->tax_id ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Address</p>
                    <p class="font-semibold text-gray-900">{{ $client->address ?? 'N/A' }}</p>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-sm">
                <h3 class="text-md font-semibold text-gray-800 mb-3">Projects ({{ $client->projects->count() }})</h3>
                <ul class="divide-y divide-gray-200 text-sm">
                    @forelse ($client->projects as $project)
                        <li class="py-3 flex justify-between items-center">
                            <div>
                                <span class="font-medium text-gray-900">
                                    {{ $project->title }}
                                </span>
                                @if ($project->description)
                                    <p class="text-xs text-gray-500">{{ Str::limit($project->description, 80) }}</p>
                                @endif
                            </div>
                            <span class="font-medium">${{ number_format($project->budget, 2) }} ({{ ucfirst(str_replace('_', ' ', $project->status)) }})</span>
                        </li>
                    @empty
                        <li class="py-3 text-gray-500">No projects assigned yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</x-app-layout>