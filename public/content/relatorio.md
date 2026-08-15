# **🚀 Project Status Report: GitPR CLI — v0.0.10 (2026-08-11)**

## **📌 Overview**

**GitPR** is an advanced CLI (Command Line Interface) tool for automating Git processes using Artificial Intelligence (Google Gemini / DeepSeek / Ollama). Its main goal is to act as a local intelligent assistant that performs Code Reviews, generates Pull Requests, semantic commit messages, audits technical debt, and injects best practices into the developer workflow (Shift Left).

**What's New in This Version (v0.0.10):**

- **Direct Invocation of MCP Tools via CLI (`gitpr-mcp --tool`):** The 12 GitPR MCP tools can now be invoked directly from the command line with `gitpr-mcp --tool <name> [--tool-args '<json>']`, without starting the stdio JSON-RPC server. The `--tool` mode (without a name) lists all available tools with their signatures. Ideal for debugging, scripts, and manual use.
- **Error Handling in PR Merge:** The PR Publisher (Textual TUI) now displays a visible error modal when the PR merge fails — especially HTTP 405 indicating conflicts. Previously, the failure was silently ignored and the flow continued as if everything had worked.
- **New MCP Documents:** 3 new MCP documentation topics in 5 languages: `mcp-annotations.md` (tool annotations), `mcp-integration.md` (integration guide), `mcp-prompts.md` (templated prompts guide).

- **Current version:** 0.0.35
- **Language dictionaries version:** v0.0.13
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
* **UI/Terminal:** Textual — TUI (Text User Interface) for interactive chat, issue editing, help screen, metrics dashboard, and PR Publisher.
* **Cryptography:** `cryptography.fernet` for local protection of API keys and GitHub tokens.
* **Configuration:** `python-dotenv`, `pyyaml` (for the static linter).
* **AI Providers:** Integration via the official Google GenAI SDK (`gemini-2.5-flash`), OpenAI SDK (`DeepSeek`), and OpenAI SDK (local `Ollama`).
* **GitHub API:** `requests` (REST API via PAT) — `src/github_api.py` module with `create_pull_request()`, `update_pull_request()`, `merge_pull_request()`.
* **MCP:** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 (official Anthropic SDK for Model Context Protocol) — 12 annotated tools, 15 resources, 7 prompts.
* **Tests:** Pytest + `unittest.mock` (13 test files, 207 scenarios).
* **Packaging:** PyInstaller (standalone binary) + setuptools/build (PyPI).
* **CI/CD:** GitHub Actions (`pr-review.yml`) + `action.yml` for execution in pipelines.

---

## **🧩 Implemented Modules and File Architecture**

### **1. Core and Git Operations (`src/core.py`)**

* **Structured Generation:** Communicates with the LLM requesting strictly JSON output.
* **Map-Reduce (Giant Diffs):** When the diff exceeds ~90k tokens, it automatically splits it into per-file batches (`split_diff_into_chunks`), processes each part (Map), and unifies the summaries (Reduce). Supports PRs, commits, and Issues.
* **Local Tokenizer:** `tokenizer.json` for accurate token estimation before sending to the AI.
* **Token Estimation:** Lightweight heuristic `len() // 4` via `estimate_token_count()` with fallback to the local tokenizer.
* **Native Git Optimization:** `-U1`, `-w`, `-M`, `-B` flags on the `get_git_diff` and `get_git_full_diff` commands to reduce useless context.
* **Pre-Save (`--pre-save`):** Hidden debug flag that saves the full payload (system instruction + prompt) as JSON before each AI call.
* **Smart Excludes with Two Layers:** Smart pathspec filter with a global layer (`~/.gitpr/conf/`) + project-local layer (`./.gitpr/conf/`). Runtime merging (union, deduplicated). Auto-seeding of the local file on first run. Support for 3 environment variables (`GITPR_SKIP_SMART_EXCLUDES`, `GITPR_SMART_EXCLUDES_GLOBAL`, `GITPR_SMART_EXCLUDES_LOCAL`).
* **Metrics with Time Tracking:** Injection of `log_command_metric()` into all flows passing the duration in milliseconds (`duration_ms`), with lazy imports.
* **Centralized Output Resolution:** `resolve_output_path()` function that centralizes output directory logic — defaulting to `.gitpr/reports/{type}/` with fallback to custom paths from `.env`.

