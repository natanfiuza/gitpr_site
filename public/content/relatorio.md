# **🚀 Project Status Report: GitPR CLI — v0.0.17 (2026-10-12)**

## **📌 Overview**

**GitPR** is an advanced CLI (Command Line Interface) tool for automating Git processes using Artificial Intelligence (Google Gemini / DeepSeek / Ollama). Its main goal is to act as a local intelligent assistant that performs Code Reviews, generates Pull Requests, semantic commit messages, audits technical debt, and injects best practices into the developer workflow (Shift Left).

**What's New in This Version (v0.0.17):**
- **Release 1.3.0 was closed and 1.4.0 is already tagged:** the `v1.3.0` tag (merge of PR #193, 2026-09-24) closed the item that had been blocking two windows, and `CHANGELOG.md` was completed with the six features that were missing. Ten days later the `v1.4.0` tag (merge of PR #207, 2026-10-12) closed this entire window — the five features below **plus** the 1.3.0 release note fit into a single cycle.
- **`gitpr release` stopped repeating the history:** release notes listed, for every version, all the commits of all previous versions (the 1.3.0 section shipped with 124 bullets, 115 of them repetition of 1.2.0). The cause was not the AI — it was the `git log` range, anchored on `git describe`, which in this repository resolved to `v0.0.8`. The cutoff became the **previous section of the changelog itself**, every bullet gained a **real, clickable** link to commit, PR and contributor, and a commit already present in another section is discarded before becoming a bullet — when nothing is left, the release **aborts** instead of publishing an empty section.
- **Telemetry left the JSON file and became a SQLite ledger:** events were written to per-command files, from a daemon thread, and then "enriched" back through the AI cache via a heuristic match on (repo, branch, action, minute) that depended on three divergent folder maps. The result was predictable: `tokens_actual` **always zero** and data loss between repositories on export. `~/.gitpr/metrics/telemetry.db` writes the row **synchronously**, with tokens copied from the provider metadata at write time, and a short-lived process (a hook, for instance) no longer loses its own row on exit.
- **Mentor Mode — the review that teaches, not just points:** a review says *what* is wrong and almost never *why it matters*. `gitpr mentor` (and the `--mentor` flag on `-r`/`-f`) enriches the findings of the last review with what is going on, why it matters, an everyday analogy and the concepts to study. It is **opt-in** and never invents: with insufficient evidence, the explanation says so instead of filling the void.
- **Deterministic risk score — no AI, no quota:** `gitpr risk` produces a 0–100 score with a `LOW`/`MEDIUM`/`HIGH`/`CRITICAL` level from local signals (critical paths, missing tests, migrations, bug and revert history, static findings). The formula is declared (**50/30/20**), so are the mandatory escalation rules, and the score enters the review prompt as **read-only** context and the report. An explainable number is worth more than an expensive guess.
- **Policy Packs — the repository's policy became a versioned artifact:** skills, linter rules, severities, critical paths, risk weights and PR/commit conventions were scattered and every repository configured them by hand. `gitpr policy` adopts a versioned YAML pack with a **checksum**, resolves precedence between layers and records the choice in the lockfile the team commits. Four built-in packs ship in the wheel; with no active pack, **nothing changes**.
- **Baseline and auditable suppressions — known noise stops blocking:** linters and reviews re-reported the same pre-existing findings on every run, and it was up to the reviewer to tell the output apart in their head. `.gitpr/baseline.json` records the snapshot of accepted findings, every new finding is classified as `new`/`existing`/`resolved`/`ignored`/`accepted_debt`, and **only `new` blocks**. Every suppression carries a reason; accepted debt carries an owner and, when there is one, a deadline.
- **The finding model became a canonical type:** `NormalizedFinding` moved from `infrastructure/linter/external/base_bridge.py` to `src/domain/finding/`. Without that, every bridge — Semgrep, Gitleaks, Bandit, the regex ruleset — would fingerprint a different shape of the same finding and the baseline would have no way to recognize it.
- **The previous window's debt was closed:** `SKILL_LABELS` and `mcp_server.SKILL_FILES` received `tests`, `explain` and now `mentor`; the **40 `__()` keys** that did not exist in any dictionary were translated; and the **4 failures** from the previous window no longer exist. The three assertions that reported the debt are still running — they simply stopped reporting. The suite, measured end to end in this window, closes at **2795 passed out of 2799** — the two remaining failures are an isolation bug in `tests/test_mentor_cli.py`, not a product regression (see §Tests).
- **i18n coverage closed the loop:** **1603 keys** in each of the 6 dictionaries (+445 in this window) and **zero keys used in the code without a translation** — the code uses 1603, the dictionaries have 1603.
- **The pending translations were paid off:** `split-command` and `otimizacao-de-tokens` completed all 5 languages; `review-pr` still has 2. Two PT-only families (`como_reverter_commit_git_localmente`, `testar_sem_usar_pypi`) moved from the top of `docs/` to `docs/extra/`.

- **Current version:** 1.4.0 (**tagged** — `v1.4.0`, merge of PR #207, 2026-10-12; `v1.3.0` on 2026-09-24)
- **Language dictionaries version:** v0.0.34 (chain v0.0.32 → v0.0.33 → v0.0.34 in this window)
- **Hook scripts version:** v0.0.4 (v0.0.3 → v0.0.4 in this window)
- **Distribution:** PyPI (`pip install gitpr-cli`) — binary channel removed
- **Website:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Repository:** [https://github.com/gitpr-cli/gitpr.git](https://github.com/gitpr-cli/gitpr.git)
- **License:** LGPL-2.1
- **Supported languages:** en_us, pt_br, pt_pt, es_es, fr_fr (5 languages, 6 dictionaries)

---

## **🏗️ Architecture and Base Libraries**

* **Language:** Python >= 3.10
* **CLI Framework:** Click (for commands, flags and terminal formatting) — **14 subcommands** 🆕 (`badge`, **`baseline`** 🆕, `config`, `demo`, `explain`, `fix`, **`mentor`** 🆕, **`metrics`** 🆕, **`policy`** 🆕, `release`, `review-pr`, **`risk`** 🆕, `split`, `tests`). Four of them are **groups**: `baseline` (6 subcommands), `metrics` (8), `policy` (7) and `tests` (1).
* **UI/Terminal:** Textual — TUI for interactive chat, issue editing, help screen, metrics dashboard, PR Publisher, linter errors (`LinterApp`), configuration (`ConfigApp`), the `NoticeScreen` modal, the **`gitpr demo` tour** (`src/ui/demo/`) and the **ledger migration assistant** (`src/ui/metrics_migration_app.py`) 🆕.
* **Layered Architecture:** `src/domain/` (pure rules, no I/O) + `src/application/use_cases/` (orchestration, **14 use cases** 🆕) + presentation (CLI/TUI). **8 domain packages** 🆕: `baseline`, `finding`, `linter`, `mentor`, `policy`, `pr`, `risk`, `tests_generation`.
* **Cryptography:** `cryptography.fernet` for local protection of API keys, GitHub tokens and SCM tokens for the forges.
* **Configuration:** `python-dotenv`, `pyyaml` (static linter) + its own declarative schema (`src/config_schema.py`, **80 `ConfigField`** and **16 categories** 🆕).
* **AI Providers:** Integration via the official Google GenAI SDK, OpenAI SDK (DeepSeek) and OpenAI SDK (local Ollama). Configured defaults: `gemini-pro-latest` / `gemini-flash-lite-latest` and `deepseek-v4-pro` / `deepseek-v4-flash`.
* **Forge APIs:** `requests` (REST) — multi-forge abstraction in `src/infrastructure/scm/` (GitHub, GitLab, Bitbucket, Azure DevOps) + **`web_links.py`** 🆕 (commit, PR and profile URLs per forge, pure functions); `src/github_api.py` kept as a deprecated shim.
* **Linter / SAST:** YAML regex + Checkstyle bridge + built-in secrets ruleset (`src/security_ruleset.py`) + 4 bridges in `src/infrastructure/linter/external/` (base + Semgrep, Gitleaks, Bandit), with the normalized model in `src/domain/linter/sast_finding_mapper.py`. 🆕 The normalized finding now lives in **`src/domain/finding/`** and there is a **`findings_out` side channel** that exposes the structured findings alongside the alert strings.
* **Git Internals:** `src/infrastructure/git/` — `patch_applier.py`, `selective_stager.py`, **`identity.py`** 🆕 (repo, branch and author label in a single git call), **`risk_history_reader.py`** 🆕 and **`test_matcher.py`** 🆕.
* **Local persistence 🆕:** `src/ledger.py` — SQLite (`~/.gitpr/metrics/telemetry.db`) with WAL, `PRAGMA user_version`, UUID keys and transport by bundle.
* **MCP:** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 — **16 annotated tools** 🆕, **19 resources** 🆕, 7 prompts.
* **Tests:** Pytest + `unittest.mock` (**129 test modules** 🆕 — 49 at the root, 11 in `tests/application/use_cases/`, 10 in `tests/fix/`, 9 in `tests/scm/`, 7 in `tests/badge/`, 6 in `tests/demo/`, 6 in `tests/split/`, 5 in `tests/domain/baseline/`, 4 in `tests/domain/policy/`, 4 in `tests/review/`, 4 in `tests/infrastructure/linter/external/`, 2 in `tests/domain/risk/`, 2 in `tests/domain/tests_generation/`, 2 in `tests/integration/`, 2 in `tests/infrastructure/git/`, 1 in `tests/domain/finding/`, 1 in `tests/domain/mentor/`, 1 in `tests/domain/pr/`, 2 in `tests/domain/linter/`, 1 in `tests/infrastructure/baseline/` —, **2799 scenarios collected**) + e2e tests of the MCP server via a real subprocess (JSON-RPC stdio) + real git repository fixtures (`tests/fix/`, `tests/split/`) + **network isolation guard** (`tests/demo/conftest.py`, `tests/integration/conftest.py`).
* **Packaging:** setuptools/build (PyPI) — `version = {attr = "src.updater.__version__"}`; **packs embedded via `package-data`** 🆕 and the new dependency **`packaging>=23.0`** 🆕.
* **CI/CD:** GitHub Actions — `pr-review.yml` + `action.yml` + `tests.yml` (Python 3.10/3.13 matrix).

---

## **🧩 Implemented Modules and File Architecture**

### **1. Core and Git Operations (`src/core.py`)**

* **Structured Generation:** Communicates with the LLM requesting a strictly JSON response.
* **Map-Reduce (Giant Diffs):** When the diff exceeds ~90k tokens, it automatically splits into per-file batches (`split_diff_into_chunks`), processes each part (Map) and unifies the summaries (Reduce). Supports PRs, commits and Issues. 🆕 `provider`/`model` are now **aggregated across the chunks** and `meta`/`modules` are passed on to `log_command_metric`, which now copies real tokens from the provider metadata at write time.
* **Local Tokenizer:** `tokenizer.json` for precise token estimation before sending to the AI.
* **Token Estimation:** Lightweight heuristic `len() // 4` via `estimate_token_count()` with a fallback to the local tokenizer.
* **Native Git Optimization:** `-U1`, `-w`, `-M`, `-B` flags on the `get_git_diff` and `get_git_full_diff` commands to reduce useless context.
* **`get_split_diff()`:** `split`'s own diff, with `SPLIT_DIFF_ARGS` (`--binary -M -U3`), **deliberately without** reusing `get_git_diff()` — `-w` (ignore whitespace) and the smart-excludes would break the byte-identical tree guarantee.
* **Pre-Save (`--pre-save`):** Hidden debug flag that saves the full payload (system instruction + prompt) as JSON before every AI call.
* **Two-Layer Smart Excludes:** Global layer (`~/.gitpr/conf/`) + project-local (`./.gitpr/conf/`), merged at runtime. Auto-seeding of the local file on first run. `_load_smart_excludes()` accepts `force=`.
* **Metrics with Time Tracking:** `log_command_metric()` in every flow, now with `meta`, `modules` and `duration_ms`. 🆕 Two divergent copies of the command→cache folder map were **removed** from here — they were the cause of `tokens_actual` always being `0`.
* **Centralized Output Resolution:** `resolve_output_path()` — defaults to `.gitpr/reports/{type}/`.
* **SCM Wizard (`run_scm_init_wizard()`)**: `gitpr --init` — detects the forge from the origin remote, requests per-forge extras, validates the token and persists **only on success**; announces the automatic badge on the PR path.
* **Release Skill Template (`ensure_release_skill_template()`)**: Downloads `templates/gitpr.release.*.md` on the first use of `gitpr release`.
* **Shared Skill Registry:** `SKILL_FILES_BY_TYPE` / `SKILL_TYPES` / `DEFAULT_SKILL_TYPE` in `src/config.py`. 🆕 It gained the **`mentor`** entry (`.gitpr.mentor.md`) — `tests` and `explain` were already there in `tests/test_skill_context.py`.
* **Review Engine with Cache Scope (`generate_pr_content`):** `cache_scope` (attached **only to the cache key**) and `store_diff` (writes the reviewed diff). 🆕 `cache_scope` now also carries the **active policy pack**, so that two repositories under different policies do not share a response.
* **Co-authorship Trailer:** `COAUTHOR_TRAILER` + `append_coauthor_trailer()` — idempotent, preserves third-party trailers.
* **Hardened Subprocesses:** `stdin=subprocess.DEVNULL` + `encoding='utf-8'`/`errors='replace'` on every `subprocess.run`; connection check via the `8.8.8.8:53` socket before network operations.

### **2. Global Plugin System (`src/plugins.py`)**

* **Plugin Architecture:** Loads plugins from `~/.gitpr/plugins/` applying to **all projects**.
* **Linter Plugins (`linter/`):** `.yml` files merged with the local `.gitpr.linter.yml`.
* **MCP Prompt Plugins (`prompts/`):** `.md` files that extend the system context.
* **Factory Closures:** `get_linter_plugins` and `get_prompt_plugins` isolate state between sessions.
* **`--plugins` Command:** Lists all installed global plugins with types and paths.
* **Multilingual Documentation:** `docs/plugins-system.md` in 5 languages.
* 🆕 **Relationship with Policy Packs:** the pack is a **third** layer of rules — plugins belong to the machine, `.gitpr.linter.yml` belongs to the project and the pack is versioned and committed. `load_linter_rules()` merges the three in declared order, with the pack's severities on top.

### **3. CLI Interface and Setup (`src/main.py` and `src/config.py`)**

* **Initial Setup:** Detects the first run, creates `~/.gitpr/` and interactively requests keys, preferences and language. The first-run hint points to `gitpr demo`.
* **Command Routing:** Manages all the flags and the **14 subcommands** — `release`, `config`, `fix`, `review-pr`, `demo`, `badge`, `split`, `tests`, `explain`, **`mentor`** 🆕, **`metrics`** 🆕, **`policy`** 🆕, **`risk`** 🆕 and **`baseline`** 🆕.
* **Default Behavior:** Running `gitpr` with no flags opens the PR Publisher TUI.
* **Flags (now 32 Click options at the root — 5 left, 1 entered in this window):**
  * **Removed 🆕:** `--metrics`, `--export`, `--purge`, `--hook-event` and `--dashboard` — they became the `gitpr metrics` group (see §36). **Declared CLI break.**
  * **`--mentor` 🆕:** Appends the didactic Mentor section to the code review (`-r` / `-f`).
  * `--explain`, `--init`, `--no-suggest-reviewers`, `--no-publish`, `--no-edit`, `--base <branch>`, `--plugins`, `--status`, `--no-unstaged-check`, `--linter-setup`, `--version` — unchanged.
* **`baseline` Group 🆕 — 6 subcommands:** `create` (`--refresh`, `--base`, `--yes`, `--format`), `show` (`--status`, `--rule`, `--file`, `--format`), `validate` (`--format`), `update` (`--recompute`, `--base`, `--yes`), `suppress` (`--reason`, `--scope`, `--debt`, `--owner`, `--due-date`, `--yes`) and `unsuppress` (`--yes`).
* **`policy` Group 🆕 — 7 subcommands:** `list`, `show`, `validate`, `use`, `off`, `install` (`--force`, `--yes`) and `init` (`--stack`, `--yes`).
* **`metrics` Group 🆕 — 8 subcommands:** `export`, `bundle`, `merge`, `dashboard`, `migrate`, `prune`, `purge` and the hidden `hook-event`; the read ones share `--days`/`--since`/`--until`.
* **`risk` Subcommand 🆕 — 4 options:** `--file`, `--format text|json`, `--base`, `--no-history`.
* **`mentor` Subcommand 🆕 — 2 options:** `--finding`, `--provider`.
* **Environment Variables (58 keys in `DEFAULT_CONFIG`, +9 in this window):** the four `GITPR_BASELINE_*` (`ENABLED`, `PATH`, `REQUIRE_LOCKFILE_CHECKSUM_MATCH`, `ALLOW_LOCAL_OVERRIDES`), the two mentor ones (`GITPR_REVIEW_MENTOR_MODE`, `GITPR_MENTOR_INCLUDE_ANALOGY`), the two policy ones (`GITPR_POLICY_ENABLED`, `GITPR_POLICY_CONTEXT_MAX_CHARACTERS`) and `GITPR_RISK_INCLUDE_IN_REVIEW`. **Outside `DEFAULT_CONFIG`:** `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT` and `GITPR_METRICS_CURRENCY`, read by `get_model_price()`.
* **Contextual Help:** `-h --flag` displays flag-specific documentation with a language-aware link; subcommands have their own `epilog=` (Click's `\b` paragraph). 🆕 `risk`, `policy`, `baseline` and `mentor` entered `HELP_MAP`/`HELP_PRIORITY`.
* **`.env` Writing Layer:** `read_env_file_values()` reads **only the file** via `dotenv_values`; `save_config_values()` with `set_key`; `remove_config_value()` with `unset_key`. `validate_ai_key()` distinguishes a refused credential (`401`/`403`) from an unreachable network.
* **Baseline Gate in the Flow 🆕:** `main.py` resolves the baseline gate before lint, review, full review, file audit, remote review and risk, and the **lint exit code now follows the baseline-aware blocking** — a new finding fails, a known finding does not.

### **4. PR Publisher TUI (`src/ui/pr_publish_app.py` and `src/ui/pr_publish_help.py`)**

* **Complete Interactive Interface:** TUI to review, edit and publish Pull Requests straight from the terminal.
* **7 Modal Screens:** `StageFilesScreen`, `CommitConfirmScreen`, `CommitProgressScreen`, `CommitMessageScreen`, `LinterErrorScreen`, `ErrorScreen` and `NoticeScreen`.
* **Badge in the PR Body:** `attach_pr_badge()` runs in `src/main.py` right after `pr_data` is complete — **single injection point**. Idempotent append by marker and explicit disclosure on `--no-edit`.
* **`_attach_reviewers` with resolution:** Resolves what the user typed **before** sending, reports what was discarded and, on a batch `422`, **retries one by one**.
* **Suggested Reviewers:** Queries the forge for suggested reviewers; `_reviewer_suggestion_view()` assembles `resolutions` via `resolve_candidates` and pre-fills `handles`.
* **Bindings:** F1 (Help), F2 (Save local .md), F3 (Publish via forge), Esc (Quit).
* **Auto-Commit Flow:** Linter → AI message → confirm → commit → push → publish PR. 🆕 The linter result now carries the **structured findings** (`findings_out`), which is what the baseline classifies before the report is composed.
* **Existing PR Handling / Auto-Upstream / Merge Flow** — unchanged (`GITPR_AUTO_MERGE`).

### **5. GitHub API Module (`src/github_api.py`)**

* **Deprecated Shim:** `create_pull_request()`, `update_pull_request()`, `merge_pull_request()` and the other functions delegate to `src/infrastructure/scm/github_provider.py`; it emits a `DeprecationWarning` and keeps the legacy `(ok, data, status)` tuples — no new code may import it.

### **6. Static Analysis Engine / Linter (`src/linter_engine.py`)**

* **Offline Linter:** Statically analyzes the added (`+`) lines in the diff without spending AI quotas.
* **YAML Rules:** Reads `.gitpr.linter.yml` (created via `--skill`).
* **Linter Plugins:** Additional rules from `~/.gitpr/plugins/linter/*.yml`.
* **Extension Wildcard:** `extensions: ["*"]` means **every** file, including those without a suffix and dotfiles.
* **Built-in Secrets Ruleset:** `load_linter_rules()` merges `src/security_ruleset.py` **after** the project rules and the plugins. Seven rules, all `extensions: ["*"]`: five `error` and two `warning`. The alert **never echoes the value** — the message carries only `{file_name}` and `{line_number}`.
* 🆕 **Policy Pack Layer:** `load_linter_rules()` now applies the layered merge of the active pack and **overrides severities**, without touching `.gitpr.linter.yml` or the plugins.
* 🆕 **Structured Findings (`findings_out`):** besides the alert strings, the engine returns the normalized findings of the canonical type `src/domain/finding/` — that list is what the baseline fingerprints. It includes the fix for the new-side line counter, so that context lines advance correctly.
* **Opt-In SAST Bridges:** Semgrep, Gitleaks and Bandit run on the whole-file path **and** on the diff path, with `[Gitleaks + Regex]` dedup, value masking (`AKIA****`) and a warning when the tool is enabled but not on `PATH`.
* **`skip_external`:** `parse_diff_and_lint(..., skip_external=True)` in the remote flow, because the bridge runs binaries against files on disk — the local tree, not the PR.
* **Consolidated Report:** `generate_linter_report_content()` consolidates regex + external errors into `.gitpr/reports/linter/` — generated only when there are violations.
* 🆕 **Baseline-aware:** the alerts are annotated with the baseline status before becoming a report, and the lint flow's exit code follows the baseline-aware blocking.

### **7. Security and Authentication (`src/security.py`, `src/config.py`, `src/tui_issue.py`)**

* **Cryptography:** Generates the `secret.key` master key in `~/.gitpr/`.
* **Token Protection:** `encrypt_data`/`decrypt_data` for AI keys, GitHub PAT and SCM tokens for the forges.
* **Multi-Forge Validation:** `validate_or_request_scm_token(provider, repo_display)` — 401 → reauthentication loop preserving the draft.
* **Secrets in the Configuration TUI:** `KIND_SECRET` fields are edited masked, **never** display the value in the clear and are encrypted with Fernet before writing.
* **Secrets Scanning in the Flow:** The linter — which runs in the pre-commit hook — is a credential gate with identical rules on every machine.
* ⚠️ **Coverage gaps confirmed as still open in this window:** the generic rule still requires quotes after the separator (`\s*[:=]\s*['\"]`), so the `.env` format **without quotes** (`API_KEY=abc123`) is still not caught; the `ASIA…`, `github_pat_…` and `xoxc-`/`xoxd-` prefixes are still out (the pattern is `gh[pousr]_` and `xox[baprs]-`); and there is no left boundary on the key name, so `mytoken` matches the same as `token`.

### **8. Auto-Updater (`src/updater.py`)**

* **PyPI as the Single Source:** `get_latest_remote_version()` queries `https://pypi.org/pypi/gitpr-cli/json` and writes the daily cache **without** the `download_url` field.
* **Mandatory Gate (`enforce_update_required()`):** Returns `True` (after printing both versions and the pip command) when the published version is newer; `False` when it is up to date, when the remote version is **unknown (offline)** or when the check is disabled.
* **`check_and_update()`:** Only queries and **informs**, never installs.
* **Escape Hatch:** `GITPR_SKIP_UPDATE_CHECK` — not advertised; it exists for the suite and offline automation.
* **Centralized Versioning:** `__version__` (**1.4.0** 🆕), `__lang_version__` (**v0.0.34** 🆕 — chain v0.0.31 → **v0.0.32** (release notes) → **v0.0.33** (mentor) → **v0.0.34** (release bump)), `__scripts_version__` (**v0.0.4** 🆕), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`, `LINTER_PRESETS_VERSION`.
* 🆕 **In this window the three bumps left the working tree and became a commit:** `af35270` raised GitPR, languages and scripts together, and `CHANGELOG.md` stopped announcing a version the package did not build.
* ⚠️ **Pending fix in the working tree:** the section headings of `[1.4.0]` came out localized (`Funcionalidades`, `Documentação`, `Tarefas`, `Contribuidores`) and were corrected to English **without a commit** — it is the only open change in the repository today.

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
* **Versioned Files:** `__lang_version__` (**v0.0.34**) controls the update of the packages (`langs/*.json`).
* **Coverage:** **1603 keys** in each of the 6 files — full key set parity **and full code coverage**: the code uses **1603** keys and **none** is left untranslated (+445 in this window; the 40-key debt from the previous window was closed). ✅
* **Environment Snapshot (`AMBIENT_ENV_KEYS`):** `frozenset(os.environ)` captured **immediately before** the module-level `load_dotenv()`.
* **Cache with Language Indexing:** Cached AI responses include the current language in the MD5 keying.
* **Lockstep Translation Source:** a new key must exist in the code, in `langs/pt_br.json` (master list), in the FR/ES dicts of `scripts/sync_all_langs.py` and in the curated values of `scripts/fix_mangled_i18n_keys.py`.

### **11. Animated Spinner (`src/spinner.py`)**

* **Braille + Thinking Words:** Background thread during AI calls with braille characters and "thinking" words.
* **263 entries per language:** Synchronized across the 5 languages. `_load_thinking_words()` / `reload_thinking_words()` accept `force=`.

### **12. AI Providers (`src/ai_providers.py`)**

* **3 Supported Providers:** Google Gemini, DeepSeek and Ollama (local), with a primary/secondary per provider.
* **JSON Mode & Deterministic Parameters:** `temperature=0.0` and `top_p=0.1`; automatic fallback between configured providers.
* **AI Timeout:** default of **180s** (`GITPR_AI_TIMEOUT`).
* 🆕 **Calls with `quiet`:** release and the new features forward `quiet` down to the AI layer, so the spinner never corrupts a `--format json` output.
* 🆕 **One call at a time, with cache:** mentor and explain reuse the MD5 cache per prompt — running again over an unchanged review costs nothing.

### **13. Smart Cache (`src/cache.py`)**

* **MD5 + Metadata:** Keying by MD5 hash of the diff and prompt, with language indexing.
* **Last Review Selection (`resolve_last_review()`):** Picks the **newest** `review`/`fullreview` record for a repo+branch pair — it is the entry point of `gitpr fix` and, now, of `gitpr mentor` as well.
* **Reviewed Diff (`reviewed_diff`):** A field at the top of the record that stores the diff actually reviewed, preferred by `fix/apply_fix.reviewed_diff()`.
* **Telemetry and Duration:** Persistence of `duration_ms` and `meta_raw`.
* **Reading for the Dashboard:** `scan_cache_files_for_dashboard()` was **demoted to a pre-migration bridge** — the dashboard reads the ledger, and the scan exists only to rebuild the series from the cache when the user chooses that in the migration assistant.

### **14. Issue Engine and TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Context Engines:** Current diff, branch history (`-ht`) and Blame archaeology (`-b`).
* **Multi-Forge Publication:** F3 creates the issue on the **configured** forge (`GITPR_SCM_PROVIDER`) via `provider.create_issue` — Azure DevOps raises `ScmNotSupportedError`.
* **Map-Reduce for Issues:** Context above ~90k tokens is split and unified.
* **401 Handling:** Reauthentication signaling without closing the application.

### **15. Code Archaeologist (`src/blame_engine.py`)**

* **Git Blame + AI:** Tracks evolution and historical authorship with commit classification (`ORIGIN` vs `REFACTORING`).
* **Blame Metrics:** Events via `log_blame_metric()` with depth and number of commits analyzed.
* 🆕 **It now also feeds risk:** the score's history reader (`risk_history_reader.py`) uses the same 90-day window and classifies bugs/reverts by pattern, without calling the AI.

### **16. MCP Server and Direct CLI Invocation (`src/mcp_server.py`)**

* **16 Annotated MCP Tools 🆕:** the previous 14 + **`get_baseline_status`** 🆕 (the baseline digest: counts by status, top rules, suppressions and accepted debt with owner and deadline, overdue debt, checksum state — **never runs the linter, never calls the AI, never writes) and **`get_usage_metrics`** 🆕 (the usage ledger over a window: runs, cost per model, modules touched, providers and quality rates; no day shortcut — the caller names its own dates).
* **19 Resources + 7 Templated Prompts 🆕:** `skill://list` + `skill://{pr,commit,review,filereview,issue,blame,release,fix}` + `linter://config` + **`baseline://summary`** 🆕 + `prompt://list` + 7 prompts.
* **Aligned Skill Registry 🆕:** `mcp_server.SKILL_FILES` now has **11 entries** — it received `tests`, `explain` and `mentor`. The `TestSkillRegistryAgreement` assertion, which reported the divergence, now **passes**. ✅
* **Direct CLI Invocation:** `gitpr-mcp --tool <name> [--tool-args '<json>']` invokes any tool without starting the stdio server; `gitpr-mcp --list` prints the full registry as JSON.
* **Real Stdout Isolation:** `_write_real_stdout()` writes to the original `sys.__stdout__`, with a fallback to `buffer` or re-encode with `errors='replace'` on legacy Windows code pages.
* **Event Loop Offload:** `_offload` decorator (`anyio.to_thread.run_sync`) on every tool — the order of the decorators matters (`@mcp.tool` **above** `@_offload`).
* **Failures as Structured Errors 🆕:** the new tools return a failure **as data** instead of raising — an agent asking for the baseline status receives the reason for the failure, not an exception.

### **17. Metrics Dashboard TUI (`src/ui/metrics_app.py`)**

* **Repository Scope:** Label `📁 Repository: owner/repo` and strict filtering by project.
* **Asynchronous Scan with Overlay:** Background worker thread with a `ProgressBar`; the tests wait for `workers.wait_for_complete()` before asserting.
* 🆕 **Ledger Reading:** the dashboard stopped scanning JSON files and now queries `telemetry.db` on the worker thread, rendering the **five new sections** (cost, quality, modules, providers, cycle) from the same renderers the CLI uses.
* 🆕 **Migration Assistant (`src/ui/metrics_migration_app.py`):** offers to import the legacy files, import **and** rebuild from the AI cache, or "not now" — with abort semantics that **never create an empty ledger** over files not yet imported.
* **Local Export:** CSV/JSON into `./.gitpr/metrics/export/` — 🆕 `.gitpr/metrics/` entered `.gitignore` with a comment explaining why `.gitpr/skill/` does **not** (it is project content). The 35 artifacts already tracked remain tracked, because `gitignore` does not untrack.

### **18. Metrics and Telemetry System (`src/metrics.py`)**

* 🆕 **Rewritten in this window — see §36.** `src/metrics.py` stopped writing files and now operates on **ledger rows**; `enrich_metrics_from_cache()` and `load_cache_token_summary()` were **removed** (they were the heuristic match that zeroed `tokens_actual`).
* **Repository Scope:** All events indexed by `repo_name`, now resolved by `src/infrastructure/git/identity.py` — which works on **any** forge, not just GitHub.
* **Hook, Linter and Blame Events:** `log_hook_event()`, `log_linter_metric()`, `log_blame_metric()`.
* **Export and Cleanup:** `gitpr metrics export` (CSV/JSON), `prune`, `purge` with interactive confirmation, `bundle`/`merge` to transport and merge windows.

### **19. Git Hooks Language Synchronization**

* **Independent Versioning:** `__scripts_version__` (**v0.0.4** 🆕).
* 🆕 **Rewritten templates:** `post-checkout`, `post-merge` and `pre-push` now call `gitpr --quiet metrics hook-event <name>`, guarded by `command -v gitpr` and `|| true` — the repository resolution in bash, which was **GitHub-only** and recorded events under unusable folder names, was removed. **Action required: reinstall the hooks with `gitpr --installhooks`.**
* **Suffix Mapping (`HOOK_SCRIPT_SUFFIXES`):** Interface codes (`es_es`, `fr_fr`) translated to the published suffixes (`.es`, `.fr`).
* **Choice vs. State (`SCRIPTS_LANG` / `SCRIPTS_INSTALLED_LANG`):** Enables **detecting a language change**.
* **`effective_hook_lang()`:** Resolves the effective language; `--lang` is no longer discarded on that path.
* **Merge-Source Skip:** `prepare-commit-msg` skips the `message|merge|squash|commit` sources.

### **20. External Linter Bridge and Interactive Assistant (`src/linter_wizard.py`, `src/ui/linter_app.py`)**

* **`--linter-setup` Assistant:** Wizard with numbered presets (PHP_CodeSniffer, ESLint, Stylelint) and injection of the `external_linters` block.
* **Remote Presets:** `templates/gitpr.linter-presets.json` with a local → download → stale → built-in fallback chain.
* **Linter Error TUI:** `src/ui/linter_app.py` (Textual) displays critical errors and warnings; in hook/quiet mode it prints and calls `sys.exit(1)`.
* **Markdown Report:** Consolidated into `.gitpr/reports/linter/` only when there are violations.
* **The SAST Layer Is a Sibling, Not a Replacement:** the Semgrep/Gitleaks/Bandit bridges feed the **same** report pipeline.

### **21. SCM Multi-Forge (`src/infrastructure/scm/`)**

* **Single Abstraction (`ScmProvider` ABC):** `base.py` defines the contract (dataclasses `RepoRef`, `PullRequestDraft` etc. and `ScmProviderError(provider, http_status, message)` — `http_status` 0 = network failure).
* **Registry and Factory:** `resolve_scm_provider()` selects by `GITPR_SCM_PROVIDER` (default `github`); `detect_provider_from_remote()` identifies the forge from the origin URL.
* 🆕 **`list_pull_requests(repo, state, since, until)` — paginated and canonical:** the four providers translate the canonical states (`open`/`merged`/`closed`/`all`) into the native vocabulary, paginate on each API's cursor (Link, `X-Next-Page`, continuation token, next-URL), filter by an inclusive date window and stop early when they can. **`list_open_pull_requests(repo)` became a thin wrapper** on the base class — no call site changed, but the one-page limit is gone.
* 🆕 **Why the filter is by state date:** three of the four forges return something broader for a merged PR (GitHub has no `merged` state, Azure calls it `completed`, Bitbucket says `DECLINED`), so each one filters its own response against the date the state implies — `merged_at` for merged, `closed_at` for closed, `created_at` for the rest.
* 🆕 **`supports_merged_dates`:** class attribute, `False` on Bitbucket Cloud, which publishes `merged_at` **empty** instead of a nearby date — declared **before** the call, like `supports_reviewable_diff`.
* 🆕 **`web_links.py` — GitPR started building HTML links:** until this window every link came ready in the payload's `html_url`. The pure functions (`repo_web_base`, `commit_url`, `pull_request_url`, `user_url`) normalize any remote format — `https`, `ssh://`, `git://`, scp-like, with credentials, custom port and **nested GitLab subgroups** — into an https base and apply each forge's page pattern. Azure DevOps has no simple profile URL, so its contributors keep the display name.
* **`get_pull_request(repo, pr_id)` — concrete method of the ABC:** the default raises `ScmNotSupportedError` and each forge implements it — `list_open_pull_requests` pages only one page and does not distinguish closed from nonexistent.
* **Synthesized GitLab Headers:** `changes[].diff` is a **loose hunk**; `old_path`/`new_path` build the `diff --git a/… / --- / +++` headers, honoring `/dev/null`. And `overflow: true` **raises** instead of reviewing half of a truncated diff.
* **`request_pull_request_reviewers` returns `list[str]` (contract break):** Returns the logins **effectively attached**, read from the `201` body. Third-party subclasses need updating — **inherited item, still undocumented.**
* **GitHub Read-Only Helpers:** `get_commit_author_login` (SHA → account linked to the author's e-mail) and `get_user_login` (validates/canonicalizes a handle without spending a request when it cannot be a login).
* **Per-Forge Fail-Fast:** Azure requires `GITPR_SCM_ORGANIZATION`/`GITPR_SCM_PROJECT`; Bitbucket requires `GITPR_SCM_USERNAME`; `create_issue` on Azure raises `ScmNotSupportedError`.
* **Release Publication:** `provider.create_release()` used by `gitpr release --publish`.
* **Artifacts:** Glossary + ADR-001/ADR-005; the `docs/scm-multiforge.*.md` family in 5 languages; 9 test files, **305 scenarios** 🆕.

### **22. `gitpr release` Subcommand — Changelog / Release Notes (`src/release_engine.py`)**

* **Flow:** `git log` between `--since` and `HEAD` → classification by Conventional Commits → suggested semantic bump (`--version <x.y.z>` overrides) → changelog assembly → optional AI executive summary → *prepend* into `CHANGELOG.md`.
* 🆕 **The Range Stopped Coming from `git describe`:** `describe --tags --abbrev=0` only sees tags reachable from HEAD — in this repository it resolved to `v0.0.8`, so **every release listed all previous versions**. The `[1.3.0]` section shipped with **124 bullets, 115 of them repetition of 1.2.0**, and the suggested bump could be **lower** than the already published version. The range's origin became the **previous section of the changelog itself**, through two anchors: the newest hash of that section (resolved in a single `git log --no-walk`, sorted by epoch to survive mixed time zones) and, as a fallback, the tag of the section's version; with neither, it degrades to the last tag **with a visible warning**.
* 🆕 **`_drop_released()` — the second line of defense:** any commit whose short hash appears in **another** version section is discarded, with a count. When nothing survives, the run **aborts** with `❌ Nothing new to release` instead of writing an empty section.
* 🆕 **Every bullet became a link:** `- {subject} ([{short_hash}]({commit_url})) — {scope} · [#{n}]({pr_url}) · {YYYY-MM-DD}`. The PR number comes from the trailing `(#123)` of the squash-merge, which remains in the subject; without a forge or without a token, it degrades to plain text.
* 🆕 **The failure of a PR merged by merge commit — fixed by construction:** `git log --no-merges` means a merge commit **never** reaches the classifier, so `pr_number` was `None` **by construction** and no changelog ever had a `[#n]` segment. A **second pass over the same range** (`git log --merges`) builds `hash → PR` from ancestry (`rev-list <merge>^1..<merge>^2`): bullets remain one per commit, now with the link. A merge that is not a pull request (`Merge branch 'main' into …`) does not lend a number, a commit reachable by two merges keeps the closest one, and the fix **is not retroactive** — only a freshly cut section gains links.
* 🆕 **Contributors resolved by the same ladder as the reviewers:** `get_commit_author_login` first (it sees private addresses that the user search does not), then `email_to_handle`, cached in `~/.gitpr/cache/contributors.json` — **only successes are cached**, so a rate-limited run tries again later.
* 🆕 **The Section Boundary Was a Latent Bug:** `upsert_changelog(force=True)` replaces from the `## [x.y.z]` line up to the next **level-2** header. A `###` as a boundary left the old body alive **above** the new one; the `### Summary` and the contributors footer were left behind.
* **Classifier (`src/commit_classifier.py`)** and **Builder with Translatable Sections (`src/changelog_builder.py`)** — 🆕 the builder gained the `LinkContext` dataclass and the classifier gained `pr_number_from_merge()`, which understands GitHub, GitLab, Bitbucket and Azure DevOps merges.
* **Semantic Bump (`src/version_bump.py`)** and **Publication** (`--publish`, `--draft`, `--format markdown|json`, `--force`). **6 options in the subcommand.**
* **Declared breaks:** `ReleaseNotesResult` gained the required `previous_version` field; `build_release_section` gained the optional `links` parameter (backward compatible).
* **Artifacts:** `docs/release-notes.*.md` — the family already existed in 5 languages and was **rewritten in this window** for the fixed engine 🆕 —, ADR and `tests/test_web_links.py`.
* **State of the 1.4.0 release:** `CHANGELOG.md` has the complete `[1.4.0] - 2026-10-12` section — the five features of this window **plus** the 1.3.0 note, with links. It is the first section generated by the fixed engine.

### **23. `gitpr config` Subcommand — Configuration TUI**

* **Master-Detail Screen (`src/ui/config_app.py`):** Categories on the left, fields on the right, edited inline. Header with search (`/`) and a pending-changes counter; footer `F1 Help · F2 Save · ^R Restore · / Search · Esc`. `ENABLE_COMMAND_PALETTE = False`.
* **Declarative Schema (`src/config_schema.py`) — the single source of truth:** **16 categories** 🆕 (+2: **Policy** and **Baseline**) and **80 `ConfigField`** 🆕 (+9), of which **8 advanced**. Each field declares category, widget type, `show_if`, validators, version markers and download actions.
* **`policy` and `baseline` Categories 🆕:** the pack fields (enablement and character ceiling of the injected context) and the four baseline ones, including the file path and the switch that requires the lockfile checksum.
* **Aligned Skills Section 🆕:** `SKILL_LABELS` went from 8 to **11 entries** — it received `tests`, `explain` and `mentor`. The two types that rendered **blank** in the sidebar now have a label, and the two assertions of `test_config_schema.py::TestSkillsSection` **pass**. ✅
* **Context Filtering (`show_if`):** `GEMINI_*`/`DEEPSEEK_*`/`OLLAMA_*` according to `DEFAULT_AI_PROVIDER`; `GITPR_SCM_USERNAME` under `[Bitbucket]`; `GITPR_SCM_ORGANIZATION`/`PROJECT` under `[Azure DevOps]`.
* **Global Search (`/`):** Matches key or label across all categories and **ignores the visibility filter**.
* **Two-Layer Validation:** **Offline** blocks `F2` with an inline error; **online** (only credentials changed in the session) runs on a worker with a 10s timeout and blocks only on `401`/`403`.
* **Restore (`Ctrl+R`):** Removes the `.env` line instead of rewriting the default; `Esc` with pending changes asks for confirmation.
* **Skills Section — the only project-scoped one:** Edits the project's `.gitpr/skill/*.md` resolved from the calling directory, writing atomically and preserving CRLF/LF.
* **Forced Downloads:** Buttons that force a re-download of smart-excludes, translations, presets and thinking words via `force=`.
* **Lightweight Links Module (`src/doc_links.py`):** `doc_url()` moved out of `core.py` so the UI can get the link without importing AI SDKs.
* **Tests:** 5 files — `test_config_app.py` (78), `test_config_schema.py` (42), `test_config_validation.py` (40), `test_config_store.py` (22), `test_config_cli.py` (9). 🆕 The suite now **stabilizes the environment** before mounting the TUI — without that, a `GITPR_BASELINE_*` leaked from the shell changed the category tree.
* **Known debt:** `gitpr -h config` opens the TUI and ignores `-h` — the gate `if ctx.invoked_subcommand is not None: return` (main.py:678) runs before the `help_flag` block.

### **24. General Usage Log (`src/usage_log.py`)**

* **One Line per Command:** Writes `~/.gitpr/logs/<uuid5>.log`, **one file per day**, with command, arguments, repository, user and timestamp.
* **Date-Derived Name:** `uuid5(NAMESPACE_DNS, f"gitpr.usage.{YYYY-MM-DD}")` — two concurrent processes cannot disagree about which file is today's.
* **Synchronous Write (explicit decision):** Unlike `log_local_metric`, which used a daemon thread and lost the write if the process exited first.
* **Never Prints:** The MCP server reserves stdout for JSON-RPC; the module also never raises.
* **A Single Git Spawn:** `git config --get-regexp '^(remote\.origin\.url|user\.name|user\.email)$'` — ~40 ms instead of ~150 ms on Windows, on *every* run.
* **Control:** `GITPR_SHOW_LOGS` (default `"true"`); disabled in `tests/conftest.py`.
* **Artifacts:** `docs/usage-log.*.md` in 5 languages; `tests/test_usage_log.py` (27 scenarios).

### **25. `gitpr fix` Subcommand — Review Findings as Reviewable Patches (`src/fix/`)**

* **The Real Pipeline:** last review from the cache (`resolve_last_review`) → **one** AI call → unified diff extraction and validation → `git apply --check` → deterministic classification → dry run or write → history.
* **8-File Package:** `patch_provenance.py`, `patch_extractor.py` (shared with the chat), `patch_safety_classifier.py` (pure, no I/O), `patch_applier.py` (shim), `fix_history.py`, `apply_fix.py`, `rollback_fix.py`, `__init__.py`.
* **Deterministic Classification:** Refuses a patch that spans more than one file or hunk, touches a sensitive path, blows the line budget, **deletes a line that looks like a call**, comes marked as low confidence or fails `git apply --check`. `--force` **never** bypasses applicability, only the classification.
* **Opt-In Writing:** Dry run is the default; any mutation requires `--apply` (or a typed phrase with `--force`).
* **Tests:** 10 files in `tests/fix/` (**204 scenarios**) with real git repository fixtures.

### **26. `gitpr review-pr` Subcommand — Remote PR Review (`src/review/`)**

* **What it is:** Orchestration, **not** a second review engine — the existing engine, the linter and the renderer fed with a diff that came from elsewhere.
* **5-File Package:** `diff_source.py`, `diff_normalizer.py`, `render.py`, `remote_pr.py`, `__init__.py`.
* **Read-Only by Default:** `--post-comment` is the **only** path that writes to the forge. The MCP tool `review_remote_pr` does not even receive the argument.
* 🆕 **The baseline is resolved before the first network call**, and the gate warnings are merged into the report/comment — a remote PR is judged by the same policy as the local repository.
* 🆕 **`render.py` gained the risk parameter:** `compose_review_content` / `render_review_result` **prepend** the `⚡ Risk Assessment` section above the linter alerts, when `GITPR_RISK_INCLUDE_IN_REVIEW` is on.
* **Artifacts:** `docs/review-pr.md` + `.pt_br.md` (the 3 remaining languages **are still pending**), `docs/code-review-ia.*.md` (5), ADR-005 and `glossary-review-pr.md`.
* **Tests:** 4 files in `tests/review/` (**97 scenarios**).

### **27. Reviewer Identity Resolution (`src/reviewer_resolution.py`)**

* **The Bug (two silent failures chained):** (1) **Empty prefill** — `handles` with only `email_to_handle()`; (2) **Unverified attach** — values passed through *verbatim*, GitHub answered **201 without attaching anyone** and `_request()` returned without raising.
* **Module:** Pure plan, no I/O of its own, **and it never raises**. `resolve_candidates()` (before the TUI) and `resolve_typed_reviewers()` (on attach); `match_candidate()` matches exactly and normalized by login, name or e-mail.
* **Resolution Ladder:** known handle (no request) → exact match with a suggested person → lookup by e-mail → login validation on the forge.
* **What does not resolve is never sent:** It leaves in `ResolutionOutcome.dropped` as `(value, i18n_reason)` and is discarded with a visible warning.
* **The Same Ladder Serves the Release 🆕:** `release_engine` reuses `get_commit_author_login` + `email_to_handle` to turn commit e-mails into clickable logins.
* **Tests:** `tests/test_reviewer_resolution.py` (18) + expansions in `test_github_provider.py` (72), `test_pr_publish_app.py` (45), `test_suggest_reviewers.py` (17), `test_reviewer_suggestion.py` (18) and `test_main_suggest_reviewers.py` (8).

### **28. `gitpr demo` Subcommand — Guided Tour over Recorded Examples (`src/demo/`)**

* **What it is:** An interactive tour that shows the three main GitPR outputs — commit message, review and PR description — **with no API key, no git repository and no network**.
* **Real Pipeline, Fake Response:** `FakeAIProvider` mirrors `src.ai_providers.call_ai_model` **argument by argument**; `demo_pipeline()` patches `src.core` and `src.metrics` so that the production pipeline runs **untouched** and only the source of the response is swapped.
* **Isolation is a Requirement, Not a Detail:** The tour **cannot** touch the user's `~/.gitpr` or the network — guaranteed by `tests/demo/conftest.py` (autouse network ban, with a loopback exception for the Windows proactor event loop).
* **Package:** `demo_runner.py`, `fake_ai_provider.py`, `scenarios/` (two examples localized in the 5 languages). The scenarios are **Python modules, not JSON**, so that they travel inside the wheel.
* **TUI:** `src/ui/demo/` with a help modal; the diff is fenced as ```` ```diff ````.
* **CLI:** `gitpr demo` with `--scenario`, `--lang` and `--no-tui`.
* **Tests:** 6 files in `tests/demo/` (**155 scenarios**).
* **Artifacts:** `docs/demo.*.md` in 5 languages, `README.*` (5) and the tour's example metrics.

### **29. GitPR Badge and the `gitpr badge` Command (`src/branding/`)**

* **What it is:** A public badge attesting that a pull request went through a local quality check, and a command that prints the adoption snippet for the project's README.
* **Static URL on Purpose:** It is a shields.io Markdown that GitPR **never** fetches — publishing a pull request cannot start depending on a third party being up.
* **Honest Measurement (`badge_data.py`):** `collect_linter_counts()` returns `None` when no linter rules are configured, because an empty list means "nothing was checked", not "nothing was found".
* **The Badge Never Blocks a Publish:** Every failure path is caught and degrades to "no badge".
* **Single Injection Point:** `attach_pr_badge()` in `src/main.py`. Idempotent append by marker.
* **`gitpr badge` Command:** `--readme` and `--style`. **It only prints** — the README is never modified.
* **Configuration:** `GITPR_BADGE` (default `true`) — **read-only** opt-out.
* **Tests:** 7 files in `tests/badge/` (**83 scenarios**).
* **Artifacts:** `docs/badge.*.md` in 5 languages and the dedicated survey.

### **30. `gitpr split` Subcommand — Atomic Commits by Hunk (`src/split/`)**

* **What it is:** A working tree with several concerns no longer turns into a blob commit. `split` reads the uncommitted diff, asks the AI to partition the hunks by **logical intent** and proposes one atomic commit per concern. The tree is never rewritten: the files end up byte for byte identical to the start.
* **6-File Package:** `split_plan.py`, `hunk_parser.py` (pure), `hunk_grouper.py`, `generate_split_plan.py` (read-only), `apply_split_plan.py` (**the only one that mutates**) and `__init__.py`.
* **The Intact-Tree Guarantee is Structural:** `selective_stager.py` pre-validates **every** group against a temporary index at HEAD and uses a disposable `GIT_INDEX_FILE`.
* **Destructive to the Index, Not to the Tree:** `--apply` resets the index to HEAD before staging, so whatever the user had already staged is unstaged — documented explicitly.
* **Configuration:** `GITPR_SPLIT_MAX_GROUPS` (5), `GITPR_SPLIT_MAX_HUNKS` (50) and `GITPR_SPLIT_REQUIRE_CONFIRMATION` (`true`). Zero or negative values are **ignored in favor of the default**.
* **Tests:** 6 files in `tests/split/` (**101 scenarios**).
* **Artifacts:** `docs/split-command.md` — 🆕 **now in 5 languages** (the 3 that were missing arrived on 2026-09-24), ADR-006, `glossary-gitpr-split.md`, spec/plan and survey.

### **31. Built-in Secrets Scan (`src/security_ruleset.py`)**

* **What it is:** Seven rules that run on **every** linter invocation, merged at the end of `load_linter_rules()` after the project rules and the plugins.
* **The Seven Rules:** five blocking `error`s — AWS access key ID, GitHub token, Slack token, Google API key and private key block — and two `warning`s: database connection URL with credentials and generic credential assignment (`password = "…"`), the latter behind a placeholder filter (`changeme`, `xxxxxx`, `example`, `dummy`, `sample`, `your_password_here`, `sua_senha`).
* **Why in the Package and Not in the Template:** A secrets gate has to behave **the same** on every machine and in every CI, so the rules live in the package and cannot be replaced by `--skill` or rewritten by the wizard.
* **Coverage Gaps (v1) — confirmed still open 🆕:** **unquoted** assignment is not caught (`API_KEY=abc123`, the `.env` format); the `ASIA…`, `github_pat_…` and `xoxc-`/`xoxd-` prefixes are missing; and there is no left boundary on the key name.
* **Configuration:** `GITPR_LINTER_SECURITY` (default `true`; fail-open opt-out) and `GITPR_LINTER_SECURITY_DISABLED_RULES` (`;`-separated).
* **Tests:** `tests/test_security_ruleset.py` (**61 scenarios**).
* **Artifacts:** ADR-007, `glossary-gitpr-secret-scanning.md`, spec/plan/survey and 3 task reports.

### **32. SAST Bridges — Semgrep, Gitleaks and Bandit (`src/infrastructure/linter/external/`)**

* **What it is:** An **opt-in** layer that plugs third-party security scanners into the existing linter pipeline.
* **Bounded Scope:** They run **only** when enabled and **only** over the files touched by the diff.
* **Common Contract:** `ExternalLinterBridge` (ABC) with hardened `subprocess` execution (`shell=False`, strict timeout, `stdin=DEVNULL`, UTF-8 with `errors='replace'`). 🆕 The `NormalizedFinding` model **left here** for `src/domain/finding/`, and `base_bridge` now **re-exports it** so as not to break existing imports.
* **Deduplication is the Point:** `deduplicate_secret_findings()` merges findings from Gitleaks and from the regex ruleset in the same file and line into a **single** entry confirmed as `[Gitleaks + Regex]`.
* **Masked Secrets:** `mask_secret_value()` guarantees the value never reaches the finding, the log or the telemetry.
* **Three-Layer Resolution:** `load_sast_config()` reads defaults → `.gitpr.linter.yml` (`sast` block, falling back to `linter.external`) → `GITPR_SAST_*` variables. **All default to `false`**.
* **Graceful Degradation:** A tool enabled but missing from `PATH` emits a warning and execution continues.
* **Tests:** 4 files in `tests/infrastructure/linter/external/` (**14 scenarios**) + `tests/domain/linter/test_sast_finding_mapper.py` (2) + **`tests/domain/linter/test_structured_findings.py` (44)** 🆕, which covers the passage of the normalized findings through the lint engine.

### **33. `gitpr tests generate` Subcommand — AI Suite Generation (`src/domain/tests_generation/`, `src/application/`)**

* **What it is:** Generates complete, runnable test files from the current diff, from a specific file or from a review finding, **respecting the repository's convention** (Pest, PHPUnit, Jest, Vitest, Pytest).
* **Domain Layer:** `TestFramework` (enum) and the dataclasses `TestGenerationTarget`, `TestScaffold`, `GeneratedTest`; `detect_test_framework()` detects from the configuration files, manifests and the contents of the test directory; `build_test_scaffold()` computes the conventional destination path per framework.
* **Application Layer (`generate_test_file.py`):** Orchestrates detection, scaffold resolution, prompt, AI, JSON parsing and optional writing. `validate_test_syntax()` runs the local toolchain (`php -l`, `node --check`, `python -m py_compile`) — a validation failure is a **warning**, not an error.
* **Presentation:** `tests` group with `generate` (`--file`, `--finding`, `--framework`, `--apply`, `--provider`); dry run is the default and overwriting an existing test asks for confirmation with **No** pre-selected. The chat delegates `/tests` to the **same** use case.
* **Debt closed 🆕:** the `tests` type is in `SKILL_LABELS` and in `mcp_server.SKILL_FILES`, and the 17 `__()` keys that did not exist were translated. **It still has no topic in `docs/`** — only a skill template and spec.

### **34. `gitpr explain` Subcommand and the `--explain` Flag — Reviewer's Guide (`src/domain/pr/`)**

* **What it is:** A guide centered on whoever is going to **review** — what changes, why it changes, where to focus and what the regression risk is.
* **Domain Layer (`explain_section_builder.py`):** `PrExplanation` / `ReviewerFocusPoint`, the `build_explain_markdown()` renderer and `parse_explain_payload()`, which tolerates non-JSON output and **detects placeholders** `[FILL]`/`[TODO]` to signal insufficient evidence instead of presenting a hollow guide as complete.
* **Application Layer (`generate_pr_explanation.py`):** Provider resolution, key validation, skill context loading (`explain`), prompt, invocation and parsing.
* **Two Entry Points:** `gitpr explain` (`--provider`, missing-diff detection before any AI) and the `--explain` flag on the root CLI, which appends the guide to the PR description body **and** to the JSON payload.
* **Configuration:** `GITPR_EXPLAIN_BY_DEFAULT` (default `false`).
* **Debt closed 🆕:** `explain` is in `SKILL_LABELS` and in `mcp_server.SKILL_FILES`, and the keys were translated. **It still has no topic in `docs/`.**

### **35. Deterministic Suite and CI (`.github/workflows/tests.yml`)**

* **What it is:** The workflow that runs the suite on **Python 3.10** (the floor declared in `pyproject.toml`) and **3.13**, with `fail-fast: false`.
* **Hermetic `tests/conftest.py`:** Pins `GITPR_LANG=en_us`, `GITPR_LINTER_SECURITY=false` and `LANG_VERSION` to the code's version, plus `GITPR_SHOW_LOGS=false` and `GITPR_SKIP_UPDATE_CHECK=true`.
* **Order Matters:** `src.updater` is imported **before** the language variables are set, because `i18n` snapshots `os.environ` into `AMBIENT_ENV_KEYS` at import.
* **Worker Test Stability:** `test_config_app.py` and `test_metrics.py` wait for `workers.wait_for_complete()` before asserting.
* **Workflow Dependency:** The clean runner has no `~/.gitpr`, and `test_demo_isolation.py` asserts that the profile exists — the job creates the directory and an empty `.env` explicitly.
* 🆕 **In this window the suite grew 48% and the failure scenario flipped sides:** the three assertions that existed to report registry debt (`TestSkillsSection`, `TestNoMissingKeys`, `TestSkillRegistryAgreement`) **pass** — the detection mechanism worked and was paid off in the same cycle it was created.

### **36. SQLite Telemetry Ledger (`src/ledger.py`, `src/infrastructure/git/identity.py`) 🆕**

* **The Problem Was Not the Format, It Was the Join:** events were written as JSON files per command, from a **daemon thread** (which a short-lived process — a hook — may not survive), and then reconnected to the AI cache by a heuristic match on `(repo, branch, action, minute)` that depended on **three divergent command→folder maps**. The symptom was `tokens_actual` **always `0`** for `fullreview`/`filereview` and data loss between repositories on export.
* **The Write is Synchronous, and That Is the Decision:** `log_command_metric()` resolves the git context **once per process** and writes the row on the spot, instead of handing it to a thread that a hook may not survive. Tokens, model and provider are **copied from the provider metadata at write time** — there is no more a-posteriori enrichment.
* **Versioned Schema:** `SCHEMA_VERSION` + `PRAGMA user_version`, UUID keys (not sequential integers, so that `merge`ing two ledgers does not collide), WAL + `synchronous=FULL`, and `LedgerVersionError` to refuse a newer bundle **before** the `ATTACH` — the local ledger is never left half-imported.
* **The "Never Born Empty" Invariant:** `ensure_ledger()` only creates the database **together** with the absorption of the legacy JSON (originals moved to `~/.gitpr/metrics_legacy/`, refusal marker, silent path in non-interactive mode).
* **One Window, One Meaning:** `--since`/`--until`/`--days` (inclusive at both ends) narrow **every** read surface. The cycle section is the exception — it asks the network, so it assumes the last 30 days and **names the window in its own header** instead of narrowing silently.
* **Each Metric Is Computed Once and Rendered Once:** the `(style, text)` pairs (`TITLE`/`PLAIN`/`WARN`) make the CLI and the Textual dashboard tell the **same** story; a section with nothing to say is omitted, never printed empty.
* **Cost with a Built-in Table and Override:** list prices of the four models GitPR distributes (`deepseek-v4-pro`, `deepseek-v4-flash`, `gemini-2.5-pro`, `gemini-2.5-flash-lite`), overridden by `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT`. **Both rates are mandatory** — pricing half of a model at an unconfigured zero **underestimates** the bill, so the model is reported in tokens only. `ollama`/`local` cost zero; `GITPR_METRICS_CURRENCY` labels the total and, when it is not USD, **discards** the built-in table instead of labeling a dollar price with another name. A total that leaves models out reads `Total (partial)`.
* **Normalized `modules`:** diff paths become two segments (`src/fix`), `(root)` for a file at the root and `(no module)` for the commands that never had a diff (linter, blame, hooks).
* **Transport by Bundle:** `bundle` writes the window as a self-contained `.db` (schema and `user_version` travel with it) and `merge` attaches it and inserts by UUID, migrating an old bundle upward and refusing a newer one.
* **`cache_backfill` Stays Out:** rows rebuilt from the AI cache are marked and **excluded from the execution aggregates** — the cache key is the prompt, so they count distinct prompts, not runs.
* **Git Identity without a Forge (`identity.py`):** repo, branch and author label in a **single** subprocess call, valid on any forge — and deliberately **without** building an `ScmProvider`, which would be expensive on every telemetry command.
* **The Cycle Section Is the Only One the Ledger Does Not Answer:** it lists merged PRs via `ScmProvider.list_pull_requests` and measures `created_at → merged_at`, with explicit status codes (ok/empty/unavailable/unsupported). Every failure degrades to **one line** — `Not read: <reason>`, no merge date on Bitbucket, nothing merged in the window — never a stack trace and never a guessed number.
* **Complete Presentation:** `gitpr metrics` group with 8 subcommands, a dashboard reading the ledger on a worker thread and the three-option migration assistant.
* **Configuration:** `GITPR_METRICS_PRICE_<MODEL>_INPUT`/`_OUTPUT`, `GITPR_METRICS_CURRENCY` and `.gitpr/metrics/` in `.gitignore`.
* **Tests:** `tests/test_metrics.py` jumped from 34 to **164 scenarios**; `tests/scm/*` gained the pagination of `list_pull_requests` and `within_window` on every forge; `test_commit_classifier.py` (30) covers `pr_number_from_merge`.
* **Artifacts:** ADR-008, `glossary-metrics-telemetry.md`, `docs/plans/develop_natan/20260927_metrics_audit_plansfacts.md`, survey and **`docs/metricas-telemetria.*.md` rewritten in the 5 languages**. Canonical vocabulary in `docs/plans/glossary-metrics-telemetry.md` (as per CLAUDE.md).

### **37. `gitpr mentor` Subcommand and the `--mentor` Flag — Mentor Mode (`src/domain/mentor/`) 🆕**

* **What it is:** A review says *what* is wrong and almost never *why that matters*. Mentor Mode enriches findings with **what is going on**, **why it matters**, an everyday **analogy** and the **concepts to study** — for someone starting out, the value of a review is in what it teaches after pointing things out.
* **Pure Domain Layer (`mentor_explanation_builder.py`):** no I/O, converts the AI payload into a validated `MentorExplanation` and enforces **epistemic honesty** on three fronts: with insufficient evidence, the explanation **says so** instead of filling the void; hallucinated URLs are **removed** from the "learn more" pointers; and unknown finding IDs are **rejected**, preserving the strict order of the original findings.
* **Application Layer (`generate_mentor_explanation.py`):** collects candidates (`collect_candidates`), **batches of at most 10 findings** per run, ordered by severity, reusing the MD5 cache per prompt hash — running mentor again over an unchanged review **costs nothing**.
* **Two Entry Points:** `gitpr mentor [--finding <id>] [--provider <name>]` runs over the **last persisted review** (so it must come after an `-r`/`-f`), and the `--mentor` flag appends the section to the normal review, with a warning when used on an action that does not support it.
* **Strict Opt-In:** `GITPR_REVIEW_MENTOR_MODE` (default `false`) and `GITPR_MENTOR_INCLUDE_ANALOGY` (default `true`), editable in the configuration TUI. Turning it on costs additional AI calls — which is why the default is off.
* **Skill and Registry:** `.gitpr.mentor.md` registered in `SKILL_FILES_BY_TYPE`, in `mcp_server.SKILL_FILES` and in `SKILL_LABELS`, with templates in **5 languages**.
* **Tests:** `tests/domain/mentor/test_mentor_explanation_builder.py` (8), `tests/application/use_cases/test_generate_mentor_explanation.py` (5) and `tests/test_mentor_cli.py` (4).
* **Artifacts:** `docs/mentor-mode.*.md` in **5 languages**, ADR-009, `glossary-gitpr-mentor.md`, spec and task reports.

### **38. `gitpr risk` Subcommand — Deterministic Risk Score (`src/domain/risk/`) 🆕**

* **What it is:** A **0–100** score with a `LOW`/`MEDIUM`/`HIGH`/`CRITICAL` level, computed **without AI** from local signals, so that the reviewer knows where to look first — and so that the same diff gets the same number twice.
* **Pure Domain Layer (`risk_types.py`, `risk_rules.py`, `risk_calculator.py`, `risk_explanation.py`):** enums and dataclasses (`RiskLevel`, `RiskSignal`, `RiskEvidence`, `FileRisk`, `PullRequestRisk`); weights, thresholds, critical path patterns, glob matching and test/non-executable detection loaded from YAML; per-file calculation with **0–100 clamp** and 50/30/20 aggregation.
* **The Formula Is Declared, Not Learned:** the aggregation combines **50%** the highest-risk file, **30%** the line-weighted average and **20%** critical evidence. Above that there are **mandatory escalation rules**: a score ≥ 80 goes up to at least `HIGH`; a blocker or a score ≥ 90 forces `CRITICAL`. A score without an escalation rule is a number the team learns to ignore.
* **Infrastructure Adapters:** `risk_history_reader.py` reads `git log` safely and extracts commits, bug fixes and reverts within a **90-day** window; `test_matcher.py` matches a test file ↔ a production file by convention, across several language layouts.
* **Use Case (`calculate_risk.py`):** correlates parsed diff sections, findings, test matching and history into `FileRisk`/`PullRequestRisk`, **degrading with a warning** when a signal is unavailable. Applies the policy pack on top of the base configuration, when one is active.
* **Three Consumptions:** the `gitpr risk` command (`--file`, `--format text|json`, `--base`, `--no-history`), the `⚡ Risk Assessment` section appended to reviews and to the remote PR review, and the **AI prompt** — where the score enters as **read-only** context, in a compact format.
* **Opt-Out:** `GITPR_RISK_INCLUDE_IN_REVIEW` (default `true`) — since the default turns it on, the section starts appearing in the reviews of anyone who updates; turning it off is a variable. **Declared behavior change.**
* **Per-Project Configuration:** `templates/gitpr.risk.yml` (or `.gitpr/skill/gitpr.risk.yml`) overrides weights, thresholds and critical paths. Negative weights are only accepted for **mitigating** signals such as `test_present`.
* **Tests:** `tests/domain/risk/` (2 files, 10 scenarios), `tests/application/use_cases/test_calculate_risk.py` (5), `tests/infrastructure/git/test_risk_history_reader.py` (4), `tests/infrastructure/git/test_test_matcher.py` (5) and `tests/test_risk_cli.py` (4).
* **Artifacts:** `docs/risk-scoring.*.md` in **5 languages**, ADR-010, `glossary-gitpr-risk-scoring.md` and task reports (including one from Gemini).

### **39. `gitpr policy` Subcommand — Versioned Policy Packs (`src/domain/policy/`, `src/infrastructure/policy/`) 🆕**

* **What it is:** A **versioned YAML pack** that carries, in a single artifact, a repository's quality decisions: skills, linter rules, severities, critical paths, risk weights and PR/commit conventions. Today every repository configures this by hand — which makes the behavior hard to reproduce, share and **lock down**. The pack makes the configuration declarative, versioned, composable and reproducible.
* **Domain Layer (`policy_types.py`, `policy_manifest.py`, `policy_checksum.py`, `policy_compatibility.py`, `policy_resolver.py`):** manifest parsing with a **closed schema** (an unknown key is an error, not a warning), compatibility and credential verification, **SHA-256 checksum** and a graph resolver with precedence and composition.
* **Infrastructure Layer:** `local_policy_repository.py` resolves the pack from **three origins** (`.gitpr/policies`, `~/.gitpr/policies` and the embedded package) and `policy_pack_loader.py` loads it.
* **Use Cases:** `activate_policy_pack`, `install_policy_pack`, `resolve_effective_policy` and `validate_policy_pack`.
* **Four Built-in Packs:** `laravel-quality`, `node-quality`, `php-security` and `vue-quality`, each with `policy.yml` and `linter.yml`, distributed via **`package-data`** — a pack that does not travel in the wheel is a pack that only exists on the machine of whoever wrote it.
* **Consumption Integrated at Four Points:** `calculate_risk` (policy on top of base configuration), `config.load_linter_rules` (layered merge + severity override), `core.get_skill_context` (injects the pack's context) and the **PR cache key** (`cache_scope`) — two repositories under different policies cannot share an AI response.
* **CLI:** `gitpr policy {list, validate, show, use, off, install, init}`, with confirmation (`--yes`/`--force`), stack detection in `init` and recording of the choice in `.gitpr/policy.lock.yml`.
* **Configuration:** `GITPR_POLICY_ENABLED` and `GITPR_POLICY_CONTEXT_MAX_CHARACTERS` (the ceiling of the context injected into the prompt), both in the new `policy` category of the schema.
* **Backward Compatibility:** with no active pack, the behavior is **preserved** — covered by an integration test, not by inspection.
* **New Dependency:** `packaging>=23.0` (SemVer/PEP 440), declared in `Pipfile` and `pyproject.toml`.
* **Tests:** `tests/domain/policy/` (4 files, 126 scenarios), `tests/application/use_cases/` (4 policy files, 89), `tests/test_policy_cli.py` (26) and `tests/integration/test_policy_effect_on_review_linter_risk.py` (31 — real effects in a real repository, with an autouse network guard).
* **Artifacts:** `docs/policy-packs.*.md` in **5 languages**, ADR-011, `glossary-policy-packs.md`, spec/plan/survey.

### **40. `gitpr baseline` Subcommand — Baseline and Auditable Suppressions (`src/domain/baseline/`) 🆕**

* **What it is:** Linters and reviews re-report the same pre-existing findings on every run, and the work of telling the output apart in your head is left to the reviewer. The baseline records the **snapshot of the findings the repository already knows about** in `.gitpr/baseline.json`, fingerprints each one in a way that is stable across runs and lets the pipeline classify what is new — **only `new` blocks**.
* **Domain Layer (`baseline_fingerprint.py`, `baseline_types.py`, `baseline_manifest.py`, `baseline_comparator.py`, `suppression_policy.py`):**
  * **Stable fingerprint:** rule identity, normalized path, lines and **sha256 of the line with whitespace collapsed** — the raw line is **never** stored, so the baseline is not a file where secrets leak by accident.
  * **Canonical manifest:** canonical JSON with checksum, validation that refuses an unknown key, a schema/fingerprint version outside the supported range, a broken suppression/debt invariant, a duplicate fingerprint and an invalid line range.
  * **Comparator:** classification, counts by status and entry resolution — `resolved` and then `back` reads as **new**, which is the conservative reading.
  * **Suppression Policy:** suppressions, accepted debt, overrides and validators, with layered matching (entry × overrides × policy pack) by **specificity and precedence**, plus overdue debt tracking.
* **Five Use Cases:** `baseline_gate` (the pipeline's single entry point — loads, classifies, annotates and composes the entry, local and pack-override layers), `baseline_summary` (**read-only** JSON view for IDEs and MCP agents), `compare_against_baseline`, `create_baseline` (creation/update with provenance, checksums and **preservation of human decisions**) and `suppress_finding` (suppresses/removes in the finding, line, file and rule scopes).
* **Policy Pack Integration:** the pack can carry a `baseline:` block, validated by the **same** validators, composed by the resolver as a labeled layer and reported by `policy validate`.
* **The Repository Is the Only One That Writes:** `local_baseline_repository.py` is the **only** reader/writer of `.gitpr/baseline.json` and `.gitpr/baseline.overrides.yml`, with atomic writing, a checksum/version gate and a repair path.
* **Configuration:** `GITPR_BASELINE_ENABLED`, `GITPR_BASELINE_PATH`, `GITPR_BASELINE_REQUIRE_LOCKFILE_CHECKSUM_MATCH` and `GITPR_BASELINE_ALLOW_LOCAL_OVERRIDES`, in the new `baseline` category.
* **Opt-In and No-Op:** turned off or with no existing baseline, the feature **reads no file and prints nothing** — whoever does not use it sees no change.
* **⚠️ The Deliberate Exception:** a baseline file that exists but **cannot be applied** makes lint, review, full review, file audit, remote review and risk **abort with exit code 1**. A baseline that fails silently is a baseline that "makes findings disappear" without anyone knowing — aborting is the safe choice.
* **Reach:** CLI (6 subcommands, all with `--format json`), MCP (tool `get_baseline_status` + resource `baseline://summary`), remote PR review (resolved **before the first network call**) and the lint flow, whose exit code now follows the baseline-aware blocking.
* **The Refactor the Feature Required:** `NormalizedFinding` became a canonical type in `src/domain/finding/` (with `snippet_hash` and `rule_version`), imported by every bridge — without that, the same finding would have one shape per tool and the fingerprint would not recognize it.
* **Tests:** `tests/domain/baseline/` (5 files, **126 scenarios**), `tests/application/use_cases/` (5 files, **141**), `tests/infrastructure/baseline/test_local_baseline_repository.py` (24), `tests/domain/finding/test_finding_types.py` (3), `tests/domain/linter/test_structured_findings.py` (44), `tests/test_baseline_cli.py` (54) and `tests/integration/test_baseline_effect_on_review_linter_risk.py` (34).
* **Artifacts:** `docs/baseline-suppressions.*.md` in **5 languages**, ADR-012, `glossary-baseline.md`, spec/plan/survey and task report.

---

## **📊 Tests and Quality**

| Test File | Scenarios | Focus |
|------------------|----------|------|
| `tests/test_blame_engine_ranges.py` | 7 | Blame by line range in a file |
| `tests/test_blame_metrics.py` | 7 | Blame metrics: depth, commits, duration |
| `tests/test_baseline_cli.py` | 54 🆕 | `baseline` group: create/show/validate/update/suppress/unsuppress |
| `tests/test_changelog_builder.py` | 22 | Changelog builder: sections, translatable headings, contributors, **links and dates** |
| `tests/test_chat_backend.py` | 19 | Chat memory, persistence, slash commands |
| `tests/test_commit_classifier.py` | 30 | Conventional Commits classification + **`pr_number_from_merge()` on the 4 forges** |
| `tests/test_config_app.py` | 78 | Configuration TUI: mounting, category switching, dirty tracking, F2 blocked, Ctrl+R, search, secrets |
| `tests/test_config_cli.py` | 9 | Registration of the `config` subcommand, `-h`, lazy import, clean stdout |
| `tests/test_config_schema.py` | 42 | `DEFAULT_CONFIG` coverage, no duplicates, categories/kinds, `advanced` only in Advanced, **skills section** ✅ |
| `tests/test_config_store.py` | 22 | Round-trip on the temporary `.env`, comments and order preserved, idempotent `remove_config_value()` |
| `tests/test_config_suggest_reviewers.py` | 8 | Suggested reviewers configuration (keys and defaults) |
| `tests/test_config_validation.py` | 40 | Types, enums, templates, `validate_ai_key()` with a mocked SDK (401 vs. network vs. ollama) |
| `tests/test_core.py` | 49 | Main flows, git diff, PR generation, timing, staging, coauthorship, hook language |
| `tests/test_diff_parser.py` | 26 | Diff parser by lines/hunks + `summarize_patch()` and `split_patch_sections()` |
| `tests/test_explain_command.py` | 2 | `explain` CLI: success, missing key, empty diff |
| `tests/test_external_linters.py` | 33 | Checkstyle bridge: XML parser, subprocess, diff intersection, report |
| `tests/test_i18n.py` | 20 | Parity between languages, missing/orphan keys, identity — **zero missing keys** ✅ |
| `tests/test_install_wizard.py` | 3 | Interactive installation wizard |
| `tests/test_issue_engine.py` | 4 | Structured issue draft |
| `tests/test_linter_metrics.py` | 4 | Linter metrics: errors, warnings, duration |
| `tests/test_linter_presets.py` | 5 | Linter presets: resolution and forced re-download |
| `tests/test_main_suggest_reviewers.py` | 8 | `--no-suggest-reviewers` flag in the CLI and in the contextual help |
| `tests/test_mcp_prompts.py` | 11 | MCP prompt templates and language fallback |
| `tests/test_mcp_server.py` | 110 | MCP tools (16), resources (19), annotations, patching, direct CLI, offload — **skill registry agreement** ✅ |
| `tests/test_mcp_server_e2e.py` | 6 | Real MCP server via subprocess + JSON-RPC stdio |
| `tests/test_mentor_cli.py` | 4 🆕 | `mentor` CLI and `--mentor` opt-in |
| `tests/test_metrics.py` | 164 | **Ledger**: synchronous write, idempotent export, bundle/merge, versioning, migration, renderers, cost, quality, modules, providers, cycle |
| `tests/test_net_timeouts.py` | 12 | Network/AI timeouts — aligned with the real 180s default |
| `tests/test_plugins.py` | 17 | Plugin discovery, linter rule merge, MCP prompts |
| `tests/test_policy_cli.py` | 26 🆕 | `policy` group: list/show/validate/use/off/install/init, confirmation and lockfile |
| `tests/test_pr_publish_app.py` | 45 | PR Publisher TUI: screens, flows, suggested reviewers, `NoticeScreen` |
| `tests/test_pr_publish_linter_modal.py` | 4 | Linter error modal: abort, no-verify |
| `tests/test_pre_save.py` | 3 | --pre-save flag and JSON payload |
| `tests/test_release_cli.py` | 4 | Release CLI: options, help with documented epilog |
| `tests/test_release_engine.py` | 53 | **Cutoff anchored in the changelog, `_drop_released()`, links, PR map by merge commit**, publication |
| `tests/test_reviewer_resolution.py` | 18 | Resolution ladder, rejection of partial name, dedup, failure tolerance |
| `tests/test_reviewer_suggestion.py` | 18 | Reviewer suggestion logic (ranking, exclusion, top-N, `last_commit_hash`) |
| `tests/test_risk_cli.py` | 4 🆕 | `risk` CLI: `--file`, `--format json`, `--base`, `--no-history` |
| `tests/test_security_ruleset.py` | 61 | Built-in ruleset matrix: regex, placeholders, level, wildcard, merge, translations |
| `tests/test_skill_command.py` | 10 | Download and validation of skill templates |
| `tests/test_skill_context.py` | 18 | `get_skill_context()` with `quiet=True`, fallbacks, skill registry |
| `tests/test_smart_excludes.py` | 15 | Smart pathspec filter and forced re-download |
| `tests/test_suggest_reviewers.py` | 17 | Suggested reviewers in the PR flow (integration, `no_login` hint) |
| `tests/test_tests_command.py` | 2 | CLI of the `tests` group and of `generate` |
| `tests/test_thinking_words.py` | 5 | Loading, parsing with a `;` separator and forced reload |
| `tests/test_updater.py` | 23 | PyPI gate: version parsing, daily cache, fetch, gate decisions, CLI wiring |
| `tests/test_usage_log.py` | 27 | Usage log: date-derived name, synchronous write, silence, multi-forge `_repo_label()` |
| `tests/test_version_bump.py` | 17 | Semantic bump: major/minor/patch, targets and validation |
| `tests/test_web_links.py` | 26 🆕 | Remote normalization (https/ssh/scp/credentials/port) and URL patterns per forge |
| `tests/application/use_cases/test_activate_policy_pack.py` | 27 🆕 | Pack activation, lockfile, refusals |
| `tests/application/use_cases/test_baseline_gate.py` | 40 🆕 | Gate: loading, classification, annotation, layers and abort |
| `tests/application/use_cases/test_baseline_summary.py` | 12 🆕 | Read-only view of the baseline for MCP/IDE |
| `tests/application/use_cases/test_calculate_risk.py` | 5 🆕 | Correlation of diff/findings/tests/history, degradation |
| `tests/application/use_cases/test_create_baseline.py` | 30 🆕 | Creation/update with provenance and preservation of decisions |
| `tests/application/use_cases/test_generate_mentor_explanation.py` | 5 🆕 | Batches, severity, cache hit, missing key |
| `tests/application/use_cases/test_generate_pr_explanation.py` | 2 | explain use case |
| `tests/application/use_cases/test_generate_test_file.py` | 4 | test generation use case |
| `tests/application/use_cases/test_install_policy_pack.py` | 10 🆕 | Pack installation, origins and conflicts |
| `tests/application/use_cases/test_suppress_finding.py` | 29 🆕 | Suppression/removal in the four scopes |
| `tests/application/use_cases/test_validate_policy_pack.py` | 23 🆕 | Validation: closed schema, checksum, compatibility |
| `tests/badge/test_badge_append.py` | 9 | Idempotent append of the badge to the PR body |
| `tests/badge/test_badge_builder.py` | 19 | shields.io Markdown composition, escape, color rule, style |
| `tests/badge/test_badge_cli.py` | 15 | `gitpr badge` command: snippet, `--readme`, `--style` |
| `tests/badge/test_badge_data.py` | 7 | Alert counting; `None` with no rules configured |
| `tests/badge/test_badge_offline.py` | 6 | Offline guard (network forbidden, except loopback) |
| `tests/badge/test_badge_optout.py` | 17 | `GITPR_BADGE=false` on every publication path |
| `tests/badge/test_badge_paths.py` | 10 | Single injection point in both publishers (TUI and `--no-edit`) |
| `tests/demo/test_demo_app.py` | 22 | Tour TUI: navigation, screens, help modal |
| `tests/demo/test_demo_isolation.py` | 14 | The tour touches neither `~/.gitpr` nor the network |
| `tests/demo/test_demo_runner.py` | 33 | `DemoState` state machine: forward, back, completed, text mode |
| `tests/demo/test_demo_scenarios.py` | 47 | Integrity of the recorded scenarios (hunk arithmetic) in the 5 languages |
| `tests/demo/test_demo_text_mode.py` | 18 | Plain-text output (`--no-tui`) for CI and recordings |
| `tests/demo/test_fake_ai_provider.py` | 21 | Fake provider contract: mirrors `call_ai_model` argument by argument |
| `tests/domain/baseline/test_baseline_comparator.py` | 21 🆕 | Classification, status counts, `resolved`→`new` |
| `tests/domain/baseline/test_baseline_fingerprint.py` | 12 🆕 | Golden digest, raw line never stored, stability |
| `tests/domain/baseline/test_baseline_manifest.py` | 26 🆕 | Canonical JSON, checksum, unknown keys, invariants |
| `tests/domain/baseline/test_baseline_types.py` | 15 🆕 | Persisted statuses, scopes and precedence |
| `tests/domain/baseline/test_suppression_policy.py` | 52 🆕 | Layers, specificity, overrides, overdue debt |
| `tests/domain/finding/test_finding_types.py` | 3 🆕 | Canonical finding type: `snippet_hash`, `rule_version` |
| `tests/domain/linter/test_sast_finding_mapper.py` | 2 | Uniform formatting and `[Gitleaks + Regex]` deduplication |
| `tests/domain/linter/test_structured_findings.py` | 44 🆕 | `findings_out`: structured findings and the new-side line counter |
| `tests/domain/mentor/test_mentor_explanation_builder.py` | 8 🆕 | Pure builder: epistemic honesty, removed URLs, order preserved |
| `tests/domain/policy/test_policy_checksum.py` | 15 🆕 | SHA-256 of the pack and detection of manual editing |
| `tests/domain/policy/test_policy_compatibility.py` | 22 🆕 | Version and credential compatibility |
| `tests/domain/policy/test_policy_manifest.py` | 42 🆕 | Closed schema, `baseline:` block, manifest errors |
| `tests/domain/policy/test_policy_resolver.py` | 47 🆕 | Precedence, layered composition, active pack × base |
| `tests/domain/pr/test_explain_section_builder.py` | 4 | Markdown rendering, tolerant parsing, `[FILL]` detection |
| `tests/domain/risk/test_risk_calculator.py` | 5 🆕 | 50/30/20 formula, 0–100 clamp, mandatory escalation |
| `tests/domain/risk/test_risk_rules.py` | 5 🆕 | Weights, thresholds, critical path glob, override YAML |
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
| `tests/infrastructure/baseline/test_local_baseline_repository.py` | 24 🆕 | Sole reader/writer: atomic write, checksum, repair |
| `tests/infrastructure/git/test_risk_history_reader.py` | 4 🆕 | Safe `git log`: commits, bugs and reverts in 90 days |
| `tests/infrastructure/git/test_test_matcher.py` | 5 🆕 | Test ↔ production matching by convention |
| `tests/infrastructure/linter/external/test_bandit_bridge.py` | 3 | Bandit bridge: availability, parsing, mapping |
| `tests/infrastructure/linter/external/test_base_bridge.py` | 4 | ABC: hardened subprocess, timeout, missing binary |
| `tests/infrastructure/linter/external/test_gitleaks_bridge.py` | 4 | Gitleaks bridge: secret masking, per-file scope |
| `tests/infrastructure/linter/external/test_semgrep_bridge.py` | 3 | Semgrep bridge: JSON parsing, severity, non-Python skip |
| `tests/integration/test_baseline_effect_on_review_linter_risk.py` | 34 🆕 | Real effect of the baseline on review, lint and risk in a real repository |
| `tests/integration/test_policy_effect_on_review_linter_risk.py` | 31 🆕 | Real effect of the pack on review, lint and risk (with an autouse network guard) |
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
| `tests/test_web_links.py` | 26 🆕 | (see above) |
| `tests/sync_i18n.py` | — | i18n coverage verification script (scaffold; never run) |

**Total:** **2799 scenarios collected across 129 test modules** (49 at the root + 11 in `tests/application/use_cases/` + 10 in `tests/fix/` + 9 in `tests/scm/` + 7 in `tests/badge/` + 6 in `tests/demo/` + 6 in `tests/split/` + **5 in `tests/domain/baseline/`** 🆕 + **4 in `tests/domain/policy/`** 🆕 + 4 in `tests/review/` + 4 in `tests/infrastructure/linter/external/` + **2 in `tests/domain/risk/`** 🆕 + 2 in `tests/domain/tests_generation/` + **2 in `tests/integration/`** 🆕 + **2 in `tests/infrastructure/git/`** 🆕 + 2 in `tests/domain/linter/` + **1 in `tests/domain/finding/`** 🆕 + **1 in `tests/domain/mentor/`** 🆕 + 1 in `tests/domain/pr/` + **1 in `tests/infrastructure/baseline/`** 🆕; **+910 scenarios and +33 files** since the previous report).

**Quality notes for this version:**
- **✅ The 4 failures of the previous window were closed, with the assertions intact.** `SKILL_LABELS` went from 8 to **11** entries and `mcp_server.SKILL_FILES` from 8 to **11**; the **40 untranslated `__()` keys** were resolved — the code uses 1603 and the dictionaries have 1603, **zero missing**. The three assertions that reported the debt (`TestSkillsSection` ×2, `TestNoMissingKeys`, `TestSkillRegistryAgreement`) **are still running and now pass** — verified in this window with `pytest tests/test_config_schema.py::TestSkillsSection tests/test_mcp_server.py::TestSkillRegistryAgreement tests/test_i18n.py::TestNoMissingKeys` → **11 passed**.
- **Growth of 910 scenarios (+48%) in a single cycle**, the largest jump ever recorded in this project. `tests/test_metrics.py` alone went from 34 to **164** scenarios, and the three new features with the highest test density are the baseline (≈380 scenarios combined), policy (≈246) and the ledger.
- **CI runs the suite on Python 3.10 and 3.13** since the previous window, and `fail-fast: false` guarantees that a failure on one version does not hide the other.
- **Integration tests entered the repertoire:** `tests/integration/` is new and proves **real effect** — an active pack and an active baseline change the review, the lint and the risk in a real repository, with an autouse guard that blocks network outside loopback.
- **`tests/conftest.py` remains hermetic:** `GITPR_SHOW_LOGS=false`, `GITPR_SKIP_UPDATE_CHECK=true`, `GITPR_LANG=en_us`, `GITPR_LINTER_SECURITY=false` and `LANG_VERSION` at the code's version.
- **Full run on this machine: `2 failed, 2795 passed, 2 skipped, 138 subtests passed in 520.46s (8min40s)`.** The suite was finally run end to end in this window and the result is honest: **2795 of the 2799 collected scenarios pass**. The **two failures are not a product regression** — they are an isolation bug in `tests/test_mentor_cli.py`, and the root cause was identified and confirmed by experiment:
  - `TestMentorCli` does `@patch("src.core.get_git_diff")`, but `src/main.py` imports the name **by value** (`from src.core import … get_git_diff`), so the patch never reaches the binding the flow actually uses: the **real** function runs. The real `get_git_diff()` executes `git diff -U1 -w -M -B HEAD -- <59 smart excludes>`, and since the only tracked change in the working tree during the run was `CHANGELOG.md` — filtered out by `:(exclude)*.md` —, the diff comes back with **0 bytes**, the flow falls into the early return at `main.py:1444` (`⚠️ Nenhum código novo encontrado`) with **exit code 0**, and the assertion `render_review_result.assert_called_once()` fails because the renderer was never called.
  - **The proof:** repeating the two scenarios with the patch pointed at `src.main.get_git_diff` (the correct binding), both behave exactly as the assertions expect — `--mentor` calls `_append_mentor_section` **and** the renderer; without `--mentor` the append is **not** called and the renderer is. In other words: **the two tests are environment-dependent**, passing as long as the working tree has any change that is not `.md` (which was true throughout the development of the features) and failing on a documentation-only tree.
  - The production code is correct — the `-r` path reaches `render_review_result` (`main.py:1593`) as it always has. **Recommended fix:** change the patch target to `src.main.get_git_diff` in both scenarios (or patch `src.core` and the main binding together).
- **None of the 4 failures of the previous window remains:** they were of another nature (skill registry and untranslated `__()` keys) and the assertions that characterized them are still running and passing.
- **Network guard in two places:** `tests/demo/conftest.py` (the tour cannot poison a real cache) and `tests/integration/conftest.py` (new).
- **Real git fixtures:** `tests/fix/git_fixture.py` and `tests/split/git_fixture.py` remain the standard for what is only honest with a real `git apply`.

---

## **🌐 Internationalization and Documentation**

* **i18n Coverage:** **1603 translation keys** in the 6 dictionaries, with **full key set parity** — and, for the first time in several windows, **parity with the code as well**: the code uses 1603 keys and none is untranslated. ✅ (+445 in this window.) The chain measured per commit was 1158 → 1161 (release notes, +3) → 1258 (ledger, +97) → 1288 (mentor, +30) → 1308 (risk docs, +20) → 1437 (policy, +129) → **1603** (baseline, +166).
* **`__lang_version__` went up v0.0.31 → v0.0.32 → v0.0.33 → v0.0.34**, triggering the OTA re-download of the translations — and this time the bump is **committed** (`af35270`), not in the working tree.
* **Translation sources in lockstep:** a new key has to exist in the code (source), in `langs/pt_br.json` (**master list**), in the FR/ES dicts of `scripts/sync_all_langs.py` and in the curated values of `scripts/fix_mangled_i18n_keys.py`; the `len(CLEAN_KEYS)` assertion remains at 49.
* **New topics 🆕 (4, all in 5 languages):**
  - `docs/mentor-mode.md` — Mentor Mode: what it teaches, the three epistemic honesty guarantees, the batches of 10 and the opt-in.
  - `docs/risk-scoring.md` — the deterministic score: the local signals, the 50/30/20 formula, the escalation rules and the `gitpr.risk.yml`.
  - `docs/policy-packs.md` — the versioned packs: the precedence ladder, the lockfile the team commits and the three failures that abort instead of degrading silently.
  - `docs/baseline-suppressions.md` — the baseline: the five statuses, why only `new` blocks, what the fingerprint hashes and what it deliberately leaves out, the four suppression scopes and the `baseline:` block of a pack.
* **Topics translated in this window (debt paid) 🆕:** `docs/split-command.*.md` completed the 5 languages (+3: pt_pt, es_es, fr_fr) and so did `docs/otimizacao-de-tokens.*.md`. **`review-pr` still has 2 languages** — it is the last partially translated feature family.
* **Topics updated in this window:** `docs/release-notes.*` (5 — rewritten for the changelog-anchored cutoff and the per-forge links), `docs/metricas-telemetria.*` (5 — rewritten for the SQLite ledger, with the canonical vocabulary of `glossary-metrics-telemetry.md`), `docs/mcp-integration.*` (5 — the new tools and resources), `docs/mcp-annotations.*` (5), `docs/ARCHITECTURE.*` (5) and `README.*` (5 — the index gained the `mentor-mode`, `risk-scoring`, `policy-packs` and `baseline-suppressions` families).
* **`docs/extra/` subdirectory 🆕:** two PT-only families (`como_reverter_commit_git_localmente`, `testar_sem_usar_pypi`) were **moved** from the top of `docs/` to `docs/extra/` (`R100`, no content change) — support material stops competing in the count of canonical topics.
* **Documentation in 5 languages:** **46 canonical topics** in `docs/` (+4 new − 2 moved) — **43 with full coverage in the 5 languages** 🆕 and only **3 partial topics** (`github-issue-prompt-com-gh`, `version-markers`, `review-pr`), against 7 in the previous window. ✅
* **Documentation gap that persists 🆕:** `explain` and `tests` remain the **only** features without a topic in `docs/` — they gained the skill registry entry and the keys, but not the page. The four features of this window (mentor, risk, policy, baseline) were born with docs in 5 languages, which makes the two exceptions even more visible.
* **Local Claude Code skills:** `.claude/skills/` with **29 skills** (count unchanged in this window).
* **Memory Index:** `.claude/memory/MEMORY.md` with **40 patterns** (count unchanged in this window).
* **Task reports:** `docs/claude-code/reports/develop_natan/` (**105** in total; **+4** — `2026-09-23_release_dedup_links.md`, `2026-09-29_metrics_telemetry_v2.md`, `2026-10-09_policy_packs.md`, `2026-10-10_baseline.md`) and `docs/gemini/reports/develop_natan/` (**14**; **+7**).
* **Status reports:** `docs/reports/` (16 reports; this is the **17th**).
* **Development plans:** 135 files in `docs/plans/` (+20 in the window — release, metrics, risk, policy and baseline specs/plans; **ADR-008 to ADR-012**; the `metrics-telemetry`, `gitpr-mentor`, `gitpr-risk-scoring`, `policy-packs` and `baseline` glossaries; and the new `docs/plans/develop_natan/` subdirectory) + **14 files in `docs/survey/`** (+4).

---

## **🔄 Distribution Pipeline**

1. **PyPI (single channel):** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **Mandatory update:** execution checks PyPI at startup and **blocks with exit code 1** if there is a newer version, printing `pip install --upgrade gitpr-cli`; the check is cached per day and `--update` only reports
3. **GitHub Releases:** removed — no PyInstaller, no `.exe` asset, no hot-swap
4. **GitHub Actions:** workflow `pr-review.yml` + `action.yml` (installs via pip) + `tests.yml` (Python 3.10 and 3.13 matrix). **The release workflow is still missing** — the build and the upload to PyPI are still manual.
5. **MCP Server:** `gitpr-mcp` entry point via `pyproject.toml`
6. **OTA templates and languages:** `templates/` and `langs/*.json` served from GitHub (main) — the `v0.0.34` bump renews the local copies of `~/.gitpr/langs/` when published; `__scripts_version__` **v0.0.4** renews the hooks (reinstall with `gitpr --installhooks`)
7. **Version derived from the code:** `pyproject.toml` uses `version = {attr = "src.updater.__version__"}` — `__version__` is the **only** source
8. **`package-data` now matters 🆕:** the four embedded packs (`src/policy_packs/*`) travel in the wheel; a build step that excludes them produces a `gitpr policy list` that cannot see the factory packs
9. **Release status 🆕:** **`v1.3.0` tagged** (2026-09-24, merge of PR #193) and **`v1.4.0` tagged** (2026-10-12, merge of PR #207). `CHANGELOG.md` has both sections complete and linked — `[1.4.0]` is the first one generated by the fixed engine. The only open change in the working tree is the fix for the localized headings of the `[1.4.0]` section, which came out in pt-BR (`Funcionalidades`, `Documentação`, `Tarefas`, `Contribuidores`) and were normalized to English.

---

## **📈 Evolution since the Previous Report (v0.0.16)**

| Area | v0.0.16 (previous) | v0.0.17 (current) |
|------|-------------------|-----------------|
| **GitPR Version** | 1.3.0 (bump **not committed**; HEAD at 1.2.0) — tag `v1.2.0` | **1.4.0** — **tags `v1.3.0` (09/24) and `v1.4.0` (10/12) created** ✅ |
| **Language Version** | v0.0.32 (bump **not committed**) | **v0.0.34** (v0.0.32 → v0.0.33 → v0.0.34, **committed**) |
| **Hook Scripts Version** | v0.0.3 | **v0.0.4** (hook templates rewritten for the ledger) |
| **AI Providers** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama (unchanged) |
| **Languages** | 5 languages, 6 dictionaries | 5 languages, 6 dictionaries |
| **Subcommands** | 9 | **14** (+ `mentor`, `metrics`, `policy`, `risk`, `baseline`) — **4 of them are groups** (baseline 6, metrics 8, policy 7, tests 1) |
| **Interface** | CLI + TUIs + wizards + 9 subcommands | **+ `gitpr mentor`/`--mentor` + `metrics` group (with migration assistant) + `gitpr policy` + `gitpr risk` + `gitpr baseline`** |
| **Layers** | `src/domain/` and `src/application/use_cases/` | **8 domain packages** (+ `baseline`, `finding`, `mentor`, `policy`, `risk`) and **14 use cases** |
| **Local persistence 🆕** | — | **`src/ledger.py`** — SQLite at `~/.gitpr/metrics/telemetry.db` (WAL, `user_version`, UUID, bundle/merge) |
| **MCP Tools** | 14 tools / 18 resources / 7 prompts (`skill://explain` and `skill://tests` **missing** ⚠️) | **16 tools / 19 resources / 7 prompts** ✅ (+`get_usage_metrics`, +`get_baseline_status`, +`baseline://summary`; skill registries aligned) |
| **CLI Flags** | 36 at the root + 9 subcommands | **32 at the root** (−5 metrics, +`--mentor`) + **14 subcommands** with 22 group subcommands |
| **Environment Variables** | 49 keys in `DEFAULT_CONFIG` | **58 keys** (+9: 4 baseline, 2 mentor, 2 policy, 1 risk) + `GITPR_METRICS_PRICE_*`/`CURRENCY` |
| **Config Schema** | 71 `ConfigField` / 14 categories | **80 `ConfigField` (+9) / 16 categories** (+ Policy, + Baseline) |
| **Linter** | Regex + built-in secrets + wildcard + 3 SAST bridges | **+ structured findings (`findings_out`) + canonical type `src/domain/finding/` + severity from the Policy Pack + baseline-aware exit code** |
| **Git Hooks** | pre-commit blocks secrets | **+ post-checkout/post-merge/pre-push call `gitpr --quiet metrics hook-event`** (bash repo resolution, GitHub-only, removed) |
| **SCM Layer** | 4 forges, `get_pull_request`, `supports_reviewable_diff` | **+ paginated and canonical `list_pull_requests`, `supports_merged_dates`, `within_window()`, `web_links.py` (per-forge links)** — 305 scenarios |
| **Release engine** | Range by `git describe`, no links | **Range anchored in the previous changelog section, `_drop_released()`, commit/PR/contributor links, PR map by merge commit** |
| **i18n (keys per file)** | 1158 × 6 — code uses 1198 → **40 untranslated** ⚠️ | **1603 × 6 — code uses 1603 → zero untranslated** ✅ (+445) |
| **Documentation** | 44 topics (37 complete + 7 partial) | **46 topics (43 complete + 3 partial)** — 4 new families in 5 languages + 2 translation debts paid + `docs/extra/` |
| **CI** | `.github/workflows/tests.yml` (3.10 + 3.13) | Unchanged (the **release** workflow is still pending) |
| **Test Suite** | 1889 scenarios (96 files) | **2799 scenarios (129 files) — +910 scenarios, +33 files** (largest jump ever recorded) |
| **Known failures** | 4 (half-done skill registry, one root cause) | **2** — the 4 from the debt **closed** ✅; 2 remain in `tests/test_mentor_cli.py`, of **test isolation** (patch on `src.core` that does not reach the `src.main` binding), with no product regression |
| **Dependencies** | — | **+ `packaging>=23.0`** (SemVer/PEP 440 for the Policy Pack) |
| **Commits since the report** | 8 commits | **11 commits** (`c561664`, `64a406f`, `4380ce8`, `811651f`, `57c5140`, `c46440f`, `e1cfb11`, `4d42d56`, `8450186`, `af35270`, `1abb1b5`) |
| **Merged PRs** | 7 PRs (#177–#189) | **10 PRs (#192, #193, #194, #196, #198, #200, #201, #203, #205, #207)**; 6 issues closed (#191, #197, #199, #202, #204, #206) |
| **Memory Index** | 41 patterns | **40 patterns** (unchanged in this window) |
| **Task reports** | 101 claude-code, 7 gemini | **105 claude-code (+4) and 14 gemini (+7)** |
| **Development plans** | 115 plans, 10 surveys | **135 plans (+20), 14 surveys (+4)** — includes ADR-008 to ADR-012 |

---

## **🚧 Next Steps**

* **Fix the isolation of `tests/test_mentor_cli.py` 🆕:** they are the **only two failures** in the suite today. The patch points at `src.core.get_git_diff`, but `src/main.py` imports the name **by value** — the real function runs, and on a tree whose only change is a `.md` (filtered out by `:(exclude)*.md` in the smart excludes) the diff comes back empty and the flow returns before the renderer. Repeated with the patch on `src.main.get_git_diff`, both scenarios pass. It is a two-line fix that closes the suite at **2797/2799**.
* **Commit the `CHANGELOG.md` fix 🆕:** the section headings of `[1.4.0]` came out localized (pt-BR) and were normalized to English **in the working tree**. It is the only open change in the repository — and the published package already carries the Portuguese version in the versioned file.
* **Document `explain` and `tests`:** they are, now, the **only two** features without a topic in `docs/` — all four that arrived in this window were born documented in 5 languages. **Item inherited from the previous window and still open.**
* **Automate the release in GitHub Actions 🆕:** the test CI has existed for two windows, but the build and the upload to PyPI are still manual — and in this window the manual step was done **twice** (v1.3.0 and v1.4.0). A release workflow triggered on tags closes the last manual stretch of the pipeline.
* **Extend the coverage of the secrets ruleset:** the three gaps remain open and were **reconfirmed in the code in this window** — unquoted assignment (`API_KEY=abc123`, the `.env` format, which is precisely where secrets leak), the `ASIA…`, `github_pat_…` and `xoxc-`/`xoxd-` prefixes, and the absence of a left boundary on the key name (today `mytoken` matches the same as `token`).
* **Translate `docs/review-pr.*.md` 🆕:** it is the **last** family with partial coverage (2 of 5 languages). `split-command` and `otimizacao-de-tokens` were paid off in this window.
* **Untrack the artifacts of `.gitpr/metrics/export/` 🆕:** `.gitpr/metrics/` entered `.gitignore` in this window — but the **35 files already tracked remain tracked**, because `gitignore` does not untrack. A single `git rm --cached` is missing. **The debt stopped growing, but it was not paid off.**
* **Document the contract break of `request_pull_request_reviewers`:** it went from `None` to `list[str]`; third-party subclasses of `ScmProvider` need to be updated. `docs/scm-multiforge.*.md` is the place, in all 5 versions. **Inherited item, still open.**
* **Map the blast radius of the baseline on the exit code 🆕:** a baseline that cannot be applied now **aborts** lint, review, full review, file audit, remote review and risk with exit code 1. It is the safe choice, but it is a behavior change across **six** flows — worth a `docs/baseline-suppressions` spelling out the exit contract for CI pipelines.
* **Reconcile the project version:** `CLAUDE.md` still says `Current version: 0.0.37` while `__version__` is at **1.4.0**. Define a single convention. **Inherited item, still open.**
* **`gitpr -h config` ignores `-h`:** the subcommand opens the TUI instead of showing the help — the gate `if ctx.invoked_subcommand is not None: return` runs before the `help_flag` block. Fixing it would change the behavior of `-h` for **all** subcommands, so it needs a decision. **Inherited item, still open.**
* **Migrate the `sync_i18n.py` extractor to AST:** the regex truncates literals with implicit concatenation (`__("a " "b")`). **Partial progress 🆕:** `tests/test_i18n.py` already uses an AST-based extractor (`_extract_keys_ast`) and it is the one that verifies coverage today; the synchronization script is still on regex.
* **Smart Exclude section in the TUI:** of the 12 items reported after using the screen, item 10 (*Smart Exclude*) is the only delivery not yet started — and the `GITPR_SMART_EXCLUDES_*` fields remain under `diff`, with no category of their own.
* **Debts recorded in the config TUI plan:** `DEFAULT_CONFIG` became redundant with the schema; the opening banner does not list `--init`, `--base` or `--plugins` (the `--dashboard` left the CLI, so that part of the item became obsolete 🆕); `LinterApp` is the **only** Textual app that still does not disable the command palette.
* **Anthropic Claude provider:** Direct support for the Claude API (`claude-sonnet-5`).
* **ASCII/Textual charts in the Dashboard:** Time histograms and token trends in the metrics TUI — the dashboard already reads the ledger, which was the missing prerequisite.
* **Seed for the local `.gitpr/conf/`:** The seeding of local configuration templates (smart-excludes, linter) remains pending as its own subcommand or a wizard step.
* **More providers:** OpenAI direct, additional local providers.

### ✅ Completed in this window (2026-09-23 → 2026-10-12)

* ~~**Close the debt of the two new features**~~ — `SKILL_LABELS` (8 → **11**) and `mcp_server.SKILL_FILES` (8 → **11**) received `tests`, `explain` and `mentor`; the **40 untranslated `__()` keys** were resolved and coverage reached 1603/1603. **The 4 failures became 0, with the assertions intact.**
* ~~**Close the 1.3.0 release**~~ — tag **`v1.3.0`** created (merge of PR #193, 2026-09-24) and `CHANGELOG.md` completed with the six features that were missing. **It was the item blocking two windows.**
* ~~**Close the 1.4.0 release**~~ 🆕 — tag **`v1.4.0`** created (merge of PR #207, 2026-10-12), with the three bumps (GitPR, languages, scripts) **committed** and no longer in the working tree.
* ~~**`gitpr release` repeats the history and has no links**~~ 🆕 — cutoff anchored in the previous changelog section, `_drop_released()`, commit/PR/contributor links, PR map by merge commit and `web_links.py` (PR #192).
* ~~**Telemetry in JSON with `tokens_actual` always zero**~~ 🆕 — SQLite ledger with synchronous writing, tokens copied at write time, `gitpr metrics` group, migration assistant and cost per model (PRs #194/#196).
* ~~**Mentor Mode**~~ 🆕 — `src/domain/mentor/`, use case, `gitpr mentor` + `--mentor`, skill in 5 languages (PR #198).
* ~~**Deterministic risk score**~~ 🆕 — `src/domain/risk/`, `calculate_risk`, history and test readers, `gitpr risk` and section appended to the review (PR #200).
* ~~**Policy Packs**~~ 🆕 — `src/domain/policy/`, `src/infrastructure/policy/`, 4 embedded packs, `gitpr policy` group and `packaging` dependency (PR #203).
* ~~**Baseline and auditable suppressions**~~ 🆕 — `src/domain/baseline/`, canonical `src/domain/finding/`, gate in the six flows, MCP tool and resource (PR #205).
* ~~**Translate the partial topics**~~ — `docs/split-command.*.md` and `docs/otimizacao-de-tokens.*.md` completed the 5 languages.
* ~~**`.gitpr/metrics/` out of version control**~~ — entered `.gitignore` with a comment explaining what is **not** ignored (`.gitpr/skill/`) and why.
* ~~**`docs/` hygiene**~~ — two families of support material (`como_reverter_commit_git_localmente`, `testar_sem_usar_pypi`) were moved to `docs/extra/`, taking them out of the canonical topic count.
* ~~**Hooks that only worked on GitHub**~~ — `post-checkout`, `post-merge` and `pre-push` now delegate to GitPR itself, with `__scripts_version__` at v0.0.4.

---

**Report generated on:** 2026-10-12  
**Branch:** `develop_natan`  
**Author:** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
