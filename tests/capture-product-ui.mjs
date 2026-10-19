#!/usr/bin/env node

import assert from 'node:assert/strict';
import { createHash } from 'node:crypto';
import { access, mkdir, readFile, rm, writeFile } from 'node:fs/promises';
import { join } from 'node:path';
import { fileURLToPath } from 'node:url';
import puppeteer from 'puppeteer';

// Regenerate product assets from the installed package's unchanged Blade/Alpine UI.
// Only the explicitly labeled example AI proposal is prepared data. Scanning and
// applying that proposal use the package's actual handlers on a temporary fixture.
const projectRoot = fileURLToPath(new URL('../', import.meta.url));
const origin = process.env.LENS_CAPTURE_URL || 'https://lens-for-laravel-website.test';
const output = join(projectRoot, 'public/assets/screenshots');
const fixturePath = join(projectRoot, 'resources/views/product-demo.blade.php');
const routePath = join(projectRoot, 'routes/web.php');
const routeMarker = '\n// Temporary product capture routes.\n';
const fixAllSelector = '[x-show="eligibleFixCount(\'a\') > 0"]';
const themes = ['light', 'dark'];
const captures = [];

assert.match(new URL(origin).hostname, /(?:\.test$|^localhost$|^127\.0\.0\.1$)/);

const fixture = `<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Demo product page</title>
    <style>
        body { margin: 0; color: #111827; background: white; font: 18px/1.6 system-ui; }
        header, main, footer { max-width: 720px; margin: auto; padding: 32px; }
        header { display: flex; align-items: center; justify-content: space-between; }
        button { width: 48px; height: 48px; color: #111827; background: #f3f4f6; border: 1px solid #374151; }
        img { width: 100%; height: 180px; object-fit: contain; }
        .product-note { color: #9ca3af; }
    </style>
</head>
<body>
    <header><span>Example project</span><a href="#details">Documentation</a></header>
    <main>
        <h1>Example project</h1>
        <img id="brand-logo" src="/assets/lens-mark.svg">
        <p>A small Laravel application used for an accessibility scan.</p>
        <p class="product-note">Search the project documentation.</p>
        <p><a href="#details">Read product details</a></p>
        <h2 id="details">Project documentation</h2>
        <form><input id="search-field" type="search"><button type="submit">Search</button></form>
    </main>
    <footer>Example project · Demo fixture for real accessibility scans</footer>
</body>
</html>
`;

const captureRoutes = String.raw`
Route::get('/product-demo', function (): Illuminate\View\View {
    abort_unless(app()->environment('local'), 404);

    return view('product-demo');
});
Route::get('/product-capture', function (): Illuminate\View\View {
    abort_unless(app()->environment('local'), 404);

    return view('lens-for-laravel::dashboard', [
        'aiFixStatus' => ['available' => true, 'message' => null],
        'aiModelInfo' => LensForLaravel\LensForLaravel\Services\AiFixer::modelSummary(),
        'authEnabled' => false,
    ]);
});
Route::post('/product-capture/apply', function (): mixed {
    abort_unless(app()->environment('local'), 404);
    app()->bind(LensForLaravel\LensForLaravel\Services\AiFixAvailability::class, function (): LensForLaravel\LensForLaravel\Services\AiFixAvailability {
        return new class extends LensForLaravel\LensForLaravel\Services\AiFixAvailability
        {
            public function available(): bool
            {
                return true;
            }
        };
    });

    $route = app('router')->getRoutes()->getByName('lens-for-laravel.fix.apply');
    $route->bind(request());

    return $route->run();
});
`;

try {
    await access(fixturePath);
    throw new Error('The capture fixture already exists; refusing to overwrite it.');
} catch (error) {
    if (error.code !== 'ENOENT') {
        throw error;
    }
}

const originalRoutes = await readFile(routePath, 'utf8');
assert.ok(!originalRoutes.includes(routeMarker), 'A capture session is already active.');
let browser;

