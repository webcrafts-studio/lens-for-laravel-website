import './bootstrap';
import './product-previews';

const root = document.documentElement;
let lastFocusedElement = document.activeElement;
document.addEventListener('focusin', event => { lastFocusedElement = event.target; });
const responsiveFocus = () => document.activeElement === document.body ? lastFocusedElement : document.activeElement;
const header = document.querySelector('.site-header');
if (header) {
    const syncHeaderHeight = () => root.style.setProperty('--site-header-height', `${Math.ceil(header.getBoundingClientRect().height)}px`);
    syncHeaderHeight();
    new ResizeObserver(syncHeaderHeight).observe(header);
}
const themeToggle = document.querySelector('[data-theme-toggle]');
const syncTheme = () => {
    const dark = root.classList.contains('dark');
    themeToggle?.setAttribute('aria-pressed', String(dark));
    themeToggle?.setAttribute('aria-label', dark ? 'Switch to light mode' : 'Switch to dark mode');
};
syncTheme();
themeToggle?.addEventListener('click', () => {
    const dark = root.classList.toggle('dark');
    try { localStorage.setItem('lens-theme', dark ? 'dark' : 'light'); } catch (_) {}
    syncTheme();
});

const mobileNavigation = document.querySelector('[data-mobile-navigation]');
const menuTrigger = mobileNavigation?.querySelector('summary');
const menuPanel = mobileNavigation?.querySelector('nav');
const desktopNavigation = matchMedia('(min-width: 1024px)');
const menuBackground = [...(header?.parentElement.children || [])].filter(element => element !== header);
const inertMenuBackground = new Set();
const syncMenu = () => {
    const open = Boolean(mobileNavigation?.open);
    menuTrigger?.setAttribute('aria-expanded', String(open));
    if (open) {
        menuBackground.forEach(element => {
            if (!element.inert) {
                element.inert = true;
                inertMenuBackground.add(element);
            }
        });
    } else {
        inertMenuBackground.forEach(element => { element.inert = false; });
        inertMenuBackground.clear();
    }
};
const closeMenu = (returnFocus = false) => {
    if (!mobileNavigation?.open) { return; }
    mobileNavigation.open = false;
    syncMenu();
    if (returnFocus) { menuTrigger.focus(); }
};
syncMenu();
mobileNavigation?.addEventListener('toggle', syncMenu);
document.addEventListener('click', event => {
    if (!mobileNavigation?.contains(event.target)) { closeMenu(mobileNavigation?.contains(responsiveFocus())); }
});
document.addEventListener('focusin', event => {
    if (!mobileNavigation?.contains(event.target)) { closeMenu(); }
});
menuPanel?.addEventListener('click', event => {
    const link = event.target.closest('a');
    if (!link) { return; }
    closeMenu(link.target === '_blank');
    if (link.origin === location.origin && link.pathname === location.pathname && link.hash) {
        const heading = document.getElementById(link.hash.slice(1))?.querySelector('h2');
        requestAnimationFrame(() => {
            if (heading && !heading.hasAttribute('tabindex')) {
                heading.tabIndex = -1;
                heading.addEventListener('blur', () => heading.removeAttribute('tabindex'), { once: true });
            }
            heading?.focus({ preventScroll: true });
        });
    }
});
desktopNavigation.addEventListener('change', () => {
    const active = responsiveFocus();
    closeMenu();
    if (desktopNavigation.matches && mobileNavigation?.contains(active)) {
        const link = [...document.querySelectorAll('.main-nav a')].find(item => item.href === active.href);
        (link || document.querySelector('.main-nav a'))?.focus();
    } else if (!desktopNavigation.matches && document.querySelector('.main-nav')?.contains(active)) {
        menuTrigger?.focus();
    }
});
const mobileQuery = matchMedia('(max-width: 900px)');
const chapters = document.querySelector('[data-chapters]');
if (chapters) {
    const setChapters = () => { chapters.open = !mobileQuery.matches; };
    setChapters();
    mobileQuery.addEventListener('change', () => {
        const active = responsiveFocus();
        const focusedInside = chapters.contains(active);
        setChapters();
        if (focusedInside && mobileQuery.matches) { chapters.querySelector('summary').focus(); }
        else if (active === chapters.querySelector('summary')) { (chapters.querySelector('[aria-current="page"]') || chapters.querySelector('a'))?.focus(); }
    });
}
const mobileToc = document.querySelector('.mobile-toc');
matchMedia('(min-width: 1201px)').addEventListener('change', event => {
    const active = responsiveFocus();
    if (event.matches && mobileToc?.contains(active)) {
        const target = active.hash ? document.getElementById(active.hash.slice(1)) : document.getElementById('main-content');
        if (target && !target.hasAttribute('tabindex')) {
            target.tabIndex = -1;
            target.addEventListener('blur', () => target.removeAttribute('tabindex'), { once: true });
        }
        target?.focus({ preventScroll: true });
    }
});

