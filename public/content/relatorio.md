# **🚀 Project Status Report: GitPR CLI — v0.0.24 (2026-11-10)**

## **📌 Overview**

**GitPR** is an advanced CLI (Command Line Interface) tool for automating Git processes using Artificial Intelligence (Google Gemini / DeepSeek / Ollama). Its main goal is to act as a local intelligent assistant that performs Code Reviews, generates Pull Requests, semantic commit messages, audits technical debt, and injects best practices into the developer workflow (Shift Left).

**What's New in This Version (v0.0.24):**
- **The metrics dashboard gained ASCII charts and tabbed navigation:** the new `src/ascii_chart.py` renders a vertical latency histogram and a token time series with pure functions, with no new dependencies; `MetricsApp` gained the **Events** and **Charts & Trends** tabs, with `1`/`2` shortcuts ([ADR-0018](docs/adr/0018-dashboard-ascii-charts-and-tabbed-navigation.md)).
- **The prompt became more resistant to binaries:** `collapse_binary_hunks()` now **also** runs at the start of `generate_pr_content()`, and the smart excludes catalog gained `*.arrow`, `*.gguf`, `*.tflite` and `*.keras` — ML/data artifacts no longer reach the AI even when they come in through an external caller.
- **The extension release was decoupled from the rest of the release:** `extension.yml` gained the `github-release` job (`.vsix` attached to `ext-v*` releases) and the non-blocking `publish-marketplaces` job, which evaluates `VSCE_PAT` and `OVSX_PAT` **independently** and reports to `$GITHUB_STEP_SUMMARY`. The VSIX installation documentation (CLI and GUI) entered all 5 languages.
- **Cost and currency are no longer fixed:** the schema gained the `KIND_FLOAT` type, the `GITPR_METRICS_CURRENCY` key and **eight** `INPUT`/`OUTPUT` price fields per model; `DEFAULT_MODEL_PRICES` now also prices `gemini-pro-latest` and `gemini-flash-lite-latest`.
- **The migration notes were formalized:** the `action.yml` break to `gitpr check` ([ADR-0011](docs/adr/0011-action-unified-check-migration.md)), the interactive commit without a TTY, the strict `GITPR_SKIP_UPDATE_CHECK` and the project configuration seed/opt-out in CI now have explicit documentation.
- **Three new ADRs closed pending contracts:** [ADR-0017](docs/adr/0017-documentation-site-language-fallback.md) (site language fallback), [ADR-0018](docs/adr/0018-dashboard-ascii-charts-and-tabbed-navigation.md) (charts/tabs) and [ADR-0019](docs/adr/0019-project-configuration-seed-segregation.md) (segregation between runtime auto-seed and explicit provisioning).
- **1.6.0 and 1.7.0 were published:** PyPI serves **1.7.0**. The previous window's release debt is closed, and `develop_natan` was merged into `main` by PR #310.
- **Current version:** 1.7.0 (**published to PyPI**; the `v1.7.0` tag on origin still points to the pre-bump commit — see Distribution)
- **Language dictionary version:** v0.0.42 (🆕 +1 since v0.0.41; delivers the 15 new keys over OTA)
- **Hook scripts version:** v0.0.4 (unchanged)
- **Distribution:** PyPI (`pip install gitpr-cli`) — binary channel removed
- **Website:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Repository:** [https://github.com/gitpr-cli/gitpr.git](https://github.com/gitpr-cli/gitpr.git)
- **License:** LGPL-2.1
- **Supported languages:** en_us, pt_br, pt_pt, es_es, fr_fr (5 languages, 6 dictionaries)

---

## **🏗️ Architecture and Base Libraries**

* **Language:** Python >= 3.10
* **CLI Framework:** Click (for commands, flags and terminal formatting) — **16 subcommands** (`badge`, `baseline`, `check`, `config`, `demo`, `explain`, `fix`, `mentor`, `metrics`, `policy`, `release`, `review-pr`, `risk`, `seed`, `split`, `tests`). Four of them are **groups**: `baseline` (6 subcommands), `metrics` (8), `policy` (7) and `tests` (1). **34 Click options at the root** — unchanged in this window.
* **UI/Terminal:** Textual — TUI for interactive chat, issue editing, help screen, metrics dashboard, PR Publisher, **CommitApp** (`src/ui/commit_app.py`, 220 lines), linter errors (`LinterApp`), configuration (`ConfigApp`), the `NoticeScreen` modal, the `gitpr demo` tour (`src/ui/demo/`), the ledger migration assistant (`src/ui/metrics_migration_app.py`) and `PrBaseApp` (`src/ui/pr_base_app.py`). 🆕 `MetricsApp` now uses `TabbedContent` with the **Events** and **Charts & Trends** tabs and `1`/`2` bindings.
* **Layered Architecture:** `src/domain/` (pure rules, no I/O) + `src/application/use_cases/` (orchestration, **15 use cases**) + presentation (CLI/TUI). **9 domain packages**: `baseline`, `check`, `finding`, `linter`, `mentor`, `policy`, `pr`, `risk`, `tests_generation`. Outside the domain: `src/issue_autoclose/` (issue closing flow over `ScmProvider` and the local registry), `src/project_config.py` (versioned configuration), `src/scm_settings.py` (forge and credential resolution per repository) and 🆕 `src/ascii_chart.py` (pure 2D rendering, no I/O).
* **Editor Extension:** `extensions/vscode/` — thin TypeScript MCP client for **VS Code and Cursor** (Node.js 20+, `@modelcontextprotocol/sdk`, VSIX). Contains no business rules. 🆕 The workflow separates the VSIX release (`github-release` job) from the granular marketplace publishing gate.
* **Cryptography:** `cryptography.fernet` for local protection of API keys, GitHub tokens and SCM tokens for the forges.
* **Configuration:** `python-dotenv`, `pyyaml` (static linter and project configuration) + its own declarative schema (`src/config_schema.py`, 🆕 **109 `ConfigField`** and **18 categories**). `DEFAULT_CONFIG` is **derived** from the fields with `seeded=True` (🆕 **56 keys**). 🆕 The `KIND_FLOAT` type was added for prices and currency. `packaging` powers the PEP 440 gate version comparison and policy pack compatibility.
* **AI Providers:** Integration via the official Google GenAI SDK, OpenAI SDK (DeepSeek) and OpenAI SDK (local Ollama). Configured defaults: `gemini-pro-latest` / `gemini-flash-lite-latest` and `deepseek-v4-pro` / `deepseek-v4-flash`.
* **Forge APIs:** `requests` (REST) — multi-forge abstraction in `src/infrastructure/scm/` (GitHub, GitLab, Bitbucket, Azure DevOps) + `web_links.py`; `src/github_api.py` kept as a deprecated shim. The issue contract (`get_issue`, `get_default_branch`, `closing_line`) is implemented by the **four** forges where it makes sense.
* **Linter / SAST:** YAML regex + Checkstyle bridge + embedded secrets ruleset (`src/security_ruleset.py`) + 4 bridges in `src/infrastructure/linter/external/` (base + Semgrep, Gitleaks, Bandit), with a normalized model in `src/domain/linter/sast_finding_mapper.py` and the canonical finding in `src/domain/finding/`.
* **CI Infrastructure:** `src/infrastructure/ci/` — `sarif_builder.py` (SARIF 2.1.0), `junit_builder.py` (JUnit XML) and `masking.py` (credential masking before serializing any artifact).
* **Git Internals:** `src/infrastructure/git/` — `patch_applier.py`, `selective_stager.py`, `identity.py`, `risk_history_reader.py` and `test_matcher.py`.
* **Local Persistence:** `src/ledger.py` — SQLite (`~/.gitpr/metrics/telemetry.db`) with WAL, `PRAGMA user_version`, UUID keys and bundle transport. The price table lives in `src/config.py` (`DEFAULT_MODEL_PRICES`) and now accepts per-model and currency overrides.
* **Network:** `src/net.bounded_urlopen` (`UPDATE_FETCH_HARD_TIMEOUT = 3.0`) bounds the **wall clock including DNS** in the update gate. `get_issue()`/`get_default_branch()` use a **5 s** timeout (`GITPR_ISSUE_AUTOCLOSE_TIMEOUT`).
* **MCP:** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 — **18 annotated tools**, **19 resources**, 7 prompts (count unchanged; no server file changed in this window).
* **Tests:** Pytest + `unittest.mock` (**160 test modules** — 64 at the root, 12 in `tests/application/use_cases/`, 10 in `tests/fix/`, 10 in `tests/scm/`, 10 in `tests/infrastructure/` (baseline 1, ci 3, git 2, linter 4), 21 in `tests/domain/` (baseline 5, check 3, finding 1, linter 2, mentor 1, policy 4, pr 1, risk 2, tests_generation 2), 7 in `tests/badge/`, 6 in `tests/demo/`, 6 in `tests/split/`, 5 in `tests/issue_autoclose/`, 4 in `tests/review/`, 3 in `tests/integration/`, 2 in `tests/unit/` —, **3436 collected scenarios**) + MCP server e2e tests via a real subprocess (JSON-RPC stdio) + real git repository fixtures (`tests/fix/`, `tests/split/`, `tests/test_full_diff_encoding.py`) + network isolation guards (`tests/demo/conftest.py`, `tests/badge/conftest.py`, `tests/integration/conftest.py`, `tests/split/conftest.py`). The extension has its own Mocha suite (7 tests against the real MCP server).
* **Packaging:** setuptools/build (PyPI) — `version = {attr = "src.updater.__version__"}`; embedded packs via `package-data`; `packaging>=23.0`. The extension is packaged separately (`vsce package`, VSIX).
* **CI/CD:** GitHub Actions — `pr-review.yml` + `action.yml` (calls `gitpr check`) + `tests.yml` (Python 3.10/3.13 matrix) + `extension.yml` (VSIX build, test and packaging; 🆕 its own release on `ext-v*` and a granular per-marketplace gate) + `release.yml` (tests, tag/version verification via SSOT, sdist/wheel build, `twine check --strict`, Trusted Publishing OIDC to PyPI and GitHub Release creation on `v*` tags).

---

## **🧩 Implemented Modules and File Architecture**

### **1. Core and Git Operations (`src/core.py`)**

* **Structured Generation:** Communicates with the LLM requesting a strictly JSON response.
* **Map-Reduce (Giant Diffs):** When the diff exceeds ~90k tokens, it is automatically split into per-file batches (`split_diff_into_chunks`), each part is processed (Map) and the summaries are unified (Reduce). Supports PRs, commits and Issues. The `provider`/`model` are aggregated across chunks and the `meta`/`modules` are forwarded to `log_command_metric`.
* **Local Tokenizer:** `tokenizer.json` for precise token estimation before sending to the AI.
* **Token Estimation:** Lightweight heuristic `len() // 4` via `estimate_token_count()` with a fallback to the local tokenizer.
* **Native Git Optimization:** Flags `-U1`, `-w`, `-M`, `-B` in the `get_git_diff` and `get_git_full_diff` commands.
* **`get_git_staged_diff()`:** runs `git diff --cached -U1 -w -M -B -- <SMART_EXCLUDES>` with `encoding="utf-8"`/`errors="replace"` and `stdin=DEVNULL`, returning `None` when git fails (never a disguised empty string). It is the preferred source of the commit message.
* **`get_split_diff()`:** `split`'s own diff, with `SPLIT_DIFF_ARGS` (`--binary -M -U3`), **deliberately without** reusing `get_git_diff()`.
* **Binary Hunk Guard:** every diff entry point (`get_git_diff`, `get_git_staged_diff`, `get_unstaged_diff`, `get_git_full_diff`, `current_diff`, `get_check_diff`, `tests_generate`, `risk`) applies `collapse_binary_hunks()` — hunks with contiguous runs of ≥8 `U+FFFD` (or ≥32 with density ≥10%) become `Binary files … differ`. The O(1) fast path returns the diff untouched when there is no `U+FFFD`, preserving legitimate Latin-1/Windows-1252 files.
* **🆕 Defensive Collapse in `generate_pr_content()`:** the function now applies `collapse_binary_hunks(diff_text)` **before** the timing and the AI call, also covering external callers that deliver the raw diff — not just the path that came from git.
* **🆕 Four New Binary Extensions:** `*.arrow`, `*.gguf`, `*.tflite` and `*.keras` entered both the embedded smart excludes fallback and `templates/gitpr.smart-excludes.json`.
* **Local Configuration Seed (`seed_project_configuration()`):** creates `.gitpr/conf/gitpr.config.yml`, `.gitpr/conf/gitpr.smart-excludes.json` and `.gitpr/skill/.gitpr.linter.yml`, returning a structured summary (`config`, `smart_excludes`, `linter`, `skills_downloaded`). It never overwrites without `force=True`.
* **Project Configuration Seed:** the root callback and `--install` call `ensure_project_config()` (`src/project_config.py`) on every invocation inside a repository, except on help paths. An existing file is **never** overwritten; creation is announced only on the root's interactive path and can be disabled by `GITPR_SKIP_PROJECT_CONFIG_SEED` (escape hatch for the test suite).
* **`get_base_branch(quiet=)`:** base resolution delegates to `resolve_pull_request_base()`; `quiet` keeps the fallback warning from corrupting the TUI or the JSON-RPC stream.
* **Pre-Save (`--pre-save`):** Hidden debug flag that saves the full payload as JSON before every AI call.
* **Two-Layer Smart Excludes:** Global layer (`~/.gitpr/conf/`) + project-local (`./.gitpr/conf/`), merged at runtime. The global and local loaders were hardened against corrupted `.env` and unreadable paths.
* **Metrics with Time Tracking:** `log_command_metric()` in all flows, with `meta`, `modules` and `duration_ms`.
* **Centralized Output Resolution:** `resolve_output_path()` — defaults to `.gitpr/reports/{type}/`.
* **SCM Wizard (`run_scm_init_wizard()`)**: `gitpr --init` — detects the forge from the origin remote, requests per-forge extras, validates the token and persists **only on success**; it writes to the chosen forge's slot and only creates a project override when that forge differs from the detected one.
* **Release Skill Template (`ensure_release_skill_template()`)**: Downloads `templates/gitpr.release.*.md` on the first use of `gitpr release`.
* **Corrupted Skill Guard:** `_CORRUPTED_PR_SKILL_ARTIFACT` documents the JSON fragment that leaked into the `pt_br` template, and `get_skill_context()` removes it **in memory** when `action_type == "pr"`.
* **Shared Skill Registry:** `SKILL_FILES_BY_TYPE` / `SKILL_TYPES` / `DEFAULT_SKILL_TYPE` in `src/config.py`.
* **Review Engine with Cache Scope (`generate_pr_content`):** `cache_scope` and `store_diff`; the `cache_scope` also carries the active policy pack.
* **Co-authorship Trailer:** `COAUTHOR_TRAILER` + `append_coauthor_trailer()` — idempotent, preserves third-party trailers.
* **Hardened Git Output Capture:** the `subprocess.run` calls in `src/cache.py`, `src/core.py`, `src/issue_engine.py` and `src/main.py` that capture git output use `encoding="utf-8"` **with** `errors="replace"`.
* **Resilient `.env` Loading:** `load_dotenv()` in `core.py`, `i18n.py` and `spinner.py` runs inside `try/except`.
* **Git Failure ≠ Empty Diff:** `diff_text is None` exits with code 1; a genuinely empty diff exits **0**; in `--hook` the path is fail-open.
* **Armored Subprocesses:** `stdin=subprocess.DEVNULL` + `encoding='utf-8'`/`errors='replace'` in every `subprocess.run`; connection check via socket before network operations.

### **2. Global Plugin System (`src/plugins.py`)**

* **Plugin Architecture:** Loads plugins from `~/.gitpr/plugins/` applying to **all projects**.
* **Linter Plugins (`linter/`):** `.yml` files merged with the local `.gitpr.linter.yml`.
* **MCP Prompt Plugins (`prompts/`):** `.md` files that extend the system context.
* **Factory Closures:** `get_linter_plugins` and `get_prompt_plugins` isolate state between sessions.
* **`--plugins` Command:** Lists all installed global plugins with types and paths.
* **Multilingual Documentation:** `docs/plugins-system.md` in 5 languages.
* **Relationship with Policy Packs:** the pack is a **third** layer of rules — plugins belong to the machine, `.gitpr.linter.yml` belongs to the project and the pack is versioned and committed.

### **3. CLI Interface and Setup (`src/main.py` and `src/config.py`)**

* **Initial Setup:** Detects the first run, creates `~/.gitpr/` and interactively requests keys, preferences and language.
* **Command Routing:** Manages all flags and the **16 subcommands** — `release`, `config`, `fix`, `review-pr`, `demo`, `badge`, `split`, `tests`, `explain`, `mentor`, `metrics`, `policy`, `risk`, `baseline`, `check` and `seed`.
* **Default Behavior:** Running `gitpr` with no flags opens the PR Publisher TUI.
* **Flags (34 Click options at the root):** `-sd/--seed` seeds the project's local configuration and exits; `--close-issue`/`--no-close-issue` remain mutually exclusive, refused with exit code 1 before any prompt or write, with the rule that `-h` wins the conflict. The others (`--mentor`, `--explain`, `--init`, `--no-suggest-reviewers`, `--no-publish`, `--no-edit`, `--base <branch>`, `--plugins`, `--status`, `--no-unstaged-check`, `--linter-setup`, `--version` etc.) remain unchanged.
* **`-c`/`--commit` branch:** `--hook` → silent, merge-aware behavior; without a hook → `CommitApp`. The hook-less path requires a TTY: routing checks `_may_ask_the_user()` and, in a script/CI/pipe, falls back to the console flow.
* **Issue Closing Flow:** `_prepare_close_issue(flags, base, may_ask)` runs **before** the Stage Selector, reuses the resolved SCM context and returns the decision (closing line + issue) plus the registry entry. The line enters `insert_close_line(pr_desc_body, decision)` at the single body-assembly point. `_publish_pr_directly` returns a **bool** so the registry entry is only cleared after a real publication (ADR-007).
* **PR Base Resolution:** the `--base` branch goes through `_resolve_pr_target()`, which repairs a stale value from the project file when the run is interactive and, outside it, reports the refusal. The `.gitpr/conf/gitpr.config.yml` file is seeded on every invocation inside a repository, except on help paths.
* **Update Gate in Subcommands:** the gate runs before the `return`, conditioned on `may_ask and not update and not help_flag and not _subcommand_help_in_argv()`. `_may_ask_the_user()` requires a **TTY on both ends** — CI, pipes, cron, hooks and the MCP server stay free.
* **`-h` Before the Subcommand Fixed:** `_subcommand_help_in_argv()` scans the raw `argv` and handles the `gitpr -h config` case, which previously opened the configuration TUI instead of the help. Covered by `tests/test_cli_help.py`.
* **`check` Subcommand — 8 options:** `--base <ref>`, `--format sarif|json|junit`, `-o/--output <path>`, `--fail-on …`, `--no-ia`, `--ia-only-high-risk`, `--baseline <path>` and `--policy <name>@<version>`.
* **`seed` Subcommand — 2 options:** `--force/-f` (overwrites existing files) and `--all/-a` (also downloads all AI skills).
* **`baseline` Group — 6 subcommands:** `create`, `show`, `validate`, `update`, `suppress` and `unsuppress`.
* **`policy` Group — 7 subcommands:** `list`, `show`, `validate`, `use`, `off`, `install` and `init`.
* **`metrics` Group — 8 subcommands:** `export`, `bundle`, `merge`, `dashboard`, `migrate`, `prune`, `purge` and the hidden `hook-event`.
* **`risk` Subcommand — 4 options:** `--file`, `--format text|json`, `--base`, `--no-history`.
* **`mentor` Subcommand — 2 options:** `--finding`, `--provider`.
* **Derived `DEFAULT_CONFIG`:** `src/config.py` imports `DEFAULT_CONFIG` from `src/config_schema.py`, built as `{field.key: field.default for field in FIELDS if field.seeded}` — 🆕 **56 keys** (was 55). Drift between code and schema is impossible.
* **🆕 Prices and Currency in the Schema:** `GITPR_METRICS_CURRENCY` (default `USD`) and eight `GITPR_METRICS_PRICE_<MODEL>_{INPUT,OUTPUT}` fields were added to the `ai` category, all with `KIND_FLOAT` and an empty default.
* **Environment Variables:** the four `GITPR_BASELINE_*`, the two mentor ones, the two policy ones and `GITPR_RISK_INCLUDE_IN_REVIEW`. **Outside `DEFAULT_CONFIG`:** `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT`, `GITPR_METRICS_CURRENCY`, the **9 `GITPR_CHECK_*`**, `GITPR_SKIP_UPDATE_CHECK` (strict boolean) and `GITPR_ISSUE_AUTOCLOSE`, `GITPR_ISSUE_AUTOCLOSE_TIMEOUT` and `GITPR_SKIP_PROJECT_CONFIG_SEED`.
* **Contextual Help:** `gitpr -h --explain` renders; `HELP_MAP["explain"]` and `HELP_PRIORITY["explain"]` point to `docs/explain-pr`, and `gitpr tests`/`gitpr tests generate` have their own `epilog=`. `HELP_MAP` includes `seed` and the `close_issue`/`no_close_issue` links point to `docs/issue-autoclose`.
* **`.env` Write Layer:** `read_env_file_values()` reads **only the file** via `dotenv_values`; `save_config_values()` with `set_key`; `remove_config_value()` with `unset_key`.
* **Baseline Gate in the Flow:** `main.py` resolves the baseline gate before lint, review, full review, file audit, remote review and risk.
* **Direct Publication Refactored:** `_publish_pr_directly` (`--no-edit`) delegates to the **`publish_pull_request_use_case`** — the same path as the `publish_pull_request` MCP tool — and returns whether the forge created the PR.

### **4. PR Publisher TUI (`src/ui/pr_publish_app.py` and `src/ui/pr_publish_help.py`)**

* **Complete Interactive Interface:** TUI to review, edit and publish Pull Requests straight from the terminal.
* **7 Modal Screens:** `StageFilesScreen`, `CommitConfirmScreen`, `CommitProgressScreen`, `CommitMessageScreen`, `LinterErrorScreen`, `ErrorScreen` and `NoticeScreen`.
* **Three of Them Also Serve the Commit:** `StageFilesScreen`, `CommitMessageScreen` and `CommitProgressScreen` are imported by `CommitApp` — no widget was duplicated, and the `_with_suppressed_stdout` helper is shared too.
* **`body_published` Distinguishes Creating from Opening:** `PrPublishApp` marks when the body was actually created/updated on the forge, not merely when an existing PR was opened. That is the signal that allows clearing the issue registry entry only after a real publication.
* **Badge in the PR Body:** `attach_pr_badge()` runs in `src/main.py` right after `pr_data` is complete — **single injection point**.
* **`_attach_reviewers` with resolution:** Resolves what the user typed **before** sending, reports what was dropped and, on a batch `422`, **retries one by one**. When a third-party provider follows the legacy contract and returns `None` on read-back, the log says `(no read-back)` instead of `attached None`.
* **Suggested Reviewers:** Queries the forge for suggested reviewers; `_reviewer_suggestion_view()` builds `resolutions` via `resolve_candidates`.
* **Bindings:** F1 (Help), F2 (Save local .md), F3 (Publish via forge), Esc (Quit).
* **Auto-Commit Flow:** Linter → AI message → confirmation → commit → push → publish PR.
* **Merge Progress Modal:** `CommitProgressScreen` with an optional `title` and `mark_finished(show_close=False)`; success displays `✅ PR #N merged successfully!`, conflicts and unexpected errors go through `_on_merge_failure`.
* **Existing PR Handling / Auto-Upstream / Merge Flow** — unchanged (`GITPR_AUTO_MERGE`).

### **5. GitHub API Module (`src/github_api.py`)**

* **Deprecated Shim:** `create_pull_request()`, `update_pull_request()`, `merge_pull_request()` and the other functions delegate to `src/infrastructure/scm/github_provider.py`; it emits a `DeprecationWarning` and keeps the legacy `(ok, data, status)` tuples — no new code may import it.

### **6. Static Analysis Engine / Linter (`src/linter_engine.py`)**

* **Offline Linter:** Statically analyzes the added (`+`) lines in the diff without spending AI quotas.
* **YAML Rules:** Reads `.gitpr.linter.yml` (created via `--skill` or `gitpr seed`).
* **Linter Plugins:** Additional rules from `~/.gitpr/plugins/linter/*.yml`.
* **Extension Wildcard:** `extensions: ["*"]` means **every** file, including those without a suffix and dotfiles.
* **Built-in Secrets Ruleset:** `load_linter_rules()` merges `src/security_ruleset.py` **after** the project rules and the plugins. Seven rules, all `extensions: ["*"]`: five `error` and two `warning`. The alert **never echoes the value**.
* **Policy Pack Layer:** `load_linter_rules()` applies the layered merge of the active pack and **overrides severities**.
* **Structured Findings (`findings_out`):** beyond the alert strings, the engine returns the normalized findings of the canonical type `src/domain/finding/`.
* **Opt-In SAST Bridges:** Semgrep, Gitleaks and Bandit run on the whole-file path **and** on the diff path, with `[Gitleaks + Regex]` dedup, value masking (`AKIA****`) and a warning when the tool is enabled but not on `PATH`.
* **`skip_external`:** `parse_diff_and_lint(..., skip_external=True)` in the remote flow.
* **Consolidated Report:** `generate_linter_report_content()` consolidates regex + external errors into `.gitpr/reports/linter/` — generated only when there are violations.
* **Baseline-aware:** alerts are annotated with the baseline status before they become a report.

### **7. Security and Authentication (`src/security.py`, `src/scm_settings.py`, `src/tui_issue.py`)**

* **Cryptography:** Generates the `secret.key` master key in `~/.gitpr/`.
* **Token Protection:** `encrypt_data`/`decrypt_data` for AI keys and for **each** forge's token (one slot per SCM provider).
* **Per-Forge Credential:** a GitLab token never answers for a GitHub repository. The raw `GITPR_SCM_TOKEN` remains the **single** global CI/CD override and is tried first on every forge.
* **Multi-Forge Validation:** `validate_or_request_scm_token(provider, repo_display, allow_prompt=True)` — 401 → reauthentication loop preserving the draft. `allow_prompt=False` (hooks, MCP, CI) never asks, never deletes the stored token and returns the "run `gitpr --init`" instruction.
* **Secrets in the Configuration TUI:** `KIND_SECRET` fields are edited masked, **never** display the value in the clear and are encrypted with Fernet before writing.
* **Secrets Scanning in the Flow:** The linter — which runs in the pre-commit hook — is a credential gate with identical rules on every machine.
* **Masking in CI:** `src/infrastructure/ci/masking.py` removes private keys, AWS access key ID/secret, GitHub, Google, OpenAI and Slack tokens, `Bearer`, basic-auth in URLs and generic password assignments **before** the artifact is serialized. The extension has a TypeScript mirror (`masking.ts`).

### **8. Auto-Updater (`src/updater.py`)**

* **PyPI as the Single Source:** `get_latest_remote_version()` queries `https://pypi.org/pypi/gitpr-cli/json` and writes the cache. It accepts `force=True`, used by `--update` to **always** go to the network.
* **Rolling 1-Hour Window (`UPDATE_CACHE_TTL_SECONDS = 3600`):** the document is `{"checked_at": <ISO>, "version": ...}`; `_cache_is_fresh()` accepts an age in `[0, 3600)` seconds.
* **Validation on Read:** a real `dict`, a parseable timestamp, a non-empty string version. A timestamp **in the future** counts as expired, and the legacy format with `date` is treated as expired and rewritten.
* **Atomic Write:** `tempfile.NamedTemporaryFile` in the same directory + `os.replace`, with cleanup of the temporary file on failure.
* **Hardened Fetch (`bounded_urlopen`):** a non-JSON body and a payload without a version are **rejected before entering the cache**. A 3 s wall-clock ceiling, DNS included.
* **PEP 440 Comparison:** `_coerce_version()` uses `packaging.version.Version`, tolerating the leading `v` and PEP 440 suffixes; `parse_version()` keeps the tuple contract for external callers. `_is_newer()` **ignores a remote pre-release** on a stable installation and treats an unreadable version on either side as unknown.
* **Mandatory Gate (`enforce_update_required()`):** Returns `True` (after printing both versions and the pip command) when the published version is newer; `False` when it is up to date, when the remote version is **unknown (offline)** or when the check is disabled.
* **Strict Escape Hatch Semantics:** `GITPR_SKIP_UPDATE_CHECK` is parsed against `_SKIP_TRUTHY_VALUES` (`1`, `true`, `yes`, `on`, `y`). The migration note for this semantics entered `docs/auto-update.*` and the installation tutorial.
* **`check_and_update()`:** Only queries and **informs**, never installs.
* **Centralized Versioning:** `__version__` (**1.7.0** 🆕), `__lang_version__` (**v0.0.42** 🆕), `__scripts_version__` (**v0.0.4**), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`, `LINTER_PRESETS_VERSION`. The package SSOT is an explicit contract ([ADR-014](docs/adr/0014-canonical-package-version-ssot.md)).
* **✅ Release status — 1.6.0 and 1.7.0 published:** PyPI serves **1.7.0**. **1.6.0** was tagged at `f5088c4`; **1.7.0** has its own section in `CHANGELOG.md` (`## [1.7.0] - 2026-11-10`) and a bump at `2354a30`. ⚠️ The `v1.7.0` tag on origin still points to `b2688c4` (pre-bump, `__version__` 1.6.0) — aligning the tag is the open release pending item.
* **⚠️ SSOT Consistency:** the `2354a30` bump changed only `src/updater.py`; `CLAUDE.md` and `GEMINI.md` still say 1.6.0, and therefore `tests/unit/test_version_consistency.py` has **2 failures** (`test_claude_md_version_matches_ssot` and `test_gemini_md_version_matches_ssot`).

### **9. Interactive Chat Interface (`src/ui/chat_app.py`)**

* **Complete TUI:** Message history, multi-line input, status bar with visible bindings. `ENABLE_COMMAND_PALETTE = False`.
* **Per-Branch Memory (`src/chat_memory.py`):** History persisted per branch, with continuity between sessions.
* **Slash Commands:** `/explain`, `/tests`, `/optimize`, `/clear`. `/tests` (and the localized aliases) delegates to the **same use case** the CLI uses.
* **Auto-Patching (F5), Diff Refresh (F2), Session Export (F6).**
* **Shared Extractor:** F5 and `ctrl+s` call `patch_extractor.extract_code_blocks()` — identical visible behavior, with no duplicated logic.
* **Genuinely Localized Help:** `ChatHelpScreen` assembles each line via `__()`. Help links go through `doc_url()`, with the site's `?lang=` contract (`es_es`→`es`, `fr_fr`→`fr`) — formalized in [ADR-0017](docs/adr/0017-documentation-site-language-fallback.md).

### **10. Internationalization — i18n (`src/i18n.py`)**

* **Laravel-Inspired System:** `__()` function with named placeholders (`{count}`, `{file}`, etc.).
* **Automatic Detection:** Detects the OS language on first run and saves it to `GITPR_LANG`; the profile directory is created **before** persisting.
* **5 Languages, 6 Dictionaries:** en_us (default/fallback), pt_br, pt_pt, es/es_es, fr/fr_fr.
* **Versioned Files:** `__lang_version__` (**v0.0.42** 🆕) controls package updates (`langs/*.json`).
* **✅ Coverage:** the **six** files have **1787 keys** each (+15). The code uses the same keys; there is no key without a dictionary and no orphan. `tests/test_i18n.py` remains **22 green**.
* **🆕 v0.0.42 has content:** the new strings for per-token prices, currency, float validation, ASCII charts/tabs and VSIX installation instructions entered the six dictionaries; the OTA refresh delivers new translation.
* **Environment Snapshot (`AMBIENT_ENV_KEYS`):** `frozenset(os.environ)` captured **immediately before** the module-level `load_dotenv()`.
* **Cache with Language Indexing:** Cached AI responses include the current language in the MD5 keying.
* **Translation Source in Lockstep:** a new key must exist in the code, in `langs/pt_br.json` (master list), in the FR/ES dicts of `scripts/sync_all_langs.py` and in the curated values of `scripts/fix_mangled_i18n_keys.py`; the extractor uses **AST**.

### **11. Animated Spinner (`src/spinner.py`)**

* **Braille + Thinking Words:** Background thread during AI calls with braille characters and "thinking" words.
* **263 entries per language:** Synchronized across the 5 languages. `_load_thinking_words()` / `reload_thinking_words()` accept `force=`.
* **The Commit TUI Does Not Use the Spinner:** the TUI shows `CommitProgressScreen` with its own animated bar, and the AI call runs in a daemon `threading.Thread`. In `--hook` the classic spinner remains the only feedback.
* **Hardened `.env` Loading:** a read failure does not prevent the spinner from starting.

### **12. AI Providers (`src/ai_providers.py`)**

* **3 Supported Providers:** Google Gemini, DeepSeek and Ollama (local), with a primary/secondary per provider.
* **JSON Mode & Deterministic Parameters:** `temperature=0.0` and `top_p=0.1`; automatic fallback between configured providers.
* **AI Timeout:** default of **180s** (`GITPR_AI_TIMEOUT`).
* **Calls with `quiet`:** release and the new features forward `quiet` down to the AI layer, so the spinner never corrupts a `--format json` output.
* **One Call at a Time, with Cache:** mentor and explain reuse the MD5 cache per prompt.
* **Already-Parsed Payload:** `parse_explain_payload()` and `generate_test_file` accept the **dict** the provider delivers, with a fallback to the raw JSON string (old cache responses).
* **Stdout Suppression in Commit:** `_generate_commit_msg()` redirects `sys.stdout` to `os.devnull` during the call, restoring it in `finally`.

### **13. Smart Cache (`src/cache.py`)**

* **MD5 + Metadata:** Keying by MD5 hash of the diff and prompt, with language indexing.
* **Last Review Selection (`resolve_last_review()`):** Picks the **newest** `review`/`fullreview` record for a repo+branch pair — it is the entry point of `gitpr fix` and of `gitpr mentor`.
* **Reviewed Diff (`reviewed_diff`):** Field at the top of the record that stores the diff that was actually reviewed.
* **Telemetry and Duration:** Persistence of `duration_ms` and `meta_raw`. Release now keys the cache by declared language, so changing the skill's language generates a new section instead of reusing the old one.
* **Dashboard Reading:** `scan_cache_files_for_dashboard()` is a **pre-migration bridge**.
* **Effect of Skill Sanitization:** since `get_skill_context()` removes the corrupted fragment from the PR prompt, the MD5 key changes and old contaminated responses are no longer reused.
* **`git config` Decoded Safely:** the `user.name`/`user.email` reads use `encoding="utf-8"` + `errors="replace"`.
* **The Commit Reuses the Cache:** the TUI calls `generate_pr_content("commit", "commit", diff_text, provider)`, so the same message for the same diff does not trigger a second AI call.

### **14. Issue Engine and TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Context Engines:** Current diff, branch history (`-ht`) and Blame archaeology (`-b`).
* **Multi-Forge Publishing:** F3 creates the issue on the forge **resolved for the repository** via `provider.create_issue` — Azure DevOps raises `ScmNotSupportedError`.
* **Map-Reduce for Issues:** Context above ~90k tokens is split and unified.
* **401 Handling:** Reauthentication signaling without closing the application.
* **`git remote -v` Decoded Safely:** the call uses `encoding="utf-8"` + `errors="replace"`.
* **Provider-Agnostic Registry:** after a successful creation on any supported forge, `register_created_issue()` writes the number **and provider** to `.gitpr/conf/issue.yml`. Bookkeeping failures are swallowed — a local problem never turns a successful creation into an error — and the first write adds the file to `.gitignore`.
* **Per-Forge Metric:** the creation event is derived dynamically (`issue:<provider>_create`) instead of being fixed to GitHub.

### **15. Code Archaeologist (`src/blame_engine.py`)**

* **Git Blame + AI:** Tracks evolution and historical authorship with commit classification (`ORIGIN` vs `REFACTORING`).
* **Blame Metrics:** Events via `log_blame_metric()` with depth and number of commits analyzed.
* **It now also feeds risk:** the score's history reader (`risk_history_reader.py`) uses the same 90-day window and classifies bugs/reverts by pattern, without calling the AI.
* **Feeds Reviewer Suggestions:** a PR's blame analysis classifies files into `files_with_blame`/`files_without_blame`, consumed by section 27's summary.

### **16. MCP Server and Direct CLI Invocation (`src/mcp_server.py`)**

* **18 Annotated MCP Tools:** `get_git_context`, `analyze_diff`, `list_unstaged_files`, `analyze_unstaged_diff`, `get_full_diff`, `generate_commit_message`, `review_code`, `full_review`, `generate_pr_description`, `run_linter`, `analyze_blame`, `generate_issue`, `list_fix_candidates`, `review_remote_pr`, `get_baseline_status`, `get_usage_metrics`, `run_check` and `publish_pull_request`.
* **Opt-In Parameters in the Contract:** `review_code` and `full_review` accept `mentor: bool = False`; `generate_pr_description` accepts `explain: bool = False`. They are **purely per parameter** — they never OR with the `.env` defaults.
* **19 Resources + 7 Templated Prompts:** `skill://list` + `skill://{pr,commit,review,filereview,issue,blame,release,fix}` + `linter://config` + `baseline://summary` + `prompt://list` + 7 prompts.
* **Aligned Skill Registry:** `mcp_server.SKILL_FILES` has **11 entries** (`tests`, `explain` and `mentor` included); `TestSkillRegistryAgreement` passes.
* **Direct CLI Invocation:** `gitpr-mcp --tool <name> [--tool-args '<json>']` invokes any tool without starting the stdio server; `gitpr-mcp --list` prints the full registry as JSON.
* **Real Stdout Isolation:** `_write_real_stdout()` writes to the original `sys.__stdout__`, with a fallback to `buffer` or re-encoding with `errors='replace'` on legacy Windows code pages.
* **Event Loop Offload:** the `_offload` decorator (`anyio.to_thread.run_sync`) on every tool — decorator order matters (`@mcp.tool` **above** `@_offload`).
* **Failures as Structured Error:** tools return failure **as data** instead of raising.
* **`get_usage_metrics` with Charts:** the payload includes `token_trend` (window of up to 14 days) and `latency` (mean/median/P95) — the same numbers as the CLI and the dashboard, now also displayed in ASCII in the TUI.
* **Credential per Repository:** every SCM resolution goes through `src.scm_settings`; without a token, the tool responds with the fixing command (`gitpr --init`) instead of trying to ask — a tool call cannot stop and wait for the user.
* **MCP Metrics:** `run_check` and `publish_pull_request` write local rows (`mcp:check`, `mcp:publish_pr`).
* **The MCP server is never blocked by the gate:** `--mcp`/`gitpr-mcp` has been on the exemption list since the gate's first version.

### **17. Metrics Dashboard TUI (`src/ui/metrics_app.py`) 🆕 Tabs and Charts**

* **Repository Scope:** `📁 Repository: owner/repo` label and strict filtering by project.
* **Asynchronous Scan with Overlay:** background worker thread with `ProgressBar`; the tests await `workers.wait_for_complete()` before asserting.
* **🆕 Two Tabs:** `MetricsApp` was reorganized into `TabbedContent` with **Events** (the existing textual sections) and **Charts & Trends** (the latency and token panels). The `1` and `2` bindings and `action_switch_tab` jump between them.
* **🆕 ASCII Chart Engine (`src/ascii_chart.py`):** pure functions, no Textual and no new dependencies — `render_vertical_histogram` (latency histogram with Y ticks, full/half block bars, count and percentage per bucket), `render_token_timeseries` (daily series with date labels, compact tokens and an optional sparkline) and `render_empty_card` (framed empty state), plus the formatters `_fmt_tokens` (`1.2M`/`45.0k`) and `_duration_ms_label` (`ms`/`s`/`min`).
* **🆕 A Single Read Pass:** `_load_sections` now returns `(rendered_sections, chart_data)`, capturing the raw `latency` and `token_trend` payloads while rendering the textual sections — with no second query to the ledger. `_finish_scan` propagates `chart_data` to `_render_sections`, with a fallback that re-queries the reports when the data is missing.
* **🆕 Instructional Empty State:** with no ledger, the charts tab shows a card pointing to `gitpr metrics migrate` instead of an empty panel.
* **Ledger Reading:** the dashboard queries `telemetry.db` on the worker thread, rendering the sections (cost, quality, modules, providers, cycle) from the same renderers the CLI uses.
* **Migration Assistant (`src/ui/metrics_migration_app.py`):** offers to import the legacy files, import **and** rebuild from the AI cache, or "not now".
* **Untracked Export Artifacts:** `.gitignore` is the single source about `.gitpr/metrics/export/`.
* **Tests:** `tests/test_ascii_chart.py` (**9 scenarios** 🆕) covers formatters, empty states, populated histogram/series, tab composition and tab switching.

### **18. Metrics and Telemetry System (`src/metrics.py`)**

* **Per-Repository Scope:** All events indexed by `repo_name`, resolved by `src/infrastructure/git/identity.py`.
* **Hook, Linter and Blame Events:** `log_hook_event()`, `log_linter_metric()`, `log_blame_metric()`. `gitpr check` writes `command="check"` with `findings_total`/`findings_errors` and the exit code.
* **`issue_autoclose` Event:** one line per closing decision, with `asked`, `accepted` and `skipped_reason` in the `meta`.
* **Token Trend (`TOKEN_TREND_MAX_DAYS = 14`):** a continuous daily series (days without usage enter as zero) with a sparkline and horizontal bars per day; it picks the side (input/output) with more movement.
* **Latency Histogram:** distribution by buckets with **mean, median and P95**; events without a measured duration are left out so they neither pull the statistics toward zero nor inflate the `< 1s` bucket.
* **`duration_ms` is no longer lost:** the ledger uses `meta["duration_ms"]` as a fallback before writing `0`.
* **🆕 Configurable Cost per Model:** `DEFAULT_MODEL_PRICES` (in `src/config.py`) gained the aliases `gemini-pro-latest` (**1.25 / 10.00** per 1M tokens) and `gemini-flash-lite-latest` (**0.10 / 0.40**), which previously fell through as "unpriced". The `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT` overrides still beat the table; **both rates are mandatory**.
* **🆕 Configurable Currency:** `GITPR_METRICS_CURRENCY` (default `USD`) labels the total and, when it is not USD, discards the embedded table instead of mislabeling a dollar price. A total that leaves models out still reads `Total (partial)`.
* **Export and Cleanup:** `gitpr metrics export` (CSV/JSON), `prune`, `purge` with interactive confirmation, `bundle`/`merge` to transport and merge windows.
* **Interactive Commit Also Measures:** the `commit` path keeps recording the command line with `meta` and `modules`.

### **19. Git Hooks Language Synchronization**

* **Independent Versioning:** `__scripts_version__` (**v0.0.4**, unchanged).
* **Templates rewritten in the v0.0.17 window:** `post-checkout`, `post-merge` and `pre-push` call `gitpr --quiet metrics hook-event <name>`, guarded by `command -v gitpr` and `|| true`.
* **Suffix Mapping (`HOOK_SCRIPT_SUFFIXES`):** UI codes (`es_es`, `fr_fr`) translated to the published suffixes (`.es`, `.fr`).
* **Choice vs. State (`SCRIPTS_LANG` / `SCRIPTS_INSTALLED_LANG`):** Allows **detecting a language switch**.
* **`effective_hook_lang()`:** Resolves the effective language; `--lang` is no longer discarded on that path.
* **Merge-Source Skip:** `prepare-commit-msg` skips the `message|merge|squash|commit` sources.
* **`prepare-commit-msg` Remains Non-Interactive:** the hook runs with `--hook`, so it **never** opens `CommitApp` — the TUI only exists in human use of `gitpr -c`, and only when a TTY is present. 🆕 The migration note for this behavior entered `docs/commit-message-ia.*` in the 5 languages.
* **The hooks run with `--quiet` and `--hook`, so they are never blocked by the update gate.**

### **20. External Linter Bridge and Interactive Assistant (`src/linter_wizard.py`, `src/ui/linter_app.py`)**

* **`--linter-setup` Wizard:** Wizard with numbered presets (PHP_CodeSniffer, ESLint, Stylelint) and injection of the `external_linters` block.
* **Remote Presets:** `templates/gitpr.linter-presets.json` with a local → download → stale → embedded fallback chain.
* **Linter Error TUI:** `src/ui/linter_app.py` (Textual) displays critical errors and warnings; in hook/quiet mode it prints and calls `sys.exit(1)`. `ENABLE_COMMAND_PALETTE = False`.
* **Markdown Report:** Consolidated in `.gitpr/reports/linter/` only when there are violations.
* **The SAST Layer is a Sibling, Not a Replacement:** the Semgrep/Gitleaks/Bandit bridges feed the **same** report pipeline — and also `gitpr check`.

### **21. Multi-Forge SCM (`src/infrastructure/scm/`)**

* **Single Abstraction (`ScmProvider` ABC):** `base.py` defines the contract (dataclasses `RepoRef`, `PullRequestDraft` etc. and `ScmProviderError(provider, http_status, message)` — `http_status` 0 = network failure).
* **`IssueInfo` with `is_open`:** the boolean flag removes fragile state-string comparisons from the decision layer.
* **Issue Operations Belong to Every Forge:** GitLab normalizes `opened` → `open` and emits `Closes #N`; Bitbucket normalizes `new`/`open`/`on hold` → `open` and emits `close #N`; Azure DevOps raises `ScmNotSupportedError` (Work Items are not issues).
* **`list_pull_requests(repo, state, since, until)` — paginated and canonical:** all four providers translate the canonical states (`open`/`merged`/`closed`/`all`) to the native vocabulary, paginate on each API's cursor and filter by an inclusive date window.
* **Why the filter is by state date:** three of the four forges return something broader for a merged PR, so each one filters its own response against the date the state implies.
* **`supports_merged_dates`:** class attribute, `False` on Bitbucket Cloud, which publishes `merged_at` **empty**.
* **`web_links.py`:** pure functions (`repo_web_base`, `commit_url`, `pull_request_url`, `user_url`) normalize any remote format.
* **`get_pull_request(repo, pr_id)` — concrete method of the ABC:** the default raises `ScmNotSupportedError` and each forge implements it.
* **Per-Repository Resolution:** the registry does not read `GITPR_SCM_PROVIDER`; `resolve_scm_provider(config)` receives the provider already resolved by `src/scm_settings.py` (section 46).
* **GitHub:** `GET /repos/{o}/{r}/issues/{n}` (marking `pull_request` and falling back to the requested number when absent), `GET /repos/{o}/{r}` for `default_branch` (empty string when absent) and the `close #N` keyword.
* **Synthesized GitLab Headers:** `changes[].diff` is a loose hunk; `old_path`/`new_path` build the `diff --git a/… / --- / +++` headers, honoring `/dev/null`. And `overflow: true` **raises** instead of reviewing half of a truncated diff.
* **`request_pull_request_reviewers` returns `list[str]`:** Returns the logins **actually attached**, read from the body of the `201`. The contract break is documented in [ADR-0009](docs/adr/0009-reviewer-attach-returns-read-back.md) and in `docs/suggested-reviewers.*`/`docs/scm-multiforge.*`.
* **Read-Only GitHub Helpers:** `get_commit_author_login` and `get_user_login`.
* **Fail-Fast per Forge:** Azure requires `GITPR_SCM_ORGANIZATION`/`GITPR_SCM_PROJECT`; Bitbucket requires `GITPR_SCM_USERNAME`; `create_issue` on Azure raises `ScmNotSupportedError`.
* **Release Publication:** `provider.create_release()` used by `gitpr release --publish`.
* **Shared PR Publication:** `src/application/use_cases/publish_pull_request.py`, used by `--no-edit` and by the MCP tool. Validates title/body, **requires `provider.token` only when `check_remote_state=True`**, optionally checks for an existing PR and an unpushed branch, masks error messages and returns `PublishPrOutcome`.
* **Artifacts:** Glossary + ADR-001/ADR-005/ADR-016; `docs/scm-multiforge.*.md` family in 5 languages; **10 test files, 370 scenarios**.

### **22. `gitpr release` Subcommand — Changelog / Release Notes (`src/release_engine.py`)**

* **Flow:** `git log` between `--since` and `HEAD` → classification by Conventional Commits → suggested semantic bump → changelog assembly → optional AI executive summary → *prepend* to `CHANGELOG.md`.
* **The Range No Longer Comes from `git describe`:** the origin of the range is the **previous section of the changelog itself**, via two anchors: the newest hash of that section and, as a fallback, the version tag of the section.
* **`_drop_released()` — the second line of defense:** any commit whose short hash appears in **another** version section is discarded, with a count.
* **Every bullet became a link:** `- {subject} ([{short_hash}]({commit_url})) — {scope} · [#{n}]({pr_url}) · {YYYY-MM-DD}`.
* **The merged-PR-by-merge-commit failure:** a second pass (`git log --merges`) builds `hash → PR` by ancestry.
* **Contributors resolved by the same ladder as reviewers:** `get_commit_author_login` first, then `email_to_handle`, cached in `~/.gitpr/cache/contributors.json`.
* **The Section Boundary Was a Latent Bug:** `upsert_changelog(force=True)` replaces from the `## [x.y.z]` line up to the next **level-2** heading.
* **Front Matter Declares the Skill's Language:** `src/skill_frontmatter.py` (`split_front_matter()`) reads an initial YAML block with `language: <code>` (allowlist `en_us`, `pt_br`, `pt_pt`, `es_es`, `fr_fr`), **removes the block** before the body becomes a system instruction and swaps the i18n language for the duration of the generation — headings, labels **and** the language requested from the AI, even with the summary disabled.
* **Classifier (`src/commit_classifier.py`)** and **Builder with Translatable Sections (`src/changelog_builder.py`)**.
* **Semantic Bump (`src/version_bump.py`)** and **Publication** (`--publish`, `--draft`, `--format markdown|json`, `--force`). **6 options on the subcommand.**
* **🆕 Release status:** the `## [1.7.0] - 2026-11-10` section is committed (`6d1f1dc`) and the bump at `2354a30` raised `__version__` to **1.7.0**; PyPI serves 1.7.0. The `v1.7.0` tag still points to the pre-bump commit.
* **The subcommand goes through the update gate** when run in an interactive terminal.
* **Artifacts:** `docs/release-notes.*.md` in 5 languages, ADR and `tests/test_web_links.py`.

### **23. `gitpr config` Subcommand — Configuration TUI**

* **Master-Detail Screen (`src/ui/config_app.py`):** Categories on the left, fields on the right, edited inline. Header with search (`/`) and a pending counter; footer `F1 Help · F2 Save · ^R Restore · / Search · Esc`. `ENABLE_COMMAND_PALETTE = False`.
* **Declarative Schema (`src/config_schema.py`) — the single source of truth:** **18 categories** and 🆕 **109 `ConfigField`** (was 100), of which **7 advanced** and 🆕 **56 `seeded`** (was 55).
* **Derived `DEFAULT_CONFIG`:** the dictionary is built from `seeded=True`, so a new key in the schema is either born seeded or deliberately not seeded.
* **`check` Category:** the nine `GITPR_CHECK_*` (`BASE`, `FORMAT`, `OUTPUT`, `FAIL_ON`, `NO_IA`, `IA_ONLY_HIGH_RISK`, `BASELINE_PATH`, `POLICY`, `TIMEOUT_SECONDS`) now appear here, with the same defaults as the pipeline.
* **`smart_excludes` Category:** replaces the old `Diff Filters` label; `SMART_EXCLUDES_VERSION` changed group and the category has its own panel (see below).
* **Smart Excludes Panel:** list of sources (project, global core, global docs), editing `TextArea`, project-file creation button, status/note and dirty tracking. `F2` saves the project file through the same save plan.
* **`project` Category and the `store` Attribute:** fields declare where they live (`env` vs `project`). The three project fields are the file path (read-only), the target branch (editable) and the effective base with origin (read-only). `KIND_BRANCH` renders the branch choice.
* **`scm` Category per Forge:** gained the editable `PROJECT_SCM_PROVIDER` line, the read-only `PROJECT_EFFECTIVE_PROVIDER` line (forge in use and origin) and a `gitlab` group; tokens and base URLs of **all** forges are always listed, and the token test uses the slot's own host.
* **`issue` Category:** `GITPR_ISSUE_AUTOCLOSE` and `GITPR_ISSUE_AUTOCLOSE_TIMEOUT` appear here.
* **🆕 `KIND_FLOAT` Type:** the schema's first float field is `GITPR_METRICS_CURRENCY`, alongside the eight per-model price fields. Validation accepts empty and refuses a negative or non-numeric value with the message "Use a number greater than or equal to zero.".
* **🆕 `ai` Category with Prices:** the `*_INPUT`/`*_OUTPUT` fields are visible when the corresponding `DEFAULT_AI_PROVIDER` is selected; an empty value preserves the previous behavior.
* **Write by Store:** `ConfigApp` dispatches by `store`: project values **never** go through `save_config_values()`, are written first, support removal on emptying, refuse the current branch and never overwrite unreadable YAML. The effective base line is updated in place after saving.
* **`policy` and `baseline` Categories:** the pack fields and the four baseline ones.
* **Aligned Skills Section:** `SKILL_LABELS` has **11 entries** (`tests`, `explain`, `mentor`).
* **Context Filtering (`show_if`):** `GEMINI_*`/`DEEPSEEK_*`/`OLLAMA_*` according to `DEFAULT_AI_PROVIDER`; `GITPR_SCM_USERNAME` under `[Bitbucket]`; `GITPR_SCM_ORGANIZATION`/`PROJECT` under `[Azure DevOps]`.
* **Global Search (`/`):** matches key or label across all categories and **ignores the visibility filter**.
* **Two-Layer Validation:** **Offline** blocks `F2` with an inline error; **online** (only credentials changed in the session) runs in a worker with a 10s timeout and only blocks on `401`/`403`.
* **Restore (`Ctrl+R`):** Removes the line from `.env` instead of rewriting the default; `Esc` with pending changes asks for confirmation.
* **Project-Scoped Sections:** Edits `.gitpr/skill/*.md`, `.gitpr/conf/gitpr.config.yml` and the project's smart-excludes, writing atomically and preserving CRLF/LF.
* **Forced Downloads:** Buttons that force re-download of smart-excludes, translations, presets and thinking words via `force=`.
* **Lightweight Links Module (`src/doc_links.py`):** `doc_url()` moved out of `core.py` so the UI can get the link without importing AI SDKs. `DOC_LANG_PARAMS` maps `es_es`→`es` and `fr_fr`→`fr`, and `CURRENT_LANG` is read **at call time**, so `set_lang()` propagates to the links.
* **Tests:** 5 files — `test_config_app.py` (94), `test_config_schema.py` (**55** 🆕, +2), `test_config_validation.py` (40), `test_config_store.py` (22), `test_config_cli.py` (9) and `test_config_issue_autoclose.py` (10).

### **24. General Usage Log (`src/usage_log.py`)**

* **One Line per Command:** Writes `~/.gitpr/logs/<uuid5>.log`, **one file per day**, with command, arguments, repository, user and timestamp.
* **Date-Derived Name:** `uuid5(NAMESPACE_DNS, f"gitpr.usage.{YYYY-MM-DD}")`.
* **Synchronous Write (explicit decision):** Unlike `log_local_metric`, which used a daemon thread and lost the record if the process exited first.
* **Never Prints:** The MCP server reserves stdout for JSON-RPC; the module also never raises an exception.
* **A Single Git Spawn:** `git config --get-regexp '^(remote\.origin\.url|user\.name|user\.email)$'` — ~40 ms instead of ~150 ms on Windows, on *every* execution.
* **Control:** `GITPR_SHOW_LOGS` (default `"true"`); disabled in `tests/conftest.py`.
* **Artifacts:** `docs/usage-log.*.md` in 5 languages; `tests/test_usage_log.py` (27 scenarios).

### **25. `gitpr fix` Subcommand — Review Findings as Reviewable Patches (`src/fix/`)**

* **The Real Pipeline:** last review from the cache (`resolve_last_review`) → **one** AI call → unified diff extraction and validation → `git apply --check` → deterministic classification → dry run or write → history.
* **An 8-file bundle:** `patch_provenance.py`, `patch_extractor.py` (shared with the chat), `patch_safety_classifier.py` (pure, no I/O), `patch_applier.py` (shim), `fix_history.py`, `apply_fix.py`, `rollback_fix.py`, `__init__.py`.
* **Deterministic Classification:** Refuses a patch that crosses more than one file or hunk, touches a sensitive path, blows the line budget, **deletes a line that looks like a call**, comes marked as low confidence or fails `git apply --check`. `--force` **never** bypasses applicability, only the classification.
* **Opt-In Writing:** Dry run is the default; any mutation requires `--apply` (or a typed phrase with `--force`).
* **Tests:** 10 files in `tests/fix/` (**204 scenarios**) with real git repository fixtures.

### **26. `gitpr review-pr` Subcommand — Remote PR Review (`src/review/`)**

* **What it is:** Orchestration, **not** a second review engine — the existing engine, the linter and the renderer fed with a diff that came from elsewhere.
* **A 5-file bundle:** `diff_source.py`, `diff_normalizer.py`, `render.py`, `remote_pr.py`, `__init__.py`.
* **Read-Only by Default:** `--post-comment` is the **only** path that writes to the forge. The `review_remote_pr` MCP tool does not even receive the argument.
* **The baseline is resolved before the first network call**, and the gate warnings are merged into the report/comment.
* **`render.py` and risk:** `compose_review_content` / `render_review_result` **place** the `⚡ Risk Assessment` section above the linter alerts when `GITPR_RISK_INCLUDE_IN_REVIEW` is on.
* **Complete documentation in the 5 languages:** `docs/review-pr.md`, `.pt_br.md`, `.pt_pt.md`, `.es_es.md` and `.fr_fr.md`.
* **Binary Hunk in the Remote Diff:** `filter_excluded_sections` always applies `collapse_binary_hunks()`, including on early exits without patterns/sections.
* **Tests:** 4 files in `tests/review/` (**97 scenarios**).

### **27. Reviewer Identity Resolution (`src/reviewer_resolution.py`)**

* **The Bug (two chained silent failures):** (1) **Empty prefill** — `handles` only with `email_to_handle()`; (2) **Unverified attach** — values passed *verbatim*, GitHub answered **201 without attaching anyone** and `_request()` returned without raising.
* **Module:** Pure, with no I/O of its own, **that never raises**. `resolve_candidates()` (before the TUI) and `resolve_typed_reviewers()` (in the attach); `match_candidate()` matches exactly and normalized by login, name or email.
* **Resolution Ladder:** known handle (no request) → exact match with a suggested person → lookup by email → validation of the login on the forge.
* **What does not resolve is never sent:** It leaves in `ResolutionOutcome.dropped` as `(value, reason_i18n)` and is discarded with a visible warning.
* **The Same Ladder Serves the Release:** `release_engine` reuses `get_commit_author_login` + `email_to_handle`.
* **Legacy Companion:** if a third-party provider returns `None` (old contract), the log says `(no read-back)` and the flow continues — a missing read-back is **never** treated as a failure.
* **Blame Summary:** `ReviewerSuggestionResult` has `files_with_blame`/`files_without_blame`; the engine separates the files and the CLI prints **one** consolidated line (`ℹ️ Blame analysis: X file(s) with blame, Y without blame`) — only when there are files without blame.
* **Tests:** `tests/test_reviewer_resolution.py` (18) + `test_github_provider.py` (86), `test_pr_publish_app.py` (59), `test_suggest_reviewers.py` (19), `test_reviewer_suggestion.py` (18) and `test_main_suggest_reviewers.py` (8).

### **28. `gitpr demo` Subcommand — Guided Tour over Recorded Examples (`src/demo/`)**

* **What it is:** An interactive tour that shows GitPR's three main outputs — commit message, review and PR description — **without an API key, without a git repository and without a network**.
* **Real Pipeline, Fake Response:** `FakeAIProvider` mirrors `src.ai_providers.call_ai_model` **argument by argument**; `demo_pipeline()` patches `src.core` and `src.metrics` so the production pipeline runs **untouched**.
* **Isolation Is a Requirement, Not a Detail:** The tour **cannot** touch the user's `~/.gitpr` or the network — guaranteed by `tests/demo/conftest.py`.
* **Bundle:** `demo_runner.py`, `fake_ai_provider.py`, `scenarios/` (two examples localized in the 5 languages). The scenarios are **Python modules, not JSON**, so they travel inside the wheel.
* **TUI:** `src/ui/demo/` with a help modal; the diff is fenced as ```` ```diff ````.
* **CLI:** `gitpr demo` with `--scenario`, `--lang` and `--no-tui`.
* **Tests:** 6 files in `tests/demo/` (**155 scenarios**).
* **Artifacts:** `docs/demo.*.md` in 5 languages, `README.*` (5) and the tour's example metrics.

### **29. GitPR Badge and the `gitpr badge` Command (`src/branding/`)**

* **What it is:** A public badge that certifies that a pull request went through a local quality check, and a command that prints the adoption snippet for the project README.
* **Static URL on Purpose:** It is a shields.io Markdown that GitPR **never** fetches — publishing a pull request cannot start depending on a third party being up.
* **Honest Measurement (`badge_data.py`):** `collect_linter_counts()` returns `None` when there are no linter rules configured, because an empty list means "nothing was checked", not "nothing was found".
* **The Badge Never Blocks a Publish:** Every failure path is captured and degrades to "no badge".
* **Single Injection Point:** `attach_pr_badge()` in `src/main.py`. Idempotent append by marker.
* **`gitpr badge` Command:** `--readme` and `--style`. **Only prints** — the README is never modified.
* **Configuration:** `GITPR_BADGE` (default `true`) — **read-only** opt-out.
* **Tests:** 7 files in `tests/badge/` (**83 scenarios**).
* **Artifacts:** `docs/badge.*.md` in 5 languages and the dedicated survey.

### **30. `gitpr split` Subcommand — Atomic Commits by Hunk (`src/split/`)**

* **What it is:** A working tree with several concerns stops becoming a commit blob. `split` reads the uncommitted diff, asks the AI to partition the hunks by **logical intent** and proposes one atomic commit per concern. The tree is never rewritten.
* **A 6-file bundle:** `split_plan.py`, `hunk_parser.py` (pure), `hunk_grouper.py`, `generate_split_plan.py` (read-only), `apply_split_plan.py` (**the only one that mutates**) and `__init__.py`.
* **The Intact-Tree Guarantee Is Structural:** `selective_stager.py` pre-validates **every** group against a temporary index at HEAD and uses a throwaway `GIT_INDEX_FILE`.
* **Destructive to the Index, Not to the Tree:** `--apply` resets the index to HEAD before staging.
* **Configuration:** `GITPR_SPLIT_MAX_GROUPS` (5), `GITPR_SPLIT_MAX_HUNKS` (50) and `GITPR_SPLIT_REQUIRE_CONFIRMATION` (`true`). Zero or negative values are **ignored in favor of the default**.
* **Relationship with the Commit TUI:** `split` and `-c` attack the same problem at different heights — `split` proposes **several** commits from the tree; `CommitApp` organizes **one** commit from the index.
* **Tests:** 6 files in `tests/split/` (**101 scenarios**).
* **Artifacts:** `docs/split-command.md` in 5 languages, ADR-006, `glossary-gitpr-split.md`, spec/plan and survey.

### **31. Embedded Secrets Scan (`src/security_ruleset.py`)**

* **What it is:** Seven rules that run on **every** linter invocation, merged at the end of `load_linter_rules()` after the project rules and the plugins.
* **The Seven Rules:** five blocking `error` — AWS access key ID, GitHub token, Slack token, Google API key and private key block — and two `warning`: database connection URL with credentials and generic credential assignment, the latter behind a placeholder filter (`changeme`, `xxxxxx`, `example`, `dummy`, `sample`, `your_password_here`, `sua_senha`).
* **Coverage:** assignment **without quotes**, **`ASIA…`**, **`github_pat_…`** and **`xoxc-`/`xoxd-`** prefixes, **left boundary** on the key name (`bypassword` does not match) and suppression of runtime false positives (`os.getenv(...)`, `process.env.X`).
* **Why in the Package and Not in the Template:** A secrets gate needs to behave **the same** on every machine and in every CI.
* **Configuration:** `GITPR_LINTER_SECURITY` (default `true`; fail-open opt-out) and `GITPR_LINTER_SECURITY_DISABLED_RULES` (`;`-separated).
* **Tests:** `tests/test_security_ruleset.py` (**77 scenarios**) — regex matrix, placeholders, level, wildcard, merge and translations; the "the alert never echoes the value" invariant still holds.
* **Artifacts:** ADR-007, [ADR-0008](docs/adr/0008-secret-ruleset-coverage-extension.md), `glossary-gitpr-secret-scanning.md`, spec/plan/survey and task reports.

### **32. SAST Bridges — Semgrep, Gitleaks and Bandit (`src/infrastructure/linter/external/`)**

* **What it is:** An **opt-in** layer that plugs third-party security scanners into the existing linter pipeline.
* **Bounded Scope:** They run **only** when enabled and **only** over the files touched by the diff.
* **Common Contract:** `ExternalLinterBridge` (ABC) with hardened `subprocess` execution (`shell=False`, strict timeout, `stdin=DEVNULL`, UTF-8 with `errors='replace'`).
* **Deduplication Is the Point:** `deduplicate_secret_findings()` merges Gitleaks and regex ruleset findings in the same file and line into a **single** confirmed `[Gitleaks + Regex]` entry.
* **Masked Secrets:** `mask_secret_value()` guarantees the value never reaches the finding, the log or the telemetry.
* **Three-Layer Resolution:** `load_sast_config()` reads defaults → `.gitpr.linter.yml` (`sast` block, falling back to `linter.external`) → `GITPR_SAST_*` variables. **All default `false`**.
* **Graceful Degradation:** A tool enabled but missing from `PATH` emits a warning and execution continues.
* **Tests:** 4 files in `tests/infrastructure/linter/external/` (**14 scenarios**) + `tests/domain/linter/test_sast_finding_mapper.py` (2) + `tests/domain/linter/test_structured_findings.py` (44).

### **33. `gitpr tests generate` Subcommand — AI Suite Generation (`src/domain/tests_generation/`, `src/application/`)**

* **What it is:** Generates complete, runnable test files from the current diff, a specific file or a review finding, **respecting the repository's convention** (Pest, PHPUnit, Jest, Vitest, Pytest).
* **Domain Layer:** `TestFramework` (enum) and the `TestGenerationTarget`, `TestScaffold`, `GeneratedTest` dataclasses; `detect_test_framework()` detects from the configuration files, manifests and test directory contents; `build_test_scaffold()` computes the conventional destination path per framework.
* **Application Layer (`generate_test_file.py`):** Orchestrates detection, scaffold resolution, prompt, AI, JSON parsing and optional writing. `validate_test_syntax()` runs the local toolchain (`php -l`, `node --check`, `python -m py_compile`) — validation failure is a **warning**, not an error.
* **Presentation:** `tests` group with `generate` (`--file`, `--finding`, `--framework`, `--apply`, `--provider`); dry run is the default and overwriting an existing test asks for confirmation with **No** pre-selected.
* **Documented in 5 languages:** `docs/tests-generate.*.md` (5 files), with a link in `gitpr tests generate` (epilog) and in the README.
* **Skill:** `.gitpr.tests.md` in `SKILL_LABELS`, `mcp_server.SKILL_FILES` and `SKILL_FILES_BY_TYPE`.

### **34. `gitpr explain` Subcommand and the `--explain` Flag — Reviewer Guide (`src/domain/pr/`)**

* **What it is:** A guide centered on whoever will **review** — what changes, why it changes, where to focus and what the regression risk is.
* **Domain Layer (`explain_section_builder.py`):** `PrExplanation` / `ReviewerFocusPoint`, the renderer `build_explain_markdown()` and `parse_explain_payload()`, which tolerates non-JSON output and **detects placeholders** `[FILL]`/`[TODO]`.
* **`parse_explain_payload()` Accepts `dict | str | None`:** the provider delivers the JSON **already parsed**; the function accepts all three forms and normalizes `None` fields.
* **Missing-Key Branch:** `generate_pr_explanation` prints the standard missing-key error and exits with **1**.
* **Application Layer (`generate_pr_explanation.py`):** Provider resolution, key validation, skill context loading (`explain`), prompt, invocation and parsing.
* **Two Entry Doors:** `gitpr explain` (`--provider`, missing-diff detection before any AI) and the `--explain` flag on the root CLI. The extension exposes the same path in `GitPR: Generate PR Draft (with Reviewer Guide)`.
* **`-h --explain` Fixed:** the option used dest `explain_flag`, but the `HELP_MAP` looked for `explain` — the contextual help renders again.
* **Documented in 5 languages:** `docs/explain-pr.*.md` (5 files), with a link in `HELP_MAP["explain"]`, in the command epilog and in the README.
* **Configuration:** `GITPR_EXPLAIN_BY_DEFAULT` (default `false`).

### **35. Deterministic Suite and CI (`.github/workflows/tests.yml`, `release.yml`)**

* **What it is:** The workflow that runs the suite on **Python 3.10** (the floor declared in `pyproject.toml`) and **3.13**, with `fail-fast: false`.
* **Hermetic `tests/conftest.py`:** Pins `GITPR_LANG=en_us`, `GITPR_LINTER_SECURITY=false` and `LANG_VERSION` to the code version, plus `GITPR_SHOW_LOGS=false`, `GITPR_SKIP_UPDATE_CHECK=true` and `GITPR_SKIP_PROJECT_CONFIG_SEED=true`.
* **Autouse Fixtures Against Profile Leakage:** `english_interface` and `no_hook_script_sync` prevent the developer profile's language, linter flags and hook auto-sync from leaking between CLI tests.
* **Order Matters:** `src.updater` is imported **before** the language variables are defined.
* **Worker-Test Stability:** `test_config_app.py` and `test_metrics.py` wait for `workers.wait_for_complete()` before asserting.
* **Workflow Dependency:** The clean runner has no `~/.gitpr`, and `test_demo_isolation.py` asserts that the profile exists — the job explicitly creates the directory and the empty `.env`.
* **🆕 In this window the suite gained 14 scenarios and 1 module:** `test_ascii_chart.py` (9), `test_config_schema.py` (53 → **55**), `test_metrics.py` (174 → **175**), `test_full_diff_encoding.py` (8 → **9**) and `unit/test_check_ci_docs_parity.py` (2 → **3**). **Total: 3436 scenarios in 160 files.**
* **🆕 `action.yml` Parity Test:** `tests/unit/test_check_ci_docs_parity.py` now also requires that ADR-0011 exists and references `action.yml`, `gitpr check`, `--linter`, `--fullreview`, `SARIF` and `docs/check-ci.md`.
* **⚠️ 2 known failures:** `tests/unit/test_version_consistency.py::test_claude_md_version_matches_ssot` and `::test_gemini_md_version_matches_ssot` fail because `CLAUDE.md`/`GEMINI.md` still say 1.6.0 while `src/updater.__version__` is already 1.7.0.
* **Extension workflow:** `.github/workflows/extension.yml` runs `npm install`, compiles, tests and packages the VSIX. 🆕 The `github-release` job attaches the `.vsix` to the GitHub Releases of `ext-v*` tags; the `publish-marketplaces` job evaluates each PAT independently and is non-blocking.
* **Release Workflow:** `release.yml` runs the tests, checks tag ↔ `src.updater.__version__` consistency, builds sdist/wheel, validates with `twine check --strict`, publishes via Trusted Publishing (OIDC) and creates the GitHub Release. The version SSOT also has its own test (`tests/unit/test_version_consistency.py`).

### **36. SQLite Telemetry Ledger (`src/ledger.py`, `src/infrastructure/git/identity.py`)**

* **The Problem Was Not the Format, It Was the Join:** events were written as JSON files per command, from a **daemon thread**, and later reconnected to the AI cache by a heuristic match of `(repo, branch, action, minute)`.
* **The Write Is Synchronous, and That Is the Decision:** `log_command_metric()` resolves the git context **once per process** and writes the row right away.
* **Versioned Schema:** `SCHEMA_VERSION` + `PRAGMA user_version`, UUID keys, WAL + `synchronous=FULL`, and `LedgerVersionError` to refuse a newer bundle **before** `ATTACH`.
* **"Never Born Empty" Invariant:** `ensure_ledger()` only creates the database **together** with the absorption of the legacy JSON.
* **One Window, One Meaning:** `--since`/`--until`/`--days` narrow **every** reading surface; the cycle section assumes the last 30 days and **names the window**.
* **Each Metric Is Computed Once and Rendered Once:** the `(style, text)` pairs make the CLI and the Textual dashboard tell the **same** story.
* **Cost with Embedded Table and Override:** list prices of the four models, overridden by `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT`. **Both rates are mandatory**. 🆕 The Gemini aliases also have a price and `GITPR_METRICS_CURRENCY` controls the label.
* **Normalized `modules`:** paths become two segments (`src/fix`), `(root)` and `(no module)`.
* **Bundle Transport:** `bundle` writes the window as a standalone `.db`; `merge` attaches it and inserts by UUID.
* **`cache_backfill` Stays Out:** rows rebuilt from the cache are excluded from the execution aggregates.
* **`issue_autoclose` Enters the Ledger:** one row per decision, with `asked`, `accepted` and `skipped_reason`.
* **`duration_ms` with `meta` Fallback:** events that only carried the duration in `meta["duration_ms"]` are no longer recorded as `0`.
* **Git Identity Without Forge (`identity.py`):** repo label, branch and author in a **single** subprocess call.
* **The Cycle Section Is the Only One the Ledger Does Not Answer:** lists merged PRs via `ScmProvider.list_pull_requests`; every failure degrades to a line — `Not read: <reason>`.
* **Complete Presentation:** `gitpr metrics` group with 8 subcommands, a dashboard reading the ledger on a worker thread and the three-option migration assistant.
* **Tests:** `tests/test_metrics.py` (**175 scenarios** 🆕).

### **37. `gitpr mentor` Subcommand and the `--mentor` Flag — Mentor Mode (`src/domain/mentor/`)**

* **What it is:** A review says *what* is wrong and almost never *why that matters*. Mentor Mode enriches findings with **what is happening**, **why it matters**, an everyday **analogy** and the **concepts to study**.
* **Pure Domain Layer (`mentor_explanation_builder.py`):** no I/O, converts the AI payload into a validated `MentorExplanation` and enforces **epistemic honesty**: without enough evidence, the explanation **says so**; hallucinated URLs are **removed**; unknown IDs are **rejected**.
* **Application Layer (`generate_mentor_explanation.py`):** collects candidates, **batches of at most 10 findings**, sorted by severity, reusing the MD5 cache by prompt hash.
* **Two Entry Doors:** `gitpr mentor [--finding <id>] [--provider <name>]` and the `--mentor` flag, which attaches the section to the normal review.
* **Strict Opt-In:** `GITPR_REVIEW_MENTOR_MODE` (default `false`) and `GITPR_MENTOR_INCLUDE_ANALOGY` (default `true`), editable in the configuration TUI.
* **Skill and Registry:** `.gitpr.mentor.md` registered in `SKILL_FILES_BY_TYPE`, in `mcp_server.SKILL_FILES` and in `SKILL_LABELS`, with templates in **5 languages**.
* **Tests:** `tests/domain/mentor/test_mentor_explanation_builder.py` (8), `tests/application/use_cases/test_generate_mentor_explanation.py` (5) and `tests/test_mentor_cli.py` (4) — **all green**.
* **Artifacts:** `docs/mentor-mode.*.md` in **5 languages**, ADR-009, `glossary-gitpr-mentor.md`, spec and task reports.

### **38. `gitpr risk` Subcommand — Deterministic Risk Score (`src/domain/risk/`)**

* **What it is:** A **0–100** score with a `LOW`/`MEDIUM`/`HIGH`/`CRITICAL` level, computed **without AI** from local signals.
* **Pure Domain Layer (`risk_types.py`, `risk_rules.py`, `risk_calculator.py`, `risk_explanation.py`):** enums and dataclasses; weights, thresholds, critical path patterns, glob matching and test/non-executable detection loaded from YAML; per-file calculation with a **0–100 clamp** and 50/30/20 aggregation.
* **The Formula Is Declared, Not Learned:** **50%** the highest-risk file, **30%** the line-weighted average and **20%** critical evidence.
* **Infrastructure Adapters:** `risk_history_reader.py` reads `git log` and extracts commits, bug fixes and reverts in a **90-day** window; `test_matcher.py` matches test file ↔ production by convention.
* **Use Case (`calculate_risk.py`):** correlates diff sections, findings, test matching and history into `FileRisk`/`PullRequestRisk`, **degrading with a warning** when a signal is unavailable.
* **Tolerant Path:** the `check` aggregation accepts `path` **or** `file_path` — the risk report does not degrade silently.
* **Three Consumptions:** the `gitpr risk` command, the `⚡ Risk Assessment` section attached to reviews and to the remote PR review, and the **AI prompt** as **read-only** context. `gitpr check` uses the same `execute_calculate_risk`.
* **Opt-Out:** `GITPR_RISK_INCLUDE_IN_REVIEW` (default `true`).
* **Per-Project Configuration:** `templates/gitpr.risk.yml` (or `.gitpr/skill/gitpr.risk.yml`) overrides weights, thresholds and critical paths.
* **Tests:** `tests/domain/risk/` (2 files, 10), `tests/application/use_cases/test_calculate_risk.py` (5), `tests/infrastructure/git/test_risk_history_reader.py` (4), `tests/infrastructure/git/test_test_matcher.py` (5) and `tests/test_risk_cli.py` (4).
* **Artifacts:** `docs/risk-scoring.*.md` in **5 languages**, ADR-010, `glossary-gitpr-risk-scoring.md` and task reports.

### **39. `gitpr policy` Subcommand — Versioned Policy Packs (`src/domain/policy/`, `src/infrastructure/policy/`)**

* **What it is:** A **versioned YAML pack** that carries, in a single artifact, a repository's quality decisions: skills, linter rules, severities, critical paths, risk weights and PR/commit conventions.
* **Domain Layer (`policy_types.py`, `policy_manifest.py`, `policy_checksum.py`, `policy_compatibility.py`, `policy_resolver.py`):** manifest parsing with a **closed schema**, compatibility and credential verification, **SHA-256 checksum** and a graph resolver with precedence and composition.
* **Infrastructure Layer:** `local_policy_repository.py` resolves the pack from **three origins** (`.gitpr/policies`, `~/.gitpr/policies` and the embedded package) and `policy_pack_loader.py` loads it.
* **Use Cases:** `activate_policy_pack`, `install_policy_pack`, `resolve_effective_policy` and `validate_policy_pack`.
* **Four Embedded Packs:** `laravel-quality`, `node-quality`, `php-security` and `vue-quality`, each with `policy.yml` and `linter.yml`, distributed via **`package-data`**.
* **Consumption Integrated at Four Points:** `calculate_risk`, `config.load_linter_rules`, `core.get_skill_context` and the **PR cache key** (`cache_scope`). `gitpr check` resolves the pack **before everything** and exits with code 2 if it is invalid.
* **CLI:** `gitpr policy {list, validate, show, use, off, install, init}`, with confirmation (`--yes`/`--force`), stack detection on `init` and the choice written to `.gitpr/policy.lock.yml`.
* **Configuration:** `GITPR_POLICY_ENABLED` and `GITPR_POLICY_CONTEXT_MAX_CHARACTERS`.
* **Backward Compatibility:** with no active pack, the behavior is **preserved** — covered by an integration test.
* **Dependency:** `packaging>=23.0` (SemVer/PEP 440).
* **Tests:** `tests/domain/policy/` (4 files, 126), `tests/application/use_cases/` (4 policy files, 89), `tests/test_policy_cli.py` (26) and `tests/integration/test_policy_effect_on_review_linter_risk.py` (31).
* **Artifacts:** `docs/policy-packs.*.md` in **5 languages**, ADR-011, `glossary-policy-packs.md`, spec/plan/survey.

### **40. `gitpr baseline` Subcommand — Baseline and Auditable Suppressions (`src/domain/baseline/`)**

* **What it is:** Linters and reviews re-report the same pre-existing findings on every run. The baseline records the **snapshot of the findings the repository already knows** in `.gitpr/baseline.json`, fingerprints each one stably across runs and lets the pipeline classify what is new — **only `new` blocks**.
* **Domain Layer (`baseline_fingerprint.py`, `baseline_types.py`, `baseline_manifest.py`, `baseline_comparator.py`, `suppression_policy.py`):**
  * **Stable fingerprint:** rule identity, normalized path, lines and **sha256 of the line with whitespace collapsed** — the raw line is **never** stored.
  * **Canonical manifest:** canonical JSON with checksum, validation that refuses an unknown key, schema/fingerprint version outside the supported range, a broken suppression/debt invariant, a duplicate fingerprint and an invalid line range.
  * **Comparator:** classification, counts per status and entry resolution — `resolved` and then `back` reads as **new**, the conservative reading.
  * **Suppression Policy:** suppressions, accepted debt, overrides and validators, with layered matching by **specificity and precedence**, plus overdue debt tracking.
* **Five Use Cases:** `baseline_gate`, `baseline_summary`, `compare_against_baseline`, `create_baseline` and `suppress_finding`.
* **Integration with Policy Pack:** the pack can carry a `baseline:` block, validated by the **same** validators.
* **Repository Is the Only One That Writes:** `local_baseline_repository.py` is the **only** reader/writer of `.gitpr/baseline.json` and `.gitpr/baseline.overrides.yml`.
* **Configuration:** `GITPR_BASELINE_ENABLED`, `GITPR_BASELINE_PATH`, `GITPR_BASELINE_REQUIRE_LOCKFILE_CHECKSUM_MATCH` and `GITPR_BASELINE_ALLOW_LOCAL_OVERRIDES`.
* **Opt-In and No-Op:** disabled or with no existing baseline, the feature **reads no file and prints nothing**.
* **⚠️ The Deliberate Exception:** a baseline file that exists but **cannot be applied** makes lint, review, full review, file audit, remote review and risk **abort with exit code 1**. In `gitpr check`, a corrupted baseline exits with **code 2**.
* **The Contract Became a Test:** `tests/integration/test_baseline_exit_code_contract.py` (8 scenarios) locks both codes.
* **Reach:** CLI (6 subcommands, all with `--format json`), MCP (`get_baseline_status` + `baseline://summary`), remote PR review and the lint flow.
* **Refactoring the Feature Required:** `NormalizedFinding` became a canonical type in `src/domain/finding/`.
* **Tests:** `tests/domain/baseline/` (5 files, **126**), `tests/application/use_cases/` (5 files, **141**), `tests/infrastructure/baseline/test_local_baseline_repository.py` (24), `tests/domain/finding/test_finding_types.py` (3), `tests/domain/linter/test_structured_findings.py` (44), `tests/test_baseline_cli.py` (54) and `tests/integration/test_baseline_effect_on_review_linter_risk.py` (34) + `tests/integration/test_baseline_exit_code_contract.py` (8).
* **Artifacts:** `docs/baseline-suppressions.*.md` in **5 languages**, ADR-012, `glossary-baseline.md`, spec/plan/survey and task report.

### **41. `gitpr check` Subcommand — Unified CI Gate (`src/domain/check/`, `src/infrastructure/ci/`)**

* **What it is:** A **single entry point** for CI pipelines that orchestrates static analysis, SAST bridges, secrets scanning, optional AI review, risk score and baseline classification, and emits **one standardized artifact** with a deterministic exit code.
* **The Pipeline Order Is Contract:** (1) policy resolution — invalid exits with `2`; (2) diff extraction with smart excludes; (3) regex linter + secrets + external bridges; (4) preliminary risk score; (5) **optional AI review**; (6) baseline classification; (7) `--fail-on` threshold evaluation; (8) artifact generation with **secret masking**.
* **Domain Layer (`src/domain/check/`):** `check_types.py` (severity normalized into three levels with spec aliases); `check_config.py` (**CLI > env > defaults** precedence); `check_output.py` (format dispatch and atomic write).
* **Infrastructure Layer (`src/infrastructure/ci/`):** `sarif_builder.py` (SARIF 2.1.0), `junit_builder.py` (testsuites with `<failure>` and `<system-out>`) and `masking.py`.
* **Use Case (`run_check.py`):** `resolve_diff_base()` (explicit → `GITHUB_BASE_REF` → `origin/main` → `HEAD`), running the linter and the bridges, risk score, optional review, baseline and the final envelope. Stdout emits **only the artifact**. The risk aggregation accepts `path` **or** `file_path`.
* **Exit Codes:** `0` success; `1` countable finding at the threshold; `2` execution error.
* **CLI:** `gitpr check` with 8 options and 9 `GITPR_CHECK_*` variables, also in the configuration TUI (`check` category).
* **GitHub Action Rewritten:** `action.yml` became a single `gitpr check` step. **Declared break** for workflows that relied on `--linter` + `--fullreview`. 🆕 the `action.yml` header now carries the migration note and the link to `docs/check-ci#7-migration-guide`; the decision is formalized in [ADR-0011](docs/adr/0011-action-unified-check-migration.md).
* **The Migration Note Became a Test:** `tests/unit/test_check_ci_docs_parity.py` requires the 5 language variants of `docs/check-ci.*`, the 7 `## 1.`–`## 7.` sections and the terms `action.yml`, `--linter`, `--fullreview`, `gitpr check` and `SARIF` in the migration section — plus the **Legacy CI action contract** term in `CONTEXT.md`. 🆕 it now also requires ADR-0011 and the references in `action.yml`.
* **Tests:** `test_run_check.py` (8), `test_check_types.py` (8), `test_check_config.py` (3), `test_check_output.py` (3), `test_masking.py` (6), `test_sarif_builder.py` (2), `test_junit_builder.py` (2), `test_check_cli.py` (5), `test_baseline_exit_code_contract.py` (8) and `test_check_ci_docs_parity.py` (**3** 🆕).
* **MCP:** the `run_check` tool returns the JSON envelope of the same pipeline, with `no_ia` default `true`.
* **Artifacts:** `docs/check-ci.*.md` in **5 languages**, ADR-013 + 🆕 ADR-0011, `glossary-check-ci.md`, spec, `plansfacts` and survey.

### **42. Editor Extension — VS Code and Cursor (`extensions/vscode/`)**

* **What it is:** A **thin client** extension that brings GitPR's flows into the editor without reimplementing anything: it starts `gitpr-mcp` over stdio and calls the existing tools. Keys, tokens, prompts and cache stay in `~/.gitpr/`, encrypted with Fernet.
* **Architecture:** `GitPrMcpClient` over `@modelcontextprotocol/sdk` + `SessionManager` with **one session per folder** (multi-root) + connection contract check: the 7 mandatory tools must exist; otherwise `MissingMcpToolsError` instructs `pip install --upgrade gitpr-cli`.
* **8 Commands:** `gitpr.commit`, `gitpr.review`, `gitpr.fullReview`, `gitpr.reviewMentor`, `gitpr.generatePr`, `gitpr.generatePrExplain`, `gitpr.publishPr` and `gitpr.check`.
* **Two-Step Publication:** `generatePr` opens an editable Markdown document; `publishPr` asks for modal confirmation and handles `success`, `needs_push`, `already_exists`, `auth_required` and `error`.
* **Diagnostics in the Problems Panel:** `run_check` is converted into `DiagnosticCollection("gitpr")`.
* **Double-Depth Masking:** `masking.ts` mirrors `src/infrastructure/ci/masking.py`.
* **Extension Localization:** bundles in **en, es, fr and pt-br** (4 languages) — the GitPR UI itself remains at 5.
* **Marketplace Metadata:** `package.json` has `icon` (`images/icon.png`), `homepage` and `bugs.url`; the package carries `LICENSE` (LGPL-2.1), the minimum condition for a credible listing.
* **🆕 Decoupled VSIX Release:** `extension.yml` gained a dedicated `github-release` job that attaches the compiled `.vsix` to the official GitHub Releases of `ext-v*` tags — the editor artifact no longer depends on the Python package release.
* **🆕 Granular Marketplace Gate:** the `publish-marketplaces` job evaluates `VSCE_PAT` and `OVSX_PAT` **independently**, does not block the rest of the workflow and reports each result in `$GITHUB_STEP_SUMMARY`. The domain term changed from *Fail-fast secret check* to **Granular marketplace gate**.
* **🆕 One-Click Installation:** `docs/editor-extension.*` (5 languages), `README.md` and `docs/marketplace-publishing-guide.md` gained VSIX installation instructions via CLI and via GUI, with direct links to the `ext-v*` releases.
* **What the Extension Does Not Do:** it does not commit, push or publish without confirmation. The extension's `gitpr.commit` command calls the `generate_commit_message` MCP tool — it does **not** open the TUI, which is exclusive to the terminal. Automatic issue closing is also **exclusive to the CLI**.
* **Tests:** its own Mocha suite (7 scenarios, including real stdio; the integration test timeout went from 20s to 60s) + `tests/test_mcp_server.py` (117 scenarios).
* **Artifacts:** `docs/editor-extension.*` in **5 languages**, `docs/marketplace-publishing-guide.md`, ADR-014, `glossary-editor-extension.md`, specs, `plansfacts` and reports.

### **43. Interactive Commit TUI (`src/ui/commit_app.py`)**

* **What it is:** `gitpr -c`/`--commit` stopped being a silent pipeline — it validated the tree, asked the AI for the message and committed immediately, without letting you choose files, review the text or refuse a bad suggestion. Now it is a `CommitApp(App)` with **4 steps** that keeps the developer in control until the `git commit`.
* **Bundle:** a single module, `src/ui/commit_app.py` (220 lines). The three modals are **imported** from `src/ui/pr_publish_app.py` (`StageFilesScreen`, `CommitMessageScreen`, `CommitProgressScreen`) along with `_with_suppressed_stdout` — **zero duplicated widget**.
* **The 4 Steps:**
  1. **Stage Selector:** if there are files outside the index, it opens `StageFilesScreen` with checkboxes, "Select All", "Deselect All", "Stage Selected", "Skip Staging" and "Cancel". Index already complete → the step is skipped.
  2. **Generation:** reads `get_git_staged_diff()`, falls back to `get_git_diff()` when nothing is staged, and calls `generate_pr_content("commit", "commit", diff_text, provider)` in a daemon `threading.Thread` while `CommitProgressScreen` animates.
  3. **Review:** `CommitMessageScreen` shows the message, which can be edited before confirming — the **edited** message is the one that goes to git.
  4. **Execution:** a new `CommitProgressScreen` runs `execute_git_commit(append_coauthor_trailer(msg))` and records the SHA in the log.
* **Every Failure Becomes State, Not an Exception:** a broken AI falls back to `__("Code update")`; a commit that raises becomes `failed` with the captured output. Terminal states: `no_changes`, `cancelled`, `committed`, `failed`.
* **Auto-Exit on Success:** `log.mark_finished(False)` removes the button, and `_schedule_auto_exit()` schedules `set_timer(0.6, self.exit)` — the user returns to the prompt without clicking anything. **On error the Close is kept**, because that is where the failure log stays readable.
* **`--hook` Untouched:** the hook path continues without a TUI and merge-aware (`is_merge_in_progress()` → silent `return`). `prepare-commit-msg` never opens the application.
* **TTY Guard:** without `--hook`, routing now consults `_may_ask_the_user()`; with a TTY, it opens `CommitApp`; without a TTY (CI, pipe, cron), it falls back to the silent console flow.
* **Configuration:** `GITPR_SKIP_UNSTAGED_CHECK` (skips the Stage Selector) and `GITPR_AUTO_STAGE` (stages everything and proceeds), both in `DEFAULT_CONFIG` and in the configuration TUI schema.
* **Textual Details:** `ENABLE_COMMAND_PALETTE = False`; `on_button_pressed` handles `#btn_close`; `_on_commit_progress_dismissed` reads `_commit_success` instead of assuming success when the modal closes.
* **Migration Note:** 🆕 `docs/commit-message-ia.*` (5 languages) now explicitly states that, without a TTY, `-c` falls back to the console flow and that `--hook` remains the CI path.
* **Tests:** `tests/test_commit_app.py` (**17 scenarios**) — every state transition and callback, a complete headless flow and `TestCliCommitRouting` (routing with and without TTY).
* **Domain:** `CONTEXT.md` keeps the **Stage selector** and **Commit flow** terms.
* **Artifacts:** `docs/commit-message-ia.*` in the **5 languages** and the Gemini task report.

### **44. Automatic Issue Closing (`src/issue_autoclose/`) — Multi-Forge**

* **What it was:** the issue created with `gitpr -is` was orphaned — the number existed only inside the issue TUI and was lost the instant it closed. Every PR had to be linked by hand, and the merge closed nothing.
* **A 5-file bundle:** `issue_registry.py`, `register_created_issue.py`, `resolve_close_issue.py`, `insert_close_line.py` and `__init__.py`. Each one is a responsibility: persist, register on creation, decide on the next execution and insert into the body.
* **Provider-Neutral Registry (`IssueRegistry`):** the file moved from `.gitpr/conf/github.yml` to **`.gitpr/conf/issue.yml`**, with a `provider` field (default `github`). The legacy `github.yml` is **read** transparently when the new one does not exist and is deleted on the next write/cleanup — migration without user intervention.
* **Self-Ignoring with Precision:** `record()` runs `git check-ignore` before the first write and appends exactly one line to `.gitignore` when the file is not covered — `.gitpr/conf/` is **not** ignored as a whole, because `gitpr.config.yml` is versioned on purpose ([ADR-015](docs/plans/ADR-015-project-local-pr-base.md)).
* **Registration on Creation:** `register_created_issue()` records the number **and provider** of any supported forge and swallows bookkeeping failures — a local write problem never turns a successful creation into an error. The old `not-github` outcome became `no-provider`.
* **Decision (`resolve_close_issue`):** gates by flags and configuration, but **no longer** by forge; each forge without support degrades to `skipped_reason="not-supported"` instead of being ignored. It checks that the issue is still **open** and **is not a PR** (preferring `IssueInfo.is_open`), deletes dead records (404, closed, PR number) and asks **once**, warning when the PR targets a branch other than the default.
* **Idempotent Line (`insert_close_line`):** detects any existing keyword (`close/closes/closed`, `fix/fixes/fixed`, `resolve/resolves/resolved`, with an optional `owner/repo#N` prefix, case-insensitive) and never duplicates the line.
* **CLI:** `--close-issue` and `--no-close-issue` — mutually exclusive, refused before any prompt or write; `-h` wins the conflict.
* **Wiring in the PR Flow:** `_prepare_close_issue` runs **before** the Stage Selector and reuses the resolved SCM context; `_publish_pr_directly` returns whether the forge actually published; `PrPublishApp.body_published` distinguishes create/update from merely opening an existing PR. The registry is only cleared after a real publication (ADR-007).
* **SCM Contract:** `IssueInfo` (state in the forge's vocabulary, `is_open` and `is_pull_request`), `get_issue()`, `get_default_branch()` and `closing_line()` are concrete defaults of the ABC; GitHub, GitLab and Bitbucket implement them (`Closes #N` syntax on GitLab, `close #N` on GitHub/Bitbucket), and Azure DevOps raises `ScmNotSupportedError`.
* **Configuration:** `GITPR_ISSUE_AUTOCLOSE` (default `true`, opt-out) and `GITPR_ISSUE_AUTOCLOSE_TIMEOUT` (default `5`, positive int), exposed in the `issue` category of the TUI.
* **Telemetry:** one `issue_autoclose` row per decision (`asked`, `accepted`, `skipped_reason`).
* **Tests:** 5 files in `tests/issue_autoclose/` (**126 scenarios**) — registry round-trip, schema, corruption, atomic-write failure, `.gitignore`, legacy migration, decision, line idempotency, flags, wiring and `body_published` semantics.
* **Artifacts:** [ADR-010](docs/adr/0010-multiforge-issue-autoclose.md), `docs/specs/issue-autoclose/` (12 files), `docs/issue-autoclose.*` in **5 languages**, `docs/issue-tui-help.*`, `docs/config-tui.*` and `docs/metricas-telemetria.*`, README in the 5 languages and task reports.

### **45. PR Base per Project (`src/project_config.py`, `src/ui/pr_base_app.py`)**

* **What it was:** the PR target branch lived only in the machine's `~/.gitpr/.env` (`PR_DEFAULT_BASE`) or was guessed from `origin/HEAD`. A repository targeting `develop` depended on every developer configuring the same variable, and the error (a PR opened against the wrong branch) only showed up on the forge.
* **What it is:** `.gitpr/conf/gitpr.config.yml` — a file **versioned with the project** that carries the PR base, edited by the configuration TUI and validated before any commit/push. A single resolver serves CLI and MCP, so every entry point agrees on precedence and failure rules.
* **Resolver (`src/project_config.py`):** `resolve_pull_request_base()` — `--base` → project file → `PR_DEFAULT_BASE` → `origin/HEAD` detection → `main`. YAML reading/writing preserves foreign keys; idempotent seeding; branch listing; detection of the current branch; verification against the remote.
* **Second Section (`scm:`):** the file also carries `scm.provider` (read/write), with the two sections independent, and it refuses to overwrite an `scm` block that is not a mapping. That override is what wins over remote detection (section 46).
* **Failure Before the PR Is Born:** `BaseBranchError`/`CurrentBranchError`; the base must exist on `origin/<branch>` and differ from the current branch — without `origin` or with a missing ref, the flow stops with an actionable hint instead of opening a doomed PR.
* **Configuration TUI:** `project` category with a `store` attribute (`env` vs `project`), `KIND_BRANCH` and three fields: file path (read-only), target branch (editable) and effective base with origin (read-only).
* **Repair Modal (`src/ui/pr_base_app.py`, 98 lines):** when the target is the current branch itself and the run is interactive, a focused modal asks for the replacement branch and writes it to the project file.
* **CLI:** `--base` goes through `_resolve_pr_target()`; the file is seeded on every invocation inside a repository (help excepted), announced only on the root's interactive path; `GITPR_SKIP_PROJECT_CONFIG_SEED` disables the seed. `gitpr seed` does the same explicitly and idempotently (section 47).
* **🆕 Formalized Boundary:** [ADR-0019](docs/adr/0019-project-configuration-seed-segregation.md) separates the **runtime auto-seed** (only `gitpr.config.yml`, on first use) from **explicit provisioning** (`gitpr seed`, which covers config, smart excludes and linter). 🆕 `docs/config-tui.md` and `docs/pull-request-publication.md` gained the migration note and the opt-out for CI/monorepo.
* **MCP:** `publish_pull_request` uses the shared resolver, returns an error envelope for `BaseBranchError` and exposes `warnings` in the payload and in the catalog.
* **Declared Side Effect:** running `gitpr` in a repository now creates the file on first execution. An existing file is never overwritten; if `.gitignore` excludes the path, a warning explains that the value will not be versioned.
* **Tests:** `tests/test_project_config.py` (**49**), `tests/test_project_config_cli.py` (7), `tests/test_pr_base_app.py` (12) + extensions in `test_config_app.py` (94), `test_config_schema.py` (**55**), `test_config_store.py` (22), `test_mcp_server.py` (117) and `test_core.py` (49).
* **Artifacts:** [ADR-015](docs/plans/ADR-015-project-local-pr-base.md), 🆕 [ADR-0019](docs/adr/0019-project-configuration-seed-segregation.md), `docs/plans/develop_natan/20261030_project_pr_base_plansfacts.md`, `docs/survey/20261030_project_pr_base_surveyfacts.md`, and `docs/config-tui.*`/`docs/install-wizard.*`/`docs/pr-descricao-padrao.*`/`docs/pull-request-publication.*` in the 5 languages.

### **46. SCM Configuration per Repository and per Forge (`src/scm_settings.py`)**

* **What it was:** the forge (`GITPR_SCM_PROVIDER`), the token (`GITPR_SCM_TOKEN_ENCRYPTED`) and the base URL were **machine-global** settings in `~/.gitpr/.env`. One machine could no longer serve more than one forge at a time, the wrong credential went to the wrong host when a repository's remote changed, and any unrecognized self-hosted host was silently mapped to GitHub.
* **What it is:** the forge and the credential became **properties of the repository** ([ADR-016](docs/plans/ADR-016-per-provider-scm-settings.md)). A single resolver answers "which forge, which token, which base URL apply here?" for all surfaces.
* **Resolution Order (`resolve_provider_choice`):** project override (`scm.provider` in `.gitpr/conf/gitpr.config.yml`) → `detect_provider_from_remote(origin, default=None)` → `github`. An unknown forge value in the project file is **ignored with a warning**, never fatal; `default=None` lets the wizard distinguish "recognized as GitHub" from "not recognized" and **ask** instead of assuming.
* **One Slot per Forge:** `GITHUB_TOKEN_ENCRYPTED`, `GITLAB_TOKEN_ENCRYPTED`, `BITBUCKET_TOKEN_ENCRYPTED`, `AZURE_DEVOPS_TOKEN_ENCRYPTED` (Fernet) and the corresponding `*_BASE_URL`. A token never answers for another forge; the raw `GITPR_SCM_TOKEN` remains the global CI/CD override and is tried first on every provider.
* **Single Idempotent Migration (`migrate_legacy_scm_config`, under a lock):** moves `GITPR_SCM_PROVIDER` + `GITPR_SCM_TOKEN_ENCRYPTED` + `GITPR_SCM_BASE_URL` into the slot of the forge they name, **never overwrites** an already filled slot, removes the legacy lines and keeps working when `.env` is read-only (it degrades to the process environment). A legacy token **without** a registered provider is deliberately **left in place**, not guessed.
* **Wizard (`gitpr --init`):** persists **only on success**, writes to the chosen forge's slot, offers the stored base URL as the prompt default (non-GitHub) and only writes `scm.provider` in the project file when the chosen forge differs from the detected one — the file is committed and shared with the team.
* **Configuration TUI:** editable `PROJECT_SCM_PROVIDER` line and read-only `PROJECT_EFFECTIVE_PROVIDER` line (forge in use + origin), tokens and base URLs of all forges always listed, token test against the slot's own host and a `gitlab` group.
* **Converted Surfaces:** `src/main.py`, `src/metrics.py`, `src/release_engine.py` and `src/mcp_server.py` resolve through `get_scm_settings()` and propagate the resolver's warnings.
* **Non-Interactive Never Asks:** `validate_or_request_scm_token(..., allow_prompt=False)` in hooks, MCP and CI returns "run `gitpr --init`", does not delete the saved credential and still uses a validated token that could not be persisted.
* **Tests:** `tests/scm/test_scm_settings.py` (**24 scenarios**) + extensions in `test_init_wizard.py` (20), `test_factory.py` (14), `test_azure_devops_provider.py` (51), `test_bitbucket_provider.py` (49), `test_gitlab_provider.py` (56) and `tests/test_tui_issue_token.py` (8).
* **Artifacts:** [ADR-016](docs/plans/ADR-016-per-provider-scm-settings.md), `docs/plans/develop_natan/20261107_per_provider_scm_settings_plansfacts.md`, `docs/scm-multiforge.*` (5 languages) and the `docs/plans/glossary-scm-multiforge.md` glossary.

### **47. `gitpr seed` Subcommand — Local Configuration Bootstrap (`src/core.py`, `src/main.py`)**

* **What it was:** the project's local configuration (PR base, smart excludes, linter rules) was born **as a side effect** of the root callback and of the skill download. There was no way to seed explicitly, repeatably and idempotently — and a CI that depended on those files only found out on the second run.
* **What it is:** `gitpr seed` (and the global `-sd/--seed` flag) promote the bootstrap to a first-class operation. `seed_project_configuration()` creates:
  * `.gitpr/conf/gitpr.config.yml` (PR base, via `ensure_project_config`);
  * `.gitpr/conf/gitpr.smart-excludes.json` (project exclusions);
  * `.gitpr/skill/.gitpr.linter.yml` (local linter rules).
* **Never Destroys Without a Request:** an existing file is **never** overwritten; `--force/-f` overwrites on purpose, and `--all/-a` also includes the download of all AI skills. The function returns a structured summary (`config`, `smart_excludes`, `linter`, `skills_downloaded`) and the command records the `seed` metric.
* **🆕 Boundary with the Auto-Seed:** [ADR-0019](docs/adr/0019-project-configuration-seed-segregation.md) formalizes the segregation: the root callback/`--install` keeps seeding **only** `gitpr.config.yml` on first use, while the complete provisioning (smart excludes + linter + skills) is always explicit through `gitpr seed`. The `GITPR_SKIP_PROJECT_CONFIG_SEED` opt-out covers CI and monorepos where the auto-seed must not run.
* **5-Step Installation Wizard:** **1)** Project Local Configuration (new, confirmable), **2)** Skill Templates (with the optional setup of external linter bridges), **3)** Git Hooks, **4)** MCP Configuration, **5)** API Key. Skipping became explicit.
* **Compatibility:** `generate_skill_template()` keeps auto-seeding the smart-excludes, so the old path does not break; `ensure_project_config()` and `download_skill_file()` gained the `force` parameter while keeping the no-clobber default.
* **Help:** `HELP_MAP["seed"]` points to `docs/install-wizard.md` and describes `--force`/`--all`.
* **Tests:** `tests/test_seed_command.py` (**8 scenarios**) — creation, idempotency, forced overwrite, `--all`, CLI and flag; `tests/test_install_wizard.py` now mocks the external linter setup.
* **Artifacts:** `docs/install-wizard.*` (5 languages), 🆕 [ADR-0019](docs/adr/0019-project-configuration-seed-segregation.md), `docs/plans/develop_natan/20261105_seed_local_conf_plansfacts.md`, survey and walkthrough, and the **Project configuration seed** term in `CONTEXT.md`.

