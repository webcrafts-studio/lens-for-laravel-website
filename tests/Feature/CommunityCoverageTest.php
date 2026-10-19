<?php

it('links to all six original community references without invented testimonials', function () {
    $response = $this->get('/')->assertSuccessful();

    foreach ([
        'https://laravel-news.com/lens-for-laravel-brings-wcag-auditing-to-your-local-dev-workflow',
        'https://www.facebook.com/laraveldaily/posts/new-laravel-packagewebcrafts-studiolens-for-laravel-a-local-first-accessibility-/1420096600238703/',
        'https://www.linkedin.com/posts/povilas-korop-laravel-developer_new-laravel-package-webcrafts-studiolens-for-laravel-activity-7505589580398522368-2PoF',
        'https://podcast.laravel-news.com/255',
        'https://www.linkedin.com/posts/daniel-newns_welcome-to-edition-76-of-artisan-weekly-activity-7440691122194042882-0Gcu',
        'https://www.angelcruz.dev/post/lens-laravel-accesibilidad-axe-ai-fix',
    ] as $url) {
        $response->assertSee('href="'.$url.'"', false);
    }

    $response->assertSee('Independent technical review')->assertDontSee('Trusted by thousands');
});

it('presents six labelled publication cards with locally hosted graphics', function () {
    $response = $this->get('/')->assertSuccessful();
    $document = new DOMDocument;
    @$document->loadHTML($response->getContent());
    $xpath = new DOMXPath($document);
    $cards = $xpath->query('//*[@id="community"]//article');

    expect($cards->length)->toBe(6);

    foreach ($cards as $card) {
        $links = $xpath->query('.//a', $card);
        $images = $xpath->query('.//img', $card);

        expect($links->length)->toBe(1);
        expect($images->length)->toBe(1);

        $link = $links->item(0);
        $title = $xpath->query('.//h3', $link)->item(0);

        expect($title)->not->toBeNull();
        expect($title->tagName)->toBe('h3');
        expect(trim($title->textContent))->not->toBeEmpty();
        expect($link->hasAttribute('aria-labelledby'))->toBeFalse();
        expect($link->hasAttribute('aria-label'))->toBeFalse();

        $image = $images->item(0);
        $assetPath = parse_url($image->getAttribute('src'), PHP_URL_PATH);

        expect($assetPath)->toStartWith('/assets/coverage-');
        expect(public_path(ltrim($assetPath, '/')))->toBeFile();
        expect($image->getAttribute('alt'))->toBe('');
        expect($image->getAttribute('loading'))->toBe('lazy');
        expect((int) $image->getAttribute('width'))->toBeGreaterThan(0);
        expect((int) $image->getAttribute('height'))->toBeGreaterThan(0);
    }
});

it('ships valid original logo vectors and appropriately sized publisher images', function () {
    foreach (['laravel-news', 'laravel-daily', 'artisan-weekly', 'angel-cruz'] as $publisher) {
        $logo = simplexml_load_file(public_path('assets/coverage-'.$publisher.'.svg'));

        expect($logo)->not->toBeFalse();
        expect((string) $logo['viewBox'])->not->toBeEmpty();
    }

    expect(array_slice(getimagesize(public_path('assets/coverage-povilas-korop.jpg')), 0, 2))->toBe([400, 400]);
    expect(array_slice(getimagesize(public_path('assets/coverage-laravel-news-podcast.webp')), 0, 2))->toBe([800, 800]);
});