const dialog = document.getElementById('docs-search');
const input = document.getElementById('docs-search-input');
const results = document.getElementById('search-results');
const searchStatus = document.getElementById('search-status');
const index = JSON.parse(document.getElementById('docs-search-index')?.textContent || '[]');
let trigger;
let searchTimer;
const renderSearch = () => {
    const query = input.value.trim().toLocaleLowerCase();
    results.replaceChildren();
    if (!query) {
        searchStatus.textContent = 'Search across all documentation pages.';
        return;
    }
    const terms = query.split(/\s+/);
    const matches = index.map(item => {
        const title = item.title.toLocaleLowerCase();
        const content = item.content.toLocaleLowerCase();
        return { ...item, score: terms.every(term => title.includes(term) || content.includes(term)) ? terms.reduce((score, term) => score + (title.includes(term) ? 10 : 1), 0) : 0 };
    }).filter(item => item.score > 0).sort((a, b) => b.score - a.score);
    searchStatus.textContent = matches.length ? `${matches.length} ${matches.length === 1 ? 'page' : 'pages'} found.` : 'No results. Try another search term.';
    matches.forEach(item => {
        const li = document.createElement('li');
        const a = document.createElement('a');
        a.href = item.url;
        const title = document.createElement('strong');
        title.textContent = item.title;
        const section = document.createElement('small');
        section.textContent = item.section.toLocaleLowerCase();
        const snippet = document.createElement('p');
        const position = Math.max(0, item.content.toLocaleLowerCase().indexOf(terms[0]) - 50);
        snippet.textContent = `${position ? '...' : ''}${item.content.slice(position, position + 150)}...`;
        a.append(title, section, snippet);
        li.append(a);
        results.append(li);
    });
};
const openSearch = (source = document.activeElement) => {
    if (dialog.open) { input.focus(); return; }
    trigger = source;
    closeMenu(false);
    input.value = '';
    renderSearch();
    dialog.showModal();
    root.classList.add('search-open');
    input.focus();
};
document.querySelectorAll('[data-search-open]').forEach(button => button.addEventListener('click', () => openSearch(button)));
document.querySelector('[data-search-close]')?.addEventListener('click', () => dialog.close());
dialog?.addEventListener('close', () => {
    root.classList.remove('search-open');
    clearTimeout(searchTimer);
    const triggerIsVisible = trigger?.isConnected && (trigger.checkVisibility?.() ?? (trigger.getClientRects().length > 0 && !trigger.closest('details:not([open])')));
    const returnTarget = triggerIsVisible ? trigger : document.querySelector('.site-header [data-search-open]');
    returnTarget?.focus();
});
dialog?.addEventListener('click', event => {
    const rect = dialog.getBoundingClientRect();
    if (event.target === dialog && (event.clientX < rect.left || event.clientX > rect.right || event.clientY < rect.top || event.clientY > rect.bottom)) { dialog.close(); }
});
input?.addEventListener('input', () => { clearTimeout(searchTimer); searchTimer = setTimeout(renderSearch, 120); });
document.addEventListener('keydown', event => {
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') { event.preventDefault(); openSearch(); }
    if (event.key === 'Escape') {
        if (dialog?.open) { event.preventDefault(); dialog.close(); }
        else { closeMenu(true); }
    }
    if (event.key === 'Tab' && dialog?.open) {
        const controls = [...dialog.querySelectorAll('button, input, a[href]')];
        const first = controls[0];
        const last = controls.at(-1);
        if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last.focus(); }
        else if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first.focus(); }
    }
});

