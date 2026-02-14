<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesQuizAccess;
use App\Http\Requests\UpdateQuizRequest;
use App\Http\Requests\StoreQuizRequest;
use App\Jobs\ProcessQuizAnalysisJob;
use App\Models\Quiz;
use App\Models\QuizAiAnalysis;
use App\Models\QuizInvitation;
use App\Models\User;
use App\Charts\DescriptiveBarChart;
use App\Services\QuizAnalyticsService;
use ArielMejiaDev\LarapexCharts\DonutChart;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuizController extends Controller
{
    use AuthorizesQuizAccess;

    protected const QUESTION_TYPE_LABELS = [
        'multiple_choice' => 'Opción múltiple',
        'multi_select' => 'Selección múltiple',
        'scale' => 'Escala',
        'open_text' => 'Respuesta abierta',
        'numeric' => 'Respuesta numérica',
    ];

    public function index(): View
    {
        $user = Auth::user();

        $quizzes = Quiz::query()
            ->when(
                $user->role !== User::ROLE_ADMIN,
                fn ($query) => $query->where('user_id', $user->id)
            )
            ->latest()
            ->withCount(['questions', 'attempts'])
            ->paginate(10)
            ->withQueryString();

        return view('quizzes.index', compact('quizzes'));
    }

    public function create(): View
    {
        $this->ensureTeacherOrAdmin();

        $quiz = new Quiz([
            'status' => 'draft',
            'max_attempts' => 1,
            'require_login' => true,
            'target_audience' => 'all',
            'randomize_questions' => false,
            'theme_color' => '#4e73df',
        ]);

        return view('quizzes.create', compact('quiz'));
    }

    public function createFromTemplate(): View
    {
        $this->ensureTeacherOrAdmin();

        $templates = collect(config('survey_templates.templates', []));

        return view('quizzes.create-from-template', compact('templates'));
    }

    public function storeFromTemplate(\Illuminate\Http\Request $request): RedirectResponse
    {
        $this->ensureTeacherOrAdmin();

        $request->validate([
            'template_key' => ['required', 'string', 'in:' . implode(',', array_keys(config('survey_templates.templates', [])))],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ]);

        $templateKey = $request->input('template_key');
        $templates = config('survey_templates.templates', []);
        $template = $templates[$templateKey] ?? null;

        if (!$template || empty($template['questions'])) {
            return redirect()
                ->route('quizzes.create-from-template')
                ->with('error', __('Plantilla no válida.'));
        }

        $quiz = null;

        DB::transaction(function () use (&$quiz, $request, $template) {
            $quiz = Quiz::create([
                'user_id' => $request->user()->id,
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'status' => 'draft',
                'opens_at' => null,
                'closes_at' => null,
                'max_attempts' => 1,
                'require_login' => true,
                'target_audience' => 'all',
                'randomize_questions' => false,
                'theme_color' => '#4e73df',
            ]);

            foreach ($template['questions'] as $position => $questionData) {
                $question = $quiz->questions()->create([
                    'title' => $questionData['title'],
                    'description' => $questionData['description'] ?? null,
                    'type' => $questionData['type'],
                    'position' => $position + 1,
                    'weight' => 1,
                    'settings' => $this->extractTemplateQuestionSettings($questionData),
                ]);

                if (!empty($questionData['options']) && in_array($questionData['type'], ['multiple_choice', 'multi_select'], true)) {
                    foreach ($questionData['options'] as $optionPosition => $optionData) {
                        $question->options()->create([
                            'label' => $optionData['label'],
                            'value' => $optionData['value'] ?? null,
                            'is_correct' => $optionData['is_correct'] ?? false,
                            'position' => $optionPosition + 1,
                        ]);
                    }
                }
            }
        });

        return redirect()
            ->route('quizzes.edit', $quiz)
            ->with('status', __('Encuesta creada desde plantilla. Revisa las preguntas y personaliza si lo deseas.'));
    }

    protected function extractTemplateQuestionSettings(array $questionData): ?array
    {
        if (($questionData['type'] ?? null) !== 'scale') {
            return null;
        }

        $settings = $questionData['settings'] ?? [];
        $min = $settings['scale_min'] ?? 1;
        $max = $settings['scale_max'] ?? 5;
        $step = $settings['scale_step'] ?? 1;

        return [
            'scale_min' => (int) $min,
            'scale_max' => (int) $max,
            'scale_step' => max(1, (int) $step),
        ];
    }

    public function store(StoreQuizRequest $request): RedirectResponse
    {
        $quiz = null;

        DB::transaction(function () use (&$quiz, $request) {
            $quiz = Quiz::create([
                'user_id' => $request->user()->id,
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'status' => $request->input('status', 'draft'),
                'opens_at' => $request->input('opens_at'),
                'closes_at' => $request->input('closes_at'),
                'max_attempts' => $request->input('max_attempts', 1),
                'require_login' => $request->boolean('require_login'),
                'target_audience' => $request->input('target_audience', 'all'),
                'randomize_questions' => $request->boolean('randomize_questions'),
                'theme_color' => $request->input('theme_color', '#4e73df'),
                'settings' => $request->input('settings'),
            ]);

            // Crear invitación automática si la encuesta está publicada
            if ($quiz->status !== 'draft') {
                $this->ensureDefaultInvitation($quiz);
            }
        });
        return redirect()
            ->route('quizzes.edit', $quiz)
            ->with('status', 'Encuesta creada correctamente. Ahora agrega preguntas y configuraciones.');
    }

    public function show(Quiz $quiz): View
    {
        $this->ensureQuizOwnership($quiz);

        $quiz->load(['questions.options', 'invitations', 'attempts.answers', 'analyses' => fn ($query) => $query->latest()]);

        $quiz->loadCount(['questions', 'attempts']);

        $latestAnalysis = $quiz->analyses->first();

        $questionTypeChart = $this->buildQuestionTypeChart($quiz);
        $invitationUsageChart = $this->buildInvitationUsageChart($quiz);
        $attemptTrendChart = $this->buildAttemptTrendChart($quiz);
        $questionProgress = $this->buildQuestionProgress($quiz);

        $analysisSummary = $this->buildAnalysisSummary($latestAnalysis);

        return view('quizzes.show', [
            'quiz' => $quiz,
            'latestAnalysis' => $latestAnalysis,
            'questionTypeChart' => $questionTypeChart,
            'invitationUsageChart' => $invitationUsageChart,
            'attemptTrendChart' => $attemptTrendChart,
            'questionProgress' => $questionProgress,
            'analysisSummary' => $analysisSummary,
        ]);
    }

    public function edit(Quiz $quiz): View
    {
        $this->ensureQuizOwnership($quiz);

        $quiz->load(['questions.options', 'invitations' => fn ($query) => $query->latest()]);

        return view('quizzes.edit', compact('quiz'));
    }

    public function update(UpdateQuizRequest $request, Quiz $quiz): RedirectResponse
    {
        $this->ensureQuizOwnership($quiz);

        $quiz->update($request->validated());

        return redirect()
            ->route('quizzes.edit', $quiz)
            ->with('status', 'Encuesta actualizada correctamente.');
    }

    public function destroy(Quiz $quiz): RedirectResponse
    {
        $this->ensureQuizOwnership($quiz);

        $quiz->delete();

        return redirect()
            ->route('quizzes.index')
            ->with('status', 'Encuesta eliminada correctamente.');
    }

    public function publish(Quiz $quiz): RedirectResponse
    {
        $this->ensureQuizOwnership($quiz);

        if ($quiz->status === 'closed') {
            return back()->with('status', __('La encuesta ya está cerrada.'));
        }

        DB::transaction(function () use ($quiz) {
            $quiz->update([
                'status' => 'published',
                'opens_at' => $quiz->opens_at ?? now(),
            ]);

            $this->ensureDefaultInvitation($quiz);
        });

        return back()->with('status', __('La encuesta se publicó y puede recibir respuestas.'));
    }

    public function close(Quiz $quiz): RedirectResponse
    {
        $this->ensureQuizOwnership($quiz);

        if ($quiz->status === 'closed') {
            return back()->with('status', __('La encuesta ya está cerrada.'));
        }

        $quiz->update([
            'status' => 'closed',
            'closes_at' => now(),
            'analysis_requested_at' => now(),
        ]);

        ProcessQuizAnalysisJob::dispatchSync($quiz->id);

        return back()->with('status', __('La encuesta se cerró y se generó un nuevo análisis con IA.'));
    }

    public function analyze(Quiz $quiz): RedirectResponse
    {
        $this->ensureQuizOwnership($quiz);

        if ($quiz->status !== 'closed') {
            return back()->with('error', __('Debes cerrar la encuesta antes de generar el análisis con IA.'));
        }

        $quiz->update([
            'analysis_requested_at' => now(),
        ]);

        ProcessQuizAnalysisJob::dispatchSync($quiz->id);

        return back()->with('status', __('Se generó un nuevo informe con IA.'));
    }

    public function analysis(Quiz $quiz, QuizAnalyticsService $analyticsService): View
    {
        $this->ensureQuizOwnership($quiz);

        $quiz->load(['questions.options', 'attempts.answers', 'analyses' => fn ($query) => $query->latest()]);

        $analysisRecord = $quiz->analyses->first();
        $analysisSummary = $analysisRecord ? $this->buildAnalysisSummary($analysisRecord) : [
            'summary' => null,
            'completed_at' => null,
            'recommendations' => [],
            'qualitative' => [],
        ];

        $quantitativeInsights = $analyticsService->buildQuantitativeInsights($quiz);
        $qualitativeInsights = $analyticsService->buildQualitativeInsights($quiz);
        $chartConfigs = $analyticsService->buildChartConfigsFromInsights($quantitativeInsights);

        return view('quizzes.analysis', [
            'quiz' => $quiz,
            'analysis' => $analysisRecord,
            'analysisSummary' => $analysisSummary,
            'quantitativeInsights' => $quantitativeInsights,
            'qualitativeInsights' => $qualitativeInsights,
            'analysisCharts' => $this->buildAnalysisCharts($chartConfigs),
        ]);
    }

    public function exportAnalysis(Quiz $quiz, QuizAnalyticsService $analyticsService)
    {
        $this->ensureQuizOwnership($quiz);

        $quiz->load(['questions.options', 'attempts.answers', 'analyses' => fn ($query) => $query->latest()]);
        $analysisRecord = $quiz->analyses->first();

        if (! $analysisRecord) {
            return redirect()
                ->route('quizzes.analysis.show', $quiz)
                ->with('error', __('Aún no se ha generado un informe para esta encuesta.'));
        }

        $analysisSummary = $this->buildAnalysisSummary($analysisRecord);
        $quantitativeInsights = $analyticsService->buildQuantitativeInsights($quiz);
        $qualitativeInsights = $analyticsService->buildQualitativeInsights($quiz);

        $pdf = Pdf::loadView('quizzes.analysis-pdf', [
            'quiz' => $quiz,
            'analysis' => $analysisRecord,
            'analysisSummary' => $analysisSummary,
            'quantitativeInsights' => $quantitativeInsights,
            'qualitativeInsights' => $qualitativeInsights,
        ])->setPaper('a4');

        $filename = 'informe-' . Str::slug($quiz->title ?? 'encuesta') . '-' . now()->format('Ymd_His') . '.pdf';

        return $pdf->download($filename);
    }

    protected function ensureDefaultInvitation(Quiz $quiz): void
    {
        if ($quiz->invitations()->exists()) {
            return;
        }

        $quiz->invitations()->create([
            'created_by' => Auth::id(),
            'code' => $this->generateInvitationCode(),
            'label' => __('Código inicial'),
            'is_active' => true,
        ]);
    }

    protected function generateInvitationCode(?string $preferred = null): string
    {
        $code = Str::upper($preferred ?: Str::random(8));

        while (QuizInvitation::where('code', $code)->exists()) {
            $code = Str::upper(Str::random(8));
        }

        return $code;
    }

    protected function buildQuestionTypeChart(Quiz $quiz): array
    {
        $grouped = $quiz->questions->groupBy('type');

        $labels = $grouped->map(function ($questions, $type) {
            return __(self::QUESTION_TYPE_LABELS[$type] ?? ucfirst(str_replace('_', ' ', $type)));
        })->values()->all();

        $data = $grouped->map->count()->values()->all();

        return compact('labels', 'data');
    }

    protected function buildInvitationUsageChart(Quiz $quiz): array
    {
        $labels = $quiz->invitations
            ->map(fn (QuizInvitation $invitation) => $invitation->label ?: $invitation->code)
            ->values()
            ->all();

        $data = $quiz->invitations
            ->map(fn (QuizInvitation $invitation) => $invitation->uses_count)
            ->values()
            ->all();

        return compact('labels', 'data');
    }

    protected function buildAttemptTrendChart(Quiz $quiz): array
    {
        $period = \Carbon\CarbonPeriod::create(
            now()->subDays(6)->startOfDay(),
            now()->endOfDay()
        );

        $labels = [];
        $values = [];

        foreach ($period as $date) {
            $labels[] = $date->translatedFormat('D d');
            $values[] = $quiz->attempts
                ->whereBetween('created_at', [$date->startOfDay(), $date->endOfDay()])
                ->count();
        }

        return compact('labels', 'values');
    }

    protected function buildQuestionProgress(Quiz $quiz): array
    {
        $totalQuestions = max(1, $quiz->questions->count());

        return $quiz->questions
            ->groupBy('type')
            ->map(function ($questions, $type) use ($totalQuestions) {
                return [
                    'label' => __(self::QUESTION_TYPE_LABELS[$type] ?? ucfirst(str_replace('_', ' ', $type))),
                    'count' => $questions->count(),
                    'percentage' => round(($questions->count() / $totalQuestions) * 100),
                ];
            })
            ->values()
            ->all();
    }

    protected function buildAnalysisSummary(?QuizAiAnalysis $analysis): array
    {
        if (! $analysis) {
            return [
                'status' => null,
                'summary' => null,
                'quantitative' => [],
                'qualitative' => [],
                'recommendations' => [],
                'completed_at' => null,
                'error_message' => null,
            ];
        }

        return [
            'status' => $analysis->status,
            'summary' => $analysis->summary,
            'quantitative' => $this->normalizeQuantitative($analysis->quantitative_insights),
            'qualitative' => $this->normalizeQualitative($analysis->qualitative_themes),
            'recommendations' => $this->normalizeRecommendations($analysis->recommendations),
            'completed_at' => $analysis->completed_at,
            'error_message' => $analysis->error_message,
        ];
    }

    protected function normalizeQuantitative(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->filter(fn ($item) => is_array($item) && isset($item['question']))
            ->map(function (array $item) {
                $item['key_findings'] = array_values(array_filter(
                    array_map('trim', (array) ($item['key_findings'] ?? []))
                ));

                return $item;
            })
            ->values()
            ->all();
    }

    protected function normalizeQualitative(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return collect($value)
            ->filter(fn ($item) => is_array($item) && isset($item['theme']))
            ->map(function (array $item) {
                $item['evidence'] = array_values(array_filter(
                    array_map('trim', (array) ($item['evidence'] ?? []))
                ));

                return $item;
            })
            ->values()
            ->all();
    }

    protected function normalizeRecommendations(mixed $value): array
    {
        if (is_array($value)) {
            return array_values(array_filter(array_map(function ($item) {
                $s = is_string($item) ? trim($item) : '';
                return $s !== '' ? $s : null;
            }, $value)));
        }

        if (is_string($value)) {
            $trimmed = trim($value);
            if ($trimmed === '') {
                return [];
            }
            // Si es JSON (array), parsear para evitar mostrar texto crudo con \u00f3n
            if (str_starts_with($trimmed, '[') || str_starts_with($trimmed, '{')) {
                $decoded = json_decode($trimmed, true);
                if (is_array($decoded)) {
                    return $this->normalizeRecommendations($decoded);
                }
            }
            $lines = preg_split('/[\r\n]+/', $value) ?: [];

            return array_values(array_filter(array_map('trim', $lines)));
        }

        return [];
    }

    /**
     * @param array<int, array<string, mixed>> $chartConfigs
     * @return array<int, array{chart: \ArielMejiaDev\LarapexCharts\LarapexChart, question: string, type: string}>
     */
    protected function buildAnalysisCharts(array $chartConfigs): array
    {
        $charts = [];

        foreach ($chartConfigs as $config) {
            $type = $config['type'] ?? 'bar';
            $question = $config['question'] ?? __('Gráfico');
            $labels = $config['labels'] ?? [];
            $data = $config['data'] ?? [];
            $xAxisTitle = $config['xAxisTitle'] ?? null;
            $yAxisTitle = $config['yAxisTitle'] ?? null;

            if ($type === 'pie') {
                $chart = new DonutChart();
                $chart
                    ->setTitle($question)
                    ->setHeight(320)
                    ->setLabels($labels)
                    ->setDataset($data)
                    ->setColors(['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#858796']);
            } else {
                $chart = new DescriptiveBarChart();
                $chart
                    ->setTitle($question)
                    ->setHeight(320)
                    ->setXAxis($labels)
                    ->setDataset([
                        [
                            'name' => __('Respuestas'),
                            'data' => $data,
                        ],
                    ])
                    ->setColors(['#4e73df'])
                    ->setYAxisMin(0);

                if ($type === 'horizontal_bar') {
                    $chart->setHorizontal(true);
                }

                if ($xAxisTitle) {
                    $chart->setXAxisTitle($xAxisTitle);
                }
                if ($yAxisTitle) {
                    $chart->setYAxisTitle($yAxisTitle);
                }
            }

            $charts[] = [
                'chart' => $chart,
                'question' => $question,
                'type' => $type,
            ];
        }

        return $charts;
    }
}
