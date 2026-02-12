<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // docente que envía
            $table->foreignId('quiz_id')->nullable()->constrained('quizzes')->nullOnDelete();
            $table->string('subject');
            $table->text('message');
            $table->unsignedInteger('recipients_count')->default(0);
            $table->timestamp('sent_at')->nullable();
            $table->string('status')->default('sent'); // sent, failed
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_reminders');
    }
};

