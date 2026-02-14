# EduQuiz — Documentación completa del proyecto

**Contexto:** Proyecto universitario de fin de máster. Plataforma de encuestas y evaluaciones educativas con análisis asistido por IA (OpenAI). Nombre de producto: **EduQuiz**.

---

## 1. Resumen ejecutivo

- **Stack:** Laravel 12, PHP 8.2+, MySQL/MariaDB, Breeze (Blade), Vite, SB Admin 2 (CSS/JS), ApexCharts (Larapex Charts), DomPDF, FastExcel.
- **Propósito:** Permitir a docentes crear encuestas/cuestionarios, compartirlos por código o invitación, recoger respuestas de estudiantes, generar informes con IA (resumen, hallazgos, recomendaciones) y exportar a PDF. Incluye comparación entre encuestas, recordatorios por email, grupos de estudiantes, reportes y exportaciones Excel.
- **Roles:** `administrador`, `docente`, `estudiante`. Accesos y menús dependen del rol.

---

## 2. Autenticación y usuarios

- **Auth:** Laravel Breeze (Blade): registro, login, verificación de email, recuperación de contraseña, confirmación de contraseña.
- **Roles en `users`:** `role` enum-like (`administrador`, `docente`, `estudiante`). Admin gestiona usuarios; docente crea encuestas y ve sus datos; estudiante puede responder encuestas (por código o enlace).
- **Perfil:** Edición de nombre/email, cambio de contraseña, eliminación de cuenta (modal). Layout SB Admin 2.
- **Middleware:** `auth`, `verified` en rutas de aplicación; rutas de “responder” y “ingresar código” solo `web` (acceso con o sin login según encuesta).

---

## 3. Modelos y base de datos

### 3.1 Usuario y soporte

| Modelo | Descripción |
|--------|-------------|
| **User** | name, email, password, role. Relaciones: quizzes (docente), quizAttempts, quizInvitations (creadas), studentGroups. |
| **Session** | Sesiones Laravel. |
| **Cache / Jobs** | Tablas estándar Laravel. |

### 3.2 Encuestas (Quizzes)

| Modelo | Descripción |
|--------|-------------|
| **Quiz** | Encuesta/cuestionario. user_id (owner), title, description, status (draft\|published\|closed), opens_at, closes_at, max_attempts, require_login, target_audience, randomize_questions, theme_color, settings (JSON), analysis_requested_at, analysis_completed_at. Soft deletes. Relaciones: owner, questions, invitations, attempts, analyses. Método: calculateParticipationRate(). |
| **Question** | Pregunta de una encuesta. quiz_id, title, description, type, position, weight, settings (JSON). Soft deletes. Tipos: multiple_choice, multi_select, true_false, scale, open_text, numeric. Relación: options (QuestionOption). |
| **QuestionOption** | Opción de respuesta. question_id, label, description, value, is_correct, position, metadata. Para opción múltiple/V-F puede marcarse is_correct (evaluativo) o no (encuesta). |
| **QuizInvitation** | Invitación/código para acceder a una encuesta. quiz_id, created_by, code, label, max_uses, uses_count, expires_at, is_active, metadata. Relaciones: quiz, creator, attempts. Métodos: incrementUses(), remainingUses(), hasExpired(), is_valid. Atributo: direct_link (ruta para responder). |
| **QuizAttempt** | Intento de respuesta de un usuario a una encuesta. quiz_id, invitation_id, user_id (nullable), participant_name, participant_email, status, score, max_score, started_at, completed_at, metadata. Relaciones: quiz, invitation, user, answers. |
| **QuizAnswer** | Respuesta a una pregunta dentro de un intento. attempt_id, question_id, question_option_id (nullable), answer_text, answer_number, is_correct, answer_meta. Según tipo: opción elegida, texto libre, número, etc. |
| **QuizAiAnalysis** | Resultado del análisis IA de una encuesta cerrada. quiz_id, status (processing\|completed\|failed), started_at, completed_at, summary, recommendations (JSON), quantitative_insights (JSON), qualitative_themes (JSON), raw_response (JSON), error_message. |
| **QuizComparison** | Comparación guardada entre dos encuestas (para el módulo Comparar). user_id, quiz_a_id, quiz_b_id, ai_analysis (longText), stats_a, stats_b, insights_a, insights_b (JSON), error_message, analyzed_at. Relaciones: user, quizA, quizB. |

