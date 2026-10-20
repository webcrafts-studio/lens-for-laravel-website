<?php

it('presents the six community references in the requested order with accurate copy', function (): void {
    $response = $this->get('/')->assertSuccessful();
    $document = new DOMDocument;
    @$document->loadHTML(mb_convert_encoding($response->getContent(), 'HTML-ENTITIES', 'UTF-8'));
    $xpath = new DOMXPath($document);
    $cards = $xpath->query('//*[@id="community"]//article');
    $references = [
        [
            'https://laravel-news.com/lens-for-laravel-brings-wcag-auditing-to-your-local-dev-workflow',
            'WCAG auditing in your local development workflow.',
            'A featured article about Lens for Laravel published by Laravel News.',
            'Read the article',
            'Laravel News',
        ],
        [
            'https://podcast.laravel-news.com/255',
            'Lens featured in Laravel News Podcast #255.',
            'Lens for Laravel was also mentioned in the official Laravel News podcast.',
            'Listen to the episode',
            'Laravel News Podcast',
        ],
        [
            'https://x.com/PovilasKorop/status/2099000377999831096',
            'Shared by Povilas Korop.',
            'Lens shared by the founder of Laravel Daily on X.',
            'View the post',
            'Povilas Korop',
        ],
        [
            'https://www.linkedin.com/posts/povilas-korop-laravel-developer_new-laravel-package-webcrafts-studiolens-for-laravel-activity-7505589580398522368-2PoF',
            'Shared on LinkedIn by Povilas Korop.',
            'Lens highlighted by the founder of Laravel Daily on LinkedIn.',
            'View the post',
            'Povilas Korop',
        ],
        [
            'https://www.angelcruz.dev/post/lens-laravel-accesibilidad-axe-ai-fix',
            'Independent technical review of Lens.',
            'A detailed article covering WCAG auditing, AI fixes, source mapping, and CI/CD.',
            'Read the review',
            'Ángel Cruz',
        ],
        [
            'https://www.programmez.com/actualites/lens-laravel-v3-outil-pour-verifier-la-conformite-wcag-2x-39792',
            'Lens for Laravel v3 featured by Programmez!',
            'Independent magazine coverage of Lens for Laravel v3 and its WCAG-focused workflow.',
            'Read the article',
            'Programmez!',
        ],
    ];

    expect($cards->length)->toBe(count($references));

    foreach ($references as $index => [$url, $title, $description, $action, $brand]) {
        $card = $cards->item($index);
        expect($xpath->query('.//a', $card)->item(0)->getAttribute('href'))->toBe($url);
        expect(trim($xpath->query('.//h3', $card)->item(0)->textContent))->toBe($title);
        expect(trim($xpath->query('.//*[contains(concat(" ", normalize-space(@class), " "), " coverage-copy ")]/p[not(contains(@class,"coverage-kind"))]', $card)->item(0)->textContent))->toBe($description);
        expect(trim($xpath->query('.//*[contains(@class,"coverage-action")]', $card)->item(0)->textContent))->toBe($action);
        expect(trim($xpath->query('.//*[contains(@class,"coverage-kind")]', $card)->item(0)->textContent))->toContain($brand);
    }

    $response->assertDontSee('facebook.com/laraveldaily')
        ->assertDontSee('Artisan Weekly')
        ->assertDontSee('daniel-newns_welcome-to-edition-76')
        ->assertDontSee('Trusted by thousands');
});

it('presents six labelled publication cards with locally hosted graphics', function (): void {
    $response = $this->get('/')->assertSuccessful();
    $document = new DOMDocument;
    @$document->loadHTML($response->getContent());
    $xpath = new DOMXPath($document);
    $cards = $xpath->query('//*[@id="community"]//article');
    $publishers = ['laravel-news', 'laravel-news-podcast', 'x', 'linkedin', 'angel-cruz', 'programmez'];

    expect($cards->length)->toBe(6);

    foreach ($cards as $index => $card) {
        $links = $xpath->query('.//a', $card);
        $images = $xpath->query('.//img', $card);

        expect($links->length)->toBe(1);
        expect($images->length)->toBe(1);

        $link = $links->item(0);
        $title = $xpath->query('.//h3', $link)->item(0);

        expect($title)->not->toBeNull();
        expect($title->tagName)->toBe('h3');
        expect(trim($title->textContent))->not->toBeEmpty();
        expect($link->getAttribute('target'))->toBe('_blank');
        expect(explode(' ', $link->getAttribute('rel')))->toContain('noopener', 'noreferrer');
        expect($link->hasAttribute('aria-labelledby'))->toBeFalse();
        expect($link->hasAttribute('aria-label'))->toBeFalse();

        $image = $images->item(0);
        $assetPath = parse_url($image->getAttribute('src'), PHP_URL_PATH);

        expect($assetPath)->toStartWith('/assets/coverage-');
        expect(basename($assetPath))->toStartWith('coverage-'.$publishers[$index].'.');
        expect(public_path(ltrim($assetPath, '/')))->toBeFile();
        expect($image->getAttribute('alt'))->toBe('');
        expect($image->getAttribute('loading'))->toBe('lazy');
        expect((int) $image->getAttribute('width'))->toBeGreaterThan(0);
        expect((int) $image->getAttribute('height'))->toBeGreaterThan(0);
    }
});

it('ships valid publication and platform logos for the displayed references', function (): void {
    foreach (['laravel-news', 'x', 'linkedin', 'angel-cruz'] as $publisher) {
        $logo = simplexml_load_file(public_path('assets/coverage-'.$publisher.'.svg'));

        expect($logo)->not->toBeFalse();
        expect((string) $logo['viewBox'])->not->toBeEmpty();
    }

    expect(array_slice(getimagesize(public_path('assets/coverage-laravel-news-podcast.webp')), 0, 2))->toBe([800, 800]);

    $response = $this->get('/')->assertSuccessful();
    $document = new DOMDocument;
    @$document->loadHTML($response->getContent());
    $xpath = new DOMXPath($document);
    $programmez = $xpath->query('//*[@id="community"]//img[contains(@src,"coverage-programmez.")]');
    expect($programmez->length)->toBe(1);
    $path = public_path(ltrim(parse_url($programmez->item(0)->getAttribute('src'), PHP_URL_PATH), '/'));

    if (pathinfo($path, PATHINFO_EXTENSION) === 'svg') {
        $logo = simplexml_load_file($path);
        expect($logo)->not->toBeFalse();
        expect((string) $logo['viewBox'])->not->toBeEmpty();
    } else {
        [$width, $height] = getimagesize($path);
        expect($width)->toBeGreaterThan(0);
        expect($height)->toBeGreaterThan(0);
    }
});
