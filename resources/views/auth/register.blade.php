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
                                        <h1 class="h3 font-weight-bold text-gray-900 mb-2">{{ __('Crea tu cuenta') }}</h1>
                                        <p class="text-muted">{{ __('Únete para crear encuestas, invitar estudiantes y analizar resultados con IA.') }}</p>
                                    </div>

                                    <form class="user" method="POST" action="{{ route('register') }}">
                                        @csrf

                                        <div class="form-group">
                                            <input id="name" 
                                                   type="text" 
                                                   class="form-control form-control-user @error('name') is-invalid @enderror" 
                                                   name="name" 
                                                   value="{{ old('name') }}" 
                                                   placeholder="{{ __('Nombre completo') }}" 
                                                   required 
                                                   autofocus 
                                                   autocomplete="name">
                                            @error('name')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <input id="email" 
                                                   type="email" 
                                                   class="form-control form-control-user @error('email') is-invalid @enderror" 
                                                   name="email" 
                                                   value="{{ old('email') }}" 
                                                   placeholder="{{ __('Correo electrónico') }}" 
                                                   required 
                                                   autocomplete="username">
                                            @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="form-group">
                                            <select id="role" 
                                                    name="role" 
                                                    class="form-control form-control-user @error('role') is-invalid @enderror">
                                                <option value="{{ \App\Models\User::ROLE_STUDENT }}" @selected(old('role', \App\Models\User::ROLE_STUDENT) === \App\Models\User::ROLE_STUDENT)>
                                                    {{ __('Estudiante') }}
                                                </option>
                                                <option value="{{ \App\Models\User::ROLE_TEACHER }}" @selected(old('role') === \App\Models\User::ROLE_TEACHER)>
                                                    {{ __('Docente') }}
                                                </option>
                                            </select>
                                            @error('role')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <div class="form-group position-relative">
                                            <input id="password" 
                                                   type="password" 
                                                   class="form-control form-control-user @error('password') is-invalid @enderror" 
                                                   name="password" 
                                                   placeholder="{{ __('Contraseña') }}" 
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

                                        <div class="form-group position-relative">
                                            <input id="password_confirmation" 
                                                   type="password" 
                                                   class="form-control form-control-user" 
                                                   name="password_confirmation" 
                                                   placeholder="{{ __('Confirmar contraseña') }}" 
                                                   required 
                                                   autocomplete="new-password">
                                            <button type="button" class="btn btn-link position-absolute" id="togglePasswordConfirmation" style="right: 10px; top: 50%; transform: translateY(-50%); padding: 0; border: none; background: none; color: #6c757d; z-index: 10; cursor: pointer;">
                                                <i class="fas fa-eye" id="eyeIconConfirmation"></i>
                                            </button>
                                        </div>

                                        <button type="submit" class="btn btn-primary btn-user btn-block">
                                            <i class="fas fa-user-plus mr-2"></i>{{ __('Registrarme') }}
                                        </button>
                                    </form>

                                    <hr class="my-4">

                                    <div class="text-center">
                                        <a class="small text-primary" href="{{ route('login') }}">
                                            <i class="fas fa-sign-in-alt mr-1"></i>{{ __('¿Ya tienes cuenta? Inicia sesión') }}
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
