<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\QuizAnswer;
use App\Models\QuizAttempt;
use App\Models\QuizInvitation;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ComparisonQuizSeeder extends Seeder
{
    /**
     * Crear encuestas de satisfacción 2025 y 2026 para comparar.
     */
    public function run(): void
    {
        $teacher = User::where('role', User::ROLE_TEACHER)->first();

        if (! $teacher) {
            $this->command->warn('No hay docentes disponibles. Ejecuta primero UserSeeder.');
            return;
        }

        $students = User::where('role', User::ROLE_STUDENT)->get();

        // Preguntas comunes para ambas encuestas
        $sharedQuestions = [
            [
                'title' => '¿Cómo calificarías la calidad general de la enseñanza?',
                'type' => 'scale',
                'options' => [
                    ['label' => '1 - Muy deficiente', 'value' => '1'],
                    ['label' => '2 - Deficiente', 'value' => '2'],
                    ['label' => '3 - Aceptable', 'value' => '3'],
                    ['label' => '4 - Buena', 'value' => '4'],
                    ['label' => '5 - Excelente', 'value' => '5'],
                ],
            ],
            [
                'title' => '¿Las clases fueron dinámicas y participativas?',
                'type' => 'multiple_choice',
                'options' => [
                    ['label' => 'Siempre', 'is_correct' => false],
                    ['label' => 'Casi siempre', 'is_correct' => false],
                    ['label' => 'A veces', 'is_correct' => false],
                    ['label' => 'Casi nunca', 'is_correct' => false],
                    ['label' => 'Nunca', 'is_correct' => false],
                ],
            ],
            [
                'title' => '¿El profesor estuvo disponible para resolver dudas?',
                'type' => 'scale',
                'options' => [
                    ['label' => '1 - Nunca', 'value' => '1'],
                    ['label' => '2', 'value' => '2'],
                    ['label' => '3', 'value' => '3'],
                    ['label' => '4', 'value' => '4'],
                    ['label' => '5 - Siempre', 'value' => '5'],
                ],
            ],
            [
                'title' => '¿Qué aspectos mejorarías del curso?',
                'type' => 'multi_select',
                'options' => [
                    ['label' => 'Material didáctico', 'is_correct' => false],
                    ['label' => 'Metodología de enseñanza', 'is_correct' => false],
                    ['label' => 'Evaluaciones', 'is_correct' => false],
                    ['label' => 'Uso de tecnología', 'is_correct' => false],
                    ['label' => 'Horarios', 'is_correct' => false],
                ],
            ],
            [
                'title' => '¿Recomendarías este curso a otros estudiantes?',
                'type' => 'multiple_choice',
                'options' => [
                    ['label' => 'Definitivamente sí', 'is_correct' => false],
                    ['label' => 'Probablemente sí', 'is_correct' => false],
                    ['label' => 'No estoy seguro', 'is_correct' => false],
                    ['label' => 'Probablemente no', 'is_correct' => false],
                    ['label' => 'Definitivamente no', 'is_correct' => false],
                ],
            ],
            [
                'title' => 'Comparte sugerencias para mejorar la experiencia educativa:',
                'type' => 'open_text',
            ],
        ];

        // --- Encuesta 2025 (resultados más bajos) ---
        $quiz2025 = Quiz::create([
            'user_id' => $teacher->id,
            'title' => 'Encuesta de Satisfacción Docente - 2025',
            'description' => 'Evaluación anual de satisfacción con la calidad de la enseñanza, primer periodo 2025.',
            'status' => 'closed',
            'opens_at' => now()->subYear()->startOfYear()->addMonths(5),
            'closes_at' => now()->subYear()->startOfYear()->addMonths(6),
            'max_attempts' => 1,
            'require_login' => true,
            'target_audience' => 'students',
        ]);

        $this->createQuestionsAndAnswers($quiz2025, $sharedQuestions, $students, $teacher, [
            'scale_bias' => [2, 3, 4], // tendencia a notas medias-bajas
            'choice_bias' => [1, 2, 3], // respuestas neutras
            'open_text_responses' => [
                'Falta más material actualizado.',
                'Las clases son algo monótonas.',
                'Me gustaría más práctica y menos teoría.',
                'Los horarios no me convienen.',
                'El profesor es amable pero las clases aburren.',
                'Necesitamos más recursos digitales.',
                'La metodología es muy tradicional.',
                'Debería haber más retroalimentación.',
            ],
        ]);

        // --- Encuesta 2026 (resultados mejorados) ---
        $quiz2026 = Quiz::create([
            'user_id' => $teacher->id,
            'title' => 'Encuesta de Satisfacción Docente - 2026',
            'description' => 'Evaluación anual de satisfacción con la calidad de la enseñanza, primer periodo 2026.',
            'status' => 'closed',
            'opens_at' => now()->startOfYear()->addMonths(0),
            'closes_at' => now()->startOfYear()->addMonth(),
            'max_attempts' => 1,
            'require_login' => true,
            'target_audience' => 'students',
        ]);

        $this->createQuestionsAndAnswers($quiz2026, $sharedQuestions, $students, $teacher, [
            'scale_bias' => [3, 4, 5], // tendencia a notas altas
            'choice_bias' => [0, 1], // respuestas más positivas
            'open_text_responses' => [
                'Excelente curso, me encantó la metodología.',
                'El profesor se esfuerza mucho y se nota.',
                'Las clases son muy dinámicas e interesantes.',
                'Los materiales en línea son de gran ayuda.',
                'Recomiendo este curso totalmente.',
                'Me gustó la combinación de teoría y práctica.',
                'Se mejoró mucho respecto al año pasado.',
                'Solo mejoraría los horarios.',
            ],
        ]);

        $this->command->info('Seeders de comparación (2025 vs 2026) creados exitosamente.');
    }

    protected function createQuestionsAndAnswers(Quiz $quiz, array $questionTemplates, $students, $teacher, array $biases): void
    {
        $questions = [];

        // Crear preguntas
        foreach ($questionTemplates as $position => $qData) {
            $question = Question::create([
                'quiz_id' => $quiz->id,
                'title' => $qData['title'],
                'description' => null,
                'type' => $qData['type'],
                'position' => $position + 1,
                'weight' => 1,
            ]);

            if (isset($qData['options']) && in_array($qData['type'], ['multiple_choice', 'multi_select', 'true_false', 'scale'])) {
                foreach ($qData['options'] as $optPos => $optData) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'label' => $optData['label'],
                        'value' => $optData['value'] ?? null,
                        'is_correct' => $optData['is_correct'] ?? false,
                        'position' => $optPos + 1,
                    ]);
                }
            }

            $question->load('options');
            $questions[] = $question;
        }

        // Crear invitación
        $invitation = QuizInvitation::create([
            'quiz_id' => $quiz->id,
            'created_by' => $teacher->id,
            'code' => Str::upper(Str::random(8)),
            'label' => 'Código principal',
            'is_active' => true,
        ]);

        // Crear intentos y respuestas
        $respondents = $students->random(min($students->count(), rand(8, 15)));

        foreach ($respondents as $student) {
            $attempt = QuizAttempt::create([
                'quiz_id' => $quiz->id,
                'invitation_id' => $invitation->id,
                'user_id' => $student->id,
                'status' => 'completed',
                'started_at' => $quiz->opens_at->addDays(rand(1, 25)),
                'completed_at' => $quiz->opens_at->addDays(rand(1, 25))->addMinutes(rand(3, 15)),
            ]);

            foreach ($questions as $question) {
                switch ($question->type) {
                    case 'scale':
                        $value = $biases['scale_bias'][array_rand($biases['scale_bias'])];
                        QuizAnswer::create([
                            'attempt_id' => $attempt->id,
                            'question_id' => $question->id,
                            'answer_number' => $value,
                        ]);
                        break;

                    case 'multiple_choice':
                    case 'true_false':
                        $biasedIndex = $biases['choice_bias'][array_rand($biases['choice_bias'])];
                        $option = $question->options[$biasedIndex] ?? $question->options->random();
                        QuizAnswer::create([
                            'attempt_id' => $attempt->id,
                            'question_id' => $question->id,
                            'question_option_id' => $option->id,
                            'answer_text' => $option->label,
                        ]);
                        break;

                    case 'multi_select':
                        $count = rand(1, 3);
                        $selected = $question->options->random($count);
                        foreach ($selected as $opt) {
                            QuizAnswer::create([
                                'attempt_id' => $attempt->id,
                                'question_id' => $question->id,
                                'question_option_id' => $opt->id,
                                'answer_text' => $opt->label,
                            ]);
                        }
                        break;

                    case 'open_text':
                        QuizAnswer::create([
                            'attempt_id' => $attempt->id,
                            'question_id' => $question->id,
                            'answer_text' => $biases['open_text_responses'][array_rand($biases['open_text_responses'])],
                        ]);
                        break;
                }
            }

            $invitation->increment('uses_count');
        }
    }
}

