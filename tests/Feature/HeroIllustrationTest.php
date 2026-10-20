<?php

use Illuminate\Support\Facades\Blade;

it('describes the hero illustration without exposing decorative controls or tiny image text', function (): void {
    $document = new DOMDocument;
    @$document->loadHTML(Blade::render('<x-hero-illustration />'));
    $xpath = new DOMXPath($document);
    $figures = $xpath->query('//figure[@data-hero-illustration]');

    expect($figures->length)->toBe(1);
    $figure = $figures->item(0);
    expect($figure->getAttribute('role'))->toBe('img');
    $caption = $document->getElementById($figure->getAttribute('aria-labelledby'));

    expect($caption)->not->toBeNull();
    expect($caption->tagName)->toBe('figcaption');
    expect($caption->getAttribute('class'))->toContain('sr-only');
    $description = strtolower(trim(preg_replace('/\s+/', ' ', $caption->textContent)));

    foreach (['diagnostic report', 'source', 'ai-assisted fixes', 'reviewed changes', 'fresh scan'] as $detail) {
        expect($description)->toContain($detail);
    }

    $illustration = $xpath->query('./svg', $figure);
    expect($illustration->length)->toBe(1);
    expect($illustration->item(0)->getAttribute('viewbox'))->toBe('0 0 800 640');
    expect($illustration->item(0)->getAttribute('aria-hidden'))->toBe('true');
    expect($illustration->item(0)->getAttribute('focusable'))->toBe('false');
    expect($xpath->query('.//a | .//button | .//*[@tabindex] | .//text | .//foreignobject | .//script', $figure)->length)->toBe(0);
});

it('provides a separate accessible link to each original report outside the decorative image', function (): void {
    $document = new DOMDocument;
    @$document->loadHTML(Blade::render('<x-hero-illustration />'));
    $xpath = new DOMXPath($document);
    $links = $xpath->query('//a[contains(concat(" ", normalize-space(@class), " "), " hero-preview-link ")]');

    expect($links->length)->toBe(2);

    foreach (['light', 'dark'] as $theme) {
        $link = $xpath->query('//a[contains(concat(" ", normalize-space(@class), " "), " hero-preview-link ") and contains(concat(" ", normalize-space(@class), " "), " preview-version-'.$theme.' ")]')->item(0);
        expect($link)->not->toBeNull();
        expect(trim(preg_replace('/\s+/', ' ', $link->textContent)))->toBe('Enlarge screenshot');
        expect(parse_url($link->getAttribute('href'), PHP_URL_PATH))->toBe('/assets/screenshots/dashboard-'.$theme.'.webp');
        expect($link->hasAttribute('data-product-preview'))->toBeTrue();
        expect($link->getAttribute('aria-label'))->toBe('Enlarge screenshot: Your audit, inside Lens');
        expect($link->getAttribute('aria-haspopup'))->toBe('dialog');
        expect($link->getAttribute('aria-controls'))->toBe('product-preview-dialog');
        expect($link->getAttribute('data-preview-title'))->toBe('Your audit, inside Lens');
        expect($link->getAttribute('data-preview-description'))->not->toBeEmpty();
        expect($link->getAttribute('data-preview-alt'))->toContain($theme.' theme');
        expect(mb_strlen($link->getAttribute('data-preview-alt')))->toBeLessThanOrEqual(100);
        expect($xpath->query('ancestor::*[@role="img"]', $link)->length)->toBe(0);
        expect($xpath->query('.//img', $link)->length)->toBe(0);
        [$width, $height] = getimagesize(public_path('assets/screenshots/dashboard-'.$theme.'.webp'));
        expect((int) $link->getAttribute('data-preview-width'))->toBe($width);
        expect((int) $link->getAttribute('data-preview-height'))->toBe($height);

        foreach (preg_split('/\s+/', $link->getAttribute('aria-describedby')) as $descriptionId) {
            $description = $document->getElementById($descriptionId);
            expect($description)->not->toBeNull();
            expect(trim($description->textContent))->not->toBeEmpty();
        }
    }
});

it('uses Lucide icons for code, text, AI fixes and verification', function (): void {
    $document = new DOMDocument;
    @$document->loadHTML(Blade::render('<x-hero-illustration />'));
    $xpath = new DOMXPath($document);

    foreach (['hero-code-icon' => 'code-xml', 'hero-text-icon' => 'text-align-start', 'hero-ai-icon' => 'sparkles', 'hero-verified-icon' => 'circle-check'] as $id => $name) {
        $icon = $document->getElementById($id);
        expect($icon)->not->toBeNull();
        expect($icon->getAttribute('data-lucide'))->toBe($name);
        expect($xpath->query('//use[@href="#'.$id.'"]')->length)->toBe(1);
    }

    $check = $xpath->query('//*[@id="hero-verified-icon"]/path')->item(0);
    expect($check->getAttribute('d'))->toBe('m16 9-5.5 5.5L8 12');
    expect($check->hasAttribute('transform'))->toBeFalse();
    $checkCircle = $xpath->query('//*[@id="hero-verified-icon"]/circle')->item(0);
    expect($checkCircle->getAttribute('cx'))->toBe('12');
    expect($checkCircle->getAttribute('cy'))->toBe('12');
    expect($checkCircle->getAttribute('r'))->toBe('10');
});

