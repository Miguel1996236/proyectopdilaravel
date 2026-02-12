<?php

namespace Database\Seeders;

use App\Models\StudentGroup;
use App\Models\User;
use Illuminate\Database\Seeder;

class StudentGroupSeeder extends Seeder
{
    public function run(): void
    {
        $teacher = User::where('role', User::ROLE_TEACHER)->first();

        if (! $teacher) {
            $this->command->warn('No hay docentes disponibles.');
            return;
        }

        $students = User::where('role', User::ROLE_STUDENT)->get();

        // Grupo 1
        $group1 = StudentGroup::create([
            'user_id' => $teacher->id,
            'name' => 'Sección A - Matemáticas 2026',
            'description' => 'Estudiantes de la sección A del curso de Matemáticas, primer periodo 2026.',
        ]);

        foreach ($students->take(10) as $student) {
            $group1->members()->create([
                'name' => $student->name,
                'email' => $student->email,
                'user_id' => $student->id,
            ]);
        }

        // Grupo 2
        $group2 = StudentGroup::create([
            'user_id' => $teacher->id,
            'name' => 'Sección B - Ciencias 2026',
            'description' => 'Estudiantes de la sección B del curso de Ciencias.',
        ]);

        foreach ($students->skip(10)->take(10) as $student) {
            $group2->members()->create([
                'name' => $student->name,
                'email' => $student->email,
                'user_id' => $student->id,
            ]);
        }

        // Grupo 3 - con correos externos
        $group3 = StudentGroup::create([
            'user_id' => $teacher->id,
            'name' => 'Estudiantes Externos',
            'description' => 'Participantes invitados que no están registrados en el sistema.',
        ]);

        $externoNames = ['María García', 'Carlos López', 'Ana Rodríguez', 'José Martínez', 'Laura Fernández'];
        foreach ($externoNames as $i => $name) {
            $group3->members()->create([
                'name' => $name,
                'email' => 'externo' . ($i + 1) . '@example.com',
                'user_id' => null,
            ]);
        }

        $this->command->info('Grupos de estudiantes de prueba creados exitosamente.');
    }
}

