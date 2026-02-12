<div class="form-group">
    <label for="name" class="font-weight-bold">{{ __('Nombre del grupo') }}</label>
    <input type="text" name="name" id="name" value="{{ old('name', $group->name ?? '') }}"
           class="form-control @error('name') is-invalid @enderror" required
           placeholder="{{ __('Ej: Sección A - Matemáticas 2026') }}">
    @error('name')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<div class="form-group">
    <label for="description" class="font-weight-bold">{{ __('Descripción') }} <small class="text-muted">({{ __('opcional') }})</small></label>
    <textarea name="description" id="description" rows="2"
              class="form-control @error('description') is-invalid @enderror"
              placeholder="{{ __('Descripción breve del grupo') }}">{{ old('description', $group->description ?? '') }}</textarea>
    @error('description')
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>

<hr>

<h6 class="font-weight-bold text-primary">{{ __('Miembros del grupo') }}</h6>
<p class="text-muted small">{{ __('Agrega los estudiantes con su nombre y correo electrónico.') }}</p>

<div id="members-container">
    @php
        $existingMembers = old('members', isset($group) && $group->members ? $group->members->map(fn($m) => ['name' => $m->name, 'email' => $m->email])->toArray() : []);
    @endphp

    @foreach ($existingMembers as $i => $member)
        <div class="form-row member-row mb-2">
            <div class="col-md-5">
                <input type="text" name="members[{{ $i }}][name]" value="{{ $member['name'] ?? '' }}"
                       class="form-control form-control-sm" placeholder="{{ __('Nombre') }}" required>
            </div>
            <div class="col-md-5">
                <input type="email" name="members[{{ $i }}][email]" value="{{ $member['email'] ?? '' }}"
                       class="form-control form-control-sm" placeholder="{{ __('Correo electrónico') }}" required>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger btn-sm btn-block remove-member">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    @endforeach
</div>

<button type="button" id="add-member" class="btn btn-outline-primary btn-sm mt-2">
    <i class="fas fa-plus mr-1"></i>{{ __('Agregar miembro') }}
</button>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let memberIndex = {{ count($existingMembers) }};
    const container = document.getElementById('members-container');

    document.getElementById('add-member').addEventListener('click', function () {
        const row = document.createElement('div');
        row.className = 'form-row member-row mb-2';
        row.innerHTML = `
            <div class="col-md-5">
                <input type="text" name="members[${memberIndex}][name]" class="form-control form-control-sm" placeholder="{{ __('Nombre') }}" required>
            </div>
            <div class="col-md-5">
                <input type="email" name="members[${memberIndex}][email]" class="form-control form-control-sm" placeholder="{{ __('Correo electrónico') }}" required>
            </div>
            <div class="col-md-2">
                <button type="button" class="btn btn-outline-danger btn-sm btn-block remove-member">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;
        container.appendChild(row);
        memberIndex++;
    });

    container.addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-member');
        if (btn) {
            btn.closest('.member-row').remove();
        }
    });
});
</script>
@endpush

