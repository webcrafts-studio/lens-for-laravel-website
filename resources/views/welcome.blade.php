@extends('layouts.site')
@section('body')
<main id="main-content" tabindex="-1">
    <section class="hero section-pad" aria-labelledby="hero-title">
        <div class="hero-copy">
            <p class="eyebrow"><span class="status-dot" aria-hidden="true"></span> Made for Laravel developers</p>
            <h1 id="hero-title">Find accessibility issues.<br><span class="text-accent">Fix them at<br class="desktop-break"> the source.</span></h1>
            <p class="hero-lead">Audit your Laravel app, trace issues to your source files, and review AI-assisted fixes. All in your own development workflow.</p>
            <div class="button-row"><a class="button button-primary" href="{{ route('docs.show', 'installation') }}">Get started <x-icon name="arrow"/></a><a class="button button-secondary" href="{{ route('docs.show', 'quick-start') }}">Quick start</a></div>
            <div class="hero-install"><p class="micro">Install with Composer</p><x-install-command/></div>
            <p class="compatibility">Open source. MIT licensed. Core: PHP 8.2+ / Laravel 10-13.</p>
        </div>
        <figure class="hero-art"><img src="{{ asset('assets/lens-workflow.svg') }}" width="760" height="660" alt="" fetchpriority="high"><figcaption class="micro">Your interface. Your code. A clearer view.</figcaption></figure>
    </section>
    <div class="framework-strip" aria-label="Supported frontends"><span class="micro">At home in your stack</span><ul><li>Blade</li><li>Livewire</li><li>React</li><li>Vue</li><li>Inertia</li></ul><span class="micro">Powered by axe-core</span></div>
    <section id="features" aria-labelledby="workflow-title">
        <div class="section-label"><span>01 / The workflow</span><span>From finding to fixing</span></div>
        <div class="section-intro section-pad"><h2 id="workflow-title">A report is only<br><span class="text-accent">the beginning.</span></h2><p>Finding an issue should lead somewhere useful. Lens connects the rendered page to your source, then helps you review the next change.</p></div>
        <div class="feature-grid">
            <article class="feature-cell"><div class="cell-top"><x-icon name="scan"/><span class="micro">01</span></div><h3>Scan real pages</h3><p>Audit a URL, a list of pages, or your site. Include interactive states and pages behind login.</p><a class="text-link" href="{{ route('docs.show', 'scanning-modes') }}">Explore scanning <span aria-hidden="true">↗</span></a></article>
            <article class="feature-cell"><div class="cell-top"><x-icon name="code"/><span class="micro">02</span></div><h3>Find the source</h3><p>Trace a failing element to a Blade, React, or Vue file. Spend less time searching through your components.</p><a class="text-link" href="{{ route('docs.show', 'frontend-support') }}">See frontend support <span aria-hidden="true">↗</span></a></article>
            <article class="feature-cell"><div class="cell-top"><x-icon name="check"/><span class="micro">03</span></div><h3>Review. Apply. Verify.</h3><p>Review an AI suggestion, edit the change, and apply it. Re-scan to check the result against fresh findings.</p><a class="text-link" href="{{ route('docs.show', 'ai-fix-engine') }}">Meet the AI Fix Engine <span aria-hidden="true">↗</span></a></article>
        </div>
    </section>
    <section aria-labelledby="source-title">
        <div class="section-label"><span>02 / Source mapping</span><span>Less searching, more context</span></div>
        <div class="split-section section-pad">
            <div class="section-copy"><p class="eyebrow">From browser to editor</p><h2 id="source-title">The element you see.<br><span class="text-accent">The file behind it.</span></h2><p>A selector tells you what failed. A source location tells you where to start. Lens maps rendered elements back to the templates and components that produced them.</p><p>Blade, Livewire, React, Vue, and Inertia fit into the same workflow. Complex dynamic abstractions can still need a closer look.</p><a class="button button-soft" href="{{ route('docs.show', 'frontend-support') }}">Explore source mapping <x-icon name="arrow"/></a></div>
            <figure class="issue-demo"><figcaption class="demo-bar"><span>Example finding</span><span>WCAG A</span></figcaption><div class="issue-body"><span class="issue-tag">link-name</span><h3>Give every link a clear name.</h3><p>Ensures links have discernible text.</p><pre><code>&lt;a href="/contact"&gt;
    &lt;svg aria-hidden="true"&gt;...&lt;/svg&gt;
