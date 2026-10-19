<?php

it('links both navigation menus to the labelled CI and references sections from any page', function (string $url) {
    $response = $this->get($url)->assertSuccessful();
    $document = new DOMDocument;
    @$document->loadHTML($response->getContent());
    $xpath = new DOMXPath($document);
    $sections = ['CI/CD' => 'ci-cd', 'References' => 'community'];

    foreach (['Main navigation', 'Mobile navigation'] as $navigationLabel) {
        $navigation = $xpath->query('//nav[@aria-label="'.$navigationLabel.'"]')->item(0);

        expect($navigation)->not->toBeNull();

        foreach ($sections as $label => $sectionId) {
            $links = $xpath->query('.//a[normalize-space(.)="'.$label.'"]', $navigation);

            expect($links->length)->toBe(1)
                ->and($links->item(0)->getAttribute('href'))->toBe(route('home').'#'.$sectionId);
        }
    }

    $homeDocument = new DOMDocument;
    @$homeDocument->loadHTML($this->get('/')->assertSuccessful()->getContent());

    foreach ($sections as $sectionId) {
        $section = $homeDocument->getElementById($sectionId);

        expect($section)->not->toBeNull()
            ->and($section->tagName)->toBe('section');

        $heading = $homeDocument->getElementById($section->getAttribute('aria-labelledby'));

        expect($heading)->not->toBeNull()
            ->and(trim($heading->textContent))->not->toBeEmpty();
    }
})->with(['/', '/docs/configuration']);

it('connects every documentation search trigger to its labelled modal', function (string $url) {
    $response = $this->get($url)->assertSuccessful();
    $document = new DOMDocument;
    @$document->loadHTML($response->getContent());
    $xpath = new DOMXPath($document);
    $dialog = $document->getElementById('docs-search');

    expect($dialog->tagName)->toBe('dialog');
    expect($document->getElementById($dialog->getAttribute('aria-labelledby'))->textContent)
        ->toBe('Search documentation');

    foreach ($xpath->query('//*[@data-search-open]') as $trigger) {
        expect($trigger->tagName)->toBe('button')
            ->and($trigger->getAttribute('type'))->toBe('button')
            ->and($trigger->getAttribute('aria-controls'))->toBe($dialog->getAttribute('id'))
            ->and($trigger->getAttribute('aria-haspopup'))->toBe('dialog');
    }

    $input = $document->getElementById('docs-search-input');
    expect($xpath->query('//label[@for="docs-search-input"]')->length)->toBe(1)
        ->and($document->getElementById($input->getAttribute('aria-controls'))->tagName)->toBe('ul')
        ->and($document->getElementById($input->getAttribute('aria-describedby'))->textContent)
        ->toContain('Escape to close');

    foreach (['search-status', 'copy-status'] as $id) {
        $status = $document->getElementById($id);
        expect($status->getAttribute('role'))->toBe('status')
            ->and($status->getAttribute('aria-atomic'))->toBe('true');
    }

    expect($document->getElementById('copy-status')->getAttribute('class'))->toBe('sr-only');
})->with(['/', '/docs/configuration']);

it('exposes the current documentation page in a named breadcrumb', function () {
    $response = $this->get('/docs/configuration')->assertSuccessful();
    $document = new DOMDocument;
    @$document->loadHTML($response->getContent());
    $xpath = new DOMXPath($document);
    $breadcrumb = $xpath->query('//main//nav[@aria-label="Breadcrumb"]')->item(0);

    expect($breadcrumb)->not->toBeNull()
        ->and($xpath->query('.//*[@aria-current="page"]', $breadcrumb)->item(0)->textContent)->toBe('Config File');
});

it('gives documentation tables column associations before JavaScript runs', function () {
    $response = $this->get('/docs/configuration')->assertSuccessful();
    $document = new DOMDocument;
    @$document->loadHTML($response->getContent());
    $xpath = new DOMXPath($document);
    $headers = $xpath->query('//main//table//th');

    expect($headers->length)->toBeGreaterThan(0);

    foreach ($headers as $header) {
        expect($header->getAttribute('scope'))->toBe('col');
    }
});
