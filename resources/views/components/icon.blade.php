@props(['name'])
<svg {{ $attributes->class(['icon']) }} viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
    @switch($name)
        @case('search') <circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/> @break
        @case('arrow') <path d="M4 12h16m-6-6 6 6-6 6"/> @break
        @case('copy') <rect x="8" y="8" width="12" height="12" rx="1"/><path d="M15 8V4H4v11h4"/> @break
        @case('code') <path d="m8 5-6 7 6 7m8-14 6 7-6 7m-3-15-2 16"/> @break
        @case('scan') <path d="M8 3H3v5m13-5h5v5M3 16v5h5m8 0h5v-5M2 12h20"/> @break
        @case('mouse-pointer-2')
            <!--
                MousePointer2 — Lucide (https://lucide.dev/icons/mouse-pointer-2)
                ISC License
                Copyright (c) 2026 Lucide Icons and Contributors

                Permission to use, copy, modify, and/or distribute this software for any
                purpose with or without fee is hereby granted, provided that the above
                copyright notice and this permission notice appear in all copies.
                THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES
                WITH REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF
                MERCHANTABILITY AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR
                ANY SPECIAL, DIRECT, INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES
                WHATSOEVER RESULTING FROM LOSS OF USE, DATA OR PROFITS, WHETHER IN AN
                ACTION OF CONTRACT, NEGLIGENCE OR OTHER TORTIOUS ACTION, ARISING OUT OF
                OR IN CONNECTION WITH THE USE OR PERFORMANCE OF THIS SOFTWARE.
            -->
            <path stroke-width="2" d="M4.037 4.688a.495.495 0 0 1 .651-.651l16 6.5a.5.5 0 0 1-.063.947l-6.124 1.58a2 2 0 0 0-1.438 1.435l-1.579 6.126a.5.5 0 0 1-.947.063z"/>
            @break
        @case('lock') <rect x="5" y="10" width="14" height="11" rx="3"/><path d="M8 10V7a4 4 0 0 1 8 0v3m-4 5v2"/> @break
        @case('globe') <circle cx="12" cy="12" r="9"/><ellipse cx="12" cy="12" rx="4" ry="9"/><path d="M3 12h18"/> @break
        @case('check') <path d="m5 12 4 4L19 6"/> @break
        @case('layers') <path d="m12 3 10 5-10 5L2 8Zm-10 10 10 5 10-5M2 18l10 5 10-5"/> @break
        @case('shield') <path d="m12 2 9 4v6c0 6-9 10-9 10S3 18 3 12V6Zm-4 10 3 3 5-6"/> @break
        @case('terminal') <path d="m5 7 5 5-5 5m8 0h6"/> @break
        @case('sun') <circle cx="12" cy="12" r="4"/><path d="M12 1v2m0 18v2M1 12h2m18 0h2M4 4l2 2m12 12 2 2M4 20l2-2M18 6l2-2"/> @break
        @case('star') <path d="m12 3 2.78 5.63 6.22.91-4.5 4.39 1.06 6.2L12 17.2l-5.56 2.93 1.06-6.2L3 9.54l6.22-.91Z"/> @break
        @case('moon') <path d="M21 13A9 9 0 0 1 11 3a9 9 0 1 0 10 10Z"/> @break
        @case('menu') <path d="M4 6h16M4 12h16M4 18h16"/> @break
        @case('close') <path d="m5 5 14 14M5 19 19 5"/> @break
    @endswitch
</svg>
