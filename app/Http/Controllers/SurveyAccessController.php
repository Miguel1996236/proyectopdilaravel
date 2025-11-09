<?php

namespace App\Http\Controllers;

use App\Models\QuizInvitation;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SurveyAccessController extends Controller
{
    public function showLinkForm(): View
    {
        return view('surveys.access');
    }

    public function verifyCode(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20'],
        ]);

        $code = Str::upper($validated['code']);

        $invitation = QuizInvitation::with('quiz')
            ->whereRaw('UPPER(code) = ?', [$code])
            ->first();

        if (! $invitation || $invitation->quiz->status !== 'published' || ! $invitation->is_valid) {
            return back()
                ->withErrors(['code' => __('El código ingresado no es válido o ya no está disponible.')])
                ->withInput();
        }

        if ($invitation->quiz->require_login && ! Auth::check()) {
            session()->put('url.intended', route('surveys.respond.show', $invitation->code));

            return redirect()->route('login')
                ->with('status', __('Inicia sesión para completar la encuesta.'));
        }

        return redirect()->route('surveys.respond.show', $invitation->code);
    }
}
