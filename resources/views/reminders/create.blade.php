<x-app-layout>
    <x-slot name="header">{{ __('Enviar recordatorios') }}</x-slot>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-envelope mr-1"></i>{{ __('Componer recordatorio') }}
                    </h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('reminders.send') }}" method="POST" class="js-show-loader">
                        @csrf

                        <div class="form-group">
                            <label for="quiz_id" class="font-weight-bold">{{ __('Encuesta relacionada') }} <small class="text-muted">({{ __('opcional') }})</small></label>
                            <select name="quiz_id" id="quiz_id" class="form-control @error('quiz_id') is-invalid @enderror">
                                <option value="">{{ __('— Ninguna (mensaje general) —') }}</option>
                                @foreach ($quizzes as $quiz)
                                    <option value="{{ $quiz->id }}" @selected(old('quiz_id') == $quiz->id)>
                                        {{ $quiz->title }} ({{ $quiz->status }})
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">{{ __('Si seleccionas una encuesta, se incluirá el enlace para responderla.') }}</small>
                            @error('quiz_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="subject" class="font-weight-bold">{{ __('Asunto') }}</label>
                            <input type="text" name="subject" id="subject" value="{{ old('subject') }}"
                                   class="form-control @error('subject') is-invalid @enderror" required
                                   placeholder="{{ __('Ej: Recordatorio - Encuesta pendiente') }}">
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="message" class="font-weight-bold">{{ __('Mensaje') }}</label>
                            <textarea name="message" id="message" rows="5"
                                      class="form-control @error('message') is-invalid @enderror" required
                                      placeholder="{{ __('Escribe el mensaje que recibirán los destinatarios...') }}">{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>

                        <h6 class="font-weight-bold text-primary">{{ __('Destinatarios') }}</h6>

                        @if ($groups->isNotEmpty())
                            <div class="form-group">
                                <label class="font-weight-bold">{{ __('Seleccionar grupos') }}</label>
                                <div class="row">
                                    @foreach ($groups as $group)
                                        <div class="col-md-6 mb-2">
                                            <div class="custom-control custom-checkbox">
                                                <input type="checkbox" class="custom-control-input" id="group_{{ $group->id }}"
                                                       name="groups[]" value="{{ $group->id }}"
                                                       @checked(is_array(old('groups')) && in_array($group->id, old('groups')))>
                                                <label class="custom-control-label" for="group_{{ $group->id }}">
                                                    {{ $group->name }}
                                                    <span class="badge badge-primary ml-1">{{ $group->members_count }}</span>
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                                @error('groups')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        @else
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                {{ __('No tienes grupos creados.') }}
                                <a href="{{ route('groups.create') }}">{{ __('Crear grupo') }}</a>
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="individual_emails" class="font-weight-bold">{{ __('Correos individuales') }} <small class="text-muted">({{ __('opcional') }})</small></label>
                            <textarea name="individual_emails" id="individual_emails" rows="3"
                                      class="form-control @error('individual_emails') is-invalid @enderror"
                                      placeholder="{{ __('Ingresa correos separados por coma, punto y coma o línea nueva') }}">{{ old('individual_emails') }}</textarea>
                            <small class="form-text text-muted">{{ __('Ejemplo: alumno1@email.com, alumno2@email.com') }}</small>
                            @error('individual_emails')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-paper-plane mr-1"></i>{{ __('Enviar recordatorio') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-info">
                        <i class="fas fa-history mr-1"></i>{{ __('Historial reciente') }}
                    </h6>
                </div>
                <div class="card-body">
                    @if ($history->isEmpty())
                        <p class="text-muted small text-center py-3">{{ __('No has enviado recordatorios aún.') }}</p>
                    @else
                        <div class="list-group list-group-flush">
                            @foreach ($history as $reminder)
                                <div class="list-group-item px-0">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1 small font-weight-bold">{{ $reminder->subject }}</h6>
                                            <p class="mb-0 small text-muted">
                                                <i class="fas fa-users mr-1"></i>{{ $reminder->recipients_count }} {{ __('destinatarios') }}
                                            </p>
                                            @if ($reminder->quiz)
                                                <small class="text-muted">{{ $reminder->quiz->title }}</small>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="badge badge-{{ $reminder->status === 'sent' ? 'success' : 'danger' }}">
                                                {{ $reminder->status === 'sent' ? __('Enviado') : __('Error') }}
                                            </span>
                                            <br>
                                            <small class="text-muted">{{ $reminder->sent_at?->diffForHumans() }}</small>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

