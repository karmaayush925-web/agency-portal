<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class InvoiceService
{
    /**
     * Create a new invoice along with its line items inside a database transaction.
     */
    public function createInvoice(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {
            $subtotal = 0;
            $preparedItems = [];

            foreach ($data['items'] as $item) {
                $lineTotal = round($item['quantity'] * $item['unit_price'], 2);
                $subtotal += $lineTotal;

                $preparedItems[] = [
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total' => $lineTotal,
                ];
            }

            $taxRate = $data['tax_rate'] ?? 0;
            $taxAmount = round($subtotal * ($taxRate / 100), 2);
            $totalAmount = round($subtotal + $taxAmount, 2);

            $invoice = Invoice::create([
                'invoice_number' => $this->generateInvoiceNumber(),
                'client_id' => $data['client_id'],
                'project_id' => $data['project_id'] ?? null,
                'created_by' => auth()->id(),
                'issue_date' => $data['issue_date'],
                'due_date' => $data['due_date'],
                'subtotal' => $subtotal,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'status' => 'sent',
                'notes' => $data['notes'] ?? null,
            ]);

            $invoice->items()->createMany($preparedItems);

            return $invoice->load(['client', 'project', 'items']);
        });
    }

    /**
     * Generate a sequential invoice number (e.g., INV-2026-0001).
     */
    protected function generateInvoiceNumber(): string
    {
        $year = now()->year;
        $latestInvoice = Invoice::withTrashed()
            ->whereYear('created_at', $year)
            ->latest('id')
            ->first();

        $nextSequence = $latestInvoice ? ((int) substr($latestInvoice->invoice_number, -4)) + 1 : 1;

        return sprintf('INV-%d-%04d', $year, $nextSequence);
    }
}