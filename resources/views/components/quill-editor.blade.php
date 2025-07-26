<div class="mb-3">
    <label class="form-label">{{ $label }}
        @if ($required) <span class="text-danger">*</span> @endif
    </label>

    <div id="quill-{{ $name }}" style="height: {{ $height }}px;">
        @if ($value)
            {!! old($name, $value) !!}
        @endif
    </div>
    <input type="hidden" name="{{ $name }}" id="{{ $name }}">

    @error($name)
        <div class="text-danger">{{ $message }}</div>
    @enderror
</div>

@once
    @push('styles')
        <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    @endpush

    @push('scripts')
        <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    @endpush
@endonce

@push('scripts')
    <script>
        (() => {
            const quill = new Quill('#quill-{{ $name }}', { theme: 'snow' });
            const hidden = document.getElementById('{{ $name }}');
            const form = hidden.closest('form');

            form.addEventListener('submit', () => {
                const plain = quill.getText().trim();
                hidden.value = plain === '' ? '' : quill.root.innerHTML;
            });
        })();
    </script>
@endpush