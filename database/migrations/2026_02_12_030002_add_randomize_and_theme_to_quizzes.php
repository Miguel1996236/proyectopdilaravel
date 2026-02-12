<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->boolean('randomize_questions')->default(false)->after('target_audience');
            $table->string('theme_color', 7)->default('#4e73df')->after('randomize_questions');
        });
    }

    public function down(): void
    {
        Schema::table('quizzes', function (Blueprint $table) {
            $table->dropColumn(['randomize_questions', 'theme_color']);
        });
    }
};

