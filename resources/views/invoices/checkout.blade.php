<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Online Checkout — {{ $invoice->invoice_number }}
            </h2>
            <a href="{{ route('invoices.show', $invoice) }}" class="text-sm text-gray-600 hover:underline">&larr; Back to Invoice</a>
        </div>
    </x-slot>

    <div class="py-8" x-data="{ method: 'credit_card' }">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg space-y-6">

                <div class="bg-indigo-50 border border-indigo-200 p-4 rounded-md flex justify-between items-center">
                    <div>
                        <p class="text-xs uppercase text-indigo-600 font-semibold">Amount Payable</p>
                        <p class="text-2xl font-bold text-gray-900">${{ number_format($invoice->total_amount, 2) }}</p>
                        <p class="text-xs text-gray-500">Billed to {{ $invoice->client->company_name }}</p>
                    </div>
                    <div class="text-right text-sm text-gray-600">
                        <p>Invoice: <strong>{{ $invoice->invoice_number }}</strong></p>
                        <p>Due Date: {{ $invoice->due_date->format('d M Y') }}</p>
                    </div>
                </div>

                @if ($errors->any())
                    <div class="p-4 bg-red-100 border border-red-400 text-red-700 rounded text-sm">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('invoices.checkout.process', $invoice) }}" class="space-y-5">
                    @csrf

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Select Payment Method</label>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                            <label class="border rounded-md p-3 cursor-pointer flex items-center space-x-2" :class="method === 'credit_card' ? 'border-indigo-600 bg-indigo-50' : 'border-gray-300'">
                                <input type="radio" name="payment_method" value="credit_card" x-model="method" class="text-indigo-600">
                                <span class="text-sm font-medium">Credit / Debit Card</span>
                            </label>

                            <label class="border rounded-md p-3 cursor-pointer flex items-center space-x-2" :class="method === 'upi' ? 'border-indigo-600 bg-indigo-50' : 'border-gray-300'">
                                <input type="radio" name="payment_method" value="upi" x-model="method" class="text-indigo-600">
                                <span class="text-sm font-medium">UPI Instant Pay</span>
                            </label>

                            <label class="border rounded-md p-3 cursor-pointer flex items-center space-x-2" :class="method === 'bank_transfer' ? 'border-indigo-600 bg-indigo-50' : 'border-gray-300'">
                                <input type="radio" name="payment_method" value="bank_transfer" x-model="method" class="text-indigo-600">
                                <span class="text-sm font-medium">NEFT / Wire Transfer</span>
                            </label>
                        </div>
                    </div>

                    <div x-show="method === 'credit_card'">
                        <label class="block text-sm font-medium text-gray-700">Cardholder Name / Last 4 Digits (Sandbox) *</label>
                        <input type="text" name="payer_reference" :disabled="method !== 'credit_card'" placeholder="e.g., VISA ending 4242" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>

                    <div x-show="method === 'upi'" style="display: none;">
                        <label class="block text-sm font-medium text-gray-700">UPI ID (VPA) *</label>
                        <input type="text" name="payer_reference" :disabled="method !== 'upi'" placeholder="e.g., client@okicici" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>

                    <div x-show="method === 'bank_transfer'" style="display: none;">
                        <label class="block text-sm font-medium text-gray-700">UTR / Bank Reference Number *</label>
                        <input type="text" name="payer_reference" :disabled="method !== 'bank_transfer'" placeholder="e.g., UTR9876543210" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                    </div>

                    <div class="flex justify-end space-x-3 pt-4 border-t">
                        <a href="{{ route('invoices.show', $invoice) }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700">Cancel</a>
                        <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md text-sm font-semibold">
                            Confirm & Pay ${{ number_format($invoice->total_amount, 2) }}
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</x-app-layout>