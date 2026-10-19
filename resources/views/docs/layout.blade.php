@extends('layouts.site')
@section('title', ($pageTitle ?? 'Docs').' - Lens for Laravel')
@section('description', 'Lens for Laravel documentation - installation, scanning, source mapping, AI fixes, and CLI workflows.')
@section('skip-label', 'documentation')
@section('body')
<div class="docs-grid">
    <aside class="docs-sidebar" aria-label="Documentation chapters">
        <details class="docs-chapters" data-chapters open>
            <summary>Documentation <span aria-hidden="true">+</span></summary>
            <nav id="docs-nav" aria-label="Documentation">
                @foreach ($navigation as $section => $items)
                    <div class="nav-section"><h2>{{ str($section)->lower()->title() }}</h2>
                        @foreach ($items as $item)
                            <a href="{{ route('docs.show', $item['slug']) }}" @if($currentSlug === $item['slug']) aria-current="page" @endif>{{ $item['title'] }}</a>
                        @endforeach
                    </div>
                @endforeach
            </nav>
        </details>
    </aside>
    <div class="docs-content">
        <main id="main-content" tabindex="-1">
            <div class="docs-breadcrumb"><a href="{{ route('docs') }}">Docs</a><span aria-hidden="true">/</span><span>{{ $pageTitle }}</span></div>
            @if(count($toc))
                <details class="mobile-toc"><summary>On this page <span aria-hidden="true">+</span></summary><nav aria-label="On this page, mobile">@foreach($toc as $heading)<a href="#{{ $heading['id'] }}">{{ $heading['title'] }}</a>@endforeach</nav></details>
            @endif
            <article class="docs-prose">@yield('content')</article>
        </main>
        @if($prev || $next)
            <nav class="docs-pagination" aria-label="Documentation pagination">
                @if($prev)<a href="{{ route('docs.show', $prev['slug']) }}"><span class="micro">← Previous</span><strong>{{ $prev['title'] }}</strong></a>@else<div></div>@endif
                @if($next)<a href="{{ route('docs.show', $next['slug']) }}"><span class="micro">Next →</span><strong>{{ $next['title'] }}</strong></a>@endif
            </nav>
        @endif
    </div>
    @if(count($toc))<aside class="docs-toc"><nav aria-label="On this page"><h2>On this page</h2>@foreach($toc as $heading)<a href="#{{ $heading['id'] }}" @class(['toc-nested' => $heading['level'] === 'h3'])>{{ $heading['title'] }}</a>@endforeach</nav></aside>@endif
</div>
@endsection
