<?php

namespace App\Http\Controllers;

use App\Jobs\SendPaymentReceiptJob;
use App\Models\ActivityLog;
use App\Models\Invoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class InvoicePaymentController extends Controller
{
    /**
     * Show the online payment checkout page for an unpaid invoice.
     */
    public function checkout(Invoice $invoice): View|RedirectResponse
    {
        $this->authorize('view', $invoice);

        if ($invoice->status === 'paid') {
            return redirect()
                ->route('invoices.show', $invoice)
                ->with('success', 'This invoice has already been paid.');
        }

        $invoice->load(['client', 'project', 'items']);

        return view('invoices.checkout', compact('invoice'));
    }

    /**
     * Process the simulated online payment and generate a transaction reference.
     */
    public function process(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('view', $invoice);

        if ($invoice->status === 'paid') {
            return redirect()
                ->route('invoices.show', $invoice)
                ->with('success', 'This invoice is already marked as paid.');
        }

        $validated = $request->validate([
            'payment_method' => ['required', 'in:credit_card,upi,bank_transfer'],
            'payer_reference' => ['required', 'string', 'max:100'],
        ]);

        $transactionId = 'TXN-' . now()->format('Ymd') . '-' . strtoupper(Str::random(6));

        $invoice->update([
            'status' => 'paid',
            'payment_method' => $validated['payment_method'],
            'transaction_id' => $transactionId,
            'paid_at' => now(),
        ]);

        ActivityLog::record(
            'invoice.paid',
            $invoice,
            "Invoice {$invoice->invoice_number} paid online via {$validated['payment_method']} (Txn: {$transactionId})."
        );

        SendPaymentReceiptJob::dispatch($invoice);

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', "Payment successful! Transaction ID: {$transactionId}");
    }
}