<?php

use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Symfony\Component\Yaml\Yaml;

test('deployment clears stale caches before migrations and rebuilds them before resuming traffic', function (): void {
    $workflow = Yaml::parseFile(__DIR__.'/../../.github/workflows/deploy.yml');
    $deployStep = collect($workflow['jobs']['deploy']['steps'])
        ->firstWhere('name', 'Deploy to VPS via SSH');
    $script = $deployStep['with']['script'];

    expect($workflow['on']['push']['branches'])->toContain('main');

    $commands = [
        'nvm use 24',
        'npm ci',
        'npm run build',
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

test('deployment handles rewritten history and tracked changes while preserving server runtime files', function (): void {
    $workflow = Yaml::parseFile(__DIR__.'/../../.github/workflows/deploy.yml');
    $deployStep = collect($workflow['jobs']['deploy']['steps'])
        ->firstWhere('name', 'Deploy to VPS via SSH');
    $script = $deployStep['with']['script'];

    expect(preg_match('/echo "=== Synchronize checkout ==="\n(.*?)\necho "=== Install PHP dependencies ==="/s', $script, $matches))->toBe(1);
    expect($script)->toContain('npm ci')->not->toContain('npm install')->not->toContain('git clean');

    $checkoutCommands = $matches[1];
    $filesystem = new Filesystem;
    $directory = sys_get_temp_dir().'/lens-deployment-'.bin2hex(random_bytes(8));
    $remoteDirectory = $directory.'/remote';
    $serverDirectory = $directory.'/server';
    $filesystem->makeDirectory($directory, 0755, true);
    $run = function (array $command, string $workingDirectory): string {
        return (new Process($command, $workingDirectory))->mustRun()->getOutput();
    };

    try {
        $run(['git', 'init', '--initial-branch=main', $remoteDirectory], $directory);
        $run(['git', 'config', 'user.name', 'Deployment test'], $remoteDirectory);
        $run(['git', 'config', 'user.email', 'deployment@example.test'], $remoteDirectory);
        file_put_contents($remoteDirectory.'/.gitignore', ".env\n/storage\n/public/build\n");
        file_put_contents($remoteDirectory.'/page.html', 'Original website');
        $run(['git', 'add', '.'], $remoteDirectory);
        $run(['git', '-c', 'commit.gpgsign=false', 'commit', '-m', 'Original website'], $remoteDirectory);
        $originalCommit = trim($run(['git', 'rev-parse', 'HEAD'], $remoteDirectory));
        $run(['git', 'clone', $remoteDirectory, $serverDirectory], $directory);

        file_put_contents($remoteDirectory.'/page.html', 'Redesign');
        $run(['git', 'commit', '-am', 'Redesign', '--no-gpg-sign'], $remoteDirectory);
        $run(['bash', '-ec', $checkoutCommands], $serverDirectory);
        expect(file_get_contents($serverDirectory.'/page.html'))->toBe('Redesign');

        $run(['git', 'reset', '--hard', $originalCommit], $remoteDirectory);
        file_put_contents($serverDirectory.'/page.html', 'Server modification');
        file_put_contents($serverDirectory.'/.env', 'SERVER_SECRET=preserved');
        file_put_contents($serverDirectory.'/server-note.txt', 'Keep this untracked file');
        $filesystem->makeDirectory($serverDirectory.'/storage', 0755, true);
        $filesystem->makeDirectory($serverDirectory.'/public/build', 0755, true);
        file_put_contents($serverDirectory.'/storage/runtime.txt', 'Runtime data');
        file_put_contents($serverDirectory.'/public/build/asset.js', 'Built asset');

        $run(['bash', '-ec', $checkoutCommands], $serverDirectory);
        expect(file_get_contents($serverDirectory.'/page.html'))->toBe('Original website');
        expect(trim($run(['git', 'status', '--porcelain', '--untracked-files=no'], $serverDirectory)))->toBe('');

        file_put_contents($remoteDirectory.'/page.html', 'Next deployment');
        $run(['git', 'commit', '-am', 'Next deployment', '--no-gpg-sign'], $remoteDirectory);
        $run(['bash', '-ec', $checkoutCommands], $serverDirectory);

        expect(file_get_contents($serverDirectory.'/page.html'))->toBe('Next deployment');
        expect(trim($run(['git', 'status', '--porcelain', '--untracked-files=no'], $serverDirectory)))->toBe('');
        expect(file_get_contents($serverDirectory.'/.env'))->toBe('SERVER_SECRET=preserved');
        expect(file_get_contents($serverDirectory.'/storage/runtime.txt'))->toBe('Runtime data');
        expect(file_get_contents($serverDirectory.'/public/build/asset.js'))->toBe('Built asset');
        expect(file_get_contents($serverDirectory.'/server-note.txt'))->toBe('Keep this untracked file');
    } finally {
        $filesystem->deleteDirectory($directory);
    }
});
