<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        private AnalyticsService $analyticsService
    ) {}

    public function index()
    {
        $stats = $this->analyticsService->getDashboardStats();

        return Inertia::render('Dashboard', [
            'stats' => $stats
        ]);
    }
}