---

## **📊 Tests and Quality**

| Test File | Scenarios | Focus |
|-----------|-----------|-------|
| `tests/test_ascii_chart.py` | 9 🆕 | Compact formatting, empty states, histogram, time series, tabs and tab switching |
| `tests/test_blame_engine_ranges.py` | 7 | Blame by line range in a file |
| `tests/test_blame_metrics.py` | 7 | Blame metrics: depth, commits, duration |
| `tests/test_baseline_cli.py` | 54 | `baseline` group: create/show/validate/update/suppress/unsuppress |
| `tests/test_changelog_builder.py` | 22 | Changelog builder: sections, translatable headings, contributors, links and dates |
| `tests/test_chat_backend.py` | 19 | Chat memory, persistence, slash commands |
| `tests/test_check_cli.py` | 5 | `check` CLI: options, formats, exit codes |
| `tests/test_cli_help.py` | 9 | `-h` before the subcommand: help rendered, TUI never opened |
| `tests/test_commit_app.py` | 17 | `CommitApp`: mount, Stage Selector, auto-stage, skip, cancel, confirm, success/failure, auto-exit, headless flow and routing with/without TTY |
| `tests/test_commit_classifier.py` | 30 | Conventional Commits classification + `pr_number_from_merge()` on all 4 forges |
| `tests/test_config_app.py` | 94 | Configuration TUI: mount, categories, dirty tracking, F2 blocked, Ctrl+R, search, secrets, `project` stores, Smart Excludes panel |
| `tests/test_config_cli.py` | 9 | `config` subcommand registration, `-h`, lazy import, clean stdout |
| `tests/test_config_issue_autoclose.py` | 10 | Configuration getters and issue autoclose schema fields |
| `tests/test_config_schema.py` | 55 🆕 | Derived `DEFAULT_CONFIG` coverage, no duplicates, categories/kinds (including `KIND_FLOAT`), `seeded`, skills section, banner and palette |
| `tests/test_config_store.py` | 22 | Round-trip in the temporary `.env`, comments and order preserved, idempotent `remove_config_value()` |
| `tests/test_config_suggest_reviewers.py` | 8 | Suggested reviewers configuration (keys and defaults) |
| `tests/test_config_validation.py` | 40 | Types, enums, templates, `validate_ai_key()` with mocked SDK (401 vs. network vs. ollama) |
| `tests/test_core.py` | 49 | Main flows, git diff, PR generation, timing, staging, co-authorship, hook language, PR base |
| `tests/test_diff_failure_cli.py` | 3 | Exit code 1 on git failure, 0 on empty diff, fail-open on `--hook` |
| `tests/test_diff_parser.py` | 26 | Line/hunk diff parser, `summarize_patch()` and `split_patch_sections()` |
| `tests/test_doc_links.py` | 11 | Site `?lang=` contract, dynamic `doc_url()`, TUI links and 5-language parity |
| `tests/test_explain_command.py` | 4 | `explain` CLI: success, missing key, empty diff, contextual help |
| `tests/test_external_linters.py` | 33 | Checkstyle bridge: XML parser, subprocess, diff crossing, report |
| `tests/test_full_diff_encoding.py` | 9 🆕 | Real repository with a Latin-1 file + binary hunk guard (fast path, collapse, density, **defensive collapse in `generate_pr_content`**) |
| `tests/test_i18n.py` | 22 ✅ | Parity between languages, missing/orphan keys, identity — **all green** |
| `tests/test_install_wizard.py` | 3 | Interactive installation assistant (5 steps) |
| `tests/test_issue_engine.py` | 4 | Structured issue draft |
| `tests/test_linter_metrics.py` | 4 | Linter metrics: errors, warnings, duration |
| `tests/test_linter_presets.py` | 5 | Linter presets: resolution and forced re-download |
| `tests/test_main_suggest_reviewers.py` | 8 | `--no-suggest-reviewers` flag in the CLI and in the contextual help |
| `tests/test_mcp_prompts.py` | 11 | MCP prompt templates and language fallback |
| `tests/test_mcp_server.py` | 117 | MCP tools (18), resources (19), annotations, patching, direct CLI, offload, extension contract, PR base |
| `tests/test_mcp_server_e2e.py` | 6 | Real MCP server via subprocess + JSON-RPC stdio |
| `tests/test_mentor_cli.py` | 4 ✅ | `mentor` CLI and `--mentor` opt-in — patches in `src.main` |
| `tests/test_metrics.py` | 175 🆕 | **Ledger**: synchronous write, export, bundle/merge, migration, renderers, cost (including Gemini aliases), currency, quality, modules, providers, cycle, Token Trend and Latency Histogram |
| `tests/test_net_timeouts.py` | 12 | Network/AI timeouts — aligned with the real 180s default |
| `tests/test_plugins.py` | 17 | Plugin discovery, linter rule merge, MCP prompts |
| `tests/test_policy_cli.py` | 26 | `policy` group: list/show/validate/use/off/install/init, confirmation and lockfile |
| `tests/test_pr_base_app.py` | 12 | PR base switch modal: headless mount, choice and cancellation |
| `tests/test_pr_publish_app.py` | 59 | PR Publisher TUI: screens, flows, suggested reviewers, `NoticeScreen`, merge modal, `body_published` |
| `tests/test_pr_publish_linter_modal.py` | 4 | Linter error modal: abort, no-verify |
| `tests/test_pre_save.py` | 3 | --pre-save flag and JSON payload |
| `tests/test_project_config.py` | 49 | `project_config`: path, YAML, seed, base resolver, `scm` section, validation against the remote |
| `tests/test_project_config_cli.py` | 7 | Project base wiring in the CLI |
| `tests/test_release_cli.py` | 4 | Release CLI: options, help with documented epilog |
| `tests/test_release_engine.py` | 61 | **Changelog-anchored cutoff, `_drop_released()`, links, PR map by merge commit**, language front matter |
| `tests/test_reviewer_resolution.py` | 18 | Resolution ladder, partial-name rejection, dedup, failure tolerance |
| `tests/test_reviewer_suggestion.py` | 18 | Reviewer suggestion logic (ranking, exclusion, top-N, `last_commit_hash`) |
| `tests/test_risk_cli.py` | 4 | `risk` CLI: `--file`, `--format json`, `--base`, `--no-history` |
| `tests/test_security_ruleset.py` | 77 | Embedded ruleset matrix: regex, placeholders, level, wildcard, merge, translations |
| `tests/test_seed_command.py` | 8 | `gitpr seed`/`--seed`: creation, idempotency, force, `--all`, metrics |
| `tests/test_skill_command.py` | 10 | Skill template download and validation |
| `tests/test_skill_context.py` | 20 | `get_skill_context()` with `quiet=True`, fallbacks, PR artifact guard |
| `tests/test_skill_frontmatter.py` | 11 | `split_front_matter()`: BOM, CRLF, malformed YAML, language allowlist |
| `tests/test_skill_templates.py` | 99 | Parametrized sweep of **every published template** against embedded references |
| `tests/test_smart_excludes.py` | 17 | Smart pathspec filter, forced re-download, binary families (including `*.arrow`/`*.gguf`/`*.tflite`/`*.keras`) |
| `tests/test_suggest_reviewers.py` | 19 | Suggested reviewers in the PR flow + blame summary |
| `tests/test_tests_command.py` | 4 | `tests` group and `generate` CLI, `--finding` and epilog with link |
| `tests/test_thinking_words.py` | 5 | Loading, parsing with `;` separator and forced reload |
| `tests/test_tui_issue_token.py` | 8 | `allow_prompt=False`: never asks, never deletes, points to `gitpr --init` |
| `tests/test_updater.py` | 55 | **PyPI gate**: elapsed TTL, legacy/corrupted cache, atomic write, `force`, `bounded_urlopen`, PEP 440 comparator, pre-release, strict skip switch and subcommand gate |
| `tests/test_usage_log.py` | 27 | Usage log: date-derived name, synchronous write, silence, multi-forge `_repo_label()` |
| `tests/test_version_bump.py` | 17 | Semantic bump: major/minor/patch, targets and validation |
| `tests/test_web_links.py` | 26 | Remote normalization (https/ssh/scp/credentials/port) and URL patterns per forge |
| `tests/application/use_cases/test_activate_policy_pack.py` | 27 | Pack activation, lockfile, refusals |
| `tests/application/use_cases/test_baseline_gate.py` | 40 | Gate: load, classification, annotation, layers and abort |
| `tests/application/use_cases/test_baseline_summary.py` | 12 | Read-only baseline view for MCP/IDE |
| `tests/application/use_cases/test_calculate_risk.py` | 5 | Diff/findings/tests/history correlation, degradation |
| `tests/application/use_cases/test_create_baseline.py` | 30 | Creation/update with provenance and preservation of decisions |
| `tests/application/use_cases/test_generate_mentor_explanation.py` | 5 | Batches, severity, cache hit, missing key |
| `tests/application/use_cases/test_generate_pr_explanation.py` | 3 | Explain use case: provider dict and missing key |
| `tests/application/use_cases/test_generate_test_file.py` | 5 | Test generation use case: provider dict and raw string |
| `tests/application/use_cases/test_install_policy_pack.py` | 10 | Pack installation, origins and conflicts |
| `tests/application/use_cases/test_run_check.py` | 8 | Check orchestration: order, degradation, exit codes |
| `tests/application/use_cases/test_suppress_finding.py` | 29 | Suppression/removal in the four scopes |
| `tests/application/use_cases/test_validate_policy_pack.py` | 23 | Validation: closed schema, checksum, compatibility |
| `tests/badge/test_badge_append.py` | 9 | Idempotent badge append to the PR body |
| `tests/badge/test_badge_builder.py` | 19 | shields.io Markdown composition, escaping, color rule, style |
| `tests/badge/test_badge_cli.py` | 15 | `gitpr badge` command: snippet, `--readme`, `--style` |
| `tests/badge/test_badge_data.py` | 7 | Alert counting; `None` with no rules configured |
| `tests/badge/test_badge_offline.py` | 6 | Offline guard (network forbidden, loopback excepted) |
| `tests/badge/test_badge_optout.py` | 17 | `GITPR_BADGE=false` on every publication path |
| `tests/badge/test_badge_paths.py` | 10 ✅ | Single injection point in both publishers |
| `tests/demo/test_demo_app.py` | 22 | Tour TUI: navigation, screens, help modal |
| `tests/demo/test_demo_isolation.py` | 14 | The tour touches neither `~/.gitpr` nor the network |
| `tests/demo/test_demo_runner.py` | 33 | `DemoState` state machine: advance, back, completed, text mode |
| `tests/demo/test_demo_scenarios.py` | 47 | Integrity of the recorded scenarios (hunk arithmetic) in the 5 languages |
| `tests/demo/test_demo_text_mode.py` | 18 | Plain-text output (`--no-tui`) for CI and recordings |
| `tests/demo/test_fake_ai_provider.py` | 21 | Fake provider contract: mirrors `call_ai_model` argument by argument |
| `tests/domain/baseline/test_baseline_comparator.py` | 21 | Classification, status counts, `resolved`→`new` |
| `tests/domain/baseline/test_baseline_fingerprint.py` | 12 | Golden digest, raw line never stored, stability |
| `tests/domain/baseline/test_baseline_manifest.py` | 26 | Canonical JSON, checksum, unknown keys, invariants |
| `tests/domain/baseline/test_baseline_types.py` | 15 | Persisted statuses, scopes and precedence |
| `tests/domain/baseline/test_suppression_policy.py` | 52 | Layers, specificity, overrides, overdue debt |
| `tests/domain/check/test_check_config.py` | 3 | CLI > env > defaults precedence |
| `tests/domain/check/test_check_output.py` | 3 | Format dispatch and atomic write |
| `tests/domain/check/test_check_types.py` | 8 | Severity in 3 levels, spec aliases, summary |
| `tests/domain/finding/test_finding_types.py` | 3 | Canonical finding type: `snippet_hash`, `rule_version` |
| `tests/domain/linter/test_sast_finding_mapper.py` | 2 | Uniform formatting and `[Gitleaks + Regex]` deduplication |
| `tests/domain/linter/test_structured_findings.py` | 44 | `findings_out`: structured findings and the new-side line counter |
| `tests/domain/mentor/test_mentor_explanation_builder.py` | 8 | Pure builder: epistemic honesty, URLs removed, order preserved |
| `tests/domain/policy/test_policy_checksum.py` | 15 | Pack SHA-256 and manual-edit detection |
| `tests/domain/policy/test_policy_compatibility.py` | 22 | Version and credential compatibility |
| `tests/domain/policy/test_policy_manifest.py` | 42 | Closed schema, `baseline:` block, manifest errors |
| `tests/domain/policy/test_policy_resolver.py` | 47 | Precedence, layered composition, active pack × base |
| `tests/domain/pr/test_explain_section_builder.py` | 6 | Markdown rendering, tolerant parsing, `[FILL]`, dict payload and `None` |
| `tests/domain/risk/test_risk_calculator.py` | 5 | 50/30/20 formula, 0–100 clamp, mandatory elevation |
| `tests/domain/risk/test_risk_rules.py` | 5 | Weights, thresholds, critical path glob, override YAML |
| `tests/domain/tests_generation/test_framework_detector.py` | 11 | Detection by config, manifest and directory; override; warned failure |
| `tests/domain/tests_generation/test_scaffold_builder.py` | 7 | Conventional path per framework (Laravel, Pytest, JS/TS) |
| `tests/fix/test_apply_fix.py` | 53 | Full use case: review → AI → validate → classify → dry-run/apply |
| `tests/fix/test_chat_shared_extractor.py` | 8 | Extractor shared between chat and `fix` |
| `tests/fix/test_fix_cli.py` | 31 | Subcommand routing, options, dry-run default, `--force` |
| `tests/fix/test_fix_history.py` | 21 | `.gitpr/fix_history.json` ledger: atomic write, read |
| `tests/fix/test_fix_settings.py` | 10 | The five `GITPR_FIX_*` and the invalid-value fallbacks |
| `tests/fix/test_patch_applier.py` | 19 | `git apply` wrapper: check/apply/reverse, branch, status |
| `tests/fix/test_patch_extractor.py` | 13 | Fenced blocks → validated unified diff |
| `tests/fix/test_patch_safety_classifier.py` | 22 | safe/review_required/experimental matrix and reason codes |
| `tests/fix/test_resolve_last_review.py` | 13 | Last review selection, exclusion of file reviews |
| `tests/fix/test_rollback_fix.py` | 14 | `--rollback`: reverse and the three refusals |
| `tests/infrastructure/baseline/test_local_baseline_repository.py` | 24 | Single reader/writer: atomic write, checksum, repair |
| `tests/infrastructure/ci/test_junit_builder.py` | 2 | JUnit XML: failure/system-out |
| `tests/infrastructure/ci/test_masking.py` | 6 | Credential masking before serializing |
| `tests/infrastructure/ci/test_sarif_builder.py` | 2 | SARIF 2.1.0 and `tool.driver.rules` |
| `tests/infrastructure/git/test_risk_history_reader.py` | 4 | Safe `git log`: commits, bugs and reverts over 90 days |
| `tests/infrastructure/git/test_test_matcher.py` | 5 | Test ↔ production matching by convention |
| `tests/infrastructure/linter/external/test_bandit_bridge.py` | 3 | Bandit bridge: availability, parsing, mapping |
| `tests/infrastructure/linter/external/test_base_bridge.py` | 4 | ABC: hardened subprocess, timeout, missing binary |
| `tests/infrastructure/linter/external/test_gitleaks_bridge.py` | 4 | Gitleaks bridge: secret masking, per-file scope |
| `tests/infrastructure/linter/external/test_semgrep_bridge.py` | 3 | Semgrep bridge: JSON parsing, severity, non-Python skip |
| `tests/integration/test_baseline_effect_on_review_linter_risk.py` | 34 | Real baseline effect on review, lint and risk in a real repository |
| `tests/integration/test_baseline_exit_code_contract.py` | 8 | Exit code 1 for an inapplicable baseline and 2 in `check` |
| `tests/integration/test_policy_effect_on_review_linter_risk.py` | 31 | Real pack effect on review, lint and risk (with an autouse network guard) |
| `tests/issue_autoclose/test_close_issue_wiring.py` | 26 | PR flow wiring: flags, prompt, real publication, registry cleanup |
| `tests/issue_autoclose/test_insert_close_line.py` | 19 | Idempotent insertion and recognition of closing keywords |
| `tests/issue_autoclose/test_issue_registry.py` | 26 | Local registry: round-trip, atomicity, corruption, `.gitignore`, repo match, `github.yml` migration |
| `tests/issue_autoclose/test_register_created_issue.py` | 13 | Post-creation registration: multi-forge, persisted provider, swallowed failures |
| `tests/issue_autoclose/test_resolve_close_issue.py` | 42 | Decision tree: gates, `is_open`, PR, dead records, `not-supported`, confirmation |
| `tests/review/test_diff_normalizer.py` | 20 | Newlines, diff validation, smart-excludes in Python |
| `tests/review/test_diff_source.py` | 12 | `DiffOrigin`/`DiffSource`: provenance, `cache_scope`, `is_remote` |
| `tests/review/test_remote_pr.py` | 40 | Orchestration: gates, PR, diff, linter, optional comment |
| `tests/review/test_review_pr_cli.py` | 25 | `review-pr` CLI: options, rejections before the AI, `--post-comment` |
| `tests/scm/test_azure_devops_provider.py` | 51 | Azure DevOps provider: org/project, PRs, `ScmNotSupportedError`, issue read-back |
| `tests/scm/test_bitbucket_provider.py` | 49 | Bitbucket provider: Basic auth, workspace, PRs, `close #N` |
| `tests/scm/test_contract.py` | 44 | `ScmProvider` contract: signatures, dataclasses, errors, issue defaults |
| `tests/scm/test_factory.py` | 14 | `resolve_scm_provider()` + `detect_provider_from_remote(default=None)` |
| `tests/scm/test_github_api_shim.py` | 18 | Deprecated `github_api` shim → delegates to the provider |
| `tests/scm/test_github_provider.py` | 86 | GitHub provider: REST, headers, PRs, issues, releases, reviewer read-back |
| `tests/scm/test_gitlab_provider.py` | 56 | GitLab provider: API v4, namespace, synthesized headers, `overflow`, `Closes #N` |
| `tests/scm/test_init_wizard.py` | 20 | `--init` wizard: forge detection, per-forge slot, validation, persistence |
| `tests/scm/test_release_publish.py` | 8 | Release publication per forge (GitHub/GitLab) |
| `tests/scm/test_scm_settings.py` | 24 | Forge/credential resolver: precedence, legacy migration, warnings, read-only |
| `tests/split/test_apply_split_plan.py` | 11 | The only mutating module: selective staging, one commit per group |
| `tests/split/test_generate_split_plan.py` | 12 | Diff → units → groups → conflict pre-validation → messages |
| `tests/split/test_hunk_grouper.py` | 18 | Prompt, budget/trimming, AI call, response validation |
| `tests/split/test_hunk_parser.py` | 18 | `parse_units()`/`build_patch()`: text ↔ model, pure |
| `tests/split/test_selective_stager.py` | 11 | Subset staging, check against HEAD, clean index |
| `tests/split/test_split_cli.py` | 31 | Split CLI: options, dry run default, `--apply` |
| `tests/unit/test_version_consistency.py` | 5 ⚠️ | Version SSOT: `pyproject.toml`, `CLAUDE.md` and `GEMINI.md` against `src.updater.__version__` — **2 failures** because both docs still say 1.6.0 |
| `tests/unit/test_check_ci_docs_parity.py` | 3 🆕 | 5-language and 7-section parity of `docs/check-ci.*` + ADR-0011 + references in `action.yml` |
| `tests/sync_i18n.py` | — | i18n coverage verification script (scaffold; never executed) |

