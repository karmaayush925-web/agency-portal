<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Invoice;
use App\Models\Project;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the role-scoped executive analytics dashboard.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->isClient()) {
            return $this->clientDashboard($user);
        }

        return $this->agencyDashboard();
    }

    /**
     * Prepare analytics data for Administrators and Project Managers.
     */
    protected function agencyDashboard(): View
    {
        $totalRevenue = Invoice::where('status', 'paid')->sum('total_amount');
        $pendingAmount = Invoice::whereIn('status', ['sent', 'overdue'])->sum('total_amount');
        $overdueCount = Invoice::where('status', 'overdue')->count();
        $activeProjects = Project::where('status', 'in_progress')->count();
        $totalClients = Client::count();

        $recentInvoices = Invoice::with(['client', 'project'])
            ->latest()
            ->take(5)
            ->get();

        $chartData = $this->getMonthlyRevenueChartData();

        return view('dashboard', [
            'isClient' => false,
            'totalRevenue' => $totalRevenue,
            'pendingAmount' => $pendingAmount,
            'overdueCount' => $overdueCount,
            'activeProjects' => $activeProjects,
            'totalClients' => $totalClients,
            'recentInvoices' => $recentInvoices,
            'chartLabels' => $chartData['labels'],
            'chartValues' => $chartData['values'],
        ]);
    }

    /**
     * Prepare scoped analytics data for logged-in Clients.
     */
    protected function clientDashboard($user): View
    {
        $clientId = $user->clientProfile?->id ?? 0;

        $totalPaid = Invoice::where('client_id', $clientId)
            ->where('status', 'paid')
            ->sum('total_amount');

        $pendingAmount = Invoice::where('client_id', $clientId)
            ->whereIn('status', ['sent', 'overdue'])
            ->sum('total_amount');

        $activeProjects = Project::where('client_id', $clientId)
            ->where('status', 'in_progress')
            ->count();

        $recentInvoices = Invoice::with('project')
            ->where('client_id', $clientId)
            ->latest()
            ->take(5)
            ->get();

        $chartData = $this->getMonthlyRevenueChartData($clientId);

        return view('dashboard', [
            'isClient' => true,
            'totalRevenue' => $totalPaid,
            'pendingAmount' => $pendingAmount,
            'overdueCount' => Invoice::where('client_id', $clientId)->where('status', 'overdue')->count(),
            'activeProjects' => $activeProjects,
            'totalClients' => 1,
            'recentInvoices' => $recentInvoices,
            'chartLabels' => $chartData['labels'],
            'chartValues' => $chartData['values'],
        ]);
    }

    /**
     * Build last 6 months paid revenue series for Chart.js (database-agnostic).
     */
    protected function getMonthlyRevenueChartData(?int $clientId = null): array
    {
        $labels = [];
        $values = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $labels[] = $month->format('M Y');

            $query = Invoice::where('status', 'paid')
                ->whereYear('issue_date', $month->year)
                ->whereMonth('issue_date', $month->month);

            if ($clientId !== null) {
                $query->where('client_id', $clientId);
            }

            $values[] = round((float) $query->sum('total_amount'), 2);
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }
}