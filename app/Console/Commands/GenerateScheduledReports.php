<?php

namespace App\Console\Commands;

use App\Mail\SurveyReminder;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class GenerateScheduledReports extends Command
{
    protected $signature = 'reports:generate-scheduled';

    protected $description = 'Genera y envía informes automáticos semanales a los docentes sobre sus encuestas activas';

    public function handle(): int
    {
        $teachers = User::where('role', User::ROLE_TEACHER)->get();

        $sentCount = 0;

        foreach ($teachers as $teacher) {
            $activeQuizzes = Quiz::where('user_id', $teacher->id)
                ->where('status', 'published')
                ->withCount('attempts')
                ->get();

            if ($activeQuizzes->isEmpty()) {
                continue;
            }

            $quizSummary = $activeQuizzes->map(function ($quiz) {
                return "• {$quiz->title}: {$quiz->attempts_count} respuestas";
            })->join("\n");

            $message = "Hola {$teacher->name},\n\n";
            $message .= "Este es tu informe semanal de encuestas activas:\n\n";
            $message .= $quizSummary;
            $message .= "\n\nIngresa al sistema para ver los detalles completos.";

            try {
                Mail::to($teacher->email)->send(new SurveyReminder(
                    customSubject: 'Informe semanal de encuestas - ' . config('app.name'),
                    customMessage: $message,
                    surveyLink: route('reports.summary'),
                    surveyTitle: null,
                    senderName: $teacher->name,
                    senderEmail: $teacher->email,
                ));

                $sentCount++;
                $this->info("Informe enviado a: {$teacher->email}");
            } catch (\Exception $e) {
                $this->error("Error enviando a {$teacher->email}: {$e->getMessage()}");
            }
        }

        $this->info("Total de informes enviados: {$sentCount}");

        return self::SUCCESS;
    }
}

