<?php

use ConsentForLaravel\ConsentForLaravel\BannerSettings;

test('consent palettes meet AA text and non-text contrast requirements', function (string $palette): void {
    $colors = config('consent.ui.'.$palette);

    foreach (['text', 'muted', 'accent'] as $token) {
        expect(accessibilityContrastRatio($colors[$token], $colors['background']))
            ->toBeGreaterThanOrEqual(4.5, "{$palette}.{$token} must meet normal-text contrast.");
    }

    foreach (['border', 'control', 'focus'] as $token) {
        expect(accessibilityContrastRatio($colors[$token], $colors['background']))
            ->toBeGreaterThanOrEqual(3.0, "{$palette}.{$token} must meet non-text contrast.");
    }

    expect(accessibilityContrastRatio($colors['accent_text'], $colors['accent']))
        ->toBeGreaterThanOrEqual(4.5, 'Filled buttons and checked switch thumbs must remain readable.');
})->with(['light' => 'colors', 'dark' => 'dark_colors']);

test('consent preserves the site red accents and enables contrast diagnostics without warnings', function (): void {
    $settings = new BannerSettings(config('consent.ui'));

    expect($settings->validateContrast)->toBeTrue()
        ->and($settings->colorWarnings)->toBeEmpty()
        ->and($settings->colors['accent'])->toBe(accessibilityThemeVariables(':root')['--lens-accent'])
        ->and($settings->darkColors['accent'])->toBe(accessibilityThemeVariables('.dark')['--lens-accent']);
});

test('the shared layout renders both configured consent palettes', function (): void {
    $settings = new BannerSettings(config('consent.ui'));
    $response = $this->get('/')->assertSuccessful();

    foreach ([$settings->colors, $settings->darkColors] as $index => $colors) {
        $prefix = $index === 0 ? '--consent-' : '--consent-dark-';

        foreach ($colors as $token => $color) {
            $response->assertSee($prefix.str_replace('_', '-', $token).':'.$color.';', false);
        }
    }
});
