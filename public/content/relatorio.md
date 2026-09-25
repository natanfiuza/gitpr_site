# **🚀 Project Status Report: GitPR CLI — v0.0.16 (2026-09-23)**

## **📌 Overview**

**GitPR** is an advanced CLI (Command Line Interface) tool for automating Git processes using Artificial Intelligence (Google Gemini / DeepSeek / Ollama). Its main goal is to act as a local intelligent assistant that performs Code Reviews, generates Pull Requests, semantic commit messages, audits technical debt, and injects best practices into the developer workflow (Shift Left).

**What's New in This Version (v0.0.16):**
- **`gitpr demo` — the first run stopped being an act of faith:** A guided tour that shows a commit message, a review and a PR description **with no API key, no git repository and no network**. The question it answers ("what does this tool do?") has only one window in which to be asked — the first use, before the user has configured a provider. The tour runs through the **real generation pipeline** with the source of the answer swapped, so what appears is what the tool actually produces, while every external effect (cache, metrics, disk, network, key reads) is neutralized.
- **GitPR badge in the PR body + `gitpr badge` command:** A PR written by AI was indistinguishable from one written by hand, and the linter result died in a terminal that had scrolled off the screen. The badge turns that private signal into a visible statement in the published PR itself — and it is deliberately a static shields.io URL, which GitPR **never** fetches, so that publishing a PR does not come to depend on a third party being up. Honest measurement: with no linter rules configured the badge is **not** emitted, because an empty list means "nothing was verified", not "nothing was found".
- **`gitpr split` — a working tree with several intentions stops becoming a commit-blob:** It reads the uncommitted diff, asks the AI to partition the hunks by logical intention and proposes **one atomic commit per concern**, each with a message generated from the patch of that isolated group. The tree is never rewritten: the files end byte for byte identical to the start — only the history changes. *(The index, yes: `--apply` resets to HEAD before staging.)*
- **Built-in secret scanning — the rule that cannot be overridden:** Seven rules (`src/security_ruleset.py`) that run on **every** invocation of the linter: five blocking `error`s (AWS key ID, GitHub/Slack token, Google key, private key block) and two `warning`s. The local catalog was entirely user-managed — it could be overwritten by the download, rewritten by the wizard (losing comments) or extended only by machine-local plugins. A secret gate must behave the same on every machine, so the rules now live **inside the package**. **Behavior change: a commit that used to pass can now be blocked.**
- **Support for `extensions: ["*"]`:** It now means *every* file, including those without a suffix — which is exactly where secrets leak from (`id_rsa`, `.env`, `credentials`, `Dockerfile`). A rule with `extensions: ["py"]` keeps the filter exactly as before.
- **Opt-in SAST bridges — Semgrep, Gitleaks and Bandit:** A layer that plugs third-party security scanners into the existing linter. They run **only** when enabled, **only** over the files touched by the diff, and the findings are deduplicated against the internal ruleset: a secret seen by both appears **once** with a multi-source confirmation marker (`[Gitleaks + Regex]`). Gitleaks has its values masked (`AKIA****`) before becoming a finding.
- **`gitpr tests generate` — the suite that respects the repository's convention:** Generates complete test files from the diff, from a specific file or from a review finding. It detects the framework in use (Pest, PHPUnit, Jest, Vitest, Pytest) instead of imposing a style, computes the conventional destination path (Laravel's `Feature`/`Unit` split, Pytest's `tests/**/test_*.py`) and validates the syntax with the local toolchain (`php -l`, `node --check`, `python -m py_compile`) — a validation failure becomes a warning, not an error. Dry run is the default.
- **`gitpr explain` + the `--explain` flag — the guide for whoever is going to review:** A reviewer-centered guide (what changes, why it changes, where to focus, what the regression risk is) so that nobody has to reconstruct the intention from a raw diff. Available as its own subcommand and as a flag that appends the section to the generated PR description.
- **Layered architecture — `src/domain/` and `src/application/`:** The two latest features (`tests` and `explain`) were born with an explicit separation between pure domain rules and use-case orchestration, and the CLI and the chat TUI now share **the same** use case instead of duplicating it.
- **Deterministic suite and the first CI:** `tests/conftest.py` became hermetic (it pins `GITPR_LANG=en_us` and turns off the secrets ruleset) and `.github/workflows/tests.yml` runs the suite on **Python 3.10** (the declared floor, never exercised) and 3.13. It was the CI that made the locale drift visible — **22 tests** failed on a pt-BR machine for asserting the English literal.
- **The 3 failures inherited from three reports in a row were closed:** the two outdated timeout tests (`600s` vs. the real default of `180s`) and the locale-sensitive test. The suite's baseline stopped being "3 known failures" and became **green by construction**.
- **New and concentrated debt:** the two most recent features (`tests` and `explain`) landed with the skill registry **half done** — **40 `__()` keys** used in the code do not exist in any of the 6 dictionaries, and the label registries of the config and of the MCP did not receive the new types. That is **4 failures** in the full suite, all with the same root cause.
- **State of the 1.3.0 release:** `__version__` and `__lang_version__` are in the **working tree and uncommitted** (HEAD is still at 1.2.0 / v0.0.31), `CHANGELOG.md` **has** the `[1.3.0] - 2026-09-21` entry — but it covers **only** the secret scanning, and not `demo`, `badge`, `split`, SAST, `tests` or `explain`. The `v1.2.0` tag **was created** (merge of PR #174), closing the item that blocked the previous window.

- **Current version:** 1.3.0 (bump in the working tree — HEAD at 1.2.0; last tag `v1.2.0`)
- **Language dictionaries version:** v0.0.32 (bump in the working tree — HEAD at v0.0.31)
- **Hook scripts version:** v0.0.3
- **Distribution:** PyPI (`pip install gitpr-cli`) — binary channel removed
- **Website:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Repository:** [https://github.com/gitpr-cli/gitpr.git](https://github.com/gitpr-cli/gitpr.git)
- **License:** LGPL-2.1
- **Supported languages:** en_us, pt_br, pt_pt, es_es, fr_fr (5 languages, 6 dictionaries)

---

## **🏗️ Base Architecture and Libraries**

* **Language:** Python >= 3.10
* **CLI Framework:** Click (for commands, flags and terminal formatting) — **9 subcommands** 🆕 (`badge`, `config`, `demo`, `explain`, `fix`, `release`, `review-pr`, `split`, `tests`).
* **UI/Terminal:** Textual — TUI for interactive chat, issue editing, help screen, metrics dashboard, PR Publisher, linter errors (`LinterApp`), configuration (`ConfigApp`), the `NoticeScreen` modal and the **`gitpr demo` tour** (`src/ui/demo/`) 🆕.
* **Layered Architecture 🆕:** `src/domain/` (pure rules, no I/O) + `src/application/use_cases/` (orchestration) + presentation (CLI/TUI). Introduced with `tests` and `explain`; the CLI and the `/tests` chat call **the same** use case.
* **Cryptography:** `cryptography.fernet` for local protection of API keys, GitHub tokens and SCM tokens for the forges.
* **Configuration:** `python-dotenv`, `pyyaml` (static linter) + its own declarative schema (`src/config_schema.py`, **71 `ConfigField`** 🆕).
* **AI Providers:** Integration via the official Google GenAI SDK (`gemini-2.5-flash`), OpenAI SDK (`DeepSeek`) and OpenAI SDK (local `Ollama`).
* **Forge APIs:** `requests` (REST) — multi-forge abstraction in `src/infrastructure/scm/` (GitHub, GitLab, Bitbucket, Azure DevOps); `src/github_api.py` kept as a deprecated shim.
* **Linter / SAST 🆕:** YAML regex + Checkstyle bridge + **built-in secrets ruleset** (`src/security_ruleset.py`) + **4 bridges in `src/infrastructure/linter/external/`** (base + Semgrep, Gitleaks, Bandit), with a normalized model in `src/domain/linter/sast_finding_mapper.py`.
* **Git Internals 🆕:** `src/infrastructure/git/` — `patch_applier.py` (moved from `src/fix/`) and `selective_stager.py` (staging of an arbitrary subset of hunks).
* **MCP:** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 — **14 annotated tools, 18 resources, 7 prompts** (count unchanged in this window).
* **Tests:** Pytest + `unittest.mock` (**96 test modules — 44 at the root, 9 in `tests/scm/`, 10 in `tests/fix/`, 6 in `tests/split/`, 6 in `tests/demo/`, 7 in `tests/badge/`, 4 in `tests/review/`, 4 in `tests/infrastructure/linter/external/`, 2 in `tests/domain/tests_generation/`, 2 in `tests/application/use_cases/`, 1 in `tests/domain/pr/`, 1 in `tests/domain/linter/`** 🆕 —, **1889 collected scenarios**) + MCP server e2e tests via a real subprocess (JSON-RPC stdio) + real git repository fixtures (`tests/fix/git_fixture.py`, `tests/split/git_fixture.py`) + a **network isolation guard** (`tests/demo/conftest.py`).
* **Packaging:** setuptools/build (PyPI) — `version = {attr = "src.updater.__version__"}`.
* **CI/CD:** GitHub Actions — `pr-review.yml` + `action.yml` + **`tests.yml`** 🆕 (Python 3.10/3.13 matrix).

---

## **🧩 Implemented Modules and File Architecture**

### **1. Core and Git Operations (`src/core.py`)**

* **Structured Generation:** Communicates with the LLM asking for a strictly JSON return.
* **Map-Reduce (Giant Diffs):** When the diff exceeds ~90k tokens, it automatically splits into per-file batches (`split_diff_into_chunks`), processes each part (Map) and unifies the summaries (Reduce). Supports PRs, commits and Issues.
* **Local Tokenizer:** `tokenizer.json` for accurate token estimation before sending to the AI.
* **Token Estimation:** Lightweight `len() // 4` heuristic via `estimate_token_count()` with a fallback to the local tokenizer.
* **Native Git Optimization:** `-U1`, `-w`, `-M`, `-B` flags in the `get_git_diff` and `get_git_full_diff` commands to reduce useless context.
* **`get_split_diff()` 🆕:** `split`'s own diff, with `SPLIT_DIFF_ARGS` (`--binary -M -U3`), **deliberately without** reusing `get_git_diff()` — the `-w` (ignores whitespace) and the smart-excludes would break the byte-identical tree guarantee, because the reconstructed patch has to match the file on disk.
* **Pre-Save (`--pre-save`):** Hidden debug flag that saves the full payload (system instruction + prompt) as JSON before each AI call.
* **Two-Layer Smart Excludes:** Global layer (`~/.gitpr/conf/`) + project-local layer (`./.gitpr/conf/`), merged at runtime. Auto-seeding of the local file on first run. `_load_smart_excludes()` accepts `force=`.
* **Metrics with Time Tracking:** `log_command_metric()` in all flows with `duration_ms` and lazy imports.
* **Centralized Output Resolution:** `resolve_output_path()` — default in `.gitpr/reports/{type}/`.
* **SCM Wizard (`run_scm_init_wizard()`)**: `gitpr --init` — detects the forge from the origin remote, requests per-forge extras, validates the token and persists **only on success**. 🆕 It announces the automatic badge on the PR path and informs the exact switch that turns it off.
* **Release Skill Template (`ensure_release_skill_template()`)**: Downloads `templates/gitpr.release.*.md` on the first use of `gitpr release`.
* **Shared Skill Registry:** `SKILL_FILES_BY_TYPE` / `SKILL_TYPES` / `DEFAULT_SKILL_TYPE` in `src/config.py`. 🆕 It gained entries for `tests` (`.gitpr.tests.md`) and `explain` (`.gitpr.explain.md`).
* **Review Engine with Cache Scope (`generate_pr_content`):** `cache_scope` (attached **only to the cache key**) and `store_diff` (writes the reviewed diff). The defaults keep the local path byte-identical.
* **Co-authorship Trailer:** `COAUTHOR_TRAILER` + `append_coauthor_trailer()` — idempotent, preserves third-party trailers.
* **Hardened Subprocesses:** `stdin=subprocess.DEVNULL` + `encoding='utf-8'`/`errors='replace'` in every `subprocess.run`; connection check via socket `8.8.8.8:53` before network operations.

### **2. Global Plugin System (`src/plugins.py`)**

* **Plugin Architecture:** Loads plugins from `~/.gitpr/plugins/`, applying to **all projects**.
* **Linter Plugins (`linter/`):** `.yml` files merged with the local `.gitpr.linter.yml`.
* **MCP Prompt Plugins (`prompts/`):** `.md` files that extend the system context.
* **Factory Closures:** `get_linter_plugins` and `get_prompt_plugins` isolate state between sessions.
* **`--plugins` Command:** Lists all installed global plugins with their types and paths.
* **Multilingual Documentation:** `docs/plugins-system.md` in 5 languages.

### **3. CLI Interface and Setup (`src/main.py` and `src/config.py`)**

* **Initial Setup:** Detects the first run, creates `~/.gitpr/` and interactively asks for keys, preferences and language. 🆕 The first-run hint points to `gitpr demo`.
* **Command Routing:** Manages all flags and the **9 subcommands** — `release`, `config`, `fix`, `review-pr`, **`demo`** 🆕, **`badge`** 🆕, **`split`** 🆕, **`tests`** 🆕 (group, with `generate`) and **`explain`** 🆕.
* **Default Behavior:** Running `gitpr` with no flags opens the PR Publisher TUI.
* **Flags (now 36 Click options at the root, +1 in this window):**
  * **`--explain` 🆕:** Includes the *Reviewer Guide* section in the PR description (and in the JSON payload emitted).
  * `--init`, `--no-suggest-reviewers`, `--no-publish`, `--no-edit`, `--base <branch>`, `--plugins`, `--linter-setup`, `--version` — unchanged.
* **`demo` Subcommand 🆕 — 3 options:** `--scenario`, `--lang` and `--no-tui` (plain-text front-end for CI and recordings). The `--lang` is applied **inside** the subcommand, because the root callback returns before the handler.
* **`badge` Subcommand 🆕 — 2 options:** `--readme` (bare form of the snippet, pipe-friendly) and `--style` (`flat`/`flat-square`/`for-the-badge`). **Only prints** — the README is never modified.
* **`split` Subcommand 🆕 — 5 options:** `--dry-run`, `--apply`, `--yes`, `--max-groups` and `--provider`. A bare invocation prints the plan and asks before committing anything.
* **`tests` Group 🆕 → `generate` subcommand — 5 options:** `--file`, `--finding`, `--framework`, `--apply` and `--provider`. Dry run is the default and overwriting an existing test asks for confirmation with **No** pre-selected.
* **`explain` Subcommand 🆕 — 1 option:** `--provider`. It detects a missing diff with a clear error before any AI call.
* **Environment Variables (49 keys in `DEFAULT_CONFIG`, +5 in this window):** `GITPR_LINTER_SECURITY`, `GITPR_LINTER_SECURITY_DISABLED_RULES` and the three `GITPR_SPLIT_*` (`MAX_GROUPS`, `MAX_HUNKS`, `REQUIRE_CONFIRMATION`). **Another 5 are read-only / not seeded:** `GITPR_BADGE` (default `true`), `GITPR_EXPLAIN_BY_DEFAULT` (default `false`) and the three `GITPR_SAST_*_ENABLED` — GitPR **never** writes them into `~/.gitpr/.env` on its own.
* **Contextual Help:** `-h --flag` displays feature-specific documentation with a language-aware link. Subcommands have their own `epilog=` (Click's `\b` paragraph so the URL is not re-wrapped under any locale). 🆕 `explain` joined `HELP_MAP`/`HELP_PRIORITY`.
* **--lang / --provider / --mcp / --install / --metrics / --status** — unchanged.
* **`.env` Write Layer:** `read_env_file_values()` reads **only the file** via `dotenv_values`; `save_config_values()` with `set_key`; `remove_config_value()` with `unset_key`. `validate_ai_key()` distinguishes a refused credential (`401`/`403`) from an unreachable network.

### **4. PR Publisher TUI (`src/ui/pr_publish_app.py` and `src/ui/pr_publish_help.py`)**

* **Complete Interactive Interface:** TUI to review, edit and publish Pull Requests directly from the terminal.
* **7 Modal Screens:** `StageFilesScreen`, `CommitConfirmScreen`, `CommitProgressScreen`, `CommitMessageScreen`, `LinterErrorScreen`, `ErrorScreen` and `NoticeScreen`.
* **Badge in the PR Body 🆕:** `attach_pr_badge()` runs in `src/main.py` right after `pr_data` is complete — a **single injection point**, so both publishers (the TUI's text area and `--no-edit`) read the badge from the same place. The user **sees** the badge and can delete it before sending; `--no-edit` prints a line saying what went into the body that they did not see. Idempotent append by marker — republishing or moving the badge does not stack badges.
* **`_attach_reviewers` with resolution:** Resolves what the user typed **before** sending, reports what was discarded and, on a batch `422`, **retries one by one** — GitHub rejects the whole batch when a single login is ineligible.
* **Suggested Reviewers:** Queries the forge for suggested reviewers; `_reviewer_suggestion_view()` builds `resolutions` via `resolve_candidates` and pre-fills `handles`.
* **Bindings:** F1 (Help), F2 (Save local .md), F3 (Publish via forge), Esc (Quit).
* **Auto-Commit Flow:** Linter → AI message → confirm → commit → push → publish PR.
* **Unstaged File Check:** `git status --porcelain` on entry, with a modal to select, skip or cancel.
* **Existing PR Handling / Auto-Upstream / Merge Flow** — unchanged (`GITPR_AUTO_MERGE`).

### **5. GitHub API Module (`src/github_api.py`)**

* **Deprecated Shim:** `create_pull_request()`, `update_pull_request()`, `merge_pull_request()` and the remaining functions delegate to `src/infrastructure/scm/github_provider.py`; it emits a `DeprecationWarning` and keeps the legacy `(ok, data, status)` tuples — no new code may import it.

### **6. Static Analysis Engine / Linter (`src/linter_engine.py`)**

* **Offline Linter:** Statically analyzes the added (`+`) lines in the diff without spending AI quota.
* **YAML Rules:** Reads `.gitpr.linter.yml` (created via `--skill`).
* **Linter Plugins:** Additional rules from `~/.gitpr/plugins/linter/*.yml`.
* **Extension Wildcard 🆕:** `extensions: ["*"]` now means **every** file, including those without a suffix and dotfiles. It is what gives coverage to `id_rsa`, `.env`, `credentials` and `Dockerfile`; a rule with `extensions: ["py"]` keeps the filter as it always was.
* **Built-in Secrets Ruleset 🆕:** `load_linter_rules()` merges `src/security_ruleset.py` **after** the project and plugin rules, which are left untouched — only the order changes, pushing the security alerts to the end of the report. Seven rules, all `extensions: ["*"]`: five `error` (AWS key ID, GitHub/Slack token, Google key, private key block) and two `warning` (database URL with credentials and generic credential assignment, behind a placeholder filter).
* **The Alert Never Echoes the Value:** The message carries only `{file_name}` and `{line_number}` — it travels to the console, to the Markdown report and, in the PR flow, to the body of a public pull request; printing the secret would copy it into all three.
* **`--input` Widened 🆕:** The whole-file audit now scans `.md`, `.txt` and lockfiles, since the ruleset matches every extension. There it **reports without blocking** — the `sys.exit(1)` exists only on the `--linter` path.
* **Opt-In SAST Bridges 🆕:** Semgrep, Gitleaks and Bandit run on the whole-file path **and** on the diff path, filtering findings by added lines and warning when the tool is enabled but missing from the `PATH`. `load_sast_config()` resolves in three layers: defaults → `.gitpr.linter.yml` (`sast` block, falling back to `linter.external`) → `GITPR_SAST_*` variables. **Everything defaults to `false`** (strict opt-in).
* **`skip_external`:** `parse_diff_and_lint(..., skip_external=False)` turns off **both** call-sites of the external bridge; the remote flow passes `True`, because the bridge runs binaries against files on disk — the local tree, not the PR.
* **Consolidated Report:** `generate_linter_report_content()` consolidates regex + external errors into `.gitpr/reports/linter/` — generated only when there are violations.
* `load_linter_presets()` accepts `force=` for re-downloading from the TUI.

### **7. Security and Authentication (`src/security.py`, `src/config.py`, `src/tui_issue.py`)**

* **Encryption:** Generates the master key `secret.key` in `~/.gitpr/`.
* **Token Protection:** `encrypt_data`/`decrypt_data` for AI keys, GitHub PAT and SCM tokens for the forges.
* **Multi-Forge Validation:** `validate_or_request_scm_token(provider, repo_display)` — 401 → reauthentication loop preserving the draft; the legacy GitHub token remains functional until `--init` runs.
* **Secrets in the Configuration TUI:** `KIND_SECRET` fields are edited masked, **never** display the value in the clear and are encrypted with Fernet before being written. `GITPR_SCM_TOKEN` is `read_only`.
* **Secret Scanning in the Flow Itself 🆕:** The linter — which runs in the pre-commit hook — became a credentials gate with identical rules on every machine, because they live in the package and not in a downloaded template that `--skill` or the wizard can replace.

### **8. Auto-Updater (`src/updater.py`)**

* **PyPI as the Single Source:** `get_latest_remote_version()` queries `https://pypi.org/pypi/gitpr-cli/json` and writes the daily cache **without** the `download_url` field.
* **Mandatory Gate (`enforce_update_required()`):** Returns `True` (after printing both versions and the pip command) when the published version is newer; `False` when up to date, when the remote version is **unknown (offline)** or when the check is turned off. Returning a `bool` instead of calling `sys.exit` internally keeps the function testable.
* **`check_and_update()`:** Only queries and **reports**, never installs.
* **Escape Hatch:** `GITPR_SKIP_UPDATE_CHECK` — it is not advertised; it exists for the suite and for offline automation.
* **Centralized Versioning:** `__version__` (**1.3.0** — bump in the working tree), `__lang_version__` (**v0.0.32** — chain v0.0.28 → v0.0.29 → v0.0.30 → v0.0.31 → v0.0.32 in this window 🆕), `__scripts_version__` (**v0.0.3**), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`, `LINTER_PRESETS_VERSION`.

### **9. Interactive Chat Interface (`src/ui/chat_app.py`)**

* **Complete TUI:** Message history, multi-line input, status bar with visible bindings.
* **Per-Branch Memory (`src/chat_memory.py`):** History persisted per branch, with continuity between sessions.
* **Slash Commands:** `/explain`, `/tests`, `/optimize`, `/clear`. 🆕 `/tests` (and the localized aliases `/testes`, `/pruebas`) **stopped being forwarded to the generic model** and now delegates to the **same use case** the CLI uses (`TestGenerationTarget`), instead of duplicating the logic.
* **Auto-Patching (F5), Diff Refresh (F2), Session Export (F6).**
* **Shared Extractor:** F5 and `ctrl+s` call `patch_extractor.extract_code_blocks()` — identical visible behavior, with no duplicated logic.

### **10. Internationalization — i18n (`src/i18n.py`)**

* **Laravel-Inspired System:** `__()` function with named placeholders (`{count}`, `{file}`, etc.).
* **Automatic Detection:** Detects the OS language on the first run and saves it to `GITPR_LANG`.
* **5 Languages, 6 Dictionaries:** en_us (default/fallback), pt_br, pt_pt, es/es_es, fr/fr_fr.
* **Versioned Files:** `__lang_version__` (**v0.0.32**) controls the update of the language packs (`langs/*.json`) — bump chain v0.0.28 → v0.0.29 → v0.0.30 → v0.0.31 → v0.0.32 in this window.
* **Coverage:** **1158 keys** in each of the 6 files — full key set parity (+110 since the previous report). **Important caveat 🆕:** the code uses **1198** keys, so **40 `__()` keys do not exist in any dictionary** (see §33 and §34) — the parity between the 6 files is total, but the coverage relative to the code is not.
* **First-Run Fix 🆕:** `i18n.py` now **creates the profile directory before** persisting the detected language, avoiding a crash on the first run on a clean machine.
* **Environment Snapshot (`AMBIENT_ENV_KEYS`):** `frozenset(os.environ)` captured **immediately before** the module-level `load_dotenv()` — it fixes the "⚠ in the environment" badge that tautologically confirmed the key is in the file.
* **Cache with Per-Language Indexing:** Cached AI responses include the current language in the MD5 keying.

### **11. Animated Spinner (`src/spinner.py`)**

* **Braille + Thinking Words:** Background thread during AI calls with braille characters and "thinking" words.
* **263 entries per language:** Synchronized across the 5 languages. `_load_thinking_words()` / `reload_thinking_words()` accept `force=`.

### **12. AI Providers (`src/ai_providers.py`)**

* **3 Supported Providers:** Google Gemini (`gemini-2.5-flash`), DeepSeek (`deepseek-chat`), Ollama (local).
* **JSON Mode & Deterministic Parameters:** `temperature=0.0` and `top_p=0.1`; automatic fallback between configured providers.
* **AI Timeout:** default of **180s** (`GITPR_AI_TIMEOUT`) — the 600s value was deliberately lowered in fix `681a7fa` and the outdated test was finally aligned in this window (see §37).

### **13. Smart Cache (`src/cache.py`)**

* **MD5 + Metadata:** Keying by MD5 hash of the diff and prompt, with per-language indexing.
* **Last Review Selection (`resolve_last_review()`):** Picks the **newest** `review`/`fullreview` record for a repo+branch pair, excluding file-scoped reviews — it is the entry point of `gitpr fix`.
* **Reviewed Diff (`reviewed_diff`):** A field at the top of the record that stores the diff that was actually reviewed, preferred by `fix/apply_fix.reviewed_diff()`.
* **Telemetry and Duration:** Persistence of `duration_ms` and `meta_raw`.
* **Reading for the Dashboard:** `scan_cache_files_for_dashboard()` reads all cache files recursively.

### **14. Issues Engine and TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Context Engines:** Current diff, Branch history (`-ht`) and Blame Archaeology (`-b`).
* **Multi-Forge Publishing:** F3 creates the issue on the **configured** forge (`GITPR_SCM_PROVIDER`) via `provider.create_issue` — Azure DevOps raises `ScmNotSupportedError`.
* **Map-Reduce for Issues:** Context above ~90k tokens is split and unified.
* **401 Handling:** Reauthentication signalling without closing the application.

### **15. Code Archaeologist (`src/blame_engine.py`)**

* **Git Blame + AI:** Tracks the evolution and historical authorship with commit classification (`ORIGIN` vs `REFACTORING`).
* **Blame Metrics:** Events via `log_blame_metric()` with depth and the number of commits analyzed.

### **16. MCP Server and Direct CLI Invocation (`src/mcp_server.py`)**

* **14 Annotated MCP Tools:** `get_git_context`, `analyze_diff`, `list_unstaged_files`, `analyze_unstaged_diff`, `get_full_diff`, `generate_commit_message`, `review_code`, `full_review`, `generate_pr_description`, `run_linter`, `analyze_blame`, `generate_issue`, `list_fix_candidates` and `review_remote_pr` — **count unchanged in this window**.
* **18 Resources + 7 Templated Prompts:** `skill://list` + `skill://{pr,commit,review,filereview,issue,blame,release,fix}` + `linter://config` + `prompt://list` + 7 prompts. **Caveat 🆕:** `skill://explain` and `skill://tests` do **not** exist — the test `TestSkillRegistryAgreement` fails precisely because `mcp_server.SKILL_FILES` did not receive the two new types that `config.SKILL_FILES_BY_TYPE` already has.
* **Direct CLI Invocation:** `gitpr-mcp --tool <name> [--tool-args '<json>']` invokes any tool without starting the stdio server; `gitpr-mcp --list` prints the complete registry as JSON.
* **Real Stdout Isolation:** `_write_real_stdout()` writes to the original `sys.__stdout__`, guaranteeing pure JSON. 🆕 Hardened against `UnicodeEncodeError` on legacy Windows code pages (fallback to `buffer` or re-encoding with `errors='replace'`).
* **Event Loop Offload:** `_offload` decorator (`anyio.to_thread.run_sync`) on the 14 tools — the order of the decorators matters (`@mcp.tool` **above** `@_offload`).
* **Usage Log:** The server's `main()` calls `log_usage()` — the `gitpr-mcp` console script never loads `main.py`.
* **E2E Tests:** `tests/test_mcp_server_e2e.py` starts the real server as a subprocess and speaks JSON-RPC stdio.

### **17. Metrics Dashboard TUI (`src/ui/metrics_app.py`)**

* **Per-Repository Scope:** `📁 Repository: owner/repo` label and strict per-project filtering.
* **Asynchronous Scan with Overlay:** Background worker thread with a `ProgressBar`. 🆕 The tests now wait for `workers.wait_for_complete()` before asserting — without it, the suite was flaky due to a race.
* **Data Consolidation:** `load_cache_token_summary()` adds cache tokens to the totalizer.
* **Local Export:** CSV/JSON in `./.gitpr/metrics/export/` — 🆕 more generated artifacts entered **tracked** (`gitpr_metrics_2026-09-18/19/21/22.*`); the `.gitignore` debt remains open.

### **18. Metrics and Telemetry System (`src/metrics.py`)**

* **Per-Repository Scope:** All events indexed by `repo_name`.
* **Hook, Linter and Blame Events:** `log_hook_event()`, `log_linter_metric()`, `log_blame_metric()`.
* **Export and Cleanup:** `--metrics --export` (CSV/JSON) and `--metrics --purge` with interactive confirmation.

### **19. Git Hooks Language Synchronization**

* **Independent Versioning:** `__scripts_version__` (v0.0.3).
* **Suffix Mapping (`HOOK_SCRIPT_SUFFIXES`):** Interface codes (`es_es`, `fr_fr`) translated into the published suffixes (`.es`, `.fr`).
* **Choice vs. State (`SCRIPTS_LANG` / `SCRIPTS_INSTALLED_LANG`):** It makes it possible to **detect a language switch**.
* **`effective_hook_lang()`:** Resolves the effective language; `--lang` is no longer discarded on that path.
* **Merge-Source Skip:** `prepare-commit-msg` skips the `message|merge|squash|commit` sources.

### **20. External Linters Bridge and Interactive Assistant (`src/linter_wizard.py`, `src/ui/linter_app.py`)**

* **`--linter-setup` Assistant:** Wizard with numbered presets (PHP_CodeSniffer, ESLint, Stylelint) and injection of the `external_linters` block.
* **Remote Presets:** `templates/gitpr.linter-presets.json` with a local → download → stale → embedded fallback chain.
* **Linter Error TUI:** `src/ui/linter_app.py` (Textual) displays critical errors and warnings; under hook/quiet it prints and calls `sys.exit(1)`.
* **Markdown Report:** Consolidated into `.gitpr/reports/linter/` only when there are violations.
* 🆕 **The SAST Layer Is a Sibling, Not a Replacement:** the Semgrep/Gitleaks/Bandit bridges live in `src/infrastructure/linter/external/` (see §31) and feed the **same** report pipeline.

### **21. SCM Multi-Forge (`src/infrastructure/scm/`)**

* **Single Abstraction (`ScmProvider` ABC):** `base.py` defines the contract (dataclasses `RepoRef`, `PullRequestDraft` etc. and `ScmProviderError(provider, http_status, message)` — `http_status` 0 = network failure); one concrete provider per forge.
* **Registry and Factory:** `resolve_scm_provider()` selects by `GITPR_SCM_PROVIDER` (default `github`); `detect_provider_from_remote()` identifies the forge from the origin URL.
* **Repository Addressing:** `parse_repo_ref(remote_url) -> RepoRef(raw, workspace, name, provider)`.
* **`get_pull_request(repo, pr_id)` — a concrete ABC method:** the default raises `ScmNotSupportedError` and each forge implements it — `list_open_pull_requests` pages **a single page** and does not distinguish closed from nonexistent.
* **`supports_reviewable_diff`:** Class attribute, `False` on Azure DevOps, checked **before any network call**.
* **GitLab Headers Synthesized:** `changes[].diff` is a **loose hunk**; `old_path`/`new_path` now assemble the `diff --git a/… / --- / +++` headers, honoring `/dev/null`.
* **GitLab `overflow` Raises:** An MR with a truncated diff was reviewed halfway; it now raises `ScmProviderError`.
* **`request_pull_request_reviewers` returns `list[str]` (contract break):** It returns the logins **effectively attached**, read from the body of the `201`. Third-party subclasses must be updated.
* **Two Read-Only GitHub Helpers:** `get_commit_author_login` (maps a SHA to the account linked to the author's e-mail) and `get_user_login` (validates/canonicalizes a handle, rejects what cannot be a login without spending a request).
* **Fail-Fast per Forge:** Azure requires `GITPR_SCM_ORGANIZATION`/`GITPR_SCM_PROJECT`; Bitbucket requires `GITPR_SCM_USERNAME`; `create_issue` on Azure raises `ScmNotSupportedError`.
* **Release Publishing:** `provider.create_release()` used by `gitpr release --publish`.
* **Artifacts:** Glossary + ADR-001/ADR-005; the `docs/scm-multiforge.*.md` family in 5 languages; 9 test files, **284 scenarios**.

### **22. `gitpr release` Subcommand — Changelog / Release Notes**

* **Flow:** `git log` between `--since` and `HEAD` → classification by Conventional Commits → suggested semantic bump (`--version <x.y.z>` overrides) → changelog assembly → optional AI executive summary → *prepend* to `CHANGELOG.md`.
* **Classifier (`src/commit_classifier.py`)** and **Builder with Translatable Sections (`src/changelog_builder.py`)** — sections rendered via `__()` at runtime.
* **Semantic Bump (`src/version_bump.py`)** and **Publishing** (`--publish`, `--draft`, `--format markdown|json`, `--force`). **6 options in the subcommand.**
* **Release Pending Item 🆕:** the `[1.3.0] - 2026-09-21` entry **exists** in `CHANGELOG.md`, but it was written by the secret-scanning commit and covers **only** that one — `demo`, `badge`, `split`, SAST, `tests` and `explain` are **not** in the changelog, and the entry is dated 21/09 while `explain` is from 23/09. Running `gitpr release` is the missing step.

### **23. `gitpr config` Subcommand — Configuration TUI**

* **Master-Detail Screen (`src/ui/config_app.py`):** Categories on the left, fields on the right, edited inline. Header with search (`/`) and a pending-changes counter; footer `F1 Help · F2 Save · ^R Restore · / Search · Esc`.
* **Declarative Schema (`src/config_schema.py`) — the single source of truth:** 🆕 **14 categories** (+1: **Split**) and **71 `ConfigField`** (+10), of which **9 are advanced**. Each field declares its category, widget type, `show_if`, validators, version markers and download actions.
* **`split` Category 🆕:** `GITPR_SPLIT_MAX_GROUPS`, `GITPR_SPLIT_MAX_HUNKS` and `GITPR_SPLIT_REQUIRE_CONFIRMATION` with a positive-integer parser that falls back to the default on an unparseable value — **zero or negative does not silently disable the ceiling**.
* **Read-Only Fields 🆕:** `GITPR_BADGE`, `GITPR_EXPLAIN_BY_DEFAULT` and the three `GITPR_SAST_*_ENABLED` appear in the schema (and therefore on the screen) but are **not** seeded into the `.env`.
* **Context Filtering (`show_if`):** `GEMINI_*`/`DEEPSEEK_*`/`OLLAMA_*` according to `DEFAULT_AI_PROVIDER`; `GITPR_SCM_USERNAME` under `[Bitbucket]`; `GITPR_SCM_ORGANIZATION`/`PROJECT` under `[Azure DevOps]`. Changing the `Select` re-filters the panel immediately.
* **Global Search (`/`):** Matches key or label in every category and **ignores the visibility filter**.
* **Two-Layer Validation:** **Offline** blocks `F2` with an inline error; **online** (only credentials changed in the session) runs in a worker with a 10s timeout and only blocks on `401`/`403`.
* **Restore (`Ctrl+R`):** Removes the line from `.env` instead of rewriting the default; `Esc` with pending changes asks for confirmation.
* **Skills Section — the only project-scoped one:** Edits the project's `.gitpr/skill/*.md` resolved from the calling directory, writing atomically and preserving CRLF/LF. **Known debt 🆕:** `SKILL_LABELS` did not receive `tests` or `explain` — without a label, the two types render **blank** in the sidebar (see §33/§34).
* **Forced Downloads:** Buttons that force re-downloading smart-excludes, translations, presets and thinking words via `force=`.
* **Lightweight Links Module (`src/doc_links.py`):** `doc_url()` moved out of `core.py` so that the UI can obtain the link without importing AI SDKs.
* **Tests:** 5 files — `test_config_app.py` (78), `test_config_schema.py` (42), `test_config_validation.py` (40), `test_config_store.py` (22), `test_config_cli.py` (9).
* **Known debt:** `gitpr -h config` opens the TUI and ignores `-h` — the gate `if ctx.invoked_subcommand is not None: return` runs before the `help_flag` block.

### **24. General Usage Log (`src/usage_log.py`)**

* **One Line per Command:** Writes to `~/.gitpr/logs/<uuid5>.log`, **one file per day**, with the command, arguments, repository, user and timestamp.
* **Name Derived from the Date:** `uuid5(NAMESPACE_DNS, f"gitpr.usage.{YYYY-MM-DD}")` instead of random — two concurrent processes cannot disagree about which file is today's.
* **Synchronous Write (explicit decision):** Unlike `log_local_metric`, which uses a daemon thread and loses the write if the process exits first.
* **Never Prints:** The MCP server reserves stdout for JSON-RPC; the module also never raises an exception.
* **A Single Git Spawn:** `git config --get-regexp '^(remote\.origin\.url|user\.name|user\.email)$'` — ~40 ms instead of ~150 ms on Windows, on *every* run.
* **Control:** `GITPR_SHOW_LOGS` (default `"true"`); turned off in `tests/conftest.py`.
* **Artifacts:** `docs/usage-log.*.md` in 5 languages; `tests/test_usage_log.py` (27 scenarios).

### **25. `gitpr fix` Subcommand — Review Findings as Reviewable Patches (`src/fix/`)**

* **The Real Pipeline:** last review from the cache (`resolve_last_review`) → **one** AI call → extraction and validation of the unified diff → `git apply --check` → deterministic classification → dry run or write → history.
* **8-File Package:** `patch_provenance.py`, `patch_extractor.py` (shared with the chat), `patch_safety_classifier.py` (pure, no I/O), `patch_applier.py`, `fix_history.py`, `apply_fix.py`, `rollback_fix.py`, `__init__.py` (docstring only).
* **Deterministic Classification:** It refuses a patch that crosses more than one file or hunk, touches a sensitive path, blows the line budget, **deletes a line that looks like a call**, comes marked as low-confidence or fails `git apply --check`. `--force` **never** bypasses applicability, only the classification.
* **Opt-In Writing:** Dry run is the default; any mutation requires `--apply` (or a typed phrase with `--force`).
* **`patch_applier.py` Moved House 🆕:** The `git apply` wrapper was promoted to `src/infrastructure/git/patch_applier.py` (shared with `split`) and `src/fix/patch_applier.py` became a **re-export shim** — no existing import broke.
* **Tests:** 10 files in `tests/fix/` (**204 scenarios**) with real git repository fixtures.

### **26. `gitpr review-pr` Subcommand — Remote PR Review (`src/review/`)**

* **What It Is:** Orchestration, **not** a second review engine — the existing engine, the linter and the renderer fed with a diff that came from somewhere else. A remote review `.txt` and a local one of the same diff differ **only in the file name**.
* **5-File Package:** `diff_source.py` (pure provenance), `diff_normalizer.py` (normalization, validation and smart-excludes **in Python**), `render.py` (extracted from `main.py`, now shared), `remote_pr.py` (the use case), `__init__.py`.
* **Read-Only by Default:** `--post-comment` is the **only** path that writes to the forge. The MCP tool `review_remote_pr` does not even receive the argument.
* **Interaction with `fix`:** Since a remote review corresponds to no local tree, the reviewed diff is stored in the cache (`reviewed_diff`) and `fix` prefers it.
* **Artifacts:** `docs/review-pr.md` + `.pt_br.md`, `docs/code-review-ia.*.md` (5), ADR-005 and `glossary-review-pr.md`.
* **Tests:** 4 files in `tests/review/` (**97 scenarios**).

### **27. Reviewer Identity Resolution (`src/reviewer_resolution.py`)**

* **The Bug (two chained silent failures):** (1) **Empty prefill** — `handles` only from `email_to_handle()`, which sees only `users.noreply.github.com` e-mails or e-mails with a public address; (2) **Unverified attach** — values passed through *verbatim*, GitHub answers **201 without attaching anyone** and `_request()` returns without raising: apparent success, reviewer missing, no warning at all.
* **New Module:** A plan, with no I/O of its own, **that never raises**. `resolve_candidates()` (before the TUI) and `resolve_typed_reviewers()` (at attach time); `match_candidate()` matches exact and normalized by login, name or e-mail.
* **Resolution Ladder:** known handle (no request) → exact match with a suggested person → lookup by e-mail → validation of the login on the forge.
* **What does not resolve is never sent:** It leaves in `ResolutionOutcome.dropped` as `(value, i18n_reason)` and is discarded with a visible warning.
* **Duck-Typed Provider:** Access is via `getattr`, so fakes and forges without the new methods keep working.
* **Tests:** `tests/test_reviewer_resolution.py` (18) + expansions in `test_github_provider.py` (68), `test_pr_publish_app.py` (45), `test_suggest_reviewers.py` (17), `test_reviewer_suggestion.py` (18) and `test_main_suggest_reviewers.py` (8).

### **28. `gitpr demo` Subcommand — Guided Tour over Recorded Examples (`src/demo/`) 🆕**

* **What It Is:** An interactive tour that shows GitPR's three main outputs — commit message, review and PR description — **with no API key, no git repository and no network**. The question "what does this tool do?" has only one window in which to be asked: the first use, before the user has configured a provider.
* **Real Pipeline, Fake Answer:** `FakeAIProvider` mirrors `src.ai_providers.call_ai_model` **argument by argument**; `demo_pipeline()` patches `src.core` and `src.metrics` so that the production pipeline (prompt assembly, diff chunking, response parsing) runs **untouched** and only the source of the answer is swapped. It is what guarantees the tour shows what the tool actually produces, and not a mock-up.
* **Isolation Is a Requirement, Not a Detail:** The tour **cannot** touch the user's `~/.gitpr` (cache, metrics, logs) or the network — guaranteed by `tests/demo/conftest.py` (autouse network prohibition, with a loopback exception for the Windows proactor event loop). A regression here would silently poison a real cache with demonstration content.
* **Package:** `demo_runner.py` (`DemoState` state machine with no UI + plain-text front-end), `fake_ai_provider.py`, `scenarios/` (two examples: a profile-update bug in Laravel and a cross-tenant IDOR in Express, each one localized into the 5 languages with a fallback to English). The scenarios are **Python modules, not JSON**, so that they travel inside the wheel.
* **TUI:** `src/ui/demo/` (single screen, following the pattern of the existing apps) with a help modal; the diff is fenced as ```` ```diff ```` and the commit message rendered as plain text.
* **CLI:** `gitpr demo` with `--scenario`, `--lang` and `--no-tui`.
* **i18n:** +32 keys and a bump to **v0.0.29**.
* **Tests:** 6 files in `tests/demo/` (**155 scenarios**) — state machine, scenario integrity (hunk arithmetic), TUI navigation, text mode, the fake provider contract and the isolation guards.
* **Artifacts:** `docs/demo.*.md` in 5 languages, `README.*` (5) and the tour's example metrics.

### **29. GitPR Badge and the `gitpr badge` Command (`src/branding/`) 🆕**

* **What It Is:** A public badge that attests that a pull request went through a local quality verification, and a command that prints the adoption snippet for the project's README.
* **The Motivation Is Trust at the Point of Publication:** A PR body written by AI is indistinguishable from one written by hand, and the linter result died in a terminal that had scrolled off the screen. The badge turns that private signal into a visible and verifiable statement in the published PR itself.
* **Static URL on Purpose:** It is a shields.io Markdown that GitPR **never** fetches — publishing a pull request cannot come to depend on a third party being up.
* **Honest Measurement (`badge_data.py`):** `collect_linter_counts()` returns `None` when there are no linter rules configured (run `gitpr --skill`), because an empty list means "nothing was verified", not "nothing was found" — a green badge over an unchecked diff would be a claim GitPR cannot support. The external bridge is skipped: it inspects the working tree, not the revision under review.
* **The Badge Never Blocks a Publish:** Every failure path in the collection is caught and degrades to "no badge".
* **Single Injection Point:** `attach_pr_badge()` in `src/main.py`, right after `pr_data` is complete — both publishers read from the same place. Idempotent append by marker.
* **`gitpr badge` Command:** `--readme` (bare form, pipe-friendly) and `--style`. **Only prints** — the README is never modified.
* **Disclosure:** The init wizard announces the automatic badge on the PR path and `--no-edit` prints a line naming what went into the body that the user did not see — a feature that only shows up after configuring a variable is a feature nobody discovers.
* **Configuration:** `GITPR_BADGE` (default `true`) — a **read-only** opt-out, never written automatically.
* **i18n:** +10 keys and a bump to **v0.0.30**.
* **Tests:** 7 files in `tests/badge/` (**83 scenarios**) — builder, data collection, CLI, opt-out, publication paths and an offline guard (autouse network prohibition, except loopback); the demo and wizard suites were extended.
* **Demo:** The tour's PR step now shows the badge that a real publish would attach, counted from the linter block **recorded in the scenario itself** — no rule is read and no diff is linted.
* **Artifacts:** `docs/badge.*.md` in 5 languages and `docs/survey/20260919_gitpr_badge_surveyfacts.md`.

### **30. `gitpr split` Subcommand — Atomic Commits per Hunk (`src/split/`) 🆕**

* **What It Is:** A working tree with several concerns no longer has to become a commit-blob. `split` reads the uncommitted diff, asks the AI to partition the hunks by **logical intention** and proposes one atomic commit per concern — each with a message generated from the patch reconstructed **from that isolated group**. The tree is never rewritten: the files end byte for byte identical to the start; only the history changes.
* **6-File Package, in Layers from the Bottom Up:**
  * `split_plan.py` — data contract: `SplitError`, `Hunk`, `OpaqueSection`, `ChangeUnit`, `HunkGroup`, `SplitPlan`.
  * `hunk_parser.py` — `parse_units()` / `build_patch()`: text ↔ model, **pure**, never runs git.
  * `hunk_grouper.py` — prompt rendering, budget and trimming, **one** AI call, response validation.
  * `generate_split_plan.py` — diff → units → groups → conflict pre-validation → messages. **Read-only.**
  * `apply_split_plan.py` — **the only module in `split` that mutates**: selective staging and one commit per group.
  * `__init__.py` — package marker.
* **The Intact-Tree Guarantee Is Structural:** `selective_stager.py` pre-validates **every** group against a temporary index at HEAD and uses a disposable `GIT_INDEX_FILE` — a plan is never applied without a check, and the user's index is never read or moved during planning.
* **Destructive to the Index, Not to the Tree:** `--apply` resets the index to HEAD before staging, so whatever the user had already staged is unstaged. The file contents are preserved, but the staging has to be redone — documented explicitly.
* **Configuration:** `GITPR_SPLIT_MAX_GROUPS` (default 5), `GITPR_SPLIT_MAX_HUNKS` (default 50) and `GITPR_SPLIT_REQUIRE_CONFIRMATION` (default `true`). Zero or negative values are **ignored in favor of the default**, instead of silently turning off a ceiling.
* **i18n:** +50 keys and a bump to **v0.0.31**.
* **Tests:** 6 files in `tests/split/` (**101 scenarios**) running against real disposable repositories, with the network blocked.
* **Artifacts:** `docs/split-command.md` + `.pt_br.md` (the remaining 3 languages are pending), ADR-006 (`split-apply-safety`), `glossary-gitpr-split.md`, spec/plan in `docs/plans/` and a survey.

### **31. Built-in Secret Scanning (`src/security_ruleset.py`) 🆕**

* **What It Is:** Seven rules that run on **every** invocation of the linter, merged at the end of `load_linter_rules()` after the project and plugin rules, which are left untouched.
* **The Seven Rules:** five blocking `error`s — AWS access key ID, GitHub token, Slack token, Google API key and private key block (`-----BEGIN … PRIVATE KEY-----`) — and two `warning`s that report without blocking: a database connection URL with credentials and a generic credential assignment (`password = "…"`), the latter behind a placeholder filter (`changeme`, `xxxxxx`, `example`, `dummy`, `sample`, `your_password_here`, `sua_senha`).
* **Why in the Package and Not in the Template:** The local catalog was entirely user-managed — it could be overwritten by the download, rewritten by the wizard (losing comments) or extended only by machine-local plugins. A secret gate must behave **the same** on every machine and in every CI, so the rules live in the package and cannot be replaced by `--skill` or rewritten by the wizard.
* **Known Coverage Gaps (v1), declared:** assignment **without quotes** is not caught (`API_KEY=abc123` — the `.env` format, which is exactly where secrets leak); the prefixes `ASIA…`, `github_pat_…` and `xoxc-`/`xoxd-` are missing; and the generic rule has no left boundary on the key name, so `mytoken` matches the same as `token`.
* **Configuration:** `GITPR_LINTER_SECURITY` (default `true`; fail-open opt-out — only `false`/`0`/`no`/`off`/`n` turns it off) and `GITPR_LINTER_SECURITY_DISABLED_RULES` (`;`-separated).
* **Tests:** `tests/test_security_ruleset.py` (**61 scenarios**) — regex compilation, positive/negative detection, placeholder filter, level routing, wildcard application, merge and translation completeness.
* **Artifacts:** ADR-007 (`secret-ruleset-location-and-severity`), `glossary-gitpr-secret-scanning.md`, spec/plan/survey and 3 task reports.

### **32. SAST Bridges — Semgrep, Gitleaks and Bandit (`src/infrastructure/linter/external/`) 🆕**

* **What It Is:** An **opt-in** layer that plugs third-party security scanners into the existing linter pipeline, raising the floor of every review at no cost to whoever does not need it.
* **Bounded Scope:** They run **only** when enabled and **only** over the files touched by the diff.
* **Common Contract:** `ExternalLinterBridge` (ABC) with hardened `subprocess` execution (`shell=False`, strict timeout, `stdin=DEVNULL`, UTF-8 with `errors='replace'`) and concrete bridges for Semgrep, Gitleaks and Bandit. The `NormalizedFinding` / `ExternalLinterResult` model makes every tool produce findings at the same severity (`error`/`warning`/`info`) and in the same shape.
* **Deduplication Is the Point:** `deduplicate_secret_findings()` merges findings from Gitleaks and from the regex ruleset at the same file and line into a **single** confirmed `[Gitleaks + Regex]` entry — a secret seen by both appears once, with multi-source confirmation, instead of twice.
* **Masked Secrets:** `mask_secret_value()` (`AKIA****`) guarantees the value never reaches the finding, the log or the telemetry.
* **Three-Layer Resolution:** `load_sast_config()` reads defaults → `.gitpr.linter.yml` (`sast` block, falling back to `linter.external`) → `GITPR_SAST_*` variables. **All default to `false`** — strict opt-in, so nobody sees a change until they ask for it.
* **Graceful Degradation:** A tool enabled but absent from the `PATH` emits `⚠️ SAST tool '{tool}' is enabled in config but was not found in PATH.` and execution continues.
* **Path Normalization:** All bridges normalize backslashes→forward slashes and to a repository-relative form, so that the diff's modified-file keys match on Windows **and** on Unix.
* **Configuration:** `GITPR_SAST_SEMGREP_ENABLED`, `GITPR_SAST_GITLEAKS_ENABLED`, `GITPR_SAST_BANDIT_ENABLED` + `GITPR_SAST_<TOOL>_TIMEOUT` (60s / 30s / 45s).
* **Dependencies:** `semgrep`, `gitleaks` and `bandit` are **not** Python packages of the project — they are external binaries that need to be on the `PATH`.
* **i18n:** +7 keys (the last key addition of this window).
* **Tests:** 4 files in `tests/infrastructure/linter/external/` (**14 scenarios**) + `tests/domain/linter/test_sast_finding_mapper.py` (2) — availability, subprocess timeout, missing binary, JSON parsing, severity mapping, secret masking and skipping of non-Python.

### **33. `gitpr tests generate` Subcommand — AI Suite Generation (`src/domain/tests_generation/`, `src/application/`) 🆕**

* **What It Is:** Generates complete, executable test files from the current diff, from a specific file or from a review finding, **respecting the repository's convention** (Pest, PHPUnit, Jest, Vitest, Pytest) instead of imposing a style.
* **Domain Layer:** `TestFramework` (enum) and the dataclasses `TestGenerationTarget`, `TestScaffold`, `GeneratedTest` as a shared contract; `detect_test_framework()` detects from configuration files, dependency manifests and the contents of the test directory, with an explicit override and a warning when detection fails; `build_test_scaffold()` computes the conventional destination path per framework (Laravel's `Feature`/`Unit` split, Pytest's `tests/**/test_*.py`, the `.test`/`.spec` conventions of JS/TS).
* **Application Layer (`generate_test_file.py`):** Orchestrates framework detection, scaffold resolution, prompt construction, the AI invocation, JSON parsing and optional writing. `validate_test_syntax()` runs the local toolchain (`php -l`, `node --check`, `python -m py_compile`) when available — a validation failure is a **warning**, not an error.
* **Graceful Degradation:** With no API key, or with a non-JSON response from the model, the result is marked as low-confidence instead of raising (it strips the markdown fences and continues).
* **Presentation:** The `tests` group with the `generate` subcommand (`--file`, `--finding`, `--framework`, `--apply`, `--provider`); dry run is the default and overwriting an existing test asks for confirmation with **No** pre-selected. The chat delegates `/tests` to the **same** use case.
* **Tests:** `tests/domain/tests_generation/` (18), `tests/application/use_cases/test_generate_test_file.py` (4) and `tests/test_tests_command.py` (2).
* **Debt 🆕:** No new i18n key was added — the feature uses **17 `__()` keys** that do not exist in any of the 6 dictionaries (part of the 40 missing), and `SKILL_LABELS`/`mcp_server.SKILL_FILES` did not receive the `tests` type. There is no `docs/tests*.md`.

### **34. `gitpr explain` Subcommand and the `--explain` Flag — Reviewer Guide (`src/domain/pr/`) 🆕**

* **What It Is:** A guide centered on whoever is going to **review** — what changes, why it changes, where to focus and what the regression risk is — so that nobody has to reconstruct the intention from a raw diff.
* **Domain Layer (`explain_section_builder.py`):** `PrExplanation` / `ReviewerFocusPoint`, the `build_explain_markdown()` renderer and `parse_explain_payload()`, which tolerates non-JSON or malformed output and **detects placeholders** `[FILL]`/`[TODO]` to flag insufficient evidence instead of presenting a hollow guide as complete.
* **Application Layer (`generate_pr_explanation.py`):** Provider resolution, key validation, skill context loading (`explain`), prompt construction, invocation and parsing into the domain model.
* **Two Entry Doors:** `gitpr explain` (subcommand, with `--provider`, detection of a missing diff before any AI and colorized output) and the `--explain` flag on the root CLI, which appends the guide to the PR description body **and** to the JSON payload emitted, in a single reused `pr_desc_body`.
* **Configuration:** `GITPR_EXPLAIN_BY_DEFAULT` (default `false`) — when `true`, the section is appended to **every** generated description, which adds an AI call and increases cost/tokens per PR.
* **Skill:** `.gitpr.explain.md` registered in `SKILL_FILES_BY_TYPE`, with templates in 5 languages.
* **Tests:** `tests/domain/pr/test_explain_section_builder.py` (4), `tests/application/use_cases/test_generate_pr_explanation.py` (2) and `tests/test_explain_command.py` (2).
* **Debt 🆕:** The same pattern as `tests` — `__()` keys with no translation (part of the 40), missing from `SKILL_LABELS` and from `mcp_server.SKILL_FILES`, and with no topic in `docs/`.

### **35. Deterministic Suite and the First CI (`.github/workflows/tests.yml`) 🆕**

* **What It Is:** The first workflow that runs the suite, on **Python 3.10** (the floor declared in `pyproject.toml`, never exercised) and **3.13** (the development version in the `Pipfile`), with `fail-fast: false`.
* **What the CI Made Visible:** **22 tests** failed on a pt-BR machine because they assert the English literal while `__()` renders Portuguese — the suite was only green with `GITPR_LANG=en_us` on the command line. It was the direct motivation for hardening `conftest.py`.
* **Hermetic `tests/conftest.py` 🆕:** It pins `GITPR_LANG=en_us` (stops the suite from writing to the real profile and from rendering translations), `GITPR_LINTER_SECURITY=false` (three suites assert the rule list that the real `load_linter_rules()` returns) and `LANG_VERSION` at the code's version (stops the re-download of `~/.gitpr/langs/*.json` on every bump).
* **Order Matters:** `src.updater` is imported **before** the language variables are set, because `i18n` snapshots `os.environ` into `AMBIENT_ENV_KEYS` at import — a real session receives `LANG_VERSION` from the file, not from the shell.
* **Worker Test Stability:** `test_config_app.py` and `test_metrics.py` now wait for `workers.wait_for_complete()` before asserting.
* **Workflow Dependency:** The clean runner has no `~/.gitpr`, and `tests/demo/test_demo_isolation.py` asserts that the profile exists (it snapshots it to prove the tour does not write to it) — the job explicitly creates the directory and the empty `.env`.

---

## **📊 Tests and Quality**

| Test File | Scenarios | Focus |
|------------------|----------|------|
| `tests/test_blame_engine_ranges.py` | 7 | Blame by line range in a file |
| `tests/test_blame_metrics.py` | 7 | Blame metrics: depth, commits, duration |
| `tests/test_changelog_builder.py` | 15 | Changelog builder: sections, translatable headings, contributors |
| `tests/test_chat_backend.py` | 19 | Chat memory, persistence, slash commands |
| `tests/test_commit_classifier.py` | 23 | Conventional Commits classification (types, tolerant parser) |
| `tests/test_config_app.py` | 78 | Configuration TUI: mounting, category switching, dirty tracking, blocked F2, Ctrl+R, search, secrets |
| `tests/test_config_cli.py` | 9 | `config` subcommand registration, `-h`, lazy import, clean stdout |
| `tests/test_config_schema.py` | 42 | `DEFAULT_CONFIG` coverage, no duplicates, categories/kinds, `advanced` only in Advanced, **skills section** ⚠️ |
| `tests/test_config_store.py` | 22 | Round-trip on a temporary `.env`, comments and order preserved, idempotent `remove_config_value()` |
| `tests/test_config_suggest_reviewers.py` | 8 | Suggested reviewers configuration (keys and defaults) |
| `tests/test_config_validation.py` | 40 | Types, enums, templates, `validate_ai_key()` with a mocked SDK (401 vs. network vs. ollama) |
| `tests/test_core.py` | 49 | Main flows, git diff, PR generation, timing, staging, co-authorship, hooks language |
| `tests/test_diff_parser.py` | 26 | Diff parser by lines/hunks + `summarize_patch()` and `split_patch_sections()` |
| `tests/test_explain_command.py` | 2 🆕 | `explain` CLI: success, missing key, empty diff |
| `tests/test_external_linters.py` | 33 | Checkstyle bridge: XML parser, subprocess, diff cross-referencing, report |
| `tests/test_i18n.py` | 20 | Language parity, missing/orphan keys, identity — **assertion of 40 missing keys** ⚠️ |
| `tests/test_install_wizard.py` | 3 | Interactive installation assistant |
| `tests/test_issue_engine.py` | 4 | Structured issue draft |
| `tests/test_linter_metrics.py` | 4 | Linter metrics: errors, warnings, duration |
| `tests/test_linter_presets.py` | 5 | Linter presets: resolution and forced re-download |
| `tests/test_main_suggest_reviewers.py` | 8 | `--no-suggest-reviewers` flag in the CLI and in the contextual help |
| `tests/test_mcp_prompts.py` | 11 | MCP prompt templates and language fallback |
| `tests/test_mcp_server.py` | 104 | MCP tools (14), resources (18), annotations, patching, direct CLI, offload — **skill registry agreement** ⚠️ |
| `tests/test_mcp_server_e2e.py` | 6 | Real MCP server via subprocess + JSON-RPC stdio |
| `tests/test_metrics.py` | 34 | Collection, local export, repo scope, cache token summary |
| `tests/test_net_timeouts.py` | 12 | Network/AI timeouts — **aligned to the real 180s default** ✅ |
| `tests/test_plugins.py` | 17 | Plugin discovery, linter rule merge, MCP prompts |
| `tests/test_pr_publish_app.py` | 45 | PR Publisher TUI: screens, flows, suggested reviewers, `NoticeScreen` |
| `tests/test_pr_publish_linter_modal.py` | 4 | Linter error modal: abort, no-verify |
| `tests/test_pre_save.py` | 3 | --pre-save flag and JSON payload |
| `tests/test_release_cli.py` | 4 | Release CLI: options, help with documented epilog |
| `tests/test_release_engine.py` | 27 | Release engine: commit range, CHANGELOG, publishing |
| `tests/test_reviewer_resolution.py` | 18 | Resolution ladder, rejection of a partial name, dedup, failure tolerance |
| `tests/test_reviewer_suggestion.py` | 18 | Reviewer suggestion logic (ranking, exclusion, top-N, `last_commit_hash`) |
| `tests/test_security_ruleset.py` | 61 🆕 | Built-in ruleset matrix: regex, placeholders, level, wildcard, merge, translations |
| `tests/test_skill_command.py` | 10 | Skill template download and validation |
| `tests/test_skill_context.py` | 14 | `get_skill_context()` with `quiet=True`, fallbacks, skill registry |
| `tests/test_smart_excludes.py` | 15 | Smart pathspec filter and forced re-download |
| `tests/test_suggest_reviewers.py` | 17 | Suggested reviewers in the PR flow (integration, `no_login` hint) |
| `tests/test_tests_command.py` | 2 🆕 | CLI of the `tests` group and of `generate` |
| `tests/test_thinking_words.py` | 5 | Loading, parsing with the `;` separator and forced reload |
| `tests/test_updater.py` | 23 | PyPI gate: version parsing, daily cache, fetch, gate decisions, CLI wiring |
| `tests/test_usage_log.py` | 27 | Usage log: date-derived name, synchronous write, silence, multi-forge `_repo_label()` |
| `tests/test_version_bump.py` | 17 | Semantic bump: major/minor/patch, targets and validation |
| `tests/application/use_cases/test_generate_pr_explanation.py` | 2 🆕 | Explain use case: provider, key, parsing |
| `tests/application/use_cases/test_generate_test_file.py` | 4 🆕 | Test generation use case: dry-run/apply, syntax validation |
| `tests/badge/test_badge_append.py` | 9 🆕 | Idempotent append of the badge to the PR body |
| `tests/badge/test_badge_builder.py` | 19 🆕 | Composition of the shields.io Markdown, escaping, color rule, style |
| `tests/badge/test_badge_cli.py` | 15 🆕 | `gitpr badge` command: snippet, `--readme`, `--style` |
| `tests/badge/test_badge_data.py` | 7 🆕 | Alert counting; `None` with no rules configured |
| `tests/badge/test_badge_offline.py` | 6 🆕 | Offline guard (network forbidden, except loopback) |
| `tests/badge/test_badge_optout.py` | 17 🆕 | `GITPR_BADGE=false` on every publication path |
| `tests/badge/test_badge_paths.py` | 10 🆕 | Single injection point in the two publishers (TUI and `--no-edit`) |
| `tests/demo/test_demo_app.py` | 22 🆕 | Tour TUI: navigation, screens, help modal |
| `tests/demo/test_demo_isolation.py` | 14 🆕 | The tour touches neither `~/.gitpr` nor the network |
| `tests/demo/test_demo_runner.py` | 33 🆕 | `DemoState` state machine: forward, back, completed, text mode |
| `tests/demo/test_demo_scenarios.py` | 47 🆕 | Integrity of the recorded scenarios (hunk arithmetic) in the 5 languages |
| `tests/demo/test_demo_text_mode.py` | 18 🆕 | Plain-text output (`--no-tui`) for CI and recordings |
| `tests/demo/test_fake_ai_provider.py` | 21 🆕 | Fake provider contract: mirrors `call_ai_model` argument by argument |
| `tests/domain/linter/test_sast_finding_mapper.py` | 2 🆕 | Uniform formatting and `[Gitleaks + Regex]` deduplication |
| `tests/domain/pr/test_explain_section_builder.py` | 4 🆕 | Markdown rendering, tolerant parsing, `[FILL]` detection |
| `tests/domain/tests_generation/test_framework_detector.py` | 11 🆕 | Detection by config, manifest and directory; override; warned failure |
| `tests/domain/tests_generation/test_scaffold_builder.py` | 7 🆕 | Conventional path per framework (Laravel, Pytest, JS/TS) |
| `tests/fix/test_apply_fix.py` | 53 | Full use case: review → AI → validate → classify → dry-run/apply |
| `tests/fix/test_chat_shared_extractor.py` | 8 | Extractor shared between the chat and `fix` (identical behavior) |
| `tests/fix/test_fix_cli.py` | 31 | Subcommand routing, options, dry-run default, `--force` |
| `tests/fix/test_fix_history.py` | 21 | `.gitpr/fix_history.json` ledger: atomic write, read |
| `tests/fix/test_fix_settings.py` | 10 | The five `GITPR_FIX_*` and the invalid-value fallbacks |
| `tests/fix/test_patch_applier.py` | 19 | `git apply` wrapper: check/apply/reverse, branch, status |
| `tests/fix/test_patch_extractor.py` | 13 | Fenced blocks → validated unified diff |
| `tests/fix/test_patch_safety_classifier.py` | 22 | safe/review_required/experimental matrix and reason codes |
| `tests/fix/test_resolve_last_review.py` | 13 | Last review selection, exclusion of per-file reviews |
| `tests/fix/test_rollback_fix.py` | 14 | `--rollback`: reverse and the three refusals |
| `tests/infrastructure/linter/external/test_bandit_bridge.py` | 3 🆕 | Bandit bridge: availability, parsing, mapping |
| `tests/infrastructure/linter/external/test_base_bridge.py` | 4 🆕 | ABC: hardened subprocess, timeout, missing binary |
| `tests/infrastructure/linter/external/test_gitleaks_bridge.py` | 4 🆕 | Gitleaks bridge: secret masking, per-file scope |
| `tests/infrastructure/linter/external/test_semgrep_bridge.py` | 3 🆕 | Semgrep bridge: JSON parsing, severity, skipping of non-Python |
| `tests/review/test_diff_normalizer.py` | 20 | Newlines, diff validation, smart-excludes in Python |
| `tests/review/test_diff_source.py` | 12 | `DiffOrigin`/`DiffSource`: provenance, `cache_scope`, `is_remote` |
| `tests/review/test_remote_pr.py` | 40 | Orchestration: gates, PR, diff, linter, optional comment |
| `tests/review/test_review_pr_cli.py` | 25 | `review-pr` CLI: options, rejections before the AI, `--post-comment` |
| `tests/scm/test_azure_devops_provider.py` | 43 | Azure DevOps provider: org/project, PRs, `ScmNotSupportedError` |
| `tests/scm/test_bitbucket_provider.py` | 39 | Bitbucket provider: Basic auth, workspace |
| `tests/scm/test_contract.py` | 38 | `ScmProvider` contract: signatures, dataclasses, errors |
| `tests/scm/test_factory.py` | 11 | `resolve_scm_provider()` + `detect_provider_from_remote()` |
| `tests/scm/test_github_api_shim.py` | 18 | Deprecated `github_api` shim → delegates to the provider |
| `tests/scm/test_github_provider.py` | 68 | GitHub provider: REST, headers, PRs, issues, releases, reviewer read-back |
| `tests/scm/test_gitlab_provider.py` | 47 | GitLab provider: API v4, namespace, synthesized headers and `overflow` |
| `tests/scm/test_init_wizard.py` | 10 | `--init` wizard: forge detection, validation, persistence |
| `tests/scm/test_release_publish.py` | 8 | Release publishing per forge (GitHub/GitLab) |
| `tests/split/test_apply_split_plan.py` | 11 🆕 | The only module that mutates: selective staging, one commit per group |
| `tests/split/test_generate_split_plan.py` | 12 🆕 | Diff → units → groups → conflict pre-validation → messages |
| `tests/split/test_hunk_grouper.py` | 18 🆕 | Prompt, budget/trimming, AI call, response validation |
| `tests/split/test_hunk_parser.py` | 18 🆕 | `parse_units()`/`build_patch()`: text ↔ model, pure |
| `tests/split/test_selective_stager.py` | 11 🆕 | Subset staging, check against HEAD, clean index |
| `tests/split/test_split_cli.py` | 31 🆕 | Split CLI: options, dry run default, `--apply` |
| `tests/sync_i18n.py` | — | i18n coverage verification script (scaffold; never executed) |

**Total:** **1889 collected scenarios in 96 test modules** (44 at the root + 9 in `tests/scm/` + 10 in `tests/fix/` + **6 in `tests/split/`** 🆕 + **6 in `tests/demo/`** 🆕 + **7 in `tests/badge/`** 🆕 + 4 in `tests/review/` + **4 in `tests/infrastructure/linter/external/`** 🆕 + **2 in `tests/domain/tests_generation/`** 🆕 + **2 in `tests/application/use_cases/`** 🆕 + **1 in `tests/domain/pr/`** 🆕 + **1 in `tests/domain/linter/`** 🆕; **+452** since the previous report, with **32 new files**). Full run on this machine with `GITPR_LANG=en_us`: **1883 passed / 4 failed / 2 skipped / 81 subtests** in ~373s.

**Quality notes for this version:**
- **✅ The 3 inherited failures were closed.** The two outdated tests in `test_net_timeouts.py` (which asserted 600s against a code that has delivered 180s since fix `681a7fa`) were aligned — the item had been open for **three reports in a row**. And the locale-sensitive failure was solved by pinning `GITPR_LANG=en_us` in `conftest.py`, which also eliminated the **22 locale failures** that the CI revealed on a pt-BR machine.
- **⚠️ 4 new failures, a single root cause:** the two most recent features (`tests` and `explain`) landed with the **skill registry half done**. They are:
  1. `test_config_schema.py::TestSkillsSection::test_the_labels_cover_the_registry_exactly` — `SKILL_LABELS` has neither `tests` nor `explain`; without a label, the two render **blank** in the sidebar of the configuration TUI.
  2. `test_config_schema.py::TestSkillsSection::test_the_labels_follow_the_registry_order` — the same gap seen through the ordering: `SKILL_TYPES` has 10 entries, `SKILL_LABELS` has 8.
  3. `test_i18n.py::TestNoMissingKeys::test_no_missing_keys` — **40 `__()` keys** used in the code do not exist in **any** of the 6 dictionaries (all of them from the `tests` and `explain` commands). Since English is the fallback, they render as the key itself in every language.
  4. `test_mcp_server.py::TestSkillRegistryAgreement::test_the_two_skill_registries_agree` — `mcp_server.SKILL_FILES` and `config.SKILL_FILES_BY_TYPE` stopped agreeing; `skill://explain` and `skill://tests` are not exposed by the MCP.
- **The cause is a single one and it is cheap to close:** register the two types in `SKILL_LABELS`, in `mcp_server.SKILL_FILES` and run `python tests/sync_i18n.py` for the 40 keys. The relevant point is that the suite **detected** it — the three assertions exist exactly for that, and the CI runs them on two Python versions.
- **Growth of 452 scenarios** with the baseline of inherited failures brought to zero — the signal of this window: the suite stopped carrying known failures and started reporting new debt in the same commit in which it is born.
- `tests/conftest.py` became **hermetic**: `GITPR_SHOW_LOGS=false`, `GITPR_SKIP_UPDATE_CHECK=true`, `GITPR_LANG=en_us`, `GITPR_LINTER_SECURITY=false` and `LANG_VERSION` at the code's version — the suite does not write to the usage log, to the real `.env` or to `~/.gitpr/langs/`.
- **Real git fixtures:** `tests/fix/git_fixture.py` and `tests/split/git_fixture.py` build real repositories — `patch_applier` and `selective_stager` are only honest if the `git apply` is the real one.
- **Network guard:** `tests/demo/conftest.py` forbids the network by autouse — a regression there would poison a real cache with demonstration content.

---

## **🌐 Internationalization and Documentation**

* **i18n Coverage:** **1158 translation keys** in the 6 dictionaries, with **full key set parity** between them (+110 since the previous report). The chain measured per commit was 1048 → 1080 (`demo`, +32) → 1090 (`badge`, +10) → 1140 (`split`, +50) → 1151 (secrets, +11) → **1158** (SAST, +7). The last two commits (`tests`, `explain`) did **not** add keys — they use 40 that do not exist. ⚠️ The code uses **1198** keys.
* **`__lang_version__` went v0.0.28 → v0.0.29 → v0.0.30 → v0.0.31 → v0.0.32**, triggering the OTA re-download of the translations. The secret scanning and SAST added keys **without** bumping the marker; the bump to v0.0.32 covers both and is **in the working tree, uncommitted**.
* **Translation sources in lockstep:** a new key must exist in the code (source), in `langs/pt_br.json` (**master list**), in the FR/ES dicts of `scripts/sync_all_langs.py` (second source) and in the curated values of `scripts/fix_mangled_i18n_keys.py` (third source, read by `tests/test_i18n.py`); the `len(CLEAN_KEYS)` assertion still stands at 49.
* **New topics 🆕 (3):**
  - `docs/demo.md` — the guided tour: what it shows, the real pipeline with a fake answer, the scenarios and the isolation guarantee — **in 5 languages**
  - `docs/badge.md` — the PR badge: what it states, where it is attached, when it is omitted and how to print the static one for the README — **in 5 languages**
  - `docs/split-command.md` — atomic commits per hunk: the pipeline, the pre-validation, what `--apply` does to the index — **in EN + PT-BR** (the remaining 3 languages are pending)
* **New subdirectory 🆕:** `docs/tutorial/` with the `install-from-source.*` family **in 5 languages** (installation from source code).
* **Topics updated in this window:** `docs/linter-regras-customizadas.*` (5 — the `level` field, the built-in ruleset and the two escape hatches), `docs/git-hooks-locais.*` (5 — what the pre-commit hook now blocks and how to get around it), `docs/auto-update.*` (5), `docs/ARCHITECTURE.md`, plus `README.md` and its 4 translations (index with the `demo`, `badge`, `split-command`, `fix-command` and `review-pr` families).
* **Documentation in 5 languages:** **44 canonical topics** in `docs/` (+3) — **37 with full coverage in the 5 languages** (+2) and **7 partial/PT-only topics** (`como_reverter_commit_git_localmente`, `github-issue-prompt-com-gh`, `otimizacao-de-tokens`, `testar_sem_usar_pypi`, `version-markers`, `review-pr` with 2 languages and, now, `split-command` with 2).
* **Documentation gap 🆕:** `explain` and `tests` are the first two features in several windows to arrive **without a topic in `docs/`** — they only have the skill templates.
* **Claude Code local skills:** `.claude/skills/` with **29 skills** (count unchanged in this window).
* **Memory Index:** `.claude/memory/MEMORY.md` with **41 patterns** (+1 in this window).
* **Task reports:** `docs/claude-code/reports/develop_natan/` (**101** in total; **+8** in the window) and `docs/gemini/reports/develop_natan/` (**7**; **+2** — `2026-09-21_skill_gitpr_sast_bridge.md` and `2026-09-22_skill_gitpr_tests_generate.md`).
* **Status reports:** `docs/reports/` (15 reports; this is the 16th).
* **Development plans:** 115 files in `docs/plans/` (+16 in the window — specs/plans for `demo`, `badge`, `split`, secrets, SAST, `tests` and `explain`, ADR-006 and ADR-007, the `glossary-gitpr-split` and `glossary-gitpr-secret-scanning` glossaries) + **10 files in `docs/survey/`** (+4).

---

## **🔄 Distribution Pipeline**

1. **PyPI (single channel):** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **Mandatory update:** execution checks PyPI at startup and **blocks with exit code 1** if there is a newer version, printing `pip install --upgrade gitpr-cli`; the check is cached per day and `--update` only reports
3. **GitHub Releases:** removed — no PyInstaller, no `.exe` asset, no hot-swap
4. **GitHub Actions:** workflow `pr-review.yml` + `action.yml` (installs via pip) + **`tests.yml`** 🆕 (Python 3.10 and 3.13 matrix, with explicit creation of the `~/.gitpr` profile that `test_demo_isolation.py` asserts exists)
5. **MCP Server:** `gitpr-mcp` entry point via `pyproject.toml`
6. **Templates and OTA languages:** `templates/` and `langs/*.json` served from GitHub (main) — the `v0.0.32` bump renews the local copies in `~/.gitpr/langs/` once published
7. **Version derived from the code 🆕:** `pyproject.toml` uses `version = {attr = "src.updater.__version__"}` — `__version__` is the **only** source, and that is why an uncommitted bump leaves the built package at 1.2.0 while `CHANGELOG.md` already announces 1.3.0
8. **State of the 1.3.0 release 🆕:** the **`v1.2.0` tag was created** (merge of PR #174, 2026-09-17), closing the blocker of the previous window. `__version__` (1.3.0) and `__lang_version__` (v0.0.32) are **in the working tree and uncommitted** (HEAD at 1.2.0 / v0.0.31); `CHANGELOG.md` has the `[1.3.0]` entry committed, but **incomplete** — it covers only the secret scanning. The path is to extend the changelog with the 6 remaining features, run `gitpr release`, commit the bump and tag.

---

## **📈 Evolution Since the Previous Report (v0.0.15)**

| Area | v0.0.15 (previous) | v0.0.16 (current) |
|------|-------------------|-----------------|
| **GitPR Version** | 1.2.0 (bump **not committed**; HEAD at 1.1.0) | **1.3.0** (bump **not committed**; HEAD at 1.2.0) — **`v1.2.0` tag created** ✅ |
| **Language Version** | v0.0.28 | **v0.0.32** (via v0.0.29, v0.0.30 and v0.0.31; bump **not committed** — HEAD at v0.0.31) |
| **Hook Scripts Version** | v0.0.3 | **v0.0.3** |
| **AI Providers** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama |
| **Languages** | 5 languages, 6 dictionaries | 5 languages, 6 dictionaries |
| **Subcommands** | 4 (`release`, `config`, `fix`, `review-pr`) | **9** (+ `demo`, `badge`, `split`, `tests`, `explain`) |
| **Interface** | CLI + TUIs + `--init`/`--install` wizards + 4 subcommands | **+ `gitpr demo` (TUI + text mode) + `gitpr badge` + `gitpr split` + `gitpr tests generate` + `gitpr explain` + `--explain` flag** |
| **Layers** | `src/infrastructure/` (SCM) | **+ `src/domain/` and `src/application/use_cases/` (layered architecture)** 🆕 |
| **MCP Tools** | 14 tools / 18 resources / 7 prompts | **14 tools / 18 resources / 7 prompts** (unchanged — `skill://explain` and `skill://tests` **missing** ⚠️) |
| **CLI Flags** | 35 at the root + `release` (6) + `config` (0) + `fix` (8) + `review-pr` (2) | **36 at the root** (+`--explain`) + `release` (6) + `config` (0) + `fix` (8) + `review-pr` (2) + **`demo` (3) + `badge` (2) + `split` (5) + `tests generate` (5) + `explain` (1)** |
| **Environment Variables** | 44 keys in `DEFAULT_CONFIG` | **49 keys** (+5: 2 security + 3 split) — **+5 read-only outside `DEFAULT_CONFIG`** (`GITPR_BADGE`, `GITPR_EXPLAIN_BY_DEFAULT`, 3× `GITPR_SAST_*_ENABLED`) |
| **Config Schema** | 61 `ConfigField` / 13 categories | **71 `ConfigField` (+10) / 14 categories** (+ Split) |
| **Linter** | Regex + Checkstyle bridge | **+ built-in secrets ruleset (7 rules) + `extensions: ["*"]` wildcard + 3 opt-in SAST bridges (Semgrep, Gitleaks, Bandit) with multi-source dedup** |
| **Git Hooks** | `HOOK_SCRIPT_SUFFIXES`, `SCRIPTS_LANG`, `effective_hook_lang()` | Unchanged (but the pre-commit now **blocks secrets** by default) |
| **SCM Layer** | 4 forges, `get_pull_request`, `supports_reviewable_diff` | Unchanged in this window |
| **i18n (keys per file)** | 1048 × 6 (full parity) | **1158 × 6 (full parity) — +110**, but the code uses **1198** → **40 untranslated keys** ⚠️ |
| **Documentation** | 41 canonical topics (35 complete + 6 partial) | **44 canonical topics (37 complete + 7 partial) — 3 new families + `docs/tutorial/`** |
| **CI** | No test workflow | **`.github/workflows/tests.yml`** (Python 3.10 + 3.13) 🆕 |
| **Test Suite** | 1437 scenarios (64 files) | **1889 scenarios (96 files) — +452 scenarios, +32 files; en_us: 1883 passed / 4 failed / 2 skipped** |
| **Inherited failures** | 3 (2 timeout + 1 locale) | **0 — all closed** ✅ (4 new, one root cause) |
| **Commits since the report** | 5 commits | **8 commits** (`8920d13`, `da162d0`, `69f41ec`, `c38aed2`, `47784a7`, `a799664`, `7d84daf`, `6138cf1`) |
| **Merged PRs** | 3 PRs (#167, #171, #173) | **7 PRs (#177, #179, #181, #183, #185, #187, #189)** |
| **Memory Index** | 40 patterns | **41 patterns** |
| **Task reports** | 93 claude-code, 5 gemini | **101 claude-code (+8) and 7 gemini (+2)** |
| **Development plans** | 99 plans, 6 surveys | **115 plans (+16), 10 surveys (+4)** |

---

## **🚧 Next Steps**

* **Close the debt of the two new features 🆕:** register `tests` and `explain` in `SKILL_LABELS` (`src/config_schema.py`) and in `mcp_server.SKILL_FILES`, and run `python tests/sync_i18n.py` for the **40 keys** missing. That is **4 red tests** with a single root cause — it is the cheapest item on this list and the only one that today leaves the suite non-green.
* **Close the 1.3.0 release 🆕:** `__version__` (1.3.0) and `__lang_version__` (v0.0.32) are in the working tree with no commit. On top of that, the `[1.3.0]` entry in `CHANGELOG.md` **covers only the secret scanning** — `demo`, `badge`, `split`, SAST, `tests` and `explain` need to go into it before tagging.
* **Document `explain` and `tests` 🆕:** they are the first two features in several windows to arrive without a topic in `docs/` — only the skill templates and the specs in `docs/plans/` exist.
* **Translate what was left partial:** `docs/split-command.*.md` exists only in EN and PT-BR (pt_pt, es_es, fr_fr are missing), as does `docs/review-pr.*.md`.
* **Extend the coverage of the secrets ruleset 🆕:** the gaps are declared in the changelog itself — assignment **without quotes** (`API_KEY=abc123`, the `.env` format, which is precisely where secrets leak), the prefixes `ASIA…`, `github_pat_…` and `xoxc-`/`xoxd-`, and the absence of a left boundary on the key name (today `mytoken` matches the same as `token`).
* **Document the `request_pull_request_reviewers` contract break:** it went from `None` to `list[str]`; third-party subclasses of `ScmProvider` must be updated. `docs/scm-multiforge.*.md` is the place, in the 5 versions. **Inherited item, still open.**
* **`.gitpr/metrics/export/` in `.gitignore`:** four more CSV/JSON pairs entered tracked in this window (`2026-09-18`, `19`, `21` and `22`). They are locally generated artifacts. **The debt grew.**
* **Anthropic Claude provider:** Direct support for the Claude API (`claude-sonnet-5`).
* **ASCII/Textual charts in the Dashboard:** Time histograms and token trends in the metrics TUI.
* **Release Pipeline in GitHub Actions:** Automation of the build and of the upload to PyPI (the test CI now exists; the release one is missing).
* **Local `.gitpr/conf/` seed:** The seeding of local configuration templates (smart-excludes, linter) remains pending as its own subcommand or a wizard step.
* **More providers:** Direct OpenAI, additional local providers.
* **`sync_i18n.py` i18n extractor:** The regex truncates literals with implicit concatenation (`__("a " "b")`) — migrate to AST.
* **Reconcile the project version:** `CLAUDE.md` still says `Current version: 0.0.37` while `__version__` is at 1.3.0. Define a single convention. **Inherited item, still open.**
* **README index debt:** the `suggested-reviewers`, `scm-multiforge`, `config-tui` and `usage-log` families are still outside the index (the ones from this window — `demo`, `badge`, `split-command` — made it in).
* **`gitpr -h config` ignores `-h`:** the subcommand opens the TUI instead of showing the help — the gate `if ctx.invoked_subcommand is not None: return` runs before the `help_flag` block. Fixing it would change the behavior of `-h` for **all** subcommands, so it needs a decision.
* **Smart Exclude section in the TUI:** of the 12 items reported after using the screen, item 10 (*Smart Exclude*) is the only deliverable not yet started.
* **Debts recorded in the config TUI plan:** `DEFAULT_CONFIG` became redundant with the schema; the opening banner does not list `--dashboard`, `--init`, `--base` or `--plugins`; `LinterApp` does not disable the command palette.

### ✅ Completed in This Window (2026-09-17 → 2026-09-23)

* ~~**Align the outdated timeout tests**~~ — `tests/test_net_timeouts.py` now asserts the real 180s (`test_ai_timeout_defaults_to_180`) and the docstring of `get_ai_timeout()` was fixed. **An item that had been open for three reports.**
* ~~**Locale robustness in the tests**~~ — `tests/conftest.py` pins `GITPR_LANG=en_us`; the **22 locale failures** that the CI revealed on a pt-BR machine also disappeared.
* ~~**Close the 1.2.0 release**~~ — `v1.2.0` tag created (merge of PR #174) and the `[1.2.0] - 2026-09-17` entry is in `CHANGELOG.md`. **It was the item that blocked publication.**
* ~~**`gitpr demo` subcommand**~~ — `src/demo/` package + `src/ui/demo/` TUI, real pipeline with a fake provider, 2 scenarios in 5 languages and an isolation guard (PR #177).
* ~~**GitPR badge and `gitpr badge` command**~~ — `src/branding/`, honest measurement, single injection point, `GITPR_BADGE` opt-out and 7 test files (PR #179).
* ~~**`gitpr split` subcommand**~~ — `src/split/` package (6 files), `src/infrastructure/git/` with `selective_stager`, pre-validation against a temporary index (PR #181).
* ~~**Built-in secret scanning**~~ — `src/security_ruleset.py` (7 rules), the `extensions: ["*"]` wildcard, two opt-out variables and the alert that never echoes the value (PR #183).
* ~~**SAST bridges Semgrep/Gitleaks/Bandit**~~ — `src/infrastructure/linter/external/`, `[Gitleaks + Regex]` dedup, secret masking and strict opt-in (PR #185).
* ~~**`gitpr tests generate` subcommand**~~ — `src/domain/tests_generation/` + `src/application/use_cases/`, framework detection, syntax validation and the chat delegating to the same use case (PR #187).
* ~~**`gitpr explain` subcommand and `--explain` flag**~~ — `src/domain/pr/explain_section_builder.py` + `generate_pr_explanation.py`, tolerant parsing with `[FILL]` detection (PR #189).
* ~~**First CI for the suite**~~ — `.github/workflows/tests.yml` on Python 3.10 and 3.13, plus the hermetic `conftest.py` and the stabilization of the worker tests.
* ~~**i18n hygiene on the first run**~~ — `i18n.py` creates the profile directory before persisting the detected language.
* ~~**Shared `patch_applier`**~~ — promoted to `src/infrastructure/git/`, with `src/fix/patch_applier.py` kept as a re-export shim (nothing broke).

---

**Report generated on:** 2026-09-23  
**Branch:** `develop_natan`  
**Author:** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
