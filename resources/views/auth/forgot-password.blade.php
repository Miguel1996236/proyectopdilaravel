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
                                        <h1 class="h3 font-weight-bold text-gray-900 mb-2">{{ __('¿Olvidaste tu contraseña?') }}</h1>
                                        <p class="text-muted">{{ __('No hay problema. Solo dinos tu dirección de correo electrónico y te enviaremos un enlace para restablecer tu contraseña.') }}</p>
                                    </div>

                                    @if (session('status'))
                                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                                            {{ session('status') }}
                                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                    @endif

                                    <form method="POST" action="{{ route('password.email') }}">
                                        @csrf

                                        <div class="form-group">
                                            <input id="email" 
                                                   class="form-control form-control-user @error('email') is-invalid @enderror" 
                                                   type="email" 
                                                   name="email" 
                                                   value="{{ old('email') }}" 
                                                   placeholder="{{ __('Correo electrónico') }}" 
                                                   required 
                                                   autofocus>
                                            @error('email')
                                                <span class="invalid-feedback" role="alert">
                                                    <strong>{{ $message }}</strong>
                                                </span>
                                            @enderror
                                        </div>

                                        <button type="submit" class="btn btn-primary btn-user btn-block">
                                            <i class="fas fa-envelope mr-2"></i>{{ __('Enviar enlace de restablecimiento') }}
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
</x-guest-layout>