**Total:** **3436 scenarios collected in 160 test modules** (64 at the root + 12 in `tests/application/use_cases/` + 10 in `tests/fix/` + 10 in `tests/scm/` + 10 in `tests/infrastructure/` + 21 in `tests/domain/` + 7 in `tests/badge/` + 6 in `tests/demo/` + 6 in `tests/split/` + 5 in `tests/issue_autoclose/` + 4 in `tests/review/` + 3 in `tests/integration/` + 2 in `tests/unit/`; **+14 scenarios, +1 file** since the previous report).

**Quality notes for this version:**
- **✅ The blocker that froze the suite stays closed:** `-c` without `--hook` does not open `CommitApp` when there is no TTY — routing consults `_may_ask_the_user()`.
- **✅ The headless commit test remains stable:** `Button.press()` + `pilot.pause()`, mount predicate and 10 s timeout.
- **🆕 Testable charts without Textual:** `tests/test_ascii_chart.py` exercises the pure renderer and the tab composition separately — the chart logic does not depend on a real terminal.
- **🆕 The binary collapse gained external-caller coverage:** `test_full_diff_encoding.py` now proves that `generate_pr_content` sanitizes a raw diff with `U+FFFD`, beyond the path that came from git.
- **🆕 The `action.yml` migration note became a test:** documentation parity now also locks ADR-0011 and the references in the action file.
- **⚠️ 2 version-consistency failures:** `CLAUDE.md` and `GEMINI.md` still declare 1.6.0 while `src/updater.py` is already at 1.7.0; `tests/unit/test_version_consistency.py` fails those two scenarios. The fix is to update both documents.
- **`tests/conftest.py` remains hermetic:** `GITPR_SHOW_LOGS=false`, `GITPR_SKIP_UPDATE_CHECK=true`, `GITPR_LANG=en_us`, `GITPR_LINTER_SECURITY=false`, `LANG_VERSION` at the code version and `GITPR_SKIP_PROJECT_CONFIG_SEED=true`.
- **Network guard in several places:** `tests/demo/conftest.py`, `tests/badge/conftest.py`, `tests/integration/conftest.py` and `tests/split/conftest.py`.
- **Real git fixtures:** `tests/fix/git_fixture.py`, `tests/split/git_fixture.py` and `tests/test_full_diff_encoding.py`.
- **The extension suite is separate:** 7 Mocha scenarios in `extensions/vscode/src/test/mcp_client.test.ts`, of which 1 really integrates with the Python MCP server over stdio; it does not enter the pytest count.
- **CI runs on Python 3.10 and 3.13** with `fail-fast: false`, and the release has its own workflow with Trusted Publishing.

