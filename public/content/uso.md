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

### 🪝 Git Hooks

```bash
gitpr -ih
# or
gitpr --installhooks
```

Installs `pre-commit` and `prepare-commit-msg` hooks in your repository for automatic quality gates.

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

---

[← Installation](/instalacao) &nbsp;|&nbsp; [Linter Guide →](/linter)
