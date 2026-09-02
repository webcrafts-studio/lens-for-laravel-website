# AI Fix Engine

The AI Fix Engine uses a local Ollama model, Gemini, OpenAI, or Anthropic to generate minimal accessibility fixes for located Blade, React, and Vue source files.

## Availability

AI Fix is an optional integration. It requires:

- PHP 8.3 or newer
- Laravel 12 or newer
- `laravel/ai` 0.3.2 or newer installed in the host application
- `LENS_FOR_LARAVEL_AI_ENABLED` not set to `false`

Install the optional SDK with:

```bash
composer require laravel/ai --dev
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

When you request a fix, Lens sends the following data to the configured provider:

- the accessibility issue description
- axe/WCAG tags
- the failing rendered DOM snippet
- the smallest relevant source element or component around the located line

Lens does not send the entire repository. With Ollama at its default localhost endpoint, the selected context stays on the machine running Laravel. Cloud providers and remote Ollama endpoints receive the context over the network. The selected fragment can still contain application data, template content, or secrets, so review it before requesting a fix.

## How It Works

When you click **AI FIX**, Lens:

1. validates the located file path
2. extracts the relevant element or balanced component around the line number
3. limits the selected source to 6000 bytes; for a larger component, it sends only its opening element
4. builds a prompt with the axe rule, WCAG tags, failing DOM snippet, and source code
5. sends the prompt through a dedicated accessibility agent to the configured provider
6. receives a minimal replacement and explanation
7. shows a diff preview and an optional in-modal editor in the dashboard
8. applies the generated or edited replacement only after you accept it
9. immediately marks the current issue as **AI Fix applied — pending re-scan**
10. keeps the issue in violation counts until a new axe-core scan verifies the result

## Reviewing and Editing in v3.1

The v3.1 modal lets you review the provider response as generated or edit the replacement before applying it. The editor includes:

- line numbers synchronized with editor scrolling
- Tab and Shift+Tab indentation for a caret or selected lines
- Ctrl+Enter or Cmd+Enter to apply
- live line-by-line diff updates
- line and character counts
- an edited-state badge
- reset to the untouched AI suggestion

The same server-side path, source, stale-content, and dangerous-code validation runs for generated and user-edited replacements. Replacement code is limited to 12000 characters and cannot be empty.

## Fix All Queues in v3.1

**Fix All A** and **Fix All AA** collect every current issue at the chosen level that has a supported source location. The dashboard opens the queue before generation completes and runs up to three workers concurrently.

Every queue item owns its issue, request controller, generation status, response, edited code, and review state. This lets the user:

- inspect ready proposals while other positions are queued or generating
- move with previous/next controls or numbered status buttons
- preserve independent edits while switching positions
- apply or reject one fix without blocking the rest
- retry one failed generation without discarding successful responses
- close the modal and abort every outstanding request

The request starts are staggered by 250 milliseconds so concurrent provider work remains fast while avoiding simultaneous SQLite-backed rate-limiter writes in common local Laravel setups. The suggestion endpoint allows 60 requests per minute for progressive queues.

## Source Context

Lens does not send an arbitrary fixed number of surrounding lines. It identifies the element reported by axe-core and, where practical, balances its matching closing tag and nested elements with the same name. This keeps unrelated templates, application text, and secrets out of the prompt.

The selected fragment is capped at 6000 bytes. If a complete component exceeds that limit, Lens sends only the opening element and instructs the agent not to invent code outside the selection.

## Generation Reliability from v3.0

The v3 agent remains deliberately conservative:

- maximum output is 12000 tokens
- temperature is `0` for more repeatable fixes
- Gemini thinking is limited to a 1024-token budget so reasoning cannot consume the output allowance
- the response schema contains only the minimal replacement and a short explanation
- a token-limit finish reason or malformed structured response triggers one controlled retry
- after that retry, Lens stops without changing a file and shows a safe, understandable error

For Gemini, OpenAI, and Anthropic, Lens selects only the provider and `laravel/ai` resolves its configured default model. In v3.3, Lens passes `LENS_FOR_LARAVEL_AI_OLLAMA_MODEL` to Ollama so a locally installed tag can be selected explicitly; if it is empty, the SDK's Ollama default is used. Successful attempts log the actual provider, resolved model, finish reason, and token usage. Failure logs contain diagnostic categories and exception classes, not the submitted source fragment or raw provider response.

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

## Local Models with Ollama in v3.3

Install [Ollama](https://ollama.com/download) from its official distribution, start it, and pull a code-capable model. For example:

```bash
ollama pull qwen2.5-coder:7b
ollama list
```

Configure Lens in the Laravel application's `.env`:

```text
LENS_FOR_LARAVEL_AI_ENABLED=true
LENS_FOR_LARAVEL_AI_PROVIDER=ollama
LENS_FOR_LARAVEL_AI_OLLAMA_MODEL=qwen2.5-coder:7b
LENS_FOR_LARAVEL_AI_OLLAMA_TIMEOUT=120
OLLAMA_URL=http://127.0.0.1:11434
```

The URL line can be omitted for Ollama's default localhost endpoint. If the host application uses `laravel/ai` 0.3.x, its configuration names the URL override `OLLAMA_BASE_URL`; current versions use `OLLAMA_URL`. After changing `.env`, clear cached configuration:

```bash
php artisan optimize:clear
```

Verify the daemon independently before opening Lens:

```bash
curl http://127.0.0.1:11434/api/tags
```

Then test the complete workflow:

1. Add a deliberately inaccessible element such as `<img src="logo.png">` to a rendered Blade, React, or Vue page.
2. Open the Lens dashboard and scan that page.
3. Confirm the issue has a source location, then click **AI FIX**.
4. Review the generated diff and confirm `storage/logs/laravel.log` records provider `ollama` and the selected model tag without logging source code.
5. Apply the reviewed proposal and confirm the issue remains marked **AI Fix applied — pending re-scan**.
6. Re-scan and confirm axe-core no longer reports the issue.

If generation fails, first confirm the model tag exactly matches `ollama list`, the Ollama process is reachable from the PHP process, and Laravel configuration is not cached with older values. Increase `LENS_FOR_LARAVEL_AI_OLLAMA_TIMEOUT` for a slower machine or larger model. Local models vary in structured-output and code quality; a larger code model can improve results at the cost of memory and response time.

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

v3.1 also protects the review state itself. Starting a newer individual request or Fix All queue aborts superseded requests, and late responses are ignored when their queue session is no longer current.

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
- Fix All queues include only A or AA issues with a located Blade, React, or Vue source file; unresolved source locations still require manual investigation.
- Suggestions are reviewed and applied one at a time. Fix All does not automatically write every generated response.