---

## **🌐 Internationalization and Documentation**

* **i18n coverage:** **1787 keys** in **all six** dictionaries (`es.json`, `es_es.json`, `fr.json`, `fr_fr.json`, `pt_br.json`, `pt_pt.json`). The code uses the same keys: **0 untranslated** and **0 orphans**. It was 1772 × 6 in the previous window.
* **🆕 `__lang_version__` went up to v0.0.42:** the new per-token price strings, currency, `KIND_FLOAT` validation, ASCII charts/tabs and VSIX installation entered the six dictionaries; the OTA refresh delivers the new translation without reinstalling.
* **Translation sources in lockstep:** a new key must exist in the code (source), in `langs/pt_br.json` (**master list**), in the FR/ES dicts of `scripts/sync_all_langs.py` and in the curated values of `scripts/fix_mangled_i18n_keys.py`; the key extractor is **AST** on both sides.
* **New topics:** **no** new canonical topic in this window — the documentation worked on existing topics and on the ADRs.
* **🆕 Topics updated in this window:**
  - `docs/editor-extension.*` (**5 files**) — decoupled VSIX release, granular marketplace gate and one-click installation.
  - `docs/auto-update.*` (**5 files**) — strict `GITPR_SKIP_UPDATE_CHECK` semantics and migration note.
  - `docs/commit-message-ia.*` (**5 files**) — migration note for the interactive commit without a TTY.
  - `docs/tutorial/install-from-source.*` (**5 files**) — same migration note for the update switch.
  - `docs/marketplace-publishing-guide.md` (**1 file, EN**) — direct links to the `ext-v*` releases; still partial.
  - `docs/config-tui.md` (**EN only**) — project configuration seed and CI/monorepo opt-out.
  - `docs/pull-request-publication.md` (**EN only**) — same seed/opt-out note.
  - `README.md` — link to the VSIX installation.
