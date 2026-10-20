import assert from 'node:assert/strict';
import { writeFile } from 'node:fs/promises';
import puppeteer from 'puppeteer';

const configuration = JSON.parse(process.argv[2] || '{}');
const baseUrl = configuration.baseUrl;
const paths = configuration.paths || ['/'];
assert.ok(baseUrl, 'Pass the built website URL through the Pest browser test.');

const axeResponse = await fetch('https://cdn.jsdelivr.net/npm/axe-core@4.14.0/axe.min.js');
assert.ok(axeResponse.ok, `axe-core download failed: ${axeResponse.status}`);
const axeSource = await axeResponse.text();
const browser = await puppeteer.launch({
    headless: true,
    acceptInsecureCerts: new URL(baseUrl).hostname.endsWith('.test'),
});
const failures = [];
const summary = { pages: 0, axeScans: 0, reflow: 0, keyboard: 0, layouts: [], axeVersion: null, manualReview: new Map() };
const record = (context, message) => failures.push(`${context}: ${message}`);
const viewports = [{ name: 'desktop', width: 1440, height: 1000 }, { name: 'mobile', width: 320, height: 800 }];
const themes = ['light', 'dark'];
const pauseForLayout = page => page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));

async function openPage(path, viewport, theme) {
    const page = await browser.newPage();
    page.setDefaultTimeout(8000);
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    page.on('console', message => { if (message.type() === 'error') { errors.push(message.text()); } });
    await page.setViewport({ width: viewport.width, height: viewport.height });
    await page.emulateMediaFeatures([{ name: 'prefers-reduced-motion', value: 'reduce' }, { name: 'prefers-color-scheme', value: theme }]);
    await page.evaluateOnNewDocument(value => localStorage.setItem('lens-theme', value), theme);
    const response = await page.goto(new URL(path, baseUrl).href, { waitUntil: 'networkidle0' });
    assert.equal(response.status(), 200, `${path} must load successfully`);
    await page.addStyleTag({ content: '#herd-ai-host { display: none !important; }' });
    await page.evaluate(() => document.fonts.ready);
    assert.equal(await page.evaluate(() => document.documentElement.classList.contains('dark')), theme === 'dark');
    await page.addScriptTag({ content: axeSource });
    return { page, errors };
}

