# **🚀 Project Status Report: GitPR CLI — v0.0.30 (2026-07-26)**

## **📌 Overview**

**GitPR** is an advanced CLI (Command Line Interface) tool for automating Git processes using Artificial Intelligence (Google Gemini / DeepSeek / Ollama). Its main goal is to act as a local intelligent assistant that performs Code Reviews, generates Pull Requests, semantic commit messages, audits technical debt, and injects best practices into the developer workflow (Shift Left).

**What's new in this version:** Offline local Metrics and Telemetry system with CSV/JSON export and interactive TUI dashboard, new Git Hooks for behavioral telemetry (post-checkout, pre-push, post-merge), Thinking Words expanded to 263 entries with "Sussing" and "Cerebrating" phrases, flickering fix in the spinner (`\033[K`), and translation of all comments/docblocks to English.

- **Current version:** 0.0.30
- **Published on:** PyPI (`pip install gitpr-cli`) + GitHub Releases (standalone binary)
- **Website:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Repository:** [github.com/natanfiuza/gitpr](https://github.com/natanfiuza/gitpr)
- **License:** LGPL-2.1
- **Supported languages:** en_us, pt_br, pt_pt, es_es, fr_fr (5 languages)

## **🏗️ Architecture and Base Libraries**

* **Language:** Python >= 3.10
* **CLI Framework:** Click (for commands, flags, and terminal formatting).
* **UI/Terminal:** Textual — TUI (Text User Interface) for interactive chat, issue editing, help screen, and metrics dashboard.
* **Cryptography:** cryptography.fernet for local API key protection.
* **Configuration:** dotenv, pyyaml (for the static linter).
* **AI Providers:** Integration via Google GenAI official SDK (gemini-2.5-flash), OpenAI SDK (DeepSeek), and OpenAI SDK (local Ollama).
* **MCP:** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 (Anthropic official SDK for Model Context Protocol) — **Tool Annotations, Prompts with templates, and prompt:// resources in v0.0.30**.
* **Testing:** Pytest + unittest.mock (8 test files, 165+ scenarios).
* **Packaging:** PyInstaller (standalone binary) + setuptools/build (PyPI).
* **CI/CD:** GitHub Actions (`pr-review.yml`) + `action.yml` for pipeline execution.

## **🧩 Implemented Modules and File Architecture**

### **1. Core and Git Operations (`src/core.py`)**

* **Structured Generation:** Communicates with the LLM requesting strictly JSON output.
* **Map-Reduce (Giant Diffs):** When the diff exceeds ~90k tokens, automatically splits into per-file batches (`split_diff_into_chunks`), processes each chunk (Map), and unifies the summaries (Reduce) while maintaining the architecture's voice tone.
* **Token Estimation:** Lightweight heuristic `len() // 4` via `estimate_token_count()`.
* **Native Git Optimization:** Flags `-U1`, `-w`, `-M`, `-B` in `get_git_diff` and `get_git_full_diff` commands to reduce unnecessary context.
* **Pre-Save (`--pre-save`):** Hidden debug flag that saves the full payload (system instruction + prompt) in JSON before each AI call.
* **Smart Excludes:** Remote intelligent pathspec filter (`gitpr.smart-excludes.json`) — downloaded from GitHub and auto-updated with versioning (`SMART_EXCLUDES_VERSION`), excluding irrelevant files (lock files, build artifacts, binary assets) to reduce tokens.
* **Fire-and-forget Metrics 🆕:** Injection of `log_command_metric()` in all flows (single-chunk and map-reduce) with lazy imports to avoid circular imports.

### **2. CLI Interface and Setup (`src/main.py` and `src/config.py`)**

* **Initial Setup:** Detects first run, creates the `~/.gitpr/` folder, and interactively prompts for API keys, preferences, and language, saving them to a `.env` file.
* **Command Routing:** Manages all flags (`--commit`, `--review`, `--fullreview`, `--linter`, `--skill`, `--issue`, `--blame`, `--chat`, `--mcp`, `--metrics`, `--export`, `--purge`, `--dashboard`, `--lang`, `--provider`, `--pre-save`).
* **Contextual Help:** `-h --flag` displays feature-specific documentation with a direct (language-aware) link to GitHub.
* **--lang:** Forces the interface language for the current execution without persisting the change.
* **--provider:** Forces the AI provider (`gemini`, `deepseek`, `ollama`) for the current execution.
* **--mcp:** Starts the MCP server on stdio transport for editor integration — **10 annotated tools + 15 resources + 7 prompts**.
* **--metrics 🆕:** Local telemetry system with sub-options: `--export` (CSV+JSON), `--purge` (cleanup), `--dashboard` (interactive TUI), `--hook-event` (internal use by git hooks).

### **3. Static Analysis / Linter Engine (`src/linter_engine.py`)**

* **Offline Linter:** Statically analyzes added lines (`+`) in the git diff without spending AI quota.
* **YAML Rules:** Reads the local `.gitpr.linter.yml` file (created via `--skill`). Supports validation regex, comment ignoring, and specific directory ignoring (using fnmatch).
* **Multilingual Template:** Linter templates available in 5 languages.

### **4. Security and Vault (`src/security.py`)**

* **Cryptography:** Generates a master key `secret.key` in the `~/.gitpr/` folder.
* **Functions:** `encrypt_data` and `decrypt_data` to ensure tokens and keys are not stored in plain text.
* **GitHub PAT:** GitHub personal access token stored encrypted for issue creation via REST API.

### **5. Auto-Updater (`src/updater.py`)**

* **Hot-Swap:** Checks the GitHub Releases API for the latest version. If there's a mismatch, downloads the compiled binary, renames the current executable, and replaces it without disrupting ongoing execution (with rollback capability).
* **Daily Cache:** Prevents repeated checks on the same day.
* **Connection Check:** Socket `8.8.8.8:53` before any network operation.
* **Asset Versioning:** `__lang_version__` (v0.0.8), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION` for template and translation update control.

### **6. Interactive Chat Interface (`src/ui/chat_app.py`)**

* **Full TUI:** Built with Textual — message history, multi-line input, status bar with visible bindings.
* **Per-Branch Memory (`src/chat_memory.py`):** Conversation history persisted per branch, allowing continuity between sessions.
* **Slash Commands:** `/explain`, `/tests`, `/optimize`, `/clear` — shortcuts for common pair programming actions.
* **Auto-Patching (F5):** Extracts code blocks suggested by the AI and exports to a patch file for easy application.
* **Diff Refresh (F2):** Reloads the current `git diff` without restarting the session.
* **Session Export (F6):** Saves the full chat history for documentation.
* **Multilingual Commands:** `chat_commands.{lang}.json` files with translations of slash commands.

### **7. Internationalization — i18n (`src/i18n.py`)**

* **Laravel-Inspired System:** `__()` function with support for named placeholders (`{count}`, `{file}`, etc.).
* **Automatic Detection:** Detects OS language on first run and saves it to `GITPR_LANG`.
* **5 Languages:** en_us (default/fallback), pt_br, pt_pt, es_es, fr_fr.
* **English Fallback:** If a translation is missing, displays the English text directly.
* **Versioned Files:** `__lang_version__` (v0.0.8) controls updating language packages (`langs/*.json`).
* **Coverage:** All interface messages, Click help, linter alerts, system messages, Git Hooks, spinner, chat, MCP tools, MCP resources, MCP prompts, MCP annotations, metrics, and TUI dashboard translated.
* **447 keys per language 🆕** (+83 keys in this version: 16 CLI metrics + 20 TUI dashboard + 47 incremental).

### **8. Animated Spinner (`src/spinner.py`)**

* **Braille + Thinking Words:** Background thread during AI calls displaying braille characters with "thinking" words.
* **Progressive Reveal:** Words revealed letter by letter with random characters, followed by a dot cycle (`. .. ...`).
* **Random Colors:** 10-color palette for each word.
* **Adaptive Speed:** Long phrases (36+ characters) revealed faster (1 frame/letter, 0.04s) to display the full text before switching words. Short words maintain original speed.
* **Flickering Fix 🆕:** Replaced `ljust(70)` with ANSI `\033[K` (clear to end of line) to eliminate residue from long phrases when switching to short words.
* **Multilingual:** Thinking Words loaded from language-specific templates (`gitpr.thinking-words.{lang}.md`), with versioning (`THINKING_WORDS_VERSION`).
* **263 entries per language 🆕:** Expanded with "Sussing" (31) and "Cerebrating" (31) phrases — 263 total words/phrases per language.

### **9. AI Providers (`src/ai_providers.py`)**

* **3 Supported Providers:**
  * **Google Gemini:** `gemini-2.5-flash` (primary) / `gemini-2.5-flash-lite` (secondary)
  * **DeepSeek:** `deepseek-chat` (primary and secondary)
  * **Ollama:** Any local model compatible with the OpenAI API
* **Multi-Model Architecture:** Automatic fallback between providers in case of failure.
* **JSON Mode:** All providers configured for structured output (`response_mime_type` / `response_format`).
* **Deterministic Parameters:** Temperature 0.0, top_p 0.1.
* **Telemetry Injection:** Usage metadata (`_telemetry_meta`) silently injected into responses to feed the metrics system.

### **10. Smart Cache (`src/cache.py`)**

* **MD5:** Exact hash of the code (diff) + instructions to identify identical calls.
* **Per-Repository Cache:** JSON includes a `repo` field for multi-project filtering.
* **Quota Savings:** Returns results in milliseconds from the local cache (`~/.gitpr/cache/prompts/`).
* **Telemetry Metadata:** `meta_raw` field with token count saved alongside the cache.

### **11. Issue Engine and TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Context Engines:**
  * **New Code Issue (`gitpr -is`):** Reads the current `git diff`.
  * **Epic/Release Issue (`gitpr -is -ht`):** Reads the full branch history (Git Log + PR Cache).
  * **Technical Debt Issue (`gitpr -is -b file:lines`):** Timeline via `git blame`.
* **Interactive TUI:** Issue editor with syntax highlighting, bindings to save locally (F2) or send via GitHub API (F3).
* **Help Screen (F1):** Modal with shortcuts and instructions.

### **12. Code Archeologist (`src/blame_engine.py`)**

* **Git Blame + AI:** Traces the origin of business rules with a maximum depth of 4 parent commits.
* **Classification:** Secondary model classifies commits as `ORIGIN` or `REFACTORING`.
* **Executive Summary:** Advanced model generates a consolidated final analysis.
* **Output:** Color-coded terminal (green=origin, yellow=refactoring) + Markdown report.

### **13. Skills and Templates System**

* **Local Templates:** `.gitpr.commit.md`, `.gitpr.pr.md`, `.gitpr.review.md`, `.gitpr.filereview.md`, `.gitpr.issue.md`, `.gitpr.blame.md` as customizable *System Instructions*.
* **Remote Templates:** Downloaded from GitHub via `--skill` (never overwrites existing local files).
* **Multilingual:** Templates available in 5 languages with intelligent fallback (`get_skill_context()`).
* **MCP Prompt Templates 🆕:** 35 files (`gitpr.prompt.*.md`) in 5 languages in the `templates/` directory.

### **14. Map-Reduce Optimization for Giant Diffs**

* **Automatic Activation:** When the diff exceeds ~90k estimated tokens.
* **Safe Split:** Breaks at the regex delimiter `(^diff --git a/)` to avoid corrupting syntax.
* **Rate Limiting:** `time.sleep(1)` between Map batches.
* **Documentation:** Dedicated page in 5 languages (`docs/map-reduce-diff.{lang}.md`) linked in the console during processing.
* **Console Progress:** Displays batch count and link to documentation.

### **15. CI/CD Integration**

* **GitHub Actions:** `pr-review.yml` workflow for automatic PR review.
* **Action Definition:** `action.yml` for use as a GitHub Action in external pipelines.
* **Local Git Hooks:** `pre-commit` (linter), `prepare-commit-msg` (AI message generation), `post-checkout`, `pre-push`, `post-merge` (behavioral telemetry 🆕) installable via `--installhooks`.

### **16. MCP Server — Editor and IDE Integration (`src/mcp_server.py`)**

* **10 MCP Tools with Annotations:** `get_git_context`, `analyze_diff`, `get_full_diff`, `generate_commit_message`, `review_code`, `full_review`, `generate_pr_description`, `run_linter`, `analyze_blame`, `generate_issue` — all with `ToolAnnotations` (`readOnlyHint`, `destructiveHint`, `idempotentHint`).
  * **3 read-only tools** (`readOnlyHint=True`, `idempotentHint=True`): `get_git_context`, `analyze_diff`, `run_linter`.
  * **7 tools with side effects** (`readOnlyHint=False`, `destructiveHint=False`): network calls (AI APIs, git fetch).
* **15 MCP Resources:** 7 skill templates (`skill://pr`, `skill://commit`, etc.) + linter config (`linter://config`) + 7 prompt templates (`prompt://review`, `prompt://commit`, etc.) + `prompt://list`.
* **7 MCP Prompts with Templates:** Content externalized in 35 template files (7 prompts × 5 languages) in the `templates/gitpr.prompt.*.md` directory with automatic language fallback.
* **stdio Transport:** Communication via JSON-RPC 2.0 — standard for local CLI tools.
* **Output Isolation:** Monkey-patching system that redirects all terminal output (banners, spinners, colors) to stderr, keeping the stdout channel clean for the MCP protocol.
* **`gitpr-mcp` Command:** Dedicated entry point registered in `pyproject.toml`.
* **`--mcp` Flag:** Alias via the main CLI (`gitpr --mcp`).

### **17. MCP Installer (`gitpr-mcp --install`)**

* **6 Supported Editors:** VS Code (`.vscode/mcp.json`), Cursor (`.cursor/mcp.json`), Claude Code (`.mcp.json`), Claude Desktop (global), Zed (global).
* **Auto Mode:** Automatically detects which editors are configured and installs for all.
* **Intelligent Merge:** Adds the GitPR server without removing existing servers — idempotent and safe.
* **Directory Creation:** Automatically creates `.vscode/`, `.cursor/` or the global directory if they don't exist.

### **18. Local Metrics and Telemetry (`src/metrics.py`, `src/ui/metrics_app.py`)** 🆕

* **Fire-and-forget Collection:** Each CLI command generates an asynchronous JSON event in `~/.gitpr/metrics/{owner}/{branch}/` with timestamp, command, status, provider, tokens, duration, repo, and branch.
* **Enriched Payload:** Additional fields such as `cache_hit`, `map_reduce_triggered`, `linter_errors`, `linter_warnings` for command-specific context.
* **CSV/JSON Export:** `gitpr --metrics --export` consolidates all unexported events with `click.progressbar()`, generating `gitpr_metrics_YYYY-MM-DD.csv` and `.json`.
* **Safe Cleanup:** `gitpr --metrics --purge` removes files after user confirmation.
* **TUI Dashboard:** `gitpr --metrics --dashboard` opens an interactive Textual interface with DataTable (last 100 events), aggregated summary bar (total, errors, tokens, top commands/providers), and F5 (refresh) / Esc (exit) bindings.
* **Telemetry Git Hooks:** `post-checkout` (branch switch), `pre-push` (deliveries), `post-merge` (integration) — automatically installed via `--installhooks`.
* **100% local and anonymous:** No data leaves the machine. Events contain usage metadata, never file content or diffs.

## **📊 Tests and Quality**

| Test File | Scenarios | Focus |
|------------------|----------|------|
| `tests/test_core.py` | 25+ | Core flows, git diff, PR generation |
| `tests/test_chat_backend.py` | 30+ | Chat memory, persistence, commands |
| `tests/test_skill_command.py` | 10+ | Template download and validation |
| `tests/test_pre_save.py` | 10+ | --pre-save flag and JSON payload |
| `tests/test_smart_excludes.py` | 14+ | Smart pathspec filter |
| `tests/test_thinking_words.py` | 10+ | Thinking words loading and parsing |
| `tests/test_mcp_prompts.py` | 11 | Prompt functions, PROMPT_FILES, _read_prompt_file(), language fallback |
| `tests/test_mcp_server.py` | 33 | MCP tools, resources, output patching, safe-call wrapper |

## **🌐 Internationalization and Documentation**

* **447 translation keys** per language (5 languages = 2,235 translations) 🆕.
* **Full documentation in 5 languages:** 23 topics × 5 languages = 115+ documentation pages.
* **New documentation 🆕:** `docs/metricas-telemetria.md` in 5 languages (EN, PT-BR, PT-PT, ES, FR).
* **Existing documentation expanded:** `docs/mcp-prompts.md` and `docs/mcp-annotations.md` updated with template system and `prompt://` resources.
* **MCP Templates:** 35 prompt files (`gitpr.prompt.*.md`) in 5 languages in the `templates/` directory.
* **Thinking Words:** 263 entries per language in `templates/gitpr.thinking-words.{lang}.md`.
* **Clean Code 🆕:** All Portuguese comments and docstrings translated to English in `src/metrics.py`, `src/main.py`, and `src/ai_providers.py`.
* **Development Plans:** 8 plans documented in `docs/plans/`.
* **Claude Code Reports:** 13+ task reports in `docs/claude-code/reports/develop_natan/`.
* **Official Site:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
* **Synced READMEs:** Updated with MCP Prompts, MCP Tool Annotations, and Metrics & Telemetry in all 5 languages 🆕.

## **🔄 Distribution Pipeline**

1. **PyPI:** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **GitHub Releases:** PyInstaller → `.exe` standalone → upload via workflow
3. **GitHub Actions:** Automated PR Review with `action.yml`
4. **MCP:** `gitpr-mcp` registered as entry point in `pyproject.toml` → automatically installed with `pip install`

## **📈 Evolution Since Previous Report (v0.0.4)**

| Area | v0.0.4 (previous) | v0.0.5 (current) |
|------|-------------------|-----------------|
| **AI Providers** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama |
| **Languages** | 5 (en, pt_br, pt_pt, es_es, fr_fr) | 5 (en, pt_br, pt_pt, es_es, fr_fr) |
| **Interface** | CLI + TUI Issues + Chat TUI + MCP Server | CLI + TUI Issues + Chat TUI + MCP Server + **TUI Metrics Dashboard** |
| **MCP Tools** | 10 tools with ToolAnnotations | 10 tools with ToolAnnotations |
| **MCP Resources** | 15 (skills + linter + prompts) | 15 (skills + linter + prompts) |
| **MCP Prompts** | 7 prompts with templates (35 files) | 7 prompts with templates (35 files) |
| **Telemetry** | — (only map-reduce event) | **Complete system: collection, export, purge, TUI dashboard** |
| **Git Hooks** | 2 (pre-commit, prepare-commit-msg) | **5 (pre-commit, prepare-commit-msg, post-checkout, pre-push, post-merge)** |
| **CLI Flags** | 16 flags | **21 flags (+ --metrics, --export, --purge, --dashboard, --hook-event)** |
| **Thinking Words** | 201 entries/language | **263 entries/language (+62 Sussing + Cerebrating phrases)** |
| **Spinner** | Adaptive speed | Adaptive speed + **flickering fix (\033[K)** |
| **i18n Keys** | 364 keys/language | **447 keys/language (+83)** |
| **Documentation** | 110+ pages (22 topics) | **115+ pages (23 topics)** |
| **Code** | Mixed PT/EN comments | **All comments in English** |
| **Version** | 0.0.29 | **0.0.30** |
| **Lang Version** | v0.0.7 | **v0.0.8** |

## **🚧 Next Steps**

* **MCP integration tests:** End-to-end coverage of the MCP server with a test client.
* **More providers:** Claude API, direct OpenAI, additional local providers.
* **Plugin system:** Extensibility for custom linter rules and prompts.
* **MCP SDK v2 Migration:** Monitor stabilization of SDK v2.x (stateless mode, tasks).
* **Automated GitHub Release:** Full CI/CD pipeline for build + release.
* **Team metrics dashboard:** Optional HTTP server for browser-based dashboards from exported CSVs.

---

**Report generated on:** 2026-07-26
**Branch:** `develop_natan`
**Author:** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