* **`CONTEXT.md` (updated):** the domain glossary went from **35 to 44 terms** (+9). Among others, **Granular marketplace gate** (replacing *Fail-fast secret check*), **Seed segregation**, **Draft extension release**, **ASCII chart**, **Chart data**, **Tabbed metrics view** and the price/currency terms entered.
* **Documentation in 5 languages:** **52 canonical topics** in `docs/` (count unchanged) — **50 with complete coverage in the 5 languages** and **2 partial topics** (`github-issue-prompt-com-gh`, `marketplace-publishing-guide`).
* **New specification artifacts:** [ADR-0011](docs/adr/0011-action-unified-check-migration.md), [ADR-0017](docs/adr/0017-documentation-site-language-fallback.md), [ADR-0018](docs/adr/0018-dashboard-ascii-charts-and-tabbed-navigation.md) and [ADR-0019](docs/adr/0019-project-configuration-seed-segregation.md) in `docs/adr/` (now **8 files**; `docs/adr/` numbering is not continuous because ADR-012/013/015/016 live in `docs/plans/`).
* **Claude Code local skills:** `.claude/skills/` with **29 skills** (count unchanged).
* **Memory Index:** `.claude/memory/MEMORY.md` with **41 patterns** (🆕 +1).
* **Task reports:** `docs/claude-code/reports/develop_natan/` (**119**; **+1**) + `docs/claude-code/reports/feat/` (**12**, unchanged) + `docs/claude-code/reports/main/` (**1**) and `docs/gemini/reports/` (**54**; **+12**), plus `docs/gemini/walkthrough/` (**31**; **+12**).
* **Status reports:** `docs/reports/` — this is the **24th report** (v0.0.24); v0.0.23 is never overwritten.
* **Development plans:** **174** files in `docs/plans/` (+13) + **36 files in `docs/gemini/plans/`** (+12) + **46 files in `docs/survey/`** (+12).

