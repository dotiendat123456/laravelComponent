<div class="mb-3">
    <label for="{{ $name }}" class="form-label">
        {{ $label }}
        @if ($required)
            <span class="text-danger">*</span>
        @endif
    </label>

    <select name="{{ $name }}" id="{{ $name }}" class="form-select @error($name) is-invalid @enderror">
        @foreach ($options() as $option)
            <option value="{{ $option->value }}" {{ old($name, $selected) == $option->value ? 'selected' : '' }}>
                {{ $option->label() }}
            </option>
        @endforeach
    </select>

    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>