### **2. Global Plugin System (`src/plugins.py`)**

* **Plugin Architecture:** Extensibility system that loads plugins from the `~/.gitpr/plugins/` directory, applying them to **all projects**.
* **Linter Plugins (`linter/`):** `.yml` files with additional regex rules merged with the local `.gitpr.linter.yml`.
* **MCP Prompt Plugins (`prompts/`):** `.md` files that extend the system context with specific instructions.
* **Factory Closures:** `get_linter_plugins` and `get_prompt_plugins` functions with closures to isolate state between sessions.
* **`--plugins` Command:** Lists all installed global plugins with their types and paths.
* **Multilingual Documentation:** `docs/plugins-system.md` in 5 languages (EN, PT-BR, PT-PT, ES, FR).

### **3. CLI Interface and Setup (`src/main.py` and `src/config.py`)**

* **Initial Setup:** Detects first run, creates the `~/.gitpr/` folder, and interactively prompts for API keys, preferences, and language.
* **Command Routing:** Manages all flags (`--commit`, `--review`, `--fullreview`, `--linter`, `--skill`, `--issue`, `--blame`, `--chat`, `--mcp`, `--install`, `--metrics`, `--export`, `--purge`, `--dashboard`, `--publish`, `--no-publish`, `--no-edit`, `--base`, `--lang`, `--provider`, `--pre-save`, `--plugins`).
* **Default Behavior:** Running `gitpr` without flags opens the PR Publisher TUI.
* **Flags:**
  * `--publish`: Opens the interactive TUI to review, edit, and publish the PR.
  * `--no-publish`: Generates the PR description and saves it locally without opening the interactive editor.
  * `--no-edit`: Skips the TUI entirely — auto-commit (with linter validation), auto-push, and publishes directly to GitHub.
  * `--base <branch>`: Overrides the Pull Request target branch.
  * `--plugins`: Lists installed global plugins.
  * `--version` 🆕: Displays the current GitPR version (via `@click.version_option`).
* **Environment Variables:** `GITPR_AUTO_COMMIT`, `GITPR_SKIP_LINT`, `GITPR_AUTO_STAGE`, `GITPR_SKIP_UNSTAGED_CHECK`, `GITPR_SHOW_LOGS`, `GITPR_AUTO_MERGE`, `GITPR_SKIP_SMART_EXCLUDES`, `GITPR_SMART_EXCLUDES_GLOBAL`, `GITPR_SMART_EXCLUDES_LOCAL`.
* **Contextual Help:** `-h --flag` displays feature-specific documentation with a direct (language-aware) link to GitHub.
* **--lang:** Forces the interface language for the current run without persisting the change.
* **--provider:** Forces the AI provider (`gemini`, `deepseek`, `ollama`) for the current run.
* **--mcp:** Starts the MCP server on stdio transport for editor integration — **12 annotated tools + 15 resources + 7 prompts**.
* **--install:** 4-step guided wizard that downloads skill templates, installs Git Hooks, configures MCP in editors, and validates API keys.
* **--metrics:** Local telemetry system scoped per repository: `--export`, `--purge`, `--dashboard` (interactive TUI with cache scanning).
* **--status:** Lists uncommitted files categorized (new/modified/deleted) — fast, no AI, no network.

### **4. PR Publisher TUI (`src/ui/pr_publish_app.py` and `src/ui/pr_publish_help.py`)**