async function audit(page, context, root = null) {
    const result = await page.evaluate(async selector => {
        const result = await axe.run(selector ? document.querySelector(selector) : document, {
            runOnly: { type: 'tag', values: ['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'wcag22aa'] },
        });
        return {
            version: axe.version,
            violations: result.violations.map(rule => ({
                id: rule.id,
                impact: rule.impact,
                nodes: rule.nodes.map(node => ({ target: node.target, message: node.failureSummary })),
            })),
            incomplete: result.incomplete.map(rule => ({ id: rule.id, targets: rule.nodes.map(node => node.target) })),
        };
    }, root);
    summary.axeVersion = result.version;
    summary.axeScans += 1;
    result.violations.forEach(rule => record(context, `${rule.impact} ${rule.id}: ${JSON.stringify(rule.nodes)}`));
    result.incomplete.forEach(rule => {
        const key = `${rule.id}: ${JSON.stringify(rule.targets)}`;
        summary.manualReview.set(key, (summary.manualReview.get(key) || 0) + 1);
    });
}

async function checkReflow(page, context) {
    const result = await page.evaluate(() => {
        const root = document.documentElement;
        const clipped = [...document.querySelectorAll('body *')].filter(element => {
            const style = getComputedStyle(element);
            const rect = element.getBoundingClientRect();
            const hasText = [...element.childNodes].some(node => node.nodeType === Node.TEXT_NODE && node.textContent.trim());
            return hasText && rect.width > 0 && rect.height > 0 && style.position !== 'absolute'
                && ['hidden', 'clip'].includes(style.overflowY) && element.scrollHeight > element.clientHeight + 2;
        }).map(element => `${element.tagName.toLowerCase()}.${element.className}: ${element.textContent.trim().slice(0, 80)}`);
        return { width: innerWidth, scrollWidth: root.scrollWidth, clipped };
    });
    if (result.scrollWidth > result.width + 1) {
        record(context, `viewport overflow (${result.scrollWidth}px content in ${result.width}px viewport)`);
    }
    result.clipped.forEach(element => record(context, `vertically clipped text in ${element}`));
}

async function checkCommunityCoverage(page, viewport, theme) {
    await page.$$eval('#community img', images => Promise.all(images.map(async image => {
        image.loading = 'eager';
        await image.decode();
    })));
    const coverage = await page.evaluate(() => {
        const rectangle = element => {
            const { x, y, width, height, right, bottom } = element.getBoundingClientRect();
            return { x, y, width, height, right, bottom };
        };
        const newsBadge = document.querySelector('.coverage-visual-news .coverage-edition');
        return {
            cards: [...document.querySelectorAll('#community article')].map(card => ({
                ...rectangle(card),
                href: card.querySelector('a').href,
                title: card.querySelector('h3').textContent.trim(),
                overflow: card.scrollWidth > card.clientWidth + 1,
                logos: [...card.querySelectorAll('img')].map(image => ({
                    loaded: image.complete && image.naturalWidth > 0,
                    ...rectangle(image),
                    surface: rectangle(image.closest('.coverage-visual')),
                })),
            })),
            section: rectangle(document.getElementById('community')),
            newsBadge: newsBadge.getClientRects().length > 0 ? {
                badge: rectangle(newsBadge),
                brand: rectangle(document.querySelector('.coverage-news-brand')),
            } : null,
            scrollY,
            viewport: innerWidth,
        };
    });
    assert.deepEqual(coverage.cards.map(card => new URL(card.href).href), [
        'https://laravel-news.com/lens-for-laravel-brings-wcag-auditing-to-your-local-dev-workflow',
        'https://podcast.laravel-news.com/255',
        'https://x.com/PovilasKorop/status/2099000377999831096',
        'https://www.linkedin.com/posts/povilas-korop-laravel-developer_new-laravel-package-webcrafts-studiolens-for-laravel-activity-7505589580398522368-2PoF',
        'https://www.angelcruz.dev/post/lens-laravel-accesibilidad-axe-ai-fix',
        'https://www.programmez.com/actualites/lens-laravel-v3-outil-pour-verifier-la-conformite-wcag-2x-39792',
    ], 'The six publication cards must retain the requested reading order.');

    if (coverage.newsBadge) {
        const { badge, brand } = coverage.newsBadge;
        const horizontalOverlap = Math.min(badge.right, brand.right) - Math.max(badge.x, brand.x);
        const verticalOverlap = Math.min(badge.bottom, brand.bottom) - Math.max(badge.y, brand.y);
        assert.ok(horizontalOverlap <= 1 || verticalOverlap <= 1, 'The editorial badge must not cover Laravel News branding.');
    }

    for (const [index, card] of coverage.cards.entries()) {
        assert.ok(card.x >= -1 && card.right <= coverage.viewport + 1, `${card.title} must fit the viewport.`);
        assert.equal(card.overflow, false, `${card.title} must not overflow its card.`);
        assert.ok(card.logos.length > 0, `${card.title} needs recognizable local branding.`);
        for (const logo of card.logos) {
            assert.equal(logo.loaded, true, `${card.title} logo must decode.`);
            assert.ok(logo.width > 0 && logo.height > 0);
            assert.ok(logo.x >= logo.surface.x - 1 && logo.right <= logo.surface.right + 1
                && logo.y >= logo.surface.y - 1 && logo.bottom <= logo.surface.bottom + 1, `${card.title} logo must remain inside its visual panel.`);
        }
        if (index > 0) {
            const previous = coverage.cards[index - 1];
            assert.ok(card.y > previous.y + 1 || (Math.abs(card.y - previous.y) < 1 && card.x > previous.x), 'Visual order must match DOM and keyboard reading order.');
        }
        for (const previous of coverage.cards.slice(0, index)) {
            const horizontalOverlap = Math.min(card.right, previous.right) - Math.max(card.x, previous.x);
            const verticalOverlap = Math.min(card.bottom, previous.bottom) - Math.max(card.y, previous.y);
            assert.ok(horizontalOverlap <= 1 || verticalOverlap <= 1, 'Community cards must not overlap.');
        }
    }
    if (viewport.width >= 1200) {
        assert.ok(Math.abs(coverage.cards[0].y - coverage.cards[1].y) < 1, 'Laravel News and its podcast should share the featured row.');
        assert.ok(coverage.cards.slice(2).every(card => Math.abs(card.y - coverage.cards[2].y) < 1), 'The remaining references should form one clean supporting row.');
    }
    if (process.env.LENS_COVERAGE_SCREENSHOT_DIR) {
        await page.screenshot({
            path: `${process.env.LENS_COVERAGE_SCREENSHOT_DIR}/lens-community-${theme}-${viewport.width}.png`,
            clip: {
                x: Math.round(coverage.section.x),
                y: Math.round(coverage.section.y + coverage.scrollY),
                width: Math.floor(coverage.section.width),
                height: Math.ceil(coverage.section.height),
            },
            captureBeyondViewport: true,
        });
    }
}

async function checkCompactLayout(page, viewport, theme) {
    const context = `/ ${theme} ${viewport.width}px compact layout`;
    const desktop = viewport.width >= 1000;
    await checkCommunityCoverage(page, viewport, theme);
    const decoration = await page.evaluate(() => ({
        localIcons: document.querySelectorAll('.model-choice > div:first-child svg, .model-choice .outcome-icon').length,
        cards: [...document.querySelectorAll('.journey-visual')].map(element => ({
            background: getComputedStyle(element).backgroundImage,
            size: getComputedStyle(element).backgroundSize,
            before: getComputedStyle(element, '::before').content,
            after: getComputedStyle(element, '::after').content,
        })),
        arrows: [...document.querySelectorAll('a svg:has(path[d="M7 7h10v10M7 17 17 7"])')]
            .filter(element => element.getClientRects().length > 0)
            .map(element => ({ width: element.getBoundingClientRect().width, height: element.getBoundingClientRect().height })),
    }));
    assert.equal(decoration.localIcons, 0, 'Local model support should have no decorative icon.');
    assert.equal(decoration.cards.length, 3);
    for (const card of decoration.cards) {
        assert.equal((card.background.match(/linear-gradient/g) || []).length, 2, 'Each feature icon should sit on a two-axis grid.');
        assert.ok(!card.background.includes('radial-gradient'), 'Feature cards should have no radial glow.');
        assert.ok(card.size.split(', ').every(size => size === '24px 24px'));
        assert.equal(card.before, 'none', 'Feature cards should have no decorative rings.');
        assert.equal(card.after, 'none');
    }
    assert.ok(decoration.arrows.length >= 2);
    for (const arrow of decoration.arrows) {
        assert.ok(arrow.width >= 14 && arrow.height >= 14, 'SVG arrows should keep a readable size at every viewport.');
        assert.equal(arrow.width, arrow.height, 'SVG arrows should remain square.');
    }
    await page.$$eval('.product-preview img', images => Promise.all(images.map(async image => {
        image.loading = 'eager';
        await image.decode();
    })));
    const reportAssets = await page.$$eval('.hero-illustration image', images => Promise.all(images.map(async image => {
        const decoded = new Image();
        decoded.src = image.href.baseVal;
        await decoded.decode();
        return {
            className: image.getAttribute('class'),
            source: new URL(image.href.baseVal, location.href).pathname,
            visible: getComputedStyle(image).display !== 'none',
            width: decoded.naturalWidth,
            height: decoded.naturalHeight,
        };
    })));
    assert.equal(reportAssets.length, 2, 'The illustration must use the two original report captures.');
    assert.deepEqual(reportAssets.filter(image => image.visible).map(image => image.className), [`hero-report-${theme}`]);
    for (const [index, reportTheme] of themes.entries()) {
        assert.equal(reportAssets[index].source, `/assets/screenshots/dashboard-${reportTheme}.webp`);
        assert.ok(reportAssets[index].width >= 1400 && reportAssets[index].height > 0, 'The original report asset must decode.');
    }
    const iconGeometry = await page.evaluate(() => {
        const illustration = document.querySelector('.hero-illustration');
        const center = bounds => new DOMPoint(bounds.x + bounds.width / 2, bounds.y + bounds.height / 2);
        const rootMatrix = illustration.getCTM().inverse();
        const faces = ['code', 'text'].map(name => {
            const surface = illustration.querySelector(`[data-hero-face-surface="${name}"]`);
            const use = illustration.querySelector(`[data-hero-face="${name}"] use`);
            const surfaceCenter = center(surface.getBBox()).matrixTransform(rootMatrix.multiply(surface.getCTM()));
            const iconCenter = center(use.getBBox()).matrixTransform(rootMatrix.multiply(use.getCTM()));

            return { name, dx: iconCenter.x - surfaceCenter.x, dy: iconCenter.y - surfaceCenter.y };
        });
        const tile = illustration.querySelector('[data-hero-tile-surface="ai"]');
        const frame = tile.querySelector('rect').getBBox();
        const use = tile.querySelector('use[href="#hero-ai-icon"]');
        const matrix = tile.getCTM().inverse().multiply(use.getCTM());
        const iconCenter = new DOMPoint(12, 12).matrixTransform(matrix);
        const frameCenter = center(frame);
        const outside = [];
        illustration.querySelectorAll('#hero-ai-icon > *').forEach(shape => {
            const length = shape.getTotalLength();
            const stroke = parseFloat(getComputedStyle(shape).strokeWidth) / 2;
            const paddingX = stroke * Math.hypot(matrix.a, matrix.c);
            const paddingY = stroke * Math.hypot(matrix.b, matrix.d);

            for (let sample = 0; sample <= 128; sample += 1) {
                const point = shape.getPointAtLength(length * sample / 128).matrixTransform(matrix);
                if (point.x - paddingX < frame.x - .1 || point.x + paddingX > frame.x + frame.width + .1
                    || point.y - paddingY < frame.y - .1 || point.y + paddingY > frame.y + frame.height + .1) {
                    outside.push({ x: point.x, y: point.y });
                    break;
                }
            }
        });

        return { faces, ai: { dx: iconCenter.x - frameCenter.x, dy: iconCenter.y - frameCenter.y, outside } };
    });
    for (const face of iconGeometry.faces) {
        assert.ok(Math.abs(face.dx) < .01 && Math.abs(face.dy) < .01, `The ${face.name} icon must be centered on its actual cube face: ${JSON.stringify(face)}`);
    }
    assert.ok(Math.abs(iconGeometry.ai.dx) < .01 && Math.abs(iconGeometry.ai.dy) < .01, 'The AI icon viewport must be centered in its flat tile.');
    assert.deepEqual(iconGeometry.ai.outside, [], 'All AI icon strokes must fit inside the flat tile frame.');
    const measure = () => page.evaluate(() => {
        const rectangle = selector => {
            const element = document.querySelector(selector);
            const { x, y, width, height, right, bottom } = element.getBoundingClientRect();
            return { x, y, width, height, right, bottom };
        };
        const previews = [...document.querySelectorAll('.product-preview')]
            .filter(element => element.getBoundingClientRect().height > 0)
            .map(element => {
                const image = [...element.querySelectorAll('img')].find(image => image.getBoundingClientRect().height > 0);
                return {
                    height: element.getBoundingClientRect().height,
                    imageHeight: image.getBoundingClientRect().height,
                    objectFit: getComputedStyle(image).objectFit,
                    naturalWidth: image.naturalWidth,
                    naturalHeight: image.naturalHeight,
                    label: element.querySelector('figcaption').textContent.trim().slice(0, 80),
                };
            });
        const heroArtwork = document.querySelector('.hero-art');
        const heroIllustration = heroArtwork.querySelector('.hero-illustration');
        const heroFigure = heroArtwork.querySelector('[data-hero-illustration]');
        const heroCaption = document.getElementById(heroFigure.getAttribute('aria-labelledby'));
        const artworkStyles = [heroArtwork, heroIllustration]
            .map(element => ({
                element: element.getAttribute('class') || element.tagName.toLowerCase(),
                transform: getComputedStyle(element).transform,
                perspective: getComputedStyle(element).perspective,
            }));
        return {
            total: document.documentElement.scrollHeight,
            smallText: [...document.querySelectorAll('body *')]
                .filter(element => element.getClientRects().length > 0
                    && [...element.childNodes].some(node => node.nodeType === Node.TEXT_NODE && node.textContent.trim())
                    && Number.parseFloat(getComputedStyle(element).fontSize) <= 10)
                .map(element => element.textContent.trim().slice(0, 80)),
            hero: rectangle('.hero'),
            heroCopy: rectangle('.hero-copy'),
            heroPreview: rectangle('.hero-art'),
            heroContent: ['.hero h1', '.hero-lead', '.hero .button-row', '.hero-install'].map(rectangle),
            heroTextAlignment: ['.hero h1', '.hero-lead'].map(selector => getComputedStyle(document.querySelector(selector)).textAlign),
            heroActionAlignment: getComputedStyle(document.querySelector('.hero .button-row')).justifyContent,
            heroIllustrationHeight: heroIllustration.getBoundingClientRect().height,
            heroSemantics: {
                role: heroFigure.getAttribute('role'),
                description: heroCaption?.textContent.trim(),
                captionTag: heroCaption?.tagName.toLowerCase(),
                hidden: heroIllustration.getAttribute('aria-hidden'),
                focusable: heroIllustration.getAttribute('focusable'),
                interactive: heroFigure.querySelectorAll('a, button, [tabindex], script, foreignObject').length,
                textCallouts: heroIllustration.querySelectorAll('text').length,
                animation: getComputedStyle(heroIllustration).animationName,
            },
            artworkStyles,
            workflow: rectangle('[data-workflow]'),
            workflowPanel: rectangle('.workflow-panel:not([hidden])'),
            workflowCopy: rectangle('.workflow-panel:not([hidden]) .workflow-explanation'),
            workflowPreview: rectangle('.workflow-panel:not([hidden]) .product-preview'),
            fix: rectangle('.fix-section'),
            fixCopy: rectangle('.fix-layout > .section-copy'),
            fixPreview: rectangle('.fix-preview'),
            previews,
        };
    });

    const initial = await measure();
    assert.equal(initial.heroSemantics.role, 'img', 'The illustration must expose one accessible image equivalent.');
    assert.equal(initial.heroSemantics.captionTag, 'figcaption');
    assert.ok(initial.heroSemantics.description?.includes('diagnostic report'), 'The illustration needs an accessible equivalent.');
    assert.equal(initial.heroSemantics.hidden, 'true');
    assert.equal(initial.heroSemantics.focusable, 'false');
    assert.equal(initial.heroSemantics.interactive, 0, 'The decorative illustration must add no keyboard stops or fake controls.');
    assert.equal(initial.heroSemantics.textCallouts, 0, 'The illustration must have no text callouts.');
    assert.equal(initial.heroSemantics.animation, 'none', 'The illustration should remain still.');
    if (process.env.LENS_HERO_SCREENSHOT_DIR) {
        try {
            if (desktop) {
                await page.setViewport({ width: viewport.width, height: 1500 });
                await pauseForLayout(page);
            }
            await page.screenshot({
                path: `${process.env.LENS_HERO_SCREENSHOT_DIR}/lens-hero-${theme}-${viewport.width}.png`,
                clip: { x: 0, y: 0, width: viewport.width, height: Math.ceil(initial.hero.bottom + 24) },
                captureBeyondViewport: true,
            });
            if (desktop) {
                await page.setViewport({ width: viewport.width, height: 1500, deviceScaleFactor: 2 });
                await pauseForLayout(page);
                const scene = await page.$('[data-hero-illustration]');
                await scene.screenshot({ path: `${process.env.LENS_HERO_SCREENSHOT_DIR}/lens-hero-icons-${theme}.png` });
            }
        } finally {
            if (desktop) {
                await page.setViewport({ width: viewport.width, height: viewport.height });
                await pauseForLayout(page);
            }
        }
    }
    initial.smallText.forEach(text => record(context, `text must be larger than 10px: ${text}`));
    summary.layouts.push({
        width: viewport.width, theme, page: Math.round(initial.total), hero: Math.round(initial.hero.height),
        heroPreview: Math.round(initial.heroPreview.height), workflow: Math.round(initial.workflow.height),
        fix: Math.round(initial.fix.height),
    });
    const pageLimit = desktop ? 7000 : viewport.width >= 390 ? 11250 : 12000;
    const heroLimit = desktop ? 720 : viewport.width >= 390 ? 1100 : 1200;
    if (initial.total > pageLimit) { record(context, `page height ${Math.round(initial.total)}px exceeds ${pageLimit}px`); }
    if (initial.hero.height > heroLimit) { record(context, `hero height ${Math.round(initial.hero.height)}px exceeds ${heroLimit}px`); }
    if (initial.heroPreview.height > (desktop ? 550 : 400)) {
        record(context, `hero artwork is too tall: ${Math.round(initial.heroPreview.height)}px`);
    }
    if (!desktop) {
        const heroCenter = initial.hero.x + initial.hero.width / 2;
        for (const [name, rectangle] of [
            ['copy', initial.heroCopy],
            ['artwork', initial.heroPreview],
            ...initial.heroContent.map((rectangle, index) => [['heading', 'lead', 'actions', 'install'][index], rectangle]),
        ]) {
            if (Math.abs(rectangle.x + rectangle.width / 2 - heroCenter) > 2) {
                record(context, `mobile hero ${name} must remain centered`);
            }
        }
        if (initial.heroPreview.y < initial.heroCopy.bottom - 1) {
            record(context, 'mobile hero artwork must appear below the centered copy');
        }
        if (initial.heroTextAlignment.some(alignment => alignment !== 'center') || initial.heroActionAlignment !== 'center') {
            record(context, 'mobile hero heading, lead and actions must use centered alignment');
        }
    }
    if (desktop && (initial.heroPreview.width > 592 + 1 || initial.heroIllustrationHeight > 490 + 1)) {
        record(context, 'hero must keep a compact illustration (at most 592px wide and 490px tall)');
    }
    for (const element of initial.artworkStyles) {
        if (element.transform !== 'none' || element.perspective !== 'none') {
            record(context, `hero ${element.element} must use the illustration's own isometric geometry without CSS distortion`);
        }
    }
    if (desktop) {
        for (const [name, copy, preview] of [
            ['hero', initial.heroCopy, initial.heroPreview],
            ['workflow', initial.workflowCopy, initial.workflowPreview],
            ['AI fixes', initial.fixCopy, initial.fixPreview],
        ]) {
            if (copy.right > preview.x + 1 || copy.y >= preview.bottom || preview.y >= copy.bottom) {
                record(context, `${name} copy and artwork must share a row without overlapping`);
            }
        }
    }

    for (let step = 0; step < 3; step += 1) {
        if (step > 0) {
            await page.$eval(`[data-workflow] [role="tab"]:nth-child(${step + 1})`, tab => tab.click());
            await pauseForLayout(page);
        }
        const layout = step === 0 ? initial : await measure();
        if (desktop && layout.workflowPanel.height > 450) {
            record(context, `workflow step ${step + 1} is too tall: ${Math.round(layout.workflowPanel.height)}px`);
        }
        for (const preview of layout.previews) {
            const imageLimit = desktop ? 400 : 220;
            if (preview.imageHeight > imageLimit + 1) {
                record(context, `screenshot thumbnail exceeds ${imageLimit}px: ${preview.label} (${Math.round(preview.imageHeight)}px)`);
            }
            if (preview.objectFit !== 'contain' || preview.naturalWidth < 1400 || preview.naturalHeight < 1) {
                record(context, `thumbnail must contain a loaded full product capture: ${preview.label}`);
            }
        }
    }
    await page.$eval('[data-workflow] [role="tab"]:first-child', tab => tab.click());
    await pauseForLayout(page);
}

async function checkPage(path, viewport, theme) {
    const context = `${path} ${theme} ${viewport.name}`;
    const { page, errors } = await openPage(path, viewport, theme);
    try {
        await audit(page, context);
        await checkReflow(page, context);
        if (path === '/') {
            const opener = await page.$('[data-consent-banner]:not([hidden]) [data-consent-action="open"]')
                || await page.$('[data-consent-launcher]:not([hidden])');
            assert.ok(opener, 'Consent preferences must be available.');
            await opener.click();
            await page.waitForSelector('[data-consent-dialog][open]');
            await page.$$eval('[data-consent-dialog] details', details => details.forEach(detail => { detail.open = true; }));
            await audit(page, `${context} consent preferences`, '[data-consent-dialog]');
            await checkReflow(page, `${context} consent preferences`);
            const category = await page.$('[data-consent-dialog] .consent-switch:not(:disabled)');
            if (category) {
                await category.click();
                await audit(page, `${context} consent switch toggled`, '[data-consent-dialog]');
            }
            await page.keyboard.press('Escape');
            assert.equal(await page.$eval('[data-consent-dialog]', dialog => dialog.open), false);
        }
        if (path === '/') { await checkCompactLayout(page, viewport, theme); }
        const missingTargets = await page.evaluate(() => [...document.querySelectorAll('[aria-controls]')].flatMap(element =>
            element.getAttribute('aria-controls').split(/\s+/).filter(id => !document.getElementById(id))));
        missingTargets.forEach(id => record(context, `aria-controls references a missing target: ${id}`));
        const inaccessibleScrollers = await page.evaluate(() => [...document.querySelectorAll('.docs-table-wrapper, .docs-prose pre')]
            .filter(element => element.scrollWidth > element.clientWidth + 1 && element.tabIndex !== 0)
            .map(element => element.className || element.tagName));
        inaccessibleScrollers.forEach(element => record(context, `overflowing technical content is not keyboard focusable: ${element}`));
        summary.pages += 1;

        if (await page.$('.faq-list')) {
            await page.$$eval('.faq-list details', details => details.forEach(detail => { detail.open = true; }));
            await pauseForLayout(page);
            await audit(page, `${context} expanded FAQ`, '.faq-list');
        }

        await page.evaluate(() => {
            const textSizes = [...document.querySelectorAll('body, body *')]
                .filter(element => !(element instanceof SVGElement))
                .map(element => [element, getComputedStyle(element).fontSize]);
            textSizes.forEach(([element, size]) => element.style.setProperty('font-size', `${parseFloat(size) * 2}px`, 'important'));
        });
        await pauseForLayout(page);
        await checkReflow(page, `${context} 200% text`);
        summary.reflow += 1;

        await page.evaluate(() => {
            document.querySelectorAll('body, body *').forEach(element => element.style.removeProperty('font-size'));
        });
        await page.addStyleTag({ content: `
            body *:not(svg):not(svg *) { line-height: 1.5 !important; letter-spacing: .12em !important; word-spacing: .16em !important; }
            p { margin-bottom: 2em !important; }
        ` });
        await pauseForLayout(page);
        await checkReflow(page, `${context} text spacing`);
        summary.reflow += 1;
        errors.forEach(error => record(context, `browser error: ${error}`));
    } finally {
        await page.close();
    }
}

async function expectFocused(page, selector, message) {
    await pauseForLayout(page);
    assert.equal(await page.evaluate(value => document.activeElement.matches(value), selector), true, message);
    await assertFocusVisible(page);
}

async function assertFocusVisible(page) {
    const visibility = await page.evaluate(() => {
        const element = document.activeElement;
        const rectangle = element.getBoundingClientRect();
        const style = getComputedStyle(element);
        const header = document.querySelector('.site-header');
        const headerBottom = element.closest('.site-header, dialog, .skip-link') ? 0 : header.getBoundingClientRect().bottom;
        return {
            control: element.matches('a, button, input, summary'),
            visible: rectangle.width > 0 && rectangle.height > 0 && rectangle.bottom > Math.max(0, headerBottom)
                && rectangle.top < innerHeight && rectangle.right > 0 && rectangle.left < innerWidth,
            indicator: style.outlineStyle !== 'none' && parseFloat(style.outlineWidth) >= 2,
            element: element.outerHTML.slice(0, 180),
        };
    });
    if (visibility.control) {
        assert.ok(visibility.visible, `Keyboard focus must remain visible: ${visibility.element}`);
        assert.ok(visibility.indicator, `Keyboard controls must show their focus outline: ${visibility.element}`);
    }
}

async function focusWithKeyboard(page, selector, limit = 120) {
    for (let index = 0; index < limit; index += 1) {
        if (await page.evaluate(value => document.activeElement.matches(value), selector)) {
            await assertFocusVisible(page);
            return;
        }
        await page.keyboard.press('Tab');
    }
    throw new Error(`Could not reach ${selector} using Tab`);
}

async function checkScreenshotPreview(page, context, selector, hero = null) {
    await focusWithKeyboard(page, selector);
    const preview = await page.evaluate(() => {
        const link = document.activeElement;
        const image = link.querySelector('img');
        return {
            source: image?.currentSrc || link.href,
            triggerHref: link.href,
            alternative: image?.alt || link.dataset.previewAlt,
            title: link.dataset.previewTitle,
            description: link.dataset.previewDescription,
            width: Number(link.dataset.previewWidth),
            height: Number(link.dataset.previewHeight),
            target: link.getBoundingClientRect().height,
            hasImageAncestor: Boolean(link.closest('[role="img"]')),
        };
    });
    assert.ok(preview.alternative?.length > 0 && preview.alternative.length <= 100);
    if (hero) {
        assert.ok(preview.target >= 44, 'The hero screenshot link must have a generous pointer target.');
        assert.equal(preview.hasImageAncestor, false, 'The screenshot link must remain outside the decorative image role.');
        assert.equal(new URL(preview.source).pathname, `/assets/screenshots/dashboard-${hero.theme}.webp`);
        assert.ok(preview.alternative.includes(`${hero.theme} theme`));
    }
    await page.keyboard.press('Enter');
    await page.waitForFunction(() => document.querySelector('#product-preview-dialog').open);
    await expectFocused(page, '#product-preview-dialog [aria-label="Close screenshot"]', 'The screenshot viewer must initially focus its close button.');
    await page.waitForFunction(() => {
        const image = document.querySelector('[data-preview-image]');
        return image.complete && image.naturalWidth > 0;
    });
    assert.equal(await page.$eval('[data-preview-image]', image => image.src), preview.source);
    assert.equal(await page.$eval('[data-preview-image]', image => image.alt), preview.alternative);
    assert.equal(await page.$eval('#product-preview-dialog', dialog => document.getElementById(dialog.getAttribute('aria-labelledby')).textContent), preview.title);
    assert.equal(await page.$eval('#product-preview-dialog', dialog => document.getElementById(dialog.getAttribute('aria-describedby')).textContent), preview.description);
    assert.equal(await page.$eval('[data-preview-file]', link => link.href), preview.source);
    if (hero) {
        const dimensions = await page.$eval('[data-preview-image]', image => ({ width: image.width, height: image.height, naturalWidth: image.naturalWidth, naturalHeight: image.naturalHeight }));
        assert.equal(dimensions.naturalWidth, preview.width);
        assert.equal(dimensions.naturalHeight, preview.height);
        assert.equal(await page.$eval('[data-preview-size="fit"]', button => button.getAttribute('aria-pressed')), 'true');
    }
    const label = hero ? 'hero screenshot' : 'workflow screenshot';
    await audit(page, `${context} ${label} open`, '#product-preview-dialog');
    await checkReflow(page, `${context} ${label} open`);
    if (hero && process.env.LENS_HERO_SCREENSHOT_DIR) {
        await page.screenshot({ path: `${process.env.LENS_HERO_SCREENSHOT_DIR}/lens-hero-enlarged-${hero.theme}-${hero.viewport.width}.png` });
    }
    await page.keyboard.down('Shift');
    await page.keyboard.press('Tab');
    await page.keyboard.up('Shift');
    await expectFocused(page, '.preview-dialog-scroll', 'Shift+Tab from the close button must wrap to the last focusable viewer region.');
    await page.keyboard.press('Tab');
    // Native Chromium dialogs can expose the browser-chrome boundary as BODY before wrapping.
    const forwardFocus = await page.evaluate(() => ({
        neutralBoundary: document.activeElement === document.body || document.activeElement === document.documentElement,
        insideViewer: Boolean(document.activeElement.closest('#product-preview-dialog')),
        active: document.activeElement.outerHTML.slice(0, 160),
    }));
    assert.ok(forwardFocus.insideViewer || forwardFocus.neutralBoundary, `Tab must not focus the background page while the viewer is open: ${forwardFocus.active}`);
    if (forwardFocus.neutralBoundary) {
        await page.keyboard.press('Tab');
    }
    await expectFocused(page, '#product-preview-dialog [aria-label="Close screenshot"]', 'Tab must return to the close button after any browser-chrome stop.');
    await focusWithKeyboard(page, '[data-preview-size="original"]');
    await page.keyboard.press('Enter');
    assert.equal(await page.$eval('[data-preview-size="original"]', button => button.getAttribute('aria-pressed')), 'true');
    await focusWithKeyboard(page, '.preview-dialog-scroll');
    const overflow = await page.$eval('.preview-dialog-scroll', region => ({
        horizontal: region.scrollWidth > region.clientWidth,
        vertical: region.scrollHeight > region.clientHeight,
    }));
    if (hero) {
        assert.ok(overflow.horizontal && overflow.vertical, 'The full diagnostic report must expose both scroll axes at original size.');
    }
    if (overflow.horizontal) {
        await page.keyboard.press('ArrowRight');
        await page.waitForFunction(() => document.querySelector('.preview-dialog-scroll').scrollLeft > 0);
    }
    if (overflow.vertical) {
        await page.keyboard.press('ArrowDown');
        await page.waitForFunction(() => document.querySelector('.preview-dialog-scroll').scrollTop > 0);
    }
    await checkReflow(page, `${context} ${label} original size`);
    await page.keyboard.press('Escape');
    assert.equal(await page.$eval('#product-preview-dialog', dialog => dialog.open), false);
    await expectFocused(page, selector, 'Closing screenshot must restore the exact preview link focus.');
    assert.equal(await page.evaluate(() => document.activeElement.href), preview.triggerHref);
}

async function checkHeroWithoutJavaScript() {
    const page = await browser.newPage();
    try {
        await page.setJavaScriptEnabled(false);
        await page.setViewport(viewports[0]);
        const response = await page.goto(new URL('/', baseUrl).href, { waitUntil: 'networkidle0' });
        assert.equal(response.status(), 200);
        const link = await page.$('.hero-preview-link.preview-version-light');
        assert.ok(link, 'The hero must retain a direct screenshot fallback without JavaScript.');
        const target = await link.evaluate(element => ({
            href: element.href,
            visible: element.getBoundingClientRect().height > 0,
            text: element.textContent.trim(),
        }));
        assert.ok(target.visible);
        assert.equal(target.text, 'Enlarge screenshot');
        assert.equal(new URL(target.href).pathname, '/assets/screenshots/dashboard-light.webp');
        await focusWithKeyboard(page, '.hero-preview-link.preview-version-light');
        const [imageResponse] = await Promise.all([
            page.waitForNavigation({ waitUntil: 'load' }),
            page.keyboard.press('Enter'),
        ]);
        assert.equal(imageResponse.status(), 200);
        assert.equal(page.url(), target.href);
        assert.ok(imageResponse.headers()['content-type'].startsWith('image/webp'));
        summary.keyboard += 1;
    } catch (error) {
        record('/ no JavaScript hero fallback', error.message);
    } finally {
        await page.close();
    }
}

async function checkKeyboard(path, viewport, theme) {
    const context = `${path} ${theme} ${viewport.name} keyboard`;
    const { page, errors } = await openPage(path, viewport, theme);
    try {
        await page.keyboard.press('Tab');
        await expectFocused(page, '.skip-link', 'The first tab stop must be the skip link.');
        await page.keyboard.press('Enter');
        await expectFocused(page, '#main-content', 'The skip link must move focus to main content.');

        await focusWithKeyboard(page, '[data-search-open]');
        await page.keyboard.press('Enter');
        await expectFocused(page, '#docs-search-input', 'Search must focus its labelled input.');
        await page.keyboard.type('Ollama');
        await page.waitForFunction(() => document.querySelector('#search-results a'));
        await audit(page, `${context} search open`, '#docs-search');
        await checkReflow(page, `${context} search open`);
        await page.keyboard.press('Tab');
        await expectFocused(page, '#search-results a', 'Search results must be keyboard reachable.');
        await focusWithKeyboard(page, '[data-search-close]');
        await page.keyboard.down('Shift');
        await page.keyboard.press('Tab');
        await page.keyboard.up('Shift');
        await expectFocused(page, '#search-results li:last-child a', 'Shift+Tab must wrap inside search.');
        await page.keyboard.press('Escape');
        assert.equal(await page.$eval('#docs-search', dialog => dialog.open), false);
        await expectFocused(page, '[data-search-open]', 'Closing search must restore trigger focus.');

        if (viewport.name === 'mobile') {
            await focusWithKeyboard(page, '[data-mobile-navigation] summary');
            await page.keyboard.press('Enter');
            await pauseForLayout(page);
            assert.equal(await page.$eval('[data-mobile-navigation] summary', summary => summary.getAttribute('aria-expanded')), 'true');
            await audit(page, `${context} navigation open`);
            await checkReflow(page, `${context} navigation open`);
            await page.keyboard.press('Tab');
            await expectFocused(page, '#mobile-site-navigation a', 'The mobile menu must expose keyboard links.');
            await page.keyboard.press('Escape');
            await expectFocused(page, '[data-mobile-navigation] summary', 'Escape must restore menu trigger focus.');
            assert.equal(await page.$eval('[data-mobile-navigation]', details => details.open), false);
            assert.equal(await page.$$eval('.site-frame > [inert]', elements => elements.length), 0, 'Closing navigation must restore the page.');

            await page.keyboard.press('Enter');
            await pauseForLayout(page);
            await focusWithKeyboard(page, '#mobile-site-navigation a:last-child');
            await page.keyboard.press('Tab');
            await expectFocused(page, '.site-header [data-search-open]', 'Tab after the mobile links must reach the header search.');
            assert.equal(await page.$eval('[data-mobile-navigation]', details => details.open), false);
            assert.equal(await page.$$eval('.site-frame > [inert]', elements => elements.length), 0);

            await focusWithKeyboard(page, '[data-mobile-navigation] summary');
            await page.keyboard.press('Enter');
            await pauseForLayout(page);
            await page.mouse.click(10, viewport.height - 20);
            await expectFocused(page, '[data-mobile-navigation] summary', 'Clicking outside navigation must restore its trigger.');
            assert.equal(await page.$eval('[data-mobile-navigation]', details => details.open), false);

            if (path === '/') {
                await page.keyboard.press('Enter');
                await pauseForLayout(page);
                await page.keyboard.press('Tab');
                await page.keyboard.press('Enter');
                await expectFocused(page, '#features h2', 'The Features link must close navigation and focus its heading.');
                assert.equal(await page.$eval('[data-mobile-navigation]', details => details.open), false);
                assert.equal(await page.$$eval('.site-frame > [inert]', elements => elements.length), 0);
            }
        }

        if (path === '/privacy-policy') {
            await focusWithKeyboard(page, '.privacy-contents a[href="#controller"]');
            await page.keyboard.press('Enter');
            await expectFocused(page, '#controller', 'Policy section links must move keyboard focus to their heading.');
            const position = await page.$eval('#controller', heading => ({
                top: heading.getBoundingClientRect().top,
                header: document.querySelector('.site-header').getBoundingClientRect().bottom,
            }));
            assert.ok(position.top >= position.header, 'The sticky header must not obscure policy headings.');
            await focusWithKeyboard(page, '.privacy-page [data-consent-open]');
            await page.keyboard.press('Enter');
            await page.waitForSelector('[data-consent-dialog][open]');
            await audit(page, `${context} cookie preferences open`, '[data-consent-dialog]');
            await checkReflow(page, `${context} cookie preferences open`);
            await page.keyboard.press('Tab');
            assert.equal(await page.evaluate(() => Boolean(document.activeElement.closest('[data-consent-dialog]'))), true);
            await assertFocusVisible(page);
            await page.keyboard.press('Escape');
            await expectFocused(page, '.privacy-page [data-consent-open]', 'Cookie preferences must return focus to the policy button.');
        } else if (path.startsWith('/docs/')) {
            await page.evaluate(() => {
                Object.defineProperty(navigator, 'clipboard', { configurable: true, value: { writeText: async () => {} } });
            });
            await focusWithKeyboard(page, '.code-block-copy');
            await page.keyboard.press('Enter');
            await page.waitForFunction(() => document.querySelector('#copy-status').textContent === 'Copied to clipboard.');
            assert.equal(await page.evaluate(() => document.activeElement.dataset.copyState), 'copied');
            assert.equal(await page.evaluate(() => document.activeElement.querySelector('.copy-feedback-mark').textContent), '✓');
            await page.evaluate(() => {
                Object.defineProperty(navigator, 'clipboard', { configurable: true, value: { writeText: async () => { throw new Error('Clipboard denied for test'); } } });
            });
            await page.keyboard.press('Enter');
            await page.waitForFunction(() => document.querySelector('.copy-feedback-message')?.textContent.includes('Could not copy'));
            await checkReflow(page, `${context} visible copy failure`);
        } else if (await page.$('[data-product-preview]')) {
            await checkScreenshotPreview(page, context, `.hero-preview-link.preview-version-${theme}`, { theme, viewport });
            await checkScreenshotPreview(page, context, '.workflow-panel:not([hidden]) [data-product-preview]');
            await focusWithKeyboard(page, '[data-workflow] [role="tab"][aria-selected="true"]');
            for (let step = 0; step < 3; step += 1) {
                await page.keyboard.press('ArrowRight');
                await expectFocused(page, '[data-workflow] [role="tab"][aria-selected="true"]', 'Arrow keys must select and focus the next workflow tab.');
                await audit(page, `${context} workflow step ${step + 1}`, '[data-workflow]');
            }
        }
        errors.forEach(error => record(context, `browser error: ${error}`));
        summary.keyboard += 1;
    } catch (error) {
        record(context, error.message);
    } finally {
        await page.close();
    }
}

async function checkResponsiveFocus(theme) {
    const context = `/docs/installation ${theme} responsive focus`;
    const { page } = await openPage('/docs/installation', viewports[0], theme);
    try {
        await focusWithKeyboard(page, '.docs-search-trigger');
        await page.keyboard.press('Enter');
        await page.setViewport({ width: 320, height: 800 });
        await pauseForLayout(page);
        await page.keyboard.press('Escape');
        await expectFocused(page, '.site-header [data-search-open]', 'Search must return to the visible header trigger after the sidebar closes.');

        await focusWithKeyboard(page, '[data-chapters] summary');
        await page.setViewport({ width: 1440, height: 1000 });
        await pauseForLayout(page);
        await expectFocused(page, '[data-chapters] a[aria-current="page"]', 'Hiding the chapter summary must focus the current chapter.');

        await page.setViewport({ width: 1000, height: 1000 });
        await pauseForLayout(page);
        await focusWithKeyboard(page, '.mobile-toc summary');
        await page.setViewport({ width: 1440, height: 1000 });
        await pauseForLayout(page);
        await expectFocused(page, '#main-content', 'Hiding the mobile table of contents summary must focus main content.');
        await page.setViewport({ width: 1000, height: 1000 });
        await pauseForLayout(page);
        await focusWithKeyboard(page, '.mobile-toc summary');
        await page.keyboard.press('Enter');
        await page.keyboard.press('Tab');
        await expectFocused(page, '.mobile-toc a', 'The mobile table of contents must expose keyboard links.');
        const target = await page.evaluate(() => document.activeElement.hash);
        await page.setViewport({ width: 1440, height: 1000 });
        await pauseForLayout(page);
        await expectFocused(page, target, 'Hiding a focused table of contents link must focus its target heading.');
        summary.keyboard += 1;
    } catch (error) {
        record(context, error.message);
    } finally {
        await page.close();
    }
}

try {
    const jobs = paths.flatMap(path => themes.flatMap(theme => viewports.map(viewport => ({ path, viewport, theme }))));
    let next = 0;
    await Promise.all(Array.from({ length: 4 }, async () => {
        while (next < jobs.length) {
            const job = jobs[next++];
            try { await checkPage(job.path, job.viewport, job.theme); }
            catch (error) { record(`${job.path} ${job.theme} ${job.viewport.name}`, error.stack); }
        }
    }));
    if (paths.includes('/')) {
        await checkHeroWithoutJavaScript();
        for (const theme of themes) {
            const viewport = { name: 'mobile', width: 390, height: 800 };
            const { page } = await openPage('/', viewport, theme);
            try {
                await checkCompactLayout(page, viewport, theme);
                await checkReflow(page, `/ ${theme} 390px compact layout`);
            } catch (error) {
                record(`/ ${theme} 390px compact layout`, error.stack);
            } finally {
                await page.close();
            }
        }
    }
    for (const path of ['/', '/docs/installation', '/privacy-policy'].filter(path => paths.includes(path))) {
        for (const theme of themes) {
            for (const viewport of viewports) { await checkKeyboard(path, viewport, theme); }
        }
    }
    if (paths.includes('/docs/installation')) {
        for (const theme of themes) { await checkResponsiveFocus(theme); }
    }
} finally {
    await browser.close();
}

const report = JSON.stringify({
    ...summary,
    manualReview: [...summary.manualReview].map(([check, states]) => ({ check, states })),
    failures,
}, null, 2);
if (process.env.LENS_BROWSER_TEST_REPORT) { await writeFile(process.env.LENS_BROWSER_TEST_REPORT, report); }
console.log(report);
process.exitCode = failures.length ? 1 : 0;
