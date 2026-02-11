<x-app-layout>
    <x-slot name="header">
        {{ __('Crear nuevo usuario') }}
    </x-slot>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('Información del usuario') }}</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.users.store') }}">
                        @csrf

                        <div class="form-group">
                            <label for="name">{{ __('Nombre completo') }}</label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name') }}" 
                                   required 
                                   autofocus>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="email">{{ __('Correo electrónico') }}</label>
                            <input type="email" 
                                   class="form-control @error('email') is-invalid @enderror" 
                                   id="email" 
                                   name="email" 
                                   value="{{ old('email') }}" 
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="role">{{ __('Rol') }}</label>
                            <select class="form-control @error('role') is-invalid @enderror" 
                                    id="role" 
                                    name="role" 
                                    required>
                                <option value="">{{ __('Selecciona un rol') }}</option>
                                @foreach ($roles as $roleValue => $roleLabel)
                                    <option value="{{ $roleValue }}" {{ old('role') === $roleValue ? 'selected' : '' }}>
                                        {{ $roleLabel }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password">{{ __('Contraseña') }} <small class="text-muted">({{ __('Opcional, se generará automáticamente si se deja vacío') }})</small></label>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   minlength="8">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">{{ __('Mínimo 8 caracteres. Si se deja vacío, se generará una contraseña aleatoria.') }}</small>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-light">
                                <i class="fas fa-arrow-left mr-1"></i>{{ __('Cancelar') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i>{{ __('Crear usuario') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('Información') }}</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        {{ __('Crea un nuevo usuario en el sistema. Puedes asignar diferentes roles según las necesidades.') }}
                    </p>
                    <ul class="list-unstyled small text-muted mb-0">
                        <li class="mb-2"><i class="fas fa-info-circle text-primary mr-2"></i>{{ __('La contraseña se puede dejar vacía para generar una automática.') }}</li>
                        <li class="mb-2"><i class="fas fa-shield-alt text-success mr-2"></i>{{ __('Solo los administradores pueden crear usuarios.') }}</li>
                        <li class="mb-0"><i class="fas fa-user-tag text-info mr-2"></i>{{ __('El rol determina los permisos del usuario en el sistema.') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

