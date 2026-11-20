<?php

namespace App\Jobs;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendPaymentReceiptJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(public Invoice $invoice)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->invoice->loadMissing('client.user');

        $recipientEmail = $this->invoice->client?->user?->email ?? 'billing@client.local';

        Log::info('Queued Payment Receipt Dispatched', [
            'invoice_number' => $this->invoice->invoice_number,
            'transaction_id' => $this->invoice->transaction_id,
            'amount' => $this->invoice->total_amount,
            'recipient' => $recipientEmail,
        ]);
    }
}