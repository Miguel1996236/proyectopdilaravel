<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Informe de encuesta') }} - {{ $quiz->title }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1f2937; }
        h1, h2, h3, h4 { color: #1f2937; margin-bottom: 8px; }
        h1 { font-size: 20px; }
        h2 { font-size: 16px; margin-top: 20px; }
        h3 { font-size: 14px; margin-top: 14px; }
        p { margin: 0 0 6px; }
        .section { margin-bottom: 18px; }
        .badge { display: inline-block; padding: 2px 6px; background: #2563eb; color: #fff; border-radius: 4px; font-size: 10px; text-transform: uppercase; }
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
        .rec-item { margin-bottom: 10px; padding: 8px 12px; background: #f8f9fc; border-left: 4px solid #4e73df; }
    </style>
</head>
<body>
    <h1>{{ $quiz->title }}</h1>
    <p class="small">{{ __('Generado el :date', ['date' => now()->format('d/m/Y H:i')]) }}</p>
    <p class="small">{{ __('Docente responsable: :name', ['name' => $quiz->owner?->name ?? __('No disponible')]) }}</p>

    <div class="section">
        <h2>{{ __('Resumen ejecutivo') }}</h2>
        <p class="muted">{{ $quiz->description }}</p>
        <p>{{ $analysisSummary['summary'] ?? __('No se recibió un resumen de la IA.') }}</p>
    </div>

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
        @empty
            <p class="muted">{{ __('No se encontraron datos cuantitativos para esta encuesta.') }}</p>
        @endforelse
    </div>

    <div class="section">
        <h2>{{ __('Respuestas abiertas') }}</h2>
        <p class="small muted">{{ __('Comentarios y sugerencias de los participantes') }}</p>
        @if (! empty($qualitativeInsights))
            @foreach ($qualitativeInsights as $item)
                <h3>{{ $item['question'] ?? __('Pregunta') }}</h3>
                @foreach ($item['responses'] ?? [] as $response)
                    <p class="quote">"{{ $response }}"</p>
                @endforeach
            @endforeach
        @else
            <p class="muted">{{ __('No hay respuestas abiertas para esta encuesta.') }}</p>
        @endif
    </div>

    <div class="section">
        <h2>{{ __('Temas cualitativos destacados') }}</h2>
        <p class="small muted">{{ __('Sugerencias para mejorar la experiencia educativa') }}</p>
        @if (! empty($analysisSummary['qualitative']))
            @foreach ($analysisSummary['qualitative'] as $theme)
                <h3>{{ $theme['theme'] ?? __('Tema') }}</h3>
                @if (! empty($theme['evidence']))
                    @foreach ($theme['evidence'] as $quote)
                        <p class="quote">"{{ $quote }}"</p>
                    @endforeach
                @endif
            @endforeach
        @else
            <p class="muted">{{ __('La IA no identificó temas cualitativos destacados.') }}</p>
        @endif
    </div>

    <div class="section">
        <h2>{{ __('Recomendaciones') }}</h2>
        @if (! empty($analysisSummary['recommendations']))
            @foreach ($analysisSummary['recommendations'] as $index => $recommendation)
                <div class="rec-item">
                    <strong>{{ $index + 1 }}.</strong> {{ $recommendation }}
                </div>
            @endforeach
        @else
            <p class="muted">{{ __('La IA no generó recomendaciones específicas para esta encuesta.') }}</p>
        @endif
    </div>
</body>
</html>
