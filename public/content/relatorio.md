# **🚀 Project Status Report: GitPR CLI — v0.0.21 (2026-10-29)**

## **📌 Overview**

**GitPR** is an advanced CLI (Command Line Interface) tool for automating Git processes using Artificial Intelligence (Google Gemini / DeepSeek / Ollama). Its main goal is to act as a local intelligent assistant that performs Code Reviews, generates Pull Requests, semantic commit messages, audits technical debt, and injects best practices into the developer workflow (Shift Left).

**What's New in This Version (v0.0.21):**
- **`gitpr -c` stopped being a silent pipeline and became a 4-step TUI:** before, the command validated the tree, asked the AI for the message and committed right away — with no chance to choose files, review the text or reject a bad suggestion. Now `CommitApp` (`src/ui/commit_app.py`) opens the **Stage Selector** for files outside the index, generates the message in a background thread over the **staged diff**, shows the **review/edit modal** and runs the commit in an **animated progress modal**. `--hook` remains silent and merge-aware.
- **The message now describes what was actually staged:** `get_git_staged_diff()` (`src/core.py`) runs `git diff --cached` with the same guards as `get_git_diff()` (`-U1 -w -M -B`, smart excludes, UTF-8 with `errors="replace"`), falling back to the working-tree diff only when the index is empty.
- **The commit modal closes by itself on success:** the flow used to stop at the progress modal until the user clicked **Close**. Now `mark_finished(False)` + `set_timer(0.6, self.exit)` hand the terminal back to the user, and `main.py` prints the result (`committed`/`failed`) with the git output. On error the **Close** button is preserved, because that is where the failure log stays readable.
- **Two windows of release debt closed:** **1.4.3** (the update gate fix) and **1.4.4** were tagged and published — PyPI serves **1.4.4**, and the `[1.4.3]` and `[1.4.4]` sections are committed in `CHANGELOG.md`.
- **The i18n debt got worse, it was not paid:** the dictionaries remain at **1620 keys** (×4) and **1618** (`es.json`, `fr.json`), but the code now uses **1629** keys — the **3 new commit TUI strings** went in untranslated. That is **11 keys without a dictionary** and **2 orphans**, and `__lang_version__` climbed to **v0.0.36** without any language file changing. ❌

