<?php

function accessibilityThemeVariables(string $selector): array
{
    $css = (string) file_get_contents(resource_path('css/app.css'));
    $pattern = '/'.preg_quote($selector, '/').'\s*\{(?<declarations>.*?)\n\}/s';

    preg_match($pattern, $css, $matches);
    preg_match_all(
        '/(?<name>--lens-[a-z-]+):\s*(?<value>#[0-9a-f]{6});/i',
        $matches['declarations'] ?? '',
        $variables,
    );

    return array_combine($variables['name'], $variables['value']);
}

function accessibilityContrastRatio(string $foreground, string $background): float
{
    $luminance = function (string $color): float {
        $channels = array_map(
            fn (string $channel): int => hexdec($channel),
            str_split(ltrim($color, '#'), 2),
        );

        $linear = array_map(
            function (int $channel): float {
                $value = $channel / 255;

                return $value <= 0.04045
                    ? $value / 12.92
                    : (($value + 0.055) / 1.055) ** 2.4;
            },
            $channels,
        );

        return (0.2126 * $linear[0]) + (0.7152 * $linear[1]) + (0.0722 * $linear[2]);
    };

    $foregroundLuminance = $luminance($foreground);
    $backgroundLuminance = $luminance($background);

    return (max($foregroundLuminance, $backgroundLuminance) + 0.05)
        / (min($foregroundLuminance, $backgroundLuminance) + 0.05);
}

function accessibilityCssColorVariable(string $name): string
{
    $css = (string) file_get_contents(resource_path('css/app.css'));

    preg_match(
        '/'.preg_quote($name, '/').':\s*(?<value>#[0-9a-f]{6});/i',
        $css,
        $matches,
    );

    return $matches['value'];
}

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

test('documentation typography stays compact and tables preserve their native layout', function () {
    $css = (string) file_get_contents(resource_path('css/app.css'));

    expect($css)
        ->toContain('font-size: 0.9375rem')
        ->toContain('font-size: 0.75rem')
        ->toContain('.docs-table-wrapper')
        ->toContain('display: table')
        ->toContain('table-layout: fixed')
        ->toContain('max-width: 100%')
        ->toContain('display: inline-block')
        ->toContain('box-sizing: border-box')
        ->toContain('overflow-wrap: anywhere')
        ->toContain('width: 43%')
        ->toContain('width: 16%')
        ->not->toMatch('/\\.docs-table-wrapper\\s*\\{[^}]*overflow-x:\\s*auto/s');
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
        ->assertSee('aria-controls="sidebar"', false)
        ->assertSee('aria-expanded="false"', false)
        ->assertSee('aria-modal="true"', false)
        ->assertSee('data-theme-toggle', false)
        ->assertSee('class="docs-prose"', false)
        ->assertDontSee('text-white/30', false)
        ->assertDontSee('text-black/30', false)
        ->assertDontSee('text-[9px]', false)
        ->assertDontSee('#e53e3e', false)
        ->assertSee("wrapper.className = 'docs-table-wrapper'", false)
        ->assertSee("document.createElement('wbr')", false)
        ->assertSee('text-[13px] leading-5', false);
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
