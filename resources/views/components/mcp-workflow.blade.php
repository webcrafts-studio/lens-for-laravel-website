<section id="mcp" class="section-pad border-t border-divider" aria-labelledby="mcp-title">
    <div class="grid items-start gap-10 lg:grid-cols-2 lg:gap-16">
        <div class="section-copy">
            <p class="eyebrow">Lens MCP / Built for your coding assistant</p>
            <h2 id="mcp-title">Your coding assistant.<br><span class="text-accent">The same Lens audit.</span></h2>
            <p>Bring real accessibility findings into Codex or Claude Code. Let your assistant inspect the source, make the change, and ask Lens to verify the result.</p>
            <p class="text-sm text-muted">Uses your assistant's model and the official Laravel MCP package. No AI Fix provider setup is required.</p>
            <a class="text-link" href="{{ route('docs.show', 'mcp') }}">Connect your assistant <x-icon name="arrow" /></a>
        </div>
        <figure class="terminal-demo min-w-0">
            <figcaption class="demo-bar"><span>One-time setup / choose your client</span><x-icon name="terminal" /></figcaption>
            <pre><code><span class="terminal-muted"># Add MCP to your Lens application</span>
composer require laravel/mcp --dev

<span class="terminal-muted"># Configure the project connection</span>
php artisan lens:mcp:install --client=codex</code></pre>
            <div class="pipeline-result"><x-icon name="check" /><span>Use --client=claude for Claude Code. Reconnect your assistant to start Lens MCP.</span></div>
        </figure>
    </div>
    <ol class="mt-12 grid gap-8 sm:grid-cols-2 xl:grid-cols-4" aria-label="Lens MCP workflow">
        <li class="min-w-0 border-t border-divider pt-5">
            <h3>Start with real findings.</h3>
            <p class="mt-3 text-sm text-muted">Audit a page or your site with the same Chromium and axe-core scanner.</p>
            <code class="mt-4 block font-mono text-xs text-accent">lens_scan</code>
        </li>
        <li class="min-w-0 border-t border-divider pt-5">
            <h3>Get the source context.</h3>
            <p class="mt-3 text-sm text-muted">Read the issue, its mapped file, and the relevant source fragment.</p>
            <code class="mt-4 block font-mono text-xs text-accent">lens_list_issues<br>lens_issue_context</code>
        </li>
        <li class="min-w-0 border-t border-divider pt-5">
            <h3>Work in your project.</h3>
            <p class="mt-3 text-sm text-muted">Your assistant edits the source and records the change as pending verification.</p>
            <code class="mt-4 block font-mono text-xs text-accent">lens_record_change</code>
        </li>
        <li class="min-w-0 border-t border-divider pt-5">
            <h3>Measure what changed.</h3>
            <p class="mt-3 text-sm text-muted">Run a fresh audit and compare the findings before calling a fix verified.</p>
            <code class="mt-4 block font-mono text-xs text-accent">lens_verify</code>
        </li>
    </ol>
</section>