### 3.3 Recordatorios y grupos

| Modelo | Descripción |
|--------|-------------|
| **EmailReminder** | Registro de envío de recordatorio. user_id, quiz_id, subject, message, recipients_count, sent_at, status. Relaciones: sender, quiz. |
| **StudentGroup** | Grupo de estudiantes creado por un docente. user_id, name, description. Relación: members (StudentGroupMember). |
| **StudentGroupMember** | Miembro de un grupo. student_group_id, name, email (y posiblemente más campos). |

---

## 4. Tipos de pregunta (Question type)

- **multiple_choice:** Una sola opción (radio). Con opción correcta → evaluativo; sin correcta → encuesta.
- **multi_select:** Varias opciones (checkboxes). Con correctas → evaluativo; sin correctas → encuesta.
- **true_false:** Verdadero/Falso (dos opciones fijas). Misma lógica evaluativo/encuesta.
- **scale:** Escala numérica (ej. 1–5 o 1–7). settings: scale_min, scale_max, scale_step. Se analiza promedio y distribución.
- **open_text:** Texto libre. Se usa para temas cualitativos e IA.
- **numeric:** Valor numérico. Promedio, min, max, distribución.

En el formulario de preguntas hay ayuda contextual por tipo (qué ve el alumno, ejemplo, evaluación) y modal “Guía de tipos de pregunta”.

---

## 5. Módulos y funcionalidad por rol

### 5.1 Dashboard

- **Admin:** KPIs (usuarios, encuestas, respuestas, invitaciones activas), gráfico de actividad semanal de respuestas (líneas), donut de distribución por rol, donut de estado de encuestas (borrador/publicada/cerrada), bloque “Adopción de la plataforma” (encuestas con participación, informes IA generados, docentes activos, uso de invitaciones) con barras de progreso reales, tabla de usuarios recientes, resumen general.
- **Docente:** KPIs (encuestas totales, publicadas, borradores, cerradas, respuestas, invitaciones activas), gráfico de actividad semanal de sus encuestas, donut estado de encuestas (publicadas/borradores/cerradas), listado de encuestas recientes y respuestas recientes, pendientes de análisis IA.
- **Estudiante:** KPIs (encuestas disponibles, completadas, última actividad), actividad semanal propia, intentos recientes.

### 5.2 Encuestas (Quizzes) — Docente / Admin

- **CRUD:** index, create, edit, show, destroy. Crear desde plantilla (createFromTemplate, storeFromTemplate).
- **Estados:** draft → publish (publish) → closed (close). Al cerrar se dispara análisis IA (ProcessQuizAnalysisJob).
- **Preguntas:** resource quizzes.questions (create, edit, store, update, destroy). Orden por position, tipos y opciones según tipo; para scale se configuran scale_min, scale_max, scale_step.
- **Invitaciones:** resource quizzes.invitations (store, update, destroy). Códigos por encuesta, max_uses, uses_count, is_active.
- **Análisis:** Botón “Generar informe” / “Analizar” (analyze) marca analysis_requested_at y lanza el job. Vista “Informe detallado de IA” (analysis): resumen ejecutivo, indicadores, gráficos por pregunta (ApexCharts/Larapex: donut/bar según tipo), hallazgos cuantitativos, temas cualitativos, respuestas abiertas, recomendaciones IA. Exportación a PDF (exportAnalysis): mismo contenido con gráficos generados en servidor (QuickChart.io → imágenes base64) si GD está habilitado; cabecera/pie por página y paginación en DomPDF.
- **Vista show:** Detalle de encuesta, invitaciones, enlaces para responder, botones publicar/cerrar, acceder al informe.

