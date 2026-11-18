<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Create New Invoice') }}
        </h2>
    </x-slot>

    <div class="py-8" x-data="{
        items: [{ description: '', quantity: 1, unit_price: 0 }],
        taxRate: 18,
        addItem() {
            this.items.push({ description: '', quantity: 1, unit_price: 0 });
        },
        removeItem(index) {
            if (this.items.length > 1) this.items.splice(index, 1);
        },
        get subtotal() {
            return this.items.reduce((sum, item) => sum + (Number(item.quantity) * Number(item.unit_price)), 0);
        },
        get taxAmount() {
            return this.subtotal * (Number(this.taxRate) / 100);
        },
        get total() {
            return this.subtotal + this.taxAmount;
        }
    }">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white p-6 shadow-sm sm:rounded-lg">
                @if ($errors->any())
                    <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                        <ul class="list-disc pl-5 text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('invoices.store') }}">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Client *</label>
                            <select name="client_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Select Client</option>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}">{{ $client->company_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Project (Optional)</label>
                            <select name="project_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Select Project</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}">{{ $project->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Issue Date *</label>
                            <input type="date" name="issue_date" value="{{ date('Y-m-d') }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Due Date *</label>
                            <input type="date" name="due_date" value="{{ date('Y-m-d', strtotime('+15 days')) }}" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                    </div>

                    <div class="mb-6">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-md font-semibold text-gray-800">Line Items</h3>
                            <button type="button" @click="addItem()" class="text-sm bg-gray-800 text-white px-3 py-1 rounded hover:bg-gray-700">
                                + Add Item
                            </button>
                        </div>

                        <template x-for="(item, index) in items" :key="index">
                            <div class="grid grid-cols-12 gap-3 mb-3 items-center">
                                <div class="col-span-6">
                                    <input type="text" :name="`items[${index}][description]`" x-model="item.description" placeholder="Service description" required class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>
                                <div class="col-span-2">
                                    <input type="number" :name="`items[${index}][quantity]`" x-model="item.quantity" min="1" required class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>
                                <div class="col-span-3">
                                    <input type="number" step="0.01" :name="`items[${index}][unit_price]`" x-model="item.unit_price" min="0" placeholder="Unit price" required class="w-full rounded-md border-gray-300 shadow-sm text-sm">
                                </div>
                                <div class="col-span-1 text-right">
                                    <button type="button" @click="removeItem(index)" class="text-red-600 hover:text-red-800 font-bold" x-show="items.length > 1">&times;</button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea name="notes" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm" placeholder="Payment instructions or terms..."></textarea>
                        </div>

                        <div class="bg-gray-50 p-4 rounded-md space-y-2 text-sm">
                            <div class="flex justify-between">
                                <span>Subtotal:</span>
                                <span class="font-medium">$<span x-text="subtotal.toFixed(2)"></span></span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span>Tax Rate (%):</span>
                                <input type="number" step="0.01" name="tax_rate" x-model="taxRate" class="w-20 rounded-md border-gray-300 text-right text-sm">
                            </div>
                            <div class="flex justify-between">
                                <span>Tax Amount:</span>
                                <span class="font-medium">$<span x-text="taxAmount.toFixed(2)"></span></span>
                            </div>
                            <div class="flex justify-between text-base font-bold border-t pt-2">
                                <span>Grand Total:</span>
                                <span>$<span x-text="total.toFixed(2)"></span></span>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('invoices.index') }}" class="px-4 py-2 border border-gray-300 rounded-md text-sm text-gray-700">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md text-sm font-medium hover:bg-indigo-700">Save Invoice</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>