<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Quiz;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

trait AuthorizesQuizAccess
{
    protected function ensureTeacherOrAdmin(): void
    {
        abort_unless(
            in_array(Auth::user()?->role, [User::ROLE_ADMIN, User::ROLE_TEACHER]),
            403
        );
    }

    protected function ensureQuizOwnership(Quiz $quiz, ?string $message = null): void
    {
        $user = Auth::user();

        abort_if(
            $user->role !== User::ROLE_ADMIN && $quiz->user_id !== $user->id,
            403,
            $message ?? __('No tienes permisos para acceder a esta encuesta.')
        );
    }
}
