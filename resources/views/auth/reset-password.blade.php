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
                                        <h1 class="h3 font-weight-bold text-gray-900 mb-2">{{ __('Restablecer contraseña') }}</h1>
                                        <p class="text-muted">{{ __('Ingresa tu nueva contraseña para completar el restablecimiento.') }}</p>
                                    </div>

                                    <form method="POST" action="{{ route('password.store') }}">
                                        @csrf

                                        <!-- Password Reset Token -->
                                        <input type="hidden" name="token" value="{{ $request->route('token') }}">

                                        <!-- Email Address -->
                                        <div class="form-group">
                                            <input id="email" 
                                                   class="form-control form-control-user @error('email') is-invalid @enderror" 
                                                   type="email" 
                                                   name="email" 
                                                   value="{{ old('email', $request->email) }}" 
                                                   placeholder="{{ __('Correo electrónico') }}" 
                                                   required 
                                                   autofocus 
                                                   autocomplete="username">
                                            @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <!-- Password -->
                                        <div class="form-group position-relative">
                                            <input id="password" 
                                                   class="form-control form-control-user @error('password') is-invalid @enderror" 
                                                   type="password" 
                                                   name="password" 
                                                   placeholder="{{ __('Nueva contraseña') }}" 
                                                   required 
                                                   autocomplete="new-password">
                                            <button type="button" class="btn btn-link position-absolute" id="togglePassword" style="right: 10px; top: 50%; transform: translateY(-50%); padding: 0; border: none; background: none; color: #6c757d; z-index: 10; cursor: pointer;">
                                                <i class="fas fa-eye" id="eyeIcon"></i>
                                            </button>
                                            @error('password')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <!-- Confirm Password -->
                                        <div class="form-group position-relative">
                                            <input id="password_confirmation" 
                                                   class="form-control form-control-user @error('password_confirmation') is-invalid @enderror" 
                                                   type="password" 
                                                   name="password_confirmation" 
                                                   placeholder="{{ __('Confirmar nueva contraseña') }}" 
                                                   required 
                                                   autocomplete="new-password">
                                            <button type="button" class="btn btn-link position-absolute" id="togglePasswordConfirmation" style="right: 10px; top: 50%; transform: translateY(-50%); padding: 0; border: none; background: none; color: #6c757d; z-index: 10; cursor: pointer;">
                                                <i class="fas fa-eye" id="eyeIconConfirmation"></i>
                                            </button>
                                            @error('password_confirmation')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <button type="submit" class="btn btn-primary btn-user btn-block">
                                            <i class="fas fa-key mr-2"></i>{{ __('Restablecer contraseña') }}
                                        </button>
                                    </form>

                                    <hr class="my-4">

                                    <div class="text-center">
                                        <a class="small text-primary" href="{{ route('login') }}">
                                            <i class="fas fa-arrow-left mr-1"></i>{{ __('Volver al inicio de sesión') }}
                                        </a>
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
            // Toggle para contraseña
            const togglePassword = document.getElementById('togglePassword');
            const passwordInput = document.getElementById('password');
            const eyeIcon = document.getElementById('eyeIcon');

            if (togglePassword && passwordInput && eyeIcon) {
                togglePassword.addEventListener('click', function() {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);
                    
                    if (type === 'password') {
                        eyeIcon.classList.remove('fa-eye-slash');
                        eyeIcon.classList.add('fa-eye');
                    } else {
                        eyeIcon.classList.remove('fa-eye');
                        eyeIcon.classList.add('fa-eye-slash');
                    }
                });
            }

            // Toggle para confirmación de contraseña
            const togglePasswordConfirmation = document.getElementById('togglePasswordConfirmation');
            const passwordConfirmationInput = document.getElementById('password_confirmation');
            const eyeIconConfirmation = document.getElementById('eyeIconConfirmation');

            if (togglePasswordConfirmation && passwordConfirmationInput && eyeIconConfirmation) {
                togglePasswordConfirmation.addEventListener('click', function() {
                    const type = passwordConfirmationInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordConfirmationInput.setAttribute('type', type);
                    
                    if (type === 'password') {
                        eyeIconConfirmation.classList.remove('fa-eye-slash');
                        eyeIconConfirmation.classList.add('fa-eye');
                    } else {
                        eyeIconConfirmation.classList.remove('fa-eye');
                        eyeIconConfirmation.classList.add('fa-eye-slash');
                    }
                });
            }
        });
    </script>
    @endpush
</x-guest-layout>
