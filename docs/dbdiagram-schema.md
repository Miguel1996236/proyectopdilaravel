// ============================================
// EduQuiz – DBML optimizado para dbdiagram.io
// ============================================

Table users {
  id bigint [pk, increment]
  name varchar
  email varchar [unique]
  email_verified_at timestamp
  role varchar [default: 'estudiante']
  password varchar
  remember_token varchar
  created_at timestamp
  updated_at timestamp
}

Table quizzes {
  id bigint [pk, increment]
  user_id bigint
  title varchar
  description text
  status varchar [default: 'draft']
  opens_at timestamp
  closes_at timestamp
  max_attempts int
  require_login bool
  target_audience varchar
  randomize_questions bool
  theme_color varchar
  settings text
  analysis_requested_at timestamp
  analysis_completed_at timestamp
  created_at timestamp
  updated_at timestamp
  deleted_at timestamp
}

Table questions {
  id bigint [pk, increment]
  quiz_id bigint
  title varchar
  description text
  type varchar
  position int
  weight int
  settings text
  created_at timestamp
  updated_at timestamp
  deleted_at timestamp
}

Table question_options {
  id bigint [pk, increment]
  question_id bigint
  label varchar
  description text
  value varchar
  is_correct bool
  position int
  metadata text
  created_at timestamp
  updated_at timestamp
}

Table quiz_invitations {
  id bigint [pk, increment]
  quiz_id bigint
  created_by bigint
  code varchar [unique]
  label varchar
  max_uses int
  uses_count int
  expires_at timestamp
  is_active bool
  metadata text
  created_at timestamp
  updated_at timestamp
}

Table quiz_attempts {
  id bigint [pk, increment]
  quiz_id bigint
  invitation_id bigint
  user_id bigint
  participant_name varchar
  participant_email varchar
  status varchar
  score decimal
  max_score decimal
  started_at timestamp
  completed_at timestamp
  metadata text
  created_at timestamp
  updated_at timestamp
}

Table quiz_answers {
  id bigint [pk, increment]
  attempt_id bigint
  question_id bigint
  question_option_id bigint
  answer_text text
  answer_number decimal
  is_correct bool
  answer_meta text
  created_at timestamp
  updated_at timestamp
}

Table quiz_ai_analyses {
  id bigint [pk, increment]
  quiz_id bigint
  status varchar
  summary text
  recommendations text
  quantitative_insights text
  qualitative_themes text
  raw_response text
  error_message text
  started_at timestamp
  completed_at timestamp
  created_at timestamp
  updated_at timestamp
}

Table student_groups {
  id bigint [pk, increment]
  user_id bigint
  name varchar
  description text
  created_at timestamp
  updated_at timestamp
}

Table student_group_members {
  id bigint [pk, increment]
  student_group_id bigint
  name varchar
  email varchar
  user_id bigint
  created_at timestamp
  updated_at timestamp
}

Table email_reminders {
  id bigint [pk, increment]
  user_id bigint
  quiz_id bigint
  subject varchar
  message text
  recipients_count int
  sent_at timestamp
  status varchar
  created_at timestamp
  updated_at timestamp
}

Table quiz_comparisons {
  id bigint [pk, increment]
  user_id bigint
  quiz_a_id bigint
  quiz_b_id bigint
  ai_analysis text
  stats_a text
  stats_b text
  insights_a text
  insights_b text
  error_message text
  analyzed_at timestamp
  created_at timestamp
  updated_at timestamp
}


// ============================================
// RELACIONES (mejor declararlas así)
// ============================================

Ref: quizzes.user_id > users.id

Ref: questions.quiz_id > quizzes.id
Ref: question_options.question_id > questions.id

Ref: quiz_invitations.quiz_id > quizzes.id
Ref: quiz_invitations.created_by > users.id

Ref: quiz_attempts.quiz_id > quizzes.id
Ref: quiz_attempts.invitation_id > quiz_invitations.id
Ref: quiz_attempts.user_id > users.id

Ref: quiz_answers.attempt_id > quiz_attempts.id
Ref: quiz_answers.question_id > questions.id
Ref: quiz_answers.question_option_id > question_options.id

Ref: quiz_ai_analyses.quiz_id > quizzes.id

Ref: student_groups.user_id > users.id
Ref: student_group_members.student_group_id > student_groups.id
Ref: student_group_members.user_id > users.id

Ref: email_reminders.user_id > users.id
Ref: email_reminders.quiz_id > quizzes.id

Ref: quiz_comparisons.user_id > users.id
Ref: quiz_comparisons.quiz_a_id > quizzes.id
Ref: quiz_comparisons.quiz_b_id > quizzes.id