it('places AI on the flat tile and code and text on their own cube faces', function (): void {
    $document = new DOMDocument;
    @$document->loadHTML(Blade::render('<x-hero-illustration />'));
    $xpath = new DOMXPath($document);

    foreach (['code' => 'hero-code-icon', 'text' => 'hero-text-icon'] as $face => $iconId) {
        $faceIcon = $xpath->query('//*[@data-hero-face="'.$face.'"]/use');
        expect($faceIcon->length)->toBe(1);
        expect($faceIcon->item(0)->getAttribute('href'))->toBe('#'.$iconId);
        expect($xpath->query('//*[@data-hero-face="'.$face.'"]//use[@href="#hero-ai-icon"]')->length)->toBe(0);
        expect($xpath->query('//*[@id="'.$iconId.'"]/path[@transform]')->length)->toBe(0);
        expect($xpath->query('//*[@data-hero-face-surface="'.$face.'"]')->length)->toBe(1);
    }

    expect($xpath->query('//*[@data-hero-tile="ai"]//use[@href="#hero-ai-icon"]')->length)->toBe(1);
    expect($xpath->query('//*[@data-hero-tile-surface="ai"]/rect')->length)->toBe(1);
    expect($xpath->query('//*[@id="hero-code-icon"]/path')->length)->toBe(3);
    expect($xpath->query('//*[@id="hero-text-icon"]/path')->length)->toBe(3);

    foreach (['hero-ai-icon', 'hero-verified-icon'] as $iconId) {
        $icon = $xpath->query('//use[@href="#'.$iconId.'"]')->item(0);
        preg_match('/rotate\(-45 ([\d.]+) ([\d.]+)\) translate\(([\d.]+) ([\d.]+)\) scale\(([\d.]+)\)/', $icon->getAttribute('transform'), $transform);

        expect($transform)->toHaveCount(6);
        expect((float) $transform[3] + 12 * (float) $transform[5])->toEqualWithDelta((float) $transform[1], 0.001);
        expect((float) $transform[4] + 12 * (float) $transform[5])->toEqualWithDelta((float) $transform[2], 0.001);
    }
});

it('uses the original diagnostic report captures for both illustration themes', function (): void {
    $document = new DOMDocument;
    @$document->loadHTML(Blade::render('<x-hero-illustration />'));
    $xpath = new DOMXPath($document);

    foreach (['light', 'dark'] as $theme) {
        $images = $xpath->query('//figure[@data-hero-illustration]//image[@class="hero-report-'.$theme.'"]');

        expect($images->length)->toBe(1);
        $image = $images->item(0);
        expect(parse_url($image->getAttribute('href'), PHP_URL_PATH))->toBe('/assets/screenshots/dashboard-'.$theme.'.webp');
        expect(public_path('assets/screenshots/dashboard-'.$theme.'.webp'))->toBeFile();
    }
});

it('keeps illustration silhouettes and brand symbols legible in both themes', function (string $selector): void {
    $css = (string) file_get_contents(resource_path('css/app.css'));
    preg_match('/'.preg_quote($selector, '/').'\s*\{(?<declarations>.*?)\n\}/s', $css, $block);
    preg_match_all('/(?<name>--hero-[a-z-]+):\s*(?<value>#[0-9a-f]{6});/i', $block['declarations'] ?? '', $matches);
    $colors = array_combine($matches['name'], $matches['value']);

    foreach (['--hero-edge', '--hero-ink'] as $foreground) {
        expect(accessibilityContrastRatio($colors[$foreground], $colors['--hero-surface']))
            ->toBeGreaterThanOrEqual(3.0, "{$selector} {$foreground} should distinguish the illustration's shapes.");
    }

    foreach (['--hero-red-top', '--hero-red-front', '--hero-red-side'] as $surface) {
        expect(accessibilityContrastRatio($colors['--hero-on-red'], $colors[$surface]))
            ->toBeGreaterThanOrEqual(4.5, "{$selector} brand symbol should remain legible on {$surface}.");
    }
})->with(['light' => ':root', 'dark' => '.dark']);
