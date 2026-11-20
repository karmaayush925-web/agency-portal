<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInvoiceRequest;
use App\Http\Resources\InvoiceResource;
use App\Models\Invoice;
use App\Services\InvoiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class InvoiceApiController extends Controller
{
    public function __construct(protected InvoiceService $invoiceService)
    {
    }

    /**
     * List invoices scoped to the authenticated user's role.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $user = $request->user();

        $query = Invoice::with(['client', 'items'])->latest();

        if ($user->role === 'client') {
            $query->whereHas('client', fn ($q) => $q->where('user_id', $user->id));
        }

        return InvoiceResource::collection($query->paginate(10));
    }

    /**
     * Create a new invoice via API using the shared InvoiceService.
     */
    public function store(StoreInvoiceRequest $request): JsonResponse
    {
        $this->authorize('create', Invoice::class);

        $invoice = $this->invoiceService->createInvoice($request->validated());

        return (new InvoiceResource($invoice))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * Display a specific invoice with Policy IDOR protection.
     */
    public function show(Invoice $invoice): InvoiceResource
    {
        $this->authorize('view', $invoice);

        $invoice->load(['client', 'items']);

        return new InvoiceResource($invoice);
    }
}