&lt;/a&gt;</code></pre><div class="source-location"><x-icon name="code"/><div><span class="micro">Located source / Vue</span><code>resources/js/Components/Footer.vue:112</code></div></div><p class="demo-note">One finding, connected to its source.</p></div></figure>
        </div>
    </section>
    <section id="ai-fixes" aria-labelledby="ai-title">
        <div class="section-label"><span>03 / AI Fix Engine</span><span>Cloud or local. Your choice.</span></div>
        <div class="section-intro section-pad"><h2 id="ai-title">Your fixes.<br><span class="text-accent">Your model. Your review.</span></h2><p>Generate focused changes for located A or AA issues. Inspect each diff, make your own edits, then apply ready suggestions together.</p></div>
        <div class="ai-showcase">
            <div class="queue-demo"><div class="demo-bar"><span>Example review queue</span><span>Fix All AA</span></div>
                @foreach([['button-name', 'Navigation.vue'], ['image-alt', 'hero.blade.php'], ['color-contrast', 'Button.tsx']] as [$rule, $file])
                <div class="queue-row"><span class="queue-number">0{{ $loop->iteration }}</span><div><strong>{{ $rule }}</strong><span>{{ $file }}</span></div><span class="queue-state"><x-icon name="check"/> Ready</span></div>
                @endforeach
                <div class="diff-example"><span class="micro">Review the proposed change</span><pre><code><span class="diff-remove">- &lt;button&gt;&lt;Icon /&gt;&lt;/button&gt;</span>
<span class="diff-add">+ &lt;button aria-label="Open menu"&gt;
+     &lt;Icon aria-hidden="true" /&gt;
+ &lt;/button&gt;</span></code></pre></div><p class="demo-note">Apply ready suggestions together. Re-scan to verify.</p></div>
            <div class="ai-details"><article><x-icon name="layers"/><h3>More fixes, one review</h3><p>Generate up to three suggestions concurrently. Keep independent edits, retry one item, and apply ready proposals with a bulk action.</p></article><article><x-icon name="shield"/><h3>Local models supported</h3><p>Run Ollama on the host running Laravel. With its default localhost endpoint, the selected source context stays on that machine.</p></article><article><x-icon name="code"/><h3>Choose the model</h3><p>Use your provider's default model or configure an explicit override. Cloud providers receive the selected context when you request a fix.</p></article></div>
        </div>
        <div class="provider-strip"><p class="micro">Eight supported providers</p><ul><li>Gemini</li><li>OpenAI</li><li>Anthropic</li><li>OpenRouter</li><li>xAI</li><li>DeepSeek</li><li>Mistral</li><li>Ollama <span class="provider-local">Local</span></li></ul></div>
        <div class="section-foot"><p>AI is optional. Requires PHP 8.3+, Laravel 12+, and laravel/ai 0.3.2+.</p><a class="text-link" href="{{ route('docs.show', 'ai-fix-engine') }}">Read about AI fixes <span aria-hidden="true">↗</span></a></div>
    </section>
    <section aria-labelledby="states-title">
        <div class="section-label"><span>04 / Beyond the first screen</span><span>Your app has more than one state</span></div>
        <div class="section-intro section-pad"><h2 id="states-title">Test the pages<br><span class="text-accent">your users actually reach.</span></h2><p>A closed menu is only half the story. Audit the interface after interactions, across routes, and behind your application's login.</p></div>
        <div class="feature-grid"><article class="feature-cell"><x-icon name="scan"/><h3>One page or a whole site</h3><p>Target URLs or crawl your application. Enable JavaScript-rendered crawling for SPA and Inertia navigation.</p></article><article class="feature-cell"><x-icon name="layers"/><h3>Open menus. Filled forms.</h3><p>Record reusable click, type, and wait steps to audit interactive states on a page.</p></article><article class="feature-cell"><x-icon name="shield"/><h3>Pages behind login</h3><p>Configure an authenticated scan user to include interfaces that a public crawler cannot reach.</p></article></div>
        <div class="section-foot"><span>Four scanning modes, with authenticated scans when configured.</span><a class="text-link" href="{{ route('docs.show', 'scanning-modes') }}">Explore scan modes <span aria-hidden="true">↗</span></a></div>
    </section>
    <section aria-labelledby="ci-title">
        <div class="section-label"><span>05 / CLI and CI</span><span>A quieter path to fewer regressions</span></div>
        <div class="split-section section-pad"><div class="section-copy"><p class="eyebrow">Part of your development workflow</p><h2 id="ci-title">Catch regressions.<br><span class="text-accent">Keep moving forward.</span></h2><p>Save a baseline, then fail CI when new issues appear. Choose the WCAG version and level that your project targets.</p><p>Follow findings over time with scan history and comparisons, inspect element previews, and share PDF reports.</p><a class="button button-soft" href="{{ route('docs.show', 'cli-reference') }}">Explore the CLI <x-icon name="arrow"/></a></div><figure class="terminal-demo"><figcaption class="demo-bar"><span>Example CI command</span><x-icon name="terminal"/></figcaption><pre><code><span class="terminal-muted"># After saving your baseline</span>
