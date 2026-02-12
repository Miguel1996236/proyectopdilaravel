<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesQuizAccess;
use App\Models\Quiz;
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

        return view('comparisons.index', compact('quizzes'));
    }

    public function compare(Request $request, QuizAnalyticsService $analyticsService): View|RedirectResponse
    {
        $this->ensureTeacherOrAdmin();

        $data = $request->validate([
            'quiz_a' => ['required', 'exists:quizzes,id'],
            'quiz_b' => ['required', 'exists:quizzes,id', 'different:quiz_a'],
        ]);

        $quizA = Quiz::with(['questions.options', 'attempts.answers', 'owner'])->findOrFail($data['quiz_a']);
        $quizB = Quiz::with(['questions.options', 'attempts.answers', 'owner'])->findOrFail($data['quiz_b']);

        // Verificar propiedad
        $user = Auth::user();
        if ($user->role !== User::ROLE_ADMIN) {
            abort_if($quizA->user_id !== $user->id || $quizB->user_id !== $user->id, 403);
        }

        // Generar insights cuantitativos para ambas
        $insightsA = $analyticsService->buildQuantitativeInsights($quizA);
        $insightsB = $analyticsService->buildQuantitativeInsights($quizB);

        // Estadísticas resumidas
        $statsA = $this->buildComparisonStats($quizA);
        $statsB = $this->buildComparisonStats($quizB);

        return view('comparisons.result', [
            'quizA' => $quizA,
            'quizB' => $quizB,
            'statsA' => $statsA,
            'statsB' => $statsB,
            'insightsA' => $insightsA,
            'insightsB' => $insightsB,
        ]);
    }

    public function analyzeWithAI(Request $request, QuizAnalyticsService $analyticsService, OpenAIService $openAI): View|RedirectResponse
    {
        $this->ensureTeacherOrAdmin();

        $data = $request->validate([
            'quiz_a' => ['required', 'exists:quizzes,id'],
            'quiz_b' => ['required', 'exists:quizzes,id', 'different:quiz_a'],
        ]);

        $quizA = Quiz::with(['questions.options', 'attempts.answers', 'owner'])->findOrFail($data['quiz_a']);
        $quizB = Quiz::with(['questions.options', 'attempts.answers', 'owner'])->findOrFail($data['quiz_b']);

        $user = Auth::user();
        if ($user->role !== User::ROLE_ADMIN) {
            abort_if($quizA->user_id !== $user->id || $quizB->user_id !== $user->id, 403);
        }

        $insightsA = $analyticsService->buildQuantitativeInsights($quizA);
        $insightsB = $analyticsService->buildQuantitativeInsights($quizB);
        $statsA = $this->buildComparisonStats($quizA);
        $statsB = $this->buildComparisonStats($quizB);

        // Construir prompt para comparación
        $prompt = $this->buildComparisonPrompt($quizA, $quizB, $insightsA, $insightsB, $statsA, $statsB);

        $aiAnalysis = null;
        $aiError = null;

        try {
            $response = $openAI->chat($prompt, [
                'temperature' => 0.3,
                'max_tokens' => 1200,
            ]);

            $aiAnalysis = data_get($response, 'choices.0.message.content');
        } catch (\Exception $e) {
            $aiError = $e->getMessage();
        }

        return view('comparisons.result', [
            'quizA' => $quizA,
            'quizB' => $quizB,
            'statsA' => $statsA,
            'statsB' => $statsB,
            'insightsA' => $insightsA,
            'insightsB' => $insightsB,
            'aiAnalysis' => $aiAnalysis,
            'aiError' => $aiError,
        ]);
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
Eres un analista educativo experto. Compara los resultados de estas dos encuestas educativas y proporciona un análisis detallado.

**IMPORTANTE**: Las encuestas deben tener relación temática para una comparación significativa. Si no tienen relación, indícalo.

## Encuesta A:
{$dataA}

## Encuesta B:
{$dataB}

Proporciona tu análisis en el siguiente formato (usa markdown):

### 1. Resumen de la comparación
Breve resumen de qué se está comparando y si tiene sentido hacerlo.

### 2. Diferencias principales
Enumera las diferencias más significativas entre ambas encuestas.

### 3. Tendencias y evolución
Si las encuestas son del mismo tipo pero de diferentes periodos, identifica tendencias de mejora o deterioro.

### 4. Áreas de mejora detectadas
Identifica en qué aspectos se mejoró y en cuáles no.

### 5. Recomendaciones pedagógicas
Proporciona 3-5 recomendaciones concretas basadas en la comparación.

Responde siempre en español.
PROMPT;
    }
}

