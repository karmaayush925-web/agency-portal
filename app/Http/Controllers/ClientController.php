<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClientRequest;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class ClientController extends Controller
{
    /**
     * Display a listing of agency clients.
     */
    public function index(): View
    {
        $clients = Client::with('user')
            ->withCount(['projects', 'invoices'])
            ->latest()
            ->paginate(10);

        return view('clients.index', compact('clients'));
    }

    /**
     * Show the form for creating a new client.
     */
    public function create(): View
    {
        return view('clients.create');
    }

    /**
     * Store a newly created client and linked portal user account.
     */
    public function store(StoreClientRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['contact_name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'client',
            ]);

            Client::create([
                'user_id' => $user->id,
                'company_name' => $validated['company_name'],
                'contact_person' => $validated['contact_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
              
                'address' => $validated['address'] ?? null,
            ]);
        });

        return redirect()
            ->route('clients.index')
            ->with('success', 'Client profile and portal login account created successfully.');
    }

    /**
     * Display the specified client details with projects and invoices.
     */
    public function show(Client $client): View
    {
        $client->load(['user', 'projects', 'invoices']);

        return view('clients.show', compact('client'));
    }

    /**
     * Remove the specified client from storage.
     */
    public function destroy(Client $client): RedirectResponse
    {
        $client->delete();

        return redirect()
            ->route('clients.index')
            ->with('success', 'Client archived successfully.');
    }
}