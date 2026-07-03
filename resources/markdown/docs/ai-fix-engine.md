# AI Fix Engine

The AI Fix Engine uses Gemini, OpenAI, or Anthropic to generate minimal accessibility fixes for located Blade, React, and Vue source files.

## Availability

AI Fix is an optional integration. It requires:

- PHP 8.3 or newer
- Laravel 12 or newer
- `laravel/ai:^0.3.2` installed in the host application
- `LENS_FOR_LARAVEL_AI_ENABLED` not set to `false`

Install the optional SDK with:

```bash
composer require laravel/ai:^0.3.2 --dev
```

The core Lens package supports PHP 8.2+ and Laravel 10–13. On older supported runtimes or without the SDK, only AI Fix is unavailable. Scans, crawling, source mapping, history, PDF reports, previews, interactive states, baselines, and CLI audits continue to work.

## Supported Files

AI Fix can read and modify:

```text
resources/views/**/*.blade.php
resources/js/**/*.js
resources/js/**/*.jsx
resources/js/**/*.ts
resources/js/**/*.tsx
resources/js/**/*.vue
```

It will not write outside those paths.

## Data Sent to the Provider

When you request a fix, Lens sends the following data to the configured Gemini, OpenAI, or Anthropic provider:

- the accessibility issue description
- axe/WCAG tags
- the failing rendered DOM snippet
- the smallest relevant source element or component around the located line

Lens does not send the entire repository. The selected context can still contain application data, template content, or secrets, so review the source before requesting a fix and follow the chosen provider's data-handling policy.

## How It Works

When you click **AI FIX**, Lens:

1. validates the located file path
2. extracts the relevant element or balanced component around the line number
3. limits the selected source to 6000 bytes; for a larger component, it sends only its opening element
4. builds a prompt with the axe rule, WCAG tags, failing DOM snippet, and source code
5. sends the prompt through a dedicated accessibility agent to the configured provider
6. receives a minimal replacement and explanation
7. shows a diff preview in the dashboard
8. applies the change only after you accept it
9. immediately marks the current issue as **AI Fix applied — pending re-scan**
10. keeps the issue in violation counts until a new axe-core scan verifies the result

## Source Context

Lens does not send an arbitrary fixed number of surrounding lines. It identifies the element reported by axe-core and, where practical, balances its matching closing tag and nested elements with the same name. This keeps unrelated templates, application text, and secrets out of the prompt.

The selected fragment is capped at 6000 bytes. If a complete component exceeds that limit, Lens sends only the opening element and instructs the agent not to invent code outside the selection.

## Generation Reliability in v3.0

The v3.0 agent is deliberately conservative:

- maximum output is 12000 tokens
- temperature is `0` for more repeatable fixes
- Gemini thinking is limited to a 1024-token budget so reasoning cannot consume the output allowance
- the response schema contains only the minimal replacement and a short explanation
- a token-limit finish reason or malformed structured response triggers one controlled retry
- after that retry, Lens stops without changing a file and shows a safe, understandable error

Lens selects only the provider. It does not expose or force a model; `laravel/ai` resolves the default model configured for Gemini, OpenAI, or Anthropic. Successful attempts log the actual provider, resolved model, finish reason, and token usage. Failure logs contain diagnostic categories and exception classes, not the submitted source fragment or raw provider response.

## Framework-Aware Prompts

The prompt identifies the source type:

- Laravel Blade
- React
- Vue

The AI is instructed to preserve framework-specific syntax, whitespace, indentation, and unrelated code.

## Configure Provider

AI Fix can be disabled without disabling Lens:

```text
LENS_FOR_LARAVEL_AI_ENABLED=false
```

```text
LENS_FOR_LARAVEL_AI_PROVIDER=gemini
GEMINI_API_KEY=your-key
```

Or:

```text
LENS_FOR_LARAVEL_AI_PROVIDER=openai
OPENAI_API_KEY=your-key
```

Or:

```text
LENS_FOR_LARAVEL_AI_PROVIDER=anthropic
ANTHROPIC_API_KEY=your-key
```

## Applying a Fix

When you apply a fix:

1. Lens validates the target file path again.
2. It reads the current file content.
3. It verifies the original code block still exists.
4. It rejects stale fixes when the original block changed.
5. It writes the replacement with an exclusive lock.
6. Only the first reviewed occurrence is replaced when identical source fragments exist more than once.
7. The corresponding dashboard card is highlighted as pending verification; this does not claim that axe-core has confirmed the fix.

The pending marker survives closing the AI Fix modal during the current dashboard session. A new scan replaces the displayed issues with fresh axe-core results: a confirmed fix disappears, while an unresolved violation returns as an ordinary active issue.

## Security Controls

AI Fix includes several safeguards:

- path traversal rejection
- writes restricted to supported source directories
- rejection of generated server-side execution calls such as `shell_exec`, `system`, `exec`, `passthru`, `proc_open`, `popen`, and `eval`
- rejection of newly introduced raw PHP tags unless they already existed in the original block
- prompt-injection mitigation by treating scanned page content as untrusted data

## Limitations

- AI output must be reviewed before committing.
- The fix can be rejected if the file changed after scanning.
- Very large components are reduced to their opening element and may still require a manual edit.
- Dynamic abstractions can require manual edits when the located source is only the outer component.
