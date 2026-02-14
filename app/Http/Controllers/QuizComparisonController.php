<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesQuizAccess;
use App\Models\Quiz;
use App\Models\QuizComparison;
use App\Models\User;
use App\Services\OpenAIService;
use App\Services\QuizAnalyticsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class QuizComparisonController extends Controller
{
    use AuthorizesQuizAccess;

    public function index(): View
    {
        $this->ensureTeacherOrAdmin();
        $user = Auth::user();

        $quizzes = Quiz::query()
            ->when($user->role !== User::ROLE_ADMIN, fn ($q) => $q->where('user_id', $user->id))
            ->where('status', 'closed')
            ->withCount(['questions', 'attempts'])
            ->orderBy('title')
            ->get();

        $comparisons = QuizComparison::query()
            ->where('user_id', $user->id)
            ->with([
                'quizA' => fn ($q) => $q->withCount('attempts'),
                'quizB' => fn ($q) => $q->withCount('attempts'),
            ])
            ->orderByDesc('updated_at')
            ->paginate(10);

        return view('comparisons.index', compact('quizzes', 'comparisons'));
    }

    /**
     * Ver una comparación guardada anteriormente.
     */
    public function show(QuizComparison $comparison): View
    {
        $this->ensureTeacherOrAdmin();
        $user = Auth::user();

        if ($comparison->user_id !== $user->id) {
            abort(403);
        }

        $comparison->load(['quizA', 'quizB']);
        $quizA = $comparison->quizA;
        $quizB = $comparison->quizB;

        if (! $quizA || ! $quizB) {
            abort(404, __('Una o ambas encuestas de esta comparación ya no existen.'));
        }

        return view('comparisons.result', [
            'quizA' => $quizA,
            'quizB' => $quizB,
            'statsA' => $comparison->stats_a ?? [],
            'statsB' => $comparison->stats_b ?? [],
            'insightsA' => $comparison->insights_a ?? [],
            'insightsB' => $comparison->insights_b ?? [],
            'aiAnalysis' => $comparison->ai_analysis,
            'aiError' => $comparison->error_message,
            'comparison' => $comparison,
        ]);
    }

    public function compare(Request $request, QuizAnalyticsService $analyticsService): View|RedirectResponse
    {
        $this->ensureTeacherOrAdmin();

        $data = $request->validate([
            'quiz_a' => ['required', 'exists:quizzes,id'],
            'quiz_b' => ['required', 'exists:quizzes,id', 'different:quiz_a'],
        ]);

        [$quizA, $quizB] = $this->loadAndAuthorizeQuizPair($data['quiz_a'], $data['quiz_b']);

        $insightsA = $analyticsService->buildQuantitativeInsights($quizA);
        $insightsB = $analyticsService->buildQuantitativeInsights($quizB);
        $statsA = $this->buildComparisonStats($quizA);
        $statsB = $this->buildComparisonStats($quizB);

        // Buscar comparación guardada previamente
        $saved = $this->findSavedComparison($quizA->id, $quizB->id);

        return view('comparisons.result', [
            'quizA' => $quizA,
            'quizB' => $quizB,
            'statsA' => $statsA,
            'statsB' => $statsB,
            'insightsA' => $insightsA,
            'insightsB' => $insightsB,
            'aiAnalysis' => $saved?->ai_analysis,
            'aiError' => null,
            'comparison' => $saved,
        ]);
    }

    public function analyzeWithAI(Request $request, QuizAnalyticsService $analyticsService, OpenAIService $openAI): View|RedirectResponse
    {
        $this->ensureTeacherOrAdmin();

        $data = $request->validate([
            'quiz_a' => ['required', 'exists:quizzes,id'],
            'quiz_b' => ['required', 'exists:quizzes,id', 'different:quiz_a'],
        ]);

        [$quizA, $quizB] = $this->loadAndAuthorizeQuizPair($data['quiz_a'], $data['quiz_b']);

        $insightsA = $analyticsService->buildQuantitativeInsights($quizA);
        $insightsB = $analyticsService->buildQuantitativeInsights($quizB);
        $statsA = $this->buildComparisonStats($quizA);
        $statsB = $this->buildComparisonStats($quizB);

        $prompt = $this->buildComparisonPrompt($quizA, $quizB, $insightsA, $insightsB, $statsA, $statsB);

        $aiAnalysis = null;
        $aiError = null;

        try {
            $response = $openAI->chat($prompt, [
                'temperature' => 0.3,
                'max_tokens' => 2000,
            ]);

            $aiAnalysis = data_get($response, 'choices.0.message.content');
        } catch (\Exception $e) {
            $aiError = $e->getMessage();
        }

        // Normalizar IDs para el par (menor siempre primero)
        [$normalizedA, $normalizedB] = $this->normalizeQuizPair($quizA->id, $quizB->id);

        // Guardar o actualizar la comparación
        $comparison = QuizComparison::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'quiz_a_id' => $normalizedA,
                'quiz_b_id' => $normalizedB,
            ],
            [
                'ai_analysis' => $aiAnalysis,
                'stats_a' => $statsA,
                'stats_b' => $statsB,
                'insights_a' => $insightsA,
                'insights_b' => $insightsB,
                'error_message' => $aiError,
                'analyzed_at' => $aiAnalysis ? now() : null,
            ]
        );

        return view('comparisons.result', [
            'quizA' => $quizA,
            'quizB' => $quizB,
            'statsA' => $statsA,
            'statsB' => $statsB,
            'insightsA' => $insightsA,
            'insightsB' => $insightsB,
            'aiAnalysis' => $aiAnalysis,
            'aiError' => $aiError,
            'comparison' => $comparison,
        ]);
    }

    /**
     * Cargar y autorizar un par de encuestas.
     *
     * @return array{0: Quiz, 1: Quiz}
     */
    protected function loadAndAuthorizeQuizPair(int|string $idA, int|string $idB): array
    {
        $quizA = Quiz::with(['questions.options', 'attempts.answers', 'owner'])->findOrFail($idA);
        $quizB = Quiz::with(['questions.options', 'attempts.answers', 'owner'])->findOrFail($idB);

        $user = Auth::user();
        if ($user->role !== User::ROLE_ADMIN) {
            abort_if($quizA->user_id !== $user->id || $quizB->user_id !== $user->id, 403);
        }

        return [$quizA, $quizB];
    }

    /**
     * Normalizar par de IDs (menor primero) para evitar duplicados.
     *
     * @return array{0: int, 1: int}
     */
    protected function normalizeQuizPair(int $idA, int $idB): array
    {
        return $idA <= $idB ? [$idA, $idB] : [$idB, $idA];
    }

    /**
     * Buscar una comparación guardada para el par de encuestas.
     */
    protected function findSavedComparison(int $idA, int $idB): ?QuizComparison
    {
        [$normalizedA, $normalizedB] = $this->normalizeQuizPair($idA, $idB);

        return QuizComparison::where('user_id', Auth::id())
            ->where('quiz_a_id', $normalizedA)
            ->where('quiz_b_id', $normalizedB)
            ->first();
    }

    protected function buildComparisonStats(Quiz $quiz): array
    {
        $attempts = $quiz->attempts;
        $completed = $attempts->where('status', 'completed');

        return [
            'total_attempts' => $attempts->count(),
            'completed' => $completed->count(),
            'questions_count' => $quiz->questions->count(),
            'avg_score' => $completed->avg('score') ?? 0,
            'max_score' => $completed->max('score') ?? 0,
            'created_at' => $quiz->created_at?->format('d/m/Y'),
            'closed_at' => $quiz->closes_at?->format('d/m/Y'),
        ];
    }

    protected function buildComparisonPrompt(Quiz $quizA, Quiz $quizB, array $insightsA, array $insightsB, array $statsA, array $statsB): string
    {
        $dataA = json_encode([
            'titulo' => $quizA->title,
            'descripcion' => $quizA->description,
            'fecha' => $statsA['created_at'],
            'participantes' => $statsA['completed'],
            'preguntas' => $statsA['questions_count'],
            'insights' => $insightsA,
        ], JSON_UNESCAPED_UNICODE);

        $dataB = json_encode([
            'titulo' => $quizB->title,
            'descripcion' => $quizB->description,
            'fecha' => $statsB['created_at'],
            'participantes' => $statsB['completed'],
            'preguntas' => $statsB['questions_count'],
            'insights' => $insightsB,
        ], JSON_UNESCAPED_UNICODE);

        return <<<PROMPT
Eres un analista pedagógico experto en evaluación educativa. Tu tarea es realizar una comparación profunda y útil entre dos encuestas educativas.

**IMPORTANTE**: Las encuestas deben tener relación temática para una comparación significativa. Si no tienen relación, indícalo al inicio.

## Encuesta A:
{$dataA}

## Encuesta B:
{$dataB}

Proporciona tu análisis usando este formato en markdown:

### 1. Resumen de la comparación
Un párrafo de 3-4 oraciones explicando qué se compara, la relación entre ambas encuestas y la conclusión general.

### 2. Diferencias principales
Enumera las diferencias más significativas entre ambas encuestas. Para cada diferencia, incluye datos numéricos concretos (porcentajes, promedios) y explica su importancia pedagógica.

### 3. Tendencias y evolución
Si las encuestas son del mismo tipo pero de diferentes periodos, analiza en detalle las tendencias de mejora o deterioro. Menciona qué preguntas mejoraron, cuáles empeoraron y en qué magnitud.

### 4. Áreas de mejora detectadas
Para cada área identificada, describe: (a) el problema o brecha detectada con datos, (b) el impacto en la experiencia educativa, y (c) por qué es prioritario abordarlo.

### 5. Recomendaciones pedagógicas
Proporciona entre 4 y 6 recomendaciones detalladas. Cada recomendación debe incluir:
- **Qué hacer**: La acción concreta.
- **Por qué**: Justificación basada en los datos comparados.
- **Cómo implementarlo**: Una sugerencia práctica de implementación.

Escribe siempre en español. Sé detallado, constructivo y orientado a la acción.
PROMPT;
    }
}