### 5.3 Responder encuestas (flujo estudiante / anónimo)

- **Acceso:** Ruta “ingresar código” (SurveyAccessController: showLinkForm, verifyCode) o enlace directo con código (SurveyResponseController: showSurvey con code).
- **Responder:** Formulario dinámico según tipos de pregunta (respond-form.blade.php, respond.blade.php). Envío vía submitSurvey; se crea QuizAttempt y QuizAnswer por pregunta; si hay invitación se incrementa uses_count.
- **Post-envío:** Redirección a “encuesta completada” (thankyou).

Las rutas de responder están en middleware `web` (no obligatorio login salvo que la encuesta lo exija).

### 5.4 Comparación de encuestas — Docente / Admin

- **Index:** Listado de comparaciones guardadas (tabla: Encuesta A, Encuesta B, fecha, botón Ver). Formulario “Nueva comparación”: selección de dos encuestas (solo cerradas), botones “Comparar resultados” y “Comparar con IA (y guardar)”.
- **Comparar (POST):** Muestra vista resultado con estadísticas lado a lado (statsA, statsB), insights cuantitativos de ambas, y si existe comparación guardada previa, el análisis IA.
- **Comparar con IA (POST):** Mismo flujo pero llama a OpenAI con prompt de comparación pedagógica; guarda/actualiza QuizComparison (quiz_a_id, quiz_b_id normalizados, ai_analysis, stats, insights). Vista resultado con análisis en markdown.
- **Ver comparación guardada:** GET comparisons/{comparison} (show). Muestra la misma vista de resultado usando datos guardados (quizA, quizB, stats_a, stats_b, insights, ai_analysis).

### 5.5 Recordatorios por email — Docente / Admin

- **Crear/envío:** Vista reminders.create; formulario con encuesta, asunto, mensaje, destinatarios (emails o grupo). POST reminders.send (ReminderController). Envía correos con Mailable SurveyReminder (HTML y texto); en el pie del correo se incluye “Enviado por: nombre del docente <email>”. Branding EduQuiz (logo, nombre app).
- **Modelo EmailReminder:** Registro de envíos (user_id, quiz_id, subject, message, recipients_count, sent_at, status).

### 5.6 Grupos de estudiantes — Docente / Admin

- **CRUD:** resource groups (StudentGroupController). index, create, store, edit, update, destroy. show con miembros. Exportación Excel por grupo (exportExcel).

### 5.7 Reportes — Docente / Admin

- **Submenú Reportes:** reports.summary (resumen), reports.students (estudiantes), reports.surveys (redirige a summary). ReportChartsService para gráficos de reportes (series por semana/mes, donut, etc.). Vistas: reports/summary.blade.php, reports/students.blade.php.

### 5.8 Exportaciones Excel

- **Rutas:** exports/surveys (listado encuestas), exports/students (estudiantes), exports/quiz/{quiz}/responses (respuestas de una encuesta). ExportController con FastExcel.

### 5.9 Administración de usuarios — Solo Admin

- **CRUD:** resource admin.users (UserController). index, create, store, edit, update, destroy. StoreUserRequest, UpdateUserRequest. Gestión de nombre, email, rol, contraseña.

---

## 6. Servicios (backend)

| Servicio | Función |
|----------|---------|
| **OpenAIService** | Cliente HTTP para OpenAI Chat Completions. Configuración por .env (API key, modelo, temperatura, max_tokens, perfiles). Usado por ProcessQuizAnalysisJob y por comparación con IA. |
| **QuizAnalyticsService** | buildQuantitativeInsights(Quiz): por cada pregunta según tipo (multiple_choice, multi_select, true_false, scale, numeric, generic) devuelve conteos, porcentajes (multi_select con total por distinct attempt_id), promedios, distribuciones. buildQualitativeInsights(Quiz): respuestas open_text. buildChartConfigs / buildChartConfigsFromInsights: prepara configs para gráficos (pie, bar, horizontal_bar). |
| **PdfChartService** | Genera imágenes de gráficos para el PDF vía QuickChart.io (Chart.js). Por cada insight cuantitativo construye config (doughnut, horizontalBar, bar), obtiene PNG y devuelve mapa question_id => data URI base64. Si PHP no tiene extensión GD, el controlador no pasa imágenes y el PDF se genera solo con tablas/barras CSS. |
| **ReportChartsService** | Construcción de series y gráficos para vistas de reportes (actividad semanal, mensual, donut, etc.) usando Larapex. |

