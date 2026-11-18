<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreInvoiceRequest;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use App\Services\InvoiceService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class InvoiceController extends Controller
{
    public function __construct(
        protected InvoiceService $invoiceService
    ) {}

    /**
     * Display a listing of invoices scoped by user role.
     */
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Invoice::class);

        $user = $request->user();
        $query = Invoice::with(['client', 'project'])->latest();

        if ($user->isClient()) {
            $clientId = $user->clientProfile?->id ?? 0;
            $query->where('client_id', $clientId);
        }

        $invoices = $query->paginate(10);

        return view('invoices.index', compact('invoices'));
    }

    /**
     * Show the form for creating a new invoice.
     */
    public function create(): View
    {
        $this->authorize('create', Invoice::class);

        $clients = Client::orderBy('company_name')->get();
        $projects = Project::orderBy('title')->get();

        return view('invoices.create', compact('clients', 'projects'));
    }

    /**
     * Store a newly created invoice in storage.
     */
    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        $this->authorize('create', Invoice::class);

        $invoice = $this->invoiceService->createInvoice($request->validated());

        return redirect()
            ->route('invoices.show', $invoice)
            ->with('success', "Invoice {$invoice->invoice_number} created successfully.");
    }

    /**
     * Display the specified invoice.
     */
    public function show(Invoice $invoice): View
    {
        $this->authorize('view', $invoice);

        $invoice->load(['client', 'project', 'items']);

        return view('invoices.show', compact('invoice'));
    }

    /**
     * Mark an invoice as paid.
     */
    public function markAsPaid(Invoice $invoice): RedirectResponse
    {
        $this->authorize('update', $invoice);

        $invoice->update(['status' => 'paid']);

        return back()->with('success', "Invoice {$invoice->invoice_number} marked as paid.");
    }

    /**
     * Download the specified invoice as a PDF document.
     */
    public function downloadPdf(Invoice $invoice): Response
    {
        $this->authorize('view', $invoice);

        $invoice->load(['client', 'project', 'items']);

        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'));

        return $pdf->download("{$invoice->invoice_number}.pdf");
    }

    /**
     * Remove the specified invoice from storage.
     */
    public function destroy(Invoice $invoice): RedirectResponse
    {
        $this->authorize('delete', $invoice);

        $invoice->delete();

        return redirect()
            ->route('invoices.index')
            ->with('success', "Invoice {$invoice->invoice_number} deleted.");
    }
}