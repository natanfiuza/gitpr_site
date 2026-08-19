# **🚀 Project Status Report: GitPR CLI — v0.0.12 (2026-08-19)**

## **📌 Overview**

**GitPR** is an advanced CLI (Command Line Interface) tool for automating Git processes using Artificial Intelligence (Google Gemini / DeepSeek / Ollama). Its main goal is to act as a local intelligent assistant that performs Code Reviews, generates Pull Requests, semantic commit messages, audits technical debt, and injects best practices into the developer workflow (Shift Left).

**What's New in This Version (v0.0.12):**
- **External Linters Bridge + `--linter-setup` Assistant:** Integration with mature linters (ESLint, PHP_CodeSniffer, Stylelint) running only on the changed lines of the diff, Checkstyle XML output parser, new error TUI (`LinterApp`) and consolidated Markdown report in `.gitpr/reports/linter/`. The interactive assistant configures everything with remote presets (`templates/gitpr.linter-presets.json`) versioned by the `LINTER_PRESETS_VERSION` marker.
- **i18n Repaired and Complete:** The legacy sync regex captured call-site arguments (`fg="cyan"`, `count=len(...)`) and generated "mangled" keys that always fell back to English. 51 corrupted keys repaired + 36 keys with literal `\n` in all 6 dictionaries; AST audit of 638 keys with **0 untranslated and 0 mangled**; total parity of **547 identical keys per file**; `__lang_version__` v0.0.13 → **v0.0.20** with guard tests.
- **Co-Authorship Trailer:** Every AI-generated commit receives `Co-Authored-By: Gitpr-cli <gitpr@natanfiuza.dev.br>` — idempotent (no duplication, preserves third-party trailers), hidden from the TUI (injected only at commit execution) and with opt-out `GITPR_COAUTHOR=false`.
- **MCP Server Hang Fix:** All 12 tool handlers were synchronous and ran inline on the event loop — any blocking call (git subprocess, OTA download, AI SDK) froze the entire stdio server. New `_offload` decorator (anyio worker threads), warm-import at startup, `stdin=subprocess.DEVNULL` on all subprocesses and a hard 10s timeout on the smart-excludes download. New e2e tests with real JSON-RPC stdio.
- **Linter Error Modal Fixes:** "Commit with --no-verify" and "Abort" buttons side by side (previously stacked and overlapping); the no-verify choice now resumes the commit flow (previously dismissed the modal and looped back to the linter); modal push deferred via `call_next` to the app's message pump.
- **Dead Code Removed + MCP Tweaks:** Dead `FileStageScreen` class removed (pending item from the previous report); `claude-code` listed in the `gitpr-mcp --install` help; hidden `gitpr --mcp` alias documented.
- **Multilingual Documentation Expanded:** `docs/ARCHITECTURE.md` rewritten in canonical EN + 4 locales created (18 architecture topics, index of 32 docs); new `i18n_explanation` topic in 5 languages; READMEs and 4 topics updated.
- **Consistent Codebase Formatting:** Black-style refactor across all of `src/` (double quotes, trailing commas, line breaks) — no functional change.
- **Local Claude Code Skills:** `status-report` (status report generation), `implement-fixes` (fix workflow) and `caveman-commit` (compact commit messages — replaced the `docs/caveman-commit.md` doc).

