<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ __('Informe de encuesta') }} - {{ $quiz->title }}</title>
    <style>
        /* ===== RESET ===== */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1f2937;
            margin: 0;
            padding: 70px 40px 60px 40px; /* espacio para header/footer */
        }
        h1 { font-size: 22px; color: #1f2937; margin-bottom: 6px; }
        h2 { font-size: 16px; color: #4e73df; margin: 20px 0 10px 0; padding-bottom: 5px; border-bottom: 2px solid #4e73df; }
        h3 { font-size: 13px; color: #1f2937; margin: 12px 0 6px 0; }
        p { margin: 0 0 6px; line-height: 1.5; }
        a { color: #4e73df; text-decoration: none; }

        /* ===== UTILIDADES ===== */
        .text-muted { color: #6b7280; }
        .text-small { font-size: 9px; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }

        /* ===== BADGE IA ===== */
        .badge-ai {
            display: inline-block; padding: 2px 8px; background: #1cc88a; color: #fff;
            border-radius: 3px; font-size: 8px; text-transform: uppercase; letter-spacing: 0.5px; font-weight: bold;
        }

        /* ===== PORTADA / CABECERA ===== */
        .report-cover {
            background-color: #4e73df; color: #ffffff;
            padding: 28px 32px; margin: -70px -40px 24px -40px;
        }
        .report-cover h1 { color: #ffffff; font-size: 20px; margin: 0 0 4px 0; border: none; }
        .report-cover .meta { font-size: 10px; color: rgba(255,255,255,0.85); margin: 2px 0; }

        /* ===== KPIs ===== */
        .kpi-grid { width: 100%; border-collapse: collapse; margin: 14px 0 18px 0; }
        .kpi-grid td {
            width: 25%; text-align: center; padding: 14px 6px;
            border: 1px solid #e5e7eb; background-color: #f8f9fc;
        }
        .kpi-value { display: block; font-size: 20px; font-weight: bold; color: #4e73df; }
        .kpi-label { display: block; font-size: 9px; color: #6b7280; text-transform: uppercase; letter-spacing: 0.5px; margin-top: 3px; }

        /* ===== SECCIONES ===== */
        .section { margin-bottom: 20px; }

        .summary-box {
            padding: 12px 16px; background-color: #f0f3ff; border-left: 4px solid #4e73df;
            margin: 8px 0 14px 0; line-height: 1.6;
        }

        /* ===== TABLAS ===== */
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; font-size: 10px; }
        th { background-color: #f3f4f6; font-weight: bold; color: #374151; }

        /* ===== BARRAS CSS ===== */
        .bar-table { width: 100%; border-collapse: collapse; margin: 8px 0; }
        .bar-table td { border: none; padding: 3px 0; vertical-align: middle; font-size: 10px; }
        .bar-table .bar-label { width: 130px; padding-right: 8px; text-align: right; color: #374151; }
        .bar-table .bar-track { padding: 0; }
        .bar-table .bar-pct { width: 50px; padding-left: 8px; font-weight: bold; color: #374151; }

        /* ===== QUESTION BLOCK ===== */
        .question-block {
            margin-bottom: 18px; padding: 14px;
            border: 1px solid #e5e7eb; border-radius: 4px;
            page-break-inside: avoid;
        }
        .question-block h3 { margin-top: 0; }

        /* ===== CHART IMAGE ===== */
        .chart-img {
            display: block; margin: 8px auto; max-width: 420px; width: 100%; height: auto;
        }

        /* ===== IA INSIGHTS ===== */
        .ai-insight {
            margin-top: 8px; padding: 8px 10px;
            background-color: #f8f9fc; border-left: 3px solid #36b9cc;
            font-size: 10px; color: #5a5c69;
        }
        .ai-insight-label {
            font-size: 8px; font-weight: bold; color: #1cc88a;
            text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 3px;
        }

        /* ===== RESPUESTAS ABIERTAS ===== */
        .open-response {
            padding: 6px 10px; margin-bottom: 6px;
            background-color: #f8f9fc; border-left: 3px solid #4e73df;
            font-style: italic; color: #5a5c69; font-size: 10px;
        }

        /* ===== TEMAS CUALITATIVOS ===== */
        .theme-box {
            margin-bottom: 10px; padding: 10px 12px;
            background-color: #f0f8ff; border-left: 3px solid #36b9cc;
            page-break-inside: avoid;
        }
        .quote {
            margin: 4px 0; padding-left: 10px;
            border-left: 2px solid #93c5fd; font-style: italic; color: #5a5c69; font-size: 10px;
        }

        /* ===== RECOMENDACIONES ===== */
        .rec-item {
            margin-bottom: 10px; padding: 10px 14px;
            background-color: #f0faf0; border-left: 4px solid #1cc88a;
            page-break-inside: avoid;
        }
        .rec-number {
            display: inline-block; width: 22px; height: 22px;
            background-color: #1cc88a; color: #fff; text-align: center;
            border-radius: 50%; font-size: 11px; font-weight: bold; line-height: 22px; margin-right: 8px;
        }

        /* ===== LISTAS ===== */
        ul { margin: 2px 0; padding-left: 18px; }
        li { margin-bottom: 4px; }

        /* ===== SALTO DE PÁGINA ===== */
        .page-break { page-break-before: always; }

        /* ===== FOOTER DE DOCUMENTO ===== */
        .doc-footer {
            margin-top: 24px; border-top: 1px solid #e5e7eb;
            padding-top: 10px; text-align: center;
            font-size: 9px; color: #9ca3af;
        }
    </style>
</head>
<body>

    {{-- ===== HEADER / FOOTER REPETIDO EN CADA PÁGINA (DomPDF inline PHP) ===== --}}
    <script type="text/php">
        if (isset($pdf)) {
            $pdf->page_script(function ($pageNumber, $pageCount, $pdf, $fontMetrics) {
                $font = $fontMetrics->get_font('DejaVu Sans', 'normal');

                // --- Header ---
                $pdf->rectangle(0, 0, 595.28, 32, [0.31, 0.45, 0.87], 0.0, [0.31, 0.45, 0.87]);
                $pdf->text(40, 10, 'EduQuiz — Informe de encuesta', $font, 8, [1, 1, 1]);
                $dateStr = date('d/m/Y H:i');
                $w = $fontMetrics->get_text_width($dateStr, $font, 8);
                $pdf->text(595.28 - 40 - $w, 10, $dateStr, $font, 8, [1, 1, 1]);

                // --- Footer ---
                $pdf->line(40, 822, 555, 822, [0.88, 0.88, 0.88], 0.5);
                $pdf->text(40, 828, 'EduQuiz — Generado con IA', $font, 7, [0.62, 0.62, 0.62]);
                $pageText = $pageNumber . ' / ' . $pageCount;
                $w2 = $fontMetrics->get_text_width($pageText, $font, 8);
                $pdf->text(595.28 - 40 - $w2, 827, $pageText, $font, 8, [0.42, 0.42, 0.42]);
            });
        }
    </script>

    {{-- ===== PORTADA ===== --}}
    <div class="report-cover">
        <h1>{{ $quiz->title }}</h1>
        <p class="meta">{{ __('Docente: :name', ['name' => $quiz->owner?->name ?? __('No disponible')]) }}</p>
        <p class="meta">{{ __('Generado el :date', ['date' => now()->format('d/m/Y H:i')]) }}</p>
        <p class="meta" style="margin-top: 6px;"><span class="badge-ai">Analizado con IA</span></p>
    </div>

    {{-- ===== KPIs ===== --}}
    <div class="section">
        <h2>{{ __('Resumen del informe') }}</h2>
        <table class="kpi-grid">
            <tr>
                <td>
                    <span class="kpi-value">{{ $totalResponses ?? $quiz->attempts->count() }}</span>
                    <span class="kpi-label">{{ __('Total respuestas') }}</span>
                </td>
                <td>
                    <span class="kpi-value">{{ $totalQuestions ?? $quiz->questions->count() }}</span>
                    <span class="kpi-label">{{ __('Preguntas') }}</span>
                </td>
                <td>
                    <span class="kpi-value">{{ $generalAverage ?? '—' }}</span>
                    <span class="kpi-label">{{ __('Promedio general') }}</span>
                </td>
                <td>
                    <span class="kpi-value">{{ $satisfactionLevel ?? __('N/A') }}</span>
                    <span class="kpi-label">{{ __('Satisfacción') }}</span>
                </td>
            </tr>
        </table>
    </div>

    {{-- ===== RESUMEN EJECUTIVO ===== --}}
    <div class="section">
        <h2>{{ __('Resumen ejecutivo') }}</h2>
        @if ($quiz->description)
            <p class="text-muted" style="margin-bottom: 6px;">{{ $quiz->description }}</p>
        @endif
        <div class="summary-box">
            {{ $analysisSummary['summary'] ?? __('No se recibió un resumen de la IA.') }}
        </div>
        <table>
            <tr>
                <th style="width: 35%;">{{ __('Estado') }}</th>
                <td>{{ $quiz->status === 'published' ? __('Activa') : ucfirst($quiz->status ?? '—') }}</td>
            </tr>
            <tr>
                <th>{{ __('Fecha de cierre') }}</th>
                <td>{{ optional($quiz->closes_at)->format('d/m/Y H:i') ?? __('No definido') }}</td>
            </tr>
        </table>
    </div>

    {{-- ===== HALLAZGOS CUANTITATIVOS ===== --}}
    <div class="page-break"></div>
    <div class="section">
        <h2>{{ __('Hallazgos cuantitativos') }}</h2>

        @forelse ($quantitativeInsights as $insight)
            <div class="question-block">
                <h3>{{ $insight['question'] }}</h3>
                <p class="text-muted text-small">
                    {{ __('Tipo: :type  ·  Respuestas: :count', ['type' => ucfirst(str_replace('_', ' ', $insight['type'] ?? '')), 'count' => $insight['total_responses'] ?? 0]) }}
                </p>

                @if (! empty($insight['average']))
                    <p style="margin-top: 4px;">
                        <strong>{{ __('Promedio') }}:</strong> {{ $insight['average'] }}
                        &nbsp;({{ __('mín.') }} {{ $insight['minimum'] ?? '—' }} / {{ __('máx.') }} {{ $insight['maximum'] ?? '—' }})
                    </p>
                @endif

                {{-- Gráfico generado por QuickChart (server-side) --}}
                @php
                    $chartImages = $chartImages ?? [];
                    $qId = $insight['question_id'] ?? null;
                    $chartBase64 = $qId && isset($chartImages[$qId]) ? $chartImages[$qId] : null;
                @endphp
                @if ($chartBase64)
                    <img src="{{ $chartBase64 }}" alt="{{ $insight['question'] }}" class="chart-img" />
                @endif

                {{-- Barras CSS como refuerzo visual --}}
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
                    <table class="bar-table">
                        @foreach ($items as $row)
                            @php $barWidth = max(2, min(100, $row['pct'])); @endphp
                            <tr>
                                <td class="bar-label">{{ Str::limit($row['label'], 28) }}</td>
                                <td class="bar-track">
                                    <table cellpadding="0" cellspacing="0" style="width: 100%; border-collapse: collapse;">
                                        <tr>
                                            <td style="width: {{ $barWidth }}%; background-color: #4e73df; height: 16px; border: none; padding: 0;{{ $row['pct'] > 0 ? '' : ' background-color: transparent;' }}"></td>
                                            <td style="width: {{ 100 - $barWidth }}%; background-color: #e5e7eb; height: 16px; border: none; padding: 0;"></td>
                                        </tr>
                                    </table>
                                </td>
                                <td class="bar-pct">{{ number_format($row['pct'], 1) }}%</td>
                            </tr>
                        @endforeach
                    </table>
                @endif

                {{-- Tabla de datos --}}
                @if (! empty($insight['options']))
                    <table>
                        <thead>
                            <tr><th>{{ __('Respuesta') }}</th><th>{{ __('Conteo') }}</th><th>{{ __('Porcentaje') }}</th></tr>
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
                            <tr><th>{{ __('Valor') }}</th><th>{{ __('Conteo') }}</th><th>{{ __('Porcentaje') }}</th></tr>
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

                {{-- Interpretación IA --}}
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
                        <div class="ai-insight-label">{{ __('Interpretación de IA') }}</div>
                        <ul>
                            @foreach ($questionFindings as $finding)
                                <li style="font-size: 10px;">{{ $finding }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @empty
            <p class="text-muted">{{ __('No se encontraron datos cuantitativos para esta encuesta.') }}</p>
        @endforelse
    </div>

    {{-- ===== RESPUESTAS ABIERTAS ===== --}}
    <div class="page-break"></div>
    <div class="section">
        <h2>{{ __('Respuestas abiertas') }}</h2>
        <p class="text-muted text-small" style="margin-bottom: 8px;">{{ __('Comentarios y sugerencias de los participantes') }}</p>
        @if (! empty($qualitativeInsights))
            @foreach ($qualitativeInsights as $item)
                <h3>{{ $item['question'] ?? __('Pregunta') }}</h3>
                @foreach ($item['responses'] ?? [] as $response)
                    <div class="open-response">"{{ $response }}"</div>
                @endforeach
            @endforeach
        @else
            <p class="text-muted">{{ __('No hay respuestas abiertas para esta encuesta.') }}</p>
        @endif
    </div>

    {{-- ===== TEMAS CUALITATIVOS ===== --}}
    <div class="section">
        <h2>{{ __('Temas cualitativos detectados') }} <span class="badge-ai">IA</span></h2>
        <p class="text-muted text-small" style="margin-bottom: 8px;">{{ __('Patrones identificados por la IA a partir de las respuestas abiertas') }}</p>
        @if (! empty($analysisSummary['qualitative']))
            @foreach ($analysisSummary['qualitative'] as $theme)
                <div class="theme-box">
                    <strong>{{ $theme['theme'] ?? __('Tema') }}</strong>
                    @if (! empty($theme['evidence']))
                        @foreach ($theme['evidence'] as $quote)
                            <p class="quote">"{{ $quote }}"</p>
                        @endforeach
                    @endif
                </div>
            @endforeach
        @else
            <p class="text-muted">{{ __('La IA no identificó temas cualitativos destacados.') }}</p>
        @endif
    </div>

    {{-- ===== RECOMENDACIONES ===== --}}
    <div class="page-break"></div>
    <div class="section">
        <h2>{{ __('Recomendaciones') }} <span class="badge-ai">{{ __('Generadas por IA') }}</span></h2>
        <p class="text-muted text-small" style="margin-bottom: 10px;">{{ __('Acciones sugeridas para mejorar la experiencia educativa') }}</p>
        @if (! empty($analysisSummary['recommendations']))
            @foreach ($analysisSummary['recommendations'] as $index => $recommendation)
                <div class="rec-item">
                    <span class="rec-number">{{ $index + 1 }}</span>
                    {{ $recommendation }}
                </div>
            @endforeach
        @else
            <p class="text-muted">{{ __('La IA no generó recomendaciones específicas para esta encuesta.') }}</p>
        @endif
    </div>

    {{-- ===== PIE DE DOCUMENTO ===== --}}
    <div class="doc-footer">
        {{ __('Este informe fue generado automáticamente por EduQuiz con asistencia de Inteligencia Artificial.') }}
        <br>{{ __('Los resultados son orientativos y deben complementarse con el criterio profesional del docente.') }}
    </div>

</body>
</html>
