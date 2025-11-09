<x-guest-layout>
    <div class="row">
        <div class="col-lg-6 d-none d-lg-block bg-register-image"></div>
        <div class="col-lg-6">
            <div class="p-5">
                <div class="text-center">
                    <h1 class="h4 text-gray-900 mb-4">{{ __('Crea tu cuenta') }}</h1>
                    <p class="text-muted small mb-4">{{ __('Únete para crear encuestas, invitar estudiantes y analizar resultados con IA.') }}</p>
                </div>
                <form class="user" method="POST" action="{{ route('register') }}">
                    @csrf

                    <div class="form-group">
                        <input id="name" type="text" class="form-control form-control-user @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" placeholder="{{ __('Nombre completo') }}" required autofocus autocomplete="name">
                        @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <input id="email" type="email" class="form-control form-control-user @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" placeholder="{{ __('Correo electrónico') }}" required autocomplete="username">
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <select id="role" name="role" class="form-control @error('role') is-invalid @enderror">
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

                    <div class="form-group row">
                        <div class="col-sm-6 mb-3 mb-sm-0">
                            <input id="password" type="password" class="form-control form-control-user @error('password') is-invalid @enderror" name="password" placeholder="{{ __('Contraseña') }}" required autocomplete="new-password">
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="col-sm-6">
                            <input id="password_confirmation" type="password" class="form-control form-control-user" name="password_confirmation" placeholder="{{ __('Confirmar contraseña') }}" required autocomplete="new-password">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-user btn-block">
                        {{ __('Registrarme') }}
                    </button>
                </form>

                <hr>

                <div class="text-center">
                    <a class="small" href="{{ route('login') }}">{{ __('¿Ya tienes cuenta? Inicia sesión') }}</a>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
