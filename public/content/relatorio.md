# GitPR CLI — Project Status Report — v0.0.28 (2026-07-24)

---

## Overview

**GitPR** is an advanced CLI tool for Git workflow automation powered by AI (Google Gemini / DeepSeek / Ollama). It acts as an intelligent local assistant that performs Code Reviews, generates Pull Request descriptions, creates semantic commit messages, audits technical debt, and injects best practices into the developer workflow (**Shift Left** approach).

**New in this version:** **MCP (Model Context Protocol)** integration — GitPR now works as an MCP server, exposing all of its AI capabilities as tools directly inside editors like VS Code, Cursor, and Claude Desktop, without needing a terminal.

- **Current version:** 0.0.28
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
- **MCP:** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 (Official Anthropic SDK for Model Context Protocol) — **NEW in v0.0.28**
- **Testing:** Pytest + unittest.mock (8 test files, 160+ scenarios)
- **Packaging:** PyInstaller (standalone binary) + setuptools/build (PyPI)
- **CI/CD:** GitHub Actions (`pr-review.yml`) + `action.yml` for pipeline execution

---

## Implemented Modules

### 1. Core & Git Operations (`src/core.py`)

Structured LLM communication requesting strictly JSON responses. Map-Reduce architecture for huge diffs (~90k+ tokens) with automatic batching via `split_diff_into_chunks`. Token estimation via `len() // 4` heuristic (`estimate_token_count()`). Native Git optimization with `-U1`, `-w`, `-M`, `-B` flags in `get_git_diff` and `get_git_full_diff`. Pre-Save debug flag (`--pre-save`) to inspect full AI payloads (system instruction + prompt) as JSON. Smart Excludes — remote pathspec filter (`gitpr.smart-excludes.json`) automatically downloaded from GitHub with versioning (`SMART_EXCLUDES_VERSION`), excluding lock files, build artifacts, and binary assets to reduce token usage.

### 2. CLI Interface & Setup (`src/main.py`, `src/config.py`)

First-run detection with interactive API key, provider, and language setup saved to `~/.gitpr/.env`. Command routing for all flags (`--commit`, `--review`, `--fullreview`, `--linter`, `--skill`, `--issue`, `--blame`, `--chat`, `--mcp`, `--lang`, `--provider`, `--pre-save`). Contextual help (`-h --flag`) with language-aware documentation links. `--lang` and `--provider` flags for per-execution overrides. `--mcp` flag starts the MCP server on stdio transport for editor integration — **NEW in v0.0.28**.

### 3. Static Analysis Engine (`src/linter_engine.py`)

Offline linter analyzing only added lines (`+`) from `git diff`. Reads `.gitpr.linter.yml` with regex rules, comment ignoring, and path exclusion via fnmatch. Multilingual templates available in 5 languages.

### 4. Security Vault (`src/security.py`)

Fernet key generation (`secret.key`), `encrypt_data` and `decrypt_data` functions. GitHub PAT stored encrypted for issue creation via REST API.

### 5. Auto-Updater (`src/updater.py`)

Hot-swap binary updates from GitHub Releases API with rollback capability. Daily cache to avoid repeated checks. Connection verification via socket `8.8.8.8:53` before network operations. Asset versioning: `__lang_version__`, `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`.

### 6. Interactive Chat (`src/ui/chat_app.py`)

Full TUI built with Textual — message history, multi-line input, status bar with visible bindings. Per-branch memory (`src/chat_memory.py`) with conversation persistence. Slash commands: `/explain`, `/tests`, `/optimize`, `/clear`. Auto-patching (F5), diff refresh (F2), session export (F6). Multilingual chat commands (`chat_commands.{lang}.json`).

### 7. Internationalization — i18n (`src/i18n.py`)

Laravel-inspired `__()` helper with named placeholders (`{count}`, `{file}`, etc.). Auto-detection of OS language on first run. 5 languages: en_us (default/fallback), pt_br, pt_pt, es_es, fr_fr. Versioned language packs (`__lang_version__`). Full coverage: CLI output, Click help, linter alerts, system messages, Git Hooks, spinner, chat, **and MCP**. **364 keys per language** — **NEW in v0.0.28** (+42 MCP keys).

### 8. Animated Spinner (`src/spinner.py`)

