# GitPR CLI — Project Status Report

---

## Overview

**GitPR** is a CLI tool for Git workflow automation powered by AI (Google Gemini / DeepSeek). It acts as an intelligent local assistant that performs Code Reviews, generates Pull Request descriptions, creates semantic commit messages, audits technical debt, and injects best practices into the developer workflow (**Shift Left** approach).

---

## Architecture & Core Libraries

- **Language:** Python 3.x
- **CLI Framework:** Click (commands, flags, terminal formatting)
- **UI/Terminal:** Interactive TUI (Textual) for chat and issue editing
- **Cryptography:** Fernet symmetric encryption for local API key protection
- **Configuration:** dotenv, PyYAML (for the static linter)
- **AI Providers:** Google GenAI SDK (gemini-2.5-flash) + DeepSeek + Ollama

---

## Implemented Modules

### 1. Core & Git Operations (`src/core.py`)
Structured LLM communication requesting strictly JSON responses (`commit_message` and `pr_description`). Native Git optimization with `-U1`, `-w`, `-M`, `-B` flags for minimal, focused diffs.

### 2. CLI Interface & Setup (`src/main.py`, `src/config.py`)
First-run detection, interactive API key setup, `.env` configuration in `~/.gitpr/`. Command routing for all flags (`--commit`, `--review`, `--fullreview`, `--linter`, `--skill`, `--issue`, `--blame`, `--chat`).

### 3. Static Analysis Engine (`src/linter_engine.py`)
Offline linter analyzing only added lines (`+`) from `git diff`. Reads `.gitpr.linter.yml` with regex rules, comment ignoring, and path exclusion via `fnmatch`.

### 4. Security Vault (`src/security.py`)
Fernet key generation (`secret.key`), `encrypt_data` and `decrypt_data` functions. API keys never stored in plain text.

### 5. Auto-Updater (`src/updater.py`)
Hot-swap binary updates from GitHub Releases API with SHA-256 verification and rollback capability.

### 6. Chat & Auto-Patch (`src/ui/chat_app.py`)
Interactive TUI with per-branch message memory. F5 extracts code blocks into patch files. F6 exports sessions to Markdown. Slash commands for common actions.

### 7. Internationalization (`src/i18n.py`)
Laravel-inspired `__()` helper with named placeholders. JSON translation packs in `~/.gitpr/langs/`. English fallback for missing keys. Supports `en`, `pt_br`, `pt_pt`, `fr`, `es`.

### 8. Map-Reduce Architecture
Two-tier optimization for large diffs:
- **Tier 1:** Native Git flags (`-U1`, `-w`, `-M`, `-B`) for minimal context
- **Tier 2:** Token estimation (`len() // 4`), safe splitting at `diff --git` boundaries, batched AI calls with `time.sleep(1)` rate limiting, and final Reduce step concatenating summaries

---

## Key Metrics

- **AI Providers:** 3 (Gemini, DeepSeek, Ollama)
- **Supported Languages:** 5 (EN, PT-BR, PT-PT, FR, ES)
- **CLI Commands:** 12+ flags
- **Linter:** YAML-configurable, zero AI cost
- **Cache:** MD5-based, automatic deduplication
- **Security:** Fernet symmetric encryption (AES-128-CBC)

---

## Documentation

Full documentation is available at [github.com/natafiuza/gitpr](https://github.com/natafiuza/gitpr) and on this website.

---

[← Contributing](/contribuicao) &nbsp;|&nbsp; [Home →](/index)
