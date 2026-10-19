<?php

it('uses actual product captures in both themes wherever the product interface is shown', function () {
    $response = $this->get('/')->assertSuccessful();
    $document = new DOMDocument;
    @$document->loadHTML($response->getContent());
    $xpath = new DOMXPath($document);
    $previews = $xpath->query('//figure[contains(concat(" ",normalize-space(@class)," ")," product-preview ")]');

    expect($previews->length)->toBe(5);
    $mobileSources = $xpath->query('//figure[contains(@class,"hero-art")]//source[@media="(max-width: 640px)"]');
    expect($mobileSources->length)->toBe(0);

    foreach ($previews as $preview) {
        $images = $xpath->query('.//img', $preview);
        expect($images->length)->toBe(2);
        expect($xpath->query('.//figcaption', $preview)->length)->toBe(1);
        $lightPath = public_path(ltrim(parse_url($images->item(0)->getAttribute('src'), PHP_URL_PATH), '/'));
        $darkPath = public_path(ltrim(parse_url($images->item(1)->getAttribute('src'), PHP_URL_PATH), '/'));
        expect(hash_file('sha256', $lightPath))->not->toBe(hash_file('sha256', $darkPath));

        foreach ($images as $index => $image) {
            $theme = $index === 0 ? 'light' : 'dark';
            $assetPath = parse_url($image->getAttribute('src'), PHP_URL_PATH);
            expect($assetPath)->toMatch('#^/assets/screenshots/[a-z-]+-'.$theme.'\\.webp$#');
            expect(public_path(ltrim($assetPath, '/')))->toBeFile();
            $dimensions = getimagesize(public_path(ltrim($assetPath, '/')));
            expect($dimensions[0])->toBeGreaterThanOrEqual(1400);
            expect((int) $image->getAttribute('width'))->toBe($dimensions[0]);
            expect((int) $image->getAttribute('height'))->toBe($dimensions[1]);
            $link = $xpath->query('ancestor::a[1]', $image)->item(0);
            $title = $link->getAttribute('data-preview-title');
            $detail = $link->getAttribute('data-preview-description');
            expect($title)->not->toBeEmpty();
            expect($image->getAttribute('alt'))->toBe($title.' ('.$theme.' theme)');
            expect(mb_strlen($image->getAttribute('alt')))->toBeLessThanOrEqual(100);
            expect($link->getAttribute('href'))->toBe($image->getAttribute('src'));
            expect($link->getAttribute('aria-controls'))->toBe('product-preview-dialog');
            expect($link->getAttribute('aria-label'))->toStartWith('Enlarge screenshot:');
            $description = $document->getElementById($link->getAttribute('aria-describedby'));
            expect($description)->not->toBeNull();
            expect($description->tagName)->toBe('figcaption');
            expect(trim($xpath->query('./strong', $description)->item(0)->textContent))->toBe($title);
            expect($detail)->not->toBeEmpty();
            expect(trim($xpath->query('./p', $description)->item(0)->textContent))->toBe($detail);
        }
    }

    $response->assertDontSee('assets/lens-workflow.svg')
        ->assertDontSee('Illustrative review queue')
        ->assertDontSee('workflow-code')
        ->assertDontSee('state-sketch')
        ->assertDontSee('&amp;amp; Apply', false)
        ->assertSee('Accept & Apply')
        ->assertSee('prepared example proposal')
        ->assertSee('Two suggestions are ready.');
});

it('offers a keyboard accessible image viewer with a direct image fallback', function () {
    $response = $this->get('/')->assertSuccessful();
    $document = new DOMDocument;
    @$document->loadHTML($response->getContent());
    $xpath = new DOMXPath($document);
    $dialog = $document->getElementById('product-preview-dialog');

    expect($dialog->tagName)->toBe('dialog');
    expect($document->getElementById($dialog->getAttribute('aria-labelledby')))->not->toBeNull();
    expect($document->getElementById($dialog->getAttribute('aria-describedby')))->not->toBeNull();
    expect($xpath->query('.//form[@method="dialog"]//button[@aria-label="Close screenshot"]', $dialog)->length)->toBe(1);
    expect($xpath->query('.//*[@data-preview-size="fit" and @aria-pressed="true"]', $dialog)->length)->toBe(1);
    expect($xpath->query('.//*[@data-preview-size="original" and @aria-pressed="false"]', $dialog)->length)->toBe(1);
    expect($xpath->query('.//*[@role="region" and @tabindex="0" and @aria-label]', $dialog)->length)->toBe(1);
});