---

## **🔄 Distribution Pipeline**

1. **PyPI (single channel):** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **Mandatory update with an elapsed window:** execution checks PyPI at startup and **blocks with exit code 1** if there is a newer version, printing `pip install --upgrade gitpr-cli`. The response is cached for **1 hour** and `--update` **always** queries the network. The comparison is PEP 440: a remote pre-release does not block a stable install, an unreadable version counts as unknown. The gate covers subcommands when there is a TTY; CI, pipes, hooks, MCP and `-h` remain exempt.
3. **GitHub Releases:** production automated by `release.yml` on `v*` tags — build, validation, Trusted Publishing and release creation with artifacts.
4. **GitHub Actions:** `pr-review.yml` workflow + `action.yml` (installs via pip) + `tests.yml` (Python 3.10 and 3.13 matrix) + `extension.yml` (VSIX; 🆕 `github-release` job on `ext-v*` and `publish-marketplaces` job with a granular per-PAT gate) + `release.yml` (PyPI + GitHub Release on `v*`). `action.yml` calls a single `gitpr check` step — a **declared break** for those who used `--linter` + `--fullreview`, covered by the documentation parity test and by ADR-0011.
5. **MCP Server:** `gitpr-mcp` entry point via `pyproject.toml`
6. **Editor Extension:** independent VSIX package (`natafiuza.gitpr`), with VS Code Marketplace and Open VSX channels; requires Node.js 20+ only for the build, not for use. The `.vsix` is attached to the GitHub Release of `ext-v*` even when marketplace publication is turned off.
7. **Templates and OTA languages:** `templates/` and `langs/*.json` served from GitHub (main) — `__lang_version__` **v0.0.42** 🆕 refreshes the local copies in `~/.gitpr/langs/`; `__scripts_version__` **v0.0.4** refreshes the hooks.
8. **Version derived from the code:** `pyproject.toml` uses `version = {attr = "src.updater.__version__"}` — `__version__` is the **single** source, guaranteed by [ADR-014](docs/adr/0014-canonical-package-version-ssot.md) and by `tests/unit/test_version_consistency.py`.
9. **`package-data` matters:** the four embedded packs (`src/policy_packs/*`) travel in the wheel.
10. **✅ Release status — 1.6.0 and 1.7.0 published:** PyPI serves **1.7.0**. 1.6.0 was tagged at `f5088c4`; 1.7.0 has its own section in `CHANGELOG.md` (`6d1f1dc`) and bump at `2354a30`. `develop_natan` was merged into `main` by PR #310.
11. **⚠️ 1.7.0 tag misaligned:** the `v1.7.0` tag on origin still points to `b2688c4`, a commit **prior** to the bump, where `__version__` was 1.6.0. The `release.yml` guard compares tag and `__version__`; aligning the tag to commit `2354a30` (or recreating it) is the pending item so that the GitHub Release reflects the published code.