* **Complete Interactive Interface:** TUI built with Textual to review, edit, and publish Pull Requests directly in the terminal.
* **6 Modal Screens:** `CommitConfirmScreen`, `FileStageScreen`, `CommitProgressScreen`, `CommitMessageScreen`, `LinterErrorScreen`, `ErrorScreen`.
* **Improved Unstaged Files Modal:** File list with fixed height (`height: 6`) and internal vertical scroll.
* **Bindings:** F1 (Help), F2 (Save local .md), F3 (Publish via GitHub API), Esc (Exit).
* **Auto-Commit Flow:** Linter → AI message → confirmation → commit → push → publish PR.
* **Unstaged Files Check:** At startup, checks `git status --porcelain` and offers a modal to select, skip, or cancel.
* **Existing PR Handling:** Detects open PRs for the current branch via GitHub API and offers push or create new.
* **Auto-Upstream:** Detects `git push` failure due to a missing upstream and automatically retries with `--set-upstream origin <branch>`.
* **"Nothing to commit" Detection:** Treats `git commit` without changes as success.
* **Merge Flow:** After PR creation/update, offers a merge option. Controlled by `GITPR_AUTO_MERGE`.
* **Merge Error Handling 🆕:** Refactoring of `_do_merge` into 3 methods with separation of responsibilities: `_do_merge` (fires on a thread), `_on_merge_success` (success callback), `_on_merge_failure` (failure callback with error modal). HTTP 405 (conflicts) displays a clear message and offers opening in the browser for manual resolution. Tracking of `final_action` ("merged"/"merge_failed") for post-TUI visual feedback with correct colors.

### **5. GitHub API Module (`src/github_api.py`)**

* **Shared Functions:** `create_pull_request()`, `update_pull_request()`, `merge_pull_request()` — encapsulating REST calls to the GitHub v3 API.
* **PAT Authentication:** Personal access token validated with `GET /user` before operations.
* **Reuse:** Functions used by both the PR TUI and the issues TUI.

### **6. Static Analysis Engine / Linter (`src/linter_engine.py`)**

* **Offline Linter:** Statically analyzes the added (`+`) lines in the git diff without spending AI quota.
* **YAML Rules:** Reads the local `.gitpr.linter.yml` file (created via `--skill`). Supports validation regex, ignoring comments, and ignoring specific directories.
* **Linter Plugins:** Additional rules loaded from `~/.gitpr/plugins/linter/*.yml` and merged with the local rules.
* **Multilingual Template:** Linter templates available in 5 languages.
* **Auto-Commit Integration:** Runs automatically before the commit in the PR publication flow.

### **7. Security and Authentication (`src/security.py`, `src/config.py`, `src/tui_issue.py`)**

* **Cryptography:** Generates a master key `secret.key` in the `~/.gitpr/` folder.
* **Token Protection:** `encrypt_data` and `decrypt_data` to protect AI API keys and GitHub PAT.
* **GitHub Token Validation:** `validate_github_token()` with a lightweight call (`GET /user`).
* **Auto-Reauth Flow:** If the token expires during `gitpr -is`, catches 401, asks for a new token, and relaunches the TUI preserving the draft.

### **8. Auto-Updater (`src/updater.py`)**

