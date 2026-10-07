<?php

use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

test('deployment clears stale caches before migrations and rebuilds them before resuming traffic', function (): void {
    $workflow = Yaml::parseFile(__DIR__.'/../../.github/workflows/deploy.yml');
    $deployStep = collect($workflow['jobs']['deploy']['steps'])
        ->firstWhere('name', 'Deploy to VPS via SSH');
    $script = $deployStep['with']['script'];

    expect($workflow['on']['push']['branches'])->toContain('main');

    $commands = [
        'php artisan optimize:clear',
        'php artisan migrate --force --no-interaction',
        'php artisan optimize',
        'php artisan queue:restart',
        'php artisan up',
    ];
    $previousPosition = -1;

    foreach ($commands as $command) {
        $position = strpos($script, $command, $previousPosition + 1);

        expect($position)->not->toBeFalse()->toBeGreaterThan($previousPosition);
        $previousPosition = $position;
    }

    $syntaxCheck = new Process(['bash', '-n']);
    $syntaxCheck->setInput($script);
    $syntaxCheck->run();

    expect($syntaxCheck->isSuccessful())->toBeTrue();
});
