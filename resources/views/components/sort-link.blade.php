@props(['field'])

@php
    $currentSort = request('sort');
    $currentDirection = request('direction') === 'desc' ? 'desc' : 'asc';
    $isActive = $currentSort === $field;
    $nextDirection = $isActive && $currentDirection === 'asc' ? 'desc' : 'asc';
    $query = array_merge(request()->query(), [
        'sort' => $field,
        'direction' => $nextDirection,
    ]);

    unset($query['page']);
@endphp

<a class="sort-link @if($isActive) active @endif" href="{{ url()->current().'?'.http_build_query($query) }}">
    {{ $slot }}
    @if($isActive)
        <span class="sort-indicator">{{ $currentDirection === 'asc' ? '↑' : '↓' }}</span>
    @endif
</a>
