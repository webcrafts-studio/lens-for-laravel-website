import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import puppeteer from 'puppeteer';

let input = '';
for await (const chunk of process.stdin) input += chunk;
const { html, cssPath } = JSON.parse(input);
const browser = await puppeteer.launch({ headless: true });
try {
    const page = await browser.newPage();
    await page.setRequestInterception(true);
    page.on('request', request => request.abort());
    await page.setContent(html.replace(/<script\b[^>]*>[\s\S]*?<\/script>/gi, '').replace(/<link\b[^>]*>/gi, ''));
    await page.addStyleTag({ path: cssPath });
    const source = await readFile('resources/js/app.js', 'utf8');
    await page.addScriptTag({ content: source.replace(/^import .*;$/gm, '') });
    const settle = () => page.evaluate(() => new Promise(resolve => requestAnimationFrame(() => requestAnimationFrame(resolve))));
    for (const width of [320, 390, 740, 768, 900, 1023, 1024, 1100, 1200, 1201, 1280, 1440]) {
        await page.setViewport({ width, height: 900 });
        await settle();
        const layout = await page.evaluate(() => {
            const rect = element => {
                const { left, right, top, bottom, height } = element.getBoundingClientRect();
                return { left, right, top, bottom, height };
            };
            const nav = document.querySelector('.main-nav');
            return {
                header: rect(document.querySelector('.site-header')),
                brand: rect(document.querySelector('.site-header .brand')),
                nav: rect(nav),
                links: [...nav.children].map(rect),
                actions: rect(document.querySelector('.header-actions')),
                desktop: getComputedStyle(nav).display !== 'none',
                menu: getComputedStyle(document.querySelector('.mobile-navigation')).display !== 'none',
                overflow: document.documentElement.scrollWidth > innerWidth,
            };
        });
        assert.equal(layout.desktop, width >= 1024, `Desktop visibility at ${width}px`);
        assert.equal(layout.menu, width < 1024, `Menu visibility at ${width}px`);
        assert.ok(layout.header.height <= 89, `Header must remain one row at ${width}px`);
        assert.ok(!layout.overflow, `No horizontal overflow at ${width}px`);
        assert.ok(layout.brand.right <= layout.actions.left, `Brand and controls must not overlap at ${width}px`);
        if (layout.desktop) {
            assert.ok(layout.brand.right < layout.nav.left && layout.nav.right < layout.actions.left, `Navigation must fit at ${width}px`);
            assert.ok(layout.links.every(link => link.top === layout.links[0].top && link.height === layout.links[0].height), `Links must share one row at ${width}px`);
            assert.ok(layout.links.every(link => link.right <= layout.actions.left), `Links must not overlap controls at ${width}px`);
        }
    }
    await page.setViewport({ width: 1024, height: 900 });
    await settle();
    await page.focus('.main-nav a');
    await page.setViewport({ width: 1023, height: 900 });
    await settle();
    assert.equal(await page.$eval('.menu-trigger', element => element === document.activeElement), true);
    await page.click('.menu-trigger');
    await page.focus('#mobile-site-navigation a');
    await page.setViewport({ width: 1024, height: 900 });
    await settle();
    assert.equal(await page.$eval('.main-nav a', element => element === document.activeElement), true);
    assert.equal(await page.$eval('[data-mobile-navigation]', element => element.open), false);
    assert.equal(await page.$$eval('.site-frame > [inert]', elements => elements.length), 0);
} finally {
    await browser.close();
}
