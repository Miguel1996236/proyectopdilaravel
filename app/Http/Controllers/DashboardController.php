<?php

namespace App\Http\Controllers;

use App\Models\User;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $stats = [
            'users' => User::count(),
            'surveys' => 0,
            'attempts' => 0,
            'invites' => 0,
        ];

        $roleCounts = User::query()
            ->selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role')
            ->toArray();

        return view('dashboard', [
            'stats' => $stats,
            'roleCounts' => $roleCounts,
        ]);
    }
}
