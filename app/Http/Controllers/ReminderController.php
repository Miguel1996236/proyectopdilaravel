<?php

namespace App\Http\Controllers;

use App\Mail\SurveyReminder;
use App\Models\EmailReminder;
use App\Models\Quiz;
use App\Models\StudentGroup;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ReminderController extends Controller
{
    protected function ensureTeacherOrAdmin(): void
    {
        abort_unless(
            in_array(Auth::user()?->role, [User::ROLE_ADMIN, User::ROLE_TEACHER]),
            403
        );
    }

    public function create(): View
    {
        $this->ensureTeacherOrAdmin();

        $user = Auth::user();

        $groups = StudentGroup::where('user_id', $user->id)
            ->withCount('members')
            ->orderBy('name')
            ->get();

        $quizzes = Quiz::where('user_id', $user->id)
            ->whereIn('status', ['published', 'closed'])
            ->with('invitations')
            ->orderBy('title')
            ->get();

        $history = EmailReminder::where('user_id', $user->id)
            ->with('quiz')
            ->latest()
            ->limit(10)
            ->get();

        return view('reminders.create', compact('groups', 'quizzes', 'history'));
    }

    public function send(Request $request): RedirectResponse
    {
        $this->ensureTeacherOrAdmin();

        $data = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:5000'],
            'quiz_id' => ['nullable', 'exists:quizzes,id'],
            'groups' => ['nullable', 'array'],
            'groups.*' => ['exists:student_groups,id'],
            'individual_emails' => ['nullable', 'string'],
        ]);

        $user = Auth::user();
        $recipients = collect();

        // Recolectar correos de los grupos seleccionados
        if (! empty($data['groups'])) {
            $groupMembers = StudentGroup::whereIn('id', $data['groups'])
                ->where('user_id', $user->id)
                ->with('members')
                ->get()
                ->flatMap(fn ($g) => $g->members->pluck('email', 'name'));

            foreach ($groupMembers as $name => $email) {
                $recipients->put($email, $name);
            }
        }

        // Agregar correos individuales
        if (! empty($data['individual_emails'])) {
            $emails = preg_split('/[\r\n,;]+/', $data['individual_emails']);
            foreach ($emails as $email) {
                $email = trim($email);
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $recipients->put($email, $email);
                }
            }
        }

        if ($recipients->isEmpty()) {
            return back()
                ->withInput()
                ->withErrors(['groups' => __('Debes seleccionar al menos un grupo o ingresar correos.')]);
        }

        // Obtener link de la encuesta si se seleccionó
        $surveyLink = null;
        $surveyTitle = null;
        $quiz = null;
        if (! empty($data['quiz_id'])) {
            $quiz = Quiz::with('invitations')->find($data['quiz_id']);
            if ($quiz) {
                $surveyTitle = $quiz->title;
                $invitation = $quiz->invitations->where('is_active', true)->first();
                if ($invitation) {
                    $surveyLink = route('surveys.respond.show', $invitation->code);
                }
            }
        }

        // Enviar correos
        $sentCount = 0;
        foreach ($recipients as $email => $name) {
            try {
                Mail::to($email)->send(new SurveyReminder(
                    customSubject: $data['subject'],
                    customMessage: $data['message'],
                    surveyLink: $surveyLink,
                    surveyTitle: $surveyTitle,
                ));
                $sentCount++;
            } catch (\Exception $e) {
                \Log::warning("Error enviando recordatorio a {$email}: " . $e->getMessage());
            }
        }

        // Registrar el envío
        EmailReminder::create([
            'user_id' => $user->id,
            'quiz_id' => $quiz?->id,
            'subject' => $data['subject'],
            'message' => $data['message'],
            'recipients_count' => $sentCount,
            'sent_at' => now(),
            'status' => $sentCount > 0 ? 'sent' : 'failed',
        ]);

        return back()->with('status', __(':count recordatorio(s) enviado(s) correctamente.', ['count' => $sentCount]));
    }
}