---

## 7. Jobs y colas

- **ProcessQuizAnalysisJob:** Recibe quiz_id. Solo actúa si la encuesta está closed y analysis_requested_at está definido. Carga quiz con questions y attempts; usa QuizAnalyticsService para insights; construye prompt con meta, quantitative y qualitative; llama OpenAIService; parsea JSON de respuesta (summary, recommendations, quantitative_insights, qualitative_themes); actualiza QuizAiAnalysis y quiz.analysis_completed_at. En error actualiza status failed y error_message. Por defecto se puede ejecutar en síncrono (dispatchSync) o en cola (queue:work).

---

## 8. Comandos programados

- **GenerateScheduledReports:** reports:generate-scheduled. Obtiene docentes con encuestas publicadas activas, genera mensaje de resumen por encuesta (títulos e intentos) y envía un correo SurveyReminder por docente (con senderName/senderEmail). No expone rutas web; para cron/scheduler.

---

## 9. Correo electrónico

- **SurveyReminder (Mailable):** Asunto y mensaje personalizados, enlace a encuesta, título de encuesta, senderName y senderEmail. Vista HTML (emails/survey-reminder.blade.php) y texto (survey-reminder-text.blade.php). Usado en recordatorios (ReminderController) y en informe semanal (GenerateScheduledReports). Cabeceras: Precedence bulk, List-Unsubscribe, X-Mailer EduQuiz.

---

## 10. Vistas principales (Blade)

- **Layouts:** app.blade.php (SB Admin 2, sidebar por rol, Reportes colapsable, Chart.js/ApexCharts), guest.blade.php.
- **Dashboard:** dashboard.blade.php; partials admin, teacher, student.
- **Auth:** login, register, verify-email, forgot-password, reset-password, confirm-password.
- **Quizzes:** index, create, edit, show, create-from-template, _form. analysis.blade.php (informe detallado con gráficos). analysis-pdf.blade.php (PDF: portada, KPIs, resumen, hallazgos con gráficos base64, respuestas abiertas, temas cualitativos, recomendaciones; header/footer y paginación vía script PHP de DomPDF).
- **Questions:** create, edit, _form (con ayuda por tipo y modal guía).
- **Surveys (responder):** access (ingresar código), respond, partials/respond-form, thankyou.
- **Comparisons:** index (listado + formulario nueva comparación), result (comparativa y análisis IA).
- **Reminders:** create.
- **Groups:** index, create, edit, show, _form.
- **Reports:** summary, students.
- **Admin users:** index, create, edit.
- **Profile:** edit, partials (update-profile-information, update-password, delete-user-form).
- **Emails:** survey-reminder (HTML), survey-reminder-text (plain).

---

## 11. Rutas (web.php) — Resumen

- **/:** Redirección a dashboard o login.
- **/dashboard:** DashboardController (por rol).
- **Auth:** Rutas de Breeze (login, register, etc.).
- **Perfil:** profile.edit, profile.update, profile.destroy (auth).
- **Quizzes:** resource quizzes; publish, close, analysis (GET/POST), analysis/export (GET); create-from-template (GET/POST); resource quizzes.questions (except index, show); resource quizzes.invitations (only store, update, destroy).
- **Admin:** resource admin.users (except show).
- **Groups:** resource groups; groups/{group}/export.
- **Reminders:** reminders create (GET), send (POST).
- **Comparisons:** index (GET), show (GET), compare (POST), analyzeWithAI (POST).
- **Exports:** exports/surveys, exports/students, exports/quiz/{quiz}/responses.
- **Reports:** reports/summary, reports/students, reports/surveys (redirect).
- **Responder:** ingresar-codigo (GET/POST), responder/{code} (GET/POST), encuesta-completada.

