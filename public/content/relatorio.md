# **🚀 Project Status Report: GitPR CLI — v0.0.15 (2026-09-17)**

## **📌 Overview**

**GitPR** is an advanced CLI (Command Line Interface) tool for automating Git processes using Artificial Intelligence (Google Gemini / DeepSeek / Ollama). Its main goal is to act as a local intelligent assistant that performs Code Reviews, generates Pull Requests, semantic commit messages, audits technical debt, and injects best practices into the developer workflow (Shift Left).

**What's New in This Version (v0.0.15):**
- **`gitpr fix` Subcommand — the review that becomes an applicable patch:** Closes the loop between the automatic review and the fix. The last review in the cache feeds **one** AI call, which returns findings in fenced blocks; the extractor validates each block as a unified diff, `git apply --check` proves that it fits the current tree and a **deterministic, I/O-free** classifier labels each candidate as `safe`, `review_required` or `experimental`. Dry run is the default — writing requires `--apply`. `--force` never bypasses the applicability check, only the classification, and it requires a typed confirmation phrase. Everything that was applied goes into `.gitpr/fix_history.json`, which is what `--rollback` reads.
- **`gitpr review-pr <n>` Subcommand — reviewing someone else's PR without a checkout:** The diff comes straight from the forge API and enters **the same engine** the local flows use — same report, same linter rules, same `.txt`. Read-only by default: nothing is published to the forge without an explicit `--post-comment`. It widens the target audience from "whoever is about to open a PR" to "whoever was invited to review someone else's PR".
- **Reviewer identity resolution — the attach that never landed:** Suggestions are born from `git blame`, so they carry **names and e-mails**, not logins. When nothing resolved, the UI showed the bare name — and typing that name back made GitPR send it *verbatim* as if it were a login. GitHub answers **201 without attaching anyone**: apparent success, reviewer missing, no warning at all. Now a dedicated layer resolves the identity **twice** (before the TUI and at attach time) and also closes the API's second silent failure — a login accepted but not attached is now detected by reading `requested_reviewers` back.
- **The SCM layer gained the primitives to review a review that is not on disk:** `get_pull_request(repo, pr_id)` became a **concrete** method of the ABC (the `create_release` pattern) and was implemented across the four forges — `list_open_pull_requests` pages a single page, so filtering it by number loses old PRs and does not distinguish closed from nonexistent. The class attribute `supports_reviewable_diff` (`False` on Azure DevOps, whose API returns a file list rather than a diff) blocks the review **before any network call**.
- **Two latent GitLab defects fixed:** `changes[].diff` is a loose hunk, so the file path was discarded — the AI would review orphan hunks and neither the chunker nor the exclusion filter would work; the `diff --git / --- / +++` headers are now synthesized from `old_path`/`new_path`. And `overflow: true` was ignored: a truncated diff was reviewed halfway and published as if it were whole — now it raises.
- **`gitpr fix` now fixes the review that was reviewed:** The reviewed diff is written to the cache record (`reviewed_diff`) and `fix` prefers it, falling back to re-derivation only for old records. It is the only correct source when the review came from a remote PR or from a whole-branch diff.
- **MCP grew from 12 to 14 tools and from 17 to 18 resources:** `list_fix_candidates` (13th, read-only) + `skill://fix`, and `review_remote_pr` (14th, read-only, no `post_comment` argument, writes no `.txt`).
- **i18n expanded to 1048 keys:** +93 since the previous report (955 → 1022 with `fix` → 1028 with the reviewers → 1048 with `review-pr`); `__lang_version__` went from v0.0.25 to **v0.0.28** (chain v0.0.25 → v0.0.26 → v0.0.27 → v0.0.28) and the 6 dictionaries keep **full key set parity**.
- **Documentation:** 2 new families — `fix-command` (5 languages) and `review-pr` (EN + PT-BR) — and 9 updated topics, including `code-review-ia` (remote mode as §1.4), `suggested-reviewers` (login resolution) and §2.2 of `fix-command` rewritten in all 5 versions, because the diff is no longer re-derived.
- **Repository hygiene:** a self-vendored pip 25.2 tree (`pypa/`, `pip/cache/http-v2/`) had been committed by mistake and was removed; `.gitignore` gained `pypa/` and `pip/`.
- **Version jump to 1.2.0:** the bump is in the **working tree and has not yet been committed or tagged** (HEAD is still at 1.1.0; the last tag is `v1.1.0`), and `CHANGELOG.md` still stops at `[1.1.0] - 2026-09-13`.

