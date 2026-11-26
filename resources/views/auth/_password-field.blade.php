{{-- Input password + tombol Tampilkan/Sembunyikan (JS ada di auth/_toggle-script) --}}
<div class="mb-3">
    <label for="{{ $id }}" class="form-label">{{ $label }}</label>
    <div class="input-group has-validation">
        <input type="password"
               id="{{ $id }}"
               name="{{ $name }}"
               autocomplete="{{ $autocomplete ?? 'current-password' }}"
               class="form-control @error($name) is-invalid @enderror">
        <button type="button"
                class="btn btn-outline-secondary"
                data-toggle-password="#{{ $id }}"
                aria-pressed="false">Tampilkan</button>
        @error($name)
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
