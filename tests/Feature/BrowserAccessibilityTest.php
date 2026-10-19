<?php

use App\Documentation;
use Symfony\Component\Process\Process;

test('the rendered site passes accessibility, reflow and keyboard regressions', function (array $paths): void {
    $process = new Process([
        'node',
        base_path('tests/Browser/site-accessibility.mjs'),
        json_encode([
            'baseUrl' => getenv('LENS_BROWSER_TEST_URL'),
            'paths' => $paths,
        ], JSON_THROW_ON_ERROR),
    ], base_path());
    $process->setTimeout(600);
    $process->run();

    expect($process->isSuccessful())->toBeTrue($process->getOutput().$process->getErrorOutput());
})->with([
    'homepage' => [['/']],
    'documentation' => [array_map(
        fn (array $page): string => '/docs/'.$page['slug'],
        array_merge(...array_values((new Documentation)->navigation)),
    )],
])->skip(getenv('LENS_BROWSER_TEST_URL') === false, 'Set LENS_BROWSER_TEST_URL to audit the built site with Chromium and axe-core.');
