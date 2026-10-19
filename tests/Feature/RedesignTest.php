<?php

use App\Documentation;

it('shares all documentation pages with search on the homepage and documentation', function (string $url) {
    $response = $this->get($url)->assertSuccessful();
    $document = new DOMDocument;
    @$document->loadHTML($response->getContent());
    $element = $document->getElementById('docs-search-index');
    $index = json_decode($element->textContent, true, 512, JSON_THROW_ON_ERROR);

    expect($index)->toHaveCount(12);

    foreach ($index as $page) {
        expect($page['content'])->not->toBeEmpty()
            ->and($page['url'])->toBe(route('docs.show', $page['slug']));
    }

    expect(collect($index)->firstWhere('slug', 'ai-fix-engine')['content'])->toContain('Ollama');
})->with(['/', '/docs/ai-fix-engine']);

it('keeps normal hyphens in page text and accessible labels', function (string $url) {
    $response = $this->get($url)->assertSuccessful();
    $document = new DOMDocument;
    @$document->loadHTML(mb_convert_encoding($response->getContent(), 'HTML-ENTITIES', 'UTF-8'));
    $xpath = new DOMXPath($document);
    foreach ($xpath->query('//body//text()[not(ancestor::script)] | //@aria-label | //@title | //head/title/text()') as $node) {
        expect($node->nodeValue)->not->toMatch('/[\x{2013}\x{2014}]/u');
    }
})->with(array_merge(['/'], array_map(fn (array $item): string => '/docs/'.$item['slug'], array_merge(...array_values((new Documentation)->navigation)))));

it('retains chapter order and stable anchors on all documentation pages', function () {
    $documentation = new Documentation;
    $pages = array_merge(...array_values($documentation->navigation));

    foreach ($pages as $page) {
        $response = $this->get(route('docs.show', $page['slug']))->assertSuccessful();
        $response->assertSee('aria-current="page"', false);
        $document = new DOMDocument;
        @$document->loadHTML($response->getContent());
        $xpath = new DOMXPath($document);
        expect($xpath->query('//main//h1')->length)->toBe(1);
        foreach ($xpath->query('//aside[contains(@class,"docs-toc")]//a') as $link) {
            expect($document->getElementById(substr($link->getAttribute('href'), 1)))->not->toBeNull();
        }
    }
});

it('ships the chosen branding in the matching export sizes', function () {
    foreach (['lens-mark.png' => [512, 512], 'lens-logo.png' => [840, 256], 'lens-logo-dark.png' => [840, 256], 'apple-touch-icon.png' => [180, 180], 'social-card.png' => [1200, 630]] as $file => $size) {
        expect(array_slice(getimagesize(public_path('assets/'.$file)), 0, 2))->toBe($size);
    }

    $art = simplexml_load_file(public_path('assets/lens-workflow.svg'));
    expect((string) $art['viewBox'])->toBe('0 0 760 660');
});

it('keeps reading and control colors accessible on actual panel surfaces', function (string $selector) {
    $colors = accessibilityThemeVariables($selector);
    foreach (['--lens-page', '--lens-panel', '--lens-accent-soft'] as $surface) {
        foreach (['--lens-body', '--lens-muted', '--lens-subtle', '--lens-accent'] as $text) {
            expect(accessibilityContrastRatio($colors[$text], $colors[$surface]))->toBeGreaterThanOrEqual(4.5);
        }
        expect(accessibilityContrastRatio($colors['--lens-control'], $colors[$surface]))->toBeGreaterThanOrEqual(3.0);
    }
})->with([':root', '.dark']);
