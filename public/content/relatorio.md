# **🚀 Project Status Report: GitPR CLI — v0.0.32 (2026-08-06)**

## **📌 Overview**

**GitPR** is an advanced CLI (Command Line Interface) tool for automating Git processes using Artificial Intelligence (Google Gemini / DeepSeek / Ollama). Its main goal is to act as a local intelligent assistant that performs Code Reviews, generates Pull Requests, semantic commit messages, audits technical debt, and injects best practices into the developer workflow (Shift Left).

**What's new in this version (v0.0.7):**
- **Expanded i18n Coverage (491 keys):** Complete synchronization of `__()` calls in `core.py`, `main.py`, and `linter_engine.py` with the `pt_br.json` translation file. `tests/sync_i18n.py` verification script to detect orphaned keys in any source file. Addition of 5 new translations for Smart Excludes strings, CLI banner with `--install`, and local telemetry.
- **Smart Excludes for Documentation:** Intelligent pathspec filter that detects and excludes documentation files (`.md`, `.rst`, `.txt`) from the diff, with visual notification of excluded file count (`📄 {count} documentation file(s) excluded`) and link to documentation.
- **Automatic Git Hook Sync:** Independent versioning system for hook scripts (`__scripts_version__` v0.0.1) with automatic verification and update when running `--installhooks`. Detects environment language and downloads the correct template version.
- **Metrics for Linter, Blame, and Git Hooks:** Expanded telemetry with `log_hook_event()` for hook events, `log_linter_metric()` for standalone linter runs, and `log_blame_metric()` for code archaeology.
- **i18n Cache with Language Indexing:** AI response cache system now includes the current language in the key, preventing collisions between responses generated in different languages.
- **Centralized Versioning in Updater:** GitPR version (`__version__`) and language dictionary version (`__lang_version__`) derived exclusively from `src/updater.py`, eliminating duplication with `pyproject.toml`.
- **Architecture Patterns Documentation:** Memory index with 14 documented patterns extracted from 36 task reports, covering cache, spinner, MCP, metrics, UI, versioning, and other subsystems.

