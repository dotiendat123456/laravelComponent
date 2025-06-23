@props(['message' => null])

<div class="alert alert-success">
    {{ $message ?? $slot }}
</div>
