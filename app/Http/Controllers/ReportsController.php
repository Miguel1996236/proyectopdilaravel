<?php

namespace App\Http\Controllers;

use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAiAnalysis;
use App\Models\User;
use ArielMejiaDev\LarapexCharts\LarapexChart;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ReportsController extends Controller
{
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
        $attemptSeries = $this->buildAttemptSeries($attemptsQuery);

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
        $monthlySeries = $this->buildMonthlySeries($attemptsQuery);

        // Gráfico de tendencias mensuales de encuestas
        $monthlyTrends = $this->buildMonthlyTrends(
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
            $totalInvitations = $survey->invitations()->where('is_active', true)->count();
            // Contar invitaciones que tienen al menos un intento completado
            $usedInvitations = $survey->invitations()
                ->where('is_active', true)
                ->whereHas('attempts', function ($q) {
                    $q->where('status', 'completed');
                })
                ->count();
            
            $survey->participation_rate = $totalInvitations > 0
                ? min(100, round(($usedInvitations / $totalInvitations) * 100, 1))
                : 0;
            
            return $survey;
        });

        // Lista de docentes para filtro (solo admin)
        $teachers = $user->role === User::ROLE_ADMIN
            ? User::where('role', User::ROLE_TEACHER)->orderBy('name')->get()
            : collect();

        // Top 3 encuestas más activas (por intentos completados)
        $topSurveys = (clone $quizQuery)
            ->withCount(['attempts' => function ($q) {
                $q->where('status', 'completed');
            }])
            ->orderBy('attempts_count', 'desc')
            ->limit(3)
            ->get()
            ->map(function ($survey) {
                $totalInvitations = $survey->invitations()->where('is_active', true)->count();
                // Contar invitaciones que tienen al menos un intento completado
                $usedInvitations = $survey->invitations()
                    ->where('is_active', true)
                    ->whereHas('attempts', function ($q) {
                        $q->where('status', 'completed');
                    })
                    ->count();
                
                $survey->participation_rate = $totalInvitations > 0
                    ? min(100, round(($usedInvitations / $totalInvitations) * 100, 1))
                    : 0;
                
                return $survey;
            });

        return view('reports.summary', [
            'stats' => $stats,
            'roleCounts' => $roleCounts,
            'surveys' => $surveys,
            'teachers' => $teachers,
            'topSurveys' => $topSurveys,
            'filters' => $request->only(['status', 'owner', 'search', 'date_from', 'date_to']),
            'charts' => [
                'weekly_activity' => $this->buildLineChart(
                    $attemptSeries,
                    __('Actividad semanal'),
                    __('Intentos completados')
                ),
                'survey_status' => $this->buildDonutChart(
                    $statusData['labels'],
                    $statusData['values'],
                    __('Estado de encuestas')
                ),
                'monthly_participation' => $this->buildLineChart(
                    $monthlySeries,
                    __('Participación mensual'),
                    __('Intentos')
                ),
                'role_distribution' => $user->role === User::ROLE_ADMIN 
                    ? $this->buildDonutChart(
                        [__('Administradores'), __('Docentes'), __('Estudiantes')],
                        [
                            $roleCounts['administrador'] ?? 0,
                            $roleCounts['docente'] ?? 0,
                            $roleCounts['estudiante'] ?? 0,
                        ],
                        __('Distribución por rol')
                    )
                    : null,
                'monthly_trends' => $this->buildLineChart(
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
        
        // Solo administradores pueden ver reportes de estudiantes
        abort_unless($user->role === User::ROLE_ADMIN, 403, __('Solo los administradores pueden ver reportes de estudiantes.'));
        
        abort_unless(
            in_array($user->role, [User::ROLE_ADMIN, User::ROLE_TEACHER]),
            403
        );

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
                'participation_distribution' => $this->buildDonutChart(
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

    public function surveys(Request $request): View
    {
        $user = Auth::user();
        
        abort_unless(
            in_array($user->role, [User::ROLE_ADMIN, User::ROLE_TEACHER]),
            403
        );

        $query = $user->role === User::ROLE_ADMIN
            ? Quiz::with(['owner', 'attempts'])
            : Quiz::where('user_id', $user->id)->with(['owner', 'attempts']);

        // Filtros
        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        if ($request->has('owner') && $request->owner && $user->role === User::ROLE_ADMIN) {
            $query->where('user_id', $request->owner);
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filtro por rango de fechas
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $surveys = $query->withCount(['questions', 'attempts', 'analyses'])
            ->with(['attempts' => function ($q) {
                $q->where('status', 'completed');
            }])
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Calcular tasa de participación para cada encuesta
        $surveys->getCollection()->transform(function ($survey) {
            $totalInvitations = $survey->invitations()->where('is_active', true)->count();
            // Contar invitaciones que tienen al menos un intento completado
            $usedInvitations = $survey->invitations()
                ->where('is_active', true)
                ->whereHas('attempts', function ($q) {
                    $q->where('status', 'completed');
                })
                ->count();
            
            $survey->participation_rate = $totalInvitations > 0
                ? min(100, round(($usedInvitations / $totalInvitations) * 100, 1))
                : 0;
            
            return $survey;
        });

        // Estadísticas para gráficos
        $statusStats = [
            'published' => (clone $query)->where('status', 'published')->count(),
            'closed' => (clone $query)->where('status', 'closed')->count(),
            'draft' => (clone $query)->where('status', 'draft')->count(),
        ];

        // Gráfico de tendencias mensuales
        $monthlyTrends = $this->buildMonthlyTrends(
            $user->role === User::ROLE_ADMIN
                ? Quiz::query()
                : Quiz::where('user_id', $user->id)
        );

        // Lista de docentes para filtro (solo admin)
        $teachers = $user->role === User::ROLE_ADMIN
            ? User::where('role', User::ROLE_TEACHER)->orderBy('name')->get()
            : collect();

        return view('reports.surveys', [
            'surveys' => $surveys,
            'statusStats' => $statusStats,
            'teachers' => $teachers,
            'filters' => $request->only(['status', 'owner', 'search', 'date_from', 'date_to']),
            'charts' => [
                'status_distribution' => $this->buildDonutChart(
                    [__('Publicadas'), __('Cerradas'), __('Borradores')],
                    [
                        $statusStats['published'],
                        $statusStats['closed'],
                        $statusStats['draft'],
                    ],
                    __('Distribución por estado')
                ),
                'monthly_trends' => $this->buildLineChart(
                    $monthlyTrends,
                    __('Tendencias mensuales'),
                    __('Encuestas creadas')
                ),
            ],
        ]);
    }

    /**
     * Construir serie de datos para actividad semanal
     */
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
                ->where('status', 'completed')
                ->count();
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * Construir serie de datos para participación mensual
     */
    protected function buildMonthlySeries(Builder $query): array
    {
        $period = CarbonPeriod::create(
            Carbon::now()->subMonths(5)->startOfMonth(),
            Carbon::now()->endOfMonth(),
            '1 month'
        );

        $labels = [];
        $values = [];

        foreach ($period as $date) {
            $labels[] = $date->translatedFormat('M Y');
            $values[] = (clone $query)
                ->whereBetween('created_at', [$date->startOfMonth(), $date->endOfMonth()])
                ->where('status', 'completed')
                ->count();
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * Construir serie de datos para tendencias mensuales de encuestas
     */
    protected function buildMonthlyTrends(Builder $query): array
    {
        $period = CarbonPeriod::create(
            Carbon::now()->subMonths(5)->startOfMonth(),
            Carbon::now()->endOfMonth(),
            '1 month'
        );

        $labels = [];
        $values = [];

        foreach ($period as $date) {
            $labels[] = $date->translatedFormat('M Y');
            $values[] = (clone $query)
                ->whereBetween('created_at', [$date->startOfMonth(), $date->endOfMonth()])
                ->count();
        }

        return [
            'labels' => $labels,
            'values' => $values,
        ];
    }

    /**
     * Construir gráfico de línea
     */
    protected function buildLineChart(array $series, string $title, string $datasetLabel)
    {
        if (empty($series['labels']) || array_sum($series['values']) === 0) {
            return null;
        }

        $chart = (new LarapexChart())->lineChart();
        $chart
            ->setHeight(300)
            ->setColors(['#4e73df'])
            ->setMarkers(['#2e59d9'], 7, 10)
            ->setXAxis($series['labels'])
            ->addData($datasetLabel, $series['values']);

        return $chart;
    }

    /**
     * Construir gráfico de dona
     */
    protected function buildDonutChart(array $labels, array $values, string $title)
    {
        if (array_sum($values) === 0) {
            return null;
        }

        $chart = (new LarapexChart())->donutChart();
        $chart
            ->setHeight(300)
            ->setLabels($labels)
            ->addData($values)
            ->setColors(['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796']);

        return $chart;
    }
}
