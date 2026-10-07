<?php

namespace App\Providers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewInstance;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['welcome', 'docs.layout'], function (ViewInstance $view): void {
            $repository = (string) config('services.github.repository');
            $token = config('services.github.token');

            $stars = Cache::remember(
                "github-stars:{$repository}",
                fn (int|false $stars): \DateTimeInterface => now()->addMinutes($stars === false ? 5 : 360),
                function () use ($repository, $token): int|false {
                    return rescue(function () use ($repository, $token): int|false {
                        $request = Http::withHeaders([
                            'Accept' => 'application/vnd.github+json',
                            'User-Agent' => config('app.name', 'Laravel'),
                            'X-GitHub-Api-Version' => '2022-11-28',
                        ])->timeout(3);

                        if (is_string($token) && $token !== '') {
                            $request = $request->withToken($token);
                        }

                        $response = $request->get("https://api.github.com/repos/{$repository}");

                        if (! $response->successful()) {
                            return false;
                        }

                        $stars = $response->json('stargazers_count');

                        return is_int($stars) && $stars >= 0 ? $stars : false;
                    }, false, false);
                },
            );

            $view->with('githubStars', $stars === false ? null : $stars);
        });
    }
}
