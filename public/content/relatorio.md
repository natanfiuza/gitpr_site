# **🚀 Project Status Report: GitPR CLI — v0.0.31 (2026-08-03)**

## **📌 Overview**

**GitPR** is an advanced CLI (Command Line Interface) tool for automating Git processes using Artificial Intelligence (Google Gemini / DeepSeek / Ollama). Its main goal is to act as a local intelligent assistant that performs Code Reviews, generates Pull Requests, semantic commit messages, audits technical debt, and injects best practices into the developer workflow (Shift Left).

**What's new in this version (v0.0.6):**
- **Overhauled TUI Metrics Dashboard:** Isolated repository scope (`repo_filter`), unlimited asynchronous scanning of cache files (`~/.gitpr/cache/prompts/`), visual overlay with `ProgressBar`, unified token totalizer per project, processed cache file tracking (`./.gitpr/metrics/{repo}/processed_cache.json`), and fix for duplicate columns bug on F5 (Refresh).
- **AI Call Duration Tracking (Wall-Clock Timing):** Injection of `duration_ms` in milliseconds via `time.perf_counter()` across all LLM responses, passed through cache and displayed on the metrics dashboard.
- **Per-Project Local Export:** `gitpr --metrics --export` now generates CSV and JSON reports in the local project folder (`./.gitpr/metrics/export/`) filtered by the active repository.
- **Automatic GitHub Token Revalidation (Auto-Reauth on 401):** PAT validation function (`GET /user`), pre-validation before issues TUI (`gitpr -is`), and graceful HTTP 401 recovery without draft loss.
- **Spinner and Thinking Words Adjustments:** Changed phrase delimiter from comma to semicolon (`;`), enabling complex phrases with internal commas in `templates/gitpr.thinking-words.*.md` without breaking parsing.
- **Quick Start in READMEs:** Installation documentation via `pip install gitpr-cli` and repository setup via `gitpr --install` across all 5 language READMEs.
- **Project Development Guide `GEMINI.md`:** Complete architectural guide, code conventions, command pipeline, and mandatory reports standard in `docs/gemini/reports/`.

- **Current version:** 0.0.31
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
* **Testing:** Pytest + `unittest.mock` (10 test files, 114 scenarios).
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
* **Smart Excludes:** Intelligent remote pathspec filter (`gitpr.smart-excludes.json`) — downloaded from GitHub and auto-updated with versioning (`SMART_EXCLUDES_VERSION`), excluding irrelevant files (lock files, build artifacts, binary assets) to save tokens.
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

### **4. Security and Vault (`src/security.py`, `src/config.py`, `src/tui_issue.py`)**

* **Cryptography:** Generates master key `secret.key` in `~/.gitpr/` folder.
* **Token Protection:** `encrypt_data` and `decrypt_data` to secure AI API keys and GitHub PATs.
* **GitHub Token Validation 🆕:** `validate_github_token()` function performs a lightweight call (`GET /user`) to validate PAT.
* **Auto-Reauth Flow 🆕:** If token expires or is invalid during `gitpr -is`, app captures 401 HTTP response, prompts user for a new token, and relaunches TUI interface preserving draft content.

### **5. Auto-Updater (`src/updater.py`)**

