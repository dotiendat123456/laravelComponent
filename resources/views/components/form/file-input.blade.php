<div class="mb-3">
    <label for="{{ $name }}" class="form-label">
        {{ $label }}
        @if ($required)
            <span class="text-danger">*</span>
        @endif
    </label>

    <input type="file" name="{{ $name }}" id="{{ $name }}" class="form-control @error($name) is-invalid @enderror">

    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror

    @if ($currentFile)
        <div class="mt-2">
            <img src="{{ asset($currentFile) }}" alt="Thumbnail hiện tại" style="max-width: 200px;">
        </div>
    @endif
</div>