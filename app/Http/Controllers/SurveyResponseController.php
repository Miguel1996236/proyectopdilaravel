<?php

namespace App\Http\Controllers;

use App\Models\QuizAnswer;
use App\Models\QuizInvitation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SurveyResponseController extends Controller
{
    public function showSurvey(Request $request, string $code): RedirectResponse|View
    {
        $invitation = $this->resolveInvitation($code);

        if (! $invitation) {
            return redirect()
                ->route('surveys.access.form')
                ->withErrors(['code' => __('El código ingresado no es válido o ya no está disponible.')]);
        }

        if ($invitation->quiz->require_login && ! Auth::check()) {
            session()->put('url.intended', route('surveys.respond.show', $invitation->code));

            return redirect()->route('login')
                ->with('status', __('Inicia sesión para completar la encuesta.'));
        }

        // Verificar si el usuario tiene el rol permitido para responder
        if (Auth::check()) {
            $user = Auth::user();
            $targetAudience = $invitation->quiz->target_audience ?? 'all';
            
            if ($targetAudience === 'students' && $user->role !== \App\Models\User::ROLE_STUDENT) {
                return redirect()
                    ->route('surveys.access.form')
                    ->withErrors(['code' => __('Esta encuesta está dirigida solo a estudiantes.')]);
            }
            
            if ($targetAudience === 'teachers' && $user->role !== \App\Models\User::ROLE_TEACHER) {
                return redirect()
                    ->route('surveys.access.form')
                    ->withErrors(['code' => __('Esta encuesta está dirigida solo a docentes.')]);
            }
        }

        if (! $invitation->is_valid) {
            return redirect()
                ->route('surveys.access.form')
                ->withErrors(['code' => __('El código ingresado ya fue utilizado o expiró.')]);
        }

        $invitation->loadMissing(['quiz.questions.options']);

        $quiz = $invitation->quiz;
        $questions = $quiz->questions;

        // Aleatorizar preguntas si está habilitado
        if ($quiz->randomize_questions) {
            $questions = $questions->shuffle();
        }

        return view('surveys.respond', [
            'quiz' => $quiz,
            'invitation' => $invitation,
            'questions' => $questions,
        ]);
    }

    public function submitSurvey(Request $request, string $code): RedirectResponse
    {
        $invitation = $this->resolveInvitation($code);

        if (! $invitation) {
            return redirect()
                ->route('surveys.access.form')
                ->withErrors(['code' => __('El código ingresado no es válido o ya no está disponible.')]);
        }

        $invitation->loadMissing(['quiz.questions.options']);

        if ($invitation->quiz->require_login && ! Auth::check()) {
            session()->put('url.intended', route('surveys.respond.show', $invitation->code));

            return redirect()->route('login')
                ->with('status', __('Inicia sesión para completar la encuesta.'));
        }

        // Verificar si el usuario tiene el rol permitido para responder
        if (Auth::check()) {
            $user = Auth::user();
            $targetAudience = $invitation->quiz->target_audience ?? 'all';
            
            if ($targetAudience === 'students' && $user->role !== \App\Models\User::ROLE_STUDENT) {
                return redirect()
                    ->route('surveys.access.form')
                    ->withErrors(['code' => __('Esta encuesta está dirigida solo a estudiantes.')]);
            }
            
            if ($targetAudience === 'teachers' && $user->role !== \App\Models\User::ROLE_TEACHER) {
                return redirect()
                    ->route('surveys.access.form')
                    ->withErrors(['code' => __('Esta encuesta está dirigida solo a docentes.')]);
            }
        }

        if (! $invitation->is_valid) {
            return redirect()
                ->route('surveys.access.form')
                ->withErrors(['code' => __('El código ingresado ya fue utilizado o expiró.')]);
        }

        $quiz = $invitation->quiz;
        $questions = $quiz->questions;

        $validated = $this->validateResponses($request, $questions);

        $responses = data_get($validated, 'responses', []);

        if ($quiz->max_attempts && Auth::check()) {
            $previousAttempts = $quiz->attempts()
                ->where('user_id', Auth::id())
                ->count();

            if ($previousAttempts >= $quiz->max_attempts) {
                return back()
                    ->withErrors(['general' => __('Ya completaste esta encuesta.')])
                    ->withInput();
            }
        }

        DB::transaction(function () use ($quiz, $invitation, $responses, $validated, $questions, $request): void {
            $attempt = $quiz->attempts()->create([
                'invitation_id' => $invitation->id,
                'user_id' => Auth::id(),
                'participant_name' => $validated['participant_name'] ?? null,
                'participant_email' => $validated['participant_email'] ?? null,
                'status' => 'completed',
                'started_at' => now(),
                'completed_at' => now(),
                'metadata' => [
                    'ip' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ],
            ]);

            $score = 0;
            $maxScore = 0;

            foreach ($questions as $question) {
                $answerKey = (string) $question->id;
                $value = $responses[$answerKey] ?? null;

                if (is_null($value)) {
                    continue;
                }

                switch ($question->type) {
                    case 'multiple_choice':
                        $maxScore++;
                        $optionId = (int) $value;
                        $option = $question->options->firstWhere('id', $optionId);
                        $isCorrect = $option ? (bool) $option->is_correct : false;

                        if ($isCorrect) {
                            $score++;
                        }

                        QuizAnswer::create([
                            'attempt_id' => $attempt->id,
                            'question_id' => $question->id,
                            'question_option_id' => $option?->id,
                            'answer_text' => $option?->label,
                            'is_correct' => $isCorrect,
                        ]);
                        break;

                    case 'multi_select':
                        $selectedIds = collect($value)
                            ->map(fn ($val) => (int) $val)
                            ->unique()
                            ->values();

                        foreach ($selectedIds as $optionId) {
                            $option = $question->options->firstWhere('id', $optionId);

                            QuizAnswer::create([
                                'attempt_id' => $attempt->id,
                                'question_id' => $question->id,
                                'question_option_id' => $option?->id,
                                'answer_text' => $option?->label,
                                'is_correct' => $option ? (bool) $option->is_correct : false,
                            ]);
                        }

                        $correctIds = $question->options
                            ->where('is_correct', true)
                            ->pluck('id')
                            ->sort()
                            ->values();

                        if ($correctIds->isNotEmpty()) {
                            $maxScore++;

                            $selectedSorted = $selectedIds->sort()->values();
                            if ($selectedSorted->count() > 0
                                && $selectedSorted->diff($correctIds)->isEmpty()
                                && $correctIds->diff($selectedSorted)->isEmpty()) {
                                $score++;
                            }
                        }

                        break;

                    case 'scale':
                        $numeric = (int) $value;

                        QuizAnswer::create([
                            'attempt_id' => $attempt->id,
                            'question_id' => $question->id,
                            'answer_number' => $numeric,
                        ]);
                        break;

                    case 'open_text':
                        QuizAnswer::create([
                            'attempt_id' => $attempt->id,
                            'question_id' => $question->id,
                            'answer_text' => trim((string) $value),
                        ]);
                        break;

                    case 'numeric':
                        $numeric = is_array($value) ? (float) reset($value) : (float) $value;

                        QuizAnswer::create([
                            'attempt_id' => $attempt->id,
                            'question_id' => $question->id,
                            'answer_number' => $numeric,
                        ]);
                        break;
                }
            }

            if ($maxScore > 0) {
                $attempt->update([
                    'score' => $score,
                    'max_score' => $maxScore,
                ]);
            }

            $invitation->incrementUses();
        });

        return redirect()
            ->route('surveys.access.form')
            ->with('status', __('¡Gracias por participar! Tu respuesta fue registrada.'));
    }

    protected function resolveInvitation(string $code, bool $mustBeValid = true): ?QuizInvitation
    {
        $invitation = QuizInvitation::with('quiz')
            ->whereRaw('UPPER(code) = ?', [strtoupper($code)])
            ->first();

        if (! $invitation) {
            return null;
        }

        if ($invitation->quiz->status !== 'published') {
            return null;
        }

        if ($mustBeValid && ! $invitation->is_valid) {
            return null;
        }

        return $invitation;
    }

    protected function validateResponses(Request $request, $questions): array
    {
        $rules = [
            'participant_name' => ['nullable', 'string', 'max:255'],
            'participant_email' => ['nullable', 'email', 'max:255'],
        ];

        $messages = [];

        foreach ($questions as $question) {
            $field = "responses.{$question->id}";

            switch ($question->type) {
                case 'multiple_choice':
                    $options = $question->options->pluck('id')->map(fn ($id) => (string) $id)->toArray();
                    $rules[$field] = ['required', Rule::in($options)];
                    $messages["{$field}.required"] = __('Selecciona una opción.');
                    break;

                case 'multi_select':
                    $options = $question->options->pluck('id')->map(fn ($id) => (string) $id)->toArray();
                    $rules[$field] = ['required', 'array'];
                    $rules["{$field}.*"] = [Rule::in($options)];
                    $messages["{$field}.required"] = __('Selecciona al menos una opción.');
                    break;

                case 'scale':
                    $min = data_get($question->settings, 'scale_min', 1);
                    $max = data_get($question->settings, 'scale_max', 5);
                    $rules[$field] = ['required', 'integer', "min:{$min}", "max:{$max}"];
                    break;

                case 'open_text':
                    $rules[$field] = ['required', 'string', 'max:2000'];
                    break;

                case 'numeric':
                    $rules[$field] = ['required', 'numeric'];
                    break;

                default:
                    $rules[$field] = ['nullable'];
            }
        }

        return $request->validate($rules, $messages);
    }
}


