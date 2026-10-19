@props(['stars' => null])

@php
    $formattedStars = match (true) {
        $stars === null => '-',
        $stars >= 1_000_000 => rtrim(rtrim(number_format($stars / 1_000_000, 1, '.', ''), '0'), '.') . 'M',
        $stars >= 1_000 => rtrim(rtrim(number_format($stars / 1_000, 1, '.', ''), '0'), '.') . 'K',
        default => number_format($stars),
    };

    $ariaLabel = $stars === null
        ? 'View Lens for Laravel on GitHub'
        : "View Lens for Laravel on GitHub - {$formattedStars} stars";

    $title = $stars === null ? 'View repository on GitHub' : "{$formattedStars} stars on GitHub";
@endphp

<a href="https://github.com/{{ config('services.github.repository') }}"
    {{ $attributes->merge(['class' => 'github-link']) }}
    target="_blank" rel="noopener noreferrer" aria-describedby="new-tab-description"
    aria-label="{{ $ariaLabel }}" title="{{ $title }}">
    <svg class="h-5 w-5 shrink-0 fill-current" viewBox="0 0 24 24" aria-hidden="true">
        <path fill-rule="evenodd"
            d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.5.5.092.682-.217.682-.483 0-.237-.009-.868-.014-1.703-2.782.605-3.369-1.342-3.369-1.342-.454-1.157-1.11-1.465-1.11-1.465-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0 1 12 6.844a9.59 9.59 0 0 1 2.504.337c1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.31.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0 0 22 12.017C22 6.484 17.523 2 12 2Z"
            clip-rule="evenodd" />
    </svg>
    <span class="github-stars"><x-icon name="star" class="github-star-icon"/><span class="star-count">{{ $formattedStars }}</span></span>
</a>
