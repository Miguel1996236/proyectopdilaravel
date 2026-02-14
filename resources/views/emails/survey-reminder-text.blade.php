{{ config('app.name', 'EduQuiz') }}
========================================

{{ $customMessage }}

@if ($surveyTitle)
---
Encuesta: {{ $surveyTitle }}
@endif
@if ($surveyLink)

Responder encuesta: {{ $surveyLink }}

Si el enlace no funciona, copia y pega la dirección en tu navegador.
@endif

----------------------------------------
Este correo fue enviado por {{ config('app.name', 'EduQuiz') }}, una plataforma educativa de encuestas y evaluaciones.
Recibes este mensaje porque un docente de la plataforma te incluyó como destinatario.
Si no reconoces este correo, puedes ignorarlo con seguridad.

© {{ date('Y') }} {{ config('app.name', 'EduQuiz') }}. Todos los derechos reservados.
