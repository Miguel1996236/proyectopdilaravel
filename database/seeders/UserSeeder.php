<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear administrador
        User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Administrador Principal',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'email_verified_at' => now(),
            ]
        );

        // Crear docentes
        $teachers = [
            ['name' => 'Prof. María González', 'email' => 'maria.gonzalez@example.com'],
            ['name' => 'Prof. Juan Pérez', 'email' => 'juan.perez@example.com'],
            ['name' => 'Prof. Ana Martínez', 'email' => 'ana.martinez@example.com'],
            ['name' => 'Prof. Carlos Rodríguez', 'email' => 'carlos.rodriguez@example.com'],
            ['name' => 'Prof. Laura Sánchez', 'email' => 'laura.sanchez@example.com'],
        ];

        foreach ($teachers as $teacher) {
            User::firstOrCreate(
                ['email' => $teacher['email']],
                [
                    'name' => $teacher['name'],
                    'password' => Hash::make('password'),
                    'role' => User::ROLE_TEACHER,
                    'email_verified_at' => now(),
                ]
            );
        }

        // Crear estudiantes
        $students = [
            ['name' => 'Estudiante Demo', 'email' => 'estudiante@example.com'],
            ['name' => 'Pedro López', 'email' => 'pedro.lopez@example.com'],
            ['name' => 'Sofía Ramírez', 'email' => 'sofia.ramirez@example.com'],
            ['name' => 'Diego Torres', 'email' => 'diego.torres@example.com'],
            ['name' => 'Valentina Morales', 'email' => 'valentina.morales@example.com'],
            ['name' => 'Andrés Herrera', 'email' => 'andres.herrera@example.com'],
            ['name' => 'Camila Vargas', 'email' => 'camila.vargas@example.com'],
            ['name' => 'Sebastián Castro', 'email' => 'sebastian.castro@example.com'],
            ['name' => 'Isabella Ruiz', 'email' => 'isabella.ruiz@example.com'],
            ['name' => 'Mateo Jiménez', 'email' => 'mateo.jimenez@example.com'],
            ['name' => 'Lucía Fernández', 'email' => 'lucia.fernandez@example.com'],
            ['name' => 'Nicolás Díaz', 'email' => 'nicolas.diaz@example.com'],
            ['name' => 'Emma Gutiérrez', 'email' => 'emma.gutierrez@example.com'],
            ['name' => 'Santiago Moreno', 'email' => 'santiago.moreno@example.com'],
            ['name' => 'Martina Álvarez', 'email' => 'martina.alvarez@example.com'],
            ['name' => 'Daniel Vega', 'email' => 'daniel.vega@example.com'],
            ['name' => 'Mariana Rojas', 'email' => 'mariana.rojas@example.com'],
            ['name' => 'Gabriel Silva', 'email' => 'gabriel.silva@example.com'],
            ['name' => 'Renata Mendoza', 'email' => 'renata.mendoza@example.com'],
            ['name' => 'Emilio Contreras', 'email' => 'emilio.contreras@example.com'],
        ];

        foreach ($students as $student) {
            User::firstOrCreate(
                ['email' => $student['email']],
                [
                    'name' => $student['name'],
                    'password' => Hash::make('password'),
                    'role' => User::ROLE_STUDENT,
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}

