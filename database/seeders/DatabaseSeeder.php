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
        $this->call(AdminUserSeeder::class);

        User::factory()->create([
            'name' => 'Docente Demo',
            'email' => 'docente@example.com',
            'role' => User::ROLE_TEACHER,
        ]);

        User::factory()->create([
            'name' => 'Estudiante Demo',
            'email' => 'estudiante@example.com',
            'role' => User::ROLE_STUDENT,
        ]);
    }
}
