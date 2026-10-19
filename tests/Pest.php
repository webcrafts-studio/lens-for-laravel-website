<?php

use Illuminate\Support\Facades\Http;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
 // ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

beforeEach(function (): void {
    config()->set('services.github.repository', 'webcrafts-studio/lens-for-laravel');
    config()->set('services.github.token', null);
    Http::preventStrayRequests();

    Http::fake([
        'https://api.github.com/repos/webcrafts-studio/lens-for-laravel' => Http::response([
            'stargazers_count' => 84000,
        ]),
    ]);
})->in('Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function something()
{
    // ..
}

function accessibilityThemeVariables(string $selector): array
{
    $css = (string) file_get_contents(resource_path('css/app.css'));
    $pattern = '/'.preg_quote($selector, '/').'\s*\{(?<declarations>.*?)\n\}/s';

    preg_match($pattern, $css, $matches);
    preg_match_all(
        '/(?<name>--lens-[a-z-]+):\s*(?<value>#[0-9a-f]{6});/i',
        $matches['declarations'] ?? '',
        $variables,
    );

    return array_combine($variables['name'], $variables['value']);
}

function accessibilityContrastRatio(string $foreground, string $background): float
{
    $luminance = function (string $color): float {
        $channels = array_map(
            fn (string $channel): int => hexdec($channel),
            str_split(ltrim($color, '#'), 2),
        );

        $linear = array_map(
            function (int $channel): float {
                $value = $channel / 255;

                return $value <= 0.04045
                    ? $value / 12.92
                    : (($value + 0.055) / 1.055) ** 2.4;
            },
            $channels,
        );

        return (0.2126 * $linear[0]) + (0.7152 * $linear[1]) + (0.0722 * $linear[2]);
    };

    $foregroundLuminance = $luminance($foreground);
    $backgroundLuminance = $luminance($background);

    return (max($foregroundLuminance, $backgroundLuminance) + 0.05)
        / (min($foregroundLuminance, $backgroundLuminance) + 0.05);
}

function accessibilityCssColorVariable(string $name): string
{
    $css = (string) file_get_contents(resource_path('css/app.css'));

    preg_match(
        '/'.preg_quote($name, '/').':\s*(?<value>#[0-9a-f]{6});/i',
        $css,
        $matches,
    );

    return $matches['value'];
}
