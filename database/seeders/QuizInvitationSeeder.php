<?php

namespace Database\Seeders;

use App\Models\Quiz;
use App\Models\QuizInvitation;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class QuizInvitationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $quizzes = Quiz::whereIn('status', ['published', 'closed'])->get();

        foreach ($quizzes as $quiz) {
            // Crear 1-3 invitaciones por cuestionario
            $invitationCount = rand(1, 3);

            for ($i = 0; $i < $invitationCount; $i++) {
                QuizInvitation::create([
                    'quiz_id' => $quiz->id,
                    'created_by' => $quiz->user_id,
                    'code' => $this->generateUniqueCode(),
                    'label' => $this->generateLabel($i),
                    'max_uses' => rand(1, 3) === 1 ? rand(10, 50) : null,
                    'uses_count' => 0,
                    'expires_at' => $quiz->closes_at ? $quiz->closes_at->copy() : now()->addDays(30),
                    'is_active' => true,
                ]);
            }
        }
    }

    protected function generateUniqueCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (QuizInvitation::where('code', $code)->exists());

        return $code;
    }

    protected function generateLabel(int $index): string
    {
        $labels = [
            'Código Principal',
            'Código para Estudiantes',
            'Código Alternativo',
            'Invitación General',
            'Acceso Rápido',
        ];

        return $labels[$index] ?? 'Código ' . ($index + 1);
    }
}

