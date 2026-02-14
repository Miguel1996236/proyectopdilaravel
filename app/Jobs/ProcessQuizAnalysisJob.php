<?php

namespace App\Jobs;

use App\Models\Quiz;
use App\Models\QuizAiAnalysis;
use App\Services\OpenAIService;
use App\Services\QuizAnalyticsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;
use Throwable;

class ProcessQuizAnalysisJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private readonly int $quizId
    ) {
    }

    public function handle(OpenAIService $openAI, QuizAnalyticsService $analyticsService): void
    {
        /** @var Quiz|null $quiz */
        $quiz = Quiz::with(['questions.options', 'attempts.answers'])
            ->find($this->quizId);

        if (! $quiz) {
            return;
        }

        if ($quiz->status !== 'closed' || ! $quiz->analysis_requested_at) {
            return;
        }

        $analysis = $quiz->analyses()->create([
            'status' => 'processing',
            'started_at' => now(),
        ]);

        try {
            $quantitative = $analyticsService->buildQuantitativeInsights($quiz);
            $qualitative = $analyticsService->buildQualitativeInsights($quiz);
            $meta = $this->buildSurveyMeta($quiz, $quantitative);

            $promptPayload = [
                'meta' => $meta,
                'quantitative' => $quantitative,
                'qualitative_samples' => $qualitative,
            ];

            $prompt = $this->buildPrompt($promptPayload);

            $response = $openAI->chat($prompt, [
                'temperature' => 0.3,
                'max_tokens' => 2000,
            ]);

            $content = data_get($response, 'choices.0.message.content');

            if (! is_string($content) || blank($content)) {
                throw new \RuntimeException('La respuesta de OpenAI no contenía texto utilizable.');
            }

            $decoded = json_decode($content, true);

            if (! is_array($decoded)) {
                $decoded = null;
            }

            $analysis->update([
                'status' => 'completed',
                'summary' => Arr::get($decoded, 'summary') ?? $content,
                'recommendations' => Arr::get($decoded, 'recommendations'),
                'quantitative_insights' => Arr::get($decoded, 'quantitative_insights', $quantitative),
                'qualitative_themes' => Arr::get($decoded, 'qualitative_themes'),
                'raw_response' => $decoded ?? ['raw' => $content],
                'completed_at' => now(),
            ]);

            $quiz->update([
                'analysis_completed_at' => now(),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            $analysis->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'raw_response' => null,
            ]);
        }
    }

    /**
     * @param array<string, mixed> $quantitative
     * @return array<string, mixed>
     */
    protected function buildSurveyMeta(Quiz $quiz, array $quantitative): array
    {
        return [
            'quiz_id' => $quiz->id,
            'title' => $quiz->title,
            'description' => $quiz->description,
            'owner' => $quiz->owner?->name,
            'total_attempts' => $quiz->attempts->count(),
            'total_questions' => $quiz->questions->count(),
            'quantitative_questions' => collect($quantitative)->pluck('question')->all(),
            'closed_at' => optional($quiz->closes_at)->toDateTimeString(),
        ];
    }

    /**
     * @param array<string, mixed> $payload
     */
    protected function buildPrompt(array $payload): string
    {
        $instructions = <<<PROMPT
Eres un analista pedagógico experto en evaluación educativa. Tu tarea es interpretar los resultados de una encuesta educativa y generar un informe detallado y útil para el docente.

Revisa cuidadosamente los datos cuantitativos y las respuestas abiertas incluidas en el JSON más abajo.

Devuelve tu respuesta ÚNICAMENTE en formato JSON válido con la siguiente estructura:
{
  "summary": "Resumen ejecutivo de 4-6 oraciones. Incluye los hallazgos más importantes, tendencias principales y una valoración general del desempeño.",
  "quantitative_insights": [
    {
      "question": "Título exacto de la pregunta",
      "key_findings": [
        "Hallazgo detallado con datos numéricos (porcentajes, promedios)",
        "Otro hallazgo relevante con interpretación pedagógica"
      ]
    }
  ],
  "qualitative_themes": [
    {
      "theme": "Nombre del tema identificado en las respuestas abiertas",
      "evidence": ["Cita textual representativa 1", "Cita textual representativa 2"]
    }
  ],
  "recommendations": [
    "Recomendación detallada 1: Describe la acción concreta, por qué es importante según los datos, y cómo implementarla.",
    "Recomendación detallada 2: Incluye el problema detectado, la estrategia sugerida y el beneficio esperado.",
    "Recomendación detallada 3: Otra acción con justificación basada en los resultados.",
    "Recomendación detallada 4: Si aplica, sugerencia adicional.",
    "Recomendación detallada 5: Si aplica, sugerencia adicional."
  ]
}

INSTRUCCIONES IMPORTANTES:
- El "summary" debe ser un párrafo completo y fluido, no una lista.
- Los "key_findings" deben incluir datos numéricos concretos (porcentajes, promedios) extraídos de los datos.
- Los "qualitative_themes" deben agrupar respuestas abiertas por temas comunes.
- Las "recommendations" son la sección MÁS IMPORTANTE: genera entre 3 y 5 recomendaciones. Cada una debe tener 2-3 oraciones que expliquen: (a) qué se recomienda, (b) por qué, basado en los datos, y (c) cómo podría implementarse.
- Usa un tono profesional, constructivo y positivo.
- Escribe todo en español.
- Si falta información para una sección, devuelve un arreglo vacío en lugar de inventar datos.
- NO incluyas texto fuera del JSON.
PROMPT;

        return $instructions.PHP_EOL.PHP_EOL.json_encode($payload, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
