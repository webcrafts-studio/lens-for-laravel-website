# Frontend Support

Lens supports Laravel applications using Blade, Livewire, React, Vue, Inertia, or mixed frontends.

All source locators and scans support the core PHP 8.2+/Laravel 10–13 matrix. References to AI Fix on this page require PHP 8.3+, Laravel 12+, and the optional `laravel/ai` package.

## Blade

Blade is supported through the source locator for:

```text
resources/views/**/*.blade.php
```

Lens matches the rendered DOM back to Blade using:

- tag names
- `id`
- `name`
- selector classes and IDs
- Blade component tags such as `<x-...>`
- rendered `src` or `href` values, including source-file basenames inside Blade helpers
- numeric `:nth-child(...)` positions when repeated elements otherwise have the same classes or attributes

AI Fix can read and modify located Blade files under `resources/views`.

## Livewire

Livewire is supported through the rendered DOM and Blade locator. For delayed rendering or hydration, configure:

```text
LENS_FOR_LARAVEL_SCAN_WAIT_MS=500
```

Lens scans the page state after load. For open modals, expanded menus, validation errors, active tabs, and similar interaction-only UI, use interactive state scans.

Example:

```text
state: Validation errors
type: input[name="email"] => invalid@example.test
click: button[type="submit"]
wait: 300
```

## React

Lens scans the hydrated DOM and locates React source files under:

```text
resources/js/**/*.js
resources/js/**/*.jsx
resources/js/**/*.ts
resources/js/**/*.tsx
```

Supported locator patterns include:

- `id="logo"`
- `name="email"`
- `href={'/pricing'}`
- `className="main-logo"`
- selector variants such as `primary-button`, `primaryButton`, and `PrimaryButton`
- Inertia page files under `resources/js/Pages/**`

AI Fix can modify React files under `resources/js`.

## Vue

Lens locates Vue single-file components under:

```text
resources/js/**/*.vue
```

Supported locator patterns include:

- static template attributes: `class="logo"`, `href="/pricing"`
- bindings: `:href="'/pricing'"`, `v-bind:href="'/pricing'"`
- class object keys: `:class="{ active: isActive }"`

AI Fix can modify Vue files under `resources/js`.

## Inertia

Inertia React and Vue apps work through the React and Vue locators. Lens searches all frontend files under `resources/js`, including conventional Inertia page paths:

```text
resources/js/Pages/**/*.tsx
resources/js/Pages/**/*.jsx
resources/js/Pages/**/*.vue
```

For link discovery in SPA-heavy apps, enable JavaScript crawling:

```text
LENS_FOR_LARAVEL_CRAWLER_RENDER_JAVASCRIPT=true
```

For stateful client-side UI, use interactive state scans to run axe-core after clicks, typing, selections, and waits.

## Source Type

When Lens locates a source file, issues include:

```json
{
  "fileName": "js/Pages/Dashboard.tsx",
  "lineNumber": 42,
  "sourceType": "react"
}
```

Possible `sourceType` values:

- `blade`
- `react`
- `vue`
- `null` when no source location is found

The dashboard shows the source type next to the source location, and history stores it with each issue.

## Repeated Element Mapping in v3.1

Rendered pages often contain several logos, cards, or links with the same class. Earlier class-first matching could select the first source occurrence even when axe-core reported a later sibling.

In v3.1, Lens ranks stronger identifiers before shared classes:

1. exact `id` or `name`
2. rendered source attributes, including the filename from an absolute `src` URL
3. target element classes
4. selector fragments and tag-only fallbacks

When the axe selector includes a numeric `:nth-child(...)`, Lens uses that position to disambiguate equally strong matches in the same Blade, React, or Vue file. Source mapping remains heuristic, but repeated elements such as `.hero-logo` siblings now resolve to the intended occurrence instead of always returning the first one.

## Interactive State Labels

Issues found during interactive state scans include the state label:

```json
{
  "fileName": "js/Components/MobileMenu.vue",
  "lineNumber": 18,
  "sourceType": "vue",
  "stateLabel": "Navigation open"
}
```

The dashboard, scan history, PDF reports, and scan comparison preserve this value. This matters when the same selector appears in multiple UI states.

## Limitations

Source mapping is heuristic. Lens can miss or misidentify source files when markup is deeply abstracted or generated at runtime.

Common difficult cases:

- custom components such as `<LogoImage />` that render HTML internally
- CSS modules where final class names do not resemble source keys
- dynamic class builders without literal class names
- runtime-generated attributes
- UI states that require long or complex workflows beyond the recorder/script model

For those cases, use the DOM selector and screenshot preview to guide manual investigation.
