<x-guest-layout>
    <div class="row no-gutters min-vh-100" style="position: relative; z-index: 2;">
        <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
        <div class="col-lg-6 d-flex align-items-center">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-xl-8 col-lg-10">
                        <div class="card o-hidden border-0 shadow-lg">
                            <div class="card-body p-5">
                                <div class="text-center mb-4">
                                    <h1 class="h3 font-weight-bold text-gray-900 mb-2">{{ __('Bienvenido de nuevo') }}</h1>
                                    <p class="text-muted">{{ __('Inicia sesión para gestionar tus encuestas e invitaciones.') }}</p>
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

                                    <div class="form-group position-relative">
                                        <input id="password" class="form-control form-control-user @error('password') is-invalid @enderror" type="password" name="password" placeholder="{{ __('Contraseña') }}" required autocomplete="current-password">
                                        <button type="button" class="btn btn-link position-absolute" id="togglePassword" style="right: 10px; top: 50%; transform: translateY(-50%); padding: 0; border: none; background: none; color: #6c757d; z-index: 10; cursor: pointer;">
                                            <i class="fas fa-eye" id="eyeIcon"></i>
                                        </button>
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

                                <hr class="my-4">

                                <div class="text-center">
                                    @if (Route::has('password.request'))
                                        <a class="small text-primary" href="{{ route('password.request') }}">{{ __('¿Olvidaste tu contraseña?') }}</a>
                                    @endif
                                </div>
                                <div class="text-center mt-2">
                                    <a class="small text-primary" href="{{ route('register') }}">{{ __('Crear una cuenta nueva') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');

            if (togglePassword && passwordInput && eyeIcon) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    
                    // Cambiar icono
                    if (type === 'password') {
                        eyeIcon.classList.remove('fa-eye-slash');
                        eyeIcon.classList.add('fa-eye');
                    } else {
                        eyeIcon.classList.remove('fa-eye');
                        eyeIcon.classList.add('fa-eye-slash');
                    }
                });
            }
        });
    </script>
    @endpush
</x-guest-layout>
