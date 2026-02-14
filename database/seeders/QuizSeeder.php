<?php

namespace Database\Seeders;

use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class QuizSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $teachers = User::where('role', User::ROLE_TEACHER)->get();

        if ($teachers->isEmpty()) {
            $this->command->warn('No hay docentes disponibles. Ejecuta primero UserSeeder.');
            return;
        }

        $quizzes = [
            [
                'title' => 'Evaluación de Satisfacción del Curso de Matemáticas',
                'description' => 'Encuesta para conocer la opinión de los estudiantes sobre el curso de matemáticas y mejorar la experiencia educativa.',
                'status' => 'published',
                'opens_at' => now()->subDays(10),
                'closes_at' => now()->addDays(5),
            ],
            [
                'title' => 'Encuesta de Retroalimentación - Lengua y Literatura',
                'description' => 'Ayúdanos a mejorar el curso compartiendo tu experiencia y sugerencias.',
                'status' => 'published',
                'opens_at' => now()->subDays(5),
                'closes_at' => now()->addDays(10),
            ],
            [
                'title' => 'Evaluación de Metodología de Enseñanza',
                'description' => 'Queremos conocer tu opinión sobre las metodologías utilizadas en clase.',
                'status' => 'closed',
                'opens_at' => now()->subDays(30),
                'closes_at' => now()->subDays(5),
            ],
            [
                'title' => 'Encuesta de Recursos Didácticos',
                'description' => 'Evalúa los recursos y materiales utilizados durante el semestre.',
                'status' => 'published',
                'opens_at' => now()->subDays(3),
                'closes_at' => now()->addDays(15),
            ],
            [
                'title' => 'Satisfacción con el Ambiente de Aprendizaje',
                'description' => 'Tu opinión es importante para mejorar el ambiente de aprendizaje.',
                'status' => 'draft',
                'opens_at' => null,
                'closes_at' => null,
            ],
            [
                'title' => 'Evaluación Final del Semestre',
                'description' => 'Encuesta completa para evaluar todos los aspectos del curso.',
                'status' => 'closed',
                'opens_at' => now()->subDays(60),
                'closes_at' => now()->subDays(20),
            ],
        ];

        foreach ($quizzes as $index => $quizData) {
            $teacher = $teachers->random();
            
            $quiz = Quiz::create([
                'user_id' => $teacher->id,
                'title' => $quizData['title'],
                'description' => $quizData['description'],
                'status' => $quizData['status'],
                'opens_at' => $quizData['opens_at'],
                'closes_at' => $quizData['closes_at'],
                'max_attempts' => 1,
                'require_login' => true,
            ]);

            // Crear preguntas según el tipo de cuestionario
            $this->createQuestionsForQuiz($quiz, $index);
        }
    }

    protected function createQuestionsForQuiz(Quiz $quiz, int $quizIndex): void
    {
        $questionTemplates = [
            // Cuestionario 0: Satisfacción Matemáticas
            [
                [
                    'title' => '¿Cómo calificarías la claridad de las explicaciones del profesor?',
                    'type' => 'multiple_choice',
                    'options' => [
                        ['label' => 'Excelente', 'is_correct' => false],
                        ['label' => 'Muy buena', 'is_correct' => false],
                        ['label' => 'Buena', 'is_correct' => false],
                        ['label' => 'Regular', 'is_correct' => false],
                        ['label' => 'Necesita mejorar', 'is_correct' => false],
                    ],
                ],
                [
                    'title' => '¿Qué tan útil consideras el material de estudio proporcionado?',
                    'type' => 'scale',
                    'options' => [
                        ['label' => '1 - Nada útil', 'value' => '1'],
                        ['label' => '2', 'value' => '2'],
                        ['label' => '3', 'value' => '3'],
                        ['label' => '4', 'value' => '4'],
                        ['label' => '5 - Muy útil', 'value' => '5'],
                    ],
                ],
                [
                    'title' => '¿Qué temas te resultaron más difíciles? (Puedes seleccionar varios)',
                    'type' => 'multi_select',
                    'options' => [
                        ['label' => 'Álgebra', 'is_correct' => false],
                        ['label' => 'Geometría', 'is_correct' => false],
                        ['label' => 'Cálculo', 'is_correct' => false],
                        ['label' => 'Estadística', 'is_correct' => false],
                        ['label' => 'Trigonometría', 'is_correct' => false],
                    ],
                ],
                [
                    'title' => 'La derivada de x² es 2x. ¿Verdadero o Falso?',
                    'type' => 'true_false',
                    'options' => [
                        ['label' => 'Verdadero', 'value' => 'true', 'is_correct' => true],
                        ['label' => 'Falso', 'value' => 'false', 'is_correct' => false],
                    ],
                ],
                [
                    'title' => '¿Te gustaría que el curso incluya más ejercicios prácticos? (Verdadero = Sí, Falso = No)',
                    'type' => 'true_false',
                    'options' => [
                        ['label' => 'Verdadero', 'value' => 'true', 'is_correct' => false],
                        ['label' => 'Falso', 'value' => 'false', 'is_correct' => false],
                    ],
                ],
                [
                    'title' => '¿Cuántas horas semanales dedicas al estudio de matemáticas?',
                    'type' => 'numeric',
                ],
                [
                    'title' => 'Comparte tus sugerencias para mejorar el curso:',
                    'type' => 'open_text',
                ],
            ],
            // Cuestionario 1: Lengua y Literatura
            [
                [
                    'title' => '¿El contenido del curso cumplió con tus expectativas?',
                    'type' => 'multiple_choice',
                    'options' => [
                        ['label' => 'Sí, completamente', 'is_correct' => false],
                        ['label' => 'En su mayoría', 'is_correct' => false],
                        ['label' => 'Parcialmente', 'is_correct' => false],
                        ['label' => 'No cumplió', 'is_correct' => false],
                    ],
                ],
                [
                    'title' => 'Califica la calidad de los textos y lecturas asignadas:',
                    'type' => 'scale',
                    'options' => [
                        ['label' => '1 - Muy baja', 'value' => '1'],
                        ['label' => '2', 'value' => '2'],
                        ['label' => '3', 'value' => '3'],
                        ['label' => '4', 'value' => '4'],
                        ['label' => '5 - Excelente', 'value' => '5'],
                    ],
                ],
                [
                    'title' => '¿Qué aspectos del curso te gustaron más?',
                    'type' => 'multi_select',
                    'options' => [
                        ['label' => 'Las discusiones en clase', 'is_correct' => false],
                        ['label' => 'Los trabajos escritos', 'is_correct' => false],
                        ['label' => 'Las presentaciones', 'is_correct' => false],
                        ['label' => 'La retroalimentación del profesor', 'is_correct' => false],
                    ],
                ],
                [
                    'title' => 'Comentarios adicionales:',
                    'type' => 'open_text',
                ],
            ],
            // Cuestionario 2: Metodología
            [
                [
                    'title' => '¿Qué metodología de enseñanza prefieres?',
                    'type' => 'multiple_choice',
                    'options' => [
                        ['label' => 'Clase magistral', 'is_correct' => false],
                        ['label' => 'Aprendizaje colaborativo', 'is_correct' => false],
                        ['label' => 'Proyectos prácticos', 'is_correct' => false],
                        ['label' => 'Combinación de métodos', 'is_correct' => false],
                    ],
                ],
                [
                    'title' => 'Evalúa la efectividad de las actividades prácticas:',
                    'type' => 'scale',
                    'options' => [
                        ['label' => '1', 'value' => '1'],
                        ['label' => '2', 'value' => '2'],
                        ['label' => '3', 'value' => '3'],
                        ['label' => '4', 'value' => '4'],
                        ['label' => '5', 'value' => '5'],
                    ],
                ],
                [
                    'title' => '¿Qué mejoras sugerirías en la metodología?',
                    'type' => 'open_text',
                ],
            ],
            // Cuestionario 3: Recursos Didácticos
            [
                [
                    'title' => '¿Los recursos digitales fueron accesibles y útiles?',
                    'type' => 'multiple_choice',
                    'options' => [
                        ['label' => 'Sí, muy útiles', 'is_correct' => false],
                        ['label' => 'Útiles', 'is_correct' => false],
                        ['label' => 'Poco útiles', 'is_correct' => false],
                        ['label' => 'No los utilicé', 'is_correct' => false],
                    ],
                ],
                [
                    'title' => 'Califica la calidad de los videos y materiales multimedia:',
                    'type' => 'scale',
                    'options' => [
                        ['label' => '1', 'value' => '1'],
                        ['label' => '2', 'value' => '2'],
                        ['label' => '3', 'value' => '3'],
                        ['label' => '4', 'value' => '4'],
                        ['label' => '5', 'value' => '5'],
                    ],
                ],
                [
                    'title' => '¿Qué recursos adicionales te gustaría que se incluyeran?',
                    'type' => 'open_text',
                ],
            ],
            // Cuestionario 4: Ambiente de Aprendizaje
            [
                [
                    'title' => '¿Cómo calificarías el ambiente en el aula?',
                    'type' => 'multiple_choice',
                    'options' => [
                        ['label' => 'Excelente', 'is_correct' => false],
                        ['label' => 'Bueno', 'is_correct' => false],
                        ['label' => 'Regular', 'is_correct' => false],
                        ['label' => 'Necesita mejorar', 'is_correct' => false],
                    ],
                ],
                [
                    'title' => '¿Te sentiste cómodo participando en clase?',
                    'type' => 'scale',
                    'options' => [
                        ['label' => '1 - Nada cómodo', 'value' => '1'],
                        ['label' => '2', 'value' => '2'],
                        ['label' => '3', 'value' => '3'],
                        ['label' => '4', 'value' => '4'],
                        ['label' => '5 - Muy cómodo', 'value' => '5'],
                    ],
                ],
            ],
            // Cuestionario 5: Evaluación Final
            [
                [
                    'title' => 'Calificación general del curso:',
                    'type' => 'multiple_choice',
                    'options' => [
                        ['label' => 'Excelente (9-10)', 'is_correct' => false],
                        ['label' => 'Muy bueno (7-8)', 'is_correct' => false],
                        ['label' => 'Bueno (5-6)', 'is_correct' => false],
                        ['label' => 'Regular (3-4)', 'is_correct' => false],
                        ['label' => 'Deficiente (1-2)', 'is_correct' => false],
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
                    'title' => 'Evalúa el nivel de dificultad del curso:',
                    'type' => 'scale',
                    'options' => [
                        ['label' => '1 - Muy fácil', 'value' => '1'],
                        ['label' => '2', 'value' => '2'],
                        ['label' => '3', 'value' => '3'],
                        ['label' => '4', 'value' => '4'],
                        ['label' => '5 - Muy difícil', 'value' => '5'],
                    ],
                ],
                [
                    'title' => 'Comentarios finales sobre tu experiencia:',
                    'type' => 'open_text',
                ],
            ],
        ];

        $questions = $questionTemplates[$quizIndex] ?? $questionTemplates[0];

        foreach ($questions as $position => $questionData) {
            $question = Question::create([
                'quiz_id' => $quiz->id,
                'title' => $questionData['title'],
                'description' => null,
                'type' => $questionData['type'],
                'position' => $position + 1,
                'weight' => 1,
            ]);

            // Crear opciones si las hay
            if (isset($questionData['options']) && in_array($questionData['type'], ['multiple_choice', 'multi_select', 'true_false', 'scale'])) {
                foreach ($questionData['options'] as $optionPosition => $optionData) {
                    QuestionOption::create([
                        'question_id' => $question->id,
                        'label' => $optionData['label'],
                        'description' => null,
                        'value' => $optionData['value'] ?? null,
                        'is_correct' => $optionData['is_correct'] ?? false,
                        'position' => $optionPosition + 1,
                    ]);
                }
            }
        }
    }
}

