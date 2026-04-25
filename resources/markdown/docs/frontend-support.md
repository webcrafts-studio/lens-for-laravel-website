# Frontend Support

Lens v2.0.0 supports Laravel applications using Blade, Livewire, React, Vue, Inertia, or mixed frontends.

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

AI Fix can read and modify located Blade files under `resources/views`.

## Livewire

Livewire is supported through the rendered DOM and Blade locator. For delayed rendering or hydration, configure:

```text
LENS_FOR_LARAVEL_SCAN_WAIT_MS=500
```

Lens scans the page state after load. Interactive states such as open modals, expanded menus, validation errors, and active tabs still need targeted test flows or manual review.

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

## Limitations

Source mapping is heuristic. Lens can miss or misidentify source files when markup is deeply abstracted or generated at runtime.

Common difficult cases:

- custom components such as `<LogoImage />` that render HTML internally
- CSS modules where final class names do not resemble source keys
- dynamic class builders without literal class names
- runtime-generated attributes
- UI states that appear only after user interaction

For those cases, use the DOM selector and screenshot preview to guide manual investigation.
