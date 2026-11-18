<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProjectRequest;
use App\Models\Client;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects scoped by user role.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $query = Project::with('client')->withSum('invoices', 'total_amount')->latest();

        if ($user->isClient()) {
            $clientId = $user->clientProfile?->id ?? 0;
            $query->where('client_id', $clientId);
        }

        $projects = $query->paginate(10);

        return view('projects.index', compact('projects'));
    }

    /**
     * Show the form for creating a new project.
     */
    public function create(): View
    {
        $clients = Client::orderBy('company_name')->get();

        return view('projects.create', compact('clients'));
    }

    /**
     * Store a newly created project in storage.
     */

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        Project::create([
            'client_id' => $validated['client_id'],
            'manager_id' => auth()->id(),
            'title' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'budget' => $validated['budget'],
            'deadline' => $validated['deadline'] ?? null,
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project created successfully.');
    }

    /**
     * Update the status of the specified project.
     */
   /**
     * Update the status of the specified project.
     */
    public function updateStatus(Request $request, Project $project): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:pending,in_progress,completed'],
        ]);

        $project->update(['status' => $validated['status']]);

        return back()->with('success', "Project '{$project->title}' status updated.");
    }

    /**
     * Remove the specified project from storage.
     */
    public function destroy(Project $project): RedirectResponse
    {
        $project->delete();

        return redirect()
            ->route('projects.index')
            ->with('success', 'Project deleted successfully.');
    }
}