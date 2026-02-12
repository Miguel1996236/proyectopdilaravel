<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $customMessage }}</title>
    <style>
        body { font-family: 'Nunito', Arial, sans-serif; background: #f8f9fc; margin: 0; padding: 0; }
        .container { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.08); overflow: hidden; }
        .header { background: linear-gradient(135deg, #4e73df, #224abe); padding: 30px; text-align: center; }
        .header h1 { color: #fff; margin: 0; font-size: 22px; }
        .body { padding: 30px; color: #333; line-height: 1.7; }
        .body p { margin: 0 0 15px; }
        .btn { display: inline-block; background: #4e73df; color: #fff !important; padding: 12px 30px; border-radius: 8px; text-decoration: none; font-weight: 600; margin-top: 10px; }
        .footer { padding: 20px 30px; text-align: center; color: #858796; font-size: 12px; border-top: 1px solid #e3e6f0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>{{ config('app.name', 'Sistema de Encuestas') }}</h1>
        </div>
        <div class="body">
            {!! nl2br(e($customMessage)) !!}

            @if ($surveyTitle)
                <p style="margin-top: 20px;"><strong>Encuesta:</strong> {{ $surveyTitle }}</p>
            @endif

            @if ($surveyLink)
                <p style="text-align: center; margin-top: 25px;">
                    <a href="{{ $surveyLink }}" class="btn">Responder encuesta</a>
                </p>
            @endif
        </div>
        <div class="footer">
            <p>Este correo fue enviado desde {{ config('app.name', 'Sistema de Encuestas') }}.</p>
        </div>
    </div>
</body>
</html>

