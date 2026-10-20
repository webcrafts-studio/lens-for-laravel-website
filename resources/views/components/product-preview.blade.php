@props(['image', 'title', 'description', 'priority' => false, 'appearance' => 'compact'])

@php
    [$width, $height] = getimagesize(public_path('assets/screenshots/'.$image.'-light.webp'));
    $captionId = 'preview-caption-'.Str::slug($title);
@endphp

<figure {{ $attributes->class(['product-preview', 'product-preview--hero' => $appearance === 'hero']) }} data-preview-variant="{{ $appearance }}">
    @foreach (['light', 'dark'] as $theme)
        <a class="preview-version preview-version-{{ $theme }}" href="{{ asset('assets/screenshots/'.$image.'-'.$theme.'.webp') }}" data-product-preview data-preview-title="{{ $title }}" data-preview-description="{{ $description }}" aria-label="Enlarge screenshot: {{ $title }}" aria-describedby="{{ $captionId }}" aria-haspopup="dialog" aria-controls="product-preview-dialog">
            <picture class="preview-viewport">
                <img src="{{ asset('assets/screenshots/'.$image.'-'.$theme.'.webp') }}" width="{{ $width }}" height="{{ $height }}" alt="{{ $title }} ({{ $theme }} theme)" loading="{{ $priority ? 'eager' : 'lazy' }}" @if($priority) fetchpriority="high" @endif>
            </picture>
            <span class="preview-enlarge"><x-icon name="search"/> Enlarge screenshot <x-icon name="arrow-up-right"/></span>
        </a>
    @endforeach
    <figcaption id="{{ $captionId }}" class="sr-only"><strong>{{ $title }}</strong><p>{{ $description }}</p></figcaption>
</figure>
