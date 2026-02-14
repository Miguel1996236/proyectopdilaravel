<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Informe de encuesta') }} - {{ $quiz->title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        h1, h2, h3, h4 { color: #1f2937; margin-bottom: 8px; }
        h1 { font-size: 20px; border-bottom: 2px solid #4e73df; padding-bottom: 6px; }
        h2 { font-size: 16px; margin-top: 20px; color: #4e73df; }
        h3 { font-size: 14px; margin-top: 14px; }
        p { margin: 0 0 6px; }
        .section { margin-bottom: 18px; }
        .badge { display: inline-block; padding: 2px 6px; background: #2563eb; color: #fff; border-radius: 4px; font-size: 10px; text-transform: uppercase; }
        .badge-ai { display: inline-block; padding: 2px 6px; background: #1cc88a; color: #fff; border-radius: 4px; font-size: 9px; text-transform: uppercase; letter-spacing: 0.5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th, td { border: 1px solid #d1d5db; padding: 6px; text-align: left; }
        th { background: #f3f4f6; font-weight: bold; }
        ul { margin: 0; padding-left: 20px; }
        li { margin-bottom: 6px; }
        .small { font-size: 10px; color: #6b7280; }
        .muted { color: #6b7280; }
        .quote { margin: 6px 0; padding-left: 10px; border-left: 3px solid #3b82f6; font-style: italic; }
        /* Barras visuales para DomPDF (usa table real) */
        .bar-table { width: 100%; border-collapse: collapse; margin: 8px 0; }
        .bar-table td { border: none; padding: 3px 0; vertical-align: middle; font-size: 11px; }
        .bar-table .bar-label-cell { width: 130px; padding-right: 8px; text-align: right; color: #374151; }
        .bar-table .bar-track-cell { padding: 0; }
        .bar-table .bar-pct-cell { width: 55px; padding-left: 8px; font-weight: bold; color: #374151; }
        .rec-item { margin-bottom: 10px; padding: 8px 12px; background: #f0faf0; border-left: 4px solid #1cc88a; }
        .rec-number { display: inline-block; width: 22px; height: 22px; background: #1cc88a; color: #fff; text-align: center; border-radius: 50%; font-size: 11px; font-weight: bold; line-height: 22px; margin-right: 8px; }
        .summary-box { padding: 10px 14px; background: #f0f3ff; border-left: 4px solid #4e73df; margin-bottom: 12px; }
        .ai-insight { margin-top: 6px; padding: 6px 10px; background: #f8f9fc; border-left: 3px solid #36b9cc; font-size: 11px; color: #5a5c69; }
        .ai-insight-label { font-size: 9px; font-weight: bold; color: #1cc88a; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px; }
        .theme-box { margin-bottom: 10px; padding: 8px 12px; background: #f0f8ff; border-left: 3px solid #36b9cc; }
        .open-response { padding: 6px 10px; margin-bottom: 6px; background: #f8f9fc; border-left: 3px solid #4e73df; font-style: italic; color: #5a5c69; }
        .header-meta { font-size: 10px; color: #6b7280; margin-bottom: 2px; }
        .page-break { page-break-before: always; }
    </style>
</head>
<body>
    <h1>{{ $quiz->title }}</h1>
    <p class="header-meta">{{ __('Generado el :date', ['date' => now()->format('d/m/Y H:i')]) }}</p>
    <p class="header-meta">{{ __('Docente responsable: :name', ['name' => $quiz->owner?->name ?? __('No disponible')]) }}</p>
    <p class="header-meta" style="margin-bottom: 10px;"><span class="badge-ai">Analizado con IA</span></p>

    {{-- Resumen ejecutivo --}}
    <div class="section">
        <h2>{{ __('Resumen ejecutivo') }}</h2>
        @if ($quiz->description)
            <p class="muted">{{ $quiz->description }}</p>
        @endif
        <div class="summary-box">
            <p style="margin: 0; line-height: 1.6;">{{ $analysisSummary['summary'] ?? __('No se recibió un resumen de la IA.') }}</p>
        </div>
    </div>

    {{-- Indicadores clave --}}
    <div class="section">
        <h2>{{ __('Indicadores clave') }}</h2>
        <table>
            <tbody>
                <tr>
                    <th>{{ __('Respuestas registradas') }}</th>
                    <td>{{ $quiz->attempts->count() }}</td>
                </tr>
                <tr>
                    <th>{{ __('Preguntas totales') }}</th>
                    <td>{{ $quiz->questions->count() }}</td>
                </tr>
                <tr>
                    <th>{{ __('Fecha de cierre') }}</th>
                    <td>{{ optional($quiz->closes_at)->format('d/m/Y H:i') ?? __('No definido') }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    {{-- Hallazgos cuantitativos --}}
    <div class="section">
        <h2>{{ __('Hallazgos cuantitativos') }}</h2>
        @forelse ($quantitativeInsights as $insight)
            <h3>{{ $insight['question'] }}</h3>
            <p class="small muted">{{ __('Respuestas: :count', ['count' => $insight['total_responses'] ?? 0]) }}</p>
            @if (! empty($insight['average']))
                <p>{{ __('Promedio: :value (mín. :min / máx. :max)', ['value' => $insight['average'], 'min' => $insight['minimum'] ?? '—', 'max' => $insight['maximum'] ?? '—']) }}</p>
            @endif

            @php
                $items = [];
                if (! empty($insight['options'])) {
                    foreach ($insight['options'] as $opt) {
                        $pct = (float) ($opt['percentage'] ?? 0);
                        $items[] = ['label' => $opt['label'], 'count' => $opt['count'], 'pct' => $pct];
                    }
                } elseif (! empty($insight['distribution'])) {
                    foreach ($insight['distribution'] as $d) {
                        $pct = (float) ($d['percentage'] ?? 0);
                        $items[] = ['label' => $d['value'], 'count' => $d['count'], 'pct' => $pct];
                    }
                }
            @endphp

            @if (! empty($items))
                {{-- Barras visuales usando table real (compatible DomPDF) --}}
                <table class="bar-table">
                    @foreach ($items as $row)
                        @php $barWidth = max(2, min(100, $row['pct'])); @endphp
                        <tr>
                            <td class="bar-label-cell">{{ Str::limit($row['label'], 25) }}</td>
                            <td class="bar-track-cell">
                                <table cellpadding="0" cellspacing="0" style="width: 100%; border-collapse: collapse;">
                                    <tr>
                                        <td style="width: {{ $barWidth }}%; background-color: #4e73df; height: 16px; border: none; padding: 0;{{ $row['pct'] > 0 ? '' : ' background-color: transparent;' }}"></td>
                                        <td style="width: {{ 100 - $barWidth }}%; background-color: #e5e7eb; height: 16px; border: none; padding: 0;"></td>
                                    </tr>
                                </table>
                            </td>
                            <td class="bar-pct-cell">{{ number_format($row['pct'], 1) }}%</td>
                        </tr>
                    @endforeach
                </table>
            @endif

            @if (! empty($insight['options']))
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('Respuesta') }}</th>
                            <th>{{ __('Conteo') }}</th>
                            <th>{{ __('Porcentaje') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($insight['options'] as $option)
                            <tr>
                                <td>{{ $option['label'] }}</td>
                                <td>{{ $option['count'] }}</td>
                                <td>{{ $option['percentage'] ?? '—' }}{{ isset($option['percentage']) ? '%' : '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            @if (! empty($insight['distribution']))
                <table>
                    <thead>
                        <tr>
                            <th>{{ __('Valor') }}</th>
                            <th>{{ __('Conteo') }}</th>
                            <th>{{ __('Porcentaje') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($insight['distribution'] as $item)
                            <tr>
                                <td>{{ $item['value'] }}</td>
                                <td>{{ $item['count'] }}</td>
                                <td>{{ $item['percentage'] }}%</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            {{-- Interpretación de IA para esta pregunta --}}
            @php
                $questionFindings = [];
                foreach (($analysisSummary['quantitative'] ?? []) as $qf) {
                    if (isset($qf['question']) && str_contains(strtolower($qf['question']), strtolower(substr($insight['question'], 0, 30)))) {
                        $questionFindings = $qf['key_findings'] ?? [];
                        break;
                    }
                }
            @endphp
            @if (! empty($questionFindings))
                <div class="ai-insight">
                    <div class="ai-insight-label">Interpretación de IA</div>
                    <ul style="margin: 3px 0; padding-left: 16px;">
                        @foreach ($questionFindings as $finding)
                            <li style="margin-bottom: 3px; font-size: 11px;">{{ $finding }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
        @empty
            <p class="muted">{{ __('No se encontraron datos cuantitativos para esta encuesta.') }}</p>
        @endforelse
    </div>

    {{-- Respuestas abiertas --}}
    <div class="section">
        <h2>{{ __('Respuestas abiertas') }}</h2>
        <p class="small muted">{{ __('Comentarios y sugerencias de los participantes') }}</p>
        @if (! empty($qualitativeInsights))
            @foreach ($qualitativeInsights as $item)
                <h3>{{ $item['question'] ?? __('Pregunta') }}</h3>
                @foreach ($item['responses'] ?? [] as $response)
                    <div class="open-response">"{{ $response }}"</div>
                @endforeach
            @endforeach
        @else
            <p class="muted">{{ __('No hay respuestas abiertas para esta encuesta.') }}</p>
        @endif
    </div>

    {{-- Temas cualitativos --}}
    <div class="section">
        <h2>{{ __('Temas cualitativos detectados') }} <span class="badge-ai">IA</span></h2>
        <p class="small muted">{{ __('Patrones identificados por la IA a partir de las respuestas abiertas') }}</p>
        @if (! empty($analysisSummary['qualitative']))
            @foreach ($analysisSummary['qualitative'] as $theme)
                <div class="theme-box">
                    <strong>{{ $theme['theme'] ?? __('Tema') }}</strong>
                    @if (! empty($theme['evidence']))
                        @foreach ($theme['evidence'] as $quote)
                            <p class="quote" style="margin-top: 4px;">"{{ $quote }}"</p>
                        @endforeach
                    @endif
                </div>
            @endforeach
        @else
            <p class="muted">{{ __('La IA no identificó temas cualitativos destacados.') }}</p>
        @endif
    </div>

    {{-- Recomendaciones --}}
    <div class="section">
        <h2>{{ __('Recomendaciones') }} <span class="badge-ai">Generadas por IA</span></h2>
        <p class="small muted" style="margin-bottom: 10px;">{{ __('Acciones sugeridas para mejorar la experiencia educativa') }}</p>
        @if (! empty($analysisSummary['recommendations']))
            @foreach ($analysisSummary['recommendations'] as $index => $recommendation)
                <div class="rec-item">
                    <span class="rec-number">{{ $index + 1 }}</span>
                    {{ $recommendation }}
                </div>
            @endforeach
        @else
            <p class="muted">{{ __('La IA no generó recomendaciones específicas para esta encuesta.') }}</p>
        @endif
    </div>

    {{-- Pie de página --}}
    <div style="margin-top: 20px; border-top: 1px solid #e5e7eb; padding-top: 10px;">
        <p class="small" style="text-align: center;">
            {{ __('Este informe fue generado automáticamente por EduQuiz con asistencia de Inteligencia Artificial.') }}
            <br>{{ __('Los resultados son orientativos y deben complementarse con el criterio profesional del docente.') }}
        </p>
    </div>
</body>
</html>
