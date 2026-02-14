<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quiz_comparisons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('quiz_a_id')->constrained('quizzes')->cascadeOnDelete();
            $table->foreignId('quiz_b_id')->constrained('quizzes')->cascadeOnDelete();
            $table->longText('ai_analysis')->nullable();
            $table->json('stats_a')->nullable();
            $table->json('stats_b')->nullable();
            $table->json('insights_a')->nullable();
            $table->json('insights_b')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('analyzed_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'quiz_a_id', 'quiz_b_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quiz_comparisons');
    }
};
