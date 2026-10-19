<?php

it('links to all six original community references without invented testimonials', function () {
    $response = $this->get('/')->assertSuccessful();

    foreach ([
        'https://laravel-news.com/lens-for-laravel-brings-wcag-auditing-to-your-local-dev-workflow',
        'https://www.facebook.com/laraveldaily/posts/new-laravel-packagewebcrafts-studiolens-for-laravel-a-local-first-accessibility-/1420096600238703/',
        'https://www.linkedin.com/posts/povilas-korop-laravel-developer_new-laravel-package-webcrafts-studiolens-for-laravel-activity-7505589580398522368-2PoF',
        'https://podcast.laravel-news.com/255',
        'https://www.linkedin.com/posts/daniel-newns_welcome-to-edition-76-of-artisan-weekly-activity-7440691122194042882-0Gcu',
        'https://www.angelcruz.dev/post/lens-laravel-accesibilidad-axe-ai-fix',
    ] as $url) {
        $response->assertSee('href="'.$url.'"', false);
    }

    $response->assertSee('Independent technical review')->assertDontSee('Trusted by thousands');
});
