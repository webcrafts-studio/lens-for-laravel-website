<?php

test('the privacy policy identifies the controller and describes the configured services', function (): void {
    $this->get(route('privacy'))->assertSuccessful()
        ->assertViewIs('privacy')
        ->assertSeeText('Privacy policy')
        ->assertSeeText('Jakub Lipiński')
        ->assertSee('mailto:kontakt@lipinskijakub.pl', false)
        ->assertSeeText('Hostinger')
        ->assertSeeText('Cloudflare')
        ->assertSeeText('Google Analytics 4')
        ->assertSeeText('Google Signals is disabled')
        ->assertSeeText('not linked to Google Ads')
        ->assertSeeText('two-month retention period')
        ->assertSeeText(config('consent.cookie.name'))
        ->assertSeeText(config('consent.retention_days').' days')
        ->assertSeeText('lens-theme');
});

test('the privacy policy has a labelled reading structure and working section links', function (): void {
    $document = new DOMDocument;
    @$document->loadHTML($this->get(route('privacy'))->assertSuccessful()->getContent());
    $xpath = new DOMXPath($document);
    $main = $document->getElementById('main-content');

    expect($document->documentElement->getAttribute('lang'))->toBe('en')
        ->and($xpath->query('//main')->length)->toBe(1)
        ->and($main->getAttribute('tabindex'))->toBe('-1')
        ->and($xpath->query('//h1')->length)->toBe(1)
        ->and($xpath->query('//a[@class="skip-link"]')->item(0)->getAttribute('href'))->toBe('#main-content');

    $links = $xpath->query('//nav[@aria-labelledby="privacy-contents-title"]//a');
    expect($links->length)->toBe(10);

    foreach ($links as $link) {
        $heading = $document->getElementById(substr($link->getAttribute('href'), 1));

        expect($heading)->not->toBeNull()
            ->and($heading->tagName)->toBe('h2')
            ->and($heading->getAttribute('tabindex'))->toBe('-1')
            ->and($heading->parentNode->getAttribute('aria-labelledby'))->toBe($heading->getAttribute('id'));
    }

    $control = $xpath->query('//main//button[@data-consent-open]')->item(0);
    expect($control->getAttribute('type'))->toBe('button')
        ->and($control->getAttribute('aria-haspopup'))->toBe('dialog')
        ->and($document->getElementById($control->getAttribute('aria-controls'))->tagName)->toBe('dialog');
});

test('the footer and Consent link to the privacy policy', function (string $path): void {
    expect(config('consent.ui.policy_url'))->toBe(route('privacy', absolute: false));

    $document = new DOMDocument;
    @$document->loadHTML($this->get($path)->assertSuccessful()->getContent());
    $xpath = new DOMXPath($document);

    expect($xpath->query('//footer//a[@href="'.route('privacy').'"]')->length)->toBe(1)
        ->and($xpath->query('//section[@data-consent-banner]//a[@class="consent-policy" and @href="'.config('consent.ui.policy_url').'"]')->length)->toBe(1);
})->with(['/', '/privacy-policy', '/docs/configuration']);

test('the privacy policy keeps consent retention information in sync with configuration', function (): void {
    config()->set('consent.retention_days', 90);
    config()->set('session.lifetime', 60);

    $this->get(route('privacy'))->assertSuccessful()
        ->assertSeeText('90 days')
        ->assertSeeText('60 minutes');
});
