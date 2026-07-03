<?php

test('the application returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

test('the website identifies v3 as the current development line', function () {
    $this->get('/')
        ->assertOk()
        ->assertSee('v3.0');

    $this->get(route('docs.show', ['page' => 'introduction']))
        ->assertOk()
        ->assertSee('What&#039;s New in v3.0.0', false)
        ->assertSee('Upgrade to v3.0.0');

    $this->get(route('docs.show', ['page' => 'upgrade-v3']))
        ->assertOk()
        ->assertSee('Upgrade to v3.0.0')
        ->assertSee('current development line')
        ->assertSee('URL-aware history comparisons');
});

test('the CLI documentation covers interactive state scripts', function () {
    $this->get(route('docs.show', ['page' => 'cli-reference']))
        ->assertOk()
        ->assertSee('--states=PATH')
        ->assertSee('requires exactly one URL')
        ->assertSee('cannot be combined with')
        ->assertSee('state: Navigation open');
});

test('the v3 documentation describes the reliable AI Fix contract', function () {
    $this->get(route('docs.show', ['page' => 'ai-fix-engine']))
        ->assertOk()
        ->assertSee('Generation Reliability in v3.0')
        ->assertSee('12000 tokens')
        ->assertSee('1024-token budget')
        ->assertSee('one controlled retry')
        ->assertSee('does not expose or force a model')
        ->assertSee('Only the first reviewed occurrence is replaced');
});

test('the configuration documents consistent local HTTPS handling', function () {
    $this->get(route('docs.show', ['page' => 'configuration']))
        ->assertOk()
        ->assertSee('sitemap and page requests made by the HTTP crawler')
        ->assertSee('JavaScript-rendered crawling in Chromium')
        ->assertSee('element preview screenshots')
        ->assertSee('production-like certificate validation');
});
