<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesQuizAccess;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Rap2hpoutre\FastExcel\FastExcel;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    use AuthorizesQuizAccess;

    /**
     * Exportar reporte general de encuestas a Excel
     */
    public function exportSurveys(Request $request): StreamedResponse
    {
        $this->ensureTeacherOrAdmin();
        $user = Auth::user();

        $quizQuery = $user->role === User::ROLE_ADMIN
            ? Quiz::query()
            : Quiz::where('user_id', $user->id);

        $quizzes = $quizQuery
            ->withCount(['questions', 'attempts', 'invitations'])
            ->with('owner')
            ->get();

        $collection = $quizzes->map(fn (Quiz $q) => [
            __('ID') => $q->id,
            __('Título') => $q->title,
            __('Descripción') => $q->description,
            __('Estado') => match ($q->status) {
                'draft' => __('Borrador'),
                'published' => __('Publicada'),
                'closed' => __('Cerrada'),
                default => $q->status,
            },
            __('Docente') => $q->owner?->name ?? '-',
            __('Preguntas') => $q->questions_count,
            __('Intentos') => $q->attempts_count,
            __('Invitaciones') => $q->invitations_count,
            __('Abre') => $q->opens_at?->format('d/m/Y H:i') ?? '-',
            __('Cierra') => $q->closes_at?->format('d/m/Y H:i') ?? '-',
            __('Creada') => $q->created_at?->format('d/m/Y H:i'),
        ]);

        return (new FastExcel($collection))->download('reporte-encuestas-' . now()->format('Ymd') . '.xlsx');
    }

    /**
     * Exportar reporte de estudiantes a Excel
     */
    public function exportStudents(Request $request): StreamedResponse
    {
        abort_unless(Auth::user()?->role === User::ROLE_ADMIN, 403);

        $students = User::where('role', User::ROLE_STUDENT)
            ->withCount([
                'quizAttempts as completed_attempts_count' => fn ($q) => $q->where('status', 'completed'),
                'quizAttempts as total_attempts_count',
            ])
            ->get();

        $collection = $students->map(fn (User $s) => [
            __('ID') => $s->id,
            __('Nombre') => $s->name,
            __('Correo electrónico') => $s->email,
            __('Intentos totales') => $s->total_attempts_count,
            __('Completados') => $s->completed_attempts_count,
            __('Registrado') => $s->created_at?->format('d/m/Y H:i'),
        ]);

        return (new FastExcel($collection))->download('reporte-estudiantes-' . now()->format('Ymd') . '.xlsx');
    }

    /**
     * Exportar respuestas de una encuesta a Excel
     */
    public function exportQuizResponses(Quiz $quiz): StreamedResponse
    {
        $this->ensureTeacherOrAdmin();
        $user = Auth::user();

        abort_if(
            $user->role !== User::ROLE_ADMIN && $quiz->user_id !== $user->id,
            403
        );

        $quiz->load(['questions', 'attempts.answers.question', 'attempts.user']);

        $rows = collect();

        foreach ($quiz->attempts->where('status', 'completed') as $attempt) {
            $row = [
                __('Participante') => $attempt->user?->name ?? $attempt->participant_name ?? __('Anónimo'),
                __('Email') => $attempt->user?->email ?? $attempt->participant_email ?? '-',
                __('Fecha') => $attempt->completed_at?->format('d/m/Y H:i') ?? '-',
            ];

            foreach ($quiz->questions as $question) {
                $answers = $attempt->answers->where('question_id', $question->id);
                $answerText = $answers->map(function ($a) {
                    return $a->answer_text ?? $a->answer_number ?? '-';
                })->join(', ');
                $row[$question->title] = $answerText ?: '-';
            }

            $rows->push($row);
        }

        if ($rows->isEmpty()) {
            $rows->push([__('Sin respuestas') => __('No hay respuestas para exportar.')]);
        }

        return (new FastExcel($rows))->download(
            'respuestas-' . \Illuminate\Support\Str::slug($quiz->title) . '-' . now()->format('Ymd') . '.xlsx'
        );
    }
}