it('uses decorative feature icons with readable benefits and executable CI examples', function (): void {
    $response = $this->get('/')->assertSuccessful();
    $document = new DOMDocument;
    @$document->loadHTML($response->getContent());
    $xpath = new DOMXPath($document);
    $section = $xpath->query('//section[@aria-labelledby="states-title"]')->item(0);
    $cards = $xpath->query('.//article[contains(concat(" ",normalize-space(@class)," ")," journey-card ")]', $section);

    expect($cards->length)->toBe(3);

    foreach ($cards as $card) {
        $visuals = $xpath->query('.//div[contains(concat(" ",normalize-space(@class)," ")," journey-visual ")]', $card);
        expect($visuals->length)->toBe(1);
        expect($visuals->item(0)->getAttribute('aria-hidden'))->toBe('true');
        $icons = $xpath->query('.//svg', $visuals->item(0));
        expect($icons->length)->toBe(1);
        expect($icons->item(0)->getAttribute('aria-hidden'))->toBe('true');
        $paths = $xpath->query('.//path', $icons->item(0));
        expect($paths->length)->toBeGreaterThan(0);

        foreach ($paths as $path) {
            expect(trim($path->getAttribute('d')))->not->toBeEmpty();
        }

        expect($xpath->query('.//a | .//button | .//*[@tabindex]', $visuals->item(0))->length)->toBe(0);
        expect($xpath->query('.//pre | .//code', $card)->length)->toBe(0);
        expect($xpath->query('./h3', $card)->length)->toBe(1);
        expect(trim($xpath->query('./h3', $card)->item(0)->textContent))->not->toBeEmpty();
        expect(trim($xpath->query('./p[last()]', $card)->item(0)->textContent))->not->toBeEmpty();
    }

    expect($xpath->query('.//a[@href="'.route('docs.show', 'scanning-modes').'"]', $section)->length)->toBe(1);

    $response
        ->assertSee('role="group" aria-label="Supported frontends"', false)
        ->assertSee('Open it. Then scan it.')
        ->assertSee('Include the private pages.')
        ->assertSee('Look beyond one URL.')
        ->assertSee('--wcag=2.2 --aa --baseline')
        ->assertSee('--wcag=2.2 --aa --fail-on-new')
        ->assertDontSee('No new violations found.');
});

it('pairs each supported frontend and AI provider with a local decorative logo and readable name', function (): void {
    $response = $this->get('/')->assertSuccessful();
    $document = new DOMDocument;
    @$document->loadHTML($response->getContent());
    $xpath = new DOMXPath($document);
    $groups = [
        'framework-strip' => [
            'blade' => 'Blade',
            'livewire' => 'Livewire',
            'react' => 'React',
            'vue' => 'Vue',
            'inertia' => 'Inertia',
        ],
        'provider-strip' => [
            'gemini' => 'Gemini',
            'openai' => 'OpenAI',
            'anthropic' => 'Anthropic',
            'openrouter' => 'OpenRouter',
            'xai' => 'xAI',
            'deepseek' => 'DeepSeek',
            'mistral' => 'Mistral',
            'ollama' => 'Ollama Local',
        ],
    ];

    foreach ($groups as $class => $technologies) {
        $group = $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " '.$class.' ")]')->item(0);
        expect($group)->not->toBeNull();
        expect($xpath->query('.//li', $group)->length)->toBe(count($technologies));
        expect($xpath->query('.//*[@data-technology-logo]', $group)->length)->toBe(count($technologies));

        foreach ($technologies as $name => $label) {
            $logos = $xpath->query('.//li/span[@data-technology-logo="'.$name.'"]', $group);
            expect($logos->length)->toBe(1);
            $logo = $logos->item(0);
            expect($logo->getAttribute('aria-hidden'))->toBe('true');
            expect($logo->hasAttribute('tabindex'))->toBeFalse();
            expect(trim(preg_replace('/\s+/', ' ', $logo->parentNode->textContent)))->toBe($label);
            expect(preg_match('/--technology-logo:\s*url\([\'"]?([^\'"\)]+)[\'"]?\)/', $logo->getAttribute('style'), $matches))->toBe(1);
            $assetPath = parse_url($matches[1], PHP_URL_PATH);
            expect($assetPath)->toBe('/assets/brands/'.$name.'.svg');
            expect(public_path(ltrim($assetPath, '/')))->toBeFile();

            $svg = new DOMDocument;
            expect($svg->load(public_path(ltrim($assetPath, '/'))))->toBeTrue();
            expect($svg->documentElement->localName)->toBe('svg');
            expect($svg->documentElement->namespaceURI)->toBe('http://www.w3.org/2000/svg');
            $viewBox = preg_split('/[\s,]+/', trim($svg->documentElement->getAttribute('viewBox')));
            expect($viewBox)->toHaveCount(4);

            foreach ($viewBox as $value) {
                expect(is_numeric($value) && is_finite((float) $value))->toBeTrue();
            }

            expect((float) $viewBox[2])->toBeGreaterThan(0);
            expect((float) $viewBox[3])->toBeGreaterThan(0);
            expect($svg->getElementsByTagName('path')->length)->toBeGreaterThan(0);

            foreach ($svg->getElementsByTagName('path') as $path) {
                expect(trim($path->getAttribute('d')))->not->toBeEmpty();
            }
        }
    }

    $localBadge = $xpath->query('//*[contains(concat(" ", normalize-space(@class), " "), " provider-local ")]');
    expect($localBadge->length)->toBe(1);
    expect(trim($localBadge->item(0)->textContent))->toBe('Local');
    expect($xpath->query('./span[@data-technology-logo="ollama"]', $localBadge->item(0)->parentNode)->length)->toBe(1);
});
