@props(['items' => []])

<nav aria-label="breadcrumb" {{ $attributes }}>
    <ol class="breadcrumb mb-0">
        <li class="breadcrumb-item">
            <a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-secondary">
                <span class="material-symbols-outlined align-middle" style="font-size: 1rem;">home</span>
                Ana Sayfa
            </a>
        </li>
        @foreach ($items as $item)
            @if (is_array($item))
                <li class="breadcrumb-item {{ $loop->last ? 'active' : '' }}" {{ $loop->last ? 'aria-current="page"' : '' }}>
                    @if (isset($item['url']) && ! $loop->last)
                        <a href="{{ $item['url'] }}">{{ $item['title'] }}</a>
                    @else
                        {{ $item['title'] }}
                    @endif
                </li>
            @else
                <li class="breadcrumb-item active" aria-current="page">{{ $item }}</li>
            @endif
        @endforeach
    </ol>
</nav>