- **Current version:** 1.2.0 (bump in the working tree — HEAD at 1.1.0)
- **Language dictionaries version:** v0.0.28
- **Hook scripts version:** v0.0.3
- **Distribution:** PyPI (`pip install gitpr-cli`) — binary channel removed in the previous window
- **Website:** [gitpr.natanfiuza.dev.br](https://gitpr.natanfiuza.dev.br/)
- **Repository:** [https://github.com/gitpr-cli/gitpr.git](https://github.com/gitpr-cli/gitpr.git)
- **License:** LGPL-2.1
- **Supported languages:** en_us, pt_br, pt_pt, es_es, fr_fr (5 languages, 6 dictionaries)

---

## **🏗️ Base Architecture and Libraries**

* **Language:** Python >= 3.10
* **CLI Framework:** Click (for commands, flags and terminal formatting).
* **UI/Terminal:** Textual — TUI for interactive chat, issue editing, help screen, metrics dashboard, PR Publisher, linter errors (`LinterApp`), configuration (`ConfigApp`) and the new `NoticeScreen` modal for PR Publisher notices 🆕.
* **Cryptography:** `cryptography.fernet` for local protection of API keys, GitHub tokens and SCM tokens for the forges — the secrets edited in the configuration TUI are also encrypted before being written.
* **Configuration:** `python-dotenv`, `pyyaml` (for the static linter) + its own declarative schema (`src/config_schema.py`).
* **AI Providers:** Integration via the official Google GenAI SDK (`gemini-2.5-flash`), OpenAI SDK (`DeepSeek`), and OpenAI SDK (local `Ollama`).
* **Forge APIs:** `requests` (REST) — multi-forge abstraction layer in `src/infrastructure/scm/` (GitHub, GitLab, Bitbucket, Azure DevOps); legacy `src/github_api.py` module kept as a deprecated shim.
* **MCP:** [mcp](https://pypi.org/project/mcp/) >= 1.0.0 (official Anthropic SDK for Model Context Protocol) — **14 annotated tools** 🆕, **18 resources** 🆕, 7 prompts; handlers offloaded to threads via `anyio`.
* **Tests:** Pytest + `unittest.mock` (**64 test modules — 41 at the root, 9 in `tests/scm/`, 10 in `tests/fix/` and 4 in `tests/review/`** 🆕 —, 1437 collected scenarios) + MCP server e2e tests via a real subprocess (JSON-RPC stdio) + real git repository fixtures in `tests/fix/git_fixture.py` 🆕.
* **Packaging:** setuptools/build (PyPI).
* **CI/CD:** GitHub Actions (`pr-review.yml`) + `action.yml` for execution in pipelines.

---

## **🧩 Implemented Modules and File Architecture**

### **1. Core and Git Operations (`src/core.py`)**

* **Structured Generation:** Communicates with the LLM asking for a strictly JSON return.
* **Map-Reduce (Giant Diffs):** When the diff exceeds ~90k tokens, it automatically splits into per-file batches (`split_diff_into_chunks`), processes each part (Map) and unifies the summaries (Reduce). Supports PRs, commits and Issues.
* **Local Tokenizer:** `tokenizer.json` for accurate token estimation before sending to the AI.
* **Token Estimation:** Lightweight `len() // 4` heuristic via `estimate_token_count()` with a fallback to the local tokenizer.
* **Native Git Optimization:** `-U1`, `-w`, `-M`, `-B` flags in the `get_git_diff` and `get_git_full_diff` commands to reduce useless context.
* **Pre-Save (`--pre-save`):** Hidden debug flag that saves the full payload (system instruction + prompt) as JSON before each AI call.
* **Two-Layer Smart Excludes:** Smart pathspec filter with a global layer (`~/.gitpr/conf/`) + project-local layer (`./.gitpr/conf/`). Merged at runtime (union, deduplicated). Auto-seeding of the local file on first run. `_load_smart_excludes()` accepts `force=` for on-demand re-download from the configuration TUI. 🆕 The `templates/gitpr.smart-excludes.json` template gained `.gitpr/fix_history.json` — an applied patch dirties a **tracked** file, so without it the file would show up in `gitpr -c` diffs and in PR descriptions.
* **Metrics with Time Tracking:** Injection of `log_command_metric()` in all flows with the duration passed through in milliseconds (`duration_ms`) and lazy imports.
* **Centralized Output Resolution:** `resolve_output_path()` function that centralizes the output directory logic — default in `.gitpr/reports/{type}/`.
* **SCM Wizard (`run_scm_init_wizard()`)**: `gitpr --init` — detects the forge from the origin remote, requests per-forge extras (Azure org/project, Bitbucket username), validates the token with `test_connection` (3 attempts, 401 re-prompt) and persists `GITPR_SCM_PROVIDER` + `GITPR_SCM_TOKEN_ENCRYPTED` (Fernet) **only on success**.
* **Release Skill Template (`ensure_release_skill_template()`)**: Downloads `templates/gitpr.release.*.md` on the first use of `gitpr release` (CLI layer, language-aware, never overwrites; skipped under `--format json`).
* **Shared Skill Registry:** `SKILL_FILES_BY_TYPE` / `SKILL_TYPES` / `DEFAULT_SKILL_TYPE` live in `src/config.py` (the TUI cannot import `core` at the top — it pulls in the AI SDKs); `get_skill_context()` uses `skill_file_for()`. 🆕 `files_to_download` needed its own entry for `gitpr.fix.md` — touching only `SKILL_FILES_BY_TYPE` was not enough for `--skill` to download the template.
* **Review Engine with Cache Scope (`generate_pr_content`) 🆕:** Two **additive** parameters — `cache_scope` (attached **only to the cache key**, never to the prompt) and `store_diff` (writes the reviewed diff into the record). The `""`/`False` defaults keep the local path **byte-identical**: zero cache invalidation for the local reviews that already exist. It is what allows a remote review to be scoped by `::diff-source::pr-<n>` without a local review of the same diff answering it by mistake.
* **Co-authorship Trailer:** `COAUTHOR_TRAILER` + `append_coauthor_trailer()` — idempotent, preserves third-party trailers.
* **Hardened Subprocesses:** `stdin=subprocess.DEVNULL` + `encoding='utf-8'`/`errors='replace'` in every `subprocess.run`; connection check via socket `8.8.8.8:53` before network operations.

### **2. Global Plugin System (`src/plugins.py`)**

* **Plugin Architecture:** Extensibility system that loads plugins from the `~/.gitpr/plugins/` directory, applying to **all projects**.
* **Linter Plugins (`linter/`):** `.yml` files with additional regex rules merged with the local `.gitpr.linter.yml`.
* **MCP Prompt Plugins (`prompts/`):** `.md` files that extend the system context with specific instructions.
* **Factory Closures:** `get_linter_plugins` and `get_prompt_plugins` functions with closures to isolate state between sessions.
* **`--plugins` Command:** Lists all installed global plugins with their types and paths.
* **Multilingual Documentation:** `docs/plugins-system.md` in 5 languages (EN, PT-BR, PT-PT, ES, FR).

### **3. CLI Interface and Setup (`src/main.py` and `src/config.py`)**

* **Initial Setup:** Detects the first run, creates the `~/.gitpr/` folder, and interactively asks for API keys, preferences and language.
* **Command Routing:** Manages all flags and the **4 subcommands** — `release`, `config`, `fix` 🆕 and `review-pr` 🆕.
* **Default Behavior:** Running `gitpr` with no flags opens the PR Publisher TUI.
* **Flags (35 Click options at the root, unchanged in this window):**
  * `--init`: Opens the multi-forge SCM configuration wizard (forge detection + token validation).
  * `--no-suggest-reviewers`: Turns off reviewer suggestion in the PR publishing flow.
  * `--no-publish`: Generates the PR description and saves it locally without opening the interactive editor.
  * `--no-edit`: Skips the TUI entirely — auto-commit, auto-push and publishes straight to the forge.
  * `--base <branch>`: Overrides the Pull Request target branch.
  * `--plugins`: Lists installed global plugins.
  * `--linter-setup`: Opens the interactive configuration assistant for external linters.
  * `--version`: Displays the current GitPR version (via `@click.version_option`).
* **`fix` Subcommand 🆕 — 8 options:** `--list` (lists the candidates from the last review — what the command does with no argument), `--apply` (writes to the tree; without it, it is a dry run), `--all-safe` (selects every `safe` candidate; writing still requires `--apply`), `--create-branch <name>`, `--no-branch`, `--yes` (skips confirmation, **never** bypasses `--force`) and `--force` (applies a non-safe patch after a typed phrase) and `--rollback <patch-id>`. Optional argument `[<finding-id>]`. Exact mold of `release`: lazy imports in the body and `epilog` for `get_doc_url("fix-command.md")`. No existing flag changed meaning — `release`'s `--force` ("regenerate existing section") does not collide because subcommand namespaces are separate.
* **`review-pr` Subcommand 🆕 — 2 options:** `review-pr <number>` with `--provider <name>` and `--post-comment`; read-only by default, **never** calls `check_unstaged_files`, writes `{branch}_{datetime}_PR_REVIEW.txt` with the name of the PR's source branch. It rejects the PR **before any AI call** for capability, existence, state, empty/non-reviewable diff and exhaustion of the smart-excludes. Reuses `_resolve_scm_context`.
* **Environment Variables (44 keys in `DEFAULT_CONFIG`, +5 in this window 🆕):** the five `GITPR_FIX_*` — `GITPR_FIX_SAFE_MAX_LINES_CHANGED`, `GITPR_FIX_SAFE_EXCLUDED_PATHS`, `GITPR_FIX_REQUIRE_CONFIRMATION`, `GITPR_FIX_CREATE_BRANCH_ON_ALL_SAFE` and `GITPR_FIX_BRANCH_NAME_TEMPLATE`. `get_fix_settings()` returns the flattened block and **falls back to the built-in default** when the value is emptied by accident — clearing the field cannot silently drop the protection.
* **Contextual Help:** `-h --flag` displays feature-specific documentation with a direct (language-aware) link to GitHub. Subcommands have their own `epilog=` (Click's `\b` paragraph so the URL is not re-wrapped under any locale).
* **--lang:** Forces the interface language for the current run without persisting the change.
* **--provider:** Forces the AI provider (`gemini`, `deepseek`, `ollama`) for the current run.
* **--mcp:** Starts the MCP server on the stdio transport for editor integration — **14 annotated tools + 18 resources + 7 prompts** 🆕.
* **--install:** Guided 4-step assistant that downloads skill templates, installs Git Hooks, configures MCP in the editors and validates API keys.
* **--metrics:** Local telemetry system with per-repository scope: `--export`, `--purge`, `--dashboard`.
* **--status:** Lists uncommitted files categorized (new/modified/deleted) — fast, no AI, no network.
* **`.env` Write Layer:** `read_env_file_values()` reads **only the file** via `dotenv_values` (immune to `os.environ`), `save_config_values()` writes with `set_key`, `remove_config_value()` with `unset_key`. `validate_ai_key()` probes the Gemini/DeepSeek SDKs with short timeouts and distinguishes a refused credential (`401`/`403`) from an unreachable network.

### **4. PR Publisher TUI (`src/ui/pr_publish_app.py` and `src/ui/pr_publish_help.py`)**

* **Complete Interactive Interface:** TUI built with Textual to review, edit and publish Pull Requests directly from the terminal.
* **7 Modal Screens:** `StageFilesScreen`, `CommitConfirmScreen`, `CommitProgressScreen`, `CommitMessageScreen`, `LinterErrorScreen`, `ErrorScreen` and **`NoticeScreen`** 🆕.
* **Blocking Notices (`NoticeScreen`) 🆕:** A modal that requires acknowledgement (Esc or Close) before the merge flow can continue. It is intentional: without it the merge prompt would take over the screen and the notices about a discarded reviewer would go unnoticed — but it pauses automated flows when there are notices.
* **`_attach_reviewers` with resolution 🆕:** Resolves what the user typed **before** sending, reports what was discarded and, on a batch `422` with several reviewers, **retries one by one** — GitHub rejects the whole batch when a single login is ineligible (the PR author, a non-collaborator), which previously took down the valid reviewers as well.
* **Suggested Reviewers:** The publishing flow queries the forge for suggested reviewers and offers them in the TUI; selection controlled by `--no-suggest-reviewers`, `GITPR_SUGGEST_REVIEWERS`, `GITPR_REVIEWER_SUGGESTION_TOP_N` and `GITPR_REVIEWER_SUGGESTION_EXCLUDED`. 🆕 `_reviewer_suggestion_view()` builds `resolutions` via `resolve_candidates`, pre-fills `handles` and marks those who have no account so the hint lines can flag them; the `resolutions` travel in the view so that attach does not resolve the same people twice.
* **Bindings:** F1 (Help), F2 (Save local .md), F3 (Publish via forge), Esc (Quit).
* **Auto-Commit Flow:** Linter → AI message → confirm → commit → push → publish PR.
* **Unstaged File Check:** On start, checks `git status --porcelain` and offers a modal to select, skip or cancel.
* **Existing PR Handling:** Detects open PRs for the current branch via the API and offers to push or create a new one.
* **Auto-Upstream:** Detects a `git push` failure due to a missing upstream and automatically tries `--set-upstream origin <branch>`.
* **Merge Flow:** After creating/updating the PR, offers a merge option. Controlled by `GITPR_AUTO_MERGE`.

### **5. GitHub API Module (`src/github_api.py`)**

* **Deprecated Shim:** `create_pull_request()`, `update_pull_request()`, `merge_pull_request()` and the remaining functions delegate to `src/infrastructure/scm/github_provider.py`; the module emits a `DeprecationWarning` and keeps the legacy `(ok, data, status)` tuples — no new code may import it.

### **6. Static Analysis Engine / Linter (`src/linter_engine.py`)**

* **Offline Linter:** Statically analyzes the added (`+`) lines in the git diff without spending AI quota.
* **YAML Rules:** Reads the local `.gitpr.linter.yml` file (created via `--skill`).
* **Linter Plugins:** Additional rules loaded from `~/.gitpr/plugins/linter/*.yml`.
* **External Linters Bridge:** Runs ESLint/PHPCS/Stylelint on the changed lines of the diff, Checkstyle XML parser and per-line cross-referencing.
* **`skip_external` — the bridge cannot lint the wrong tree 🆕:** `parse_diff_and_lint(..., skip_external=False)` gained the parameter that turns off **both** call-sites of the external bridge. The remote flow passes `True`, because the bridge runs binaries **against files on disk** — the user's local tree, not the PR — and those warnings would be published as a public comment on someone else's PR.
* **Consolidated Report:** `generate_linter_report_content()` consolidates regex + external errors into `.gitpr/reports/linter/` — generated only when there are violations.
* `load_linter_presets()` accepts `force=` for re-downloading the presets from the TUI.

### **7. Security and Authentication (`src/security.py`, `src/config.py`, `src/tui_issue.py`)**

* **Encryption:** Generates a master key `secret.key` in the `~/.gitpr/` folder.
* **Token Protection:** `encrypt_data` and `decrypt_data` to protect AI API keys, GitHub PAT and SCM tokens for the forges (`GITPR_SCM_TOKEN_ENCRYPTED`).
* **Multi-Forge Validation:** `validate_or_request_scm_token(provider, repo_display)` — validates the token on the configured forge with a 401 → reauthentication loop preserving the draft; the legacy GitHub token (`GITHUB_TOKEN_ENCRYPTED`) remains functional until `--init` runs.
* **Secrets in the Configuration TUI:** `KIND_SECRET` fields are edited in a masked field, **never** display the value in the clear and are encrypted with Fernet before being written — no path reads the secret back to the screen. `GITPR_SCM_TOKEN` is `read_only` and its description points to `gitpr --init` as the only path that should write it.

### **8. Auto-Updater (`src/updater.py`)**

* **PyPI as the Single Source:** `get_latest_remote_version()` always queries `https://pypi.org/pypi/gitpr-cli/json`, returns a version **string** and writes the daily cache **without** the `download_url` field.
* **Mandatory Gate (`enforce_update_required()`):** Returns `True` (after printing both versions and the pip command) when the published version is newer; returns `False` when up to date, when the remote version is **unknown (offline — the user would have no way to update)** or when the check is turned off. Returning a `bool` instead of calling `sys.exit` internally keeps the function testable.
* **`check_and_update()`:** Only queries and **reports**, never installs.
* **Escape Hatch:** `GITPR_SKIP_UPDATE_CHECK` (any non-empty value) — not advertised to the user as a feature; it exists for the test suite and offline automation.
* **Daily Cache:** Avoids repeated checks on the same day.
* **Centralized Versioning:** `__version__` (**1.2.0** — bump in the working tree), `__lang_version__` (**v0.0.28** — chain v0.0.25 → v0.0.26 → v0.0.27 → v0.0.28 in this window 🆕), `__scripts_version__` (**v0.0.3**), `SMART_EXCLUDES_VERSION`, `THINKING_WORDS_VERSION`, `LINTER_PRESETS_VERSION`.

### **9. Interactive Chat Interface (`src/ui/chat_app.py`)**

* **Complete TUI:** Built with Textual — message history, multi-line input, status bar with visible bindings.
* **Per-Branch Memory (`src/chat_memory.py`):** Conversation history persisted per branch, allowing continuity between sessions.
* **Slash Commands:** `/explain`, `/tests`, `/optimize`, `/clear` — shortcuts for pair programming.
* **Auto-Patching (F5), Diff Refresh (F2), Session Export (F6).**
* **Shared Extractor 🆕:** The regex block was **duplicated** in F5 and `ctrl+s`; both now call `patch_extractor.extract_code_blocks()`. The file lost 31 lines and gained 3, with **identical visible behavior** (same keys, same `GITPR_PATCH_SUGGESTION_<key>.txt`, same content) — which is why there is no changelog note about the chat. It is also the reason `src/fix/`'s `__init__.py` is **docstring only**: the chat imports it on every session, and an `__init__` that re-exported would drag along the AI and git layers (see ADR-004, rejected alternative).

### **10. Internationalization — i18n (`src/i18n.py`)**

* **Laravel-Inspired System:** `__()` function with support for named placeholders (`{count}`, `{file}`, etc.).
* **Automatic Detection:** Detects the OS language on the first run and saves it to `GITPR_LANG`.
* **5 Languages, 6 Dictionaries:** en_us (default/fallback), pt_br, pt_pt, es/es_es, fr/fr_fr.
* **Versioned Files:** `__lang_version__` (**v0.0.28**) controls the update of the language packs (`langs/*.json`) — bump chain v0.0.25 → v0.0.26 → v0.0.27 → v0.0.28 in this window.
* **Coverage:** **1048 translation keys** in each of the 6 files — **full key set parity** (+93 since the previous report).
* **Environment Snapshot (`AMBIENT_ENV_KEYS`):** `frozenset(os.environ)` captured in `i18n.py` **immediately before** the module-level `load_dotenv()` — it fixes the "⚠ in the environment" badge that tautologically confirmed the key is in the file.
* **Rewritten Key 🆕:** `GitHub usernames, comma separated` → `GitHub login, name or email, comma separated` — the field stopped promising what it did not accept.
* **Cache with Per-Language Indexing:** Cached AI responses include the current language in the MD5 keying.

### **11. Animated Spinner (`src/spinner.py`)**

* **Braille + Thinking Words:** Background thread during AI calls displaying braille characters with "thinking" words.
* **263 entries per language:** Synchronized across the 5 languages. `_load_thinking_words()` / `reload_thinking_words()` accept `force=`.

### **12. AI Providers (`src/ai_providers.py`)**

* **3 Supported Providers:** Google Gemini (`gemini-2.5-flash`), DeepSeek (`deepseek-chat`), Ollama (local).
* **JSON Mode & Deterministic Parameters:** Structured outputs with `temperature=0.0` and `top_p=0.1`; automatic fallback between configured providers.

### **13. Smart Cache (`src/cache.py`)**

* **MD5 + Metadata:** Keying by MD5 hash of the diff and prompt, with per-language indexing.
* **Last Review Selection (`resolve_last_review()`) 🆕:** Along with `REVIEW_ACTION_TYPES`, it picks the **newest** `review`/`fullreview` record for a repo+branch pair, **excluding file-scoped reviews** (`-i`), which do not describe the branch. It is the entry point of `gitpr fix`.
* **Reviewed Diff (`reviewed_diff`) 🆕:** A field at the top of the record that stores the diff that was actually reviewed. Preferred by `fix/apply_fix.reviewed_diff()`, with a fallback to re-derivation for old records — the only correct source when the review came from a remote PR or from a whole-branch diff.
* **Telemetry and Duration:** Persistence of the `duration_ms` and `meta_raw` fields in cache files.
* **Reading for the Dashboard:** `scan_cache_files_for_dashboard()` reads all cache files recursively.

### **14. Issues Engine and TUI (`src/issue_engine.py`, `src/tui_issue.py`, `src/ui/issue_app.py`)**

* **3 Context Engines:** Current diff, Branch history (`-ht`), and Blame Archaeology (`-b`).
* **Multi-Forge Publishing:** F3 creates the issue on the **configured** forge (`GITPR_SCM_PROVIDER`) via `provider.create_issue` — Azure DevOps raises `ScmNotSupportedError` (Work Items depend on the process template).
* **Map-Reduce for Issues:** When the context exceeds ~90k tokens, it automatically splits into chunks and unifies the results.
* **401 Handling:** Reauthentication signalling without closing the application.

### **15. Code Archaeologist (`src/blame_engine.py`)**

* **Git Blame + AI:** Tracks the evolution and historical authorship of code excerpts with commit classification (`ORIGIN` vs `REFACTORING`).
* **Blame Metrics:** Events recorded via `log_blame_metric()` with depth tracking and the number of commits analyzed.

### **16. MCP Server and Direct CLI Invocation (`src/mcp_server.py`)**

* **14 Annotated MCP Tools 🆕:** `get_git_context`, `analyze_diff`, `list_unstaged_files`, `analyze_unstaged_diff`, `get_full_diff`, `generate_commit_message`, `review_code`, `full_review`, `generate_pr_description`, `run_linter`, `analyze_blame`, `generate_issue`, **`list_fix_candidates`** (13th — candidates from the last review with patch, classification and id; read-only) and **`review_remote_pr`** (14th — review of an open PR on the forge, fetched by number; read-only, **never** comments and **never** writes a file, resolves the forge on its own).
* **18 Resources + 7 Templated Prompts 🆕:** `skill://list` + `skill://{pr,commit,review,filereview,issue,blame,release,fix}` (**`skill://fix` is new**) + `linter://config` + `prompt://list` + 7 prompts.
* **Direct CLI Invocation:** The `gitpr-mcp --tool <name> [--tool-args '<json>']` command invokes any MCP tool directly without starting the stdio JSON-RPC server. `gitpr-mcp --list` prints the complete registry as JSON.
* **Real Stdout Isolation:** `_write_real_stdout()` writes directly to the original `sys.__stdout__`, guaranteeing pure JSON on stdout — the reason the usage log **never** prints.
* **Event Loop Offload:** `_offload` decorator (`anyio.to_thread.run_sync`) applied to the 14 tools — synchronous handlers do not freeze the stdio server.
* **Usage Log:** The server's `main()` calls `log_usage()` — the `gitpr-mcp` console script never loads `main.py`, so this is the only point that reaches it.
* **E2E Tests:** `tests/test_mcp_server_e2e.py` starts the real server as a subprocess and speaks JSON-RPC stdio.

### **17. Metrics Dashboard TUI (`src/ui/metrics_app.py`)**

* **Per-Repository Scope (Repo-Scope):** `📁 Repository: owner/repo` label and strict per-project filtering.
* **Asynchronous Scan with Overlay:** Background worker thread with a `ProgressBar` widget.
* **Data Consolidation:** `load_cache_token_summary()` adds cache tokens to the totalizer.
* **Local Export:** Saving CSV/JSON to `./.gitpr/metrics/export/` — 🆕 the `gitpr_metrics_2026-09-17.csv`/`.json` artifacts entered **tracked** via PR #171 and are candidates for `.gitignore`.

### **18. Metrics and Telemetry System (`src/metrics.py`)**

* **Per-Repository Scope:** All events indexed by `repo_name`.
* **Hook, Linter and Blame Events:** `log_hook_event()`, `log_linter_metric()`, `log_blame_metric()`.
* **Export and Cleanup:** `--metrics --export` (CSV/JSON) and `--metrics --purge` with interactive confirmation.

### **19. Git Hooks Language Synchronization**

* **Independent Versioning:** `__scripts_version__` (v0.0.3) controls the version of the hook scripts; automatic detection and update.
* **Suffix Mapping (`HOOK_SCRIPT_SUFFIXES`):** Interface codes (`es_es`, `fr_fr`) translated into the suffixes actually published (`.es`, `.fr`).
* **Choice vs. State (`SCRIPTS_LANG` / `SCRIPTS_INSTALLED_LANG`):** `SCRIPTS_LANG` is the user's choice; `SCRIPTS_INSTALLED_LANG` is what is on disk. Separated, auto-synchronization can **detect a language switch**.
* **`effective_hook_lang()`:** Resolves the effective language of the hooks; `--lang` is no longer discarded on that path (documented behavior change).
* **Merge-Source Skip:** The `prepare-commit-msg` template skips the `message|merge|squash|commit` sources — commits generated by git preserve the original message.

### **20. External Linters Bridge and Interactive Assistant (`src/linter_wizard.py`, `src/ui/linter_app.py`)**

* **`--linter-setup` Assistant:** Interactive wizard with numbered presets (PHP_CodeSniffer, ESLint, Stylelint) and injection of the `external_linters` block into `.gitpr.linter.yml`.
* **Remote Presets:** `templates/gitpr.linter-presets.json` served from GitHub with a local → download → stale → embedded fallback resolution chain.
* **Linter Error TUI:** `src/ui/linter_app.py` (Textual) displays critical errors and warnings; under hook/quiet it prints and calls `sys.exit(1)`.
* **Markdown Report:** Consolidated into `.gitpr/reports/linter/` — only when there are violations.

### **21. SCM Multi-Forge (`src/infrastructure/scm/`)**

* **Single Abstraction (`ScmProvider` ABC):** `base.py` defines the contract (dataclasses `RepoRef`, `PullRequestDraft` etc. and `ScmProviderError(provider, http_status, message)` — `http_status` 0 = network failure); one concrete provider per forge in `github_provider.py`, `gitlab_provider.py`, `bitbucket_provider.py`, `azure_devops_provider.py`.
* **Registry and Factory:** `resolve_scm_provider()` selects by `GITPR_SCM_PROVIDER` (default `github` — zero migration, legacy GitHub token fallback intact); `detect_provider_from_remote()` identifies the forge from the origin URL.
* **Repository Addressing:** `parse_repo_ref(remote_url) -> RepoRef(raw, workspace, name, provider)` — workspace = GitHub owner / GitLab namespace (subgroups) / Bitbucket workspace / Azure `{org}/{project}` display.
* **`get_pull_request(repo, pr_id)` — a concrete ABC method 🆕:** The `create_release` pattern: the default raises `ScmNotSupportedError` and each of the four forges implements it. It exists because `list_open_pull_requests` pages **a single page** — filtering it by number silently loses old PRs and **does not distinguish closed from nonexistent**, and `review-pr` needs to reject the PR for the right reason before spending AI.
* **`supports_reviewable_diff` — capability gate 🆕:** Class attribute, `False` on Azure DevOps, checked **before any network call**. Azure's REST API returns a **list of files**, not a unified diff — reviewing would be inventing content.
* **GitLab Headers Synthesized 🆕:** `changes[].diff` is a **loose hunk**; `old_path`/`new_path` were ignored, so the file path was lost — the AI would review orphan hunks and neither the chunker nor the exclusion filter would have anything to lean on. The provider now assembles the `diff --git a/… / --- / +++` headers, honoring `/dev/null` for added/removed files.
* **GitLab `overflow` Raises 🆕:** An MR whose diff blows past the API limit was reviewed **halfway** and published as if it were whole; it now raises `ScmProviderError` with a clear message.
* **`request_pull_request_reviewers` returns `list[str]` 🆕 (contract break):** It returned `None`; it now returns the logins **effectively attached**, read from the body of the `201`. It is the only way to detect a login accepted and silently ignored. `None` is still treated as "cannot verify", never as a failure — old callers do not break, they merely lose the read-back. Third-party subclasses **must** be updated.
* **Two Read-Only GitHub Helpers 🆕:** `get_commit_author_login` (maps a SHA to the account linked to the author's e-mail — the reliable path for corporate addresses that the user-search API cannot see) and `get_user_login` (validates/canonicalizes a typed handle, rejects what cannot be a login **without spending a request** and distinguishes 404 from a transient error).
* **Fail-Fast per Forge:** Azure DevOps requires `GITPR_SCM_ORGANIZATION`/`GITPR_SCM_PROJECT`; Bitbucket requires `GITPR_SCM_USERNAME` (App Password = Basic auth user+token); `create_issue` on Azure raises `ScmNotSupportedError`.
* **Release Publishing:** `provider.create_release()` used by `gitpr release --publish` (GitHub creates the tag on the default branch; GitLab requires the tag to exist).
* **Artifacts:** Glossary + ADR-001/ADR-005 in `docs/plans/`; `docs/scm-multiforge.*.md` family in 5 languages; tests: 9 files, **282 scenarios** (+17 in this window).

### **22. `gitpr release` Subcommand — Changelog / Release Notes**

* **Flow:** `git log` between `--since` (default: last reachable tag, or the first commit) and `HEAD` → classification by Conventional Commits → suggested semantic bump (`--version <x.y.z>` overrides) → changelog assembly → optional AI executive summary → *prepend* to `CHANGELOG.md`. Local generation is the default — nothing is published or touched without a request.
* **Classifier (`src/commit_classifier.py`):** Classifies commits by Conventional Commits type (feat/fix/refactor/docs/chore/etc.) with a tolerant parser.
* **Builder with Translatable Sections (`src/changelog_builder.py`):** "✨ Features", "🐛 Bug Fixes", "⚠️ Breaking Changes", "Summary", "Contributors" etc. sections rendered via `__()` at runtime (they follow `--lang`).
* **Semantic Bump (`src/version_bump.py`):** Suggests the next version from the classified types (major for breaking, minor for feat, patch for fix) and validates `x.y.z` targets.
* **Publishing:** `--publish` creates the release on the configured forge (with explicit confirmation); `--draft` creates it as a draft (GitHub; GitLab has no draft concept); `--format markdown|json` for structured output; `--force` to overwrite. **6 options in the subcommand.**
* **Skill Template:** On first use it downloads `templates/gitpr.release.*.md` (5 languages) via `ensure_release_skill_template()` — never overwrites.
* **Release Pending Item 🆕:** `CHANGELOG.md` was **not** touched in this window — the last entry is still `[1.1.0] - 2026-09-13`, while `__version__` already says 1.2.0. Running `gitpr release` is the missing step.
* **Artifacts:** `docs/release-notes.*.md` family (5 languages), spec in `docs/plans/`, ADR-002 and ADR-003, release notes glossary.

### **23. `gitpr config` Subcommand — Configuration TUI**

* **Master-Detail Screen (`src/ui/config_app.py`):** Categories on the left, fields of the category on the right, edited inline. Header with search (`/`) and a pending-changes counter (`● N unsaved`); footer with `F1 Help · F2 Save · ^R Restore · / Search · Esc`. `General` is always the first entry in the menu.
* **Declarative Schema (`src/config_schema.py`) — the single source of truth:** 🆕 **13 categories** (General, AI Providers, Pull Request, Code Review, Issue, Blame, Linter, Release, SCM / Forge, Diff Filters, Skills, **Fix** and Advanced) and **61 `ConfigField`**, of which **8 are advanced** (+1 category and +5 fields in this window). Each field declares its category, widget type (`bool`/`int`/`str`/`enum`/`template`/`path`/`secret`/`version`/`words`), `show_if`, validators, version markers and download actions. Labels are `__()` literals for the i18n scanner.
* **`fix` Category 🆕:** The five `GITPR_FIX_*` gained an editable surface with descriptions that explain the consequence of each one (line budget, sensitive globs, require confirmation, create branch on the all-safe batch and the branch name template).
* **Context Filtering (`show_if`):** `GEMINI_*` / `DEEPSEEK_*` / `OLLAMA_*` appear according to the selected `DEFAULT_AI_PROVIDER`; `GITHUB_TOKEN_ENCRYPTED` appears with an empty provider or `github`; `GITPR_SCM_USERNAME` under `[Bitbucket]`; `GITPR_SCM_ORGANIZATION`/`GITPR_SCM_PROJECT` under `[Azure DevOps]`. Changing the `Select` re-filters the panel **immediately, without F2**.
* **Global Search (`/`):** Matches key or label in every category and **ignores the visibility filter** — searching for `deepseek` with Gemini selected finds the fields, to allow pre-filling them.
* **Two-Layer Validation:** **Offline** (type, enum, template with a known placeholder and mandatory `{datetime}`) blocks `F2` with an inline error; **online** (only for credentials changed in the session) runs in a worker with a 10s timeout and only blocks on `401`/`403` — a network failure still allows saving.
* **Restore (`Ctrl+R`):** Removes the line from `.env` instead of rewriting the default; **`Esc`** with pending changes asks for confirmation; the **Unknown** category preserves keys outside the schema in read-only mode.
* **Skills Section — the only project-scoped one:** Inline master-detail panel that edits the project's `.gitpr/skill/*.md` resolved from the calling directory, writing atomically and preserving CRLF/LF. `F2` writes the `.env` and the skill files **in the same pass**.
* **Forced Downloads:** Buttons that force re-downloading smart-excludes, translations, linter presets and thinking words, via a `force=` parameter chained through the loaders.
* **Lightweight Links Module (`src/doc_links.py`):** `doc_url()` moved out of `core.py` so that the UI can obtain the documentation link without importing `core`/AI SDKs.
* **Tests:** 5 files — `test_config_app.py` (78), `test_config_schema.py` (42), `test_config_validation.py` (40), `test_config_store.py` (22), `test_config_cli.py` (9). 🆕 `test_config_schema.py` now covers the new fields (the assertion that `advanced` only exists in Advanced still holds for the 61).
* **Artifacts:** `docs/config-tui.*.md` in 5 languages, plan `docs/plans/20260912_config_tui.md`, glossary `glossary-config-tui.md` (8 terms) and the grill survey.
* **Known debt:** `gitpr -h config` opens the TUI and ignores `-h` — the gate `if ctx.invoked_subcommand is not None: return` runs before the `help_flag` block; fixing it would change the behavior of `-h` for **all** subcommands (documented in `docs/config-tui.md`).

### **24. General Usage Log (`src/usage_log.py`)**

* **One Line per Command:** Writes to `~/.gitpr/logs/<uuid5>.log`, **one file per day**, with the command, arguments, repository, user and timestamp. It answers "what did I actually run, and when?".
* **Name Derived from the Date:** `uuid5(NAMESPACE_DNS, f"gitpr.usage.{YYYY-MM-DD}")` instead of random — a random name would require a counter or a state file to know which file is today's, and two concurrent processes could disagree.
* **Synchronous Write (explicit decision):** Unlike `log_local_metric`, which uses a daemon thread and therefore loses the write if the process exits early — unacceptable for a log that promises to record *every* command.
* **Never Prints:** The MCP server reserves stdout for JSON-RPC; an accidental `print` would corrupt the protocol. The module also never raises.
* **A Single Git Spawn:** `git config --get-regexp '^(remote\.origin\.url|user\.name|user\.email)$'` instead of the three idiomatic ones — ~40 ms instead of ~150 ms on Windows, on *every* run.
* **Its Own `_repo_label()`:** Without reusing `get_repo_name()` from `core.py` (regex hardcoded to `github.com`, would return `unknown/repo` on GitLab/Bitbucket/Azure) and without `parse_repo_ref`, which is a provider method and would require building a provider (token, `requests`) on every command.
* **Control:** `GITPR_SHOW_LOGS` (default `"true"`); turned off in `tests/conftest.py`.
* **Dogfooding 🆕:** The execution log of an earlier `pr_desc` was the evidence that isolated the reviewer bug — the line `Reviewers requested on PR #1068: ['Eduarda Leal']` proved that the **name** went out as a login and that `_request()` returned without raising. Without that record, the symptom ("it does not show up on the PR") could not have been distinguished from a network failure.
* **Artifacts:** `docs/usage-log.*.md` in 5 languages; `tests/test_usage_log.py` (27 scenarios).

### **25. `gitpr fix` Subcommand — Review Findings as Reviewable Patches (`src/fix/`) 🆕**

* **What It Is:** A **new** capability, not a generalization of the chat. The mandatory investigation (step 0 of the plan) took down three premises of the spec: the chat **never** applied any patch (F5 and `ctrl+s` write a `.txt` in the CWD, with no `subprocess` and no valid diff), a finding with `id`/`severity`/`file_path` **did not exist** anywhere, and `config.schema.yml` never existed.
* **The Real Pipeline:** last review from the cache (`resolve_last_review`) → **one** AI call (`call_ai_model`, never `generate_pr_content`, whose `else` is the PR branch) → extraction and validation of the unified diff → `git apply --check` → deterministic classification → dry run or write → history.
* **8-File Package (1147 lines):**
  * `patch_provenance.py` — pure data contract: `PatchSafety`, `FindingRef`, `PatchCandidate` (with a derived `patch_id`), `PatchProvenance`, `ApplyFixResult`.
  * `patch_extractor.py` — fenced blocks + unified diff validation; **shared with the chat**.
  * `patch_safety_classifier.py` — `safe`/`review_required`/`experimental`, logic that is **pure, I/O-free and AI-free**; returns a **reason code**, never a ready-made sentence.
  * `patch_applier.py` — the project's first `git apply` wrapper (`--check`, `apply`, `--reverse`, `checkout -b`, `status`).
  * `fix_history.py` — `.gitpr/fix_history.json` with atomic writing (`.tmp` + `os.replace`).
  * `apply_fix.py` — the use case (470 lines).
  * `rollback_fix.py` — `git apply --reverse` over the stored diff, with three distinct refusals.
  * `__init__.py` — **docstring only**, and it orders the modules on purpose (see ADR-004).
* **Deterministic Classification:** It refuses a patch that crosses more than one file or more than one hunk, that touches a configured sensitive path, that blows the added+removed line budget, that **deletes a line that looks like a call**, that the AI declared low-confidence, or that fails `git apply --check` against the current tree. `--force` **never** bypasses the applicability check — only the classification.
* **Opt-In Writing:** Dry run is the default; any mutation requires `--apply` (or a typed confirmation phrase with `--force`).
* **Support:** `src/diff_parser.py` gained `summarize_patch()` / `PatchSummary` (pure) — file count, hunk count, line delta and removed-call detection — used by the classifier.
* **MCP:** 13th tool `list_fix_candidates` (read-only) and the `skill://fix` resource.
* **Skill:** `templates/gitpr.fix.md` + `.pt_br.md` (persona: Senior Software Engineer), downloaded by `gitpr --skill`.
* **Configuration and Artifacts:** 5 `GITPR_FIX_*` variables + the `fix` category in the TUI; `docs/fix-command.*.md` (5 languages, 8 sections); ADR-004 and `glossary-gitpr-fix.md`; spec/plan/survey in `docs/plans/` and `docs/survey/`.
* **Tests:** 10 files in `tests/fix/` (**204 scenarios**) with real git repository fixtures (`tests/fix/git_fixture.py`) — the classifier matrix, the applier, the history, the CLI routing, the rollback, the settings and the extractor shared with the chat.

### **26. `gitpr review-pr` Subcommand — Remote PR Review (`src/review/`) 🆕**

* **What It Is:** Orchestration, **not** a second review engine. The existing engine (`generate_pr_content`), the linter (`parse_diff_and_lint`) and the renderer are the pieces of the local flow, fed with a diff that came from somewhere else — a remote review `.txt` and a local one of the same diff differ **only in the file name**.
* **5-File Package (560 lines):**
  * `diff_source.py` — `DiffOrigin` + `DiffSource`: pure provenance (`cache_scope`, `is_remote`), no I/O.
  * `diff_normalizer.py` — newline normalization, diff validation (`is_reviewable_diff`) and the smart-excludes filter **in Python**, for a diff that git has never seen.
  * `render.py` — composition and writing of the artifact, **extracted from `main.py`** and now **shared** with the local flows.
  * `remote_pr.py` — the use case: gate → PR → diff → normalize → exclude → engine → linter → optional comment.
  * `__init__.py` — package marker.
* **`split_patch_sections` (`src/diff_parser.py`) 🆕:** Returns the path of each file **alongside** its own text — the basis of the remote exclusion filter and of the GitLab header synthesis.
* **Read-Only by Default:** `--post-comment` is the **only** path that writes to the forge. The same holds for the MCP tool `review_remote_pr`, which does not even receive the argument.
* **Interaction with `fix` 🆕:** Since a remote review corresponds to no local tree, the reviewed diff is now stored in the cache (`reviewed_diff`) and `fix` prefers it — fixing a defect that would only have appeared after this feature.
* **Artifacts:** `docs/review-pr.md` + `.pt_br.md` (8 sections), `docs/code-review-ia.*.md` (remote mode as §1.4 + the external linter caveat, in the 5 versions), ADR-005 and `glossary-review-pr.md`; spec, plan and survey in `docs/plans/` and `docs/survey/`.
* **Tests:** 4 files in `tests/review/` (**97 scenarios**) — `test_remote_pr.py` (40), `test_review_pr_cli.py` (25), `test_diff_normalizer.py` (20), `test_diff_source.py` (12) — plus 6 new scenarios in `tests/scm/test_gitlab_provider.py` and 11 in `tests/test_mcp_server.py`.

### **27. Reviewer Identity Resolution (`src/reviewer_resolution.py`) 🆕**

* **The Bug (two chained silent failures):** (1) **Empty prefill** — `_reviewer_suggestion_view()` built `handles` only from `provider.email_to_handle()`, which sees only `users.noreply.github.com` e-mails or e-mails with a **public** address; with a corporate e-mail (the real case: `eduardaleal@grafjb.com.br`) nothing resolves, the `Input` is born empty and the hint shows only the **name**. (2) **Unverified attach** — `_attach_reviewers()` passed the field values through **verbatim**; GitHub answers **201 without attaching anyone**, `_request()` returns without raising and the log line is written: apparent success, reviewer missing, no warning at all.
* **New Module (159 lines):** A plan, with no I/O of its own, **that never raises**. `resolve_candidates()` (before the TUI) and `resolve_typed_reviewers()` (at attach time); `match_candidate()` matches **exact and normalized** by login, name or e-mail.
* **Resolution Ladder:** already-known handle (no request) → exact match with a suggested person → lookup by e-mail (`email_to_handle`) → validation of the login on the forge (`get_user_login`).
* **What does not resolve is never sent:** It leaves in `ResolutionOutcome.dropped` as `(value, i18n_reason)` and is discarded with a visible warning, instead of becoming an empty `201`.
* **Duck-Typed Provider:** Access is via `getattr`, so fakes and forges without the new methods keep working — the existing suite needed no rewrite.
* **Read-Back on GitHub:** `request_pull_request_reviewers` reads `requested_reviewers` from the body of the `201` and returns the logins **actually** attached; a batch rejected with `422` (GitHub rejects the whole batch when a single login is ineligible) is retried **one by one**, instead of taking down the valid reviewers as well.
* **Adjacent Utilities:** `_identity_key` → `identity_key` (public), a new `normalize_identity()`, and `ReviewerCandidate` now carries `last_commit_hash` so the aggregation keeps the commit of the most recent touch.
* **Diagnosis:** The tool's own usage log was the originating evidence — see §24.
* **Artifacts:** `docs/suggested-reviewers.*.md` re-synchronized in the 5 languages, `ADR-002-reviewer-suggestion.md` and `glossary-reviewer-suggestion.md`, plan and survey from the 4-round grill.
* **Tests:** `tests/test_reviewer_resolution.py` (18, **new**) + expansions in `test_github_provider.py` (68), `test_pr_publish_app.py` (45), `test_suggest_reviewers.py` (17), `test_reviewer_suggestion.py` (18) and `test_main_suggest_reviewers.py` (8).

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
| `tests/test_config_schema.py` | 42 | `DEFAULT_CONFIG` coverage, no duplicates, categories/kinds, `advanced` only in Advanced |
| `tests/test_config_store.py` | 22 | Round-trip on a temporary `.env`, comments and order preserved, idempotent `remove_config_value()` |
| `tests/test_config_suggest_reviewers.py` | 8 | Suggested reviewers configuration (keys and defaults) |
| `tests/test_config_validation.py` | 40 | Types, enums, templates, `validate_ai_key()` with a mocked SDK (401 vs. network vs. ollama) |
| `tests/test_core.py` | 49 | Main flows, git diff, PR generation, timing, staging, co-authorship, hooks language |
| `tests/test_diff_parser.py` | 26 | Diff parser by lines/hunks + `summarize_patch()` and `split_patch_sections()` 🆕 |
| `tests/test_external_linters.py` | 33 | Checkstyle bridge: XML parser, subprocess, diff cross-referencing, report |
| `tests/test_i18n.py` | 20 | Language parity (1048×6), missing/orphan keys, identity |
| `tests/test_install_wizard.py` | 3 | Interactive installation assistant |
| `tests/test_issue_engine.py` | 4 | Structured issue draft |
| `tests/test_linter_metrics.py` | 4 | Linter metrics: errors, warnings, duration |
| `tests/test_linter_presets.py` | 5 | Linter presets: resolution and forced re-download |
| `tests/test_main_suggest_reviewers.py` | 8 | `--no-suggest-reviewers` flag in the CLI and in the contextual help |
| `tests/test_mcp_prompts.py` | 11 | MCP prompt templates and language fallback |
| `tests/test_mcp_server.py` | 104 🆕 | MCP tools (14), resources (18), annotations, patching, direct CLI, offload |
| `tests/test_mcp_server_e2e.py` | 6 | Real MCP server via subprocess + JSON-RPC stdio |
| `tests/test_metrics.py` | 34 | Collection, local export, repo scope, cache token summary |
| `tests/test_net_timeouts.py` | 12 | Network/AI timeouts — **2 outdated assertions (600s)** |
| `tests/test_plugins.py` | 17 | Plugin discovery, linter rule merge, MCP prompts |
| `tests/test_pr_publish_app.py` | 45 | PR Publisher TUI: screens, flows, suggested reviewers, `NoticeScreen` |
| `tests/test_pr_publish_linter_modal.py` | 4 | Linter error modal: abort, no-verify |
| `tests/test_pre_save.py` | 3 | --pre-save flag and JSON payload |
| `tests/test_release_cli.py` | 4 | Release CLI: options, help with documented epilog |
| `tests/test_release_engine.py` | 27 | Release engine: commit range, CHANGELOG, publishing |
| `tests/test_reviewer_resolution.py` | 18 🆕 | Resolution ladder, rejection of a partial name, dedup, failure tolerance |
| `tests/test_reviewer_suggestion.py` | 18 | Reviewer suggestion logic (ranking, exclusion, top-N, `last_commit_hash`) |
| `tests/test_skill_command.py` | 10 | Skill template download and validation |
| `tests/test_skill_context.py` | 14 | `get_skill_context()` with `quiet=True`, fallbacks, skill registry |
| `tests/test_smart_excludes.py` | 15 | Smart pathspec filter and forced re-download |
| `tests/test_suggest_reviewers.py` | 17 | Suggested reviewers in the PR flow (integration, `no_login` hint) |
| `tests/test_thinking_words.py` | 5 | Loading, parsing with the `;` separator and forced reload |
| `tests/test_updater.py` | 23 | PyPI gate: version parsing, daily cache, fetch, gate decisions, CLI wiring |
| `tests/test_usage_log.py` | 27 | Usage log: date-derived name, synchronous write, silence, multi-forge `_repo_label()` |
| `tests/test_version_bump.py` | 17 | Semantic bump: major/minor/patch, targets and validation |
| `tests/fix/test_apply_fix.py` | 53 🆕 | Full use case: review → AI → validate → classify → dry-run/apply |
| `tests/fix/test_chat_shared_extractor.py` | 8 🆕 | Extractor shared between the chat and `fix` (identical behavior) |
| `tests/fix/test_fix_cli.py` | 31 🆕 | Subcommand routing, options, dry-run default, `--force` |
| `tests/fix/test_fix_history.py` | 21 🆕 | `.gitpr/fix_history.json` ledger: atomic write, read |
| `tests/fix/test_fix_settings.py` | 10 🆕 | The five `GITPR_FIX_*` and the invalid-value fallbacks |
| `tests/fix/test_patch_applier.py` | 19 🆕 | `git apply` wrapper: check/apply/reverse, branch, status |
| `tests/fix/test_patch_extractor.py` | 13 🆕 | Fenced blocks → validated unified diff |
| `tests/fix/test_patch_safety_classifier.py` | 22 🆕 | safe/review_required/experimental matrix and reason codes |
| `tests/fix/test_resolve_last_review.py` | 13 🆕 | Last review selection, exclusion of per-file reviews |
| `tests/fix/test_rollback_fix.py` | 14 🆕 | `--rollback`: reverse and the three refusals |
| `tests/review/test_diff_normalizer.py` | 20 🆕 | Newlines, diff validation, smart-excludes in Python |
| `tests/review/test_diff_source.py` | 12 🆕 | `DiffOrigin`/`DiffSource`: provenance, `cache_scope`, `is_remote` |
| `tests/review/test_remote_pr.py` | 40 🆕 | Orchestration: gates, PR, diff, linter, optional comment |
| `tests/review/test_review_pr_cli.py` | 25 🆕 | `review-pr` CLI: options, rejections before the AI, `--post-comment` |
| `tests/scm/test_azure_devops_provider.py` | 43 | Azure DevOps provider: org/project, PRs, `ScmNotSupportedError` |
| `tests/scm/test_bitbucket_provider.py` | 39 | Bitbucket provider: Basic auth, workspace |
| `tests/scm/test_contract.py` | 38 | `ScmProvider` contract: signatures, dataclasses, errors |
| `tests/scm/test_factory.py` | 11 | `resolve_scm_provider()` + `detect_provider_from_remote()` |
| `tests/scm/test_github_api_shim.py` | 18 | Deprecated `github_api` shim → delegates to the provider |
| `tests/scm/test_github_provider.py` | 68 🆕 | GitHub provider: REST, headers, PRs, issues, releases, reviewer read-back |
| `tests/scm/test_gitlab_provider.py` | 47 🆕 | GitLab provider: API v4, namespace, **synthesized headers and `overflow`** |
| `tests/scm/test_init_wizard.py` | 10 | `--init` wizard: forge detection, validation, persistence |
| `tests/scm/test_release_publish.py` | 8 | Release publishing per forge (GitHub/GitLab) |
| `tests/sync_i18n.py` | — | i18n coverage verification script (scaffold; never executed) |

**Total:** **1437 collected scenarios in 64 test modules** (41 at the root + 9 in `tests/scm/` + **10 in `tests/fix/`** 🆕 + **4 in `tests/review/`** 🆕; **+377** since the previous report, with **15 new files**). Full run on this machine with `GITPR_LANG=en_us`: **1432 passed / 3 failed / 2 skipped / 81 subtests** in ~347s.

**Quality notes for this version:**
- **No new failures.** The 3 failures are exactly the same as in the previous report — and the two timeout ones remain inherited from before:
- **2 real failures (outdated tests, inherited):** `test_net_timeouts.py::test_ai_timeout_defaults_to_600` and `::test_invalid_ai_timeout_falls_back_to_default` assert the 600s default for `GITPR_AI_TIMEOUT`, but the code has used **180s** since fix `681a7fa`. It is the same item that was already in the Next Steps of **two** previous reports and it **remains open**.
- **1 locale failure (inherited, not a regression):** `test_core.py::TestHooksLanguage::test_the_language_chosen_with_the_lang_flag_is_honoured` asserts `i18n.CURRENT_LANG == "pt_br"` — it passes with the machine's pt-BR locale and fails with `GITPR_LANG=en_us`. It remains the only locale-sensitive test in the suite.
- **Growth of 377 scenarios with the failure baseline intact** — the relevant signal of this window: the three new features landed without taking down or masking anything.
- `tests/conftest.py` keeps `GITPR_SHOW_LOGS=false` and `GITPR_SKIP_UPDATE_CHECK=true` — the suite neither writes to the usage log nor is blocked by the update gate.
- 🆕 **Real git fixtures:** `tests/fix/git_fixture.py` builds real git repositories, instead of mocking `subprocess` — `patch_applier` is only honest if `git apply` is the real one.

---

## **🌐 Internationalization and Documentation**

* **i18n Coverage:** **1048 translation keys** in each of the 6 dictionaries (+93 since the previous report) with **full key set parity**. The chain measured per commit was 955 → 1022 (`fix`, +67) → 1028 (reviewers, +6) → **1048** (`review-pr`, +20). `__lang_version__` went v0.0.25 → v0.0.26 → v0.0.27 → **v0.0.28**, triggering the OTA re-download of the translations.
* **Translation sources in lockstep:** a new key must exist in the code (source), in `langs/pt_br.json` (**master list**), in the FR/ES dicts of `scripts/sync_all_langs.py` (second source) and in the curated values of `scripts/fix_mangled_i18n_keys.py` (third source, read by `tests/test_i18n.py`); the `len(CLEAN_KEYS)` assertion still stands at 49.
* **New topics 🆕 (2):**
  - `docs/fix-command.md` — review findings as patches: pipeline, safety classification, reading before writing, history and rollback, skill, MCP and variables — **in 5 languages**
  - `docs/review-pr.md` — remote PR review: what it is, which forges can be reviewed, the report, publishing, skill, MCP and variables — **in EN + PT-BR** (the remaining 3 languages are pending)
* **Topics updated in this window:** `docs/code-review-ia.*` (5 — remote mode as §1.4 and the caveat that the external linter does not run), `docs/suggested-reviewers.*` (5 — identity resolution), `docs/fix-command.*` (5 — §2.2 rewritten: the diff now comes from the record), `docs/commit-message-ia.*` (5), `docs/issue-tui-help.*` (5), `docs/linter-regras-customizadas.*` (5), `docs/providers-ia.*` (5) and `docs/skill-template.*` (5), plus `README.md`/`README.pt_br.md` (2×) and `CLAUDE.md` (command table, MCP tools 13 → 14, tree with `src/review/`).
* **Documentation in 5 languages:** **41 canonical topics** in `docs/` — **35 with full coverage in the 5 languages** (+1 since the previous report) and **6 partial/PT-only topics** (`como_reverter_commit_git_localmente`, `github-issue-prompt-com-gh`, `otimizacao-de-tokens`, `testar_sem_usar_pypi`, `version-markers` and, now, `review-pr` with 2 languages).
* **Claude Code local skills:** `.claude/skills/` with **29 skills** (count unchanged in this window).
* **Memory Index:** `.claude/memory/MEMORY.md` with 40 patterns (count unchanged in this window).
* **Task reports:** `docs/claude-code/reports/develop_natan/` (**93** in total; **+3** in the window — `gitpr fix`, reviewer login resolution and remote PR review) and `docs/gemini/reports/develop_natan/` (5 files; no new ones).
* **Status reports:** `docs/reports/` (14 reports; this is the 15th).
* **Development plans:** 99 files in `docs/plans/` (+10 in the window — the `fix` and `review-pr` specs/plans, the reviewer suggestion fix, ADR-004, ADR-005 and the `glossary-gitpr-fix` and `glossary-review-pr` glossaries) + **6 files in `docs/survey/`** (+3 in the window).

---

## **🔄 Distribution Pipeline**

1. **PyPI (single channel):** `python -m build` → `twine upload dist/*` → `pip install gitpr-cli`
2. **Mandatory update:** execution checks PyPI at startup and **blocks with exit code 1** if there is a newer version, printing `pip install --upgrade gitpr-cli`; the check is cached per day and `--update` only reports
3. **GitHub Releases:** removed — no PyInstaller, no `.exe` asset, no hot-swap
4. **GitHub Actions:** Workflow `pr-review.yml` + `action.yml` (installs via pip)
5. **MCP Server:** `gitpr-mcp` entry point via `pyproject.toml`
6. **Templates and OTA languages:** `templates/` and `langs/*.json` served from GitHub (main) — the `v0.0.28` bump renews the local copies in `~/.gitpr/langs/` once published
7. **State of the 1.2.0 release 🆕:** `__version__` went to 1.2.0 **in the working tree** and has not yet been committed or tagged (HEAD at 1.1.0, last tag `v1.1.0`); `CHANGELOG.md` still stops at `[1.1.0] - 2026-09-13`. The path is to run `gitpr release` and commit the bump.

---

## **📈 Evolution Since the Previous Report (v0.0.14)**

| Area | v0.0.14 (previous) | v0.0.15 (current) |
|------|-------------------|-----------------|
| **GitPR Version** | 1.1.0 | **1.2.0** (bump **not committed**; HEAD at 1.1.0, last tag `v1.1.0`, CHANGELOG still at `[1.1.0]`) |
| **Language Version** | v0.0.25 | **v0.0.28** (via v0.0.26 and v0.0.27) |
| **Hook Scripts Version** | v0.0.3 | **v0.0.3** |
| **AI Providers** | Gemini + DeepSeek + Ollama | Gemini + DeepSeek + Ollama |
| **Languages** | 5 languages, 6 dictionaries | 5 languages, 6 dictionaries |
| **Interface** | CLI + TUIs (Issues, Chat, Dashboard, PR Publisher, LinterApp, ConfigApp) + `--init`/`--install` wizards + `gitpr release`/`gitpr config` | **+ `gitpr fix` (reviewable patches) + `gitpr review-pr` (remote PR review) + `NoticeScreen`** |
| **MCP Tools** | 12 tools / 17 resources / 7 prompts | **14 tools / 18 resources / 7 prompts** (+`list_fix_candidates`, +`review_remote_pr`, +`skill://fix`) |
| **CLI Flags** | 35 options at the root + `release` (6) + `config` (0) | **35 at the root** + `release` (6) + `config` (0) + **`fix` (8)** + **`review-pr` (2)** |
| **Environment Variables** | 39 keys in `DEFAULT_CONFIG` | **44 keys** (+5 `GITPR_FIX_*`) |
| **Linter** | Regex + Checkstyle bridge (wizard/TUI/report) | Unchanged (+ **`skip_external`**: the remote review does not publish warnings from the local tree) |
| **Git Hooks** | Fixed: `HOOK_SCRIPT_SUFFIXES`, `SCRIPTS_LANG`, `effective_hook_lang()` | Unchanged |
| **Commit Messages** | With `Co-Authored-By` trailer (opt-out) | Unchanged |
| **SCM Layer** | 4 forges, `create_release`, `create_issue` | **+ `get_pull_request` (concrete on the ABC) + `supports_reviewable_diff` + reviewer read-back (`list[str]`, contract break) + GitLab fixes** |
| **i18n (keys per file)** | 955 × 6 (full parity) | **1048 × 6 (full parity) — +93 keys** |
| **Documentation** | 39 canonical topics (34 complete + 5 partial) | **41 canonical topics (35 complete + 6 partial) — 2 new families, 8 updated** |
| **Distribution** | PyPI exclusive | Unchanged (1.2.0 release pending) |
| **Test Suite** | 1060 scenarios (49 files) | **1437 scenarios (64 files: 41 + 9 SCM + 10 fix + 4 review) — en_us: 1432 passed / 3 failed (2 outdated + 1 locale) / 2 skipped** |
| **Commits since the report** | 2 commits | **5 commits** (`f4d5186`, `f5bed07`, `b9dd930`, `eb55400`, `a8a7770`) |
| **Merged PRs** | 2 PRs (#162, #164) | **3 PRs (#167, #171, #173)** |
| **Memory Index** | 40 patterns | **40 patterns** |
| **Task reports** | 90 claude-code, 5 gemini | **93 claude-code (+3 in the window) and 5 gemini** |
| **Development plans** | 89 plans, 3 surveys | **99 plans (+10), 6 surveys (+3)** |
| **Repository hygiene** | — | **+ `pypa/`/`pip/` removed from tracking, `.gitignore` updated** 🆕 |

---

## **🚧 Next Steps**

* **Close the 1.2.0 release 🆕:** `__version__` is at 1.2.0 in the working tree with no commit, no tag and **no entry in `CHANGELOG.md`** (which stops at `[1.1.0]`). Run `gitpr release`, commit the bump and tag — it is the only item on this list that blocks publication.
* **Translate what was left partial 🆕:** `docs/review-pr.*.md` exists only in EN and PT-BR (pt_pt, es_es, fr_fr are missing) and `templates/gitpr.fix.md` only in EN and PT-BR — it is the first time in several windows that a new topic does **not** arrive complete in the 5 languages.
* **Document the `request_pull_request_reviewers` contract break 🆕:** it went from `None` to `list[str]`; third-party subclasses of `ScmProvider` must be updated. `docs/scm-multiforge.*.md` is the place, in the 5 versions.
* **`.gitpr/metrics/export/` in `.gitignore` 🆕:** PR #171 committed `gitpr_metrics_2026-09-17.csv`/`.json` — they are locally generated artifacts, like the August ones that are already tracked.
* **Anthropic Claude provider:** Direct support for the Claude API (`claude-sonnet-5`).
* **ASCII/Textual charts in the Dashboard:** Add time histograms and token trend charts to the metrics TUI.
* **Release Pipeline in GitHub Actions:** Full automation of the build and the upload to PyPI (changelog generation is now local via `gitpr release`).
* **Local `.gitpr/conf/` seed:** The seeding of local configuration templates (smart-excludes, linter) remains pending as its own subcommand or a wizard step; the configuration TUI offers the **downloads** of those files, but not the project seed.
* **More providers:** Direct OpenAI, additional local providers.
* **`sync_i18n.py` i18n extractor:** The regex truncates literals with implicit concatenation (`__("a " "b")`) — migrate to AST (the guard in `test_i18n.py` already uses AST and does not depend on the script).
* **Fix the outdated timeout tests:** `tests/test_net_timeouts.py` asserts a 600s default, but the code has used 180s since fix `681a7fa`; also align the stale docstring in `config.py`. **Inherited item, still open — three reports now.**
* **Reconcile the project version:** `CLAUDE.md` still says `Current version: 0.0.37` while `__version__` is at 1.2.0 — the gap only widened (+0.0.37 vs. 1.1.0 in the previous report). Define a single convention and update the `CLAUDE.md`. **Inherited item, still open.**
* **README index debt:** the bullets for the `suggested-reviewers`, `scm-multiforge`, `config-tui`, `usage-log` **and now `fix-command` and `review-pr`** families are not in the index — the debt grew in this window.
* **Locale robustness in the tests:** 1 test is sensitive to the machine's pt_br locale (`test_core.py::TestHooksLanguage`) — pin `GITPR_LANG=en_us` in the setup or mock `TRANSLATIONS` so the suite is 100% green on any machine/CI.
* **`gitpr -h config` ignores `-h`:** the subcommand opens the TUI instead of showing the help — the gate `if ctx.invoked_subcommand is not None: return` runs before the `help_flag` block. Fixing it would change the behavior of `-h` for **all** subcommands, so it needs a decision.
* **Smart Exclude section in the TUI:** of the 12 items reported after using the screen, item 10 (the *Smart Exclude* section) is the only deliverable not yet started.
* **Debts recorded in the config TUI plan:** `DEFAULT_CONFIG` became redundant with the schema; the opening banner does not list `--dashboard`, `--init`, `--base` or `--plugins`; `LinterApp` does not disable the command palette.

### ✅ Completed in This Window (2026-09-13 → 2026-09-17)

* ~~**`gitpr fix` subcommand**~~ — `src/fix/` package (8 files, 1147 lines), deterministic `safe`/`review_required`/`experimental` classifier, dry run by default, `.gitpr/fix_history.json` and `--rollback` (PR #167).
* ~~**`resolve_last_review()` + `reviewed_diff` in the cache**~~ — the review that feeds `fix` is now chosen by repo+branch, excluding file-scoped reviews.
* ~~**Reviewer identity resolution**~~ — `src/reviewer_resolution.py`, `request_pull_request_reviewers` returning `list[str]`, read-back of the `201`, individual retry on `422` and `NoticeScreen` (PR #171).
* ~~**`gitpr review-pr` subcommand**~~ — `src/review/` package (5 files, 560 lines), read-only by default, `.txt` named after the source branch, `--post-comment` as the only write path (PR #173).
* ~~**`ScmProvider.get_pull_request` + `supports_reviewable_diff`**~~ — concrete method on the ABC implemented in the 4 forges; Azure blocked before any network call.
* ~~**Latent GitLab fixes**~~ — `diff --git` headers synthesized from `old_path`/`new_path` and `overflow: true` now raising.
* ~~**`skip_external` in the linter**~~ — the remote review no longer publishes warnings from the external bridge, which runs against the local tree.
* ~~**MCP: 12 → 14 tools and 17 → 18 resources**~~ — `list_fix_candidates` + `skill://fix` and `review_remote_pr`.
* ~~**i18n: +93 keys and chain v0.0.25 → v0.0.28**~~ — full key set parity across the 6 dictionaries.
* ~~**Repository hygiene**~~ — vendored pip 25.2 tree removed from tracking; `.gitignore` with `pypa/` and `pip/` (commit `f5bed07`).

---

**Report generated on:** 2026-09-17  
**Branch:** `develop_natan`  
**Author:** Natan Fiuza ([contato@natanfiuza.dev.br](mailto:contato@natanfiuza.dev.br))
