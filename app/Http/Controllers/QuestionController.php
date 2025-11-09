<?php

namespace App\Http\Controllers;

use App\Http\Requests\QuestionRequest;
use App\Models\Question;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class QuestionController extends Controller
{
    public function create(Quiz $quiz): View
    {
        $this->ensureOwnership($quiz);

        $question = new Question([
            'type' => 'multiple_choice',
            'weight' => 1,
        ]);
        $question->setRelation('options', collect());

        return view('questions.create', [
            'quiz' => $quiz,
            'question' => $question,
        ]);
    }

    public function store(QuestionRequest $request, Quiz $quiz): RedirectResponse
    {
        $this->ensureOwnership($quiz);

        $data = $request->validated();

        $nextPosition = ($quiz->questions()->max('position') ?? 0) + 1;

        $question = $quiz->questions()->create([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'weight' => $data['weight'] ?? 1,
            'position' => $nextPosition,
            'settings' => $this->extractSettings($data),
        ]);

        $this->syncOptions($question, $data);

        return redirect()
            ->route('quizzes.edit', $quiz)
            ->with('status', __('Pregunta creada correctamente.'));
    }

    public function edit(Quiz $quiz, Question $question): View
    {
        $this->ensureQuestionOwnership($quiz, $question);

        $question->load('options');

        return view('questions.edit', [
            'quiz' => $quiz,
            'question' => $question,
        ]);
    }

    public function update(QuestionRequest $request, Quiz $quiz, Question $question): RedirectResponse
    {
        $this->ensureQuestionOwnership($quiz, $question);

        $data = $request->validated();

        $question->update([
            'title' => $data['title'],
            'description' => $data['description'] ?? null,
            'type' => $data['type'],
            'weight' => $data['weight'] ?? 1,
            'settings' => $this->extractSettings($data),
        ]);

        $this->syncOptions($question, $data, true);

        return redirect()
            ->route('quizzes.edit', $quiz)
            ->with('status', __('Pregunta actualizada correctamente.'));
    }

    public function destroy(Quiz $quiz, Question $question): RedirectResponse
    {
        $this->ensureQuestionOwnership($quiz, $question);

        $question->delete();
        $this->reorderQuestionPositions($quiz);

        return redirect()
            ->route('quizzes.edit', $quiz)
            ->with('status', __('Pregunta eliminada correctamente.'));
    }

    protected function extractSettings(array $data): ?array
    {
        $settings = $data['settings'] ?? [];

        if (($data['type'] ?? null) !== 'scale') {
            unset($settings['scale_min'], $settings['scale_max'], $settings['scale_step']);
        }

        return empty(array_filter($settings, fn ($value) => $value !== null && $value !== ''))
            ? null
            : $settings;
    }

    protected function syncOptions(Question $question, array $data, bool $refresh = false): void
    {
        $type = $data['type'] ?? $question->type;
        $requiresOptions = in_array($type, ['multiple_choice', 'multi_select'], true);

        if ($refresh) {
            $question->options()->delete();
        }

        if (!$requiresOptions) {
            return;
        }

        $options = $data['options'] ?? [];

        foreach ($options as $index => $option) {
            $question->options()->create([
                'label' => $option['label'],
                'value' => $option['value'] ?? null,
                'is_correct' => $option['is_correct'] ?? false,
                'position' => $index + 1,
            ]);
        }
    }

    protected function reorderQuestionPositions(Quiz $quiz): void
    {
        $quiz->questions()
            ->orderBy('position')
            ->get()
            ->each(function (Question $question, int $index) {
                $question->updateQuietly(['position' => $index + 1]);
            });
    }

    protected function ensureOwnership(Quiz $quiz): void
    {
        $user = Auth::user();

        abort_if(
            $user->role !== User::ROLE_ADMIN && $quiz->user_id !== $user->id,
            403,
            __('No tienes permisos para gestionar encuestas de otros usuarios.')
        );
    }

    protected function ensureQuestionOwnership(Quiz $quiz, Question $question): void
    {
        abort_if(
            $question->quiz_id !== $quiz->id,
            404,
            __('No se encontró la pregunta solicitada en esta encuesta.')
        );

        $this->ensureOwnership($quiz);
    }
}
