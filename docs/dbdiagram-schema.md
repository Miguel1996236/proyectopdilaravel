# Esquema de base de datos – EduQuiz (DBML para dbdiagram.io)

Copia todo el bloque de código que está debajo de la línea "```dbml" y pégalo en [dbdiagram.io](https://dbdiagram.io) para generar el diagrama.

---

```dbml
// ============================================
// EduQuiz – Esquema derivado de migraciones Laravel
// ============================================

Table users {
  id bigint [pk, increment, note: 'PK']
  name varchar(255) [not null]
  email varchar(255) [unique, not null]
  email_verified_at timestamp [null]
  role varchar(20) [default: 'estudiante', note: 'administrador, docente, estudiante']
  password varchar(255) [not null]
  remember_token varchar(100) [null]
  created_at timestamp [not null]
  updated_at timestamp [not null]
  
  Note: 'Usuarios del sistema (admin, docentes, estudiantes)'
}

Table password_reset_tokens {
  email varchar(255) [pk]
  token varchar(255) [not null]
  created_at timestamp [null]
}

Table sessions {
  id varchar(255) [pk]
  user_id bigint [null, ref: > users.id]
  ip_address varchar(45) [null]
  user_agent text [null]
  payload text [not null]
  last_activity int [not null]
}

Table cache {
  key varchar(255) [pk]
  value longtext [not null]
  expiration int [not null]
}

Table cache_locks {
  key varchar(255) [pk]
  owner varchar(255) [not null]
  expiration int [not null]
}

Table jobs {
  id bigint [pk, increment]
  queue varchar(255) [not null]
  payload longtext [not null]
  attempts smallint [not null]
  reserved_at int [null]
  available_at int [not null]
  created_at int [not null]
}

Table job_batches {
  id varchar(255) [pk]
  name varchar(255) [not null]
  total_jobs int [not null]
  pending_jobs int [not null]
  failed_jobs int [not null]
  failed_job_ids longtext [not null]
  options longtext [null]
  cancelled_at int [null]
  created_at int [not null]
  finished_at int [null]
}

Table failed_jobs {
  id bigint [pk, increment]
  uuid varchar(255) [unique, not null]
  connection text [not null]
  queue text [not null]
  payload longtext [not null]
  exception longtext [not null]
  failed_at timestamp [not null]
}

Table quizzes {
  id bigint [pk, increment]
  user_id bigint [not null, ref: > users.id]
  title varchar(255) [not null]
  description text [null]
  status varchar(20) [default: 'draft', note: 'draft, published, closed']
  opens_at timestamp [null]
  closes_at timestamp [null]
  max_attempts smallint [default: 1]
  require_login boolean [default: true]
  target_audience varchar(50) [default: 'all']
  randomize_questions boolean [default: false]
  theme_color varchar(7) [default: '#4e73df']
  settings json [null]
  analysis_requested_at timestamp [null]
  analysis_completed_at timestamp [null]
  created_at timestamp [not null]
  updated_at timestamp [not null]
  deleted_at timestamp [null]
  
  Note: 'Encuestas/Cuestionarios creados por docentes'
}

Table questions {
  id bigint [pk, increment]
  quiz_id bigint [not null, ref: > quizzes.id]
  title varchar(255) [not null]
  description text [null]
  type varchar(30) [default: 'multiple_choice', note: 'multiple_choice, multi_select, scale, open_text, numeric, true_false']
  position int [default: 1]
  weight smallint [default: 1]
  settings json [null]
  created_at timestamp [not null]
  updated_at timestamp [not null]
  deleted_at timestamp [null]
}

Table question_options {
  id bigint [pk, increment]
  question_id bigint [not null, ref: > questions.id]
  label varchar(255) [not null]
  description text [null]
  value varchar(255) [null]
  is_correct boolean [default: false]
  position int [default: 1]
  metadata json [null]
  created_at timestamp [not null]
  updated_at timestamp [not null]
}

Table quiz_invitations {
  id bigint [pk, increment]
  quiz_id bigint [not null, ref: > quizzes.id]
  created_by bigint [null, ref: > users.id]
  code varchar(20) [unique, not null]
  label varchar(255) [null]
  max_uses int [null]
  uses_count int [default: 0]
  expires_at timestamp [null]
  is_active boolean [default: true]
  metadata json [null]
  created_at timestamp [not null]
  updated_at timestamp [not null]
}

Table quiz_attempts {
  id bigint [pk, increment]
  quiz_id bigint [not null, ref: > quizzes.id]
  invitation_id bigint [null, ref: > quiz_invitations.id]
  user_id bigint [null, ref: > users.id]
  participant_name varchar(255) [null]
  participant_email varchar(255) [null]
  status varchar(20) [default: 'pending', note: 'pending, in_progress, completed, cancelled']
  score decimal(8,2) [null]
  max_score decimal(8,2) [null]
  started_at timestamp [null]
  completed_at timestamp [null]
  metadata json [null]
  created_at timestamp [not null]
  updated_at timestamp [not null]
}

Table quiz_answers {
  id bigint [pk, increment]
  attempt_id bigint [not null, ref: > quiz_attempts.id]
  question_id bigint [not null, ref: > questions.id]
  question_option_id bigint [null, ref: > question_options.id]
  answer_text text [null]
  answer_number decimal(10,3) [null]
  is_correct boolean [null]
  answer_meta json [null]
  created_at timestamp [not null]
  updated_at timestamp [not null]
  
  indexes {
    (attempt_id, question_id)
  }
}

Table quiz_ai_analyses {
  id bigint [pk, increment]
  quiz_id bigint [not null, ref: > quizzes.id]
  status varchar(50) [default: 'pending']
  summary text [null]
  recommendations text [null]
  quantitative_insights json [null]
  qualitative_themes json [null]
  raw_response json [null]
  error_message text [null]
  started_at timestamp [null]
  completed_at timestamp [null]
  created_at timestamp [not null]
  updated_at timestamp [not null]
  
  indexes {
    (quiz_id, status)
  }
}

Table student_groups {
  id bigint [pk, increment]
  user_id bigint [not null, ref: > users.id]
  name varchar(255) [not null]
  description text [null]
  created_at timestamp [not null]
  updated_at timestamp [not null]
  
  Note: 'Grupos de estudiantes (dueño: docente)'
}

Table student_group_members {
  id bigint [pk, increment]
  student_group_id bigint [not null, ref: > student_groups.id]
  name varchar(255) [not null]
  email varchar(255) [not null]
  user_id bigint [null, ref: > users.id]
  created_at timestamp [not null]
  updated_at timestamp [not null]
  
  indexes {
    (student_group_id, email) [unique]
  }
}

Table email_reminders {
  id bigint [pk, increment]
  user_id bigint [not null, ref: > users.id]
  quiz_id bigint [null, ref: > quizzes.id]
  subject varchar(255) [not null]
  message text [not null]
  recipients_count int [default: 0]
  sent_at timestamp [null]
  status varchar(50) [default: 'sent']
  created_at timestamp [not null]
  updated_at timestamp [not null]
}

Table quiz_comparisons {
  id bigint [pk, increment]
  user_id bigint [not null, ref: > users.id]
  quiz_a_id bigint [not null, ref: > quizzes.id]
  quiz_b_id bigint [not null, ref: > quizzes.id]
  ai_analysis longtext [null]
  stats_a json [null]
  stats_b json [null]
  insights_a json [null]
  insights_b json [null]
  error_message text [null]
  analyzed_at timestamp [null]
  created_at timestamp [not null]
  updated_at timestamp [not null]
  
  indexes {
    (user_id, quiz_a_id, quiz_b_id) [unique]
  }
}
```

---

## Cómo usarlo en dbdiagram.io

1. Entra en **https://dbdiagram.io**
2. Crea un nuevo diagrama o abre uno existente.
3. En el panel izquierdo (código DBML), **borra** el contenido de ejemplo.
4. **Pega** todo el código que está entre \`\`\`dbml y \`\`\` (sin incluir esas líneas si el editor no las acepta; en ese caso pega solo las líneas de `Table` y `Ref`).
5. El diagrama se generará automáticamente.

Si dbdiagram.io no reconoce algún tipo (por ejemplo `longtext`), cámbialo por `text`. Las referencias `ref: > tabla.id` ya indican las relaciones para dibujar las líneas entre tablas.