* **Hot-Swap:** Checks GitHub Releases API for latest version. On mismatch, downloads compiled binary, renames current executable, and replaces without breaking running execution (with rollback capability).
* **Daily Cache:** Avoids repeated checks on the same day.
* **Connection Check:** Socket `8.8.8.8:53` before network operations.
* **Asset Versioning:** `__lang_version__` (v0.0.8), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION` controlling template and translation updates.

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
* **Versioned Files:** `__lang_version__` (v0.0.8) controls language package updates (`langs/*.json`).
* **Complete Coverage:** CLI messages, Click help, linter alerts, Git Hooks, spinner, TUI chat, MCP, metrics, and TUI Dashboard translated.

### **8. Animated Spinner (`src/spinner.py`)**

* **Braille + Thinking Words:** Background thread during AI calls displaying braille characters alongside "thinking" words.
* **Updated Delimiter 🆕:** Changed phrase separator to semicolon (`;`), preventing internal commas from incorrectly splitting phrases.
* **Adaptive Speed & Flickering:** Character discovery animation adapted for long phrases, using ANSI `\033[K` to prevent visual artifacts in terminal.
* **263 entries per language:** Synchronized across 5 languages in `templates/gitpr.thinking-words.{lang}.md` files.

### **9. AI Providers (`src/ai_providers.py`)**

* **3 Supported Providers:** Google Gemini (`gemini-2.5-flash`), DeepSeek (`deepseek-chat`), Ollama (local).
* **Duration Measurement 🆕:** Injection of `duration_ms` (high-precision timing via `time.perf_counter()`) in `meta_raw` and `_telemetry_meta`.
* **JSON Mode & Deterministic Parameters:** Structured outputs with `temperature=0.0` and `top_p=0.1`.

### **10. Intelligent Cache (`src/cache.py`)**

* **MD5 + Metadata:** Keying by MD5 hash of diff and prompt.
* **Telemetry & Duration 🆕:** Persistence of `duration_ms` field and `meta_raw` in cache files under `~/.gitpr/cache/prompts/`.
* **Dashboard Scanner 🆕:** `scan_cache_files_for_dashboard()` recursively reads all cache files to compute complete historical metrics.

### **11. Issue Engine and TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Context Engines:** Current Diff, Branch History (`-ht`), and Blame Archeology (`-b`).
* **Interactive TUI:** Draft editing, F2 (save local), F3 (publish to GitHub via REST API), and F1 (help).
* **401 Handling 🆕:** Re-authentication prompt without closing application or losing draft data.

### **12. Code Archaeologist (`src/blame_engine.py`)**

* **Git Blame + AI:** Tracks evolution and historical authorship of code snippets with commit classification (`ORIGIN` vs `REFACTORING`).

### **13. MCP Server and Installer (`src/mcp_server.py`)**

* **10 Annotated MCP Tools:** Annotations (`readOnlyHint`, `destructiveHint`, `idempotentHint`) configured for IDEs such as Cursor, VS Code, and Claude Code.
* **15 Resources + 7 Templatized Prompts:** 35 template files in `templates/gitpr.prompt.*.md`.
* **Automated Installer:** Configuration for supported editors (VS Code, Cursor, Claude Code, Claude Desktop, Zed) with intelligent JSON merge.

### **14. Overhauled TUI Metrics Dashboard (`src/ui/metrics_app.py`)** 🆕

* **Repository Scope (Repo-Scope):** Label `📁 Repository: owner/repo` and strict filtering of events and cache data per project.
* **Async Scanning with Overlay:** Background worker thread loading cache data while displaying Textual's `ProgressBar` widget.
* **Data Consolidation:** `load_cache_token_summary()` adds cache call tokens to dashboard totalizer.
* **Cache State Control:** Registration file in `./.gitpr/metrics/{repo}/processed_cache.json`.
* **F5 Column Fix:** One-time column initialization (`_setup_columns()`), preventing visual duplication on refreshes.
* **Local Export:** Saving CSV/JSON to `./.gitpr/metrics/export/`.

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

**Total:** 114 automated test scenarios passing with 100% success.

---

## **🌐 Internationalization and Documentation**

* **Quick Start Section in READMEs 🆕:** Updated `README.md`, `README.pt_br.md`, `README.pt_pt.md`, `README.es_es.md`, and `README.fr_fr.md` with `pip install gitpr-cli` and `gitpr --install` instructions.
* **New `GEMINI.md` Guide 🆕:** Development guide covering code standards, commands, project structure, and mandatory reports.
* **447 translation keys** per language (2,235 total translations).
* **5-Language Documentation:** 23 topics in `docs/` translated to EN, PT-BR, PT-PT, ES, FR.
* **Task Reports:** `docs/claude-code/reports/` and `docs/gemini/reports/`.

---

## **🔄 Distribution Pipeline**

1. **PyPI:** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **GitHub Releases:** PyInstaller → standalone `.exe` → automated upload
3. **GitHub Actions:** Workflow `pr-review.yml` + `action.yml`
4. **MCP Server:** Entry point `gitpr-mcp` via `pyproject.toml`

---

## **📈 Evolution since Previous Report (v0.0.5)**

| Area | v0.0.5 (previous) | v0.0.6 (current) |
|------|-------------------|------------------|
| **AI Providers** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama |
| **Languages** | 5 (en, pt_br, pt_pt, es_es, fr_fr) | 5 (en, pt_br, pt_pt, es_es, fr_fr) |
| **TUI Dashboard** | Global, limited to 100 events | **Repo-scoped, unlimited cache scanning + ProgressBar + F5 fix** |
| **Metrics & Duration** | Simple tokens and counters | **Wall-clock duration (`duration_ms`) + Local export (`./.gitpr/metrics/export/`)** |
| **GitHub PAT Auth** | Secure storage without pre-validation | **Pre-validation via `GET /user` + Graceful Auto-Reauth on HTTP 401** |
| **Thinking Words** | Comma separator `,` | **Semicolon separator `;` (supports complex phrases) synced in 5 languages** |
| **README Documentation** | Binary download focus | **Quick Start with `pip install gitpr-cli` and `gitpr --install` in 5 languages** |
| **Development Guides**| CLAUDE.md | **CLAUDE.md + GEMINI.md** |
| **Test Suite** | 100+ scenarios | **114 test scenarios (100% passing)** |
| **PyPI Version** | 0.0.30 | **0.0.31** |

---

## **🚧 Next Steps**

* **End-to-End Integration Tests for MCP:** Validation of tool calls and prompts via simulated stdio client.
* **Anthropic Claude Provider:** Direct support for Claude API (`claude-3-5-sonnet`).
* **ASCII/Textual Charts in Dashboard:** Add timing histograms and token trend graphs to metrics TUI.
* **GitHub Actions Release Pipeline:** Complete automation for PyInstaller builds and asset uploads to GitHub Releases.

---

**Report generated on:** 2026-08-03  
**Branch:** `develop_natan`  
**Author:** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))  
