<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class SurveyReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $customSubject,
        public string $customMessage,
        public ?string $surveyLink = null,
        public ?string $surveyTitle = null,
    ) {
    }

    /**
     * Cabeceras del correo: incluye List-Unsubscribe, Precedence y Message-ID
     * para reducir probabilidad de spam.
     */
    public function headers(): Headers
    {
        $fromAddress = config('mail.from.address', 'noreply@example.com');

        return new Headers(
            messageId: null,
            references: [],
            text: [
                // Indica a los clientes de correo que es correo masivo controlado
                'Precedence'       => 'bulk',
                // List-Unsubscribe: Gmail usa esto para mostrar "Cancelar suscripción"
                'List-Unsubscribe' => '<mailto:' . $fromAddress . '?subject=unsubscribe>',
                // X-Mailer personalizado
                'X-Mailer'         => config('app.name', 'EduQuiz') . ' Mailer',
            ],
        );
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->customSubject,
        );
    }

    /**
     * Contenido: incluye vista HTML y vista de texto plano.
     * Gmail y otros clientes prefieren correos con ambas versiones (multipart/alternative).
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.survey-reminder',
            text: 'emails.survey-reminder-text',
            with: [
                'customSubject' => $this->customSubject,
                'customMessage' => $this->customMessage,
                'surveyLink'    => $this->surveyLink,
                'surveyTitle'   => $this->surveyTitle,
            ],
        );
    }
}
