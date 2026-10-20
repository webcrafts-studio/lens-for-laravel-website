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

async function checkCompactLayout(page, viewport, theme) {
    const context = `/ ${theme} ${viewport.width}px compact layout`;
    const desktop = viewport.width >= 1000;
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
        const heroVersion = [...heroArtwork.querySelectorAll('.preview-version')]
            .find(element => element.getBoundingClientRect().height > 0);
        const heroFrame = heroVersion.querySelector('.preview-viewport');
        const heroImage = heroFrame.querySelector('img');
        const artworkStyles = [heroArtwork, heroFrame, heroImage]
            .map(element => ({
                element: element.className || element.tagName.toLowerCase(),
                transform: getComputedStyle(element).transform,
                perspective: getComputedStyle(element).perspective,
            }));
        const artworkLayers = ['::before', '::after'].map(pseudo => ({
            pseudo,
            content: getComputedStyle(heroArtwork, pseudo).content,
            transform: getComputedStyle(heroArtwork, pseudo).transform,
            perspective: getComputedStyle(heroArtwork, pseudo).perspective,
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
            heroImageHeight: heroImage.getBoundingClientRect().height,
            artworkStyles,
            artworkLayers,
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
    initial.smallText.forEach(text => record(context, `text must be larger than 10px: ${text}`));
    summary.layouts.push({
        width: viewport.width, theme, page: Math.round(initial.total), hero: Math.round(initial.hero.height),
        heroPreview: Math.round(initial.heroPreview.height), workflow: Math.round(initial.workflow.height),
        fix: Math.round(initial.fix.height),
    });
    const pageLimit = desktop ? 7000 : viewport.width >= 390 ? 10500 : 11500;
    const heroLimit = desktop ? 720 : viewport.width >= 390 ? 1100 : 1200;
    if (initial.total > pageLimit) { record(context, `page height ${Math.round(initial.total)}px exceeds ${pageLimit}px`); }
    if (initial.hero.height > heroLimit) { record(context, `hero height ${Math.round(initial.hero.height)}px exceeds ${heroLimit}px`); }
    if (initial.heroPreview.height > (desktop ? 490 : 300)) {
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
    if (desktop && (initial.heroPreview.width > 592 + 1 || initial.heroImageHeight > 400 + 1)) {
        record(context, 'hero must keep a compact preview (at most 592px wide and a 400px screenshot)');
    }
    for (const element of initial.artworkStyles) {
        if (element.transform !== 'none' || element.perspective !== 'none') {
            record(context, `hero ${element.element} must display without tilt or perspective`);
        }
    }
    for (const layer of initial.artworkLayers) {
        if (!['none', 'normal'].includes(layer.content) || layer.transform !== 'none' || layer.perspective !== 'none') {
            record(context, `hero ${layer.pseudo} must not add a fake backplate`);
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
            await focusWithKeyboard(page, '.hero-art [data-product-preview]');
            const source = await page.evaluate(() => document.activeElement.querySelector('img').currentSrc || document.activeElement.href);
            const previewText = await page.evaluate(() => ({
                title: document.activeElement.dataset.previewTitle,
                description: document.activeElement.dataset.previewDescription,
                alternative: document.activeElement.querySelector('img').alt,
            }));
            await page.keyboard.press('Enter');
            await page.waitForFunction(() => document.querySelector('#product-preview-dialog').open);
            assert.equal(await page.$eval('[data-preview-image]', image => image.src), source);
            assert.equal(await page.$eval('[data-preview-image]', image => image.alt), previewText.alternative);
            assert.ok(previewText.alternative.length > 0 && previewText.alternative.length <= 100);
            assert.equal(await page.$eval('#product-preview-dialog', dialog => document.getElementById(dialog.getAttribute('aria-labelledby')).textContent), previewText.title);
            assert.equal(await page.$eval('#product-preview-dialog', dialog => document.getElementById(dialog.getAttribute('aria-describedby')).textContent), previewText.description);
            assert.equal(await page.$eval('[data-preview-file]', link => link.href), source);
            await page.waitForFunction(() => document.querySelector('[data-preview-image]').complete);
            await audit(page, `${context} screenshot open`, '#product-preview-dialog');
            await checkReflow(page, `${context} screenshot open`);
            await focusWithKeyboard(page, '[data-preview-size="original"]');
            await page.keyboard.press('Enter');
            assert.equal(await page.$eval('[data-preview-size="original"]', button => button.getAttribute('aria-pressed')), 'true');
            await focusWithKeyboard(page, '.preview-dialog-scroll');
            await page.keyboard.press('ArrowRight');
            await checkReflow(page, `${context} screenshot original size`);
            await page.keyboard.press('Escape');
            assert.equal(await page.$eval('#product-preview-dialog', dialog => dialog.open), false);
            await expectFocused(page, '[data-product-preview]', 'Closing screenshot must restore the preview link focus.');
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
