<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAiAnalysis;
use App\Models\User;
use App\Services\ReportChartsService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReportsController extends Controller
{
    public function __construct(
        protected ReportChartsService $chartService
    ) {}

    public function summary(Request $request): View
    {
        $user = Auth::user();
        
        // Verificar permisos (solo admin y docentes)
        abort_unless(
            in_array($user->role, [User::ROLE_ADMIN, User::ROLE_TEACHER]),
            403
        );

        // Si es docente, solo ver sus encuestas
        $quizQuery = $user->role === User::ROLE_ADMIN
            ? Quiz::query()
            : Quiz::where('user_id', $user->id);

        // Estadísticas generales
        $stats = [
            'total_surveys' => $quizQuery->count(),
            'active_surveys' => (clone $quizQuery)->whereIn('status', ['published', 'open'])->count(),
            'closed_surveys' => (clone $quizQuery)->where('status', 'closed')->count(),
            'draft_surveys' => (clone $quizQuery)->where('status', 'draft')->count(),
        ];

        // Intentos y participación
        $quizIds = $quizQuery->pluck('id');
        $attemptsQuery = QuizAttempt::whereIn('quiz_id', $quizIds);
        
        $stats['total_attempts'] = $attemptsQuery->count();
        $stats['completed_attempts'] = (clone $attemptsQuery)->where('status', 'completed')->count();
        $stats['pending_attempts'] = (clone $attemptsQuery)->where('status', '!=', 'completed')->count();
        
        // Participación estudiantil (solo para admin)
        $stats['student_participation'] = 0;
        $stats['students_with_attempts'] = 0;
        $stats['total_students'] = 0;
        if ($user->role === User::ROLE_ADMIN) {
            $totalStudents = User::where('role', User::ROLE_STUDENT)->count();
            $studentsWithAttempts = User::where('role', User::ROLE_STUDENT)
                ->whereHas('quizAttempts', function (Builder $query) use ($quizIds) {
                    $query->whereIn('quiz_id', $quizIds);
                })
                ->count();
            
            $stats['student_participation'] = $totalStudents > 0 
                ? round(($studentsWithAttempts / $totalStudents) * 100, 1)
                : 0;
            $stats['students_with_attempts'] = $studentsWithAttempts;
            $stats['total_students'] = $totalStudents;
        }

        // Análisis IA
        $stats['ai_analyses'] = QuizAiAnalysis::whereIn('quiz_id', $quizIds)
            ->where('status', 'completed')
            ->count();

        // Gráfico de actividad semanal
        $attemptSeries = $this->chartService->buildAttemptSeries($attemptsQuery);

        // Gráfico de estado de encuestas
        $statusData = [
            'labels' => [__('Publicadas'), __('Cerradas'), __('Borradores')],
            'values' => [
                $stats['active_surveys'],
                $stats['closed_surveys'],
                $stats['draft_surveys'],
            ],
        ];

        // Gráfico de participación mensual
        $monthlySeries = $this->chartService->buildMonthlySeries($attemptsQuery);

        // Gráfico de tendencias mensuales de encuestas
        $monthlyTrends = $this->chartService->buildMonthlyTrends(
            $user->role === User::ROLE_ADMIN
                ? Quiz::query()
                : Quiz::where('user_id', $user->id)
        );

        // Usuarios por rol (solo para admin)
        $roleCounts = [];
        if ($user->role === User::ROLE_ADMIN) {
            $roleCounts = User::query()
                ->selectRaw('role, COUNT(*) as total')
                ->groupBy('role')
                ->pluck('total', 'role')
                ->toArray();
        }

        // Tabla de encuestas con filtros (unificada)
        $surveysQuery = $user->role === User::ROLE_ADMIN
            ? Quiz::with(['owner', 'attempts'])
            : Quiz::where('user_id', $user->id)->with(['owner', 'attempts']);

        // Aplicar filtros
        if ($request->has('status') && $request->status !== '') {
            $surveysQuery->where('status', $request->status);
        }

        if ($request->has('owner') && $request->owner && $user->role === User::ROLE_ADMIN) {
            $surveysQuery->where('user_id', $request->owner);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $surveysQuery->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->has('date_from') && $request->date_from) {
            $surveysQuery->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $surveysQuery->whereDate('created_at', '<=', $request->date_to);
        }

        $surveys = $surveysQuery->withCount(['questions', 'attempts', 'analyses'])
            ->with(['attempts' => function ($q) {
                $q->where('status', 'completed');
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Calcular tasa de participación para cada encuesta
        $surveys->getCollection()->transform(function ($survey) {
            $survey->participation_rate = $survey->calculateParticipationRate();

            return $survey;
        });

        // Lista de docentes para filtro (solo admin)
        $teachers = $user->role === User::ROLE_ADMIN
            ? User::where('role', User::ROLE_TEACHER)->orderBy('name')->get()
            : collect();

        // Top 3 encuestas más activas (por intentos completados)
        $topSurveys = (clone $quizQuery)
            ->withCount(['attempts' => fn ($q) => $q->where('status', 'completed')])
            ->orderBy('attempts_count', 'desc')
            ->limit(3)
            ->get()
            ->map(fn ($survey) => tap($survey, fn ($s) => $s->participation_rate = $s->calculateParticipationRate()));

        return view('reports.summary', [
            'stats' => $stats,
            'roleCounts' => $roleCounts,
            'surveys' => $surveys,
            'teachers' => $teachers,
            'topSurveys' => $topSurveys,
            'filters' => $request->only(['status', 'owner', 'search', 'date_from', 'date_to']),
            'charts' => [
                'weekly_activity' => $this->chartService->buildLineChart(
                    $attemptSeries,
                    __('Actividad semanal'),
                    __('Intentos completados')
                ),
                'survey_status' => $this->chartService->buildDonutChart(
                    $statusData['labels'],
                    $statusData['values'],
                    __('Estado de encuestas')
                ),
                'monthly_participation' => $this->chartService->buildLineChart(
                    $monthlySeries,
                    __('Participación mensual'),
                    __('Intentos')
                ),
                'role_distribution' => $user->role === User::ROLE_ADMIN
                    ? $this->chartService->buildDonutChart(
                        [__('Administradores'), __('Docentes'), __('Estudiantes')],
                        [
                            $roleCounts['administrador'] ?? 0,
                            $roleCounts['docente'] ?? 0,
                            $roleCounts['estudiante'] ?? 0,
                        ],
                        __('Distribución por rol')
                    )
                    : null,
                'monthly_trends' => $this->chartService->buildLineChart(
                    $monthlyTrends,
                    __('Tendencias mensuales'),
                    __('Encuestas creadas')
                ),
            ],
        ]);
    }

    public function students(Request $request): View
    {
        $user = Auth::user();
        
        abort_unless($user->role === User::ROLE_ADMIN, 403, __('Solo los administradores pueden ver reportes de estudiantes.'));

        // Si es docente, solo ver estudiantes que respondieron sus encuestas
        $quizIds = $user->role === User::ROLE_ADMIN
            ? Quiz::pluck('id')
            : Quiz::where('user_id', $user->id)->pluck('id');

        $query = User::where('role', User::ROLE_STUDENT)
            ->withCount([
                'quizAttempts as completed_attempts_count' => function (Builder $q) use ($quizIds) {
                    $q->whereIn('quiz_id', $quizIds)
                      ->where('status', 'completed');
                },
                'quizAttempts as total_attempts_count' => function (Builder $q) use ($quizIds) {
                    $q->whereIn('quiz_id', $quizIds);
                },
            ])
            ->with([
                'quizAttempts' => function ($q) use ($quizIds) {
                    $q->whereIn('quiz_id', $quizIds)
                      ->latest('completed_at')
                      ->limit(1);
                },
            ]);

        // Filtro por participación
        if ($request->has('participation') && $request->participation !== '') {
            if ($request->participation === 'low') {
                $query->having('completed_attempts_count', '<=', 2);
            } elseif ($request->participation === 'high') {
                $query->having('completed_attempts_count', '>=', 5);
            }
        }

        // Búsqueda por nombre o email
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $students = $query->orderBy('completed_attempts_count', 'desc')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        // Estadísticas para gráficos
        $allStudents = User::where('role', User::ROLE_STUDENT)
            ->withCount([
                'quizAttempts as completed_count' => function (Builder $q) use ($quizIds) {
                    $q->whereIn('quiz_id', $quizIds)
                      ->where('status', 'completed');
                },
            ])
            ->get();

        $participationStats = [
            'high' => $allStudents->filter(fn($s) => ($s->completed_count ?? 0) >= 5)->count(),
            'medium' => $allStudents->filter(fn($s) => ($count = ($s->completed_count ?? 0)) >= 3 && $count <= 4)->count(),
            'low' => $allStudents->filter(fn($s) => ($count = ($s->completed_count ?? 0)) >= 1 && $count <= 2)->count(),
            'none' => $allStudents->filter(fn($s) => ($s->completed_count ?? 0) === 0)->count(),
        ];

        return view('reports.students', [
            'students' => $students,
            'participationStats' => $participationStats,
            'filters' => $request->only(['search', 'participation']),
            'charts' => [
                'participation_distribution' => $this->chartService->buildDonutChart(
                    [
                        __('Alta (5+)'),
                        __('Media (3-4)'),
                        __('Baja (1-2)'),
                        __('Sin participación'),
                    ],
                    [
                        $participationStats['high'],
                        $participationStats['medium'],
                        $participationStats['low'],
                        $participationStats['none'],
                    ],
                    __('Distribución de participación')
                ),
            ],
        ]);
    }

}
