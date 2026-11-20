<?php

namespace App\Console\Commands;

use App\Models\ActivityLog;
use App\Models\Invoice;
use Illuminate\Console\Command;

class MarkOverdueInvoices extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'invoices:mark-overdue';

    /**
     * The console command description.
     */
    protected $description = 'Automatically mark past-due unpaid invoices as overdue and log audit events';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $overdueInvoices = Invoice::where('status', 'unpaid')
            ->whereDate('due_date', '<', now()->toDateString())
            ->get();

        if ($overdueInvoices->isEmpty()) {
            $this->info('No overdue invoices found.');
            return self::SUCCESS;
        }

        foreach ($overdueInvoices as $invoice) {
            $invoice->update(['status' => 'overdue']);

            ActivityLog::record(
                'invoice.overdue',
                $invoice,
                "System automatically marked {$invoice->invoice_number} as overdue (Due: {$invoice->due_date->format('d M Y')})."
            );
        }

        $this->info("Marked {$overdueInvoices->count()} invoice(s) as overdue.");

        return self::SUCCESS;
    }
}