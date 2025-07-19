<div class="mb-3">
    <label for="{{ $name }}" class="form-label">
        {{ $label }}
        @if ($required) <span class="text-danger">*</span> @endif
    </label>

    <input type="{{ $type }}" name="{{ $name }}" id="{{ $name }}" value="{{ old($name, $value) }}"
        class="form-control @error($name) is-invalid @enderror">

    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>