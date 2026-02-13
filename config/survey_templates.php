<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Plantillas de encuestas
    |--------------------------------------------------------------------------
    |
    | Plantillas predefinidas para que los docentes creen encuestas rápidamente
    | sin tener que pensar en las preguntas. Cada plantilla incluye título,
    | descripción y un array de preguntas con su tipo y opciones.
    |
    */

    'templates' => [
        'evaluacion_docente' => [
            'key' => 'evaluacion_docente',
            'name' => 'Evaluación de la labor docente',
            'description' => 'Encuesta para que los estudiantes evalúen la claridad, dominio del tema, actitud y metodología del docente.',
            'icon' => 'fa-chalkboard-teacher',
            'questions' => [
                [
                    'title' => '¿El docente explica los temas con claridad y de forma comprensible?',
                    'type' => 'scale',
                    'description' => '1 = Totalmente en desacuerdo, 5 = Totalmente de acuerdo',
                    'settings' => ['scale_min' => 1, 'scale_max' => 5, 'scale_step' => 1],
                ],
                [
                    'title' => '¿El docente muestra dominio del contenido de la asignatura?',
                    'type' => 'scale',
                    'settings' => ['scale_min' => 1, 'scale_max' => 5, 'scale_step' => 1],
                ],
                [
                    'title' => '¿El docente fomenta la participación activa de los estudiantes?',
                    'type' => 'scale',
                    'settings' => ['scale_min' => 1, 'scale_max' => 5, 'scale_step' => 1],
                ],
                [
                    'title' => '¿El docente resuelve dudas de manera oportuna y respetuosa?',
                    'type' => 'scale',
                    'settings' => ['scale_min' => 1, 'scale_max' => 5, 'scale_step' => 1],
                ],
                [
                    'title' => '¿El docente utiliza recursos y materiales variados para apoyar el aprendizaje?',
                    'type' => 'scale',
                    'settings' => ['scale_min' => 1, 'scale_max' => 5, 'scale_step' => 1],
                ],
                [
                    'title' => '¿El docente cumple con los horarios y el programa establecido?',
                    'type' => 'scale',
                    'settings' => ['scale_min' => 1, 'scale_max' => 5, 'scale_step' => 1],
                ],
                [
                    'title' => '¿Cómo calificarías la actitud general del docente hacia los estudiantes?',
                    'type' => 'multiple_choice',
                    'options' => [
                        ['label' => 'Excelente', 'is_correct' => false],
                        ['label' => 'Muy buena', 'is_correct' => false],
                        ['label' => 'Buena', 'is_correct' => false],
                        ['label' => 'Regular', 'is_correct' => false],
                        ['label' => 'Deficiente', 'is_correct' => false],
                    ],
                ],
                [
                    'title' => '¿Qué aspectos positivos destacarías del docente?',
                    'description' => 'Puedes elegir varios',
                    'type' => 'multi_select',
                    'options' => [
                        ['label' => 'Puntualidad', 'is_correct' => false],
                        ['label' => 'Claridad en las explicaciones', 'is_correct' => false],
                        ['label' => 'Empatía con los estudiantes', 'is_correct' => false],
                        ['label' => 'Dominio del tema', 'is_correct' => false],
                        ['label' => 'Uso de tecnología', 'is_correct' => false],
                        ['label' => 'Retroalimentación oportuna', 'is_correct' => false],
                    ],
                ],
                [
                    'title' => 'Del 1 al 10, ¿qué calificación general le darías al docente?',
                    'type' => 'numeric',
                ],
                [
                    'title' => '¿Qué sugerencias le darías al docente para mejorar su práctica?',
                    'type' => 'open_text',
                ],
                [
                    'title' => '¿Recomendarías tomar una materia con este docente?',
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
                    'title' => 'Comparte cualquier comentario adicional sobre tu experiencia con el docente:',
                    'type' => 'open_text',
                ],
            ],
        ],

        'satisfaccion_curso' => [
            'key' => 'satisfaccion_curso',
            'name' => 'Satisfacción del curso',
            'description' => 'Evalúa el contenido, la estructura y la organización de la asignatura.',
            'icon' => 'fa-book-open',
            'questions' => [
                [
                    'title' => '¿El contenido del curso cumplió con tus expectativas?',
                    'type' => 'scale',
                    'settings' => ['scale_min' => 1, 'scale_max' => 5, 'scale_step' => 1],
                ],
                [
                    'title' => '¿La secuencia de los temas fue lógica y coherente?',
                    'type' => 'scale',
                    'settings' => ['scale_min' => 1, 'scale_max' => 5, 'scale_step' => 1],
                ],
                [
                    'title' => '¿El nivel de dificultad del curso fue apropiado?',
                    'type' => 'multiple_choice',
                    'options' => [
                        ['label' => 'Muy fácil', 'is_correct' => false],
                        ['label' => 'Adecuado', 'is_correct' => false],
                        ['label' => 'Un poco difícil', 'is_correct' => false],
                        ['label' => 'Muy difícil', 'is_correct' => false],
                    ],
                ],
                [
                    'title' => '¿Qué unidades o temas te resultaron más útiles?',
                    'description' => 'Puedes seleccionar varios',
                    'type' => 'multi_select',
                    'options' => [
                        ['label' => 'Introducción y conceptos básicos', 'is_correct' => false],
                        ['label' => 'Desarrollo de contenidos', 'is_correct' => false],
                        ['label' => 'Ejercicios prácticos', 'is_correct' => false],
                        ['label' => 'Casos de estudio', 'is_correct' => false],
                        ['label' => 'Evaluaciones y retroalimentación', 'is_correct' => false],
                    ],
                ],
                [
                    'title' => '¿Los materiales de estudio fueron suficientes y de calidad?',
                    'type' => 'scale',
                    'settings' => ['scale_min' => 1, 'scale_max' => 5, 'scale_step' => 1],
                ],
                [
                    'title' => '¿Las evaluaciones reflejaron adecuadamente lo aprendido?',
                    'type' => 'scale',
                    'settings' => ['scale_min' => 1, 'scale_max' => 5, 'scale_step' => 1],
                ],
                [
                    'title' => '¿Qué sugerencias tienes para mejorar el curso?',
                    'type' => 'open_text',
                ],
            ],
        ],

        'fin_de_semestre' => [
            'key' => 'fin_de_semestre',
            'name' => 'Evaluación de fin de semestre',
            'description' => 'Encuesta integral que cubre docente, curso, recursos y experiencia general.',
            'icon' => 'fa-graduation-cap',
            'questions' => [
                [
                    'title' => 'Calificación general del curso (1-10):',
                    'type' => 'numeric',
                ],
                [
                    'title' => '¿El docente explicó los temas con claridad?',
                    'type' => 'scale',
                    'settings' => ['scale_min' => 1, 'scale_max' => 5, 'scale_step' => 1],
                ],
                [
                    'title' => '¿Los materiales y recursos fueron útiles para tu aprendizaje?',
                    'type' => 'scale',
                    'settings' => ['scale_min' => 1, 'scale_max' => 5, 'scale_step' => 1],
                ],
                [
                    'title' => '¿Te sentiste cómodo participando en clase?',
                    'type' => 'scale',
                    'settings' => ['scale_min' => 1, 'scale_max' => 5, 'scale_step' => 1],
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
                    'title' => '¿Qué fue lo mejor del curso?',
                    'type' => 'open_text',
                ],
                [
                    'title' => '¿Qué mejorarías del curso?',
                    'type' => 'open_text',
                ],
                [
                    'title' => 'Comentarios finales sobre tu experiencia:',
                    'type' => 'open_text',
                ],
            ],
        ],

        'clima_aula' => [
            'key' => 'clima_aula',
            'name' => 'Clima del aula y ambiente de aprendizaje',
            'description' => 'Evalúa el ambiente, el respeto y la inclusión en el espacio de clase.',
            'icon' => 'fa-users',
            'questions' => [
                [
                    'title' => '¿Te sentiste respetado por tus compañeros y el docente?',
                    'type' => 'scale',
                    'settings' => ['scale_min' => 1, 'scale_max' => 5, 'scale_step' => 1],
                ],
                [
                    'title' => '¿El ambiente en el aula favoreció el aprendizaje?',
                    'type' => 'scale',
                    'settings' => ['scale_min' => 1, 'scale_max' => 5, 'scale_step' => 1],
                ],
                [
                    'title' => '¿Te sentiste cómodo participando y expresando tus ideas?',
                    'type' => 'scale',
                    'settings' => ['scale_min' => 1, 'scale_max' => 5, 'scale_step' => 1],
                ],
                [
                    'title' => '¿Cómo calificarías el trabajo en equipo durante el curso?',
                    'type' => 'multiple_choice',
                    'options' => [
                        ['label' => 'Excelente', 'is_correct' => false],
                        ['label' => 'Bueno', 'is_correct' => false],
                        ['label' => 'Regular', 'is_correct' => false],
                        ['label' => 'Deficiente', 'is_correct' => false],
                    ],
                ],
                [
                    'title' => '¿Hay situaciones que afectaron el ambiente de aprendizaje? Descríbelas brevemente:',
                    'type' => 'open_text',
                ],
            ],
        ],

        'recursos_didacticos' => [
            'key' => 'recursos_didacticos',
            'name' => 'Evaluación de recursos didácticos y tecnología',
            'description' => 'Evalúa las herramientas, plataformas y materiales utilizados en el curso.',
            'icon' => 'fa-laptop',
            'questions' => [
                [
                    'title' => '¿Los recursos digitales fueron accesibles y fáciles de usar?',
                    'type' => 'scale',
                    'settings' => ['scale_min' => 1, 'scale_max' => 5, 'scale_step' => 1],
                ],
                [
                    'title' => '¿La calidad de los materiales multimedia (videos, presentaciones) fue adecuada?',
                    'type' => 'scale',
                    'settings' => ['scale_min' => 1, 'scale_max' => 5, 'scale_step' => 1],
                ],
                [
                    'title' => '¿Qué recursos utilizaste durante el curso?',
                    'description' => 'Puedes seleccionar varios',
                    'type' => 'multi_select',
                    'options' => [
                        ['label' => 'Plataforma en línea / LMS', 'is_correct' => false],
                        ['label' => 'Videos educativos', 'is_correct' => false],
                        ['label' => 'Presentaciones', 'is_correct' => false],
                        ['label' => 'Material impreso', 'is_correct' => false],
                        ['label' => 'Simulaciones o software', 'is_correct' => false],
                        ['label' => 'Otros', 'is_correct' => false],
                    ],
                ],
                [
                    'title' => '¿Qué herramientas o recursos te gustaría que se implementen en futuros cursos?',
                    'type' => 'open_text',
                ],
            ],
        ],
    ],

];