php artisan lens:audit \
    https://your-app.test \
    --wcag=2.2 \
    --aa --crawl \
    --fail-on-new

<span class="terminal-muted">WCAG 2.2 / Level A + AA</span>
Compare with saved baseline
<span class="terminal-success">No new violations found.</span></code></pre><p class="demo-note">Automatic checks support your manual accessibility testing.</p></figure></div>
    </section>
    <section class="getting-started" aria-labelledby="start-title"><div class="section-label"><span>07 / Getting started</span><span>A clear path from install to insight</span></div><div class="section-intro section-pad"><h2 id="start-title">Make accessibility part<br>of your next change.</h2><a class="text-link" href="{{ route('docs') }}">Browse the docs <span aria-hidden="true">↗</span></a></div><ol class="start-grid"><li><span class="micro">01 / Install</span><h3>Add Lens to your app</h3><p>Install the package and browser dependencies. AI can be added when you need it.</p><a href="{{ route('docs.show', 'installation') }}">Installation <span aria-hidden="true">↗</span></a></li><li><span class="micro">02 / Scan</span><h3>Run your first audit</h3><p>Open the dashboard or use Artisan. Start with a page, then explore more states.</p><a href="{{ route('docs.show', 'quick-start') }}">Quick start <span aria-hidden="true">↗</span></a></li><li><span class="micro">03 / Improve</span><h3>Review and verify</h3><p>Follow findings to your code, review the changes, and run a fresh scan.</p><a href="{{ route('docs.show', 'dashboard') }}">Dashboard guide <span aria-hidden="true">↗</span></a></li></ol></section>
    <section class="faq-section section-pad" aria-labelledby="faq-title"><div><p class="eyebrow">A few things before you start</p><h2 id="faq-title">Before your<br><span class="text-accent">first audit.</span></h2></div><div class="faq-list">
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
    <section class="final-cta section-pad"><img src="{{ asset('assets/lens-mark.svg') }}" alt="" width="64" height="64" loading="lazy"><p class="eyebrow">Open source for Laravel</p><h2>Build with a<br><span class="text-accent">clearer view.</span></h2><div class="button-row"><a class="button button-primary" href="{{ route('docs.show', 'installation') }}">Get started <x-icon name="arrow"/></a><a class="button button-secondary" href="https://github.com/webcrafts-studio/lens-for-laravel">View on GitHub</a></div><p>Keep finding. Keep improving. Keep building.</p></section>
</main>
@endsection
