EduQuiz
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
Este correo fue enviado por EduQuiz, una plataforma educativa de encuestas y evaluaciones.
Recibes este mensaje porque un docente de la plataforma te incluyó como destinatario.
Si no reconoces este correo, puedes ignorarlo con seguridad.
@if (!empty($senderName) || !empty($senderEmail))

Enviado por: {{ $senderName ?? '—' }}@if (!empty($senderEmail)) ({{ $senderEmail }})@endif
@endif

© {{ date('Y') }} EduQuiz. Todos los derechos reservados.
