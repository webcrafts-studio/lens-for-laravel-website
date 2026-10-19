@extends('layouts.site')
@section('body')
<main id="main-content" tabindex="-1">
    <section class="hero section-pad" aria-labelledby="hero-title">
        <div class="hero-copy">
            <p class="eyebrow"><span class="status-dot" aria-hidden="true"></span> Accessibility, built into your Laravel workflow</p>
            <h1 id="hero-title">Find accessibility issues.<br><span class="text-accent">Get straight to the fix.</span></h1>
            <p class="hero-lead">Stop hunting through templates and fixing findings one by one. Scan your app, jump to the source, and review AI-assisted changes without leaving your development workflow.</p>
            <div class="button-row"><a class="button button-primary" href="{{ route('docs.show', 'installation') }}">Start using Lens <x-icon name="arrow"/></a><a class="button button-secondary" href="#features">See how it works <x-icon name="scan"/></a></div>
            <div class="hero-install"><x-install-command/></div>
            <p class="compatibility">Free and open source. Runs in your project. AI is optional.</p>
        </div>
        <x-product-preview class="hero-art" appearance="hero" image="dashboard" title="Your audit, inside Lens" description="The actual Diagnostic Report: 3 violations, with 2 at level A and 1 at AA. The image-alt finding links to its Blade source. Fix All A and Fix All AA start a review queue." :priority="true"/>
    </section>
    <div class="framework-strip" role="group" aria-label="Supported frontends"><span class="micro">Works with the stack you already use</span><ul role="list">@foreach (['blade' => 'Blade', 'livewire' => 'Livewire', 'react' => 'React', 'vue' => 'Vue', 'inertia' => 'Inertia'] as $technology => $label)<li><x-technology-logo :name="$technology"/>{{ $label }}</li>@endforeach</ul></div>

    <section id="features" class="workflow-section section-pad" aria-labelledby="workflow-title">
        <div class="centered-intro"><p class="eyebrow">The work between finding and fixing</p><h2 id="workflow-title">A finding should be a starting point.<br><span class="text-accent">Not another investigation.</span></h2><p>The slow part is often everything after the audit. Lens connects the steps you would otherwise repeat by hand.</p></div>
        @include('components.workflow-preview')
        <div class="outcome-grid">
            <article><span class="outcome-icon"><x-icon name="code"/></span><h3>Skip the file hunt</h3><p>Go from a failing element to its Blade, React, or Vue source location. Keep the context you need to make the change.</p></article>
            <article><span class="outcome-icon"><x-icon name="layers"/></span><h3>Stop fixing one at a time</h3><p>Build a queue of A or AA fixes, review the proposed changes, and apply ready suggestions together.</p></article>
            <article><span class="outcome-icon"><x-icon name="check"/></span><h3>Know what changed</h3><p>Run a fresh scan after applying fixes. Track findings across scans instead of working through the same report again.</p></article>
        </div>
    </section>

    <section id="ai-fixes" class="fix-section section-pad" aria-labelledby="ai-title">
        <div class="fix-layout">
            <div class="section-copy"><p class="eyebrow">A shorter queue. The same control.</p><h2 id="ai-title">Give the repetitive fixes<br><span class="text-accent">a little help.</span></h2><p>Missing labels. Unnamed buttons. The same small issues across different components. Let Lens prepare the changes, then spend your attention on reviewing them.</p><ul class="benefit-list" role="list"><li><x-icon name="check"/>Review the diff and edit the proposal.</li><li><x-icon name="check"/>Apply ready suggestions in one bulk action.</li><li><x-icon name="check"/>Re-scan to verify what was actually fixed.</li></ul><a class="text-link" href="{{ route('docs.show', 'ai-fix-engine') }}">Explore the AI Fix Engine <x-icon name="arrow"/></a></div>
            <x-product-preview class="fix-preview" image="ai-review" title="The Fix All A review window" description="The actual Fix All A window with a prepared example proposal: two ready suggestions, an original/fixed diff, Edit, Accept & Apply, and Apply All Ready. A fresh scan verifies the result."/>
        </div>
        <div class="model-choice"><div><span class="outcome-icon"><x-icon name="shield"/></span><h3>Local models supported</h3><p>Use Ollama on the machine running Laravel, or choose a cloud provider. With Ollama's default localhost endpoint, selected source context stays on that machine.</p></div><div class="provider-strip"><p class="micro">Eight supported providers</p><ul role="list">@foreach (['gemini' => 'Gemini', 'openai' => 'OpenAI', 'anthropic' => 'Anthropic', 'openrouter' => 'OpenRouter', 'xai' => 'xAI', 'deepseek' => 'DeepSeek', 'mistral' => 'Mistral', 'ollama' => 'Ollama'] as $provider => $label)<li><x-technology-logo :name="$provider"/>{{ $label }}@if($provider === 'ollama') <span class="provider-local">Local</span>@endif</li>@endforeach</ul><p class="model-note">Cloud providers receive selected context when you request a fix. AI requires PHP 8.3+, Laravel 12+, and laravel/ai 0.3.2+.</p></div></div>
    </section>

    <section class="states-section section-pad" aria-labelledby="states-title">
        <div class="states-heading"><div><p class="eyebrow">The parts a first-page scan misses</p><h2 id="states-title">Your app doesn't stop<br>at the first screen.</h2></div><p>Neither should your audit. Record the interactions once, then scan the open menu, the validation error, or the dashboard behind login.</p></div>
        <div class="journey-grid">
            <article class="journey-card"><div class="journey-visual journey-visual-interact" aria-hidden="true"><span class="journey-icon"><x-icon name="mouse-pointer-2"/></span></div><p class="micro">01 / Interact</p><h3>Open it. Then scan it.</h3><p>Record clicks, typing, and waits. Reuse that script to audit the same interface states on the next run.</p></article>
            <article class="journey-card"><div class="journey-visual journey-visual-private" aria-hidden="true"><span class="journey-icon"><x-icon name="lock"/></span></div><p class="micro">02 / Go further</p><h3>Include the private pages.</h3><p>Configure an authenticated scan user to reach account pages, dashboards, and other interfaces behind login.</p></article>
            <article class="journey-card"><div class="journey-visual journey-visual-crawl" aria-hidden="true"><span class="journey-icon"><x-icon name="globe"/></span></div><p class="micro">03 / Cover more</p><h3>Look beyond one URL.</h3><p>Scan a page, a list of URLs, or crawl your site. JavaScript-rendered discovery supports SPA and Inertia navigation.</p></article>
        </div>
        <a class="text-link" href="{{ route('docs.show', 'scanning-modes') }}">Find the right scanning mode <x-icon name="arrow"/></a>
    </section>

    <section id="ci-cd" class="regression-section section-pad" aria-labelledby="ci-title"><div class="section-copy"><p class="eyebrow">Make the next audit easier</p><h2 id="ci-title">Fix it once.<br>Catch it if it comes back.</h2><p>Save a baseline and let CI flag new issues. Use scan history and comparisons to follow progress, and share a PDF when someone needs the full picture.</p><a class="text-link" href="{{ route('docs.show', 'cli-reference') }}">Bring Lens into your pipeline <x-icon name="arrow"/></a><p class="regression-note">Powered by axe-core. Automated checks support manual accessibility testing.</p></div><figure class="terminal-demo"><figcaption class="demo-bar"><span>CLI commands / save and check a baseline</span><x-icon name="terminal"/></figcaption><pre><code><span class="terminal-muted"># Save the current findings</span>
