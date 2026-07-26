# GitPR CLI — Project Status Report — v0.0.29 (2026-07-25)

---

## Overview

**GitPR** is an advanced CLI tool for Git workflow automation powered by AI (Google Gemini / DeepSeek / Ollama). It acts as an intelligent local assistant that performs Code Reviews, generates Pull Request descriptions, creates semantic commit messages, audits technical debt, and injects best practices into the developer workflow (**Shift Left** approach).

**New in this version:** MCP Prompts with multilingual template system (35 files in 5 languages), MCP Tool Annotations (`readOnlyHint`, `destructiveHint`, `idempotentHint`) for better IDE integration, Thinking Words expanded to 201 entries with full phrases, and Spinner with adaptive speed for long phrases.

- **Current version:** 0.0.29
- **Distribution:** PyPI (`pip install gitpr-cli`) + GitHub Releases (standalone binary)
- **Website:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Repository:** [github.com/natanfiuza/gitpr](https://github.com/natanfiuza/gitpr)
- **License:** LGPL-2.1
- **Supported languages:** en_us, pt_br, pt_pt, es_es, fr_fr (5 languages)

---

## Architecture & Core Libraries

- **Language:** Python >= 3.10
- **CLI Framework:** Click (commands, flags, terminal formatting)
- **UI/Terminal:** Textual — TUI for interactive chat, issue editing, and help screen
- **Cryptography:** Fernet symmetric encryption for local API key protection
- **Configuration:** dotenv, PyYAML (for the static linter)
- **AI Providers:** Google GenAI SDK (gemini-2.5-flash), OpenAI SDK (DeepSeek), OpenAI SDK (Ollama local)
- **MCP:** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 (Official Anthropic SDK for Model Context Protocol) — **Tool Annotations and Prompts refactored in v0.0.29**
- **Testing:** Pytest + unittest.mock (8 test files, 165+ scenarios)
- **Packaging:** PyInstaller (standalone binary) + setuptools/build (PyPI)
- **CI/CD:** GitHub Actions (`pr-review.yml`) + `action.yml` for pipeline execution

---

## Implemented Modules

### 1. Core & Git Operations (`src/core.py`)

Structured LLM communication requesting strictly JSON responses. Map-Reduce architecture for huge diffs (~90k+ tokens) with automatic batching via `split_diff_into_chunks`. Token estimation via `len() // 4` heuristic (`estimate_token_count()`). Native Git optimization with `-U1`, `-w`, `-M`, `-B` flags in `get_git_diff` and `get_git_full_diff`. Pre-Save debug flag (`--pre-save`) to inspect full AI payloads (system instruction + prompt) as JSON. Smart Excludes — remote pathspec filter (`gitpr.smart-excludes.json`) automatically downloaded from GitHub with versioning (`SMART_EXCLUDES_VERSION`), excluding lock files, build artifacts, and binary assets to reduce token usage.

### 2. CLI Interface & Setup (`src/main.py`, `src/config.py`)

First-run detection with interactive API key, provider, and language setup saved to `~/.gitpr/.env`. Command routing for all flags (`--commit`, `--review`, `--fullreview`, `--linter`, `--skill`, `--issue`, `--blame`, `--chat`, `--mcp`, `--lang`, `--provider`, `--pre-save`). Contextual help (`-h --flag`) with language-aware documentation links. `--lang` and `--provider` flags for per-execution overrides. `--mcp` flag starts the MCP server on stdio transport for editor integration — **10 annotated tools + 15 resources + 7 prompts** 🆕.

### 3. Static Analysis Engine (`src/linter_engine.py`)

Offline linter analyzing only added lines (`+`) from `git diff`. Reads `.gitpr.linter.yml` with regex rules, comment ignoring, and path exclusion via fnmatch. Multilingual templates available in 5 languages.

### 4. Security Vault (`src/security.py`)

Fernet key generation (`secret.key`), `encrypt_data` and `decrypt_data` functions. GitHub PAT stored encrypted for issue creation via REST API.

### 5. Auto-Updater (`src/updater.py`)

Hot-swap binary updates from GitHub Releases API with rollback capability. Daily cache to avoid repeated checks. Connection verification via socket `8.8.8.8:53` before network operations. Asset versioning: `__lang_version__`, `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`.

### 6. Interactive Chat (`src/ui/chat_app.py`)

Full TUI built with Textual — message history, multi-line input, status bar with visible bindings. Per-branch memory (`src/chat_memory.py`) with conversation persistence. Slash commands: `/explain`, `/tests`, `/optimize`, `/clear`. Auto-patching (F5), diff refresh (F2), session export (F6). Multilingual chat commands (`chat_commands.{lang}.json`).

### 7. Internationalization — i18n (`src/i18n.py`)

Laravel-inspired `__()` helper with named placeholders (`{count}`, `{file}`, etc.). Auto-detection of OS language on first run. 5 languages: en_us (default/fallback), pt_br, pt_pt, es_es, fr_fr. Versioned language packs (`__lang_version__`). Full coverage: CLI output, Click help, linter alerts, system messages, Git Hooks, spinner, chat, MCP tools, MCP resources, MCP prompts, and MCP annotations. **364 keys per language** (+42 MCP keys in v0.0.29).

### 8. Animated Spinner (`src/spinner.py`)

Braille characters + "thinking words" displayed during AI calls via background thread. Progressive word reveal with random characters, followed by dot cycle (`. .. ...`). Random 10-color palette per word. **Adaptive Speed 🆕:** Long phrases (36+ characters) revealed faster (1 frame/letter, 0.04s) to display the full text before switching words. Short words keep original speed. Multilingual thinking words loaded from language-specific templates (`gitpr.thinking-words.{lang}.md`) with versioning (`THINKING_WORDS_VERSION`). **201 entries per language 🆕:** Expanded list with creative phrases merged from `words_happy.md` (84 original words + 117 phrases).

### 9. AI Providers (`src/ai_providers.py`)

3 supported providers:
- **Google Gemini:** `gemini-2.5-flash` (primary) / `gemini-2.5-flash-lite` (secondary)
- **DeepSeek:** `deepseek-chat` (primary and secondary)
- **Ollama:** Any local OpenAI-compatible model

Multi-model architecture with automatic fallback. JSON mode for all providers (`response_mime_type` / `response_format`). Deterministic parameters: temperature 0.0, top_p 0.1.

### 10. Smart Cache (`src/cache.py`)

MD5-based exact hash of code (diff) + instructions. Per-repository caching with `repo` field for multi-project filtering. Instant millisecond returns from `~/.gitpr/cache/prompts/`.

### 11. Issue Engine & TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)

3 context engines: new code issue (`gitpr -is`), epic/release issue (`gitpr -is -ht` using Git Log + PR Cache), technical debt issue (`gitpr -is -b file:lines` via `git blame`). TUI editor with syntax highlight, F2 save local, F3 publish via GitHub API. Help screen (F1) with shortcuts.

### 12. Code Archaeologist (`src/blame_engine.py`)

Git Blame + AI tracing with max depth of 4 parent commits. Secondary model classifies commits as `ORIGIN` or `REFACTORING`. Advanced model generates consolidated final analysis. Color-coded terminal output (green=origin, yellow=refactoring) + Markdown report.

### 13. Skills & Templates System

Local templates: `.gitpr.commit.md`, `.gitpr.pr.md`, `.gitpr.review.md`, `.gitpr.filereview.md`, `.gitpr.issue.md`, `.gitpr.blame.md` as customizable System Instructions. Remote templates downloaded from GitHub via `--skill` (never overwrites existing local files). 5-language availability with intelligent fallback (`get_skill_context()`). Clean `.gitpr/skill/` folder organization.

### 14. Map-Reduce for Huge Diffs

Automatic activation when diff exceeds ~90k estimated tokens. Safe splitting at regex delimiter `(^diff --git a/)`. Rate limiting with `time.sleep(1)` between Map batches. Dedicated documentation page in 5 languages (`docs/map-reduce-diff.{lang}.md`) linked in console during processing. Console progress with batch count and doc link.

### 15. CI/CD Integration

GitHub Actions workflow `pr-review.yml` for automated PR review. `action.yml` for use as GitHub Action in external pipelines. Local Git Hooks (`pre-commit` + `prepare-commit-msg`) installable via `--installhooks`.

### 16. MCP Server — Editor & IDE Integration (`src/mcp_server.py`) 🆕

**10 MCP Tools with Annotations 🆕:** `get_git_context`, `analyze_diff`, `get_full_diff`, `generate_commit_message`, `review_code`, `full_review`, `generate_pr_description`, `run_linter`, `analyze_blame`, `generate_issue` — all with `ToolAnnotations` (`readOnlyHint`, `destructiveHint`, `idempotentHint`).
- **3 read-only tools** (`readOnlyHint=True`, `idempotentHint=True`): `get_git_context`, `analyze_diff`, `run_linter`.
- **7 tools with side effects** (`readOnlyHint=False`, `destructiveHint=False`): network calls (AI APIs, git fetch).
**15 MCP Resources 🆕:** 7 skill templates (`skill://pr`, `skill://commit`, etc.) + linter config (`linter://config`) + 7 prompt templates (`prompt://review`, `prompt://commit`, etc.) + `prompt://list`.
**7 MCP Prompts with Templates 🆕:** Content externalized in 35 template files (7 prompts × 5 languages) in the `templates/gitpr.prompt.*.md` directory with automatic language fallback.
**stdio transport:** JSON-RPC 2.0 communication — standard for local CLI tools.
**Output isolation:** Monkey-patching system that redirects all terminal output (banners, spinners, colors) to stderr, keeping the stdout channel clean for the MCP protocol.
**`gitpr-mcp` command:** Dedicated entry point registered in `pyproject.toml`.
**`--mcp` flag:** Alias via main CLI (`gitpr --mcp`).

### 17. MCP Installer (`gitpr-mcp --install`) 🆕

**6 Supported Editors:** VS Code (`.vscode/mcp.json`), Cursor (`.cursor/mcp.json`), Claude Code (`.mcp.json`), Claude Desktop (global), Zed (global). **Auto mode:** Automatically detects configured editors and installs for all of them. **Smart merge:** Adds the GitPR server without removing existing servers — idempotent and safe. **Directory creation:** Automatically creates `.vscode/`, `.cursor/` or the global directory if they don't exist.

---

## Testing & Quality

| Test File | Scenarios | Focus |
|---|---|---|
| `tests/test_core.py` | 25+ | Core flows, git diff, PR generation |
| `tests/test_chat_backend.py` | 30+ | Chat memory, persistence, commands |
| `tests/test_skill_command.py` | 10+ | Template download and validation |
| `tests/test_pre_save.py` | 10+ | --pre-save flag and JSON payload |
| `tests/test_smart_excludes.py` | 14+ | Smart pathspec filter |
| `tests/test_thinking_words.py` | 10+ | Thinking words loading and parsing |
| `tests/test_mcp_prompts.py` 🆕 | 11 | Prompt functions, PROMPT_FILES, _read_prompt_file(), language fallback |
| `tests/test_mcp_server.py` | 33 | MCP tools, resources, output patching, safe-call wrapper |

---

## i18n & Documentation

- **364 translation keys** per language (5 languages = 1,820 translations)
- **Full documentation in 5 languages:** 22 topics × 5 languages = 110+ documentation pages
- **New documentation 🆕:** `docs/mcp-prompts.md` (template system), `docs/mcp-annotations.md` (tool annotations) — each with 4 translations
- **MCP Templates 🆕:** 35 prompt files (`gitpr.prompt.*.md`) in 5 languages in the `templates/` directory
- **Thinking Words 🆕:** 201 entries per language (84 words + 117 phrases) in `templates/gitpr.thinking-words.{lang}.md`
- **Development plans:** 8 plans in `docs/plans/`
- **Claude Code reports:** 12+ task reports in `docs/claude-code/reports/develop_natan/`
- **Official website:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Synchronized READMEs:** Relative links converted to absolute (PyPI compatible). Updated with MCP Prompts and MCP Tool Annotations in all 5 languages 🆕

---

## Distribution Pipeline

1. **PyPI:** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **GitHub Releases:** PyInstaller → `.exe` standalone → upload via workflow
3. **GitHub Actions:** Automated PR Review with `action.yml`
4. **MCP:** `gitpr-mcp` registered as entry point in `pyproject.toml` → auto-installed with `pip install`

---

## Evolution Since Previous Report (v0.0.3)

| Area | v0.0.3 (previous) | v0.0.4 (current) |
|---|---|---|
| **AI Providers** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama |
| **Languages** | 5 (en, pt_br, pt_pt, es_es, fr_fr) | 5 (en, pt_br, pt_pt, es_es, fr_fr) |
| **Interface** | CLI + TUI Issues + Chat TUI + MCP Server | CLI + TUI Issues + Chat TUI + MCP Server |
| **MCP Tools** | 10 tools (no annotations) | **10 tools with ToolAnnotations** |
| **MCP Resources** | 7 (skills + linter) | **15 (skills + linter + prompts)** |
| **MCP Prompts** | 7 prompts (hardcoded) | **7 prompts with templates (35 files in 5 languages)** |
| **MCP Prompt Resources** | — | **8 `prompt://` resources** |
| **MCP Docs** | `mcp-integration.md` | **+ `mcp-prompts.md` + `mcp-annotations.md` (5 languages each)** |
| **Thinking Words** | ~15 words (fallback) | **201 entries/language (words + phrases)** |
| **Spinner** | Fixed speed | **Adaptive speed (long phrases ~2.2s)** |
| **Tests** | 8 files (160+ scenarios) | **8 files (165+ scenarios)** |
| **Documentation** | 100+ pages | **110+ pages** |
| **READMEs** | Links MCP Integration + Prompts | **+ MCP Tool Annotations (all 5 languages)** |
| **Version** | 0.0.28 | **0.0.29** |

---

## Next Steps

- **MCP integration tests:** End-to-end coverage of the MCP server with a test client
- **More providers:** Claude API, direct OpenAI, additional local providers
- **Metrics & analytics:** Usage dashboard for teams
- **Plugin system:** Extensibility for linter rules and custom prompts
- **MCP SDK v2 Migration:** Monitor v2.x SDK stabilization (stateless mode, tasks)
- **Automated GitHub Release:** Full CI/CD pipeline for build + release

---

**Report generated:** 2026-07-25
**Branch:** `develop_natan`
**Author:** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))

---

[← Contributing](/contribuicao) &nbsp;|&nbsp; [Home →](/index)