---

## 12. Seguridad y autorización

- **AuthorizesQuizAccess (trait):** ensureQuizOwnership(Quiz), ensureTeacherOrAdmin(). Usado en QuizController, QuestionController, QuizInvitationController, QuizComparisonController, etc.
- **Admin:** Solo administrador accede a admin/users y a datos globales del dashboard/reportes.
- **Docente:** Solo ve y gestiona sus propias encuestas, invitaciones, grupos y comparaciones.
- **CSRF:** Todas las peticiones POST con token.
- **Verificación de email:** Middleware verified en rutas principales.

---

## 13. Frontend y assets

- **CSS/JS:** SB Admin 2 (sb-admin-2.min.css/js), Bootstrap 4, jQuery, Font Awesome, Chart.js, ApexCharts (Larapex). Vite (resources/css/app.css, resources/js/app.js). Layout carga Larapex CDN y Chart.js.
- **Componentes Blade:** x-app-layout, x-slot, inputs (text-input, etc.), botones, modal. Formularios con @csrf y validación @error.
- **Modal global:** globalLoaderModal para formularios con clase js-show-loader (evitar doble envío en publicar/cerrar/analizar/perfil, etc.).

---

## 14. Configuración relevante (.env / config)

- **APP_NAME:** EduQuiz.
- **DB_*:** Conexión MySQL.
- **OPENAI_API_KEY, OPENAI_MODEL_1, OPENAI_TEMP_1, OPENAI_MAXTOKENS_1** (y opcionalmente organization, base_url, profiles). config/services.php → openai.
- **MAIL_*:** Driver, from, para envío de recordatorios e informes semanales.
- **Queue:** Por defecto sync; si se usa database/redis, ejecutar queue:work para ProcessQuizAnalysisJob.
- **DomPDF:** config/dompdf.php (publicado). enable_php true para header/footer y paginación en PDF. GD recomendado para incrustar imágenes de gráficos.

---

## 15. Seeders (orden de ejecución)

1. **UserSeeder:** 1 admin, 5 docentes, 20 estudiantes (emails/passwords ejemplo).
2. **QuizSeeder:** Encuestas de ejemplo asociadas a docentes (draft, published, closed), con preguntas y opciones de distintos tipos.
3. **QuizInvitationSeeder:** Invitaciones por encuesta (códigos, is_active, uses_count 0).
4. **QuizAttemptSeeder:** Intentos y respuestas (QuizAnswer) para encuestas; incrementa uses_count en invitaciones.
5. **StudentGroupSeeder:** Grupos de estudiantes con miembros (nombre, email).
6. **ComparisonQuizSeeder:** Encuestas comparativas (ej. 2025 vs 2026) e intentos adicionales.

**AdminUserSeeder:** Llamado desde UserSeeder o por separado; asegura usuario admin@example.com. Contraseña de ejemplo: `password`.

---

## 16. Dependencias Composer

- laravel/framework ^12
- arielmejiadev/larapex-charts ^2.1 (gráficos ApexCharts)
- barryvdh/laravel-dompdf ^3.1 (PDF)
- rap2hpoutre/fast-excel ^5.6 (Excel)
- laravel/breeze ^2.3 (dev, auth)
- fakerphp/faker, laravel/pint, phpunit, etc. (dev)

---

## 17. Pruebas

- PHPUnit. Tests en tests/Feature y tests/Unit (Auth, Profile, Example). No se listan todos los casos aquí; estructura estándar Laravel.

---

## 18. Resumen de flujos de negocio