php artisan lens:audit \
    https://your-app.test \
    --wcag=2.2 --aa --baseline

<span class="terminal-muted"># Fail CI only on new findings</span>
php artisan lens:audit \
    https://your-app.test \
    --wcag=2.2 --aa --fail-on-new</code></pre><div class="pipeline-result"><x-icon name="check"/><span>Use the same URL and scan options for both runs.</span></div></figure></section>

    @include('components.community-coverage')

    <section class="faq-section section-pad" aria-labelledby="faq-title"><div><p class="eyebrow">Before you start</p><h2 id="faq-title">Good questions.<br><span class="text-accent">Straight answers.</span></h2><p class="faq-intro">The essentials are here. Setup, configuration, and the technical details live in the documentation.</p><a class="text-link" href="{{ route('docs') }}">Open the documentation <x-icon name="arrow"/></a></div><div class="faq-list">
        @foreach ([
            ['Does Lens prove WCAG compliance?', 'No. Automated scanning catches many issues, but full accessibility evaluation also needs manual keyboard testing, screen reader testing, and human judgement.'],
            ['Can I use Lens without AI?', 'Yes. Audits, source mapping, interactive states, history, PDF reports, and CLI baselines work independently of the optional AI integration.'],
            ['Can AI fixes run locally?', 'Yes, with Ollama. When it uses its default localhost endpoint, the selected source context stays on the machine running Laravel. Cloud providers and remote Ollama endpoints receive that context over the network.'],
            ['Can I review and apply multiple fixes?', 'Yes. Fix All A and AA build a queue for issues with supported source locations. Review or edit proposals independently, then apply ready suggestions together. A fresh scan verifies the result.'],
            ['Can it scan interactive pages behind login?', 'Lens supports interaction-state scripts and configured authenticated scans. See Scanning Modes for setup, supported session drivers, and the limits of each scanning mode.'],
            ['Which PHP and Laravel versions are supported?', 'The core package supports PHP 8.2+ and Laravel 10-13. AI Fix is optional and requires PHP 8.3+, Laravel 12+, and laravel/ai 0.3.2 or newer.'],
            ['Does it support my frontend?', 'Lens scans rendered interfaces and maps supported source locations in Blade, React, and Vue files. Livewire and Inertia apps fit into this workflow. Some dynamic abstractions still require manual investigation.'],
        ] as [$question, $answer])
            <details><summary>{{ $question }}<span aria-hidden="true">+</span></summary><p>{{ $answer }}</p></details>
        @endforeach

    </div></section>
    <section class="install-section section-pad" aria-labelledby="install-title"><div><p class="eyebrow">Your next accessibility audit starts here</p><h2 id="install-title">Add Lens.<br><span class="text-accent">Find your first fix.</span></h2><p>PHP 8.2+ / Laravel 10-13 / MIT licensed</p></div><div><x-install-command/><div class="button-row"><a class="button button-primary" href="{{ route('docs.show', 'installation') }}">Installation guide <x-icon name="arrow"/></a><a class="text-link" href="{{ route('docs.show', 'quick-start') }}">Quick start <span aria-hidden="true">↗</span></a></div></div></section>
</main>
@include('components.product-preview-dialog')
@endsection
