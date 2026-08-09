# **🚀 Project Status Report: GitPR CLI — v0.0.33 (2026-08-09)**

## **📌 Overview**

**GitPR** is an advanced CLI (Command Line Interface) tool for automating Git processes using Artificial Intelligence (Google Gemini / DeepSeek / Ollama). Its main goal is to act as a local intelligent assistant that performs Code Reviews, generates Pull Requests, semantic commit messages, audits technical debt, and injects best practices into the developer workflow (Shift Left).

**What's New in This Version (v0.0.8):**

- **PR Publisher TUI (`gitpr` default):** Interactive terminal interface to review, edit, and publish Pull Requests directly on GitHub via REST API. Includes editing of title, body, and base branch with F1 (Help), F2 (Save locally), F3 (Publish), and Esc (Exit) bindings. Complete flow with 6 modal screens for commit, staging, and progress.
- **Smart Auto-Commit Flow:** When using `--no-edit` or publishing with F3 with uncommitted changes, GitPR runs the static linter, generates an AI commit message (Conventional Commits), confirms with the user, and runs `git commit` before publishing the PR.
- **Unstaged Files Management:** At startup, GitPR checks for unstaged files and offers a TUI modal (`StageFilesApp`) to select, skip, or cancel before PR generation.
- **Existing PR Handling:** When a PR already exists for the current branch, the TUI offers pushing to the existing PR (updating the body via PATCH) or creating a new one.
- **Merge Flow:** After PR creation or update, GitPR can optionally merge it. Controlled by the `GITPR_AUTO_MERGE` environment variable.
- **Auto-Upstream on Push:** When `git push` fails due to a missing upstream, GitPR automatically retries with `--set-upstream origin <branch>`.
- **"Nothing to commit" Detection:** Commit failures due to the absence of staged changes are treated as success — the flow continues to PR publication.
- **Output Centralization:** All generated files now use `.gitpr/reports/` organized by type (`pr_desc/`, `review/`, `full_review/`, `file_review/`, `blame/`, `issue/`). Custom paths in `.env` are respected for compatibility.
- **6 New Environment Variables:** `GITPR_AUTO_COMMIT`, `GITPR_SKIP_LINT`, `GITPR_AUTO_STAGE`, `GITPR_SKIP_UNSTAGED_CHECK`, `GITPR_SHOW_LOGS`, `GITPR_AUTO_MERGE` — granular control of the publication flow.
- **GitHub API Module (`src/github_api.py`):** Shared functions for `create_pull_request()`, `update_pull_request()`, and `merge_pull_request()` via REST API.
- **Multilingual Technical Documentation:** `docs/pull-request-publication.md` in 5 languages (EN, PT-BR, PT-PT, ES, FR) with complete coverage of the PR flow.
- **CHANGELOG.md:** Complete version history from v0.0.1 to v0.0.33 in the Keep a Changelog format, populated from the status reports in `docs/reports/`.