Braille characters + "thinking words" displayed during AI calls via background thread. Progressive word reveal with random characters, followed by dot cycle (`. .. ...`). Random 10-color palette per word. Multilingual thinking words loaded from language-specific templates (`gitpr.thinking-words.{lang}.md`) with versioning (`THINKING_WORDS_VERSION`).

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

**10 MCP Tools:** `get_git_context`, `analyze_diff`, `get_full_diff`, `generate_commit_message`, `review_code`, `full_review`, `generate_pr_description`, `run_linter`, `analyze_blame`, `generate_issue`. **7 MCP Resources:** Skill templates (`skill://pr`, `skill://commit`, etc.) + linter config (`linter://config`). **stdio transport:** JSON-RPC 2.0 communication — standard for local CLI tools. **Output isolation:** Monkey-patching system that redirects all terminal output (banners, spinners, colors) to stderr, keeping the stdout channel clean for the MCP protocol. **`gitpr-mcp` command:** Dedicated entry point registered in `pyproject.toml`. **`--mcp` flag:** Alias via main CLI (`gitpr --mcp`).

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
| `tests/test_mcp_server.py` 🆕 | 33 | MCP tools, resources, output patching, safe-call wrapper |

---

## i18n & Documentation

- **364 translation keys** per language (5 languages = 1,820 translations)
- **Full documentation in 5 languages:** 20 topics × 5 languages = 100+ documentation pages
- **New MCP documentation:** `docs/mcp-integration.md` + 4 translations (PT-BR, PT-PT, ES, FR)
- **Development plans:** 7 plans in `docs/plans/`
- **Claude Code reports:** 11+ task reports in `docs/claude-code/reports/develop_natan/`
- **Official website:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Synchronized READMEs:** Relative links converted to absolute (PyPI compatible)

---

## Distribution Pipeline

1. **PyPI:** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **GitHub Releases:** PyInstaller → `.exe` standalone → upload via workflow
3. **GitHub Actions:** Automated PR Review with `action.yml`
4. **MCP:** `gitpr-mcp` registered as entry point in `pyproject.toml` → auto-installed with `pip install` 🆕

---

## Evolution Since Previous Report (v0.0.2)

| Area | v0.0.2 (previous) | v0.0.3 (current) |
|---|---|---|
| **AI Providers** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama |
| **Languages** | 5 (en, pt_br, pt_pt, es_es, fr_fr) | 5 (en, pt_br, pt_pt, es_es, fr_fr) |
| **Interface** | CLI + TUI Issues + Chat TUI | CLI + TUI Issues + Chat TUI + **MCP Server** |
| **MCP (Model Context Protocol)** | — (planned) | **Full MCP server with 10 tools + 7 resources** |
| **MCP Installer** | — | **`gitpr-mcp --install` for 6 editors** |
| **Editor Integration** | — (terminal only) | **VS Code, Cursor, Claude Code, Claude Desktop, Zed** |
| **MCP Documentation** | — | **5 languages (EN, PT-BR, PT-PT, ES, FR)** |
| **i18n Keys** | 322 keys/language | **364 keys/language (+42 MCP)** |
| **Tests** | 7 files (130+ scenarios) | **8 files (160+ scenarios)** |
| **Dependencies** | 8 packages | **9 packages (+mcp>=1.0.0)** |
| **PyPI README** | Relative links (broken) | **Absolute links (working on PyPI)** |
| **Version** | 0.0.27 | **0.0.28** |

---

## Next Steps

- **Integration tests:** End-to-end coverage of main flows, including MCP server tests
- **MCP Prompts:** Add MCP prompts (message templates) for common flows like "review PR"
- **MCP Annotations:** Tool annotations (`readOnlyHint`, `destructiveHint`) for better IDE integration
- **More providers:** Claude API, direct OpenAI, additional local providers
- **Metrics & analytics:** Usage dashboard for teams
- **Plugin system:** Extensibility for linter rules and custom prompts
- **MCP SDK v2 Migration:** Monitor v2.x SDK stabilization (stateless mode, tasks)

---

**Report generated:** 2026-07-24
**Branch:** `develop_natan`
**Author:** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))

---

[← Contributing](/contribuicao) &nbsp;|&nbsp; [Home →](/index)
