<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }
        .header { width: 100%; border-bottom: 2px solid #4f46e5; padding-bottom: 12px; margin-bottom: 20px; }
        .title { font-size: 22px; font-weight: bold; color: #4f46e5; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th { background-color: #f3f4f6; text-align: left; padding: 8px; border-bottom: 1px solid #ddd; }
        td { padding: 8px; border-bottom: 1px solid #eee; }
        .text-right { text-align: right; }
        .totals { width: 250px; float: right; margin-top: 20px; }
        .totals td { border: none; padding: 4px 8px; }
        .grand-total { font-weight: bold; font-size: 14px; border-top: 1px solid #333 !important; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">INVOICE: {{ $invoice->invoice_number }}</div>
        <div>Status: <strong>{{ strtoupper($invoice->status) }}</strong></div>
        @if ($invoice->transaction_id)
            <div style="color: #15803d; margin-top: 4px;">
                Transaction ID: <strong>{{ $invoice->transaction_id }}</strong>
                ({{ strtoupper(str_replace('_', ' ', $invoice->payment_method)) }} — {{ $invoice->paid_at?->format('d M Y') }})
            </div>
        @endif
    </div>

    <p><strong>Billed To:</strong><br>
        {{ $invoice->client->company_name }}<br>
        {{ $invoice->client->address }}<br>
        Tax ID: {{ $invoice->client->tax_id ?? 'N/A' }}
    </p>

    <p><strong>Issue Date:</strong> {{ $invoice->issue_date->format('d M Y') }} |
       <strong>Due Date:</strong> {{ $invoice->due_date->format('d M Y') }}</p>

    <table>
        <thead>
            <tr>
                <th>Description</th>
                <th class="text-right">Qty</th>
                <th class="text-right">Unit Price</th>
                <th class="text-right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td class="text-right">{{ $item->quantity }}</td>
                    <td class="text-right">${{ number_format($item->unit_price, 2) }}</td>
                    <td class="text-right">${{ number_format($item->total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>Subtotal:</td>
            <td class="text-right">${{ number_format($invoice->subtotal, 2) }}</td>
        </tr>
        <tr>
            <td>Tax ({{ $invoice->tax_rate }}%):</td>
            <td class="text-right">${{ number_format($invoice->tax_amount, 2) }}</td>
        </tr>
        <tr>
            <td class="grand-total">Grand Total:</td>
            <td class="text-right grand-total">${{ number_format($invoice->total_amount, 2) }}</td>
        </tr>
    </table>
</body>
</html>