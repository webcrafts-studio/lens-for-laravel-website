@php
    $previewTitle = 'Your audit, inside Lens';
    $previewDescription = 'The actual Diagnostic Report: 3 violations, with 2 at level A and 1 at AA. The image-alt finding links to its Blade source. Fix All A and Fix All AA start a review queue.';
@endphp

<div class="hero-art">
    <figure class="hero-scene" data-hero-illustration role="img" aria-labelledby="hero-art-caption">
        <svg class="hero-illustration" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 800 640" width="800" height="640" fill="none" aria-hidden="true" focusable="false">
            <defs>
                <!--
                    CircleCheck, Sparkles, CodeXml and TextAlignStart — Lucide (https://lucide.dev)
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

                    CodeXml is derived from Feather and also covered by the MIT License.
                    Copyright (c) 2013-present Cole Bemis

                    Permission is hereby granted, free of charge, to any person obtaining a copy
                    of this software and associated documentation files (the "Software"), to deal
                    in the Software without restriction, including without limitation the rights
                    to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
                    copies of the Software, and to permit persons to whom the Software is
                    furnished to do so, subject to the following conditions:
                    The above copyright notice and this permission notice shall be included in all
                    copies or substantial portions of the Software.
                    THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
                    IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
                    FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
                    AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
                    LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
                    OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
                    SOFTWARE.
                -->
                <g id="hero-code-icon" data-lucide="code-xml" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m18 16 4-4-4-4"/>
                    <path d="m6 8-4 4 4 4"/>
                    <path d="m14.5 4-5 16"/>
                </g>
                <g id="hero-text-icon" data-lucide="text-align-start" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 5H3"/>
                    <path d="M15 12H3"/>
                    <path d="M17 19H3"/>
                </g>
                <g id="hero-ai-icon" data-lucide="sparkles" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M11.017 2.814a1 1 0 0 1 1.966 0l1.051 5.558a2 2 0 0 0 1.594 1.594l5.558 1.051a1 1 0 0 1 0 1.966l-5.558 1.051a2 2 0 0 0-1.594 1.594l-1.051 5.558a1 1 0 0 1-1.966 0l-1.051-5.558a2 2 0 0 0-1.594-1.594l-5.558-1.051a1 1 0 0 1 0-1.966l5.558-1.051a2 2 0 0 0 1.594-1.594z"/>
                    <path d="M20 2v4"/>
                    <path d="M22 4h-4"/>
                    <circle cx="4" cy="20" r="2"/>
                </g>
                <g id="hero-verified-icon" data-lucide="circle-check" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" fill="var(--hero-check-surface)"/>
                    <path d="m16 9-5.5 5.5L8 12"/>
                </g>
                <linearGradient id="hero-floor" x1="400" y1="210" x2="400" y2="610" gradientUnits="userSpaceOnUse">
                    <stop class="hero-floor-start"/>
                    <stop offset="1" class="hero-floor-end"/>
                </linearGradient>
                <linearGradient id="hero-bezel" x1="0" y1="0" x2="350" y2="250" gradientUnits="userSpaceOnUse">
                    <stop class="hero-bezel-start"/>
                    <stop offset="1" class="hero-bezel-end"/>
                </linearGradient>
                <linearGradient id="hero-red-top" x1="530" y1="252" x2="530" y2="368" gradientUnits="userSpaceOnUse">
                    <stop stop-color="var(--hero-red-top)"/>
                    <stop offset="1" stop-color="var(--hero-red-front)"/>
                </linearGradient>
                <radialGradient id="hero-grid-fade">
                    <stop offset=".35" stop-color="white"/>
                    <stop offset="1" stop-color="white" stop-opacity="0"/>
                </radialGradient>
                <pattern id="hero-grid" width="40" height="40" patternUnits="userSpaceOnUse">
                    <path d="M40 0H0V40" stroke="var(--hero-grid)" stroke-width=".8"/>
                </pattern>
                <mask id="hero-grid-mask">
                    <ellipse cx="400" cy="420" rx="390" ry="220" fill="url(#hero-grid-fade)"/>
                </mask>
                <filter id="hero-ground-shadow" x="-30%" y="-50%" width="160%" height="200%">
                    <feGaussianBlur stdDeviation="12"/>
                </filter>
                <filter id="hero-contact-shadow" x="-30%" y="-60%" width="160%" height="220%">
                    <feGaussianBlur stdDeviation="5"/>
                </filter>
                <g id="hero-lens-symbol" stroke="var(--hero-on-red)">
                    <circle cx="32" cy="32" r="24" stroke-width="3.5"/>
                    <g transform="translate(7.04 7.04) scale(.78)">
                        <circle cx="32" cy="16" r="4.25" fill="var(--hero-on-red)" stroke="none"/>
                        <path d="M16 25Q32 34 48 25M32 29.5V38m-7 11 7-11 7 11" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"/>
                    </g>
                </g>
            </defs>

            <g mask="url(#hero-grid-mask)">
                <g transform="matrix(.8660254 .5 -.8660254 .5 400 210)">
                    <rect x="-400" y="-400" width="1200" height="1200" fill="url(#hero-grid)"/>
                </g>
            </g>

            <path d="m400 235 346.41 200L400 635 53.59 435Z" fill="var(--hero-shadow)" opacity=".16" filter="url(#hero-ground-shadow)"/>
            <g stroke="var(--hero-edge)" stroke-width="1.25" stroke-linejoin="round">
                <path d="m53.59 410 346.41 200v14L53.59 424Z" fill="var(--hero-side)"/>
                <path d="m400 610 346.41-200v14L400 624Z" fill="var(--hero-front)"/>
                <path d="m400 210 346.41 200L400 610 53.59 410Z" fill="url(#hero-floor)"/>
            </g>
            <path d="m66 410 334 193 334-193" stroke="var(--hero-highlight)" stroke-width="2"/>
            <path d="m400 230 312 180-312 180L88 410Z" stroke="var(--hero-inset)" stroke-width="1"/>

            <g stroke-linecap="round" stroke-linejoin="round">
                <path d="m278 344 88 51 64-37M340 448l43 25 54-31M530 493l-62 36-42-24" stroke="var(--hero-trace-bed)" stroke-width="8"/>
                <path d="m278 344 88 51 64-37M340 448l43 25 54-31M530 493l-62 36-42-24" stroke="var(--hero-ink)" stroke-width="1.8"/>
            </g>
            <g fill="var(--hero-surface)" stroke="var(--hero-ink)" stroke-width="1.5">
                <ellipse cx="366" cy="395" rx="5" ry="2.9"/>
                <ellipse cx="383" cy="473" rx="5" ry="2.9"/>
                <ellipse cx="468" cy="529" rx="5" ry="2.9"/>
            </g>

            <path d="m135 426 290-168 12 8-290 168Z" fill="var(--hero-shadow)" opacity=".2" filter="url(#hero-contact-shadow)"/>
            <g transform="matrix(.8660254 -.5 0 1 130 194)" stroke-linejoin="round">
                <path d="M-11-20 0-27h330l11 7v261l-11 7H0l-11-7Z" fill="var(--hero-side)" stroke="var(--hero-edge)" stroke-width="1.4"/>
                <path d="m330-27 11 7v261l-11 7Z" fill="var(--hero-front)" stroke="var(--hero-edge)" stroke-width="1.4"/>
                <rect x="-11" y="-20" width="341" height="261" rx="5" fill="url(#hero-bezel)" stroke="var(--hero-edge)" stroke-width="1.4"/>
                <rect x="-3" y="-12" width="325" height="245" rx="1" fill="var(--hero-surface)" stroke="var(--hero-edge)"/>
                <image class="hero-report-light" href="{{ asset('assets/screenshots/dashboard-light.webp') }}" x="1" y="-8" width="317" height="237" preserveAspectRatio="xMidYMid meet"/>
                <image class="hero-report-dark" href="{{ asset('assets/screenshots/dashboard-dark.webp') }}" x="1" y="-8" width="317" height="237" preserveAspectRatio="xMidYMid meet"/>
                <path d="M-7-15h332" stroke="var(--hero-highlight)" stroke-width="1.5"/>
                <g fill="var(--hero-edge)"><circle cx="-6" cy="-6" r="1.5"/><circle cx="325" cy="-6" r="1.5"/><circle cx="-6" cy="227" r="1.5"/><circle cx="325" cy="227" r="1.5"/></g>
            </g>

            <path d="m410 441 120-69 120 69-120 69Z" fill="var(--hero-shadow)" opacity=".22" filter="url(#hero-contact-shadow)"/>
            <g stroke="var(--hero-edge)" stroke-width="1.25" stroke-linejoin="round">
                <path d="m410 424 120 69v12l-120-69Z" fill="var(--hero-side)"/>
                <path d="m530 493 120-69v12l-120 69Z" fill="var(--hero-front)"/>
                <path d="m530 355 120 69-120 69-120-69Z" fill="var(--hero-surface)"/>
            </g>
            <path d="m417 424 113 65 113-65" stroke="var(--hero-highlight)" stroke-width="1.5"/>

            <g stroke="var(--hero-red-edge)" stroke-width="1.5" stroke-linejoin="round">
                <path data-hero-face-surface="code" d="m430 310 100 58v115l-100-58Z" fill="var(--hero-red-side)"/>
                <path data-hero-face-surface="text" d="m530 368 100-58v115l-100 58Z" fill="var(--hero-red-front)"/>
                <path d="m530 252 100 58-100 58-100-58Z" fill="url(#hero-red-top)"/>
            </g>
            <path d="m436 310 94 54 94-54M530 374v101" stroke="var(--hero-on-red)" stroke-opacity=".35" stroke-width="1"/>
            <g transform="matrix(.8660254 .5 -.8660254 .5 530 252)">
                <rect x="13" y="13" width="89" height="89" rx="20" stroke="var(--hero-on-red)" stroke-opacity=".4"/>
                <use href="#hero-lens-symbol" transform="translate(19.1 19.1) scale(1.2)"/>
            </g>
            <g data-hero-face="code" transform="matrix(.8695652174 .5043478261 0 1 430 310)" stroke="var(--hero-on-red)">
                <use href="#hero-code-icon" transform="translate(27.5 27.5) scale(2.5)"/>
            </g>
            <g data-hero-face="text" transform="matrix(.8695652174 -.5043478261 0 1 530 368)" stroke="var(--hero-on-red)">
                <use href="#hero-text-icon" transform="translate(27.5 27.5) scale(2.5)"/>
            </g>

            <g data-hero-tile="ai" transform="translate(0 -40)">
                <path d="m250 447 90 52-90 52-90-52Z" fill="var(--hero-shadow)" opacity=".16" filter="url(#hero-contact-shadow)"/>
                <g stroke="var(--hero-edge)" stroke-width="1.25" stroke-linejoin="round">
                    <path d="m160 482 90 52v11l-90-52Z" fill="var(--hero-side)"/>
                    <path d="m250 534 90-52v11l-90 52Z" fill="var(--hero-front)"/>
                    <path d="m250 430 90 52-90 52-90-52Z" fill="var(--hero-surface)"/>
                </g>
                <g data-hero-tile-surface="ai" transform="matrix(.8653846154 .5 -.8653846154 .5 250 430)">
                    <rect x="12" y="12" width="80" height="80" rx="5" fill="var(--hero-code-surface)" stroke="var(--hero-edge)"/>
                    <use href="#hero-ai-icon" transform="rotate(-45 52 52) translate(25.6 25.6) scale(2.2)" stroke="var(--hero-ink)"/>
                </g>
            </g>

            <g transform="translate(0 -20)">
                <path d="m412 521 77.94 45-77.94 45-77.94-45Z" fill="var(--hero-shadow)" opacity=".16" filter="url(#hero-contact-shadow)"/>
                <g stroke="var(--hero-edge)" stroke-width="1.25" stroke-linejoin="round">
                    <path d="m334.06 555 77.94 45v10l-77.94-45Z" fill="var(--hero-side)"/>
                    <path d="M412 600l77.94-45v10L412 610Z" fill="var(--hero-front)"/>
                    <path d="m412 510 77.94 45L412 600l-77.94-45Z" fill="var(--hero-surface)"/>
                </g>
                <g transform="matrix(.8660254 .5 -.8660254 .5 412 510)">
                    <use href="#hero-verified-icon" transform="rotate(-45 45 45) translate(12 12) scale(2.75)" stroke="var(--hero-ink)"/>
                </g>
            </g>
        </svg>
        <figcaption id="hero-art-caption" class="sr-only">An isometric Lens workspace: the actual diagnostic report connects to the Lens module, source code, AI-assisted fixes, reviewed changes and a fresh scan. Lens helps you find accessibility issues and review fixes in your Laravel project.</figcaption>
    </figure>
    <div class="hero-preview-actions">
        @foreach (['light', 'dark'] as $theme)
            @php
                [$width, $height] = getimagesize(public_path('assets/screenshots/dashboard-'.$theme.'.webp'));
            @endphp
            <a class="hero-preview-link preview-version-{{ $theme }}" href="{{ asset('assets/screenshots/dashboard-'.$theme.'.webp') }}" data-product-preview data-preview-title="{{ $previewTitle }}" data-preview-description="{{ $previewDescription }}" data-preview-alt="Lens Diagnostic Report: 3 violations, 2 level A and 1 level AA ({{ $theme }} theme)" data-preview-width="{{ $width }}" data-preview-height="{{ $height }}" aria-label="Enlarge screenshot: {{ $previewTitle }}" aria-describedby="hero-preview-description" aria-haspopup="dialog" aria-controls="product-preview-dialog">
                <span class="preview-enlarge"><x-icon name="search"/> Enlarge screenshot <x-icon name="arrow-up-right"/></span>
            </a>
        @endforeach
        <p id="hero-preview-description" class="sr-only">{{ $previewDescription }}</p>
    </div>
</div>
