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
use Illuminate\Support\Carbon;

class QuizAttemptSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $quizzes = Quiz::whereIn('status', ['published', 'closed'])->get();
        $students = User::where('role', User::ROLE_STUDENT)->get();

        if ($students->isEmpty()) {
            $this->command->warn('No hay estudiantes disponibles. Ejecuta primero UserSeeder.');
            return;
        }

        foreach ($quizzes as $quiz) {
            // Obtener invitaciones del cuestionario
            $invitations = $quiz->invitations;

            if ($invitations->isEmpty()) {
                continue;
            }

            // Determinar cuántos intentos crear (60-90% de los estudiantes)
            $attemptCount = (int) ($students->count() * (rand(60, 90) / 100));
            $selectedStudents = $students->random(min($attemptCount, $students->count()));

            foreach ($selectedStudents as $student) {
                $invitation = $invitations->random();
                
                // Fechas aleatorias dentro del rango del cuestionario
                $startDate = $quiz->opens_at 
                    ? Carbon::parse($quiz->opens_at)->addDays(rand(0, 5))
                    : now()->subDays(rand(1, 10));
                
                $endDate = $startDate->copy()->addMinutes(rand(10, 60));

                $attempt = QuizAttempt::create([
                    'quiz_id' => $quiz->id,
                    'invitation_id' => $invitation->id,
                    'user_id' => $student->id,
                    'participant_name' => $student->name,
                    'participant_email' => $student->email,
                    'status' => 'completed',
                    'score' => null,
                    'max_score' => null,
                    'started_at' => $startDate,
                    'completed_at' => $endDate,
                ]);

                // Incrementar contador de usos de la invitación
                $invitation->increment('uses_count');

                // Crear respuestas para cada pregunta
                $this->createAnswersForAttempt($attempt, $quiz);
            }
        }
    }

    protected function createAnswersForAttempt(QuizAttempt $attempt, Quiz $quiz): void
    {
        $questions = $quiz->questions()->orderBy('position')->get();

        foreach ($questions as $question) {
            $answer = null;

            switch ($question->type) {
                case 'multiple_choice':
                    $answer = $this->createMultipleChoiceAnswer($attempt, $question);
                    break;

                case 'multi_select':
                    $answer = $this->createMultiSelectAnswer($attempt, $question);
                    break;

                case 'scale':
                    $answer = $this->createScaleAnswer($attempt, $question);
                    break;

                case 'numeric':
                    $answer = $this->createNumericAnswer($attempt, $question);
                    break;

                case 'open_text':
                    $answer = $this->createOpenTextAnswer($attempt, $question);
                    break;
            }

            if ($answer) {
                QuizAnswer::create($answer);
            }
        }
    }

    protected function createMultipleChoiceAnswer(QuizAttempt $attempt, Question $question): array
    {
        $options = $question->options;
        if ($options->isEmpty()) {
            return [];
        }

        $selectedOption = $options->random();

        return [
            'attempt_id' => $attempt->id,
            'question_id' => $question->id,
            'question_option_id' => $selectedOption->id,
            'answer_text' => $selectedOption->label,
            'answer_number' => null,
            'is_correct' => $selectedOption->is_correct,
        ];
    }

    protected function createMultiSelectAnswer(QuizAttempt $attempt, Question $question): array
    {
        $options = $question->options;
        if ($options->isEmpty()) {
            return [];
        }

        // Seleccionar 1-3 opciones aleatoriamente
        $selectedOptions = $options->random(min(rand(1, 3), $options->count()));
        $labels = $selectedOptions->pluck('label')->toArray();

        // Para multi_select, guardamos la primera opción como referencia
        // y el resto en answer_meta
        $firstOption = $selectedOptions->first();

        return [
            'attempt_id' => $attempt->id,
            'question_id' => $question->id,
            'question_option_id' => $firstOption->id,
            'answer_text' => implode(', ', $labels),
            'answer_number' => null,
            'is_correct' => null,
            'answer_meta' => [
                'selected_options' => $selectedOptions->pluck('id')->toArray(),
                'labels' => $labels,
            ],
        ];
    }

    protected function createScaleAnswer(QuizAttempt $attempt, Question $question): array
    {
        $options = $question->options;
        if ($options->isEmpty()) {
            return [];
        }

        // Seleccionar un valor de la escala (tendencia hacia el centro)
        $values = $options->pluck('value')->map(fn($v) => (int)$v)->toArray();
        $selectedValue = $values[rand(0, count($values) - 1)];
        
        // Sesgo hacia valores medios (3-4 en escala 1-5)
        if (count($values) === 5 && rand(1, 3) === 1) {
            $selectedValue = rand(3, 4);
        }

        $selectedOption = $options->firstWhere('value', (string)$selectedValue) 
            ?? $options->random();

        return [
            'attempt_id' => $attempt->id,
            'question_id' => $question->id,
            'question_option_id' => $selectedOption->id,
            'answer_text' => $selectedOption->label,
            'answer_number' => (float) $selectedValue,
            'is_correct' => null,
        ];
    }

    protected function createNumericAnswer(QuizAttempt $attempt, Question $question): array
    {
        // Generar un número aleatorio razonable (ej: horas de estudio 0-20)
        $numericValue = (float) rand(0, 20);

        return [
            'attempt_id' => $attempt->id,
            'question_id' => $question->id,
            'question_option_id' => null,
            'answer_text' => (string) $numericValue,
            'answer_number' => $numericValue,
            'is_correct' => null,
        ];
    }

    protected function createOpenTextAnswer(QuizAttempt $attempt, Question $question): array
    {
        $sampleTexts = [
            'El curso fue muy completo y los materiales fueron útiles.',
            'Me gustaría que hubiera más ejercicios prácticos.',
            'Las explicaciones fueron claras y el profesor muy accesible.',
            'Considero que el ritmo del curso fue adecuado.',
            'Sugeriría incluir más ejemplos reales en las clases.',
            'El contenido fue interesante pero algunas partes fueron difíciles.',
            'Me ayudó mucho a entender los conceptos fundamentales.',
            'La retroalimentación fue muy valiosa para mi aprendizaje.',
            'Algunos temas necesitarían más tiempo de explicación.',
            'En general, estoy satisfecho con el curso.',
        ];

        return [
            'attempt_id' => $attempt->id,
            'question_id' => $question->id,
            'question_option_id' => null,
            'answer_text' => $sampleTexts[array_rand($sampleTexts)],
            'answer_number' => null,
            'is_correct' => null,
        ];
    }
}

