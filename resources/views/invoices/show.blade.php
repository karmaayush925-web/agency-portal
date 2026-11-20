<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Invoice {{ $invoice->invoice_number }}
            </h2>
            <div class="flex space-x-2">
                @if ($invoice->status !== 'paid')
                    <a href="{{ route('invoices.checkout', $invoice) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2 rounded-md">
                        Pay Now
                    </a>
                @endif
                <a href="{{ route('invoices.pdf', $invoice) }}" class="bg-gray-800 hover:bg-gray-700 text-white text-sm px-4 py-2 rounded-md">
                    Download PDF
                </a>

                @can('update', $invoice)
                    <form method="POST" action="{{ route('invoices.pay', $invoice) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white text-sm px-4 py-2 rounded-md">
                            Mark as Paid
                        </button>
                    </form>
                @endcan

                @can('delete', $invoice)
                    <form method="POST" action="{{ route('invoices.destroy', $invoice) }}" onsubmit="return confirm('Delete this invoice?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white text-sm px-4 py-2 rounded-md">
                            Delete
                        </button>
                    </form>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            <div class="bg-white p-8 shadow-sm sm:rounded-lg">
                <div class="flex justify-between border-b pb-6 mb-6">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900">Billed To:</h3>
                        <p class="text-gray-700 font-medium">{{ $invoice->client->company_name }}</p>
                        <p class="text-sm text-gray-500">{{ $invoice->client->address }}</p>
                        <p class="text-sm text-gray-500">Tax ID: {{ $invoice->client->tax_id ?? 'N/A' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500">Status: <strong class="uppercase text-gray-800">{{ $invoice->status }}</strong></p>
                        @if ($invoice->transaction_id)
                            <p class="text-xs text-green-700 mt-1">
                                Paid via <strong>{{ strtoupper(str_replace('_', ' ', $invoice->payment_method)) }}</strong>
                                | Txn ID: <strong>{{ $invoice->transaction_id }}</strong>
                                ({{ $invoice->paid_at?->format('d M Y, h:i A') }})
                            </p>
                        @endif
                        <p class="text-sm text-gray-500 mt-1">Issue Date: {{ $invoice->issue_date->format('d M Y') }}</p>
                        <p class="text-sm text-gray-500">Due Date: {{ $invoice->due_date->format('d M Y') }}</p>
                    </div>
                </div>

                <table class="min-w-full divide-y divide-gray-200 mb-6">
                    <thead>
                        <tr class="text-left text-xs font-medium text-gray-500 uppercase">
                            <th class="py-2">Description</th>
                            <th class="py-2 text-right">Qty</th>
                            <th class="py-2 text-right">Unit Price</th>
                            <th class="py-2 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 text-sm">
                        @foreach ($invoice->items as $item)
                            <tr>
                                <td class="py-3">{{ $item->description }}</td>
                                <td class="py-3 text-right">{{ $item->quantity }}</td>
                                <td class="py-3 text-right">${{ number_format($item->unit_price, 2) }}</td>
                                <td class="py-3 text-right font-medium">${{ number_format($item->total, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="flex justify-end">
                    <div class="w-64 space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Subtotal:</span>
                            <span>${{ number_format($invoice->subtotal, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tax ({{ $invoice->tax_rate }}%):</span>
                            <span>${{ number_format($invoice->tax_amount, 2) }}</span>
                        </div>
                        <div class="flex justify-between text-base font-bold border-t pt-2">
                            <span>Total:</span>
                            <span>${{ number_format($invoice->total_amount, 2) }}</span>
                        </div>
                    </div>
                </div>

                @if ($invoice->notes)
                    <div class="mt-8 border-t pt-4 text-sm text-gray-600">
                        <strong>Notes:</strong> {{ $invoice->notes }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>