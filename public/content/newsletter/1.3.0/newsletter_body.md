# GitPR 1.3.0 — What's New

## What's New in This Version

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

## How to Use

Upgrade from PyPI:

```
pip install --upgrade gitpr-cli
```

Try it before configuring anything — the tour needs no API key, no git repository and no network:

```
gitpr demo                             # guided tour of a commit message, a review and a PR
gitpr demo --lang=en_us                # the tour in your language
gitpr demo --no-tui                    # plain text, for CI and recordings
```

The new subcommands — every one of them is read-only until you pass the switch that writes:

```
gitpr badge                            # prints the badge snippet for your README (writes nothing)
gitpr split                            # the plan: one atomic commit per concern (writes nothing)
gitpr split --apply                    # commits each group; the tree ends byte-identical
gitpr tests generate                   # a test file following your repo's convention (writes nothing)
gitpr tests generate --file src/core.py --apply
gitpr explain                          # a reviewer guide for the current diff
gitpr --explain                        # appends that guide to the generated PR description
```

A bare `gitpr split` asks before committing anything — and `--apply` resets the index to HEAD, so anything you had already staged has to be staged again (the file contents are never touched). `gitpr tests generate` overwrites nothing without `--apply`, and when it does, the confirmation dialog opens with **No** pre-selected.

Secret scanning now runs on **every** linter invocation, with rules that live inside the package and cannot be replaced by `--skill` or rewritten by the wizard — so a commit that used to pass can now be blocked. `GITPR_LINTER_SECURITY=false` is the escape hatch. The Semgrep/Gitleaks/Bandit bridges stay strictly opt-in (`GITPR_SAST_*_ENABLED`), and a tool enabled but missing from your `PATH` is a warning, not a failure.

## Useful Tips

The linter is free: no API keys, no AI calls, only the added lines of your diff. Exit code 0 = pass, 1 = violations, so it's ideal as a GitHub Actions quality gate that blocks secrets and debug code before human review — the docs include the complete workflow.
