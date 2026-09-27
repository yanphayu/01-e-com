@php
    $path = $avatar ?? null;
    $initial = strtoupper(substr($name ?? '?', 0, 1));
    $url = null;

    if ($path) {
        $path = ltrim($path, '/');
        $url = str_starts_with($path, 'http')
            ? $path
            : asset(str_starts_with($path, 'storage/') ? $path : 'storage/'.$path);
    }
@endphp

@if ($url)
    <img src="{{ $url }}" alt="" class="{{ $class }}">
@else
    <span class="{{ $class }} {{ ($primary ?? false) ? 'admin-avatar--primary' : '' }}">{{ $initial }}</span>
@endif
