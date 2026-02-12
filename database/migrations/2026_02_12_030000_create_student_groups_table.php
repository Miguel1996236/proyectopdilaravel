<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // docente dueño
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('student_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_group_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete(); // vínculo opcional con user registrado
            $table->timestamps();

            $table->unique(['student_group_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_group_members');
        Schema::dropIfExists('student_groups');
    }
};

