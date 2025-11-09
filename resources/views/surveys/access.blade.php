<x-guest-layout>
    <div class="row">
        <div class="col-lg-12">
            <div class="p-5">
                <div class="text-center mb-4">
                    <h1 class="h4 text-gray-900">{{ __('Accede a una encuesta') }}</h1>
                    <p class="text-muted small mb-0">{{ __('Ingresa el código que te compartió tu docente para completar la encuesta correspondiente.') }}</p>
                </div>

                @if (session('status'))
                    <div class="alert alert-success small" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <form class="user" method="POST" action="{{ route('surveys.access.verify') }}">
                    @csrf

                    <div class="form-group">
                        <input type="text" name="code" value="{{ old('code') }}" class="form-control form-control-user text-uppercase @error('code') is-invalid @enderror" placeholder="{{ __('Código de invitación') }}" maxlength="20" required autofocus>
                        @error('code')
                            <span class="invalid-feedback d-block" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-primary btn-user btn-block">
                        {{ __('Continuar') }}
                    </button>
                </form>

                <hr>

                <div class="text-center">
                    <a class="small" href="{{ route('login') }}">{{ __('¿Eres docente o estudiante registrado? Inicia sesión aquí') }}</a>
                </div>
                <div class="text-center">
                    <a class="small" href="{{ route('register') }}">{{ __('¿Aún no tienes cuenta? Regístrate') }}</a>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>


