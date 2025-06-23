@props(['message' => null])

<div class="alert alert-danger">
    {{ $message ?? $slot }}
</div>