---

## **📈 Evolution since the Previous Report (v0.0.23)**

| Area | v0.0.23 (previous) | v0.0.24 (current) |
|------|-------------------|-----------------|
| **GitPR Version** | 1.6.0 — in the changelog and in `develop_natan`, **without a tag**, PyPI at **1.5.0** | **1.7.0** — published on PyPI; the `v1.7.0` tag exists but points to the pre-bump commit |
| **Language Version** | v0.0.41 | **v0.0.42** — dictionaries synchronized, +15 keys |
| **Hook Scripts Version** | v0.0.4 | v0.0.4 (unchanged) |
| **AI Providers** | Gemini + DeepSeek + Ollama | Unchanged |
| **Languages** | 5 languages, 6 dictionaries | Unchanged |
| **Subcommands** | 16 | 16 — unchanged; 4 groups (baseline 6, metrics 8, policy 7, tests 1) |
| **CLI Flags** | 34 root options | 34 — unchanged |
| **SCM Configuration** | Per repository and per forge | Unchanged |
| **Issue Closing** | Multi-forge | Unchanged |
| **Configuration Seed** | `gitpr seed`/`--seed` + 5-step wizard | Behavior unchanged; 🆕 boundary formalized in ADR-0019 |
| **Layers** | 9 domain packages, 15 use cases | Unchanged — `ascii_chart.py` is pure presentation |
| **MCP Tools** | 18 tools / 19 resources / 7 prompts | Unchanged |
| **`DEFAULT_CONFIG`** | 55 keys, derived from the schema (`seeded`) | **56 keys** — `GITPR_METRICS_CURRENCY` |
| **Config Schema** | 100 `ConfigField` / 18 categories / 55 `seeded` | **109 / 18 / 56** — `KIND_FLOAT` + 9 currency/price fields |
| **i18n (keys per file)** | 1772 × 6 | **1787 × 6** — 0 missing, 0 orphans ✅ |
| **Update Gate** | 1 h elapsed TTL, `-u` always online, PEP 440 | Code unchanged; 🆕 migration note for the strict switch |
| **Linter** | Regex + secrets + SAST + `findings_out` + baseline-aware | Unchanged |
| **Diff / Smart Excludes** | + binary hunk guard and 100+ patterns | + **defensive collapse in `generate_pr_content`** and 4 new binary families |
| **Git Hooks** | post-checkout/post-merge/pre-push in the ledger | Unchanged — `prepare-commit-msg` remains non-interactive |
| **SCM Layer** | 4 forges, pagination, `web_links.py` | Unchanged |
| **Release engine** | `[1.6.0]` section; 1.5.0 published; 1.6.0 without a tag | `[1.7.0]` section; **1.6.0 and 1.7.0 published**; the 1.7.0 tag misaligned |
| **Metrics** | Token Trend and Latency Histogram in CLI/TUI/MCP | + **ASCII charts and tabs** in the dashboard; 🆕 per-model prices and currency |
| **Commit TUI** | TTY guard and stable headless test | Unchanged; 🆕 migration note for the flow without TTY |
| **Extension** | Hardened workflow with `dry_run` and fail-fast | + **decoupled VSIX release** and **granular per-marketplace gate** |
| **Documentation** | 52 topics (50 complete + 2 partial) | **52 topics (50 complete + 2 partial)** — 4 new ADRs |
| **Domain agenda** | `CONTEXT.md` with 35 terms | **44 terms** — Granular marketplace gate, Seed segregation, ASCII chart etc. |
| **CI** | `tests.yml` + `action.yml` + `extension.yml` + `release.yml` | Base files unchanged; `extension.yml` restructured |
| **Test Suite** | 3422 scenarios (159 files) | **3436 scenarios (160 files) — +14 scenarios, +1 file** |
| **Suite Stability** | No known blocker | **2 version-consistency failures** in `test_version_consistency.py` (docs at 1.6.0) |
| **Dependencies** | No new Python dependencies | None new — `ascii_chart.py` uses only the stdlib |
| **Commits since the report** | 16 commits, 0 merges | **13 commits, 13 merges** (#295–#310) |
| **Referenced PRs** | 14 PRs (#261 … #291) | **13 merged PRs** (#295, #296, #297, #298, #299, #301, #302, #304, #305, #307, #308, #309, #310); issues closed #294, #300, #303 and #306 |
| **Memory Index** | 40 patterns | **41 patterns** |
| **Task reports** | 118 claude-code, 42 gemini, 19 walkthrough | **119 claude-code (+12 feat, +1 main), 54 gemini, 31 walkthrough** |
| **Development plans** | 161 plans, 24 gemini, 34 surveys | **174 plans, 36 gemini, 46 surveys** |

---

## **🚧 Next Steps**

* **🆕 Update `CLAUDE.md` and `GEMINI.md` to 1.7.0:** the `2354a30` bump changed only `src/updater.py`, and both documents still say 1.6.0. `tests/unit/test_version_consistency.py` has **2 failures** because of this — it is the most urgent fix of this window.
* **🆕 Align the `v1.7.0` tag:** the tag on origin points to `b2688c4` (pre-bump, where `__version__` was 1.6.0). The `release.yml` guard compares tag and version; moving/recreating the tag on commit `2354a30` makes the GitHub Release match PyPI 1.7.0.
* **🆕 Translate `marketplace-publishing-guide`:** the topic remains English-only; PT-BR, PT-PT, ES and FR are still pending.
* **🆕 Translate the EN-only editions of `config-tui` and `pull-request-publication`:** the seed/CI and opt-out migration notes entered only the English version.
* **🆕 Publish the extension to the marketplaces:** the granular gate already evaluates `VSCE_PAT` and `OVSX_PAT` independently and the VSIX is already attached to the GitHub Release, but publication depends on the credentials.
* **🆕 Document prices/currency in `metricas-telemetria`:** the `GITPR_METRICS_PRICE_*` and `GITPR_METRICS_CURRENCY` fields are in the TUI and in the code, but the telemetry topic still does not explain them.
* **Per-repository SCM migration note:** the `GITPR_SCM_PROVIDER`, `GITPR_SCM_TOKEN_ENCRYPTED` and `GITPR_SCM_BASE_URL` keys were removed from `.env` by the automatic migration. Anyone who automated writing those keys needs to know there is now one slot per forge. **Inherited item, still open.**
* **Translate `docs/metricas-telemetria.*`:** PT-BR, PT-PT, ES and FR still lag behind the English original. **Inherited item, still open.**
* **Track the unrecognized self-hosted host:** remote resolution only recognizes domains containing the forge name; a GitLab/GitHub on a custom domain falls back to GitHub until someone runs `gitpr --init` and writes the override. **Inherited item, still open.**
* **Anthropic Claude provider:** Direct support for the Claude API (`claude-sonnet-5`). **Inherited item, still open.**
* **More providers:** OpenAI direct, additional local providers. **Inherited item, still open.**
* **`GITPR_SKIP_UPDATE_CHECK` migration note:** ✅ **completed** — `docs/auto-update.*` and the installation tutorial describe the strict parser.
* **Interactive `-c` migration note:** ✅ **completed** — `docs/commit-message-ia.*` in the 5 languages explains the flow without a TTY and `--hook`.
* **1.6.0/1.7.0 tag and publication:** ✅ **completed** — PyPI serves 1.7.0 (aligning the 1.7.0 tag remains as a new item above).
* **Merge of `develop_natan` into `main`:** ✅ **completed** — PR #310.
* **Configuration TUI debts:** ✅ **completed** — formal audit and closure recorded in the 2026-11-09 reports.
* **Smart Excludes item in the TUI:** ✅ **completed** — formal audit and closure recorded.
* **`action.yml` migration note:** ✅ **completed** — `action.yml`, ADR-0011 and the parity test with 3 scenarios.

### ✅ Completed in this window (2026-11-08 → 2026-11-10)

* ~~**The dashboard only had textual sections**~~ 🆕 — PR #307 (`07822f7`): `src/ascii_chart.py`, **Events**/**Charts & Trends** tabs, `1`/`2` bindings and `tests/test_ascii_chart.py` (9 scenarios) ([ADR-0018](docs/adr/0018-dashboard-ascii-charts-and-tabbed-navigation.md)).
* ~~**Binaries could enter through an external caller**~~ 🆕 — PR #304 (`c1cddbb`): `collapse_binary_hunks()` at the start of `generate_pr_content` and `*.arrow`/`*.gguf`/`*.tflite`/`*.keras` in the smart excludes.
* ~~**The extension release depended on the package release**~~ 🆕 — PR #302 (`3bcc6c9`): `github-release` job on `ext-v*`, granular marketplace gate and VSIX installation in the 5 languages.
* ~~**Cost and currency were fixed**~~ 🆕 — PR #295 (`c5485dd`): `KIND_FLOAT`, `GITPR_METRICS_CURRENCY`, 8 price fields and priced Gemini aliases.
* ~~**The `action.yml` break was not formalized**~~ 🆕 — PR #301 (`7bd6678`): migration note in `action.yml`, [ADR-0011](docs/adr/0011-action-unified-check-migration.md) and parity test.
* ~~**The site's ES/FR fallback had no contract**~~ 🆕 — `80378b0`: [ADR-0017](docs/adr/0017-documentation-site-language-fallback.md).
* ~~**The boundary between auto-seed and `gitpr seed` was not written down**~~ 🆕 — `98b23cc`: [ADR-0019](docs/adr/0019-project-configuration-seed-segregation.md).
* ~~**The interactive commit migration note did not exist**~~ 🆕 — `1995213`: `docs/commit-message-ia.*` in the 5 languages.
* ~~**The strict `GITPR_SKIP_UPDATE_CHECK` semantics were not documented**~~ 🆕 — `1995213`: `docs/auto-update.*` and tutorial.
* ~~**The project configuration seed had no migration note**~~ 🆕 — `7cf4ac8`: `docs/config-tui.md` and `docs/pull-request-publication.md`.
* ~~**The config TUI and Smart Excludes debts were open**~~ 🆕 — `8886976` and `a7c316a`: formal audit and closure.
* ~~**The changelog did not have 1.7.0**~~ 🆕 — `6d1f1dc`: `## [1.7.0] - 2026-11-10` section.
* ~~**The package and dictionary version was at 1.6.0/v0.0.41**~~ 🆕 — `2354a30`: bump to **1.7.0** and **v0.0.42**.
* ~~**1.6.0 was tagged but not published**~~ — PyPI went on to serve 1.6.0 and then 1.7.0.
* ~~**The whole window was in `develop_natan`, without a merge**~~ — PR #310 took the branch to `main`.

---

**Report generated on:** 2026-11-10  
**Branch:** `develop_natan`  
**Author:** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