- **Current version:** 0.0.37
- **Language dictionaries version:** v0.0.20
- **Hook scripts version:** v0.0.3
- **Distribution:** PyPI (`pip install gitpr-cli`) + GitHub Releases (standalone binary)
- **Website:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Repository:** [github.com/natanfiuza/gitpr](https://github.com/natanfiuza/gitpr)
- **License:** LGPL-2.1
- **Supported languages:** en_us, pt_br, pt_pt, es_es, fr_fr (5 languages, 6 dictionaries)

---

## **🏗️ Base Architecture and Libraries**

* **Language:** Python >= 3.10
* **CLI Framework:** Click (for commands, flags, and terminal formatting).
* **UI/Terminal:** Textual — TUI (Text User Interface) for interactive chat, issue editing, help screen, metrics dashboard, PR Publisher and linter errors (`LinterApp`).
* **Cryptography:** `cryptography.fernet` for local protection of API keys and GitHub tokens.
* **Configuration:** `python-dotenv`, `pyyaml` (for the static linter).
* **AI Providers:** Integration via the official Google GenAI SDK (`gemini-2.5-flash`), OpenAI SDK (`DeepSeek`), and OpenAI SDK (local `Ollama`).
* **GitHub API:** `requests` (REST API via PAT) — `src/github_api.py` module with `create_pull_request()`, `update_pull_request()`, `merge_pull_request()`.
* **MCP:** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 (official Anthropic SDK for Model Context Protocol) — 12 annotated tools, 15 resources, 7 prompts; handlers offloaded to threads via `anyio`.
* **Tests:** Pytest + `unittest.mock` (17 test files, 264 scenarios) + MCP server e2e tests via real subprocess (JSON-RPC stdio).
* **Packaging:** PyInstaller (standalone binary) + setuptools/build (PyPI).
* **CI/CD:** GitHub Actions (`pr-review.yml`) + `action.yml` for execution in pipelines.

---

## **🧩 Implemented Modules and File Architecture**

### **1. Core and Git Operations (`src/core.py`)**

* **Structured Generation:** Communicates with the LLM requesting strictly JSON output.
* **Map-Reduce (Giant Diffs):** When the diff exceeds ~90k tokens, automatically splits it into batches by file (`split_diff_into_chunks`), processes each part (Map) and unifies the summaries (Reduce). Supports PRs, commits and Issues.
* **Local Tokenizer:** `tokenizer.json` for accurate token estimation before sending to the AI.
* **Token Estimation:** Lightweight `len() // 4` heuristic via `estimate_token_count()` with fallback to the local tokenizer.
* **Native Git Optimization:** `-U1`, `-w`, `-M`, `-B` flags on the `get_git_diff` and `get_git_full_diff` commands to reduce useless context.
* **Pre-Save (`--pre-save`):** Hidden debug flag that saves the full payload (system instruction + prompt) as JSON before each AI call.
* **Two-Layer Smart Excludes:** Smart pathspec filter with global (`~/.gitpr/conf/`) + local project (`./.gitpr/conf/`) layers. Runtime merging (union, deduplicated). Auto-seeding of the local file on first run. Supports 3 environment variables (`GITPR_SKIP_SMART_EXCLUDES`, `GITPR_SMART_EXCLUDES_GLOBAL`, `GITPR_SMART_EXCLUDES_LOCAL`).
* **Metrics with Time Tracking:** Injection of `log_command_metric()` into all flows with millisecond duration reporting (`duration_ms`) and lazy imports.
* **Centralized Output Resolution:** `resolve_output_path()` function that centralizes output directory logic — default in `.gitpr/reports/{type}/` with fallback to custom paths from `.env`.
* **In-Progress Merge Detection:** `is_merge_in_progress()` helper (checks `git rev-parse -q --verify MERGE_HEAD`, silent and worktree-safe) — used as defense in depth against old hooks calling the CLI during a merge.
* **Staging with Real Error:** `stage_files()` returns the `(success, error_message)` tuple capturing `git add` stderr/stdout on failures — the real git error reaches the user instead of being swallowed.
* **Co-Authorship Trailer 🆕:** `COAUTHOR_TRAILER` + `append_coauthor_trailer()` — idempotent helper that appends `Co-Authored-By: Gitpr-cli <gitpr@natanfiuza.dev.br>` with blank-line separation; does not duplicate an existing trailer and preserves third-party `Co-Authored-By:`.
* **OTA Download with Hard Timeout 🆕:** `_download_smart_excludes()` runs the request in a daemon thread with a 10s timeout — the urllib timeout does not limit DNS resolution on Windows; on stall, falls back to the offline copy.
* **Shielded Subprocesses 🆕:** `stdin=subprocess.DEVNULL` on all `subprocess.run` — children no longer inherit the MCP server's JSON-RPC pipe (avoids interactive hang).
* **Centralized Linter Output 🆕:** `OUTPUT_FILE_NAME_LINTER` mapped to the `linter` folder in `_OUTPUT_FOLDER_MAP` — reports saved to `.gitpr/reports/linter/`.

### **2. Global Plugin System (`src/plugins.py`)**

* **Plugin Architecture:** Extensibility system that loads plugins from the `~/.gitpr/plugins/` directory applying to **all projects**.
* **Linter Plugins (`linter/`):** `.yml` files with additional regex rules merged with the local `.gitpr.linter.yml`. 🆕 `load_external_linters()` also reads the `external_linters` section from global plugins.
* **MCP Prompt Plugins (`prompts/`):** `.md` files that extend the system context with specific instructions.
* **Factory Closures:** `get_linter_plugins` and `get_prompt_plugins` functions with closures to isolate state between sessions.
* **`--plugins` Command:** Lists all installed global plugins with their types and paths.
* **Multilingual Documentation:** `docs/plugins-system.md` in 5 languages (EN, PT-BR, PT-PT, ES, FR).

### **3. CLI Interface and Setup (`src/main.py` and `src/config.py`)**

* **Initial Setup:** Detects first run, creates the `~/.gitpr/` folder, and interactively asks for API keys, preferences and language.
* **Command Routing:** Manages all flags (`--commit`, `--review`, `--fullreview`, `--linter`, `--linter-setup`, `--skill`, `--issue`, `--blame`, `--chat`, `--mcp`, `--install`, `--metrics`, `--export`, `--purge`, `--dashboard`, `--no-publish`, `--no-edit`, `--base`, `--lang`, `--provider`, `--pre-save`, `--plugins`, `--status`, `--update`).
* **Default Behavior:** Running `gitpr` without flags opens the PR Publisher TUI.
* **Flags:**
  * `--publish`: replaced by the default flow — the PR Publisher TUI opens by default; the `--no-publish` / `--no-edit` / `--base` modifiers control the flow.
  * `--no-publish`: Generates the PR description and saves it locally without opening the interactive editor.
  * `--no-edit`: Skips the TUI entirely — auto-commit (with linter validation), auto-push and publishes directly to GitHub.
  * `--base <branch>`: Overrides the Pull Request target branch.
  * `--plugins`: Lists installed global plugins.
  * `--linter-setup` 🆕: Opens the interactive external linter configuration assistant (remote presets + injection into `.gitpr.linter.yml`).
  * `--version`: Shows the current GitPR version (via `@click.version_option`).
* **Environment Variables:** `GITPR_AUTO_COMMIT`, `GITPR_SKIP_LINT`, `GITPR_AUTO_STAGE`, `GITPR_SKIP_UNSTAGED_CHECK`, `GITPR_SHOW_LOGS`, `GITPR_AUTO_MERGE`, `GITPR_SKIP_SMART_EXCLUDES`, `GITPR_SMART_EXCLUDES_GLOBAL`, `GITPR_SMART_EXCLUDES_LOCAL`, `OUTPUT_FILE_NAME_LINTER` 🆕, `GITPR_COAUTHOR` 🆕 (read-only opt-out, outside `DEFAULT_CONFIG`).
* **Contextual Help:** `-h --flag` shows feature-specific documentation with a direct (language-aware) link to GitHub. 🆕 Fixed for hyphenated flags (`--linter-setup`, `--no-publish`, `--no-edit`, `--no-unstaged-check`) — `param_name.replace('-', '_')`.
* **--lang:** Forces the interface language for the current run without persisting the change.
* **--provider:** Forces the AI provider (`gemini`, `deepseek`, `ollama`) for the current run.
* **--mcp:** Starts the MCP server on stdio transport for editor integration — **12 annotated tools + 15 resources + 7 prompts**.
* **--install:** 4-step guided assistant that downloads skill templates, installs Git Hooks, configures MCP in editors and validates API keys. 🆕 100% translated output (10 hardcoded messages migrated to `__()` + 34 new keys).
* **--metrics:** Local telemetry system scoped per repository: `--export`, `--purge`, `--dashboard` (interactive TUI with cache scan).
* **--status:** Lists uncommitted files categorized (new/modified/deleted) — fast, no AI, no network.
* **Conditional Linter Report 🆕:** The `.gitpr/reports/linter/` report is only generated when there are warnings or errors — clean diffs no longer create empty files.

### **4. PR Publisher TUI (`src/ui/pr_publish_app.py` and `src/ui/pr_publish_help.py`)**

* **Complete Interactive Interface:** TUI built with Textual to review, edit and publish Pull Requests directly in the terminal.
* **6 Modal Screens:** `StageFilesScreen`, `CommitConfirmScreen`, `CommitProgressScreen`, `CommitMessageScreen`, `LinterErrorScreen`, `ErrorScreen`.
* **Improved Unstaged Files Modal:** File list with fixed height (`height: 6`) and internal vertical scroll.
* **Bindings:** F1 (Help), F2 (Save local .md), F3 (Publish via GitHub API), Esc (Exit).
* **Auto-Commit Flow:** Linter → AI message → confirm → commit → push → publish PR.
* **Unstaged Files Check:** On start, checks `git status --porcelain` and offers a modal to select, skip or cancel.
* **Existing PR Handling:** Detects open PRs for the current branch via GitHub API and offers push or create new.
* **Auto-Upstream:** Detects `git push` failure due to missing upstream and automatically tries `--set-upstream origin <branch>`.
* **"Nothing to commit" Detection:** Handles `git commit` with no changes as success.
* **Merge Flow:** After PR creation/update, offers a merge option. Controlled by `GITPR_AUTO_MERGE`.
* **Merge Error Handling:** `_on_merge_success` / `_on_merge_failure` callbacks with error modal for HTTP 405 (conflicts) and visual feedback post-TUI.
* **Real File Selection:** `StageFilesScreen.btn_stage` reads the selection directly from `SelectionList.selected` — individual row toggles (click/Enter) are now respected; removed the manual `_selected` dictionary that fell out of sync and the duplicated `git add` inside the TUI (single staging in `main.py`).
* **Dead Code Removed 🆕:** The draft `FileStageScreen` class (dead duplicate of `StageFilesScreen`) was removed along with the orphaned `get_unstaged_files`/`stage_files` imports — "Next Steps" item from the previous report completed.
* **Hidden Co-Authorship Trailer 🆕:** The `Co-Authored-By:` no longer appears on the message editing screen (`CommitMessageScreen`) — it is injected only at commit execution, after user confirmation. `_pending_commit_msg` stays clean for the PR title fallback.
* **Fixed Linter Error Modal 🆕:** Side-by-side buttons in a `Horizontal` container with `height: auto` (previously stacked/overlapping due to `1fr`); `LinterErrorScreen` push deferred via `call_next` to the app's message pump (previously the callback was attached to the progress screen's dead queue); `skip_linter` in `_start_progress_and_commit`/`_run_linter_and_commit` ensures the no-verify commit resumes the flow without re-running the linter.

