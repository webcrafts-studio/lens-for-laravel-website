<?php

namespace App;

use Illuminate\Support\Str;

class Documentation
{
    /** @var array<string, list<array{title: string, slug: string}>> */
    public array $navigation = [
        'GETTING STARTED' => [
            ['title' => 'Introduction', 'slug' => 'introduction'],
            ['title' => 'Installation', 'slug' => 'installation'],
            ['title' => 'Quick Start', 'slug' => 'quick-start'],
            ['title' => 'Version 3 Upgrades', 'slug' => 'upgrade-v3'],
            ['title' => 'Upgrade to v2.1.0', 'slug' => 'upgrade-v2-1'],
            ['title' => 'Upgrade to v2.0.0', 'slug' => 'upgrade-v2'],
        ],
        'CONFIGURATION' => [
            ['title' => 'Config File', 'slug' => 'configuration'],
            ['title' => 'Frontend Support', 'slug' => 'frontend-support'],
        ],
        'SCANNING' => [
            ['title' => 'Scanning Modes', 'slug' => 'scanning-modes'],
        ],
        'FEATURES' => [
            ['title' => 'AI Fix Engine', 'slug' => 'ai-fix-engine'],
            ['title' => 'Lens MCP', 'slug' => 'mcp'],
            ['title' => 'CLI Reference', 'slug' => 'cli-reference'],
            ['title' => 'Dashboard', 'slug' => 'dashboard'],
        ],
    ];

    public function title(string $slug): string
    {
        foreach ($this->navigation as $items) {
            foreach ($items as $item) {
                if ($item['slug'] === $slug) {
                    return $item['title'];
                }
            }
        }

        return ucwords(str_replace('-', ' ', $slug));
    }

    /**
     * @return list<array{title: string, slug: string, section: string, url: string, content: string}>
     */
    public function searchIndex(): array
    {
        $index = [];

        foreach ($this->navigation as $section => $items) {
            foreach ($items as $item) {
                $path = resource_path("markdown/docs/{$item['slug']}.md");

                if (! file_exists($path)) {
                    continue;
                }

                $markdown = (string) file_get_contents($path);
                $html = Str::markdown($markdown, [
                    'html_input' => 'strip',
                    'allow_unsafe_links' => false,
                ]);

                $index[] = [
                    'title' => $item['title'],
                    'slug' => $item['slug'],
                    'section' => $section,
                    'url' => route('docs.show', $item['slug']),
                    'content' => Str::of(strip_tags($html))
                        ->replaceMatches('/\s+/', ' ')
                        ->trim()
                        ->toString(),
                ];
            }
        }

        return $index;
    }

    /**
     * Return the previous and next navigation items relative to the current page.
     *
     * @return array{0: array{title: string, slug: string}|null, 1: array{title: string, slug: string}|null}
     */
    public function previousNext(string $currentSlug): array
    {
        $flat = array_merge(...array_values($this->navigation));
        $slugs = array_column($flat, 'slug');
        $index = array_search($currentSlug, $slugs, true);

        if ($index === false) {
            return [null, null];
        }

        return [
            $index > 0 ? $flat[$index - 1] : null,
            $index < count($flat) - 1 ? $flat[$index + 1] : null,
        ];
    }
}