- **Current version:** 0.0.32
- **Language dictionary version:** v0.0.10
- **Hook scripts version:** v0.0.1
- **Published on:** PyPI (`pip install gitpr-cli`) + GitHub Releases (standalone binary)
- **Website:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Repository:** [github.com/natanfiuza/gitpr](https://github.com/natanfiuza/gitpr)
- **License:** LGPL-2.1
- **Supported languages:** en_us, pt_br, pt_pt, es_es, fr_fr (5 languages)

---

## **🏗️ Architecture and Base Libraries**

* **Language:** Python >= 3.10
* **CLI Framework:** Click (for commands, flags, and terminal formatting).
* **UI/Terminal:** Textual — TUI (Text User Interface) for interactive chat, issue editing, help screen, and metrics dashboard.
* **Cryptography:** `cryptography.fernet` for local API key and GitHub token protection.
* **Configuration:** `python-dotenv`, `pyyaml` (for the static linter).
* **AI Providers:** Integration via official Google GenAI SDK (`gemini-2.5-flash`), OpenAI SDK (`DeepSeek`), and OpenAI SDK (local `Ollama`).
* **MCP:** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 (official Anthropic SDK for Model Context Protocol) — **Tool Annotations, Prompts with templates, and prompt:// resources**.
* **Testing:** Pytest + `unittest.mock` (12 test files, 131 scenarios).
* **Packaging:** PyInstaller (standalone binary) + setuptools/build (PyPI).
* **CI/CD:** GitHub Actions (`pr-review.yml`) + `action.yml` for pipeline execution.

---

## **🧩 Implemented Modules and File Architecture**

### **1. Core and Git Operations (`src/core.py`)**

* **Structured Generation:** Communicates with the LLM requesting strictly JSON output.
* **Map-Reduce (Giant Diffs):** When diffs exceed ~90k tokens, automatically splits into per-file batches (`split_diff_into_chunks`), processes each chunk (Map), and unifies summaries (Reduce) while maintaining architectural tone.
* **Token Estimation:** Lightweight heuristic `len() // 4` via `estimate_token_count()`.
* **Native Git Optimization:** Flags `-U1`, `-w`, `-M`, `-B` in `get_git_diff` and `get_git_full_diff` commands to reduce unnecessary context.
* **Pre-Save (`--pre-save`):** Hidden debug flag saving full payload (system instruction + prompt) in JSON before each AI call.
* **Smart Excludes 🆕:** Intelligent remote pathspec filter (`gitpr.smart-excludes.json`) — downloaded from GitHub and auto-updated with versioning (`SMART_EXCLUDES_VERSION`). **New feature:** documentation file exclusion with visual notification (`📄 {count} documentation file(s) excluded`) and `Learn more` link to documentation.
* **Metrics with Time Tracking:** Injection of `log_command_metric()` across all flows with duration passing in milliseconds (`duration_ms`) and lazy imports to prevent circular dependency.

### **2. CLI Interface and Setup (`src/main.py` and `src/config.py`)**

* **Initial Setup:** Detects first run, creates `~/.gitpr/` folder, and interactively prompts for API keys, preferences, and language, saving to `.env`.
* **Command Routing:** Manages all flags (`--commit`, `--review`, `--fullreview`, `--linter`, `--skill`, `--issue`, `--blame`, `--chat`, `--mcp`, `--install`, `--metrics`, `--export`, `--purge`, `--dashboard`, `--lang`, `--provider`, `--pre-save`).
* **Contextual Help:** `-h --flag` displays feature-specific documentation with direct (language-aware) GitHub link.
* **--lang:** Forces interface language for the current execution without persisting changes.
* **--provider:** Forces AI provider (`gemini`, `deepseek`, `ollama`) for the current execution.
* **--mcp:** Starts MCP server on stdio transport for editor integration — **10 annotated tools + 15 resources + 7 prompts**.
* **--install:** Guided 4-step wizard downloading skill templates, installing Git Hooks, configuring MCP in editors, and validating API keys.
* **--metrics:** Local telemetry system with repository scope: `--export` (saves in `./.gitpr/metrics/export/`), `--purge` (cleanup), `--dashboard` (interactive TUI with cache scanning).

### **3. Static Analysis / Linter Engine (`src/linter_engine.py`)**

* **Offline Linter:** Statically analyzes added lines (`+`) in git diff without using AI quota.
* **YAML Rules:** Reads local `.gitpr.linter.yml` file (created via `--skill`). Supports regex validation, comment skipping, and specific directory exclusion (using fnmatch).
* **Multilingual Template:** Linter templates available in 5 languages.
* **Linter Metrics 🆕:** Linter execution events recorded via `log_linter_metric()` with error and warning counts.

### **4. Security and Vault (`src/security.py`, `src/config.py`, `src/tui_issue.py`)**

* **Cryptography:** Generates master key `secret.key` in `~/.gitpr/` folder.
* **Token Protection:** `encrypt_data` and `decrypt_data` to secure AI API keys and GitHub PATs.
* **GitHub Token Validation:** `validate_github_token()` function performs a lightweight call (`GET /user`) to validate PAT.
* **Auto-Reauth Flow:** If token expires or is invalid during `gitpr -is`, app captures 401 HTTP response, prompts user for a new token, and relaunches TUI interface preserving draft content.

### **5. Auto-Updater (`src/updater.py`)**

* **Hot-Swap:** Checks GitHub Releases API for latest version. On mismatch, downloads compiled binary, renames current executable, and replaces without breaking running execution (with rollback capability).
* **Daily Cache:** Avoids repeated checks on the same day.
* **Connection Check:** Socket `8.8.8.8:53` before network operations.
* **Centralized Versioning 🆕:** `__version__` (0.0.32), `__lang_version__` (v0.0.10), `__scripts_version__` (v0.0.1), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION` — all derived exclusively from `updater.py`, single source of truth for versioning.

### **6. Interactive Chat Interface (`src/ui/chat_app.py`)**

* **Full TUI:** Built with Textual — message history, multi-line input, status bar with visible key bindings.
* **Per-Branch Memory (`src/chat_memory.py`):** Conversation history persisted per branch, allowing continuity between sessions.
* **Slash Commands:** `/explain`, `/tests`, `/optimize`, `/clear` — shortcuts for common pair programming actions.
* **Auto-Patching (F5):** Extracts AI-suggested code blocks and exports to patch file for easy application.
* **Diff Refresh (F2):** Reloads current `git diff` without restarting session.
* **Session Export (F6):** Saves full chat history for documentation.

### **7. Internationalization — i18n (`src/i18n.py`)**

* **Laravel-Inspired System:** `__()` function supporting named placeholders (`{count}`, `{file}`, etc.).
* **Automatic Detection:** Detects OS language on first run and saves in `GITPR_LANG`.
* **5 Languages:** en_us (default/fallback), pt_br, pt_pt, es_es, fr_fr.
* **Versioned Files:** `__lang_version__` (v0.0.10) controls language package updates (`langs/*.json`).
* **Expanded Coverage 🆕:** 491 translation keys in pt_BR (+44 since v0.0.6). Complete synchronization between `__()` calls in source code and translation dictionaries. `tests/sync_i18n.py` script for automatic detection of orphaned keys.
* **Cache with Language Indexing 🆕:** Cached AI responses now include the current language in MD5 keying, preventing collisions between different languages.

### **8. Animated Spinner (`src/spinner.py`)**

* **Braille + Thinking Words:** Background thread during AI calls displaying braille characters alongside "thinking" words.
* **Updated Delimiter:** Changed phrase separator to semicolon (`;`), preventing internal commas from incorrectly splitting phrases.
* **Adaptive Speed & Flickering:** Character discovery animation adapted for long phrases, using ANSI `\033[K` to prevent visual artifacts in terminal.
* **263 entries per language:** Synchronized across 5 languages in `templates/gitpr.thinking-words.{lang}.md` files.

### **9. AI Providers (`src/ai_providers.py`)**

* **3 Supported Providers:** Google Gemini (`gemini-2.5-flash`), DeepSeek (`deepseek-chat`), Ollama (local).
* **Duration Measurement:** Injection of `duration_ms` (high-precision timing via `time.perf_counter()`) in `meta_raw` and `_telemetry_meta`.
* **JSON Mode & Deterministic Parameters:** Structured outputs with `temperature=0.0` and `top_p=0.1`.

### **10. Intelligent Cache (`src/cache.py`)**

* **MD5 + Metadata:** Keying by MD5 hash of diff and prompt.
* **Language Indexing 🆕:** The `lang` field has been added to cache keying, enabling distinct responses for the same diff in different languages.
* **Telemetry & Duration:** Persistence of `duration_ms` field and `meta_raw` in cache files under `~/.gitpr/cache/prompts/`.
* **Dashboard Scanner:** `scan_cache_files_for_dashboard()` recursively reads all cache files to compute complete historical metrics.

### **11. Issue Engine and TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Context Engines:** Current Diff, Branch History (`-ht`), and Blame Archeology (`-b`).
* **Interactive TUI:** Draft editing, F2 (save local), F3 (publish to GitHub via REST API), and F1 (help).
* **401 Handling:** Re-authentication prompt without closing application or losing draft data.

### **12. Code Archaeologist (`src/blame_engine.py`)**

* **Git Blame + AI:** Tracks evolution and historical authorship of code snippets with commit classification (`ORIGIN` vs `REFACTORING`).
* **Blame Metrics 🆕:** Archaeology events recorded via `log_blame_metric()` with depth tracking and analyzed commit count.

### **13. MCP Server and Installer (`src/mcp_server.py`)**

* **10 Annotated MCP Tools:** Annotations (`readOnlyHint`, `destructiveHint`, `idempotentHint`) configured for IDEs such as Cursor, VS Code, and Claude Code.
* **15 Resources + 7 Templatized Prompts:** 35 template files in `templates/gitpr.prompt.*.md`.
* **Automated Installer:** Configuration for supported editors (VS Code, Cursor, Claude Code, Claude Desktop, Zed) with intelligent JSON merge.

### **14. TUI Metrics Dashboard (`src/ui/metrics_app.py`)**

* **Repository Scope (Repo-Scope):** Label `📁 Repository: owner/repo` and strict filtering of events and cache data per project.
* **Async Scanning with Overlay:** Background worker thread loading cache data while displaying Textual's `ProgressBar` widget.
* **Data Consolidation:** `load_cache_token_summary()` adds cache call tokens to dashboard totalizer.
* **Cache State Control:** Registration file in `./.gitpr/metrics/{repo}/processed_cache.json`.
* **F5 Column Fix:** One-time column initialization (`_setup_columns()`), preventing visual duplication on refreshes.
* **Local Export:** Saving CSV/JSON to `./.gitpr/metrics/export/`.

### **15. Metrics and Telemetry System (`src/metrics.py`)**

* **Repository Scope:** All metric events are indexed by `repo_name`, enabling isolation between projects.
* **New Events 🆕:** `log_hook_event()` for Git hooks (pre-commit, prepare-commit-msg), `log_linter_metric()` for standalone linter, `log_blame_metric()` for code archaeology.
* **Local Export:** `--metrics --export` generates CSV and JSON in `./.gitpr/metrics/export/` filtered by repository.
* **Cleanup:** `--metrics --purge` removes all local metric files with interactive confirmation.

### **16. Git Hook Sync 🆕**

* **Independent Versioning:** `__scripts_version__` (v0.0.1) in `updater.py` controls hook script versioning separately from language dictionaries.
* **Automatic Detection:** When running `--installhooks`, the system compares local version (stored in `.env`) with the latest version and auto-updates if needed.
* **Language-Aware:** Detects configured language and downloads the corresponding hook templates.

---

## **📊 Testing and Quality**

| Test File | Scenarios | Focus |
|-----------|-----------|-------|
| `tests/test_core.py` | 25+ | Main flows, git diff, PR generation, timing |
| `tests/test_chat_backend.py` | 30+ | Chat memory, persistence, slash commands |
| `tests/test_skill_command.py` | 10+ | Download and validation of skill templates |
| `tests/test_pre_save.py` | 10+ | Flag --pre-save and JSON payload |
| `tests/test_smart_excludes.py` | 14+ | Intelligent pathspec filter |
| `tests/test_thinking_words.py` | 10+ | Loading and parsing with `;` separator |
| `tests/test_mcp_prompts.py` | 11 | MCP prompt templates and language fallback |
| `tests/test_mcp_server.py` | 33 | MCP tools, resources, annotations, and patching |
| `tests/test_metrics.py` | 36+ | Collection, local export, repo scope, cache token summary, duration_ms |
| `tests/test_install_wizard.py` | 5+ | Interactive installation wizard |
| `tests/test_blame_metrics.py` 🆕 | 10+ | Blame metrics: depth, commits, duration |
| `tests/test_linter_metrics.py` 🆕 | 8+ | Linter metrics: errors, warnings, duration |
| `tests/sync_i18n.py` 🆕 | — | i18n coverage verification script (orphaned keys) |

**Total:** 131 automated test scenarios passing with 100% success.

---

## **🌐 Internationalization and Documentation**

* **Expanded i18n Coverage 🆕:** 491 translation keys in pt_BR (up from 447 in v0.0.6, +44 new). Complete synchronization validated for `core.py`, `main.py`, and `linter_engine.py`.
* **Sync Script 🆕:** `tests/sync_i18n.py` — reusable script to detect `__()` keys in any source file that lack translations in the language dictionary.
* **New Metrics Tests 🆕:** `test_blame_metrics.py` (140 lines) and `test_linter_metrics.py` (116 lines) covering telemetry for the new modules.
* **5-Language Documentation:** 23 topics in `docs/` translated to EN, PT-BR, PT-PT, ES, FR.
* **Memory Index:** `.claude/memory/MEMORY.md` with 14 architecture patterns extracted from 36 reports.
* **Task Reports:** `docs/claude-code/reports/` and `docs/reports/`.

---

## **🔄 Distribution Pipeline**

1. **PyPI:** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **GitHub Releases:** PyInstaller → standalone `.exe` → automated upload
3. **GitHub Actions:** Workflow `pr-review.yml` + `action.yml`
4. **MCP Server:** Entry point `gitpr-mcp` via `pyproject.toml`

---

## **📈 Evolution since Previous Report (v0.0.6)**

| Area | v0.0.6 (previous) | v0.0.7 (current) |
|------|-------------------|------------------|
| **GitPR Version** | 0.0.31 | **0.0.32** |
| **Language Version** | v0.0.8 | **v0.0.10** |
| **Hook Scripts Version** | — | **v0.0.1** |
| **AI Providers** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama |
| **Languages** | 5 (en, pt_br, pt_pt, es_es, fr_fr) | 5 (en, pt_br, pt_pt, es_es, fr_fr) |
| **pt_BR Translations** | 447 keys | **491 keys (+44)** |
| **TUI Dashboard** | Repo-scoped, unlimited cache + ProgressBar + F5 fix | Repo-scoped, unlimited cache + ProgressBar + F5 fix |
| **Smart Excludes** | Remote pathspec filter | **+ Documentation exclusion with visual notification** |
| **Metrics & Telemetry** | Wall-clock duration + Local export | **+ Linter, Blame, and Git Hook metrics** |
| **Git Hooks** | Manual installation (`--installhooks`) | **+ Automatic sync with versioning** |
| **i18n Cache** | Keying by diff MD5 | **+ Language indexing** |
| **Versioning** | `__version__` duplicated (updater + pyproject) | **Single source in updater.py** |
| **Test Suite** | 114 scenarios (10 files) | **131 scenarios (12 files + sync_i18n)** |
| **Patterns Documentation** | CLAUDE.md + GEMINI.md | **+ Memory Index with 14 patterns** |

---

## **🚧 Next Steps**

* **i18n Sync for Remaining Languages:** Expand translations for es_es, fr_fr, and pt_pt with the same coverage as pt_br (491 keys).
* **End-to-End Integration Tests for MCP:** Validation of tool calls and prompts via simulated stdio client.
* **Anthropic Claude Provider:** Direct support for Claude API (`claude-sonnet-5`).
* **ASCII/Textual Charts in Dashboard:** Add timing histograms and token trend graphs to metrics TUI.
* **GitHub Actions Release Pipeline:** Complete automation for PyInstaller builds and asset uploads to GitHub Releases.

---

**Report generated on:** 2026-08-06  
**Branch:** `develop_natan`  
**Author:** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
