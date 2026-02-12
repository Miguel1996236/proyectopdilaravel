<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\AuthorizesQuizAccess;
use App\Http\Requests\InvitationRequest;
use App\Models\Quiz;
use App\Models\QuizInvitation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class QuizInvitationController extends Controller
{
    use AuthorizesQuizAccess;

    public function store(InvitationRequest $request, Quiz $quiz): RedirectResponse
    {
        $this->ensureQuizOwnership($quiz, __('No tienes permisos para gestionar invitaciones de esta encuesta.'));

        $data = $request->validated();

        $quiz->invitations()->create([
            'created_by' => $request->user()->id,
            'label' => $data['label'] ?? null,
            'code' => $this->uniqueCode($data['code'] ?? null),
            'max_uses' => $data['max_uses'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return back()->with('status', __('Código de invitación creado correctamente.'));
    }

    public function update(InvitationRequest $request, Quiz $quiz, QuizInvitation $invitation): RedirectResponse
    {
        $this->ensureInvitationOwnership($quiz, $invitation);

        $data = $request->validated();

        $update = [
            'label' => $data['label'] ?? null,
            'max_uses' => $data['max_uses'] ?? null,
            'expires_at' => $data['expires_at'] ?? null,
            'is_active' => $data['is_active'] ?? true,
        ];

        if (! empty($data['code']) && $data['code'] !== $invitation->code) {
            $update['code'] = $this->uniqueCode($data['code'], $invitation->id);
        }

        $invitation->update($update);

        return back()->with('status', __('Código de invitación actualizado.'));
    }

    public function destroy(Quiz $quiz, QuizInvitation $invitation): RedirectResponse
    {
        $this->ensureInvitationOwnership($quiz, $invitation);

        $invitation->delete();

        return back()->with('status', __('Código de invitación eliminado.'));
    }

    protected function uniqueCode(?string $code = null, ?int $ignoreId = null): string
    {
        $code = $code ?: Str::upper(Str::random(8));

        while (
            QuizInvitation::query()
                ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
                ->where('code', $code)
                ->exists()
        ) {
            $code = Str::upper(Str::random(8));
        }

        return $code;
    }

    protected function ensureInvitationOwnership(Quiz $quiz, QuizInvitation $invitation): void
    {
        abort_if($invitation->quiz_id !== $quiz->id, 404);

        $this->ensureQuizOwnership($quiz, __('No tienes permisos para gestionar invitaciones de esta encuesta.'));
    }
}
