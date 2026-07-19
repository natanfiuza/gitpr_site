# How to Use GitPR CLI

GitPR has a powerful default behavior and advanced options for every stage of your Git workflow.

---

## Default Behavior: PR Generation

Simply run:

```bash
gitpr
```

The tool will:
1. Sync with the remote (`git fetch`)
2. Compare your changes against `origin/main`
3. Generate a Markdown file (e.g., `feature-login_20260421110134_PR_DESC.md`) with a complete Pull Request description

---

## Commands & Flags

### 🔖 Commit Message

```bash
gitpr -c
# or
gitpr --commit
```

Runs `git diff` and outputs a **Conventional Commits** message. Great for quick, standardized commits.

---

### 🔍 Code Review (Staged Changes)

```bash
gitpr -r
# or
gitpr --review
```

Detailed AI review of your local staged changes. Focuses on bugs, security, performance, and code quality.

---

### 🔎 Full Code Review

```bash
gitpr -f
# or
gitpr --fullreview
```

Complete review analyzing **all changes since the remote branch**. Ideal for comprehensive PR reviews.

---

### 📄 Full File Audit

```bash
gitpr -r -i src/legacy_module.py
# or
gitpr --review --input path/to/file
```

Ignores git history and audits the **entire file**. Excellent for legacy code refactoring consulting. Must be used with `-r` or `-f`.

---

### 💬 Interactive Chat (Pair Programming)

```bash
gitpr -ch
# or
gitpr --chat
```

Opens a **TUI terminal** where the AI sees your current diff and maintains contextual conversation:

| Shortcut | Action |
| --- | --- |
| `F2` | Refresh diff context |
| `F5` | Extract code blocks into patch file |
| `F6` | Export session to Markdown |
| `/explain` | Explain the current diff |
| `/tests` | Generate unit tests |
| `/optimize` | Suggest optimizations |
| `/clear` | Clear conversation memory |

Memory is **per-branch**, so switching branches gives you a clean context.

---

### 🛡️ Static Linter

```bash
gitpr -l
# or
gitpr --linter
```

Runs **only the local static linter** — zero AI cost. Validates changed lines against rules in `.gitpr.linter.yml`. Perfect for CI/CD pipelines and pre-commit hooks.

---

### 🎫 Issue Generator

```bash
gitpr -is
# or
gitpr --issue
```

Opens an interactive **TUI panel** for editing and submitting structured issues. **3 context engines**:

| Command | Context | Use Case |
| --- | --- | --- |
| `gitpr -is` | Current `git diff` | Document a task you just finished coding |
| `gitpr -is -ht` | Full branch history | Generate release/epic documentation |
| `gitpr -is -b file:lines` | File timeline via `git blame` | Document legacy code evolution and technical debt |

---

### 🏺 Code Archaeologist (Git Blame)

```bash
gitpr -b file.py:10-20
# or
gitpr --blame path/to/file
```

Traces the **origin and evolution** of business rules using `git blame` with AI classification. Two modes:

| Mode | Command | Description |
| --- | --- | --- |
| **Direct** | `gitpr --blame file.py:10-20` | Analyze a specific line range immediately |
| **Interactive** | `gitpr --blame file.py` | Browse the file and select the target region in a TUI |

The AI classifies each relevant commit as **ORIGIN** (the business rule was introduced) or **REFACTORING** (transformation without logic change).

> 💡 **Combine with `--issue`** to generate structured technical debt issues from the blame analysis:
> ```bash
> gitpr -is --blame legacy_module.py:45-120
> ```

---

### 🪝 Git Hooks

```bash
gitpr -ih
# or
gitpr --installhooks
```

Installs `pre-commit` and `prepare-commit-msg` hooks in your repository for automatic quality gates.

::: note --hook `<file>`
GitPR has a hidden flag `--hook <file>` triggered exclusively by the Git Hooks system in the background. It allows the AI to inject the suggested commit message directly into Git's temporary file, without cluttering your terminal.
:::

---

### 🎨 Skills Templates

```bash
gitpr -s
# or
gitpr --skill
```

Generates customizable AI prompt templates (`.gitpr.*.md` files) and linter rules (`.gitpr.linter.yml`) in your project root.

---

### 🌐 Language & Provider Overrides

```bash
# Force language for this execution
gitpr --lang pt_br

# Switch AI provider on the fly
gitpr --provider deepseek
gitpr --provider ollama
```

---

### 🔄 Auto-Updater

```bash
gitpr -u
# or
gitpr --update
```

Checks GitHub Releases for the latest version and hot-swaps the binary.

---

### ❓ Help

```bash
gitpr -h              # General help
gitpr -h --issue      # Contextual help for the issue command
gitpr -h --linter     # Contextual help for the linter command
```

::: note --pre-save (Debug)
GitPR has a hidden debug flag `--pre-save` that can be combined with any AI command (e.g., `gitpr -c --pre-save`). Before each AI call, it saves the **full payload** sent to the model — system instruction + prompt + character counters — to a `_{action}-{datetime}.json` file in the current folder. Useful for inspecting very large prompts. When the response comes from the local cache, no call is made and no file is generated.
:::

---

[← Installation](/instalacao) &nbsp;|&nbsp; [Linter Guide →](/linter)