1. **Docente crea encuesta** (borrador) → Añade preguntas (varios tipos, opciones, correctas o no) → Publica → Crea invitaciones/códigos → Comparte enlace o código.
2. **Estudiante (o anónimo)** entra por “ingresar código” o enlace → Responde → Envío crea attempt + answers → Gracias.
3. **Docente cierra encuesta** → Se dispara ProcessQuizAnalysisJob → OpenAI genera informe → Se guarda en QuizAiAnalysis y analysis_completed_at.
4. **Docente ve informe** en análisis (gráficos, hallazgos, recomendaciones) y puede exportar PDF (con o sin imágenes de gráficos según GD).
5. **Docente compara dos encuestas** (cerradas) → Ve estadísticas; opcionalmente “Comparar con IA” → Se guarda QuizComparison y se muestra análisis comparativo.
6. **Docente envía recordatorios** por email (lista o grupo) con SurveyReminder (incluye “Enviado por”).
7. **Admin** ve dashboard con adopción real, estado de encuestas, usuarios; gestiona usuarios; puede usar reportes y exportaciones.

---

Este documento describe la totalidad del proyecto EduQuiz para que una herramienta externa (por ejemplo ChatGPT) tenga contexto completo del proyecto universitario de fin de máster: modelos, rutas, roles, módulos, servicios, jobs, correo, PDF, comparaciones, recordatorios, grupos, reportes y exportaciones.




---
Esto es lo que queda pendiente para abarcar la totalidad prometida en el PDI aquie GPT nos da un pront de lo que faltaria.


"Necesito implementar la última funcionalidad pendiente del sistema de análisis de cuestionarios (Laravel).

Contexto:
El sistema permite dos tipos de preguntas:
1) Evaluativas → cuando la pregunta tiene al menos una opción marcada como correcta.
2) Encuesta → cuando la pregunta no tiene opciones correctas.

Objetivo:
Implementar el cálculo del índice de error por pregunta SOLO para preguntas evaluativas y mostrar “No aplica (encuesta)” en las demás.

----------------------------------------
1) Detectar tipo de pregunta
----------------------------------------

Una pregunta es evaluativa si:
QuestionOption::where('question_id', $question->id)
              ->where('is_correct', true)
              ->exists();

Si no existen opciones correctas → es encuesta.

Agregar método en el modelo Question:

public function isEvaluative(): bool

----------------------------------------
2) Cálculo del índice de error
----------------------------------------

Para preguntas evaluativas:

Total respuestas = número de QuizAnswer de la pregunta

Respuestas correctas:
QuizAnswer donde is_correct = true

Cálculos:
accuracy = (correct / total) * 100
error_rate = 100 - accuracy

Si total = 0 → devolver null

Guardar estos valores en el servicio de análisis:
QuizAnalyticsService

Agregar en el resultado:

'accuracy' => porcentaje_correctas
'error_rate' => porcentaje_error

----------------------------------------
3) Preguntas de encuesta
----------------------------------------

Si la pregunta NO es evaluativa:

No calcular métricas.

En la respuesta del análisis devolver:

'is_evaluative' => false
'accuracy' => null
'error_rate' => null
'note' => 'No aplica (encuesta)'

----------------------------------------
4) Vista (Blade)
----------------------------------------

En el análisis por pregunta:

Si is_evaluative == true:
Mostrar:
- Porcentaje de aciertos
- Porcentaje de error

Si is_evaluative == false:
Mostrar:
"No aplica (encuesta)"

----------------------------------------
5) PDF
----------------------------------------

Aplicar la misma lógica en analysis-pdf.blade.php:

Si evaluativa:
Mostrar métricas de acierto/error

Si encuesta:
Mostrar:
"No aplica (encuesta)"

----------------------------------------
6) Opcional (si es fácil)
----------------------------------------

En el resumen general:
Mostrar TOP 3 preguntas con mayor error
(solo evaluativas)

----------------------------------------
Entregables
----------------------------------------

- Archivos modificados
- Cambios en QuizAnalyticsService
- Método isEvaluative() en Question
- Cambios en Blade
- Cambios en PDF
- Confirmar que no afecta preguntas existentes
"