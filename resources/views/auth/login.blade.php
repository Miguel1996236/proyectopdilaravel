<x-guest-layout>
    <div class="row">
        <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
        <div class="col-lg-6">
            <div class="p-5">
                <div class="text-center">
                    <h1 class="h4 text-gray-900 mb-4">{{ __('Bienvenido de nuevo') }}</h1>
                    <p class="text-muted small mb-4">{{ __('Inicia sesión para gestionar tus encuestas e invitaciones.') }}</p>
                </div>

                @if (session('status'))
                    <div class="alert alert-success small" role="alert">
                        {{ session('status') }}
                    </div>
                @endif

                <form class="user" method="POST" action="{{ route('login') }}">
                    @csrf

                    <div class="form-group">
                        <input id="email" class="form-control form-control-user @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email') }}" placeholder="{{ __('Correo electrónico') }}" required autofocus autocomplete="username">
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <input id="password" class="form-control form-control-user @error('password') is-invalid @enderror" type="password" name="password" placeholder="{{ __('Contraseña') }}" required autocomplete="current-password">
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <div class="custom-control custom-checkbox small">
                            <input type="checkbox" class="custom-control-input" id="remember_me" name="remember">
                            <label class="custom-control-label" for="remember_me">{{ __('Recuérdame') }}</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-user btn-block">
                        {{ __('Iniciar sesión') }}
                    </button>
                </form>

                <hr>

                <div class="text-center">
                    @if (Route::has('password.request'))
                        <a class="small" href="{{ route('password.request') }}">{{ __('¿Olvidaste tu contraseña?') }}</a>
                    @endif
                </div>
                <div class="text-center">
                    <a class="small" href="{{ route('register') }}">{{ __('Crear una cuenta nueva') }}</a>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