### **5. GitHub API Module (`src/github_api.py`)**

* **Shared Functions:** `create_pull_request()`, `update_pull_request()`, `merge_pull_request()` — encapsulating REST calls to the GitHub v3 API.
* **Authentication via PAT:** Personal access token validated with `GET /user` before operations.
* **Reuse:** Functions used by both the PR TUI and the issues TUI.

### **6. Static Analysis Engine / Linter (`src/linter_engine.py`)**

* **Offline Linter:** Statically analyzes the added (`+`) lines in the git diff without spending AI quotas.
* **YAML Rules:** Reads the local `.gitpr.linter.yml` file (created via `--skill`). Supports regex validation, ignoring comments and ignoring specific directories.
* **Linter Plugins:** Additional rules loaded from `~/.gitpr/plugins/linter/*.yml` and merged with local rules.
* **External Linters Bridge 🆕:** `_run_external_linter()` runs external linters via subprocess (`encoding="utf-8"`, `errors="replace"`, `stdin=DEVNULL`, `timeout=120`) and returns the XML stdout **regardless of the exit code** — linters return > 0 when they find problems.
* **Checkstyle XML Parser 🆕:** `_parse_checkstyle_xml()` extracts errors (line/severity/message) with `xml.etree.ElementTree`, tolerating non-numeric line numbers and invalid XML.
* **Diff Cross-Reference 🆕:** Diff mode tracks the added (`+`) lines and counts only XML errors whose line was changed in the current diff — pre-existing problems are ignored.
* **External-Only Setup 🆕:** With no regex rules but external linters configured, the scan still runs (previously silently ignored).
* **Consolidated Report 🆕:** `generate_linter_report_content()` consolidates regex + external errors into a single Markdown.
* **Multilingual template:** Linter templates available in 5 languages.
* **Auto-Commit Integration:** Runs automatically before the commit in the PR publication flow.

