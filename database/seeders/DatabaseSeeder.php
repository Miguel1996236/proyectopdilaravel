<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Crear usuarios (administrador, docentes y estudiantes)
        $this->call(UserSeeder::class);

        // 2. Crear cuestionarios con preguntas y opciones
        $this->call(QuizSeeder::class);

        // 3. Crear invitaciones para los cuestionarios publicados/cerrados
        $this->call(QuizInvitationSeeder::class);

        // 4. Crear intentos y respuestas de cuestionarios
        $this->call(QuizAttemptSeeder::class);
    }
}