- **Current version:** 0.0.33
- **Language dictionaries version:** v0.0.11
- **Hook scripts version:** v0.0.1
- **Distribution:** PyPI (`pip install gitpr-cli`) + GitHub Releases (standalone binary)
- **Website:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Repository:** [github.com/natanfiuza/gitpr](https://github.com/natanfiuza/gitpr)
- **License:** LGPL-2.1
- **Supported languages:** en_us, pt_br, pt_pt, es_es, fr_fr (5 languages)

---

## **🏗️ Base Architecture and Libraries**

* **Language:** Python >= 3.10
* **CLI Framework:** Click (for commands, flags, and terminal formatting).
* **UI/Terminal:** Textual — TUI (Text User Interface) for interactive chat, issue editing, help screen, metrics dashboard, and **PR Publisher** 🆕.
* **Cryptography:** `cryptography.fernet` for local protection of API keys and GitHub tokens.
* **Configuration:** `python-dotenv`, `pyyaml` (for the static linter).
* **AI Providers:** Integration via the official Google GenAI SDK (`gemini-2.5-flash`), OpenAI SDK (`DeepSeek`), and OpenAI SDK (local `Ollama`).
* **GitHub API:** `requests` (REST API via PAT) — **expanded usage with new `github_api.py` module** 🆕.
* **MCP:** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 (official Anthropic SDK for Model Context Protocol) — Tool Annotations, templated Prompts, and prompt:// resources.
* **Tests:** Pytest + `unittest.mock` (12 test files, 131 scenarios).
* **Packaging:** PyInstaller (standalone binary) + setuptools/build (PyPI).
* **CI/CD:** GitHub Actions (`pr-review.yml`) + `action.yml` for execution in pipelines.

---

## **🧩 Implemented Modules and File Architecture**

### **1. Core and Git Operations (`src/core.py`)**

* **Structured Generation:** Communicates with the LLM requesting strictly JSON output.
* **Map-Reduce (Giant Diffs):** When the diff exceeds ~90k tokens, it automatically splits it into per-file batches (`split_diff_into_chunks`), processes each part (Map), and unifies the summaries (Reduce) while keeping the architecture's voice.
* **Token Estimation:** Lightweight heuristic `len() // 4` via `estimate_token_count()`.
* **Native Git Optimization:** `-U1`, `-w`, `-M`, `-B` flags on the `get_git_diff` and `get_git_full_diff` commands to reduce useless context.
* **Pre-Save (`--pre-save`):** Hidden debug flag that saves the full payload (system instruction + prompt) as JSON before each AI call.
* **Smart Excludes:** Remote smart pathspec filter (`gitpr.smart-excludes.json`) — downloaded from GitHub and auto-updated with versioning (`SMART_EXCLUDES_VERSION`), excluding irrelevant files (lock files, build artifacts, binary assets, and documentation) to reduce tokens.
* **Metrics with Time Tracking:** Injection of `log_command_metric()` into all flows passing duration in milliseconds (`duration_ms`), with lazy imports to avoid circular imports.
* **Centralized Output Resolution 🆕:** New `resolve_output_path()` function that centralizes output directory logic — defaulting to `.gitpr/reports/{type}/` with fallback to custom paths from `.env`.

### **2. CLI Interface and Setup (`src/main.py` and `src/config.py`)**

* **Initial Setup:** Detects first run, creates the `~/.gitpr/` folder, and interactively prompts for API keys, preferences, and language, saving to a `.env`.
* **Command Routing:** Manages all flags (`--commit`, `--review`, `--fullreview`, `--linter`, `--skill`, `--issue`, `--blame`, `--chat`, `--mcp`, `--install`, `--metrics`, `--export`, `--purge`, `--dashboard`, `--publish`, `--no-publish`, `--no-edit`, `--base`, `--lang`, `--provider`, `--pre-save`).
* **Changed Default Behavior 🆕:** Running `gitpr` without flags now opens the PR Publisher TUI (previously: generated a file and exited).
* **New Flags 🆕:**
  * `--publish`: Opens the interactive TUI to review, edit, and publish the PR (default behavior).
  * `--no-publish`: Generates the PR description and saves it locally without opening the interactive editor.
  * `--no-edit`: Skips the TUI entirely — performs auto-commit (with linter validation), auto-push, and publishes directly to GitHub. Ideal for CI/CD.
  * `--base <branch>`: Overrides the Pull Request target branch.
* **New Environment Variables 🆕:** `GITPR_AUTO_COMMIT` (skip commit confirmation), `GITPR_SKIP_LINT` (skip linter validation), `GITPR_AUTO_STAGE` (automatic staging of files), `GITPR_SKIP_UNSTAGED_CHECK` (skip unstaged check), `GITPR_SHOW_LOGS` (control progress logs), `GITPR_AUTO_MERGE` (auto-merge after publication).
* **Contextual Help:** `-h --flag` displays feature-specific documentation with a direct (language-aware) link to GitHub.
* **--lang:** Forces the interface language for the current run without persisting the change.
* **--provider:** Forces the AI provider (`gemini`, `deepseek`, `ollama`) for the current run.
* **--mcp:** Starts the MCP server on stdio transport for editor integration — **10 annotated tools + 15 resources + 7 prompts**.
* **--install:** 4-step guided wizard that downloads skill templates, installs Git Hooks, configures MCP in editors, and validates API keys.
* **--metrics:** Local telemetry system scoped per repository: `--export` (saves to `./.gitpr/metrics/export/`), `--purge` (cleanup), `--dashboard` (interactive TUI with cache scanning).

### **3. PR Publisher TUI (`src/ui/pr_publish_app.py` and `src/ui/pr_publish_help.py`)** 🆕

* **Complete Interactive Interface:** TUI built with Textual to review, edit, and publish Pull Requests directly in the terminal.
* **6 Modal Screens:**
  * `CommitConfirmScreen`: Confirmation before the automatic commit.
  * `FileStageScreen`: Interactive file selection for staging.
  * `CommitProgressScreen`: Progress bar during commit and push with real-time logs.
  * `CommitMessageScreen`: Display and confirmation of the AI-generated message.
  * `LinterErrorScreen`: Display of linter errors with the option to abort or continue.
  * `ErrorScreen`: Display of general errors with scroll, capped at `max-height: 80%`.
* **Bindings:** F1 (Help — modal with shortcuts and instructions), F2 (Save local .md), F3 (Publish via GitHub API), Esc (Exit).
* **Auto-Commit Flow:** When there are uncommitted changes and the user uses `--no-edit` or F3, GitPR automatically:
  1. Runs the static linter (unless `GITPR_SKIP_LINT=true`)
  2. Generates an AI commit message (Conventional Commits)
  3. Confirms with the user (unless `GITPR_AUTO_COMMIT=true`)
  4. Runs `git commit`
  5. Continues to push and PR publication
* **Unstaged Files Check:** At startup, checks `git status --porcelain` and offers the `StageFilesApp` modal to select, skip, or cancel.
* **Existing PR Handling:** Detects open PRs for the current branch via GitHub API and offers pushing to the existing PR (updating via PATCH) or creating a new one.
* **Auto-Upstream:** Detects `git push` failure due to a missing upstream and automatically retries with `--set-upstream origin <branch>`.
* **"Nothing to commit" Detection:** Treats `git commit` with no changes as success — the flow continues without error.
* **Merge Flow:** After PR creation/update, offers a merge option. Controlled by `GITPR_AUTO_MERGE`.
* **Stdout Fix:** `_with_real_stdout()` wrapper to avoid `OSError: [Errno 9] Bad file descriptor` when the Textual TUI calls `click.secho()`.

### **4. GitHub API Module (`src/github_api.py`)** 🆕

* **Shared Functions:** `create_pull_request()`, `update_pull_request()`, `merge_pull_request()` — encapsulating REST calls to the GitHub v3 API.
* **PAT Authentication:** Personal access token validated with `GET /user` before operations.
* **Reuse:** Functions used by both the PR TUI and the issues TUI, eliminating duplication.

### **5. Static Analysis Engine / Linter (`src/linter_engine.py`)**

* **Offline Linter:** Statically analyzes the added (`+`) lines in the git diff without spending AI quota.
* **YAML Rules:** Reads the local `.gitpr.linter.yml` file (created via `--skill`). Supports validation regex, ignoring comments, and ignoring specific directories (using fnmatch).
* **Multilingual template:** Linter templates available in 5 languages.
* **Auto-Commit Integration 🆕:** The linter runs automatically before the commit in the PR publication flow.

### **6. Security and Authentication (`src/security.py`, `src/config.py`, `src/tui_issue.py`)**

* **Cryptography:** Generates a master key `secret.key` in the `~/.gitpr/` folder.
* **Token Protection:** `encrypt_data` and `decrypt_data` to protect AI API keys and GitHub PAT.
* **GitHub Token Validation:** The `validate_github_token()` function makes a lightweight call (`GET /user`) to validate the PAT.
* **Auto-Reauth Flow:** If the token expires or becomes invalid during `gitpr -is`, the app catches the HTTP 401 response, asks the user for a new token, and relaunches the TUI while preserving the draft.

### **7. Auto-Updater (`src/updater.py`)**

* **Hot-Swap:** Checks the latest version via the GitHub Releases API. If there is a difference, downloads the compiled binary, renames the current executable, and replaces it without breaking the running process (with rollback capability).
* **Daily cache:** Avoids repeated checks on the same day.
* **Connection check:** Socket `8.8.8.8:53` before any network operation.
* **Centralized Versioning:** `__version__` (0.0.33), `__lang_version__` (v0.0.11), `__scripts_version__` (v0.0.1), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION` — all derived exclusively from `updater.py`.

### **8. Interactive Chat Interface (`src/ui/chat_app.py`)**

* **Complete TUI:** Built with Textual — message history, multi-line input, status bar with visible bindings.
* **Per-Branch Memory (`src/chat_memory.py`):** Conversation history persisted per branch, allowing continuity between sessions.
* **Slash Commands:** `/explain`, `/tests`, `/optimize`, `/clear` — shortcuts for common pair programming actions.
* **Auto-Patching (F5):** Extracts code blocks suggested by the AI and exports them to a patch file for easy application.
* **Diff Refresh (F2):** Reloads the current `git diff` without restarting the session.
* **Session Export (F6):** Saves the full chat history for documentation.

### **9. Internationalization — i18n (`src/i18n.py`)**

* **Laravel-Inspired System:** `__()` function with support for named placeholders (`{count}`, `{file}`, etc.).
* **Automatic Detection:** Detects the OS language on first run and saves it in `GITPR_LANG`.
* **5 Languages:** en_us (default/fallback), pt_br, pt_pt, es_es, fr_fr.
* **Versioned Files:** `__lang_version__` (v0.0.11) controls updating of language packs (`langs/*.json`).
* **Expanded Coverage 🆕:** ~623 translation keys in pt_BR (+132 since v0.0.32). New strings for PR Publisher TUI, modal screens, commit flow, and PR publication documentation.
* **Cache with Language Indexing:** Cached AI responses include the current language in the MD5 keying.
* **Sync Script:** `tests/sync_i18n.py` for automatic detection of orphan keys.

### **10. Animated Spinner (`src/spinner.py`)**

* **Braille + Thinking Words:** Background thread during AI calls displaying braille characters with "thinking" words.
* **Delimiter:** Sentence separator using semicolon (`;`), compatible with complex sentences containing commas.
* **Adaptive Speed & Flickering:** Character-reveal animation adapted for long sentences, using ANSI `\033[K` to avoid visual artifacts in the terminal.
* **263 entries per language:** Synchronized across the 5 languages in the `templates/gitpr.thinking-words.{lang}.md` files.

### **11. AI Providers (`src/ai_providers.py`)**

* **3 Supported Providers:** Google Gemini (`gemini-2.5-flash`), DeepSeek (`deepseek-chat`), Ollama (local).
* **Duration Measurement:** Injection of `duration_ms` (high-precision timing via `time.perf_counter()`) into `meta_raw` and `_telemetry_meta`.
* **JSON Mode & Deterministic Parameters:** Structured outputs with `temperature=0.0` and `top_p=0.1`.

### **12. Smart Cache (`src/cache.py`)**

* **MD5 + Metadata:** Keying by MD5 hash of the diff and prompt.
* **Language Indexing:** The `lang` field was added to the cache keying, allowing distinct responses for the same diff in different languages.
* **Telemetry and Duration:** Persistence of the `duration_ms` and `meta_raw` fields in cache files under `~/.gitpr/cache/prompts/`.
* **Dashboard Reading:** `scan_cache_files_for_dashboard()` reads all cache files recursively to compute complete historical metrics.

### **13. Issues Engine and TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Context Engines:** Current diff, Branch history (`-ht`), and Blame archaeology (`-b`).
* **Interactive TUI:** Draft editing, F2 shortcut (save locally), F3 (publish on GitHub via REST API), and F1 (help).
* **401 Handling:** Reauthentication signaling without closing the app and losing content.

### **14. Code Archaeologist (`src/blame_engine.py`)**

* **Git Blame + AI:** Tracks the historical evolution and authorship of code snippets with commit classification (`ORIGIN` vs `REFACTORING`).
* **Blame Metrics:** Archaeology events recorded via `log_blame_metric()` with tracking of depth and number of analyzed commits.

### **15. MCP Server and Installer (`src/mcp_server.py`)**

* **10 Annotated MCP Tools:** Annotations (`readOnlyHint`, `destructiveHint`, `idempotentHint`) configured for IDEs such as Cursor, VS Code, and Claude Code.
* **15 Resources + 7 Templated Prompts:** 35 template files in `templates/gitpr.prompt.*.md`.
* **Automatic Installer:** Configuration of supported editors (VS Code, Cursor, Claude Code, Claude Desktop, Zed) with smart JSON merge.

### **16. Metrics Dashboard TUI (`src/ui/metrics_app.py`)**

* **Per-Repository Scope (Repo-Scope):** `📁 Repository: owner/repo` label and strict filtering of events and cache data per project.
* **Async Scanning with Overlay:** Background worker thread that loads cache data while displaying the Textual `ProgressBar` widget.
* **Data Consolidation:** `load_cache_token_summary()` adds cache call tokens to the dashboard totalizer.
* **Cache State Control:** Registry file at `./.gitpr/metrics/{repo}/processed_cache.json`.
* **F5 Column Fix:** One-time column initialization (`_setup_columns()`), preventing visual duplication on updates.
* **Local Export:** CSV/JSON saving to `./.gitpr/metrics/export/`.

### **17. Metrics and Telemetry System (`src/metrics.py`)**

* **Per-Repository Scope:** All metric events are indexed by `repo_name`, allowing isolation between projects.
* **Hook Events:** `log_hook_event()` for Git hooks (pre-commit, prepare-commit-msg, post-checkout, pre-push, post-merge).
* **Linter and Blame Events:** `log_linter_metric()` for standalone linter, `log_blame_metric()` for code archaeology.
* **Local Export:** `--metrics --export` generates CSV and JSON in `./.gitpr/metrics/export/` with repository filtering.
* **Cleanup:** `--metrics --purge` removes all local metrics files with interactive confirmation.

### **18. Git Hooks Synchronization**

* **Independent Versioning:** `__scripts_version__` (v0.0.1) in `updater.py` controls the version of the hook scripts separately from the language dictionaries.
* **Automatic Detection:** When running `--installhooks`, the system compares the local version (stored in `.env`) with the latest version and updates automatically if needed.
* **Language-Aware:** Detects the configured language and downloads the corresponding hook templates.

---

## **📊 Tests and Quality**

| Test File | Scenarios | Focus |
|------------------|----------|------|
| `tests/test_core.py` | 25+ | Main flows, git diff, PR generation, timing |
| `tests/test_chat_backend.py` | 30+ | Chat memory, persistence, slash commands |
| `tests/test_skill_command.py` | 10+ | Skill template download and validation |
| `tests/test_pre_save.py` | 10+ | --pre-save flag and JSON payload |
| `tests/test_smart_excludes.py` | 14+ | Smart pathspec filter |
| `tests/test_thinking_words.py` | 10+ | Loading and parsing with `;` separator |
| `tests/test_mcp_prompts.py` | 11 | MCP prompt templates and language fallback |
| `tests/test_mcp_server.py` | 33 | MCP tools, resources, annotations, and patching |
| `tests/test_metrics.py` | 36+ | Collection, local export, repo scope, cache token summary, duration_ms |
| `tests/test_install_wizard.py` | 5+ | Interactive installation wizard |
| `tests/test_blame_metrics.py` | 10+ | Blame metrics: depth, commits, duration |
| `tests/test_linter_metrics.py` | 8+ | Linter metrics: errors, warnings, duration |
| `tests/sync_i18n.py` | — | i18n coverage verification script (orphan keys) |

**Total:** 131 automated test scenarios passing with 100% success.

---

## **🌐 Internationalization and Documentation**

* **Expanded i18n Coverage 🆕:** ~623 translation keys in pt_BR (was 491 in v0.0.32, +132 new). New strings covering PR Publisher TUI, commit modal screens, staging flow, and documentation.
* **New Technical Documentation 🆕:** `docs/pull-request-publication.md` in 5 languages (EN, PT-BR, PT-PT, ES, FR) with complete coverage of the PR publication flow, environment variables, and troubleshooting.
* **CHANGELOG.md 🆕:** Complete history of all versions (v0.0.1 → v0.0.33) in the Keep a Changelog format with Added, Changed, and Fixed sections.
* **Updated READMEs 🆕:** All 5 READMEs updated with PR Publisher features, `.gitpr/reports/` directory structure, and documentation links.
* **Documentation in 5 languages:** 24 topics in `docs/` translated to EN, PT-BR, PT-PT, ES, FR (+1 new topic: pull-request-publication).
* **Memory Index:** `.claude/memory/MEMORY.md` with 14 architecture patterns extracted from 36 reports.
* **Task reports:** `docs/claude-code/reports/` and `docs/reports/` (8 status reports).
* **Development plans:** 8+ plans documented in `docs/plans/`.

---

## **🔄 Distribution Pipeline**

1. **PyPI:** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **GitHub Releases:** PyInstaller → standalone `.exe` → automated upload
3. **GitHub Actions:** Workflow `pr-review.yml` + `action.yml`
4. **MCP Server:** Entry point `gitpr-mcp` via `pyproject.toml`

---

## **📈 Evolution Since the Previous Report (v0.0.7)**

| Area | v0.0.7 (previous) | v0.0.8 (current) |
|------|-------------------|----------------|
| **GitPR Version** | 0.0.32 | **0.0.33** |
| **Language Version** | v0.0.10 | **v0.0.11** |
| **Hook Scripts Version** | v0.0.1 | v0.0.1 |
| **AI Providers** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama |
| **Languages** | 5 (en, pt_br, pt_pt, es_es, fr_fr) | 5 (en, pt_br, pt_pt, es_es, fr_fr) |
| **Interface** | CLI + TUI Issues + Chat TUI + MCP Server + Dashboard | CLI + TUI Issues + Chat TUI + MCP Server + Dashboard + **PR Publisher TUI** |
| **PR Publication** | Only local .md generation | **Complete interactive TUI + auto-commit + push + API publication** |
| **Default Behavior** | `gitpr` generates local file | **`gitpr` opens the PR Publisher TUI** |
| **TUI Screens (total)** | 3 (issues, chat, metrics) | **5 TUI apps + 6 commit modal screens** |
| **GitHub API** | Issues via REST | **+ PRs (create, update, merge) via dedicated module** |
| **New CLI Flags** | 21 flags | **24 flags (+ `--publish`, `--no-publish`, `--no-edit`, `--base`)** |
| **Environment Variables** | 7 vars | **13 vars (+6: AUTO_COMMIT, SKIP_LINT, AUTO_STAGE, SKIP_UNSTAGED, SHOW_LOGS, AUTO_MERGE)** |
| **pt_BR Translations** | 491 keys | **~623 keys (+132 PR Publisher and commit flow)** |
| **Python Modules** | 21 files in src/ | **25 files (+ github_api.py, pr_publish_app.py, pr_publish_help.py)** |
| **Documentation** | 23 topics | **24 topics (+ pull-request-publication.md in 5 languages)** |
| **CHANGELOG** | — (GitHub Releases only) | **Complete history of the 8 versions (v0.0.1 → v0.0.33)** |
| **Test Suite** | 131 scenarios (12 files) | 131 scenarios (12 files) |
| **Commits since v0.0.32** | — | **7 commits (PR Publisher + merge flow)** |

---

## **🚧 Next Steps**

* **Tests for PR Publisher:** Unit and integration test coverage for the PR publication flow (`pr_publish_app.py`, `github_api.py`).
* **End-to-end integration tests for MCP:** Validation of tool calls and prompts via a simulated stdio client.
* **Anthropic Claude provider:** Direct support for the Claude API (`claude-sonnet-5`).
* **ASCII/Textual charts in the Dashboard:** Add time histograms and token trend charts to the metrics TUI.
* **Release pipeline in GitHub Actions:** Full automation of the PyInstaller build and asset upload to GitHub Releases.
* **More providers:** Direct OpenAI, additional local providers.
* **Plugin system:** Extensibility for linter rules and custom prompts.

---

**Report generated on:** 2026-08-09  
**Branch:** `develop_natan`  
**Author:** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
