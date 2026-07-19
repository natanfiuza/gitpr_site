# GitPR CLI 🚀

<p align="center">
  <img src="https://raw.githubusercontent.com/natanfiuza/gitpr/main/docs/logo.png" alt="GitPR Logo" width="120">
</p>

**AI-powered Git workflow automation** — Code Reviews, PR descriptions, semantic commits, and more, directly from your terminal.

GitPR CLI uses **Google Gemini** and **DeepSeek** to analyze your `git diff` and entire files, generating:
- Commit messages in the **Conventional Commits** standard
- Detailed **Pull Request** descriptions
- Deep **Code Reviews** focused on reducing technical debt
- **Static linting** reports without consuming AI quotas

---

## ⚡ Quick Start

```bash
# Download from GitHub Releases and add to your PATH, then:
gitpr
```

On first run, a wizard will guide you through setup — just enter your API key and you're ready.

---

## 🎯 Key Features

| Feature | Command | Description |
| --- | --- | --- |
| **PR Generation** | `gitpr` | Auto-generates pull request descriptions from your diff |
| **Commit Messages** | `gitpr -c` | Semantic commit messages in Conventional Commits format |
| **Code Review** | `gitpr -r` | Detailed review of staged changes |
| **Full Review** | `gitpr -f` | Complete review against the remote branch |
| **File Audit** | `gitpr -r -i <file>` | Full file analysis, great for legacy code refactoring |
| **Interactive Chat** | `gitpr -ch` | Pair-programming TUI with memory, slash commands, and auto-patch |
| **Static Linter** | `gitpr -l` | Offline rule validation — zero AI cost, CI/CD ready |
| **Issue Generator** | `gitpr -is` | Auto-generate structured issues with 3 context engines |
| **Git Hooks** | `gitpr -ih` | Install pre-commit and prepare-commit-msg hooks locally |
| **Code Archaeologist** | `gitpr -b` | Trace origin of business rules via `git blame` with AI classification |
| **Auto-Update** | `gitpr -u` | Hot-swap binary updates from GitHub Releases |

::: note Hidden Technical Flags
- **`--hook <file>`** — Used internally by Git Hooks to inject commit messages directly into Git's temp file.
- **`--pre-save`** — Debug flag that saves the full AI payload (system instruction + prompt) to a JSON file before each call. Combine with any AI command (e.g., `gitpr -c --pre-save`).
:::

---

## 🧠 Multi-Model Architecture

GitPR is **AI-agnostic** — choose your engine:

- **Google Gemini** (default: `gemini-pro-latest`)
- **DeepSeek** (default: `deepseek-v4-pro`)
- **Ollama** — run local models without internet

Switch anytime with `--provider <gemini|deepseek|ollama>`.

---

## 🌐 Internationalization

GitPR auto-detects your system language. Currently supports **PT-BR** and **EN**, with translations downloaded automatically. Force a language with `--lang pt_br` or set `GITPR_LANG` in your config.

---

## 📦 Map-Reduce for Huge Diffs

When your diff is too large for a single AI call (~90k tokens), GitPR automatically splits it by file, summarizes each chunk (**Map**), and unifies everything (**Reduce**) — no flags needed.

---

## 🔒 Security

Your API keys are encrypted with **Fernet (symmetric encryption)** and stored in `~/.gitpr/`. Never share your `secret.key` file.

---

[Installation Guide →](/instalacao) &nbsp;|&nbsp; [Usage Guide →](/uso) &nbsp;|&nbsp; [GitHub Repository →](https://github.com/natafiuza/gitpr)
