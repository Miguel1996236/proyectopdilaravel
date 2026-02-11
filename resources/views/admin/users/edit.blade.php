<x-app-layout>
    <x-slot name="header">
        {{ __('Editar usuario') }}: <span class="text-primary">{{ $user->name }}</span>
    </x-slot>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('Información del usuario') }}</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.users.update', $user) }}">
                        @csrf
                        @method('PUT')

                        <div class="form-group">
                            <label for="name">{{ __('Nombre completo') }}</label>
                            <input type="text" 
                                   class="form-control @error('name') is-invalid @enderror" 
                                   id="name" 
                                   name="name" 
                                   value="{{ old('name', $user->name) }}" 
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
                                   value="{{ old('email', $user->email) }}" 
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
                                    <option value="{{ $roleValue }}" {{ old('role', $user->role) === $roleValue ? 'selected' : '' }}>
                                        {{ $roleLabel }}
                                    </option>
                                @endforeach
                            </select>
                            @error('role')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="password">{{ __('Nueva contraseña') }} <small class="text-muted">({{ __('Opcional, déjalo vacío para mantener la actual') }})</small></label>
                            <input type="password" 
                                   class="form-control @error('password') is-invalid @enderror" 
                                   id="password" 
                                   name="password" 
                                   minlength="8">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">{{ __('Mínimo 8 caracteres. Solo completa este campo si deseas cambiar la contraseña.') }}</small>
                        </div>

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-light">
                                <i class="fas fa-arrow-left mr-1"></i>{{ __('Cancelar') }}
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save mr-1"></i>{{ __('Guardar cambios') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('Información del usuario') }}</h6>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-3">
                            <strong>{{ __('Registrado el') }}:</strong><br>
                            <span class="text-muted small">{{ $user->created_at->format('d/m/Y H:i') }}</span>
                        </li>
                        <li class="mb-3">
                            <strong>{{ __('Última actualización') }}:</strong><br>
                            <span class="text-muted small">{{ $user->updated_at->format('d/m/Y H:i') }}</span>
                        </li>
                        @if ($user->email_verified_at)
                            <li class="mb-3">
                                <strong>{{ __('Correo verificado') }}:</strong><br>
                                <span class="text-muted small">{{ $user->email_verified_at->format('d/m/Y H:i') }}</span>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">{{ __('Información') }}</h6>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        {{ __('Modifica la información del usuario. Puedes cambiar el rol y otros datos personales.') }}
                    </p>
                    <ul class="list-unstyled small text-muted mb-0">
                        <li class="mb-2"><i class="fas fa-key text-warning mr-2"></i>{{ __('Deja la contraseña vacía para mantener la actual.') }}</li>
                        <li class="mb-2"><i class="fas fa-user-shield text-success mr-2"></i>{{ __('El cambio de rol afecta los permisos inmediatamente.') }}</li>
                        <li class="mb-0"><i class="fas fa-exclamation-triangle text-danger mr-2"></i>{{ __('No puedes eliminar tu propia cuenta desde aquí.') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

