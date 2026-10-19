<?php

namespace App\Http\Controllers;

use App\Documentation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DocsController extends Controller
{
    public function __construct(private Documentation $documentation) {}

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
        $html = Str::replace('<th>', '<th scope="col">', $html);
        $html = Str::replace(
            '<a href="https://github.com/',
            '<a target="_blank" rel="noopener noreferrer" aria-describedby="new-tab-description" href="https://github.com/',
            $html,
        );

        [$html, $toc] = $this->processHeadings($html);
        [$prev, $next] = $this->documentation->previousNext($page);

        return view('docs.show', [
            'html' => $html,
            'toc' => $toc,
            'navigation' => $this->documentation->navigation,
            'currentSlug' => $page,
            'pageTitle' => $this->documentation->title($page),
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
}