- **Current version:** 1.4.4 (**tagged as `v1.4.4` and published on PyPI** — `pip install --upgrade gitpr-cli` delivers this version)
- **Language dictionary version:** v0.0.36 (🆕 bump with no dictionary changes)
- **Hook scripts version:** v0.0.4 (unchanged)
- **Distribution:** PyPI (`pip install gitpr-cli`) — binary channel removed
- **Website:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Repository:** [https://github.com/gitpr-cli/gitpr.git](https://github.com/gitpr-cli/gitpr.git)
- **License:** LGPL-2.1
- **Supported languages:** en_us, pt_br, pt_pt, es_es, fr_fr (5 languages, 6 dictionaries)

---

## **🏗️ Architecture and Base Libraries**

* **Language:** Python >= 3.10
* **CLI Framework:** Click (for commands, flags and terminal formatting) — **15 subcommands** (`badge`, `baseline`, `check`, `config`, `demo`, `explain`, `fix`, `mentor`, `metrics`, `policy`, `release`, `review-pr`, `risk`, `split`, `tests`). Four of them are **groups**: `baseline` (6 subcommands), `metrics` (8), `policy` (7) and `tests` (1). 🆕 The `-c`/`--commit` path is no longer terminal-only and now opens the TUI when it is not a hook.
* **UI/Terminal:** Textual — TUI for interactive chat, issue editing, help screen, metrics dashboard, PR Publisher (with the merge progress modal reused), 🆕 **CommitApp** (`src/ui/commit_app.py`, 220 lines) which reuses the PR Publisher's three modals, linter errors (`LinterApp`), configuration (`ConfigApp`), the `NoticeScreen` modal, the `gitpr demo` tour (`src/ui/demo/`) and the ledger migration assistant (`src/ui/metrics_migration_app.py`).
* **Layered Architecture:** `src/domain/` (pure rule, no I/O) + `src/application/use_cases/` (orchestration, **15 use cases**) + presentation (CLI/TUI). **9 domain packages**: `baseline`, `check`, `finding`, `linter`, `mentor`, `policy`, `pr`, `risk`, `tests_generation`. The commit TUI did **not** enter `domain/`: there is no new rule, only orchestration of widgets over existing `core.py` functions.
* **Editor Extension:** `extensions/vscode/` — a thin TypeScript MCP client for **VS Code and Cursor** (Node.js 20+, `@modelcontextprotocol/sdk`, VSIX). It contains no business logic.
* **Cryptography:** `cryptography.fernet` for local protection of API keys, GitHub tokens and SCM tokens for the forges.
* **Configuration:** `python-dotenv`, `pyyaml` (static linter) + its own declarative schema (`src/config_schema.py`, **80 `ConfigField`** and **16 categories**). `packaging` feeds the gate's version comparison (PEP 440) and policy pack compatibility.
* **AI Providers:** Integration via the official Google GenAI SDK, OpenAI SDK (DeepSeek) and OpenAI SDK (local Ollama). Configured defaults: `gemini-pro-latest` / `gemini-flash-lite-latest` and `deepseek-v4-pro` / `deepseek-v4-flash`.
* **Forge APIs:** `requests` (REST) — multi-forge abstraction in `src/infrastructure/scm/` (GitHub, GitLab, Bitbucket, Azure DevOps) + `web_links.py`; `src/github_api.py` kept as a deprecated shim.
* **Linter / SAST:** YAML regex + Checkstyle bridge + built-in secrets ruleset (`src/security_ruleset.py`) + 4 bridges in `src/infrastructure/linter/external/` (base + Semgrep, Gitleaks, Bandit), with the normalized model in `src/domain/linter/sast_finding_mapper.py` and the canonical finding in `src/domain/finding/`.
* **CI Infrastructure:** `src/infrastructure/ci/` — `sarif_builder.py` (SARIF 2.1.0), `junit_builder.py` (JUnit XML) and `masking.py` (credential masking before serializing any artifact).
* **Git Internals:** `src/infrastructure/git/` — `patch_applier.py`, `selective_stager.py`, `identity.py`, `risk_history_reader.py` and `test_matcher.py`.
* **Local persistence:** `src/ledger.py` — SQLite (`~/.gitpr/metrics/telemetry.db`) with WAL, `PRAGMA user_version`, UUID keys and transport by bundle.
* **Networking:** `src/net.bounded_urlopen` (`UPDATE_FETCH_HARD_TIMEOUT = 3.0`) bounds the **wall clock including DNS** in the update gate.
* **MCP:** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 — **18 annotated tools**, **19 resources**, 7 prompts. 🆕 The commit TUI exposed **no** new tool: commit already had its own path in the CLI, and MCP remains read-only at that point.
* **Tests:** Pytest + `unittest.mock` (**141 test modules** — 54 at the root, 12 in `tests/application/use_cases/`, 10 in `tests/fix/`, 9 in `tests/scm/`, 7 in `tests/badge/`, 6 in `tests/split/`, 6 in `tests/demo/`, 5 in `tests/domain/baseline/`, 4 in `tests/review/`, 4 in `tests/infrastructure/linter/external/`, 4 in `tests/domain/policy/`, 3 in `tests/infrastructure/ci/`, 3 in `tests/domain/check/`, 2 in `tests/integration/`, 2 in `tests/infrastructure/git/`, 2 in `tests/domain/tests_generation/`, 2 in `tests/domain/risk/`, 2 in `tests/domain/linter/`, 1 in `tests/infrastructure/baseline/`, 1 in `tests/domain/pr/`, 1 in `tests/domain/mentor/`, 1 in `tests/domain/finding/` —, **3010 collected scenarios**) + e2e tests of the MCP server over a real subprocess (JSON-RPC stdio) + real git repository fixtures (`tests/fix/`, `tests/split/`, `tests/test_full_diff_encoding.py`) + network isolation guard (`tests/demo/conftest.py`, `tests/integration/conftest.py`). The extension has its own Mocha suite (7 tests against the real MCP server).
* **Packaging:** setuptools/build (PyPI) — `version = {attr = "src.updater.__version__"}`; packs embedded via `package-data`; `packaging>=23.0`. The extension is packaged separately (`vsce package`, VSIX).
* **CI/CD:** GitHub Actions — `pr-review.yml` + `action.yml` (calls `gitpr check`) + `tests.yml` (Python 3.10/3.13 matrix) + `extension.yml` (build, test and VSIX packaging; publishing to the Marketplace/Open VSX on `ext-v*` tags).

---

## **🧩 Implemented Modules and File Architecture**

### **1. Core and Git Operations (`src/core.py`)**

* **Structured Generation:** Communicates with the LLM requesting a strictly JSON response.
* **Map-Reduce (Giant Diffs):** When the diff exceeds ~90k tokens, it is automatically split into per-file batches (`split_diff_into_chunks`), each part is processed (Map) and the summaries are unified (Reduce). Supports PRs, commits and Issues. The `provider`/`model` are aggregated across chunks and the `meta`/`modules` are forwarded to `log_command_metric`.
* **Local Tokenizer:** `tokenizer.json` for precise token estimation before sending to the AI.
* **Token Estimation:** Lightweight heuristic `len() // 4` via `estimate_token_count()` with a fallback to the local tokenizer.
* **Native Git Optimization:** Flags `-U1`, `-w`, `-M`, `-B` in the `get_git_diff` and `get_git_full_diff` commands.
* **🆕 `get_git_staged_diff()`:** runs `git diff --cached -U1 -w -M -B -- <SMART_EXCLUDES>` with `encoding="utf-8"`/`errors="replace"` and `stdin=DEVNULL`, returning `None` when git fails (never a disguised empty string). It is the preferred source of the commit message: what is not staged does not enter the prompt.
* **`get_split_diff()`:** `split`'s own diff, with `SPLIT_DIFF_ARGS` (`--binary -M -U3`), **deliberately without** reusing `get_git_diff()`.
* **Pre-Save (`--pre-save`):** Hidden debug flag that saves the full payload (system instruction + prompt) as JSON before every AI call.
* **Two-Layer Smart Excludes:** Global layer (`~/.gitpr/conf/`) + project-local (`./.gitpr/conf/`), merged at runtime. Auto-seeding of the local file on first run. `_load_smart_excludes()` accepts `force=`.
* **Metrics with Time Tracking:** `log_command_metric()` in all flows, with `meta`, `modules` and `duration_ms`.
* **Centralized Output Resolution:** `resolve_output_path()` — defaults to `.gitpr/reports/{type}/`.
* **SCM Wizard (`run_scm_init_wizard()`)**: `gitpr --init` — detects the forge from the origin remote, requests per-forge extras, validates the token and persists **only on success**.
* **Release Skill Template (`ensure_release_skill_template()`)**: Downloads `templates/gitpr.release.*.md` on the first use of `gitpr release`.
* **Corrupted Skill Guard:** `_CORRUPTED_PR_SKILL_ARTIFACT` documents the JSON fragment that leaked into the `pt_br` template, and `get_skill_context()` removes it **in memory** when `action_type == "pr"`.
* **Shared Skill Registry:** `SKILL_FILES_BY_TYPE` / `SKILL_TYPES` / `DEFAULT_SKILL_TYPE` in `src/config.py`.
* **Review Engine with Cache Scope (`generate_pr_content`):** `cache_scope` and `store_diff`; the `cache_scope` also carries the active policy pack.
* **Co-authorship Trailer:** `COAUTHOR_TRAILER` + `append_coauthor_trailer()` — idempotent, preserves third-party trailers. 🆕 It is this trailer that the commit TUI applies to the edited message before `execute_git_commit()`.
* **Hardened Git Output Capture:** the `subprocess.run` calls in `src/cache.py`, `src/core.py`, `src/issue_engine.py` and `src/main.py` that capture git output use `encoding="utf-8"` **with** `errors="replace"` — including the new `get_git_staged_diff()`.
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
* **Command Routing:** Manages all flags and the **15 subcommands** — `release`, `config`, `fix`, `review-pr`, `demo`, `badge`, `split`, `tests`, `explain`, `mentor`, `metrics`, `policy`, `risk`, `baseline` and `check`.
* **Default Behavior:** Running `gitpr` with no flags opens the PR Publisher TUI.
* **Flags (32 Click options at the root — unchanged in this window):** `--mentor`, `--explain`, `--init`, `--no-suggest-reviewers`, `--no-publish`, `--no-edit`, `--base <branch>`, `--plugins`, `--status`, `--no-unstaged-check`, `--linter-setup`, `--version` etc.
* **🆕 The `-c`/`--commit` Branch Was Rewritten:** the old inline check for unstaged files was replaced by three paths: (1) `--hook` → behavior identical to before (skips the AI during a merge, builds the diff and returns the message to the hook); (2) without a hook → `CommitApp(provider=active_provider, skip_unstaged=no_unstaged_check).run()`; (3) an unconditional `return` after the TUI, because the commit now happens **inside** it.
* **🆕 TUI Result Mapped to the Console:** `no_changes` prints the yellow "no new code" notice; `committed` prints `✅ Commit created successfully!` in green and then the first line/output of `git commit` (the SHA). `cancelled` and `failed` return silently — the user already saw the reason in the modal.
* **Update Gate in Subcommands:** the group callback returned before the root gate, so `gitpr release`, `fix`, `review-pr` and the others were **never** blocked by a new release. The gate runs before the `return`, conditioned on `may_ask and not update and not help_flag and not _subcommand_help_in_argv()`. `_may_ask_the_user()` requires a **TTY on both ends** — CI, pipes, cron, hooks and the MCP server stay free.
* **Subcommand Help Read from the Raw `argv`:** `_subcommand_help_in_argv()` exists because Click **clears the context arguments** before the group callback runs: at that point `gitpr release -h` is indistinguishable from `gitpr release`. The scan fails open, costing at most one skipped gate.
* **`check` Subcommand — 8 options:** `--base <ref>`, `--format sarif|json|junit`, `-o/--output <path>`, `--fail-on …`, `--no-ia`, `--ia-only-high-risk`, `--baseline <path>` and `--policy <name>@<version>`.
* **`baseline` Group — 6 subcommands:** `create`, `show`, `validate`, `update`, `suppress` and `unsuppress`.
* **`policy` Group — 7 subcommands:** `list`, `show`, `validate`, `use`, `off`, `install` and `init`.
* **`metrics` Group — 8 subcommands:** `export`, `bundle`, `merge`, `dashboard`, `migrate`, `prune`, `purge` and the hidden `hook-event`.
* **`risk` Subcommand — 4 options:** `--file`, `--format text|json`, `--base`, `--no-history`.
* **`mentor` Subcommand — 2 options:** `--finding`, `--provider`.
* **Environment Variables (58 keys in `DEFAULT_CONFIG`):** the four `GITPR_BASELINE_*`, the two mentor ones, the two policy ones and `GITPR_RISK_INCLUDE_IN_REVIEW`. **Outside `DEFAULT_CONFIG`:** `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT`, `GITPR_METRICS_CURRENCY`, the **9 `GITPR_CHECK_*`** and `GITPR_SKIP_UPDATE_CHECK` (strict boolean). 🆕 `GITPR_AUTO_STAGE` and `GITPR_SKIP_UNSTAGED_CHECK` already existed and now also govern the commit TUI.
* **Contextual Help:** `gitpr -h --explain` renders; `HELP_MAP["explain"]` and `HELP_PRIORITY["explain"]` point to `docs/explain-pr`, and `gitpr tests`/`gitpr tests generate` have their own `epilog=`.
* **`.env` Write Layer:** `read_env_file_values()` reads **only the file** via `dotenv_values`; `save_config_values()` with `set_key`; `remove_config_value()` with `unset_key`.
* **Baseline Gate in the Flow:** `main.py` resolves the baseline gate before lint, review, full review, file audit, remote review and risk.
* **Direct Publication Refactored:** `_publish_pr_directly` (`--no-edit`) delegates to the **`publish_pull_request_use_case`** — the same path as the `publish_pull_request` MCP tool.

### **4. PR Publisher TUI (`src/ui/pr_publish_app.py` and `src/ui/pr_publish_help.py`)**

* **Complete Interactive Interface:** TUI to review, edit and publish Pull Requests straight from the terminal.
* **7 Modal Screens:** `StageFilesScreen`, `CommitConfirmScreen`, `CommitProgressScreen`, `CommitMessageScreen`, `LinterErrorScreen`, `ErrorScreen` and `NoticeScreen`.
* **🆕 Three of Them Also Serve the Commit:** `StageFilesScreen` (with "Select All", "Deselect All", "Stage Selected", "Skip Staging" and "Cancel"), `CommitMessageScreen` and `CommitProgressScreen` (which accepts `title` and `mark_finished(show_close=)`) are imported by `CommitApp` — no widget was duplicated, and the `_with_suppressed_stdout` helper is shared too.
* **Badge in the PR Body:** `attach_pr_badge()` runs in `src/main.py` right after `pr_data` is complete — **single injection point**.
* **`_attach_reviewers` with resolution:** Resolves what the user typed **before** sending, reports what was discarded and, on a batch `422`, **retries one by one**.
* **Suggested Reviewers:** Queries the forge for suggested reviewers; `_reviewer_suggestion_view()` builds `resolutions` via `resolve_candidates`.
* **Bindings:** F1 (Help), F2 (Save local .md), F3 (Publish via forge), Esc (Quit).
* **Auto-Commit Flow:** Linter → AI message → confirmation → commit → push → publish PR.
* **Merge Progress Modal:** `CommitProgressScreen` with an optional `title` and `mark_finished(show_close=False)`; success displays `✅ PR #N merged successfully!`, conflicts and unexpected errors go through `_on_merge_failure`.
* **Existing PR Handling / Auto-Upstream / Merge Flow** — unchanged (`GITPR_AUTO_MERGE`).

### **5. GitHub API Module (`src/github_api.py`)**

* **Deprecated Shim:** `create_pull_request()`, `update_pull_request()`, `merge_pull_request()` and the other functions delegate to `src/infrastructure/scm/github_provider.py`; it emits a `DeprecationWarning` and keeps the legacy `(ok, data, status)` tuples — no new code may import it.

### **6. Static Analysis Engine / Linter (`src/linter_engine.py`)**

* **Offline Linter:** Statically analyzes the added (`+`) lines in the diff without spending AI quotas.
* **YAML Rules:** Reads `.gitpr.linter.yml` (created via `--skill`).
* **Linter Plugins:** Additional rules from `~/.gitpr/plugins/linter/*.yml`.
* **Extension Wildcard:** `extensions: ["*"]` means **every** file, including those without a suffix and dotfiles.
* **Built-in Secrets Ruleset:** `load_linter_rules()` merges `src/security_ruleset.py` **after** the project rules and the plugins. Seven rules, all `extensions: ["*"]`: five `error` and two `warning`. The alert **never echoes the value**.
* **Policy Pack Layer:** `load_linter_rules()` applies the layered merge of the active pack and **overrides severities**.
* **Structured Findings (`findings_out`):** beyond the alert strings, the engine returns the normalized findings of the canonical type `src/domain/finding/`.
* **Opt-In SAST Bridges:** Semgrep, Gitleaks and Bandit run on the whole-file path **and** on the diff path, with `[Gitleaks + Regex]` dedup, value masking (`AKIA****`) and a warning when the tool is enabled but not on `PATH`.
* **`skip_external`:** `parse_diff_and_lint(..., skip_external=True)` in the remote flow.
* **Consolidated Report:** `generate_linter_report_content()` consolidates regex + external errors into `.gitpr/reports/linter/` — generated only when there are violations.
* **Baseline-aware:** alerts are annotated with the baseline status before they become a report.

### **7. Security and Authentication (`src/security.py`, `src/config.py`, `src/tui_issue.py`)**

* **Cryptography:** Generates the `secret.key` master key in `~/.gitpr/`.
* **Token Protection:** `encrypt_data`/`decrypt_data` for AI keys, GitHub PAT and SCM tokens for the forges.
* **Multi-Forge Validation:** `validate_or_request_scm_token(provider, repo_display)` — 401 → reauthentication loop preserving the draft.
* **Secrets in the Configuration TUI:** `KIND_SECRET` fields are edited masked, **never** display the value in the clear and are encrypted with Fernet before writing.
* **Secrets Scanning in the Flow:** The linter — which runs in the pre-commit hook — is a credential gate with identical rules on every machine.
* **Masking in CI:** `src/infrastructure/ci/masking.py` removes private keys, AWS access key ID/secret, GitHub, Google, OpenAI and Slack tokens, `Bearer`, basic-auth in URLs and generic password assignments **before** the artifact is serialized. The extension has a TypeScript mirror (`masking.ts`).
* ⚠️ **Coverage gaps confirmed as still open:** the generic rule still requires quotes after the separator, so `API_KEY=abc123` is still missed; the prefixes `ASIA…`, `github_pat_…` and `xoxc-`/`xoxd-` remain outside the ruleset **of the linter**; and there is no left boundary in the key name.

### **8. Auto-Updater (`src/updater.py`)**

* **PyPI as the Single Source:** `get_latest_remote_version()` queries `https://pypi.org/pypi/gitpr-cli/json` and writes the cache. It accepts `force=True`, used by `--update` to **always** go to the network.
* **Rolling 1-Hour Window (`UPDATE_CACHE_TTL_SECONDS = 3600`):** the cache is no longer "valid as long as the local date is today". The document is `{"checked_at": <ISO>, "version": ...}`; `_cache_is_fresh()` accepts an age in `[0, 3600)` seconds.
* **Validation on Read:** a real `dict`, a parseable timestamp, a non-empty string version. A timestamp **in the future** counts as expired, and the legacy format with `date` is treated as expired and rewritten.
* **Atomic Write:** `tempfile.NamedTemporaryFile` in the same directory + `os.replace`, with cleanup of the temporary file on failure.
* **Hardened Fetch (`bounded_urlopen`):** a non-JSON body and a payload without a version are **rejected before entering the cache**. A 3 s wall-clock ceiling, DNS included.
* **PEP 440 Comparison:** `_coerce_version()` uses `packaging.version.Version`, tolerating the leading `v` and PEP 440 suffixes; `parse_version()` keeps the tuple contract for external callers. `_is_newer()` **ignores a remote pre-release** on a stable installation and treats an unreadable version on either side as unknown.
* **Mandatory Gate (`enforce_update_required()`):** Returns `True` (after printing both versions and the pip command) when the published version is newer; `False` when it is up to date, when the remote version is **unknown (offline)** or when the check is disabled.
* **Strict Escape Hatch Semantics:** `GITPR_SKIP_UPDATE_CHECK` is parsed against `_SKIP_TRUTHY_VALUES` (`1`, `true`, `yes`, `on`, `y`).
* **`check_and_update()`:** Only queries and **informs**, never installs.
* **Centralized Versioning:** `__version__` (**1.4.4** 🆕), `__lang_version__` (**v0.0.36** 🆕), `__scripts_version__` (**v0.0.4**), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`, `LINTER_PRESETS_VERSION`.
* **✅ Release state — two windows of debt closed:** `1.4.3` and `1.4.4` were tagged and published. **PyPI serves 1.4.4**, the `[1.4.3]` and `[1.4.4]` sections are committed in `CHANGELOG.md`, and the working tree is clean. Since 1.4.3 is exactly the gate fix, old installations that upgrade receive the 1-hour TTL in one step.
* **🆕 Pending revalidation:** with 1.4.4 published, the real gate behavior still needs to be observed on an old client (1.4.2 → 1.4.4), which is the scenario that originated the fix.

### **9. Interactive Chat Interface (`src/ui/chat_app.py`)**

* **Complete TUI:** Message history, multi-line input, status bar with visible bindings. `ENABLE_COMMAND_PALETTE = False`.
* **Per-Branch Memory (`src/chat_memory.py`):** History persisted per branch, with continuity between sessions.
* **Slash Commands:** `/explain`, `/tests`, `/optimize`, `/clear`. `/tests` (and the localized aliases) delegates to the **same use case** the CLI uses.
* **Auto-Patching (F5), Diff Refresh (F2), Session Export (F6).**
* **Shared Extractor:** F5 and `ctrl+s` call `patch_extractor.extract_code_blocks()` — identical visible behavior, with no duplicated logic.

### **10. Internationalization — i18n (`src/i18n.py`)**

* **Laravel-Inspired System:** `__()` function with named placeholders (`{count}`, `{file}`, etc.).
* **Automatic Detection:** Detects the OS language on first run and saves it to `GITPR_LANG`; the profile directory is created **before** persisting.
* **5 Languages, 6 Dictionaries:** en_us (default/fallback), pt_br, pt_pt, es/es_es, fr/fr_fr.
* **Versioned Files:** `__lang_version__` (**v0.0.36** 🆕) controls the update of the packages (`langs/*.json`).
* **Coverage — the debt grew in this window ❌:** **1620 keys** in `pt_br`, `pt_pt`, `es_es` and `fr_fr`, but **1618** in `es.json` and `fr.json` (unchanged). The code now uses **1629** keys: **11 do not exist in any dictionary** — the previous 8 (`publish_pull_request` messages and the descriptions of the `run_check`/`publish_pull_request` MCP tools) plus **3 new ones from the commit TUI** (`\n✅ Commit created successfully!\n`, `✅ Commit successfully created!` and `❌ Failed to commit: {error}`) — and **2 old keys remain orphaned**.
* **🆕 The Language Bump Brought No Translation:** `__lang_version__` went from v0.0.35 to **v0.0.36** without any `langs/*.json` changing. The practical effect is an OTA refresh that downloads exactly the same content — and the three new commit strings remain in English in all 5 languages.
* **Environment Snapshot (`AMBIENT_ENV_KEYS`):** `frozenset(os.environ)` captured **immediately before** the module-level `load_dotenv()`.
* **Cache with Language Indexing:** Cached AI responses include the current language in the MD5 keying.
* **Lockstep Translation Source:** a new key must exist in the code, in `langs/pt_br.json` (master list), in the FR/ES dicts of `scripts/sync_all_langs.py` and in the curated values of `scripts/fix_mangled_i18n_keys.py`.

### **11. Animated Spinner (`src/spinner.py`)**

* **Braille + Thinking Words:** Background thread during AI calls with braille characters and "thinking" words.
* **263 entries per language:** Synchronized across the 5 languages. `_load_thinking_words()` / `reload_thinking_words()` accept `force=`.
* **🆕 The Commit TUI Does Not Use the Spinner:** the TUI shows `CommitProgressScreen` with its own animated bar, and the AI call runs in a daemon `threading.Thread`. In `--hook` the classic spinner remains the only feedback.

### **12. AI Providers (`src/ai_providers.py`)**

* **3 Supported Providers:** Google Gemini, DeepSeek and Ollama (local), with a primary/secondary per provider.
* **JSON Mode & Deterministic Parameters:** `temperature=0.0` and `top_p=0.1`; automatic fallback between configured providers.
* **AI Timeout:** default of **180s** (`GITPR_AI_TIMEOUT`).
* **Calls with `quiet`:** release and the new features forward `quiet` down to the AI layer, so the spinner never corrupts a `--format json` output.
* **One Call at a Time, with Cache:** mentor and explain reuse the MD5 cache per prompt.
* **Already-Parsed Payload:** `parse_explain_payload()` and `generate_test_file` accept the **dict** the provider delivers, with a fallback to the raw JSON string (old cache responses).
* **🆕 Stdout Suppression in the Commit:** `_generate_commit_msg()` redirects `sys.stdout` to `os.devnull` during the call (the same pattern as `_with_suppressed_stdout`), restoring it in `finally` — a provider print does not corrupt the Textual screen.

### **13. Smart Cache (`src/cache.py`)**

* **MD5 + Metadata:** Keying by MD5 hash of the diff and prompt, with language indexing.
* **Last Review Selection (`resolve_last_review()`):** Picks the **newest** `review`/`fullreview` record for a repo+branch pair — it is the entry point of `gitpr fix` and of `gitpr mentor`.
* **Reviewed Diff (`reviewed_diff`):** Field at the top of the record that stores the diff that was actually reviewed.
* **Telemetry and Duration:** Persistence of `duration_ms` and `meta_raw`.
* **Dashboard Reading:** `scan_cache_files_for_dashboard()` is a **pre-migration bridge**.
* **Effect of Skill Sanitization:** since `get_skill_context()` removes the corrupted fragment from the PR prompt, the MD5 key changes and old contaminated responses are no longer reused.
* **`git config` Decoded Safely:** the `user.name`/`user.email` reads use `encoding="utf-8"` + `errors="replace"`.
* **🆕 The Commit Reuses the Cache:** the TUI calls `generate_pr_content("commit", "commit", diff_text, provider)`, so the same message for the same diff does not trigger a second AI call.

### **14. Issue Engine and TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Context Engines:** Current diff, branch history (`-ht`) and Blame archaeology (`-b`).
* **Multi-Forge Publication:** F3 creates the issue on the **configured** forge (`GITPR_SCM_PROVIDER`) via `provider.create_issue` — Azure DevOps raises `ScmNotSupportedError`.
* **Map-Reduce for Issues:** Context above ~90k tokens is split and unified.
* **401 Handling:** Reauthentication signaling without closing the application.
* **`git remote -v` Decoded Safely:** the call uses `encoding="utf-8"` + `errors="replace"`.

### **15. Code Archaeologist (`src/blame_engine.py`)**

* **Git Blame + AI:** Tracks evolution and historical authorship with commit classification (`ORIGIN` vs `REFACTORING`).
* **Blame Metrics:** Events via `log_blame_metric()` with depth and number of commits analyzed.
* **It now also feeds risk:** the score's history reader (`risk_history_reader.py`) uses the same 90-day window and classifies bugs/reverts by pattern, without calling the AI.

### **16. MCP Server and Direct CLI Invocation (`src/mcp_server.py`)**

* **18 Annotated MCP Tools:** `get_git_context`, `analyze_diff`, `list_unstaged_files`, `analyze_unstaged_diff`, `get_full_diff`, `generate_commit_message`, `review_code`, `full_review`, `generate_pr_description`, `run_linter`, `analyze_blame`, `generate_issue`, `list_fix_candidates`, `review_remote_pr`, `get_baseline_status`, `get_usage_metrics`, `run_check` and `publish_pull_request`.
* **Opt-In Parameters in the Contract:** `review_code` and `full_review` accept `mentor: bool = False`; `generate_pr_description` accepts `explain: bool = False`. They are **purely per parameter** — they never OR with the `.env` defaults.
* **19 Resources + 7 Templated Prompts:** `skill://list` + `skill://{pr,commit,review,filereview,issue,blame,release,fix}` + `linter://config` + `baseline://summary` + `prompt://list` + 7 prompts.
* **Aligned Skill Registry:** `mcp_server.SKILL_FILES` has **11 entries** (`tests`, `explain` and `mentor` included); `TestSkillRegistryAgreement` passes.
* **Direct CLI Invocation:** `gitpr-mcp --tool <name> [--tool-args '<json>']` invokes any tool without starting the stdio server; `gitpr-mcp --list` prints the full registry as JSON.
* **Real Stdout Isolation:** `_write_real_stdout()` writes to the original `sys.__stdout__`, with a fallback to `buffer` or re-encoding with `errors='replace'` on legacy Windows code pages.
* **Event Loop Offload:** the `_offload` decorator (`anyio.to_thread.run_sync`) on every tool — decorator order matters (`@mcp.tool` **above** `@_offload`).
* **Failures as Structured Errors:** the new tools return failure **as data** instead of raising.
* **MCP Metrics:** `run_check` and `publish_pull_request` write local rows (`mcp:check`, `mcp:publish_pr`).
* **The MCP server is never blocked by the gate:** `--mcp`/`gitpr-mcp` has been on the exemption list since the gate's first version.

### **17. Metrics Dashboard TUI (`src/ui/metrics_app.py`)**

* **Repository Scope:** `📁 Repository: owner/repo` label and strict filtering by project.
* **Asynchronous Scan with Overlay:** background worker thread with `ProgressBar`; the tests await `workers.wait_for_complete()` before asserting.
* **Ledger Reading:** the dashboard queries `telemetry.db` on the worker thread, rendering the five sections (cost, quality, modules, providers, cycle) from the same renderers the CLI uses.
* **Migration Assistant (`src/ui/metrics_migration_app.py`):** offers to import the legacy files, import **and** rebuild from the AI cache, or "not now".
* **Local Export:** CSV/JSON in `./.gitpr/metrics/export/` — `.gitpr/metrics/` is in `.gitignore`, but the **34 artifacts already tracked remain tracked** (gitignore does not untrack).

### **18. Metrics and Telemetry System (`src/metrics.py`)**

* **Per-Repository Scope:** All events indexed by `repo_name`, resolved by `src/infrastructure/git/identity.py`.
* **Hook, Linter and Blame Events:** `log_hook_event()`, `log_linter_metric()`, `log_blame_metric()`. `gitpr check` writes `command="check"` with `findings_total`/`findings_errors` and the exit code.
* **Export and Cleanup:** `gitpr metrics export` (CSV/JSON), `prune`, `purge` with interactive confirmation, `bundle`/`merge` to transport and merge windows.
* **🆕 The Interactive Commit Also Measures:** the `commit` path keeps writing the command line with `meta` and `modules`; the TUI did not create a new metric type.

### **19. Git Hooks Language Synchronization**

* **Independent Versioning:** `__scripts_version__` (**v0.0.4**, unchanged).
* **Templates rewritten in the v0.0.17 window:** `post-checkout`, `post-merge` and `pre-push` call `gitpr --quiet metrics hook-event <name>`, guarded by `command -v gitpr` and `|| true`.
* **Suffix Mapping (`HOOK_SCRIPT_SUFFIXES`):** UI codes (`es_es`, `fr_fr`) translated to the published suffixes (`.es`, `.fr`).
* **Choice vs. State (`SCRIPTS_LANG` / `SCRIPTS_INSTALLED_LANG`):** Allows **detecting a language switch**.
* **`effective_hook_lang()`:** Resolves the effective language; `--lang` is no longer discarded on that path.
* **Merge-Source Skip:** `prepare-commit-msg` skips the `message|merge|squash|commit` sources.
* **🆕 `prepare-commit-msg` Remains Non-Interactive:** the hook runs with `--hook`, so it **never** opens `CommitApp` — the TUI exists only in a human's `gitpr -c`.
* **The hooks run with `--quiet` and `--hook`, so they are never blocked by the update gate.**

### **20. External Linter Bridge and Interactive Assistant (`src/linter_wizard.py`, `src/ui/linter_app.py`)**

* **`--linter-setup` Wizard:** Wizard with numbered presets (PHP_CodeSniffer, ESLint, Stylelint) and injection of the `external_linters` block.
* **Remote Presets:** `templates/gitpr.linter-presets.json` with a local → download → stale → embedded fallback chain.
* **Linter Error TUI:** `src/ui/linter_app.py` (Textual) displays critical errors and warnings; in hook/quiet mode it prints and calls `sys.exit(1)`.
* **Markdown Report:** Consolidated in `.gitpr/reports/linter/` only when there are violations.
* **The SAST Layer is a Sibling, Not a Replacement:** the Semgrep/Gitleaks/Bandit bridges feed the **same** report pipeline — and now also `gitpr check`.

### **21. Multi-Forge SCM (`src/infrastructure/scm/`)**

* **Single Abstraction (`ScmProvider` ABC):** `base.py` defines the contract (dataclasses `RepoRef`, `PullRequestDraft` etc. and `ScmProviderError(provider, http_status, message)` — `http_status` 0 = network failure).
* **Registry and Factory:** `resolve_scm_provider()` selects by `GITPR_SCM_PROVIDER` (default `github`); `detect_provider_from_remote()` identifies the forge from the origin URL.
* **`list_pull_requests(repo, state, since, until)` — paginated and canonical:** the four providers translate the canonical states (`open`/`merged`/`closed`/`all`) into the native vocabulary, paginate on each API's cursor and filter by an inclusive date window.
* **Why the filter is by state date:** three of the four forges return something broader for a merged PR, so each filters its own response against the date the state implies.
* **`supports_merged_dates`:** class attribute, `False` in Bitbucket Cloud, which publishes **empty** `merged_at`.
* **`web_links.py`:** pure functions (`repo_web_base`, `commit_url`, `pull_request_url`, `user_url`) normalize any remote format.
* **`get_pull_request(repo, pr_id)` — concrete ABC method:** the default raises `ScmNotSupportedError` and each forge implements it.
* **Synthesized GitLab Headers:** `changes[].diff` is a loose hunk; `old_path`/`new_path` build the `diff --git a/… / --- / +++` headers, honoring `/dev/null`. And `overflow: true` **raises** instead of reviewing half a truncated diff.
* **`request_pull_request_reviewers` returns `list[str]` (contract break):** It returns the logins **effectively attached**, read from the `201` body. Third-party subclasses need to be updated — **inherited item, still undocumented.**
* **GitHub Read-Only Helpers:** `get_commit_author_login` and `get_user_login`.
* **Per-Forge Fail-Fast:** Azure requires `GITPR_SCM_ORGANIZATION`/`GITPR_SCM_PROJECT`; Bitbucket requires `GITPR_SCM_USERNAME`; `create_issue` on Azure raises `ScmNotSupportedError`.
* **Release Publication:** `provider.create_release()` used by `gitpr release --publish`.
* **Shared PR Publication:** `src/application/use_cases/publish_pull_request.py`, used by `--no-edit` and by the MCP tool. It validates title/body, **requires `provider.token` only when `check_remote_state=True`**, optionally checks for an existing PR and an unpushed branch, masks error messages and returns `PublishPrOutcome`. The result's `url`/`number` fields are read with defensive `getattr`.
* **Artifacts:** Glossary + ADR-001/ADR-005; the `docs/scm-multiforge.*.md` family in 5 languages; 9 test files, **307 scenarios**.

### **22. `gitpr release` Subcommand — Changelog / Release Notes (`src/release_engine.py`)**

* **Flow:** `git log` between `--since` and `HEAD` → Conventional Commits classification → suggested semantic bump → changelog assembly → optional AI executive summary → *prepend* to `CHANGELOG.md`.
* **The Range No Longer Comes from `git describe`:** the range origin is the **previous section of the changelog itself**, via two anchors: the newest hash of that section and, as a fallback, the version tag of the section.
* **`_drop_released()` — the second line of defense:** any commit whose short hash appears in **another** version section is discarded, with a count.
* **Every bullet became a link:** `- {subject} ([{short_hash}]({commit_url})) — {scope} · [#{n}]({pr_url}) · {YYYY-MM-DD}`.
* **The merge-commit PR failure:** a second pass (`git log --merges`) builds `hash → PR` by ancestry. 🆕 The `[1.4.4]` section, generated by this flow, **came out without `[#n]`** — the release's only commit (`9bd2038`) was PR #228 itself and no merge inside the range referenced it.
* **Contributors resolved by the same reviewer ladder:** `get_commit_author_login` first, then `email_to_handle`, cached in `~/.gitpr/cache/contributors.json`.
* **The Section Boundary Was a Latent Bug:** `upsert_changelog(force=True)` replaces from the `## [x.y.z]` line up to the next **level 2** heading.
* **Classifier (`src/commit_classifier.py`)** and **Builder with Translatable Sections (`src/changelog_builder.py`)**.
* **Semantic Bump (`src/version_bump.py`)** and **Publication** (`--publish`, `--draft`, `--format markdown|json`, `--force`). **6 options on the subcommand.**
* **✅ Release state:** the `## [1.4.3] - 2026-10-27` and `## [1.4.4] - 2026-10-29` sections are committed; tags `v1.4.3` and `v1.4.4` exist; PyPI serves **1.4.4**. The artifact in `.gitpr/reports/release/` was generated in PT-BR.
* **The subcommand goes through the update gate** when run in an interactive terminal.
* **Artifacts:** `docs/release-notes.*.md` in 5 languages, ADR and `tests/test_web_links.py`.

### **23. `gitpr config` Subcommand — Configuration TUI**

* **Master-Detail Screen (`src/ui/config_app.py`):** Categories on the left, fields on the right, edited inline. Header with search (`/`) and a pending-changes counter; footer `F1 Help · F2 Save · ^R Restore · / Search · Esc`. `ENABLE_COMMAND_PALETTE = False`.
* **Declarative Schema (`src/config_schema.py`) — the single source of truth:** **16 categories** and **80 `ConfigField`**, of which **8 are advanced**. The 9 `GITPR_CHECK_*` are still **not** in the schema.
* **`policy` and `baseline` Categories:** the pack fields and the four baseline ones.
* **Aligned Skills Section:** `SKILL_LABELS` has **11 entries** (`tests`, `explain`, `mentor`).
* **Context Filtering (`show_if`):** `GEMINI_*`/`DEEPSEEK_*`/`OLLAMA_*` according to `DEFAULT_AI_PROVIDER`; `GITPR_SCM_USERNAME` under `[Bitbucket]`; `GITPR_SCM_ORGANIZATION`/`PROJECT` under `[Azure DevOps]`.
* **Global Search (`/`):** Matches key or label across all categories and **ignores the visibility filter**.
* **Two-Layer Validation:** **Offline** blocks `F2` with an inline error; **online** (only credentials changed in the session) runs on a worker with a 10s timeout and only blocks on `401`/`403`.
* **Restore (`Ctrl+R`):** Removes the `.env` line instead of rewriting the default; `Esc` with pending changes asks for confirmation.
* **Skills Section — the only project-scoped one:** Edits the project's `.gitpr/skill/*.md` resolved from the invocation directory, writing atomically and preserving CRLF/LF.
* **Forced Downloads:** Buttons that force re-download of smart-excludes, translations, presets and thinking words via `force=`.
* **Lightweight Links Module (`src/doc_links.py`):** `doc_url()` moved out of `core.py` so the UI can get the link without importing AI SDKs.
* **Tests:** 5 files — `test_config_app.py` (78), `test_config_schema.py` (42), `test_config_validation.py` (40), `test_config_store.py` (22), `test_config_cli.py` (9).
* **Known debt:** `gitpr -h config` opens the TUI and ignores `-h` — the gate `if ctx.invoked_subcommand is not None: return` (main.py) runs before the `help_flag` block. The update gate now also lives at that point, but with `not help_flag` and the raw `argv` scan as a guard.

### **24. General Usage Log (`src/usage_log.py`)**

* **One Line per Command:** Writes `~/.gitpr/logs/<uuid5>.log`, **one file per day**, with command, arguments, repository, user and timestamp.
* **Date-Derived Name:** `uuid5(NAMESPACE_DNS, f"gitpr.usage.{YYYY-MM-DD}")`.
* **Synchronous Write (explicit decision):** Unlike `log_local_metric`, which used a daemon thread and lost the write if the process exited first.
* **Never Prints:** The MCP server reserves stdout for JSON-RPC; the module also never raises an exception.
* **A Single Git Spawn:** `git config --get-regexp '^(remote\.origin\.url|user\.name|user\.email)$'` — ~40 ms instead of ~150 ms on Windows, on *every* execution.
* **Control:** `GITPR_SHOW_LOGS` (default `"true"`); disabled in `tests/conftest.py`.
* **Artifacts:** `docs/usage-log.*.md` in 5 languages; `tests/test_usage_log.py` (27 scenarios).

### **25. `gitpr fix` Subcommand — Review Findings as Reviewable Patches (`src/fix/`)**

* **The Real Pipeline:** last review from the cache (`resolve_last_review`) → **one** AI call → extraction and validation of the unified diff → `git apply --check` → deterministic classification → dry run or write → history.
* **8-file package:** `patch_provenance.py`, `patch_extractor.py` (shared with the chat), `patch_safety_classifier.py` (pure, no I/O), `patch_applier.py` (shim), `fix_history.py`, `apply_fix.py`, `rollback_fix.py`, `__init__.py`.
* **Deterministic Classification:** Rejects a patch that spans more than one file or hunk, touches a sensitive path, exceeds the line budget, **deletes a line that looks like a call**, is marked low-confidence or fails `git apply --check`. `--force` **never** bypasses applicability, only the classification.
* **Opt-In Write:** Dry run is the default; any mutation requires `--apply` (or a typed phrase with `--force`).
* **Tests:** 10 files in `tests/fix/` (**204 scenarios**) with real git repository fixtures.

### **26. `gitpr review-pr` Subcommand — Remote PR Review (`src/review/`)**

* **What it is:** Orchestration, **not** a second review engine — the existing engine, linter and renderer fed with a diff that came from elsewhere.
* **5-file package:** `diff_source.py`, `diff_normalizer.py`, `render.py`, `remote_pr.py`, `__init__.py`.
* **Read-Only by Default:** `--post-comment` is the **only** path that writes to the forge. The MCP tool `review_remote_pr` does not even receive the argument.
* **The baseline is resolved before the first network call**, and the gate warnings are merged into the report/comment.
* **`render.py` gained the risk parameter:** `compose_review_content` / `render_review_result` **prepend** the `⚡ Risk Assessment` section above the linter alerts when `GITPR_RISK_INCLUDE_IN_REVIEW` is enabled.
* **Artifacts:** `docs/review-pr.md` + `.pt_br.md` (the remaining 3 languages **are still pending**), `docs/code-review-ia.*.md` (5), ADR-005 and `glossary-review-pr.md`.
* **Tests:** 4 files in `tests/review/` (**97 scenarios**).

### **27. Reviewer Identity Resolution (`src/reviewer_resolution.py`)**

* **The Bug (two chained silent failures):** (1) **Empty prefill** — `handles` only with `email_to_handle()`; (2) **Unverified attach** — values passed through *verbatim*, GitHub answered **201 without attaching anyone** and `_request()` returned without raising.
* **Module:** Pure, with no I/O of its own, **that never raises**. `resolve_candidates()` (before the TUI) and `resolve_typed_reviewers()` (on attach); `match_candidate()` matches exactly and normalized by login, name or email.
* **Resolution Ladder:** known handle (no request) → exact match with a suggested person → email lookup → login validation on the forge.
* **What does not resolve is never sent:** It comes out in `ResolutionOutcome.dropped` as `(value, reason_i18n)` and is discarded with a visible warning.
* **The Same Ladder Serves the Release:** `release_engine` reuses `get_commit_author_login` + `email_to_handle`.
* **Tests:** `tests/test_reviewer_resolution.py` (18) + expansions in `test_github_provider.py` (72), `test_pr_publish_app.py` (55), `test_suggest_reviewers.py` (17), `test_reviewer_suggestion.py` (18) and `test_main_suggest_reviewers.py` (8).

### **28. `gitpr demo` Subcommand — Guided Tour over Recorded Examples (`src/demo/`)**

* **What it is:** An interactive tour that shows GitPR's three main outputs — commit message, review and PR description — **without an API key, without a git repository and without a network**.
* **Real Pipeline, Fake Response:** `FakeAIProvider` mirrors `src.ai_providers.call_ai_model` **argument by argument**; `demo_pipeline()` patches `src.core` and `src.metrics` so the production pipeline runs **untouched**.
* **Isolation is a Requirement, Not a Detail:** The tour **cannot** touch the user's `~/.gitpr` or the network — guaranteed by `tests/demo/conftest.py`.
* **Package:** `demo_runner.py`, `fake_ai_provider.py`, `scenarios/` (two examples localized in the 5 languages). The scenarios are **Python modules, not JSON**, so they travel inside the wheel.
* **TUI:** `src/ui/demo/` with a help modal; the diff is fenced as ```` ```diff ````.
* **CLI:** `gitpr demo` with `--scenario`, `--lang` and `--no-tui`.
* **Tests:** 6 files in `tests/demo/` (**155 scenarios**).
* **Artifacts:** `docs/demo.*.md` in 5 languages, `README.*` (5) and the tour's example metrics.

### **29. GitPR Badge and the `gitpr badge` Command (`src/branding/`)**

* **What it is:** A public badge attesting that a pull request went through a local quality check, plus a command that prints the adoption snippet for the project's README.
* **Static URL on Purpose:** It is a shields.io Markdown that GitPR **never** fetches — publishing a pull request cannot start depending on a third party being online.
* **Honest Measurement (`badge_data.py`):** `collect_linter_counts()` returns `None` when there are no linter rules configured, because an empty list means "nothing was checked", not "nothing was found".
* **The Badge Never Blocks a Publish:** Every failure path is caught and degrades to "no badge".
* **Single Injection Point:** `attach_pr_badge()` in `src/main.py`. Idempotent append by marker.
* **`gitpr badge` Command:** `--readme` and `--style`. **It only prints** — the README is never modified.
* **Configuration:** `GITPR_BADGE` (default `true`) — **read-only** opt-out.
* **Tests:** 7 files in `tests/badge/` (**83 scenarios**), all green since the v0.0.20 window.
* **Artifacts:** `docs/badge.*.md` in 5 languages and the dedicated survey.

### **30. `gitpr split` Subcommand — Atomic Commits by Hunk (`src/split/`)**

* **What it is:** A working tree with several concerns stops becoming a commit blob. `split` reads the uncommitted diff, asks the AI to partition the hunks by **logical intent** and proposes one atomic commit per concern. The tree is never rewritten.
* **6-file package:** `split_plan.py`, `hunk_parser.py` (pure), `hunk_grouper.py`, `generate_split_plan.py` (read-only), `apply_split_plan.py` (**the only one that mutates**) and `__init__.py`.
* **The Intact-Tree Guarantee is Structural:** `selective_stager.py` pre-validates **every** group against a temporary index at HEAD and uses a disposable `GIT_INDEX_FILE`.
* **Destructive to the Index, Not the Tree:** `--apply` resets the index to HEAD before staging.
* **Configuration:** `GITPR_SPLIT_MAX_GROUPS` (5), `GITPR_SPLIT_MAX_HUNKS` (50) and `GITPR_SPLIT_REQUIRE_CONFIRMATION` (`true`). Zero or negative values are **ignored in favor of the default**.
* **🆕 Relationship with the Commit TUI:** `split` and `-c` attack the same problem at different heights — `split` proposes **several** commits from the tree; `CommitApp` organizes **one** commit from the index.
* **Tests:** 6 files in `tests/split/` (**101 scenarios**).
* **Artifacts:** `docs/split-command.md` in 5 languages, ADR-006, `glossary-gitpr-split.md`, spec/plan and survey.

### **31. Built-in Secrets Scanning (`src/security_ruleset.py`)**

* **What it is:** Seven rules that run on **every** linter invocation, merged at the end of `load_linter_rules()` after the project rules and the plugins.
* **The Seven Rules:** five blocking `error`s — AWS access key ID, GitHub token, Slack token, Google API key and private key block — and two `warning`s: database connection URL with credentials and generic credential assignment, the latter behind a placeholder filter (`changeme`, `xxxxxx`, `example`, `dummy`, `sample`, `your_password_here`, `sua_senha`).
* **Why in the Package and Not in the Template:** A secrets gate must behave **the same** on every machine and in every CI.
* **Coverage Gaps (v1) — confirmed still open:** **unquoted** assignment is not caught (`API_KEY=abc123`); the prefixes `ASIA…`, `github_pat_…` and `xoxc-`/`xoxd-` are missing; and there is no left boundary in the key name.
* **Configuration:** `GITPR_LINTER_SECURITY` (default `true`; fail-open opt-out) and `GITPR_LINTER_SECURITY_DISABLED_RULES` (`;`-separated).
* **Tests:** `tests/test_security_ruleset.py` (**61 scenarios**).
* **Artifacts:** ADR-007, `glossary-gitpr-secret-scanning.md`, spec/plan/survey and 3 task reports.

### **32. SAST Bridges — Semgrep, Gitleaks and Bandit (`src/infrastructure/linter/external/`)**

* **What it is:** An **opt-in** layer that plugs third-party security scanners into the existing linter pipeline.
* **Bounded Scope:** They run **only** when enabled and **only** over the files touched by the diff.
* **Common Contract:** `ExternalLinterBridge` (ABC) with hardened `subprocess` execution (`shell=False`, strict timeout, `stdin=DEVNULL`, UTF-8 with `errors='replace'`).
* **Deduplication is the Point:** `deduplicate_secret_findings()` merges findings from Gitleaks and the regex ruleset in the same file and line into a **single** confirmed entry `[Gitleaks + Regex]`.
* **Masked Secrets:** `mask_secret_value()` guarantees the value never reaches the finding, the log or the telemetry.
* **Three-Layer Resolution:** `load_sast_config()` reads defaults → `.gitpr.linter.yml` (`sast` block, falling back to `linter.external`) → `GITPR_SAST_*` variables. **All default to `false`**.
* **Graceful Degradation:** A tool enabled but absent from `PATH` emits a warning and execution continues.
* **Tests:** 4 files in `tests/infrastructure/linter/external/` (**14 scenarios**) + `tests/domain/linter/test_sast_finding_mapper.py` (2) + `tests/domain/linter/test_structured_findings.py` (44).

### **33. `gitpr tests generate` Subcommand — AI Suite Generation (`src/domain/tests_generation/`, `src/application/`)**

* **What it is:** Generates complete, runnable test files from the current diff, a specific file or a review finding, **respecting the repository's convention** (Pest, PHPUnit, Jest, Vitest, Pytest).
* **Domain Layer:** `TestFramework` (enum) and the `TestGenerationTarget`, `TestScaffold`, `GeneratedTest` dataclasses; `detect_test_framework()` detects from configuration files, manifests and test-directory contents; `build_test_scaffold()` computes the conventional destination path per framework.
* **Application Layer (`generate_test_file.py`):** Orchestrates detection, scaffold resolution, prompt, AI, JSON parsing and optional write. `validate_test_syntax()` runs the local toolchain (`php -l`, `node --check`, `python -m py_compile`) — a validation failure is a **warning**, not an error.
* **Presentation:** `tests` group with `generate` (`--file`, `--finding`, `--framework`, `--apply`, `--provider`); dry run is the default and overwriting an existing test asks for confirmation with **No** pre-selected.
* **Documented in 5 languages:** `docs/tests-generate.*.md` (5 files), with a link in `gitpr tests generate` (epilog) and in the README.
* **Two Defects Fixed (v0.0.19 window):** the use case accepts the provider's already-parsed **dict**, and `--finding` uses `cand.suggested_test`/`cand.diff_unified`.
* **Skill:** `.gitpr.tests.md` in `SKILL_LABELS`, `mcp_server.SKILL_FILES` and `SKILL_FILES_BY_TYPE`.

### **34. `gitpr explain` Subcommand and the `--explain` Flag — Reviewer Guide (`src/domain/pr/`)**

* **What it is:** A guide centered on whoever is going to **review** — what changes, why it changes, where to focus and what the regression risk is.
* **Domain Layer (`explain_section_builder.py`):** `PrExplanation` / `ReviewerFocusPoint`, the `build_explain_markdown()` renderer and `parse_explain_payload()`, which tolerates non-JSON output and **detects placeholders** `[FILL]`/`[TODO]`.
* **`parse_explain_payload()` Accepts `dict | str | None`:** the provider delivers the JSON **already parsed**; the function accepts all three forms and normalizes `None` fields.
* **Missing Key Branch:** `generate_pr_explanation` prints the standard missing-key error and exits with **1**.
* **Application Layer (`generate_pr_explanation.py`):** Provider resolution, key validation, skill context loading (`explain`), prompt, invocation and parsing.
* **Two Entry Points:** `gitpr explain` (`--provider`, missing-diff detection before any AI) and the `--explain` flag on the root CLI. The extension exposes the same path in `GitPR: Generate PR Draft (with Reviewer Guide)`.
* **`-h --explain` Fixed:** the option used dest `explain_flag`, but the `HELP_MAP` looked for `explain` — contextual help renders again.
* **Documented in 5 languages:** `docs/explain-pr.*.md` (5 files), with a link in `HELP_MAP["explain"]`, in the command epilog and in the README.
* **Configuration:** `GITPR_EXPLAIN_BY_DEFAULT` (default `false`).

### **35. Deterministic Suite and CI (`.github/workflows/tests.yml`)**

* **What it is:** The workflow that runs the suite on **Python 3.10** (the floor declared in `pyproject.toml`) and **3.13**, with `fail-fast: false`.
* **Hermetic `tests/conftest.py`:** Pins `GITPR_LANG=en_us`, `GITPR_LINTER_SECURITY=false` and `LANG_VERSION` to the code version, plus `GITPR_SHOW_LOGS=false` and `GITPR_SKIP_UPDATE_CHECK=true`.
* **Order Matters:** `src.updater` is imported **before** the language variables are defined.
* **Worker Test Stability:** `test_config_app.py` and `test_metrics.py` await `workers.wait_for_complete()` before asserting.
* **Workflow Dependency:** The clean runner has no `~/.gitpr`, and `test_demo_isolation.py` asserts that the profile exists — the job creates the directory and the empty `.env` explicitly.
* **🆕 In this window the suite gained 14 scenarios and 1 module:** `tests/test_commit_app.py` covers every state transition of `CommitApp` (mount with and without files outside the index, auto-stage, skip, cancel, confirm, commit failure and success, `_schedule_auto_exit`) plus a **complete headless flow** with the `run_test()` pilot. **Total: 3010 scenarios in 141 files.**
* **Extension Workflow:** `.github/workflows/extension.yml` runs `npm install`, builds, tests and packages the VSIX; on `ext-v*` tags it publishes to the VS Code Marketplace and Open VSX, skipping each step when `VSCE_PAT`/`OVSX_PAT` is empty.

### **36. SQLite Telemetry Ledger (`src/ledger.py`, `src/infrastructure/git/identity.py`)**

* **The Problem Was Not the Format, It Was the Join:** events were written as JSON files per command, from a **daemon thread**, and then reconnected to the AI cache by a heuristic match of `(repo, branch, action, minute)`.
* **The Write Is Synchronous, and That Is the Decision:** `log_command_metric()` resolves the git context **once per process** and writes the row immediately.
* **Versioned Schema:** `SCHEMA_VERSION` + `PRAGMA user_version`, UUID keys, WAL + `synchronous=FULL`, and `LedgerVersionError` to refuse a newer bundle **before** `ATTACH`.
* **"Never Born Empty" Invariant:** `ensure_ledger()` only creates the database **together** with the absorption of legacy JSON.
* **One Window, One Meaning:** `--since`/`--until`/`--days` narrow **every** read surface; the cycle section assumes the last 30 days and **names the window**.
* **Each Metric is Computed Once and Rendered Once:** the `(style, text)` pairs make the CLI and the Textual dashboard tell the **same** story.
* **Cost with Embedded Table and Override:** list prices of the four models, overridden by `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT`. **Both rates are mandatory**.
* **Normalized `modules`:** paths become two segments (`src/fix`), `(root)` and `(no module)`.
* **Bundle Transport:** `bundle` writes the window as a standalone `.db`; `merge` attaches it and inserts by UUID.
* **`cache_backfill` is Left Out:** rows rebuilt from the cache are excluded from the execution aggregates.
* **Git Identity without a Forge (`identity.py`):** repo label, branch and author in a **single** subprocess call.
* **The Cycle Section Is the Only One the Ledger Cannot Answer:** lists merged PRs via `ScmProvider.list_pull_requests`; every failure degrades to a single line — `Not read: <reason>`.
* **Complete Presentation:** `gitpr metrics` group with 8 subcommands, a dashboard reading the ledger on a worker thread and the three-option migration assistant.
* **Tests:** `tests/test_metrics.py` (**164 scenarios**).

### **37. `gitpr mentor` Subcommand and the `--mentor` Flag — Mentor Mode (`src/domain/mentor/`)**

* **What it is:** A review says *what* is wrong and almost never *why* that matters. Mentor Mode enriches findings with **what is happening**, **why it matters**, an everyday **analogy** and the **concepts to study**.
* **Pure Domain Layer (`mentor_explanation_builder.py`):** no I/O, converts the AI payload into a validated `MentorExplanation` and enforces **epistemic honesty**: without enough evidence, the explanation **says so**; hallucinated URLs are **removed**; unknown IDs are **rejected**.
* **Application Layer (`generate_mentor_explanation.py`):** collects candidates, **batches of at most 10 findings**, ordered by severity, reusing the MD5 cache per prompt hash.
* **Two Entry Points:** `gitpr mentor [--finding <id>] [--provider <name>]` and the `--mentor` flag, which appends the section to the regular review.
* **Strict Opt-In:** `GITPR_REVIEW_MENTOR_MODE` (default `false`) and `GITPR_MENTOR_INCLUDE_ANALOGY` (default `true`), editable in the configuration TUI.
* **Skill and Registry:** `.gitpr.mentor.md` registered in `SKILL_FILES_BY_TYPE`, in `mcp_server.SKILL_FILES` and in `SKILL_LABELS`, with templates in **5 languages**.
* **Tests:** `tests/domain/mentor/test_mentor_explanation_builder.py` (8), `tests/application/use_cases/test_generate_mentor_explanation.py` (5) and `tests/test_mentor_cli.py` (4) — **2 of these fail** due to the inherited isolation bug.
* **Artifacts:** `docs/mentor-mode.*.md` in **5 languages**, ADR-009, `glossary-gitpr-mentor.md`, spec and task reports.

### **38. `gitpr risk` Subcommand — Deterministic Risk Score (`src/domain/risk/`)**

* **What it is:** A score **0–100** with level `LOW`/`MEDIUM`/`HIGH`/`CRITICAL`, computed **without AI** from local signals.
* **Pure Domain Layer (`risk_types.py`, `risk_rules.py`, `risk_calculator.py`, `risk_explanation.py`):** enums and dataclasses; weights, thresholds, critical path patterns, glob matching and test/non-executable detection loaded from YAML; per-file calculation with **clamp 0–100** and 50/30/20 aggregation.
* **The Formula Is Declared, Not Learned:** **50%** the highest-risk file, **30%** the line-weighted average and **20%** critical evidence.
* **Infrastructure Adapters:** `risk_history_reader.py` reads `git log` and extracts commits, bug fixes and reverts over a **90-day** window; `test_matcher.py` matches test ↔ production files by convention.
* **Use Case (`calculate_risk.py`):** correlates diff sections, findings, test matching and history into `FileRisk`/`PullRequestRisk`, **degrading with a warning** when a signal is unavailable.
* **Three Consumers:** the `gitpr risk` command, the `⚡ Risk Assessment` section appended to reviews and to remote PR review, and the **AI prompt** as **read-only** context. `gitpr check` uses the same `execute_calculate_risk`.
* **Opt-Out:** `GITPR_RISK_INCLUDE_IN_REVIEW` (default `true`).
* **Per-Project Configuration:** `templates/gitpr.risk.yml` (or `.gitpr/skill/gitpr.risk.yml`) overrides weights, thresholds and critical paths.
* **Tests:** `tests/domain/risk/` (2 files, 10), `tests/application/use_cases/test_calculate_risk.py` (5), `tests/infrastructure/git/test_risk_history_reader.py` (4), `tests/infrastructure/git/test_test_matcher.py` (5) and `tests/test_risk_cli.py` (4).
* **Artifacts:** `docs/risk-scoring.*.md` in **5 languages**, ADR-010, `glossary-gitpr-risk-scoring.md` and task reports.

### **39. `gitpr policy` Subcommand — Versioned Policy Packs (`src/domain/policy/`, `src/infrastructure/policy/`)**

* **What it is:** A **versioned YAML pack** that carries, in a single artifact, a repository's quality decisions: skills, linter rules, severities, critical paths, risk weights and PR/commit conventions.
* **Domain Layer (`policy_types.py`, `policy_manifest.py`, `policy_checksum.py`, `policy_compatibility.py`, `policy_resolver.py`):** manifest parsing with a **closed schema**, compatibility and credential checks, **SHA-256 checksum** and a graph resolver with precedence and composition.
* **Infrastructure Layer:** `local_policy_repository.py` resolves the pack from **three origins** (`.gitpr/policies`, `~/.gitpr/policies` and the embedded package) and `policy_pack_loader.py` loads it.
* **Use Cases:** `activate_policy_pack`, `install_policy_pack`, `resolve_effective_policy` and `validate_policy_pack`.
* **Four Embedded Packs:** `laravel-quality`, `node-quality`, `php-security` and `vue-quality`, each with `policy.yml` and `linter.yml`, distributed via **`package-data`**.
* **Integrated Consumption at Four Points:** `calculate_risk`, `config.load_linter_rules`, `core.get_skill_context` and the **PR cache key** (`cache_scope`). `gitpr check` resolves the pack **before everything else** and exits with code 2 if it is invalid.
* **CLI:** `gitpr policy {list, validate, show, use, off, install, init}`, with confirmation (`--yes`/`--force`), stack detection in `init` and the choice recorded in `.gitpr/policy.lock.yml`.
* **Configuration:** `GITPR_POLICY_ENABLED` and `GITPR_POLICY_CONTEXT_MAX_CHARACTERS`.
* **Backward Compatibility:** with no active pack, behavior is **preserved** — covered by an integration test.
* **Dependency:** `packaging>=23.0` (SemVer/PEP 440).
* **Tests:** `tests/domain/policy/` (4 files, 126), `tests/application/use_cases/` (4 policy files, 89), `tests/test_policy_cli.py` (26) and `tests/integration/test_policy_effect_on_review_linter_risk.py` (31).
* **Artifacts:** `docs/policy-packs.*.md` in **5 languages**, ADR-011, `glossary-policy-packs.md`, spec/plan/survey.

### **40. `gitpr baseline` Subcommand — Baseline and Auditable Suppressions (`src/domain/baseline/`)**

* **What it is:** Linters and reviews re-report the same pre-existing findings on every run. The baseline records the **snapshot of the findings the repository already knows about** in `.gitpr/baseline.json`, fingerprints each one stably across runs and lets the pipeline classify what is new — **only `new` blocks**.
* **Domain Layer (`baseline_fingerprint.py`, `baseline_types.py`, `baseline_manifest.py`, `baseline_comparator.py`, `suppression_policy.py`):**
  * **Stable fingerprint:** rule identity, normalized path, lines and **sha256 of the line with collapsed whitespace** — the raw line is **never** stored.
  * **Canonical manifest:** canonical JSON with checksum, validation that refuses an unknown key, an unsupported schema/fingerprint version, a broken suppression/debt invariant, a duplicate fingerprint and an invalid line range.
  * **Comparator:** classification, counts by status and entry resolution — `resolved` and then `back` reads as **new**, the conservative reading.
  * **Suppression Policy:** suppressions, accepted debt, overrides and validators, with layered matching by **specificity and precedence**, plus overdue-debt tracking.
* **Five Use Cases:** `baseline_gate`, `baseline_summary`, `compare_against_baseline`, `create_baseline` and `suppress_finding`.
* **Policy Pack Integration:** the pack can carry a `baseline:` block, validated by the **same** validators.
* **The Repository Is the Only Writer:** `local_baseline_repository.py` is the **only** reader/writer of `.gitpr/baseline.json` and `.gitpr/baseline.overrides.yml`.
* **Configuration:** `GITPR_BASELINE_ENABLED`, `GITPR_BASELINE_PATH`, `GITPR_BASELINE_REQUIRE_LOCKFILE_CHECKSUM_MATCH` and `GITPR_BASELINE_ALLOW_LOCAL_OVERRIDES`.
* **Opt-In and No-Op:** disabled or without an existing baseline, the feature **reads no file and prints nothing**.
* **⚠️ The Deliberate Exception:** a baseline file that exists but **cannot be applied** makes lint, review, full review, file audit, remote review and risk **abort with exit code 1**. In `gitpr check`, a corrupted baseline exits with **code 2**.
* **Reach:** CLI (6 subcommands, all with `--format json`), MCP (`get_baseline_status` + `baseline://summary`), remote PR review and the lint flow.
* **Refactoring the Feature Required:** `NormalizedFinding` became a canonical type in `src/domain/finding/`.
* **Tests:** `tests/domain/baseline/` (5 files, **126**), `tests/application/use_cases/` (5 files, **141**), `tests/infrastructure/baseline/test_local_baseline_repository.py` (24), `tests/domain/finding/test_finding_types.py` (3), `tests/domain/linter/test_structured_findings.py` (44), `tests/test_baseline_cli.py` (54) and `tests/integration/test_baseline_effect_on_review_linter_risk.py` (34).
* **Artifacts:** `docs/baseline-suppressions.*.md` in **5 languages**, ADR-012, `glossary-baseline.md`, spec/plan/survey and task report.

### **41. `gitpr check` Subcommand — Unified CI Gate (`src/domain/check/`, `src/infrastructure/ci/`)**

* **What it is:** A **single entry point** for CI pipelines that orchestrates static analysis, SAST bridges, secrets scanning, optional AI review, risk score and baseline classification, and emits **one standardized artifact** with a deterministic exit code.
* **The Pipeline Order Is Contract:** (1) policy resolution — invalid exits with `2`; (2) diff extraction with smart excludes; (3) regex linter + secrets + external bridges; (4) preliminary risk score; (5) **optional AI review**; (6) baseline classification; (7) `--fail-on` threshold evaluation; (8) artifact generation with **secret masking**.
* **Domain Layer (`src/domain/check/`):** `check_types.py` (severity normalized into three levels with spec aliases); `check_config.py` (**CLI > env > defaults** precedence); `check_output.py` (format dispatch and atomic write).
* **Infrastructure Layer (`src/infrastructure/ci/`):** `sarif_builder.py` (SARIF 2.1.0), `junit_builder.py` (testsuites with `<failure>` and `<system-out>`) and `masking.py`.
* **Use Case (`run_check.py`):** `resolve_diff_base()` (explicit → `GITHUB_BASE_REF` → `origin/main` → `HEAD`), linter and bridge execution, risk score, optional review, baseline and final envelope. Stdout emits **only the artifact**.
* **Exit Codes:** `0` success; `1` finding countable at the threshold; `2` execution error.
* **CLI:** `gitpr check` with 8 options and 9 `GITPR_CHECK_*` variables.
* **GitHub Action Rewritten:** `action.yml` became a single `gitpr check` step. **Declared break** for workflows that relied on `--linter` + `--fullreview`.
* **Tests:** **37 scenarios** — `test_run_check.py` (8), `test_check_types.py` (8), `test_check_config.py` (3), `test_check_output.py` (3), `test_masking.py` (6), `test_sarif_builder.py` (2), `test_junit_builder.py` (2) and `test_check_cli.py` (5).
* **MCP:** the `run_check` tool returns the JSON envelope of the same pipeline, with `no_ia` defaulting to `true`.
* **Artifacts:** `docs/check-ci.*.md` in **5 languages**, ADR-013, `glossary-check-ci.md`, spec, `plansfacts` and survey.

### **42. Editor Extension — VS Code and Cursor (`extensions/vscode/`)**

* **What it is:** A **thin client** extension that brings GitPR flows into the editor without reimplementing anything: it starts `gitpr-mcp` over stdio and calls the existing tools. Keys, tokens, prompts and cache stay in `~/.gitpr/`, encrypted with Fernet.
* **Architecture:** `GitPrMcpClient` on top of `@modelcontextprotocol/sdk` + a `SessionManager` with **one session per folder** (multi-root) + contract verification on connect: the 7 mandatory tools must exist; if not, `MissingMcpToolsError` instructs `pip install --upgrade gitpr-cli`.
* **8 Commands:** `gitpr.commit`, `gitpr.review`, `gitpr.fullReview`, `gitpr.reviewMentor`, `gitpr.generatePr`, `gitpr.generatePrExplain`, `gitpr.publishPr` and `gitpr.check`.
* **Two-Step Publication:** `generatePr` opens an editable Markdown document; `publishPr` asks for modal confirmation and handles `success`, `needs_push`, `already_exists`, `auth_required` and `error`.
* **Diagnostics in the Problems Panel:** `run_check` is converted into a `DiagnosticCollection("gitpr")`.
* **Double-Depth Masking:** `masking.ts` mirrors `src/infrastructure/ci/masking.py`.
* **Extension Localization:** bundles in **en, es, fr and pt-br** (4 languages) — the GitPR UI itself keeps 5.
* **Extension CI/CD:** `.github/workflows/extension.yml` installs, builds, tests and packages the VSIX; on `ext-v*` tags it publishes to the Marketplace and Open VSX.
* **What the Extension Does Not Do:** it does not commit, does not push and does not publish without confirmation. 🆕 The extension's `gitpr.commit` command calls the `generate_commit_message` MCP tool — it does **not** open the new TUI, which is terminal-only.
* **Tests:** its own Mocha suite (7 scenarios, including real stdio) + `tests/test_mcp_server.py` (115 scenarios).
* **Artifacts:** `docs/editor-extension.*.md` in **5 languages**, ADR-014, `glossary-editor-extension.md`, specs, `plansfacts` and the Gemini report.

### **43. Interactive Commit TUI (`src/ui/commit_app.py`) 🆕**

* **What it is:** `gitpr -c`/`--commit` stopped being a silent pipeline — it validated the tree, asked the AI for the message and committed immediately, offering no way to choose files, review the text or reject a bad suggestion. It is now a **4-step** `CommitApp(App)` that keeps the developer in control all the way to the `git commit`.
* **Package:** a single module, `src/ui/commit_app.py` (220 lines). The three modals are **imported** from `src/ui/pr_publish_app.py` (`StageFilesScreen`, `CommitMessageScreen`, `CommitProgressScreen`) along with `_with_suppressed_stdout` — **zero duplicated widget**.
* **The 4 Steps:**
  1. **Stage Selector:** if there are files outside the index, it opens `StageFilesScreen` with checkboxes, "Select All", "Deselect All", "Stage Selected", "Skip Staging" and "Cancel". Index already complete → the step is skipped.
  2. **Generation:** reads `get_git_staged_diff()`, falls back to `get_git_diff()` when nothing is staged, and calls `generate_pr_content("commit", "commit", diff_text, provider)` in a daemon `threading.Thread` while `CommitProgressScreen` animates.
  3. **Review:** `CommitMessageScreen` shows the message, which can be edited before confirming — the **edited** message is the one that goes to git.
  4. **Execution:** a new `CommitProgressScreen` runs `execute_git_commit(append_coauthor_trailer(msg))` and records the SHA in the log.
* **Every Failure Becomes State, Not an Exception:** a broken AI falls back to `__("Code update")`; a commit that raises becomes `failed` with the captured output. Terminal states: `no_changes`, `cancelled`, `committed`, `failed`.
* **Auto-Exit on Success:** `log.mark_finished(False)` removes the button, and `_schedule_auto_exit()` registers `set_timer(0.6, self.exit)` — the user returns to the prompt without clicking anything. **On error Close is kept**, because that is where the failure log stays legible.
* **`--hook` Untouched:** the hook path remains TUI-free and merge-aware (`is_merge_in_progress()` → silent `return`). `prepare-commit-msg` never opens the application.
* **Configuration:** `GITPR_SKIP_UNSTAGED_CHECK` (skips the Stage Selector) and `GITPR_AUTO_STAGE` (stages everything and moves on), both already present in `DEFAULT_CONFIG` and in the configuration TUI schema.
* **Textual Details:** `ENABLE_COMMAND_PALETTE = False`; `on_button_pressed` handles `#btn_close`; `_on_commit_progress_dismissed` reads `_commit_success` instead of assuming success when the modal closes.
* **⚠️ Behavior Break:** without `--hook`, `gitpr -c` **always** opens the TUI — there is no TTY guard in routing. A script or CI job that relied on the silent commit has to switch to `--hook`; `GITPR_SKIP_UNSTAGED_CHECK`/`--no-unstaged-check` only skip step 1.
* **Tests:** `tests/test_commit_app.py` (**14 scenarios**) — every state transition and callback, plus a complete headless flow with the `run_test()` pilot.
* **Domain:** `CONTEXT.md` gained the terms **Stage selector** and **Commit flow**, with the synonyms to avoid ("file picker", "commit wizard").
* **Artifacts:** `docs/commit-message-ia.*.md` rewritten in the **5 languages** and the Gemini task report.

---

## **📊 Tests and Quality**

| Test File | Scenarios | Focus |
|------------------|----------|------|
| `tests/test_blame_engine_ranges.py` | 7 | Blame by line range in a file |
| `tests/test_blame_metrics.py` | 7 | Blame metrics: depth, commits, duration |
| `tests/test_baseline_cli.py` | 54 | `baseline` group: create/show/validate/update/suppress/unsuppress |
| `tests/test_changelog_builder.py` | 22 | Changelog builder: sections, translatable headings, contributors, links and dates |
| `tests/test_chat_backend.py` | 19 | Chat memory, persistence, slash commands |
| `tests/test_check_cli.py` | 5 | `check` CLI: options, formats, exit codes |
| `tests/test_commit_app.py` | 14 🆕 | `CommitApp`: mount, Stage Selector, auto-stage, skip, cancel, confirm, commit success/failure, auto-exit and the headless flow |
| `tests/test_commit_classifier.py` | 30 | Conventional Commits classification + `pr_number_from_merge()` across the 4 forges |
| `tests/test_config_app.py` | 78 | Configuration TUI: mounting, category switching, dirty tracking, F2 blocked, Ctrl+R, search, secrets |
| `tests/test_config_cli.py` | 9 | Registration of the `config` subcommand, `-h`, lazy import, clean stdout |
| `tests/test_config_schema.py` | 42 | `DEFAULT_CONFIG` coverage, no duplicates, categories/kinds, `advanced` only in Advanced, skills section |
| `tests/test_config_store.py` | 22 | Round-trip on the temporary `.env`, comments and order preserved, idempotent `remove_config_value()` |
| `tests/test_config_suggest_reviewers.py` | 8 | Suggested reviewers configuration (keys and defaults) |
| `tests/test_config_validation.py` | 40 | Types, enums, templates, `validate_ai_key()` with a mocked SDK (401 vs. network vs. ollama) |
| `tests/test_core.py` | 49 | Main flows, git diff, PR generation, timing, staging, coauthorship, hook language |
| `tests/test_diff_failure_cli.py` | 3 | Exit code 1 on git failure, 0 on empty diff, fail-open in `--hook` |
| `tests/test_diff_parser.py` | 26 | Diff parser by lines/hunks + `summarize_patch()` and `split_patch_sections()` |
| `tests/test_explain_command.py` | 4 | `explain` CLI: success, missing key, empty diff, contextual help |
| `tests/test_external_linters.py` | 33 | Checkstyle bridge: XML parser, subprocess, diff intersection, report |
| `tests/test_full_diff_encoding.py` | 1 | Real repository with a Latin-1 file: the `0xFA` byte becomes `U+FFFD` and the file appears in the diff |
| `tests/test_i18n.py` | 20 | Language parity, missing/orphan keys, identity — **3 failures** ❌ |
| `tests/test_install_wizard.py` | 3 | Interactive installation wizard |
| `tests/test_issue_engine.py` | 4 | Structured issue draft |
| `tests/test_linter_metrics.py` | 4 | Linter metrics: errors, warnings, duration |
| `tests/test_linter_presets.py` | 5 | Linter presets: resolution and forced re-download |
| `tests/test_main_suggest_reviewers.py` | 8 | `--no-suggest-reviewers` flag in the CLI and in the contextual help |
| `tests/test_mcp_prompts.py` | 11 | MCP prompt templates and language fallback |
| `tests/test_mcp_server.py` | 115 | MCP tools (18), resources (19), annotations, patching, direct CLI, offload, extension contract |
| `tests/test_mcp_server_e2e.py` | 6 | Real MCP server via subprocess + JSON-RPC stdio |
| `tests/test_mentor_cli.py` | 4 | `mentor` CLI and `--mentor` opt-in — **2 known failures** ❌ |
| `tests/test_metrics.py` | 164 | **Ledger**: synchronous write, idempotent export, bundle/merge, versioning, migration, renderers, cost, quality, modules, providers, cycle |
| `tests/test_net_timeouts.py` | 12 | Network/AI timeouts — aligned with the real 180s default |
| `tests/test_plugins.py` | 17 | Plugin discovery, linter rule merge, MCP prompts |
| `tests/test_policy_cli.py` | 26 | `policy` group: list/show/validate/use/off/install/init, confirmation and lockfile |
| `tests/test_pr_publish_app.py` | 55 | PR Publisher TUI: screens, flows, suggested reviewers, `NoticeScreen`, merge modal |
| `tests/test_pr_publish_linter_modal.py` | 4 | Linter error modal: abort, no-verify |
| `tests/test_pre_save.py` | 3 | --pre-save flag and JSON payload |
| `tests/test_release_cli.py` | 4 | Release CLI: options, help with documented epilog |
| `tests/test_release_engine.py` | 53 | **Cutoff anchored in the changelog, `_drop_released()`, links, PR map by merge commit**, publication |
| `tests/test_reviewer_resolution.py` | 18 | Resolution ladder, rejection of partial name, dedup, failure tolerance |
| `tests/test_reviewer_suggestion.py` | 18 | Reviewer suggestion logic (ranking, exclusion, top-N, `last_commit_hash`) |
| `tests/test_risk_cli.py` | 4 | `risk` CLI: `--file`, `--format json`, `--base`, `--no-history` |
| `tests/test_security_ruleset.py` | 61 | Built-in ruleset matrix: regex, placeholders, level, wildcard, merge, translations |
| `tests/test_skill_command.py` | 10 | Download and validation of skill templates |
| `tests/test_skill_context.py` | 20 | `get_skill_context()` with `quiet=True`, fallbacks, PR artifact guard |
| `tests/test_skill_templates.py` | 99 | Parameterized sweep of **every published template** against embedded references |
| `tests/test_smart_excludes.py` | 15 | Smart pathspec filter and forced re-download |
| `tests/test_suggest_reviewers.py` | 17 | Suggested reviewers in the PR flow (integration, `no_login` hint) |
| `tests/test_tests_command.py` | 4 | `tests` group and `generate` CLI, `--finding` and epilog with link |
| `tests/test_thinking_words.py` | 5 | Loading, parsing with a `;` separator and forced reload |
| `tests/test_updater.py` | 55 | **PyPI gate**: rolling TTL, legacy/corrupted cache, atomic write, `force`, `bounded_urlopen`, PEP 440 comparator, pre-release, strict skip switch and subcommand gate |
| `tests/test_usage_log.py` | 27 | Usage log: date-derived name, synchronous write, silence, multi-forge `_repo_label()` |
| `tests/test_version_bump.py` | 17 | Semantic bump: major/minor/patch, targets and validation |
| `tests/test_web_links.py` | 26 | Remote normalization (https/ssh/scp/credentials/port) and URL patterns per forge |
| `tests/application/use_cases/test_activate_policy_pack.py` | 27 | Pack activation, lockfile, refusals |
| `tests/application/use_cases/test_baseline_gate.py` | 40 | Gate: loading, classification, annotation, layers and abort |
| `tests/application/use_cases/test_baseline_summary.py` | 12 | Read-only view of the baseline for MCP/IDE |
| `tests/application/use_cases/test_calculate_risk.py` | 5 | Correlation of diff/findings/tests/history, degradation |
| `tests/application/use_cases/test_create_baseline.py` | 30 | Creation/update with provenance and preservation of decisions |
| `tests/application/use_cases/test_generate_mentor_explanation.py` | 5 | Batches, severity, cache hit, missing key |
| `tests/application/use_cases/test_generate_pr_explanation.py` | 3 | explain use case: provider dict and missing key |
| `tests/application/use_cases/test_generate_test_file.py` | 5 | test generation use case: provider dict and raw string |
| `tests/application/use_cases/test_install_policy_pack.py` | 10 | Pack installation, origins and conflicts |
| `tests/application/use_cases/test_run_check.py` | 8 | check orchestration: order, degradation, exit codes |
| `tests/application/use_cases/test_suppress_finding.py` | 29 | Suppression/removal in the four scopes |
| `tests/application/use_cases/test_validate_policy_pack.py` | 23 | Validation: closed schema, checksum, compatibility |
| `tests/badge/test_badge_append.py` | 9 | Idempotent append of the badge to the PR body |
| `tests/badge/test_badge_builder.py` | 19 | shields.io Markdown composition, escape, color rule, style |
| `tests/badge/test_badge_cli.py` | 15 | `gitpr badge` command: snippet, `--readme`, `--style` |
| `tests/badge/test_badge_data.py` | 7 | Alert counting; `None` with no rules configured |
| `tests/badge/test_badge_offline.py` | 6 | Offline guard (network forbidden, except loopback) |
| `tests/badge/test_badge_optout.py` | 17 | `GITPR_BADGE=false` on every publication path |
| `tests/badge/test_badge_paths.py` | 10 ✅ | Single injection point in both publishers — remains green |
| `tests/demo/test_demo_app.py` | 22 | Tour TUI: navigation, screens, help modal |
| `tests/demo/test_demo_isolation.py` | 14 | The tour touches neither `~/.gitpr` nor the network |
| `tests/demo/test_demo_runner.py` | 33 | `DemoState` state machine: forward, back, completed, text mode |
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
| `tests/domain/check/test_check_types.py` | 8 | Three-level severity, spec aliases, summary |
| `tests/domain/finding/test_finding_types.py` | 3 | Canonical finding type: `snippet_hash`, `rule_version` |
| `tests/domain/linter/test_sast_finding_mapper.py` | 2 | Uniform formatting and `[Gitleaks + Regex]` deduplication |
| `tests/domain/linter/test_structured_findings.py` | 44 | `findings_out`: structured findings and the new-side line counter |
| `tests/domain/mentor/test_mentor_explanation_builder.py` | 8 | Pure builder: epistemic honesty, removed URLs, order preserved |
| `tests/domain/policy/test_policy_checksum.py` | 15 | SHA-256 of the pack and detection of manual editing |
| `tests/domain/policy/test_policy_compatibility.py` | 22 | Version and credential compatibility |
| `tests/domain/policy/test_policy_manifest.py` | 42 | Closed schema, `baseline:` block, manifest errors |
| `tests/domain/policy/test_policy_resolver.py` | 47 | Precedence, layered composition, active pack × base |
| `tests/domain/pr/test_explain_section_builder.py` | 6 | Markdown rendering, tolerant parsing, `[FILL]`, dict payload and `None` |
| `tests/domain/risk/test_risk_calculator.py` | 5 | 50/30/20 formula, 0–100 clamp, mandatory escalation |
| `tests/domain/risk/test_risk_rules.py` | 5 | Weights, thresholds, critical path glob, override YAML |
| `tests/domain/tests_generation/test_framework_detector.py` | 11 | Detection by config, manifest and directory; override; warned failure |
| `tests/domain/tests_generation/test_scaffold_builder.py` | 7 | Conventional path per framework (Laravel, Pytest, JS/TS) |
| `tests/fix/test_apply_fix.py` | 53 | Full use case: review → AI → validate → classify → dry-run/apply |
| `tests/fix/test_chat_shared_extractor.py` | 8 | Extractor shared between chat and `fix` |
| `tests/fix/test_fix_cli.py` | 31 | Subcommand routing, options, dry-run default, `--force` |
| `tests/fix/test_fix_history.py` | 21 | `.gitpr/fix_history.json` ledger: atomic write, read |
| `tests/fix/test_fix_settings.py` | 10 | The five `GITPR_FIX_*` and the fallbacks for invalid values |
| `tests/fix/test_patch_applier.py` | 19 | `git apply` wrapper: check/apply/reverse, branch, status |
| `tests/fix/test_patch_extractor.py` | 13 | Fenced blocks → validated unified diff |
| `tests/fix/test_patch_safety_classifier.py` | 22 | safe/review_required/experimental matrix and reason codes |
| `tests/fix/test_resolve_last_review.py` | 13 | Selection of the last review, exclusion of per-file reviews |
| `tests/fix/test_rollback_fix.py` | 14 | `--rollback`: reverse and the three refusals |
| `tests/infrastructure/baseline/test_local_baseline_repository.py` | 24 | Sole reader/writer: atomic write, checksum, repair |
| `tests/infrastructure/ci/test_junit_builder.py` | 2 | JUnit XML: failure/system-out |
| `tests/infrastructure/ci/test_masking.py` | 6 | Credential masking before serializing |
| `tests/infrastructure/ci/test_sarif_builder.py` | 2 | SARIF 2.1.0 and `tool.driver.rules` |
| `tests/infrastructure/git/test_risk_history_reader.py` | 4 | Safe `git log`: commits, bugs and reverts in 90 days |
| `tests/infrastructure/git/test_test_matcher.py` | 5 | Test ↔ production matching by convention |
| `tests/infrastructure/linter/external/test_bandit_bridge.py` | 3 | Bandit bridge: availability, parsing, mapping |
| `tests/infrastructure/linter/external/test_base_bridge.py` | 4 | ABC: hardened subprocess, timeout, missing binary |
| `tests/infrastructure/linter/external/test_gitleaks_bridge.py` | 4 | Gitleaks bridge: secret masking, per-file scope |
| `tests/infrastructure/linter/external/test_semgrep_bridge.py` | 3 | Semgrep bridge: JSON parsing, severity, non-Python skip |
| `tests/integration/test_baseline_effect_on_review_linter_risk.py` | 34 | Real effect of the baseline on review, lint and risk in a real repository |
| `tests/integration/test_policy_effect_on_review_linter_risk.py` | 31 | Real effect of the pack on review, lint and risk (with an autouse network guard) |
| `tests/review/test_diff_normalizer.py` | 20 | Newlines, diff validation, smart-excludes in Python |
| `tests/review/test_diff_source.py` | 12 | `DiffOrigin`/`DiffSource`: provenance, `cache_scope`, `is_remote` |
| `tests/review/test_remote_pr.py` | 40 | Orchestration: gates, PR, diff, linter, optional comment |
| `tests/review/test_review_pr_cli.py` | 25 | `review-pr` CLI: options, rejections before the AI, `--post-comment` |
| `tests/scm/test_azure_devops_provider.py` | 48 | Azure DevOps provider: org/project, PRs, `ScmNotSupportedError` |
| `tests/scm/test_bitbucket_provider.py` | 44 | Bitbucket provider: Basic auth, workspace, PRs |
| `tests/scm/test_contract.py` | 42 | `ScmProvider` contract: signatures, dataclasses, errors |
| `tests/scm/test_factory.py` | 11 | `resolve_scm_provider()` + `detect_provider_from_remote()` |
| `tests/scm/test_github_api_shim.py` | 18 | Deprecated `github_api` shim → delegates to the provider |
| `tests/scm/test_github_provider.py` | 72 | GitHub provider: REST, headers, PRs, issues, releases, reviewer read-back |
| `tests/scm/test_gitlab_provider.py` | 52 | GitLab provider: API v4, namespace, synthesized headers and `overflow` |
| `tests/scm/test_init_wizard.py` | 12 | `--init` wizard: forge detection, validation, persistence |
| `tests/scm/test_release_publish.py` | 8 | Release publication per forge (GitHub/GitLab) |
| `tests/split/test_apply_split_plan.py` | 11 | The only module that mutates: selective staging, one commit per group |
| `tests/split/test_generate_split_plan.py` | 12 | Diff → units → groups → conflict pre-validation → messages |
| `tests/split/test_hunk_grouper.py` | 18 | Prompt, budget/trimming, AI call, response validation |
| `tests/split/test_hunk_parser.py` | 18 | `parse_units()`/`build_patch()`: text ↔ model, pure |
| `tests/split/test_selective_stager.py` | 11 | Staging of a subset, check against HEAD, clean index |
| `tests/split/test_split_cli.py` | 31 | split CLI: options, dry run default, `--apply` |
| `tests/sync_i18n.py` | — | i18n coverage verification script (scaffold; never run) |

**Total:** **3010 scenarios collected across 141 test modules** (54 at the root + 12 in `tests/application/use_cases/` + 10 in `tests/fix/` + 9 in `tests/scm/` + 7 in `tests/badge/` + 6 in `tests/split/` + 6 in `tests/demo/` + 5 in `tests/domain/baseline/` + 4 in `tests/domain/policy/` + 4 in `tests/review/` + 4 in `tests/infrastructure/linter/external/` + 3 in `tests/domain/check/` + 3 in `tests/infrastructure/ci/` + 2 in `tests/domain/risk/` + 2 in `tests/domain/tests_generation/` + 2 in `tests/integration/` + 2 in `tests/infrastructure/git/` + 2 in `tests/domain/linter/` + 1 in `tests/domain/finding/` + 1 in `tests/domain/mentor/` + 1 in `tests/domain/pr/` + 1 in `tests/infrastructure/baseline/`; **+14 scenarios, +1 file** since the previous report).

**Quality notes for this version:**
- **Full run on this machine:** **5 failed, 3003 passed, 2 skipped, 138 subtests passed in 442.79 s (7m22s)** 🆕 — the 5 failures are the same inherited debts: 3 i18n and 2 mentor. The scenario total grew by 14 and the failure count **did not change**.
- **The 3 i18n failures are still real translation debt — and they are now bigger:** `test_key_parity_and_count` (es/fr at 1618), `test_no_missing_keys` (**now 11** keys used in code without a dictionary) and `test_no_orphan_keys` (2 old keys). The three new commit TUI strings (`\n✅ Commit created successfully!\n`, `✅ Commit successfully created!` and `❌ Failed to commit: {error}`) entered the code without an entry in any `langs/*.json`. **It was 8, it became 11.** ❌
- **The 2 failures in `tests/test_mentor_cli.py` are still the same isolation bug:** `@patch("src.core.get_git_diff")` does not reach the binding imported by value in `src/main.py`. In a tree whose only change is a `.md` (filtered by `:(exclude)*.md` in the smart excludes) the real diff comes back empty, the flow returns before the renderer and the assertion fails. **With the patch at `src.main.get_git_diff`, both scenarios pass.** It remains a two-line fix.
- **The 14 new scenarios cover exactly what changed:** `tests/test_commit_app.py` exercises `on_mount` on all three paths (files outside the index, `GITPR_AUTO_STAGE` and `skip_unstaged`), the cancel and stage callbacks, the `no_changes` short-circuit, the progress push, both `CommitMessageScreen` outcomes, the worker that runs the commit and marks the modal, `btn_close`, the auto-exit timer and a **complete headless flow** with the Textual pilot.
- **⚠️ The new headless flow is flaky — a new failure in this window:** `test_headless_full_flow` failed in **2 of 3 isolated runs** (1 passed), always at the same point: after `pilot.click("#btn_confirm")` the app stays on `CommitMessageScreen` and `_wait_until(app.result == "committed")` blows the 5 s timeout. The screen handler is correct (`on_button_pressed` → `dismiss("confirm")`); what varies is the click arriving before the modal layout settles — the test waits for `#btn_confirm` to **exist**, not to be clickable. It is the suite's first **flaky** failure, and it was born with this window's code.
- **No new integration test was needed:** the TUI orchestrates functions already covered by `tests/test_core.py` (`get_git_diff`, `get_git_staged_diff`, `execute_git_commit`, `append_coauthor_trailer`) — the suite was extended where the new behavior lives, as in the previous windows.
- **`tests/conftest.py` remains hermetic:** `GITPR_SHOW_LOGS=false`, `GITPR_SKIP_UPDATE_CHECK=true`, `GITPR_LANG=en_us`, `GITPR_LINTER_SECURITY=false` and `LANG_VERSION` at the code's version.
- **Network guard in two places:** `tests/demo/conftest.py` and `tests/integration/conftest.py`.
- **Real git fixtures:** `tests/fix/git_fixture.py`, `tests/split/git_fixture.py` and `tests/test_full_diff_encoding.py`.
- **The extension suite is separate:** 7 Mocha scenarios in `extensions/vscode/src/test/mcp_client.test.ts`, of which 1 really integrates with the Python MCP server over stdio; it does not count toward pytest.
- **CI runs on Python 3.10 and 3.13** with `fail-fast: false`.

---

## **🌐 Internationalization and Documentation**

* **i18n coverage — the debt grew:** **1620 keys** in `pt_br.json`, `pt_pt.json`, `es_es.json` and `fr_fr.json`, but **1618** in `es.json` and `fr.json`. The code uses **1629** keys (**it was 1626**): **11 untranslated** — the previous 8 (`publish_pull_request` messages and the descriptions of the `run_check`/`publish_pull_request` MCP tools) plus **3 new ones from the commit TUI** — and **2 orphans** (`Cannot publish a Pull Request from a detached HEAD or empty branch.` and `Current branch has not been pushed to the remote…`). ❌
* **🆕 `__lang_version__` went up to v0.0.36 without changing a single dictionary:** the bump accompanied the 1.4.4 release, but the three new strings **were not** added to the master lists — the visible effect is an OTA refresh that downloads the same content. In other words: the bump was of version, not of translation.
* **Translation sources in lockstep:** a new key must exist in the code (source), in `langs/pt_br.json` (**master list**), in the FR/ES dicts of `scripts/sync_all_langs.py` and in the curated values of `scripts/fix_mangled_i18n_keys.py`; the `len(CLEAN_KEYS)` assertion remains at 49.
* **New topics:** **none** in this window — the 50 canonical topics hold.
* **🆕 Topics updated in this window:**
  - `docs/commit-message-ia.*` (**5 files**) — the `-c` documentation stopped describing a command "that prints the message" and started documenting the **4-step interactive flow**, the Stage Selector, the message review/editing, the animated progress, the `GITPR_SKIP_UNSTAGED_CHECK`/`GITPR_AUTO_STAGE` variables and the non-interactive behavior of `--hook`.
* **`CONTEXT.md` (updated):** the domain glossary gained **Stage selector** and **Commit flow**, with the synonyms to avoid ("file picker", "commit wizard") — it now has **7 terms**, alongside the five from the update gate.
* **Documentation in 5 languages:** **50 canonical topics** in `docs/` (unchanged) — **47 with full coverage in all 5 languages** and **3 partial topics** (`github-issue-prompt-com-gh`, `version-markers`, `review-pr`), the same 3 as before.
* **Local Claude Code skills:** `.claude/skills/` with **29 skills** (count unchanged).
* **Memory Index:** `.claude/memory/MEMORY.md` with **40 patterns** (count unchanged).
* **Task reports:** `docs/claude-code/reports/develop_natan/` (**114** in total, unchanged since the previous report) and `docs/gemini/reports/develop_natan/` (**19**; **+2** — `2026-10-29_interactive_commit_tui.md` and `2026-10-29_auto_close_commit_modal.md`).
* **Status reports:** `docs/reports/` — this is the **21st report** (v0.0.21); v0.0.20 is never overwritten.
* **Development plans:** 148 files in `docs/plans/` (unchanged) + **2 files in `docs/gemini/plans/`** (+1 — `2026-10-29_develop_natan_interactive_commit_tui.md`) + **21 files in `docs/survey/`** (unchanged).

---

## **🔄 Distribution Pipeline**

1. **PyPI (single channel):** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **Mandatory update with a rolling window:** execution checks PyPI on startup and **blocks with exit code 1** if there is a newer version, printing `pip install --upgrade gitpr-cli`. The response is cached for **1 hour** and `--update` **always** queries the network. The comparison is PEP 440: a remote pre-release does not block a stable installation, and an unreadable version counts as unknown. **The gate now covers subcommands** when there is a TTY; CI, pipes, hooks, MCP and `-h` remain exempt. 🆕 **1.4.4 is already published**, so the gate can be revalidated on a 1.4.2 client at any time.
3. **GitHub Releases:** removed — no PyInstaller, no `.exe` asset, no hot-swap
4. **GitHub Actions:** workflow `pr-review.yml` + `action.yml` (installs via pip) + `tests.yml` (Python 3.10 and 3.13 matrix) + `extension.yml` (VSIX; publishing to the Marketplace/Open VSX on `ext-v*`). `action.yml` calls a single `gitpr check` step — **declared break** for anyone using `--linter` + `--fullreview`.
5. **MCP Server:** `gitpr-mcp` entry point via `pyproject.toml`
6. **Editor Extension:** standalone VSIX package (`natanfiuza.gitpr`), with VS Code Marketplace and Open VSX channels; requires Node.js 20+ only to build, not to use
7. **OTA templates and languages:** `templates/` and `langs/*.json` served from GitHub (main) — `__lang_version__` **v0.0.36** 🆕 refreshes the local copies in `~/.gitpr/langs/`; `__scripts_version__` **v0.0.4** refreshes the hooks
8. **Version derived from the code:** `pyproject.toml` uses `version = {attr = "src.updater.__version__"}` — `__version__` is the **only** source
9. **`package-data` matters:** the four embedded packs (`src/policy_packs/*`) travel in the wheel
10. **✅ Release state — up to date:** `__version__` is **1.4.4**, the `v1.4.4` tag exists, PyPI serves **1.4.4** and the `[1.4.3]` and `[1.4.4]` sections of `CHANGELOG.md` are committed. The working tree is clean: the last `PR_DESC` and this report went into commit `c22a4dc`. **The release debt opened in the two previous windows is closed** — the release pipeline remains manual, but the state is consistent.

---

## **📈 Evolution since the Previous Report (v0.0.20)**

| Area | v0.0.20 (previous) | v0.0.21 (current) |
|------|-------------------|-----------------|
| **GitPR Version** | 1.4.3 — merged in PR #228, **not published** (PyPI 1.4.2, no tag, changelog in the working tree) | **1.4.4** — tagged (`v1.4.4`) and **published on PyPI**; 1.4.3 went out too |
| **Language Version** | v0.0.35 | **v0.0.36** — bump with no dictionary change |
| **Hook Scripts Version** | v0.0.4 | v0.0.4 (unchanged) |
| **AI Providers** | Gemini + DeepSeek + Ollama | Unchanged |
| **Languages** | 5 languages, 6 dictionaries | Unchanged |
| **Subcommands** | 15 | 15 (unchanged) — 4 groups (baseline 6, metrics 8, policy 7, tests 1) |
| **`-c`/`--commit` Flow 🆕** | Silent pipeline: validates the tree, generates the message, commits | **4-step TUI** (`CommitApp`) with Stage Selector, review/edit and animated progress; `--hook` unchanged |
| **Commit Diff 🆕** | `git diff HEAD` (whole tree) | **`get_git_staged_diff()`** (`git diff --cached`) with a fallback to the tree |
| **Commit Feedback 🆕** | Terminal returned after the `git commit` | Modal closes on its own in 0.6 s and the CLI prints the result with the SHA |
| **Layers** | 9 domain packages, 15 use cases | Unchanged — the TUI created no domain rule |
| **MCP Tools** | 18 tools / 19 resources / 7 prompts | Unchanged |
| **CLI Flags** | 32 at the root + 15 subcommands | Unchanged |
| **Environment Variables** | 58 in `DEFAULT_CONFIG` + prices/currency + 9 `GITPR_CHECK_*` | Unchanged — `GITPR_AUTO_STAGE`/`GITPR_SKIP_UNSTAGED_CHECK` now also govern the TUI |
| **Config Schema** | 80 `ConfigField` / 16 categories | Unchanged |
| **Commit TUI 🆕** | — | `src/ui/commit_app.py` (220 lines), reusing 3 modals from the PR Publisher |
| **Update Gate** | Rolling 1 h TTL, `-u` always online, PEP 440, `bounded_urlopen`, strict semantics, subcommand gate on a TTY | Unchanged in the code — 🆕 **now published** in 1.4.3/1.4.4 |
| **Linter** | Regex + secrets + SAST + `findings_out` + baseline-aware | Unchanged |
| **Git Hooks** | post-checkout/post-merge/pre-push in the ledger | Unchanged — `prepare-commit-msg` remains non-interactive |
| **SCM Layer** | 4 forges, pagination, `web_links.py` | Unchanged |
| **Release engine** | `[1.4.3]` section generated **in the working tree**; no tag and no publication | `[1.4.3]` and `[1.4.4]` sections committed; `v1.4.3`/`v1.4.4` tags; PyPI at 1.4.4 |
| **i18n (keys per file)** | 1620 × 4 and 1618 × 2 — 1626 in the code, 8 untranslated, 2 orphans | **1620 × 4 and 1618 × 2 — 1629 in the code, 11 untranslated, 2 orphans** ❌ |
| **Documentation** | 50 topics (47 complete + 3 partial) | 50 topics (47 + 3) — **no new topic**; `commit-message-ia` rewritten in all 5 languages |
| **Domain glossary** | `CONTEXT.md` with 5 update gate terms | **7 terms** — `Stage selector` and `Commit flow` |
| **CI** | `tests.yml` (3.10 + 3.13) + `action.yml` + `extension.yml` | Unchanged |
| **Test Suite** | 2996 scenarios (140 files) | **3010 scenarios (141 files) — +14 scenarios, +1 file** |
| **Known Failures** | 5 (3 i18n + 2 mentor) | **5** (3 i18n + 2 mentor) — same failures; the i18n debt got worse, from 8 to 11 keys |
| **Dependencies** | No new Python dependencies | None new — the TUI uses the Textual already present |
| **Commits since the report** | 2 commits (`68df9ae`, `145cbac`) | **3 code commits + 1 documentation commit + 2 merges** (`9bd2038`, `80cb849`, `709f4f8`, `c22a4dc`; merges #230 and #231) |
| **Merged PRs** | 3 PRs (#224, #226, #228) | **2 PRs (#230, #231)**; the commit TUI PR references issue #227 |
| **Memory Index** | 40 patterns | 40 patterns (unchanged) |
| **Task reports** | 114 claude-code, 17 gemini | **114 claude-code (unchanged) and 19 gemini (+2)** |
| **Development plans** | 148 plans, 21 surveys | 148 plans (`docs/plans/`, unchanged), 2 in `docs/gemini/plans/` (+1), 21 surveys (unchanged) |

---

## **🚧 Next Steps**

* **🆕 Close the i18n debt of the three commit TUI strings:** `\n✅ Commit created successfully!\n`, `✅ Commit successfully created!` and `❌ Failed to commit: {error}` need to enter `langs/pt_br.json` (master list), the FR/ES dicts of `scripts/sync_all_langs.py` and the values of `scripts/fix_mangled_i18n_keys.py` — and the 8 old keys remain pending. Closing the 11 unblocks `test_no_missing_keys` and `test_key_parity_and_count` at once. **Inherited item, now bigger.**
* **🆕 Stabilize `test_headless_full_flow`:** the click on `#btn_confirm` arrives before the `CommitMessageScreen` layout settles in ~2/3 of runs, and the test dies on the 5 s timeout stuck on the screen. Waiting for the button to become **clickable** (`pilot.pause` until `btn.can_focus`/`screen.is_mounted`, or replacing the click with `screen.dismiss("confirm")`) — or increasing the `_wait_until` tolerance — removes the suite's first flaky failure.
* **🆕 Protect `-c` from non-interactive execution:** routing has no TTY guard — without `--hook`, `gitpr -c` **always** opens `CommitApp`, including in scripts and CI. The pattern already exists in `_may_ask_the_user()`; reusing it (or requiring `--hook`) keeps a job from getting stuck inside a Textual app.
* **🆕 Revalidate the real gate after the release:** with 1.4.4 published, a 1.4.4 installation should **not** block, and a 1.4.2 one should block within one hour after the package goes up — the scenario that originated the fix. **Inherited item, now executable.**
* **Fix the isolation in `tests/test_mentor_cli.py`:** they are the **oldest two failures** in the suite and the **only** ones remaining besides i18n. The patch targets `src.core.get_git_diff`, but `src/main.py` imports the name **by value**. Switching the target to `src.main.get_git_diff` closes the suite without touching the product. **Inherited item, still open.**
* **Automate the release in GitHub Actions:** the test CI has four workflows, but build + upload to PyPI remain manual — and `extension.yml` already shows the pattern (tag trigger) a `release.yml` can follow. **Inherited item, still open.**
* **Extend the secrets ruleset coverage:** the three gaps remain open — unquoted assignment (`API_KEY=abc123`), the prefixes `ASIA…`, `github_pat_…` and `xoxc-`/`xoxd-`, and the absence of a left boundary in the key name. **Inherited item, still open.**
* **Translate `docs/review-pr.*.md`:** it is the **last** feature family with partial coverage (2 of 5 languages). **Inherited item, still open.**
* **Untrack the artifacts in `.gitpr/metrics/export/`:** **34 tracked files** remain, because `gitignore` does not untrack. A single `git rm --cached` is still missing. **Inherited item, still open.**
* **Document the `request_pull_request_reviewers` contract break:** it went from `None` to `list[str]`; third-party `ScmProvider` subclasses need to be updated. **Inherited item, still open.**
* **Document the `GITPR_SKIP_UPDATE_CHECK` semantics change as a migration note:** `docs/tutorial/install-from-source` already describes the strict parser, but automations that set `=false` expecting to silence the check now see it run. **Inherited item, still open.**
* **🆕 Document the behavior break of the interactive `-c`:** `commit-message-ia` describes the new flow, but does not explicitly say "it used to be silent; in CI use `--hook`". A migration note in the next release's `CHANGELOG` closes the gap — 1.4.4 already shipped without it.
* **Map the blast radius of the baseline on the exit code:** a baseline that cannot be applied aborts six flows with exit code 1, and `check` uses **2**. It is worth spelling out the full contract in `docs/baseline-suppressions`/`docs/check-ci`. **Inherited item, partially covered by check.**
* **Reconcile the project version:** `CLAUDE.md` still says `Current version: 0.0.37` while `__version__` is at **1.4.4**. Define a single convention. **Inherited item, still open.**
* **`gitpr -h config` ignores `-h`:** the subcommand opens the TUI instead of showing help — the gate `if ctx.invoked_subcommand is not None: return` runs before the `help_flag` block. **Inherited item, still open.**
* **Migrate the `sync_i18n.py` extractor to AST:** the regex truncates literals with implicit concatenation (`__("a " "b")`). `tests/test_i18n.py` already uses AST; the sync script remains on regex. **Inherited item, still open.**
* **Smart Exclude section in the TUI:** item 10 of the configuration TUI survey remains unstarted, and the `GITPR_SMART_EXCLUDES_*` fields remain under `diff`, without their own category. **Inherited item, still open.**
* **Debts recorded in the config TUI plan:** `DEFAULT_CONFIG` became redundant with the schema; the opening banner does not list `--init`, `--base` or `--plugins`; `LinterApp` is the **only** Textual app that still does not disable the command palette; and the **9 `GITPR_CHECK_*`** still have **no** category in the schema. **Inherited item, still open.**
* **Create a migration note for `action.yml`:** replacing `--linter` + `--fullreview` with `gitpr check` is a contract break for existing workflows; `docs/check-ci` documents the new command, but not a "before and after". **Inherited item, still open.**
* **Publish the extension to the marketplaces:** the workflow is ready, but it depends on `VSCE_PAT`/`OVSX_PAT`; until then, installation is via local VSIX. **Inherited item, still open.**
* **Review `git diff` in repositories with large binary files:** decoding with `errors="replace"` solves legacy content, but binary bytes in the diff now reach the model as a sequence of `U+FFFD`. It is worth confirming that the smart excludes cover the real cases. **Inherited item, still open.**
* **Monitor the ES/FR fallback on the site:** the README links already use `?lang=es` and `?lang=fr` (the correct format), but the site may still serve English for those languages. Publishing the translations fulfills the links' promise. **Inherited item, still open.**
* **Anthropic Claude provider:** Direct support for the Claude API (`claude-sonnet-5`).
* **ASCII/Textual charts in the Dashboard:** Time histograms and token trends in the metrics TUI.
* **Local `.gitpr/conf/` seed:** The seed of local configuration templates (smart-excludes, linter) remains pending as its own subcommand or a wizard step.
* **More providers:** Direct OpenAI, additional local providers.

### ✅ Completed in this window (2026-10-28 → 2026-10-29)

* ~~**`gitpr -c` with no control over what entered the commit**~~ 🆕 — PR #230 (`9bd2038`): `CommitApp` with a Stage Selector, generation over the staged diff (`get_git_staged_diff()`), message review/editing and animated execution. `--hook` preserved. **14 new scenarios in `tests/test_commit_app.py`.**
* ~~**The commit modal stopped at the progress screen waiting for a click**~~ 🆕 — PR #231 (`709f4f8`): `_schedule_auto_exit()` with a 0.6 s timer on success, `mark_finished(False)` and printing the result in the CLI; on error the **Close** button stays.
* ~~**`gitpr -c` generated the message from `git diff HEAD`**~~ 🆕 — `get_git_staged_diff()` now prefers the index, falling back to the tree only when nothing is staged.
* ~~**1.4.3 was merged but not published**~~ — 1.4.3 and 1.4.4 were tagged and published (`v1.4.4`); the `CHANGELOG.md` sections are committed and PyPI serves 1.4.4. **Debt open for two windows — closed.**
* ~~**The `-c` documentation described a silent command**~~ 🆕 — `docs/commit-message-ia.*` rewritten in the 5 languages with the 4-step flow, the control variables and the hook behavior.
* ~~**The glossary had no commit terms**~~ 🆕 — `CONTEXT.md` gained **Stage selector** and **Commit flow** (7 terms in total).

---

**Report generated on:** 2026-10-29  
**Branch:** `develop_natan`  
**Author:** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