* **Hot-Swap:** Checks the latest version via the GitHub Releases API, downloads the compiled binary, and replaces it without breaking the running execution (with rollback).
* **Daily Cache:** Avoids repeated checks on the same day.
* **Connection Check:** Socket `8.8.8.8:53` before any network operation.
* **Centralized Versioning:** `__version__` (0.0.35), `__lang_version__` (v0.0.13), `__scripts_version__` (v0.0.1), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`.

### **9. Interactive Chat Interface (`src/ui/chat_app.py`)**

* **Complete TUI:** Built with Textual — message history, multi-line input, status bar with visible bindings.
* **Per-Branch Memory (`src/chat_memory.py`):** Conversation history persisted per branch, allowing continuity between sessions.
* **Slash Commands:** `/explain`, `/tests`, `/optimize`, `/clear` — shortcuts for pair programming.
* **Auto-Patching (F5):** Extracts code blocks suggested by the AI and exports them to a patch file.
* **Diff Refresh (F2):** Reloads the current `git diff` without restarting the session.
* **Session Export (F6):** Saves the full chat history for documentation.

### **10. Internationalization — i18n (`src/i18n.py`)**

* **Laravel-Inspired System:** `__()` function with support for named placeholders (`{count}`, `{file}`, etc.).
* **Automatic Detection:** Detects the OS language on first run and saves it in `GITPR_LANG`.
* **5 Languages:** en_us (default/fallback), pt_br, pt_pt, es_es, fr_fr.
* **Versioned Files:** `__lang_version__` (v0.0.13) controls updating of the language packs (`langs/*.json`).
* **Coverage:** 503 translation keys in pt_BR.
* **Cache with Language Indexing:** Cached AI responses include the current language in the MD5 keying.
* **Sync Script:** `tests/sync_i18n.py` for automatic detection of orphan keys.

### **11. Animated Spinner (`src/spinner.py`)**

* **Braille + Thinking Words:** Background thread during AI calls displaying braille characters with "thinking" words.
* **Delimiter:** Sentence separator using semicolon (`;`), compatible with complex sentences containing commas.
* **Adaptive Speed & Flickering:** Character-reveal animation adapted for long sentences and use of ANSI `\033[K` to avoid visual artifacts in the terminal.
* **263 entries per language:** Synchronized across the 5 languages.

### **12. AI Providers (`src/ai_providers.py`)**

* **3 Supported Providers:** Google Gemini (`gemini-2.5-flash`), DeepSeek (`deepseek-chat`), Ollama (local).
* **Duration Measurement:** Injection of `duration_ms` (high-precision timing via `time.perf_counter()`) into `meta_raw` and `_telemetry_meta`.
* **JSON Mode & Deterministic Parameters:** Structured outputs with `temperature=0.0` and `top_p=0.1`.

### **13. Smart Cache (`src/cache.py`)**

* **MD5 + Metadata:** Keying by MD5 hash of the diff and prompt.
* **Language Indexing:** The `lang` field was added to the cache keying.
* **Telemetry and Duration:** Persistence of the `duration_ms` and `meta_raw` fields in cache files.
* **Dashboard Reading:** `scan_cache_files_for_dashboard()` reads all cache files recursively.

### **14. Issues Engine and TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Context Engines:** Current diff, Branch history (`-ht`), and Blame archaeology (`-b`).
* **Map-Reduce for Issues:** When the context exceeds ~90k tokens, it automatically splits into chunks and unifies the results.
* **Interactive TUI:** Draft editing, F2 shortcut (save locally), F3 (publish on GitHub), and F1 (help).
* **401 Handling:** Reauthentication signaling without closing the application.

### **15. Code Archaeologist (`src/blame_engine.py`)**

* **Git Blame + AI:** Tracks the historical evolution and authorship of code snippets with commit classification (`ORIGIN` vs `REFACTORING`).
* **Blame Metrics:** Events recorded via `log_blame_metric()` with tracking of depth and number of analyzed commits.

### **16. MCP Server and Direct CLI Invocation (`src/mcp_server.py`)** 🆕

* **12 Annotated MCP Tools:** Tools for `get_git_context`, `analyze_diff`, `list_unstaged_files`, `analyze_unstaged_diff`, `get_full_diff`, `generate_commit_message`, `review_code`, `full_review`, `generate_pr_description`, `run_linter`, `analyze_blame`, `generate_issue`.
* **15 Resources + 7 Templated Prompts:** 35 template files in `templates/gitpr.prompt.*.md`.
* **Direct CLI Invocation 🆕:** Command `gitpr-mcp --tool <name> [--tool-args '<json>']` invokes any MCP tool directly without starting the stdio JSON-RPC server.
* **Registry Pattern 🆕:** `_TOOL_FUNCS` maps tool name → callable; `_get_tool_registry()` merges with catalog metadata.
* **Real Stdout Isolation 🆕:** `_write_real_stdout()` writes directly to the original `sys.__stdout__` (saved before monkey-patching), guaranteeing pure JSON on stdout.
* **Tool Listing 🆕:** `gitpr-mcp --tool` (without a name) lists all 12 available tools with parameter signatures.
* **Automatic .env Loading 🆕:** API keys automatically available in CLI mode.
* **New MCP Documents 🆕:** `docs/mcp-annotations.md`, `docs/mcp-integration.md`, `docs/mcp-prompts.md` in 5 languages each (15 new files).
* **Automatic Installer:** Configuration of supported editors (VS Code, Cursor, Claude Code, Claude Desktop, Zed) with smart JSON merge.

### **17. Metrics Dashboard TUI (`src/ui/metrics_app.py`)**

* **Per-Repository Scope (Repo-Scope):** `📁 Repository: owner/repo` label and strict filtering of events and cache data per project.
* **Async Scanning with Overlay:** Background worker thread with `ProgressBar` widget.
* **Data Consolidation:** `load_cache_token_summary()` adds cache tokens to the totalizer.
* **Cache State Control:** Registry file at `./.gitpr/metrics/{repo}/processed_cache.json`.
* **Local Export:** CSV/JSON saving to `./.gitpr/metrics/export/`.

### **18. Metrics and Telemetry System (`src/metrics.py`)**

* **Per-Repository Scope:** All events indexed by `repo_name`.
* **New Events:** Events for listing unstaged files and telemetry export.
* **Hook Events:** `log_hook_event()` for Git hooks (pre-commit, prepare-commit-msg, post-checkout, pre-push, post-merge).
* **Linter and Blame Events:** `log_linter_metric()` and `log_blame_metric()`.
* **Local Export:** `--metrics --export` generates CSV and JSON in `./.gitpr/metrics/export/` with repository filtering.
* **Cleanup:** `--metrics --purge` removes all local metrics files with interactive confirmation.

### **19. Git Hooks Synchronization**

* **Independent Versioning:** `__scripts_version__` (v0.0.1) controls the version of the hook scripts.
* **Automatic Detection:** Compares the local version with the latest and updates automatically.
* **Language-Aware:** Downloads hook templates corresponding to the configured language.

---

## **📊 Tests and Quality**

| Test File | Scenarios | Focus |
|------------------|----------|------|
| `tests/test_core.py` | 25+ | Main flows, git diff, PR generation, timing |
| `tests/test_chat_backend.py` | 30+ | Chat memory, persistence, slash commands |
| `tests/test_plugins.py` | 17 | Plugin discovery, linter rule merging, MCP prompts |
| `tests/test_mcp_server.py` | 75+ 🆕 | MCP tools, resources, annotations, patching, direct CLI |
| `tests/test_metrics.py` | 36+ | Collection, local export, repo scope, cache token summary, duration_ms |
| `tests/test_smart_excludes.py` | 14+ | Smart pathspec filter |
| `tests/test_mcp_prompts.py` | 11 | MCP prompt templates and language fallback |
| `tests/test_blame_metrics.py` | 10+ | Blame metrics: depth, commits, duration |
| `tests/test_linter_metrics.py` | 8+ | Linter metrics: errors, warnings, duration |
| `tests/test_thinking_words.py` | 9+ | Loading and parsing with `;` separator |
| `tests/test_skill_command.py` | 5+ | Skill template download and validation |
| `tests/test_install_wizard.py` | 5+ | Interactive installation wizard |
| `tests/test_pre_save.py` | 3+ | --pre-save flag and JSON payload |
| `tests/sync_i18n.py` | — | i18n coverage verification script (orphan keys) |

**Total:** 207 automated test scenarios passing (13 test files). 1 known failure in `test_metrics.py::test_app_skips_export_and_config_files` (pre-existing, unrelated to recent changes).

---

## **🌐 Internationalization and Documentation**

* **i18n Coverage:** 503 translation keys in pt_BR.
* **New Technical Documents 🆕:** 3 new MCP topics in 5 languages each (15 files):
  - `docs/mcp-annotations.md` — catalog of annotations for the 12 MCP tools
  - `docs/mcp-integration.md` — MCP integration guide for editors (VS Code, Cursor, Claude Code, Claude Desktop, Zed)
  - `docs/mcp-prompts.md` — reference for the 7 templated MCP prompts
* **Existing Documentation:** `docs/plugins-system.md`, `docs/smart-excludes.md`, `docs/untracked-files.md` and more — all in 5 languages.
* **Documentation in 5 languages:** 37 unique topics in `docs/` (+3 new MCP topics).
* **Memory Index:** `.claude/memory/MEMORY.md` with 20 architecture patterns (+2 new: mcp-tool-cli-invocacao-direta, merge-conflict-error-handling).
* **Task reports:** `docs/claude-code/reports/` and `docs/reports/` (10 status reports).
* **Development plans:** 10+ plans documented in `docs/plans/`.

---

## **🔄 Distribution Pipeline**

1. **PyPI:** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **GitHub Releases:** PyInstaller → standalone `.exe` → automated upload
3. **GitHub Actions:** Workflow `pr-review.yml` + `action.yml`
4. **MCP Server:** Entry point `gitpr-mcp` via `pyproject.toml`

---

## **📈 Evolution Since the Previous Report (v0.0.9)**

| Area | v0.0.9 (previous) | v0.0.10 (current) |
|------|-------------------|----------------|
| **GitPR Version** | 0.0.34 | **0.0.35** |
| **Language Version** | v0.0.12 | **v0.0.13** |
| **Hook Scripts Version** | v0.0.1 | v0.0.1 |
| **AI Providers** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama |
| **Languages** | 5 (en, pt_br, pt_pt, es_es, fr_fr) | 5 (en, pt_br, pt_pt, es_es, fr_fr) |
| **Interface** | CLI + TUI Issues + Chat TUI + MCP Server + Dashboard + PR Publisher TUI | CLI + TUI Issues + Chat TUI + MCP Server + Dashboard + PR Publisher TUI + **Direct MCP CLI** |
| **MCP Tools** | 10 tools | **12 tools (+ list_unstaged_files, analyze_unstaged_diff)** |
| **Direct MCP CLI** | — | **`gitpr-mcp --tool <name>` — direct invocation without server** |
| **PR Merge Handling** | Flow ignored merge errors | **Error modal for HTTP 405 (conflicts) + visual feedback** |
| **CLI Flags** | 25 flags | **26 flags (+ `--version`)** |
| **Environment Variables** | 16 vars | 16 vars |
| **Documentation** | 34 topics | **37 topics (+3: mcp-annotations, mcp-integration, mcp-prompts in 5 languages)** |
| **Test Suite** | 171 scenarios (13 files) | **207 scenarios (13 files, +36 MCP tests)** |
| **Commits since v0.0.34** | — | **2 commits (mcp-tool-cli + merge-error-handling)** |
| **Merged PRs** | — | **2 PRs (#107, #110)** |

---

## **🚧 Next Steps**

* **Tests for PR Publisher:** Unit and integration test coverage for the PR publication flow (`pr_publish_app.py`, `github_api.py`).
* **End-to-end integration tests for MCP:** Validation of tool calls and prompts via a simulated stdio client.
* **Anthropic Claude provider:** Direct support for the Claude API (`claude-sonnet-5`).
* **ASCII/Textual charts in the Dashboard:** Add time histograms and token trend charts to the metrics TUI.
* **Release pipeline in GitHub Actions:** Full automation of the PyInstaller build and asset upload to GitHub Releases.
* **More providers:** Direct OpenAI, additional local providers.
* **Local `--init` command:** Seed of `.gitpr/conf/` with local configuration templates (smart-excludes, linter, etc.).

---

**Report generated on:** 2026-08-11  
**Branch:** `develop_natan`  
**Author:** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
