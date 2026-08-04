# Local Linter — Static Analysis

GitPR's linter validates code against custom rules **without consuming AI quotas**. It analyzes only the **added lines** in your `git diff`, making it fast, focused, and CI/CD ready.

::: tip ⚡ Linter Utility
Create, test, and validate YAML rules graphically in your browser: **[Open Rule Builder](/linter-utility?lang=en)**
:::

---

## Quick Start

```bash
# Generate the default linter config
gitpr -s

# Run the linter standalone (no AI)
gitpr -l
```

The linter also runs automatically as part of `--review` and `--fullreview`, with violations highlighted at the top of the review output.

---

## Configuration: `.gitpr.linter.yml`

Define rules using **Regular Expressions**:

```yaml
rules:
  - name: "check-localhost"
    extensions: ["js", "php", "py"]
    regex: 'http(s)?://(localhost|127\.0\.0\.1)'
    message: "🚨 Localhost usage detected in file {file_name}"
    ignore_comments: true
    ignore_paths:
      - "vendor/*"
      - "node_modules/*"
      - "tests/*"

  - name: "no-console-log"
    extensions: ["js", "ts"]
    regex: 'console\.log\('
    message: "🚨 console.log() found in {file_name}:{line_number}"
    ignore_comments: false

  - name: "no-debugger"
    extensions: ["js", "ts"]
    regex: 'debugger'
    message: "🚨 debugger statement found in {file_name}:{line_number}"
    ignore_comments: true

  - name: "no-todo-without-ticket"
    extensions: ["*"]
    regex: 'TODO(?!\s*\(\w+-\d+\))'
    message: "📝 TODO without ticket reference in {file_name}:{line_number}"
    ignore_comments: false
```

---

## Rule Fields

| Field | Required | Description |
| --- | --- | --- |
| `name` | Yes | Unique identifier for the rule |
| `extensions` | Yes | File extensions to check (`["*"]` for all) |
| `regex` | Yes | Regular expression to match |
| `message` | Yes | Violation message. Supports `{file_name}` and `{line_number}` |
| `ignore_comments` | No | Skip lines that are commented out (default: `false`) |
| `ignore_paths` | No | Glob patterns for directories/files to skip |

---

## CI/CD Integration

Run the linter in your pipeline to **block merges** with violations:

### GitHub Actions Example

```yaml
name: GitPR Linter
on: [pull_request]

jobs:
  lint:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
        with:
          fetch-depth: 0
      - name: Run GitPR Linter
        run: |
          gitpr --linter
```

---

## Pre-Commit Hooks

Install automatically with:

```bash
gitpr --installhooks
```

This creates `pre-commit` and `prepare-commit-msg` hooks that run the linter before every commit, catching issues at the earliest possible moment (**Shift-Left** approach).

---

## Why a Local Linter?

- **Zero AI cost** — no API calls, no rate limits
- **Instant feedback** — runs in milliseconds
- **Customizable** — rules match YOUR team's standards
- **Git-aware** — only checks what you changed, not the entire codebase
- **CI/CD native** — single command, no external services

---

## 🛠️ Interactive Linter Utility

::: tip ⚡ Rule Builder & Regex Tester
Need help creating your rules or testing your regular expressions visually in real-time?

<a href="/linter-utility?lang=en" class="inline-block mt-3 px-5 py-2.5 bg-gitpr_primary text-white font-semibold rounded-lg shadow-md hover:bg-blue-600 transition-colors no-underline">
  🚀 Open Interactive Linter Utility →
</a>
:::

---

[← Usage Guide](/uso) &nbsp;|&nbsp; [AI Providers →](/providers)
