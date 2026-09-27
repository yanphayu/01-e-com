@php
    $currentFilters = collect(request()->query())->except(['page', 'format', 'scope']);
@endphp

<form method="GET" action="{{ route('admin.exports.index', ['resource' => $resource]) }}" class="admin-export-form">
    @foreach ($currentFilters as $key => $value)
        @if (is_scalar($value) && $value !== '')
            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
        @endif
    @endforeach

    <select name="scope" class="admin-control" aria-label="Export scope">
        <option value="filtered" @selected(request()->query('scope') !== 'all')>Filtered results</option>
        <option value="all" @selected(request()->query('scope') === 'all')>All records</option>
    </select>

    <button type="submit" name="format" value="pdf" class="admin-button admin-button-secondary">Export PDF</button>
    <button type="submit" name="format" value="excel" class="admin-button admin-button-secondary">Export Excel</button>
</form>