const status = document.getElementById('copy-status');
let statusTimer;
const copyFeedbackTimers = new WeakMap();
const copyErrors = new WeakMap();
const announceCopy = message => {
    clearTimeout(statusTimer);
    status.textContent = '';
    requestAnimationFrame(() => { status.textContent = message; });
    statusTimer = setTimeout(() => { status.textContent = ''; }, 6000);
};
document.addEventListener('click', async event => {
    const button = event.target.closest('[data-copy]');
    if (!button) { return; }
    try {
        if (!navigator.clipboard?.writeText) { throw new Error('Clipboard unavailable'); }
        await navigator.clipboard.writeText(button.dataset.copy);
        announceCopy('Copied to clipboard.');
        copyErrors.get(button)?.remove();
        copyErrors.delete(button);
        clearTimeout(copyFeedbackTimers.get(button));
        if (!button.querySelector('.copy-feedback-mark')) {
            const mark = document.createElement('span');
            mark.className = 'copy-feedback-mark';
            mark.textContent = '✓';
            mark.setAttribute('aria-hidden', 'true');
            button.append(mark);
        }
        button.dataset.copyState = 'copied';
        copyFeedbackTimers.set(button, setTimeout(() => { delete button.dataset.copyState; }, 6000));
    } catch (_) {
        const message = 'Could not copy. Select the text and copy it manually.';
        announceCopy(message);
        delete button.dataset.copyState;
        if (!copyErrors.has(button)) {
            const error = document.createElement('p');
            error.className = 'copy-feedback-message';
            error.setAttribute('aria-hidden', 'true');
            error.textContent = message;
            (button.closest('.install-command, .code-block-header') || button).after(error);
            copyErrors.set(button, error);
        }
    }
});

document.querySelectorAll('.docs-prose pre > code').forEach((code, index) => {
    const pre = code.parentElement;
    const wrapper = document.createElement('div');
    wrapper.className = 'code-block-wrapper';
    const header = document.createElement('div');
    header.className = 'code-block-header';
    const language = [...code.classList].find(name => name.startsWith('language-'))?.replace('language-', '') || 'Code';
    const label = document.createElement('span');
    label.textContent = language;
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'code-block-copy';
    button.textContent = 'Copy';
    button.setAttribute('aria-label', `Copy ${language} code block ${index + 1}`);
    button.dataset.copy = code.textContent;
    header.append(label, button);
    pre.before(wrapper);
    wrapper.append(header, pre);
});
document.querySelectorAll('.docs-prose table').forEach((table, index) => {
    const wrapper = document.createElement('div');
    wrapper.className = 'docs-table-wrapper';
    table.before(wrapper);
    wrapper.append(table);
    table.querySelectorAll('thead th').forEach(cell => { cell.scope = 'col'; });
    const syncTableScroll = () => {
        if (wrapper.scrollWidth > wrapper.clientWidth + 1) {
            wrapper.tabIndex = 0;
            wrapper.setAttribute('role', 'region');
            wrapper.setAttribute('aria-label', table.caption?.textContent.trim() || `Scrollable documentation table ${index + 1}`);
        } else {
            wrapper.removeAttribute('tabindex');
            wrapper.removeAttribute('role');
            wrapper.removeAttribute('aria-label');
        }
    };
    new ResizeObserver(syncTableScroll).observe(wrapper);
    syncTableScroll();
});
const tocLinks = [...document.querySelectorAll('.docs-toc a')];
const headings = [...document.querySelectorAll('.docs-prose h2[id], .docs-prose h3[id]')];
if (headings.length && tocLinks.length) {
    const updateToc = () => {
        const current = [...headings].reverse().find(heading => heading.getBoundingClientRect().top < 160) || headings[0];
        tocLinks.forEach(link => {
            if (link.hash === `#${current.id}`) { link.setAttribute('aria-current', 'location'); }
            else { link.removeAttribute('aria-current'); }
        });
    };
    let scheduled = false;
    window.addEventListener('scroll', () => {
        if (!scheduled) { requestAnimationFrame(() => { updateToc(); scheduled = false; }); scheduled = true; }
    }, { passive: true });
    updateToc();
}
const workflow = document.querySelector('[data-workflow]');
if (workflow) {
    const tabs = [...workflow.querySelectorAll('[role="tab"]')];
    const selectTab = tab => {
        tabs.forEach(item => {
            const selected = item === tab;
            item.setAttribute('aria-selected', String(selected));
            item.tabIndex = selected ? 0 : -1;
            document.getElementById(item.getAttribute('aria-controls')).hidden = !selected;
        });
    };
    tabs.forEach((tab, index) => {
        tab.addEventListener('click', () => selectTab(tab));
        tab.addEventListener('keydown', event => {
            let target;
            if (event.key === 'ArrowRight') { target = tabs[(index + 1) % tabs.length]; }
            else if (event.key === 'ArrowLeft') { target = tabs[(index + tabs.length - 1) % tabs.length]; }
            else if (event.key === 'Home') { target = tabs[0]; }
            else if (event.key === 'End') { target = tabs.at(-1); }
            if (target) { event.preventDefault(); selectTab(target); target.focus(); }
        });
    });
}
