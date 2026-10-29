<?php

use Symfony\Component\Process\Process;

it('keeps the header in one row and preserves navigation focus across breakpoints', function (string $url): void {
    $response = $this->get($url)->assertSuccessful();
    $manifest = json_decode(file_get_contents(public_path('build/manifest.json')), true, 512, JSON_THROW_ON_ERROR);
    $process = new Process(['node', base_path('tests/Browser/header-navigation.mjs')], base_path());
    $process->setInput(json_encode([
        'html' => $response->getContent(),
        'cssPath' => public_path('build/'.$manifest['resources/css/app.css']['file']),
    ], JSON_THROW_ON_ERROR));
    $process->setTimeout(60);
    $process->run();

    expect($process->isSuccessful())->toBeTrue($process->getOutput().$process->getErrorOutput());
})->with(['/', '/docs/ai-fix-engine']);
