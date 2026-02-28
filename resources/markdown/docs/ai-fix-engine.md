# AI Fix Engine

The AI Fix Engine uses Google Gemini to analyse accessibility violations in context and generate minimal, precise Blade fixes that preserve your existing code structure.

## How It Works

When you click **AI FIX** on a violation in the dashboard, Lens's `AiFixer` service performs the following steps:

### 1. Path Validation

Lens enforces that only files within `resources/views/` can be read and modified. Any attempt to access files outside this directory is rejected immediately.

### 2. Context Reading

Rather than sending only the single failing element, Lens reads **±20 lines of surrounding Blade code** around the violation's line number. This gives Gemini full context — adjacent directives, component attributes, loop variables, and indentation patterns.

```text
Line 102: <div class="footer__social-links">
Line 103:   <ul>
Line 104:     @foreach($socialLinks as $link)
Line 105:       <li>
...
Line 112:         <a href="{{ $link->url }}" class="footer__social">   ← failing line
Line 113:           <i class="fa-brands fa-{{ $link->icon }}"
Line 114:              aria-hidden="true">
Line 115:           </i>
Line 116:         </a>
...
```

### 3. Prompt Construction

The AI prompt contains:

- The **accessibility rule description** (e.g., "Ensures links have discernible text")
- The **WCAG standards** the rule belongs to (e.g., `wcag2a`, `wcag21a`)
- The **failing HTML snippet** captured by Axe-core
- The **full 40-line Blade code block** for context
- An instruction to preserve Blade directives and indentation

The AI system prompt: *"You are an expert in web accessibility (WCAG) and Laravel Blade templates. You produce minimal, precise fixes that resolve accessibility violations without touching unrelated code."*

### 4. Structured AI Response

Gemini returns a structured JSON response with two fields:

```json
{
  "fixedCode": "<a href=\"{{ $link->url }}\" class=\"footer__social\" aria-label=\"{{ $link->label }}\">\n  ...\n</a>",
  "explanation": "Added aria-label attribute using the link's label property to provide discernible text for screen readers."
}
```

### 5. Fix Preview

The dashboard shows the original code and the proposed fix side by side before you apply it.

## Applying a Fix

When you click **APPLY FIX** in the dashboard:

1. Lens validates the file path is within `resources/views/`
2. It reads the current file content
3. It performs a string-replace of the `originalCode` block with `fixedCode`
4. If the original code is no longer present (stale fix), the application is aborted — you must re-run the scan to get a fresh fix
5. The file is written back to disk

> **Note:** Lens writes directly to your Blade files on disk. Commit the result to version control after reviewing.

## Requirements

The AI Fix Engine requires the `laravel/ai` package and a Gemini API key:

```bash
composer require laravel/ai
```

```text
GOOGLE_API_KEY=your_gemini_api_key
```

## Limitations

- **Heuristic accuracy:** FileLocator uses best-effort heuristics to find Blade source locations. The mapped file and line number may occasionally be incorrect, especially for deeply nested components or dynamically generated HTML.
- **Stale fixes:** If you modify the Blade file between scanning and applying, the original code may not match, and the fix will be rejected.
- **Context window:** Very large Blade files or those with complex nesting may confuse the AI context reading.
- **Gemini availability:** The AI Fix Engine requires an active internet connection and a valid Google AI API key.

> Automated AI fixes should be **reviewed before committing**. Always verify the generated fix resolves the violation without introducing regressions.
