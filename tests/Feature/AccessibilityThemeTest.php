<?php

test('light and dark reading tokens meet their contrast targets', function (string $selector) {
    $colors = accessibilityThemeVariables($selector);

    foreach (['--lens-content', '--lens-body', '--lens-muted'] as $token) {
        expect(accessibilityContrastRatio($colors[$token], $colors['--lens-page']))
            ->toBeGreaterThanOrEqual(7.0, "{$selector} {$token} should meet an AAA reading-text target.");
    }

    foreach (['--lens-subtle', '--lens-accent'] as $token) {
        expect(accessibilityContrastRatio($colors[$token], $colors['--lens-page']))
            ->toBeGreaterThanOrEqual(4.5, "{$selector} {$token} should meet the AA normal-text target.");
    }

    foreach (['--lens-control', '--lens-focus'] as $token) {
        expect(accessibilityContrastRatio($colors[$token], $colors['--lens-page']))
            ->toBeGreaterThanOrEqual(3.0, "{$selector} {$token} should meet the non-text contrast target.");
    }

    expect(accessibilityContrastRatio($colors['--lens-on-accent'], $colors['--lens-accent-solid']))
        ->toBeGreaterThanOrEqual(4.5, "{$selector} solid accent content should meet the AA normal-text target.");
})->with([
    'light mode' => ':root',
    'dark mode' => '.dark',
]);

test('terminal and code tokens keep compact technical content readable', function () {
    $surface = accessibilityCssColorVariable('--color-terminal-surface');

    foreach (['--color-terminal', '--color-terminal-muted', '--color-terminal-subtle', '--color-terminal-red'] as $token) {
        expect(accessibilityContrastRatio(accessibilityCssColorVariable($token), $surface))
            ->toBeGreaterThanOrEqual(4.5, "{$token} should meet the AA normal-text target.");
    }

    expect(accessibilityContrastRatio(accessibilityCssColorVariable('--color-terminal-divider'), $surface))
        ->toBeGreaterThanOrEqual(4.5, 'Terminal dividers should meet the AA normal-text contrast target.');

    expect(accessibilityContrastRatio(accessibilityCssColorVariable('--color-terminal-border'), $surface))
        ->toBeGreaterThanOrEqual(3.0, 'Terminal borders should meet the non-text contrast target.');

    $css = (string) file_get_contents(resource_path('css/app.css'));
    preg_match('/\.hljs-comment,.*?color:\s*(?<value>#[0-9a-f]{6})/is', $css, $commentColor);

    expect(accessibilityContrastRatio($commentColor['value'], $surface))
        ->toBeGreaterThanOrEqual(7.0, 'Syntax-highlighted comments should remain highly readable.');
});

test('marketing page exposes accessible navigation and avoids legacy low contrast utilities', function () {
    $response = $this->get('/');

    $response->assertSuccessful()
        ->assertSee('href="#main-content"', false)
        ->assertSee('data-theme-toggle', false)
        ->assertSee('aria-label="Switch to dark mode"', false)
        ->assertSee('aria-pressed="false"', false)
        ->assertSee('id="main-content"', false)
        ->assertSee('tabindex="-1"', false)
        ->assertDontSee('text-white/30', false)
        ->assertDontSee('text-black/30', false)
        ->assertDontSee('text-[9px]', false)
        ->assertDontSee('#e53e3e', false);
});

test('documentation typography stays readable and tables preserve their native layout', function () {
    $css = (string) file_get_contents(resource_path('css/app.css'));

    expect($css)->toContain('.docs-prose')->toContain('font-size: 1rem')
        ->toContain('.docs-table-wrapper')->toContain('display: table')
        ->toContain('overflow-wrap: anywhere')
        ->not->toContain('width: 43%')->not->toContain('width: 16%');

    $response = $this->get(route('docs.show', 'configuration'))->assertSuccessful();
    $document = new DOMDocument;
    @$document->loadHTML($response->getContent());
    expect($document->getElementsByTagName('table')->length)->toBeGreaterThan(0)
        ->and($document->getElementsByTagName('th')->length)->toBeGreaterThan(0);
});

test('local Lens package dashboard is available through package discovery', function () {
    config()->set('lens-for-laravel.enabled_environments', ['testing']);

    $this->get(route('lens-for-laravel.dashboard'))
        ->assertSuccessful();
});

test('documentation pages share accessible controls and readable theme utilities', function (string $page) {
    $response = $this->get(route('docs.show', ['page' => $page]));

    $response->assertSuccessful()
        ->assertSee('href="#main-content"', false)
        ->assertSee('data-chapters', false)
        ->assertSee('<summary>Documentation', false)
        ->assertSee('aria-expanded="false"', false)
        ->assertSee('aria-modal="true"', false)
        ->assertSee('data-theme-toggle', false)
        ->assertSee('class="docs-prose"', false)
        ->assertDontSee('text-white/30', false)
        ->assertDontSee('text-black/30', false)
        ->assertDontSee('text-[9px]', false)
        ->assertDontSee('#e53e3e', false)
        ->assertSee('id="docs-search-index"', false)
        ->assertSee('id="copy-status"', false);
})->with([
    'introduction',
    'installation',
    'quick-start',
    'upgrade-v3',
    'upgrade-v2-1',
    'upgrade-v2',
    'configuration',
    'frontend-support',
    'scanning-modes',
    'ai-fix-engine',
    'cli-reference',
    'dashboard',
]);
