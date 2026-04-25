<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DocsController extends Controller
{
    /** @var array<string, list<array{title: string, slug: string}>> */
    private array $navigation = [
        'GETTING STARTED' => [
            ['title' => 'Introduction', 'slug' => 'introduction'],
            ['title' => 'Installation', 'slug' => 'installation'],
            ['title' => 'Quick Start', 'slug' => 'quick-start'],
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
            ['title' => 'CLI Reference', 'slug' => 'cli-reference'],
            ['title' => 'Dashboard', 'slug' => 'dashboard'],
        ],
    ];

    public function redirect(): RedirectResponse
    {
        return redirect()->route('docs.show', ['page' => 'introduction']);
    }

    public function show(string $page): View
    {
        $path = resource_path("markdown/docs/{$page}.md");

        abort_unless(file_exists($path), 404);

        $markdown = (string) file_get_contents($path);
        $html = Str::markdown($markdown, [
            'html_input' => 'allow',
            'allow_unsafe_links' => false,
        ]);

        [$html, $toc] = $this->processHeadings($html);
        [$prev, $next] = $this->getPrevNext($page);

        return view('docs.show', [
            'html' => $html,
            'toc' => $toc,
            'navigation' => $this->navigation,
            'currentSlug' => $page,
            'pageTitle' => $this->resolvePageTitle($page),
            'prev' => $prev,
            'next' => $next,
        ]);
    }

    /**
     * Inject anchor IDs into h2/h3 headings and build the TOC list.
     *
     * @return array{0: string, 1: list<array{level: string, title: string, id: string}>}
     */
    private function processHeadings(string $html): array
    {
        $toc = [];

        $html = (string) preg_replace_callback(
            '/<(h[23])>(.*?)<\/h[23]>/is',
            function (array $matches) use (&$toc): string {
                $level = $matches[1];
                $text = strip_tags($matches[2]);
                $id = Str::slug($text);

                $toc[] = ['level' => $level, 'title' => $text, 'id' => $id];

                return "<{$level} id=\"{$id}\">{$matches[2]}</{$level}>";
            },
            $html,
        );

        return [$html, $toc];
    }

    private function resolvePageTitle(string $slug): string
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
     * Return the previous and next navigation items relative to the current page.
     *
     * @return array{0: array{title: string, slug: string}|null, 1: array{title: string, slug: string}|null}
     */
    private function getPrevNext(string $currentSlug): array
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
