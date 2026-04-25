# AI Fix Engine

The AI Fix Engine uses Gemini, OpenAI, or Anthropic to generate minimal accessibility fixes for located Blade, React, and Vue source files.

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

## How It Works

When you click **AI FIX**, Lens:

1. validates the located file path
2. reads source context around the line number
3. expands the context to include a matching closing tag when needed
4. builds a prompt with the axe rule, WCAG tags, failing DOM snippet, and source code
5. sends the prompt to the configured provider
6. receives `fixedCode` and `explanation`
7. shows a diff preview in the dashboard
8. applies the change only after you accept it

## Source Context

Lens reads approximately 20 lines above and below the detected line. For larger elements, it expands downward until the matching closing tag is included.

This helps prevent fixes such as changing `<div>` to `<header>` without also changing `</div>` to `</header>`.

## Framework-Aware Prompts

The prompt identifies the source type:

- Laravel Blade
- React
- Vue

The AI is instructed to preserve framework-specific syntax, whitespace, indentation, and unrelated code.

## Configure Provider

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
- Very large context blocks are rejected and must be fixed manually.
- Dynamic abstractions can require manual edits when the located source is only the outer component.