try {
    await mkdir(output, { recursive: true });
    await writeFile(fixturePath, fixture);
    await writeFile(routePath, originalRoutes + routeMarker + captureRoutes);
    browser = await puppeteer.launch({
        headless: true,
        args: ['--no-sandbox', '--ignore-certificate-errors'],
    });

    const page = await browser.newPage();
    const pageErrors = [];
    page.on('pageerror', error => pageErrors.push(error.message));
    await page.setViewport({ width: 1200, height: 1000, deviceScaleFactor: 2 });
    await page.goto(origin + '/product-capture', { waitUntil: 'networkidle0' });
    await page.waitForFunction(() => window.Alpine && document.body._x_dataStack);

    const response = await page.evaluate(async () => {
        const result = await fetch('/lens-for-laravel/scan', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ url: location.origin + '/product-demo', wcagVersion: '2.2' }),
        });

        return { status: result.status, data: await result.json() };
    });

    assert.equal(response.status, 200, 'The installed scanner must succeed.');
    const scan = response.data;
    assert.deepEqual(scan.issues.map(issue => issue.id).sort(), ['color-contrast', 'image-alt', 'label']);
    assert.ok(scan.issues.every(issue => issue.fileName === 'product-demo.blade.php'));

    await page.evaluate(data => {
        const scanner = Alpine.$data(document.body);
        scanner.url = location.origin + '/product-demo';
        scanner.wcagVersion = '2.2';
        scanner.issues = scanner.prepareIssues(data.issues);
        scanner.hasResults = true;
        const originalFetch = window.fetch;
        window.fetch = (input, init) => originalFetch(
            typeof input === 'string' && input.endsWith('/lens-for-laravel/fix/apply')
                ? '/product-capture/apply'
                : input,
            init,
        );
    }, scan);
    await page.addScriptTag({ url: 'https://cdnjs.cloudflare.com/ajax/libs/axe-core/4.8.2/axe.min.js' });

    const settle = async () => {
        await page.evaluate(() => document.fonts.ready);
        await new Promise(resolve => setTimeout(resolve, 650));
    };

    const setTheme = async theme => {
        await page.evaluate(value => {
            Alpine.$data(document.body).theme = value;
            window.scrollTo(0, 0);
        }, theme);
        await settle();
    };

    const reportBounds = () => page.evaluate(() => {
        const report = document.querySelector('[x-show="hasResults"]').getBoundingClientRect();
        const issue = document.querySelector('[x-show="hasResults && filteredIssues.length > 0"] > li').getBoundingClientRect();

        return {
            report: {
                x: report.x - 16,
                y: report.y + window.scrollY - 16,
                width: report.width + 32,
                height: issue.bottom - report.y + 32,
            },
            source: {
                x: issue.x - 1,
                y: issue.y + window.scrollY - 1,
                width: issue.width + 2,
                height: issue.height + 2,
            },
        };
    });

    const assertContrast = async (name, selector = 'body') => {
        const violations = await page.evaluate(async target => {
            const result = await axe.run(document.querySelector(target), {
                runOnly: { type: 'rule', values: ['color-contrast'] },
            });

            return result.violations.map(violation => ({
                id: violation.id,
                nodes: violation.nodes.map(node => ({ target: node.target, summary: node.failureSummary })),
            }));
        }, selector);

        assert.deepEqual(violations, [], name + ' contrast');
    };

    const save = async (name, bounds) => {
        const clip = Object.fromEntries(Object.entries(bounds).map(([key, value]) => [key, Math.round(value)]));
        await page.screenshot({
            path: `${output}/${name}.webp`,
            type: 'webp',
            quality: 96,
            clip,
            captureBeyondViewport: true,
        });
        captures.push({ name, width: clip.width * 2, height: clip.height * 2 });
    };

    const captureReport = async (name, theme, includeSource = false) => {
        await setTheme(theme);
        // The actual package's hover state removes its 0.9 control opacity.
        // This preserves the original UI and meets the AA text contrast target.
        await page.hover(fixAllSelector);
        await settle();
        await assertContrast(`${name} ${theme}`);
        const bounds = await reportBounds();
        await save(`${name}-${theme}`, bounds.report);

        if (includeSource) {
            await save(`source-${theme}`, bounds.source);
        }
    };

    for (const theme of themes) {
        await captureReport('dashboard', theme, true);
    }

    await page.setViewport({ width: 390, height: 1800, deviceScaleFactor: 2 });
    for (const theme of themes) {
        await captureReport('dashboard-mobile', theme);
    }

    await page.setViewport({ width: 1200, height: 1000, deviceScaleFactor: 2 });
    await page.evaluate(() => {
        const scanner = Alpine.$data(document.body);
        scanner.isBulkFix = true;
        scanner.activeFixIndex = 0;
        scanner.fixQueue = scanner.eligibleFixIssues('a').map((issue, index) => {
            const fixedCode = issue.id === 'image-alt'
                ? issue.htmlSnippet.replace('>', ' alt="Lens for Laravel logo">')
                : issue.htmlSnippet.replace('>', ' aria-label="Search documentation">');

            return {
                key: 'capture-' + index,
                issue,
                status: 'ready',
                data: {
                    fileName: issue.fileName,
                    startLine: issue.lineNumber,
                    originalCode: issue.htmlSnippet,
                    fixedCode,
                    explanation: issue.id === 'image-alt'
                        ? 'Example suggestion: add descriptive alternate text so screen readers can identify the Lens for Laravel logo. Review the proposed change before applying it.'
                        : 'Example suggestion: give the search field a descriptive accessible name.',
                },
                error: null,
                editedCode: fixedCode,
                isEditing: false,
                controller: null,
            };
        });
        scanner.showFixModal = true;
        scanner.loadActiveFix();
    });

    const modalSelector = '[aria-labelledby="ai-fix-modal-title"]';
    for (const theme of themes) {
        await setTheme(theme);
        await assertContrast('AI review ' + theme, modalSelector);
        const bounds = await page.evaluate(selector => {
            const modal = document.querySelector(selector + ' > div').getBoundingClientRect();

            return {
                x: modal.x - 12,
                y: modal.y + window.scrollY - 12,
                width: modal.width + 32,
                height: modal.height + 32,
            };
        }, modalSelector);
        await save('ai-review-' + theme, bounds);
    }

    const applied = await page.evaluate(async () => {
        const scanner = Alpine.$data(document.body);
        await scanner.applyFix();
        const result = {
            applied: scanner.fixApplied,
            error: scanner.fixError,
            issues: scanner.issues.length,
            pending: scanner.issues.filter(issue => issue.aiFixStatus === 'pending_verification').length,
        };
        scanner.closeFixModal();

        return result;
    });

    assert.equal(applied.applied, true, applied.error || 'Actual package apply handler failed.');
    assert.equal(applied.issues, 3);
    assert.equal(applied.pending, 1);
    assert.ok((await readFile(fixturePath, 'utf8')).includes('alt="Lens for Laravel logo"'));

    for (const theme of themes) {
        await captureReport('verification', theme);
    }

    for (const name of ['dashboard', 'dashboard-mobile', 'source', 'ai-review', 'verification']) {
        const light = captures.find(capture => capture.name === name + '-light');
        const dark = captures.find(capture => capture.name === name + '-dark');
        assert.equal(light.width, dark.width, name + ' theme widths');
        assert.equal(light.height, dark.height, name + ' theme heights');
    }
    assert.deepEqual(pageErrors, [], 'The canonical product UI must render without JavaScript errors.');

    const dashboardSource = await readFile(join(projectRoot, 'vendor/webcrafts-studio/lens-for-laravel/resources/views/dashboard.blade.php'));
    console.log(JSON.stringify({
        dashboardSha256: createHash('sha256').update(dashboardSource).digest('hex'),
        fixtureSha256: createHash('sha256').update(fixture).digest('hex'),
        captures,
        scan,
        applied,
    }, null, 2));
} finally {
    await browser?.close();
    await rm(fixturePath, { force: true });
    const currentRoutes = await readFile(routePath, 'utf8');
    await writeFile(routePath, currentRoutes.replace(routeMarker + captureRoutes, ''));
}
