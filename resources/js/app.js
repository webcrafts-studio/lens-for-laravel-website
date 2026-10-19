import './bootstrap';

const root = document.documentElement;
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

const menuToggle = document.querySelector('[data-menu-toggle]');
const mobileNav = document.getElementById('mobile-nav');
const closeMenu = (returnFocus = false) => {
    if (!mobileNav || mobileNav.hidden) { return; }
    mobileNav.hidden = true;
    menuToggle.setAttribute('aria-expanded', 'false');
    menuToggle.setAttribute('aria-label', 'Open navigation');
    if (returnFocus) { menuToggle.focus(); }
};
menuToggle?.addEventListener('click', () => {
    mobileNav.hidden = !mobileNav.hidden;
    menuToggle.setAttribute('aria-expanded', String(!mobileNav.hidden));
    menuToggle.setAttribute('aria-label', mobileNav.hidden ? 'Open navigation' : 'Close navigation');
});
mobileNav?.querySelectorAll('a').forEach(link => link.addEventListener('click', () => closeMenu(true)));
const mobileQuery = matchMedia('(max-width: 900px)');
mobileQuery.addEventListener('change', () => {
    const returnFocus = mobileNav?.contains(document.activeElement);
    closeMenu(false);
    if (returnFocus) { document.querySelector('.main-nav a')?.focus(); }
});
const chapters = document.querySelector('[data-chapters]');
if (chapters) {
    const setChapters = () => { chapters.open = !mobileQuery.matches; };
    setChapters();
    mobileQuery.addEventListener('change', () => {
        const focusedInside = chapters.contains(document.activeElement);
        setChapters();
        if (focusedInside && mobileQuery.matches) { chapters.querySelector('summary').focus(); }
    });
}

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
    input.focus();
};
document.querySelectorAll('[data-search-open]').forEach(button => button.addEventListener('click', () => openSearch(button)));
document.querySelector('[data-search-close]')?.addEventListener('click', () => dialog.close());
dialog?.addEventListener('close', () => { clearTimeout(searchTimer); if (trigger?.isConnected) { trigger.focus(); } });
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
    } catch (_) {
        announceCopy('Could not copy. Select the text and copy it manually.');
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
document.querySelectorAll('.docs-prose table').forEach(table => {
    const wrapper = document.createElement('div');
    wrapper.className = 'docs-table-wrapper';
    table.before(wrapper);
    wrapper.append(table);
    table.querySelectorAll('thead th').forEach(cell => { cell.scope = 'col'; });
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
document.querySelector('[data-back-top]')?.addEventListener('click', event => {
    event.preventDefault();
    window.scrollTo({ top: 0, behavior: matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth' });
    document.querySelector('.site-header .brand')?.focus({ preventScroll: true });
});
