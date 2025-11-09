<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreQuizRequest;
use App\Http\Requests\UpdateQuizRequest;
use App\Models\Quiz;
use App\Models\QuizInvitation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class QuizController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $quizzes = Quiz::query()
            ->when(
                $user->role !== $user::ROLE_ADMIN,
                fn ($query) => $query->where('user_id', $user->id)
            )
            ->latest()
            ->withCount(['questions', 'attempts'])
            ->paginate(10)
            ->withQueryString();

        return view('quizzes.index', compact('quizzes'));
    }

    public function create(): View
    {
        $quiz = new Quiz([
            'status' => 'draft',
            'max_attempts' => 1,
            'require_login' => true,
        ]);

        return view('quizzes.create', compact('quiz'));
    }

    public function store(StoreQuizRequest $request): RedirectResponse
    {
        $quiz = null;

        DB::transaction(function () use (&$quiz, $request) {
            $quiz = Quiz::create([
                'user_id' => $request->user()->id,
                'title' => $request->input('title'),
                'description' => $request->input('description'),
                'status' => $request->input('status', 'draft'),
                'opens_at' => $request->input('opens_at'),
                'closes_at' => $request->input('closes_at'),
                'max_attempts' => $request->input('max_attempts', 1),
                'require_login' => $request->boolean('require_login'),
                'settings' => $request->input('settings'),
            ]);

            // Crear invitación automática si la encuesta está publicada
            if ($quiz->status !== 'draft') {
                $this->ensureDefaultInvitation($quiz);
            }
        });
        return redirect()
            ->route('quizzes.edit', $quiz)
            ->with('status', 'Encuesta creada correctamente. Ahora agrega preguntas y configuraciones.');
    }

    public function show(Quiz $quiz): View
    {
        $this->ensureOwnership($quiz);

        $quiz->load(['questions.options', 'invitations', 'attempts.answers']);

        $quiz->loadCount(['questions', 'attempts']);

        return view('quizzes.show', compact('quiz'));
    }

    public function edit(Quiz $quiz): View
    {
        $this->ensureOwnership($quiz);

        $quiz->load(['questions.options', 'invitations' => fn ($query) => $query->latest()]);

        return view('quizzes.edit', compact('quiz'));
    }

    public function update(UpdateQuizRequest $request, Quiz $quiz): RedirectResponse
    {
        $this->ensureOwnership($quiz);

        $quiz->update($request->validated());

        return redirect()
            ->route('quizzes.edit', $quiz)
            ->with('status', 'Encuesta actualizada correctamente.');
    }

    public function destroy(Quiz $quiz): RedirectResponse
    {
        $this->ensureOwnership($quiz);

        $quiz->delete();

        return redirect()
            ->route('quizzes.index')
            ->with('status', 'Encuesta eliminada correctamente.');
    }

    public function publish(Quiz $quiz): RedirectResponse
    {
        $this->ensureOwnership($quiz);

        if ($quiz->status === 'closed') {
            return back()->with('status', __('La encuesta ya está cerrada.'));
        }

        DB::transaction(function () use ($quiz) {
            $quiz->update([
                'status' => 'published',
                'opens_at' => $quiz->opens_at ?? now(),
            ]);

            $this->ensureDefaultInvitation($quiz);
        });

        return back()->with('status', __('La encuesta se publicó y puede recibir respuestas.'));
    }

    public function close(Quiz $quiz): RedirectResponse
    {
        $this->ensureOwnership($quiz);

        if ($quiz->status === 'closed') {
            return back()->with('status', __('La encuesta ya está cerrada.'));
        }

        $quiz->update([
            'status' => 'closed',
            'closes_at' => now(),
            'analysis_requested_at' => now(),
        ]);

        return back()->with('status', __('La encuesta se cerró y se detuvo la recepción de respuestas.'));
    }

    protected function ensureDefaultInvitation(Quiz $quiz): void
    {
        if ($quiz->invitations()->exists()) {
            return;
        }

        $quiz->invitations()->create([
            'created_by' => Auth::id(),
            'code' => $this->generateInvitationCode(),
            'label' => __('Código inicial'),
            'is_active' => true,
        ]);
    }

    protected function generateInvitationCode(?string $preferred = null): string
    {
        $code = Str::upper($preferred ?: Str::random(8));

        while (QuizInvitation::where('code', $code)->exists()) {
            $code = Str::upper(Str::random(8));
        }

        return $code;
    }

    protected function ensureOwnership(Quiz $quiz): void
    {
        $user = Auth::user();

        abort_if(
            $user->role !== $user::ROLE_ADMIN && $quiz->user_id !== $user->id,
            403,
            'No tienes permisos para acceder a esta encuesta.'
        );
    }
}
