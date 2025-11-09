<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizInvitation;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        $role = $user->role;

        return match ($role) {
            User::ROLE_ADMIN => $this->adminDashboard(),
            User::ROLE_TEACHER => $this->teacherDashboard($user),
            User::ROLE_STUDENT => $this->studentDashboard($user),
            default => $this->studentDashboard($user),
        };
    }

    protected function adminDashboard(): View
    {
        $stats = [
            'users' => User::count(),
            'surveys' => Quiz::count(),
            'attempts' => QuizAttempt::count(),
            'invites' => QuizInvitation::where('is_active', true)->count(),
        ];

        $roleCounts = User::query()
            ->selectRaw('role, COUNT(*) as total')
            ->groupBy('role')
            ->pluck('total', 'role')
            ->toArray();

        $recentUsers = User::latest()->take(5)->get();

        $chart = $this->buildAttemptSeries(QuizAttempt::query());

        return view('dashboard', [
            'role' => User::ROLE_ADMIN,
            'stats' => $stats,
            'roleCounts' => $roleCounts,
            'recentUsers' => $recentUsers,
            'chart' => $chart,
        ]);
    }

    protected function teacherDashboard(User $user): View
    {
        $quizIds = $user->quizzes()->pluck('id');

        $surveysCount = $quizIds->count();

        $publishedCount = $user->quizzes()
            ->where('status', 'published')
            ->count();

        $draftCount = $user->quizzes()
            ->where('status', 'draft')
            ->count();

        $closedCount = $user->quizzes()
            ->where('status', 'closed')
            ->count();

        $attemptsCount = QuizAttempt::whereIn('quiz_id', $quizIds)->count();

        $activeInvitations = QuizInvitation::whereIn('quiz_id', $quizIds)
            ->where('is_active', true)
            ->count();

        $recentSurveys = $user->quizzes()
            ->withCount(['questions', 'attempts'])
            ->latest()
            ->take(5)
            ->get();

        $recentResponses = QuizAttempt::with('quiz')
            ->whereIn('quiz_id', $quizIds)
            ->latest()
            ->take(5)
            ->get();

        $pendingAnalysis = $user->quizzes()
            ->where('status', 'closed')
            ->whereNotNull('analysis_requested_at')
            ->whereNull('analysis_completed_at')
            ->latest('analysis_requested_at')
            ->take(5)
            ->get();

        $chart = $this->buildAttemptSeries(
            QuizAttempt::query()->whereIn('quiz_id', $quizIds)
        );

        return view('dashboard', [
            'role' => User::ROLE_TEACHER,
            'stats' => [
                'total_surveys' => $surveysCount,
                'published_surveys' => $publishedCount,
                'draft_surveys' => $draftCount,
                'closed_surveys' => $closedCount,
                'responses' => $attemptsCount,
                'active_invitations' => $activeInvitations,
            ],
            'recentSurveys' => $recentSurveys,
            'recentResponses' => $recentResponses,
            'pendingAnalysis' => $pendingAnalysis,
            'chart' => $chart,
        ]);
    }

    protected function studentDashboard(User $user): View
    {
        $completedAttempts = $user->quizAttempts()->count();

        $recentAttempts = $user->quizAttempts()
            ->with('quiz')
            ->latest()
            ->take(5)
            ->get();

        $lastAttemptAt = optional($user->quizAttempts()->latest('completed_at')->first())->completed_at;

        $availableSurveys = Quiz::where('status', 'published')
            ->where(function (Builder $query) use ($user) {
                $query->where('require_login', false)
                    ->orWhereHas('attempts', function (Builder $attemptQuery) use ($user) {
                        $attemptQuery->where('user_id', $user->id);
                    });
            })
            ->count();

        $chart = $this->buildAttemptSeries(
            QuizAttempt::query()->where('user_id', $user->id)
        );

        return view('dashboard', [
            'role' => User::ROLE_STUDENT,
            'stats' => [
                'available_surveys' => $availableSurveys,
                'completed_surveys' => $completedAttempts,
                'last_activity' => $lastAttemptAt,
            ],
            'recentAttempts' => $recentAttempts,
            'chart' => $chart,
        ]);
    }

    protected function buildAttemptSeries(Builder $query): array
    {
        $period = CarbonPeriod::create(
            Carbon::now()->subDays(6)->startOfDay(),
            Carbon::now()->endOfDay()
        );

        $labels = [];
        $values = [];

        foreach ($period as $date) {
            $labels[] = $date->translatedFormat('D d');

            $values[] = (clone $query)
                ->whereBetween('created_at', [$date->startOfDay(), $date->endOfDay()])
                ->count();
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }
}
