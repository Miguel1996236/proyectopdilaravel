<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
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

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->customSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.survey-reminder',
            with: [
                'customMessage' => $this->customMessage,
                'surveyLink' => $this->surveyLink,
                'surveyTitle' => $this->surveyTitle,
            ],
        );
    }
}