### **7. Security and Authentication (`src/security.py`, `src/config.py`, `src/tui_issue.py`)**

* **Encryption:** Generates a master `secret.key` in the `~/.gitpr/` folder.
* **Token Protection:** `encrypt_data` and `decrypt_data` to protect AI API keys and the GitHub PAT.
* **GitHub Token Validation:** `validate_github_token()` with a lightweight call (`GET /user`).
* **Auto-Reauth Flow:** If the token expires during `gitpr -is`, catches 401, asks for a new token and relaunches the TUI preserving the draft.

### **8. Auto-Updater (`src/updater.py`)**

* **Hot-Swap:** Checks the GitHub Releases API for the latest version, downloads the compiled binary and replaces it without breaking the running execution (with rollback).
* **Daily Cache:** Avoids repeated checks on the same day.
* **Connection Check:** Socket `8.8.8.8:53` before any network operation.
* **Centralized Versioning:** `__version__` (0.0.37), `__lang_version__` (v0.0.20), `__scripts_version__` (v0.0.3), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`, `LINTER_PRESETS_VERSION` 🆕 (linter presets updatable without a release).

### **9. Interactive Chat Interface (`src/ui/chat_app.py`)**

* **Complete TUI:** Built with Textual — message history, multi-line input, status bar with visible bindings.
* **Per-Branch Memory (`src/chat_memory.py`):** Conversation history persisted per branch, allowing continuity between sessions.
* **Slash Commands:** `/explain`, `/tests`, `/optimize`, `/clear` — pair programming shortcuts.
* **Auto-Patching (F5):** Extracts AI-suggested code blocks and exports them to a patch file.
* **Diff Refresh (F2):** Reloads the current `git diff` without restarting the session.
* **Session Export (F6):** Saves the full chat history for documentation.

### **10. Internationalization — i18n (`src/i18n.py`)**

* **Laravel-Inspired System:** `__()` function with named placeholder support (`{count}`, `{file}`, etc.).
* **Automatic Detection:** Detects the OS language on first run and saves it to `GITPR_LANG`.
* **5 Languages, 6 Dictionaries:** en_us (default/fallback), pt_br, pt_pt, es/es_es, fr/fr_fr (es/fr duplicated per family).
* **Versioned Files:** `__lang_version__` (v0.0.20) controls language pack updates (`langs/*.json`) — bump chain v0.0.13 → v0.0.20 in this window.
* **Coverage:** 547 translation keys in each of the 6 files — **total key-set parity**.
* **Corrupted Key Repair 🆕:** 51 "mangled" keys (the legacy sync regex captured call-site kwargs like `fg="cyan"`) + 36 keys with double-escaped literal `\n` were repaired in all 6 files — **0 mangled, 0 untranslated** after an AST audit of 638 keys.
* **Complete i18n of `--install` 🆕:** The 10 hardcoded messages of the MCP installer (`_run_install`, `_install_for_editor`) migrated to `__()` with named kwargs; 34 new keys translated.
* **Fixed Sync Script 🆕:** `tests/sync_i18n.py` — new `PATTERN` for `__()` call literals (no longer captures the call-site `)`), `ast.literal_eval` for escape sequences, `_live_key()` index to migrate legacy entries and empty-scan guard (never overwrites with zero keys).
* **Language-Indexed Cache:** Cached AI responses include the current language in the MD5 keying.
* **Identity Keys by Design:** 11 keys intentionally kept in EN (AI prompts, universal `[OK]`/`[FAIL]` markers, technical terms).

### **11. Animated Spinner (`src/spinner.py`)**

* **Braille + Thinking Words:** Background thread during AI calls displaying braille characters with "thinking" words.
* **Delimiter:** Sentence separator by semicolon (`;`), compatible with complex sentences containing commas.
* **Adaptive Speed & Flickering:** Character discovery animation adapted for long phrases and use of ANSI `\033[K` to avoid visual artifacts in the terminal.
* **263 entries per language:** Synced across the 5 languages.

### **12. AI Providers (`src/ai_providers.py`)**

* **3 Supported Providers:** Google Gemini (`gemini-2.5-flash`), DeepSeek (`deepseek-chat`), Ollama (local).
* **Duration Measurement:** Injection of `duration_ms` (high-precision timing via `time.perf_counter()`) into `meta_raw` and `_telemetry_meta`.
* **JSON Mode & Deterministic Parameters:** Structured outputs with `temperature=0.0` and `top_p=0.1`.

### **13. Smart Cache (`src/cache.py`)**

* **MD5 + Metadata:** Keyed by MD5 hash of the diff and prompt.
* **Language Indexing:** The `lang` field was added to cache keying.
* **Telemetry and Duration:** Persistence of the `duration_ms` and `meta_raw` fields in cache files.
* **Dashboard Reading:** `scan_cache_files_for_dashboard()` reads all cache files recursively.

### **14. Issues Engine and TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Context Engines:** Current diff, Branch history (`-ht`), and Blame archaeology (`-b`).
* **Map-Reduce for Issues:** When the context exceeds ~90k tokens, automatically splits into chunks and unifies the results.
* **Interactive TUI:** Draft editing, F2 shortcut (save locally), F3 (publish to GitHub) and F1 (help).
* **401 Handling:** Reauthentication signaling without closing the application.

### **15. Code Archaeologist (`src/blame_engine.py`)**

* **Git Blame + AI:** Tracks the evolution and historical authorship of code excerpts with commit classification (`ORIGIN` vs `REFACTORING`).
* **Blame Metrics:** Events logged via `log_blame_metric()` with depth tracking and number of analyzed commits.

### **16. MCP Server and Direct CLI Invocation (`src/mcp_server.py`)**

* **12 Annotated MCP Tools:** Tools for `get_git_context`, `analyze_diff`, `list_unstaged_files`, `analyze_unstaged_diff`, `get_full_diff`, `generate_commit_message`, `review_code`, `full_review`, `generate_pr_description`, `run_linter`, `analyze_blame`, `generate_issue`.
* **15 Resources + 7 Templated Prompts:** 35 template files in `templates/gitpr.prompt.*.md`.
* **Direct CLI Invocation:** The `gitpr-mcp --tool <name> [--tool-args '<json>']` command invokes any MCP tool directly without starting the stdio JSON-RPC server.
* **Registry Pattern:** `_TOOL_FUNCS` maps tool name → callable; `_get_tool_registry()` merges with catalog metadata.
* **Real Stdout Isolation:** `_write_real_stdout()` writes directly to the original `sys.__stdout__` (saved before monkey-patching), ensuring pure JSON on stdout.
* **Tool Listing:** `gitpr-mcp --tool` (without a name) lists all 12 available tools with parameter signatures.
* **Automatic .env Loading:** API keys automatically available in CLI mode.
* **Event Loop Offload 🆕:** `_offload` decorator (`anyio.to_thread.run_sync`) applied to the 12 tools — synchronous handlers no longer freeze the stdio server during blocking calls (root cause of the `run_linter` hang in Claude Code). `_TOOL_FUNCS` unwraps (`fn.__wrapped__`) keeping the `--tool` CLI mode synchronous.
* **Warm-Import at Startup 🆕:** `src.core` pre-import thread — the smart-excludes OTA download never delays the first call (import lock contended in a worker thread, never on the loop).
* **Fixed `--install` Help 🆕:** `claude-code` now appears in the help's supported editors list (was accepted in `choices` but omitted from the text).
* **E2E Tests 🆕:** `tests/test_mcp_server_e2e.py` starts the real server as a subprocess and speaks JSON-RPC stdio (initialize, `run_linter`, `get_git_context` — each response asserted within 60s), hermetic via `GITPR_SKIP_SMART_EXCLUDES=1`.
* **Automatic Installer:** Configuration of supported editors (VS Code, Cursor, Claude Code, Claude Desktop, Zed) with smart JSON merge.

### **17. Metrics Dashboard TUI (`src/ui/metrics_app.py`)**

* **Repo-Scope:** `📁 Repository: owner/repo` label and strict filtering of events and cache data per project.
* **Async Scan with Overlay:** Background worker thread with `ProgressBar` widget.
* **Data Consolidation:** `load_cache_token_summary()` adds cache tokens to the totalizer.
* **Cache State Control:** Registry file at `./.gitpr/metrics/{repo}/processed_cache.json`.
* **Local Export:** CSV/JSON saved to `./.gitpr/metrics/export/`.

### **18. Metrics and Telemetry System (`src/metrics.py`)**

* **Repo-Scope:** All events indexed by `repo_name`.
* **New Events:** Unstaged file listing events and telemetry export.
* **Hook Events:** `log_hook_event()` for Git hooks (pre-commit, prepare-commit-msg, post-checkout, pre-push, post-merge).
* **Linter and Blame Events:** `log_linter_metric()` and `log_blame_metric()`.
* **Local Export:** `--metrics --export` generates CSV and JSON in `./.gitpr/metrics/export/` with repository filtering. 🆕 Export examples (CSV/JSON) versioned in the repository and `.gitignore` adjusted — the `.gitpr/reports/` folder is no longer ignored.
* **Cleanup:** `--metrics --purge` removes all local metrics files with interactive confirmation.

### **19. Git Hooks Synchronization**

* **Independent Versioning:** `__scripts_version__` (v0.0.3) controls the hook scripts version.
* **Automatic Detection:** Compares the local version with the latest and updates automatically.
* **Language-Aware:** Downloads hook templates matching the configured language.
* **Merge-Source Skip:** The `prepare-commit-msg` template (5 language variants) uses a POSIX case that skips the `message|merge|squash|commit` sources and checks `.git/MERGE_HEAD` as belt-and-braces — git-generated commits (`git pull`, `git merge`, `--amend`, `-c`/`-C`, `--squash`) preserve the original git message.

### **20. External Linters Bridge and Interactive Assistant (`src/linter_wizard.py`, `src/ui/linter_app.py`) 🆕**

* **`--linter-setup` Assistant 🆕:** Interactive wizard that lists numbered presets (PHP_CodeSniffer, ESLint, Stylelint), shows the linter's native installation command and injects the `external_linters` block into `.gitpr.linter.yml` (with dedup and creation of the `.gitpr/skill/` folder).
* **Remote Presets 🆕:** `templates/gitpr.linter-presets.json` served from GitHub with a resolution chain (updated local copy → download → stale copy → embedded `_LINTER_PRESETS` fallback), versioned by the `LINTER_PRESETS_VERSION` marker — new linters arrive without a release.
* **Linter Error TUI 🆕:** `src/ui/linter_app.py` (Textual) displays critical errors and warnings when there are blocking errors outside hooks/quiet; in hook/quiet it prints and does `sys.exit(1)` (commit blocking preserved).
* **Markdown Report 🆕:** `generate_linter_report_content()` consolidates regex + external errors into `.gitpr/reports/linter/` with configurable name via `OUTPUT_FILE_NAME_LINTER` — generated only when there are violations.
* **Efficient Scope 🆕:** External linters only run when there are modified files with compatible extensions; config YAML read once per run.
* **Test Coverage 🆕:** 13 scenarios in `tests/test_external_linters.py` (XML parser, subprocess, diff cross-reference, config merge, report generator) + 4 hermetic mock metrics tests.

---

## **📊 Tests and Quality**

| Test File | Scenarios | Focus |
|------------------|----------|------|
| `tests/test_core.py` | 31 | Main flows, git diff, PR generation, timing, merge in progress, staging, co-authorship trailer |
| `tests/test_chat_backend.py` | 18 | Chat memory, persistence, slash commands |
| `tests/test_plugins.py` | 17 | Plugin discovery, linter rule merge, MCP prompts |
| `tests/test_mcp_server.py` | 82 | MCP tools, resources, annotations, patching, direct CLI, `_offload` decorator |
| `tests/test_metrics.py` | 34 | Collection, local export, repo scope, cache token summary, duration_ms |
| `tests/test_smart_excludes.py` | 13 | Smart pathspec filter |
| `tests/test_mcp_prompts.py` | 11 | MCP prompt templates and language fallback |
| `tests/test_blame_metrics.py` | 4 | Blame metrics: depth, commits, duration |
| `tests/test_linter_metrics.py` | 4 | Linter metrics: errors, warnings, duration |
| `tests/test_thinking_words.py` | 3 | Loading and parsing with `;` separator |
| `tests/test_skill_command.py` | 3 | Skill template download and validation |
| `tests/test_install_wizard.py` | 3 | Interactive installation assistant |
| `tests/test_pre_save.py` | 3 | --pre-save flag and JSON payload |
| `tests/test_external_linters.py` | 13 🆕 | Checkstyle bridge: XML parser, subprocess, diff cross-reference, report |
| `tests/test_i18n.py` | 15 🆕 | Language parity, mangled, truncated and orphan keys, linter modal keys |
| `tests/test_mcp_server_e2e.py` | 6 🆕 | Real MCP server via subprocess + JSON-RPC stdio (initialize, run_linter, get_git_context) + `--tool` mode |
| `tests/test_pr_publish_linter_modal.py` | 4 🆕 | Linter error modal: side-by-side layout, abort, no-verify, full TUI flow with `no_verify=True` commit |
| `tests/sync_i18n.py` | — | i18n coverage verification script (orphan keys, extraction by literal) |

**Total:** 264 automated test scenarios passing (17 test files). Full run verified in this version: **264/264 passed in ~44s** — first 100% green run on the pt-BR machine (the 2 pre-existing locale failures in `test_external_linters.py` were fixed by pinning `TRANSLATIONS` to `{}` via `mock.patch`). New tests: `TestExternalLinters` (13), `test_i18n.py` (15), `test_mcp_server_e2e.py` (6), `test_pr_publish_linter_modal.py` (4), `TestOffloadDecorator` (7) and `TestCoauthorTrailer` (5).

---

## **🌐 Internationalization and Documentation**

* **i18n Coverage:** 547 translation keys in each of the 6 dictionaries (+40 since the previous report) with **total key-set parity** — AST audit of 638 keys used in code: 0 mangled, 0 untranslated.
* **Updated Documents 🆕 (all in 5 languages):**
  - `docs/ARCHITECTURE.md` — rewritten in canonical EN + 4 locales created (`ARCHITECTURE.pt_br.md`, `.pt_pt.md`, `.es_es.md`, `.fr_fr.md`): 18 architecture topics, documentation index with 32 links, note on the MCP offload and the co-authorship trailer
  - `docs/i18n_explanation.md` 🆕 — new topic about the internationalization system in 5 languages
  - `docs/linter-regras-customizadas.md` — new sections 5 (Checkstyle Bridge) and 6 (Markdown Reports) + `external_linters` block in the YAML structure
  - `docs/commit-message-ia.md` — "Co-Author Signature" section with updated console example
  - `docs/mcp-integration.md` — "Alternative Entry Point (`gitpr --mcp`)" section + `claude-code` in the editors list
  - `docs/pull-request-publication.md` — trailer injection note per flow + fixed components table (`FileStageScreen` → `StageFilesScreen`)
  - `docs/providers-ia.md` — synced
  - `README.md` + 4 locales — "External Linters (Checkstyle Bridge)" subsection, "Linter Report" line in the output structure and the `--linter-setup` flag bullet
  - `docs/caveman-commit.md` — removed: the topic became the local `caveman-commit` skill (`.claude/skills/`)
* **Documentation in 5 languages:** 33 canonical topics in `docs/` (29 with complete coverage in the 5 languages; 4 PT-only topics: `como_reverter_commit_git_localmente`, `github-issue-prompt-com-gh`, `otimizacao-de-tokens`, `testar_sem_usar_pypi`).
* **Local Claude Code skills:** `.claude/skills/` with `status-report` 🆕 (generation of this report), `implement-fixes` 🆕 (fix workflow) and `caveman-commit` 🆕 (compact commit messages) — alongside the existing `new-feature` and `reports-to-memory`.
* **Memory Index:** `.claude/memory/MEMORY.md` with 32 patterns in 3 categories (21 project, 3 reference, 8 feedback).
* **Task reports:** `docs/claude-code/reports/` (65 in total; +15 new: external linter, corrupted i18n keys, staging i18n + dead code + MCP docs, skills, README, co-author, conditional linter report, multilingual EN ARCHITECTURE, co-author in the TUI, MCP hang, install wizard i18n, i18n untranslated/mangled, linter error modal) and `docs/gemini/reports/` (8, none new in this window).
* **Status reports:** `docs/reports/` (12 status reports).
* **Development plans:** 59 files documented in `docs/plans/` (+6 new: external linter, i18n keys, multilingual ARCHITECTURE, MCP hang ×2, linter modal fixes).

---

## **🔄 Distribution Pipeline**

1. **PyPI:** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **GitHub Releases:** PyInstaller → standalone `.exe` → automated upload
3. **GitHub Actions:** `pr-review.yml` workflow + `action.yml`
4. **MCP Server:** `gitpr-mcp` entry point via `pyproject.toml`

---

## **📈 Evolution Since the Previous Report (v0.0.11)**

| Area | v0.0.11 (previous) | v0.0.12 (current) |
|------|-------------------|----------------|
| **GitPR Version** | 0.0.36 | **0.0.37** |
| **Language Version** | v0.0.13 | **v0.0.20** |
| **Hook Scripts Version** | v0.0.2 | **v0.0.3** |
| **AI Providers** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama |
| **Languages** | 5 (en, pt_br, pt_pt, es_es, fr_fr) | 5 languages, 6 dictionaries (es/fr duplicated) |
| **Interface** | CLI + Issues TUI + Chat TUI + MCP Server + Dashboard + PR Publisher TUI | **+ Linter error TUI (`LinterApp`) + `--linter-setup` assistant** |
| **MCP Tools** | 12 tools (handlers inline on the event loop) | **12 tools (handlers offloaded to worker threads via anyio + stdio e2e tests)** |
| **CLI Flags** | 26 flags | **27 flags (+ `--linter-setup`)** |
| **Environment Variables** | 16 vars | **23 vars (+ `OUTPUT_FILE_NAME_LINTER` in DEFAULT_CONFIG (22 keys) + read-only `GITPR_COAUTHOR`)** |
| **Linter** | Regex rules only (local + plugins) | **Regex + Checkstyle bridge (ESLint/PHPCS/Stylelint) with diff-line cross-reference, wizard, TUI and Markdown report** |
| **Commit Messages** | Pure AI message | **+ `Co-Authored-By: Gitpr-cli` trailer (idempotent, hidden from the TUI, opt-out `GITPR_COAUTHOR=false`)** |
| **i18n (keys per file)** | 507 in pt_BR (incomplete parity) | **547 × 6 files with total parity — 0 mangled, 0 untranslated** |
| **Documentation** | 34 topics | **33 canonical topics (29 with complete 5-language coverage) — 1 new (i18n_explanation), 1 removed (caveman-commit → skill), 7 updated topics + ARCHITECTURE with 4 new locales** |
| **Test Suite** | 214 scenarios (13 files) | **264 scenarios (17 files, +50) — first 100% green run on the pt-BR machine** |
| **Commits since the report** | 4 commits | **17 commits** |
| **Merged PRs** | 2 PRs (#111, #114) | **8 PRs (#119, #122, #124, #127, #129, #131, #133, #135) + 2 PR_DESCs without reference (i18n mangled, linter modal)** |
| **Memory Index** | 27 patterns | **32 patterns in 3 categories (project/reference/feedback)** |
| **Task Reports** | 50 claude-code (+4 in the window) | **65 claude-code (+15) and 8 gemini (0 new)** |
| **Development Plans** | 11+ | **59 (+6 in the window)** |

---

## **🚧 Next Steps**

* **91 i18n keys still missing:** Used in code via `__()` but missing from the dictionaries (MCP tool descriptions, TUI strings like "❌ Merge Conflict", updater/ai_providers/github_api messages) — they fall back to English. AI prompts should remain in EN by design.
* **`missing == 0` guard in test_i18n.py:** Extend the tests with an assertion that fails when new `__()` calls without a dictionary entry are introduced (today it only guards parity, mangled and identity keys).
* **`develop_natan` → `main` merge:** Publish the `__lang_version__` v0.0.20 bump and the TUI fixes to users — the fixed `langs/*.json` are already on `main` via `e2f0fa0`; the marker is what triggers the OTA refresh.
* **Manual sanity check of the real TUI flow:** A manual end-to-end test of the PR Publisher with a diff that breaks the linter (headless tests mock git/AI).
* **PR Publisher tests:** Remaining coverage for `pr_publish_app.py` and `github_api.py` (progress: `test_pr_publish_linter_modal.py` covers the linter modal flow).
* **Anthropic Claude provider:** Direct support for the Claude API (`claude-sonnet-5`).
* **ASCII/Textual charts in the Dashboard:** Add time histograms and token trend charts to the metrics TUI.
* **Release pipeline on GitHub Actions:** Full automation of the PyInstaller build and asset upload to GitHub Releases.
* **Local `--init` command:** Seed of `.gitpr/conf/` with local configuration templates (smart-excludes, linter, etc.).
* **More providers:** Direct OpenAI, additional local providers.
* **Subprocess and timeout hardening:** Replace the `shell=True` f-string of `_run_external_linter` with a shlex/argv list; limit the AI SDK timeouts in `ai_providers.py` (~600s default); apply the DNS-bounding pattern to the urllib calls of `i18n.py`/`ai_providers.py`.
* **External linters in full-file mode:** Support for `external_linters` in `--input` and `file` filtering in the Checkstyle XML (today the cross-reference only uses lines).
* **Document `LINTER_PRESETS_VERSION`:** Presets version marker in `.env` (Version Marker pattern).
* **Broken doc references in HELP_MAP:** `chat-interativo.md` (actual file: `understanding_chat_functionality.md`) and `metricas_analytics_dashboard.md` (actual: `metricas-telemetria.md`) — small fix.
* **Outdated CLAUDE.md:** Still declares version 0.0.30 (actual: 0.0.37) and mentions the `--publish` flag that no longer exists — ARCHITECTURE.md is the most accurate reference.
* **Legacy i18n scripts:** `scripts/` one-offs (`fix_pt_br.py`, `fix_pt_br_pass2.py`, `final_fix.py`, `_temp_check_i18n.py`, `generate_lang_files.py`) contain inert mangled-key tables — candidates for removal/archiving.

---

**Report generated on:** 2026-08-19  
**Branch:** `develop_natan`  
**Author:** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
