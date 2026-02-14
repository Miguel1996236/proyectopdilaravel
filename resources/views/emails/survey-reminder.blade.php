<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="es" xml:lang="es">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="color-scheme" content="light" />
    <meta name="supported-color-schemes" content="light" />
    <title>{{ $customSubject ?? 'EduQuiz' }}</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
</head>
<body style="margin: 0; padding: 0; width: 100%; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; background-color: #f4f5f7; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, Arial, sans-serif;">
    {{-- Preheader text (se muestra en la vista previa del email en Gmail/Outlook) --}}
    <div style="display: none; max-height: 0; overflow: hidden; mso-hide: all;">
        {{ Str::limit(strip_tags($customMessage), 120) }}
        {{-- Espacios para empujar el HTML fuera del preheader --}}
        &nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;
    </div>

    {{-- Wrapper table --}}
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="background-color: #f4f5f7;">
        <tr>
            <td align="center" style="padding: 24px 16px;">

                {{-- Container principal (600px max) --}}
                <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="600" style="max-width: 600px; width: 100%; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 4px rgba(0,0,0,0.08);">

                    {{-- ========== HEADER EDUQUIZ ========== --}}
                    <tr>
                        <td style="background: linear-gradient(135deg, #4e73df 0%, #2e59d9 100%); padding: 32px 40px; text-align: center;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td align="center">
                                        {{-- Logo EduQuiz --}}
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" align="center">
                                            <tr>
                                                <td align="center">
                                                    <img src="{{ asset('images/eduquiz-logo.png') }}" alt="EduQuiz" width="200" height="56" style="display: block; max-width: 200px; height: auto; border: 0; outline: none; text-decoration: none;" />
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                @if ($surveyTitle)
                                <tr>
                                    <td align="center" style="padding-top: 8px; font-size: 14px; color: rgba(255,255,255,0.85); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, Arial, sans-serif;">
                                        {{ $surveyTitle }}
                                    </td>
                                </tr>
                                @endif
                            </table>
                        </td>
                    </tr>

                    {{-- ========== BODY ========== --}}
                    <tr>
                        <td style="padding: 36px 40px 20px 40px;">

                            {{-- Saludo --}}
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, Arial, sans-serif; font-size: 15px; line-height: 1.7; color: #3a3b45;">
                                        {!! nl2br(e($customMessage)) !!}
                                    </td>
                                </tr>
                            </table>

                            @if ($surveyTitle)
                            {{-- Info de la encuesta --}}
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 24px;">
                                <tr>
                                    <td style="background-color: #f8f9fc; border-left: 4px solid #4e73df; border-radius: 4px; padding: 16px 20px;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                            <tr>
                                                <td style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, Arial, sans-serif; font-size: 12px; color: #858796; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600; padding-bottom: 4px;">
                                                    Encuesta
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, Arial, sans-serif; font-size: 16px; color: #2e59d9; font-weight: 700;">
                                                    {{ $surveyTitle }}
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>
                            @endif

                            @if ($surveyLink)
                            {{-- Botón CTA --}}
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%" style="margin-top: 28px;">
                                <tr>
                                    <td align="center">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td align="center" style="border-radius: 6px; background-color: #4e73df;">
                                                    <a href="{{ $surveyLink }}" target="_blank" style="display: inline-block; padding: 14px 36px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, Arial, sans-serif; font-size: 15px; font-weight: 600; color: #ffffff; text-decoration: none; border-radius: 6px; letter-spacing: 0.02em;">
                                                        &#9654;&nbsp;&nbsp;Responder encuesta
                                                    </a>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding-top: 14px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, Arial, sans-serif; font-size: 12px; color: #b7b9cc; line-height: 1.5;">
                                        Si el botón no funciona, copia y pega este enlace en tu navegador:<br />
                                        <a href="{{ $surveyLink }}" style="color: #4e73df; word-break: break-all; text-decoration: underline;">{{ $surveyLink }}</a>
                                    </td>
                                </tr>
                            </table>
                            @endif
                        </td>
                    </tr>

                    {{-- ========== SEPARADOR ========== --}}
                    <tr>
                        <td style="padding: 0 40px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td style="border-top: 1px solid #e3e6f0; height: 1px; font-size: 0; line-height: 0;">&nbsp;</td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                    {{-- ========== FOOTER ========== --}}
                    <tr>
                        <td style="padding: 24px 40px 32px 40px;">
                            <table role="presentation" cellpadding="0" cellspacing="0" border="0" width="100%">
                                <tr>
                                    <td align="center" style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, Arial, sans-serif; font-size: 12px; color: #858796; line-height: 1.6;">
                                        Este correo fue enviado por <strong>EduQuiz</strong>,
                                        una plataforma educativa de encuestas y evaluaciones.
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding-top: 8px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, Arial, sans-serif; font-size: 12px; color: #b7b9cc; line-height: 1.5;">
                                        Recibes este mensaje porque un docente de la plataforma te incluyó como destinatario.
                                        <br />
                                        Si no reconoces este correo, puedes ignorarlo con seguridad.
                                    </td>
                                </tr>
                                @if (!empty($senderName) || !empty($senderEmail))
                                <tr>
                                    <td align="center" style="padding-top: 12px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, Arial, sans-serif; font-size: 12px; color: #5a5c69; line-height: 1.5;">
                                        Enviado por: <strong>{{ $senderName ?? '—' }}</strong>
                                        @if (!empty($senderEmail))
                                            &lt;<a href="mailto:{{ $senderEmail }}" style="color: #4e73df; text-decoration: none;">{{ $senderEmail }}</a>&gt;
                                        @endif
                                    </td>
                                </tr>
                                @endif
                                <tr>
                                    <td align="center" style="padding-top: 16px; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, Arial, sans-serif; font-size: 11px; color: #d1d3e2;">
                                        &copy; {{ date('Y') }} EduQuiz. Todos los derechos reservados.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>

                </table>
                {{-- Fin container --}}

            </td>
        </tr>
    </table>
    {{-- Fin wrapper --}}
</body>
</html>
