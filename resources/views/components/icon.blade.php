@props(['name'])
<svg {{ $attributes->class(['icon']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('search') <circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/> @break
        @case('arrow') <path d="M4 12h16m-6-6 6 6-6 6"/> @break
        @case('copy') <rect x="8" y="8" width="12" height="12" rx="1"/><path d="M15 8V4H4v11h4"/> @break
        @case('code') <path d="m8 5-6 7 6 7m8-14 6 7-6 7m-3-15-2 16"/> @break
        @case('scan') <path d="M8 3H3v5m13-5h5v5M3 16v5h5m8 0h5v-5M2 12h20"/> @break
        @case('check') <path d="m5 12 4 4L19 6"/> @break
        @case('layers') <path d="m12 3 10 5-10 5L2 8Zm-10 10 10 5 10-5M2 18l10 5 10-5"/> @break
        @case('shield') <path d="m12 2 9 4v6c0 6-9 10-9 10S3 18 3 12V6Zm-4 10 3 3 5-6"/> @break
        @case('terminal') <path d="m5 7 5 5-5 5m8 0h6"/> @break
        @case('sun') <circle cx="12" cy="12" r="4"/><path d="M12 1v2m0 18v2M1 12h2m18 0h2M4 4l2 2m12 12 2 2M4 20l2-2M18 6l2-2"/> @break
        @case('moon') <path d="M21 13A9 9 0 0 1 11 3a9 9 0 1 0 10 10Z"/> @break
        @case('menu') <path d="M4 6h16M4 12h16M4 18h16"/> @break
        @case('close') <path d="m5 5 14 14M5 19 19 5"/> @break
    @endswitch
</svg>
