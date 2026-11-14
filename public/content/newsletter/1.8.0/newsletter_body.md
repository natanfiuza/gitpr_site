# GitPR 1.8.0 — What's New

## What's New in This Version

- **Map-Reduce stopped having a single ceiling:** `get_map_reduce_thresholds(provider)` resolves the trigger and batch size per provider (`_GEMINI`, `_DEEPSEEK`, `_OLLAMA` → generic → 90,000/50,000), and the slicer became a **cascade**: file → hunks (`@@`) → lines, always preserving the header and the `[file - part N]` marker. An intermediate pre-reduce consolidates partial summaries that still exceed the trigger ([ADR-0022](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0022-map-reduce-per-provider-token-partitioning.md)).
- **Forge detection was fixed and the silent fallback became a warning:** `github.com` is now recognized **before** returning `default=None` — without this, every GitHub repository was treated as an unknown host and received a spurious `scm.provider`. When the remote exists but does not name a known forge, `resolve_scm_settings` now warns and points to `gitpr --init`.
- **Migration notes gained contracts:** [ADR-0020](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0020-per-repo-scm-provisioning-migration.md) formalizes per-repository SCM provisioning (scripts and images must not write the legacy keys), and [ADR-0021](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0021-strict-boolean-update-check-migration.md) locks down the strict semantics of `GITPR_SKIP_UPDATE_CHECK`.
- **The editor extension gained a reproducible publishing path:** the marketplace guide was translated into all **five languages**, and `scripts/marketplace-setup-wizard.sh` walks through the five credential stages (Azure DevOps PAT, Open VSX namespace, GitHub Actions secrets), validating each token against the live API before saving it.
- **Metrics and Map-Reduce documentation was synchronized:** the **Charts & Trends** tab, Token Trend, and Latency Histogram entered all five languages, with a translation parity guard that prevents an EN-only edit from slipping through unnoticed.
- **The test suite became stable on Windows:** the headless `CommitApp` flow is skipped on `win32` (Textual's `pilot.pause` blocks without a real TTY) and CLI routing mocks `check_internet_connection`, eliminating the network dependency.
- **1.8.0 was published:** `__version__` = **1.8.0**, `CHANGELOG.md` has `## [1.8.0] - 2026-11-14`, tag `v1.8.0` and PyPI serving 1.8.0.

## How to Use

Update to version 1.8.0 via pip:

```bash
pip install --upgrade gitpr-cli
```

Tune Map-Reduce thresholds per AI provider in `~/.gitpr/.env` (or interactively via `gitpr config` under the `ai` category):

```bash
# Example: custom trigger and batch size for Ollama vs. Gemini
GITPR_MAP_REDUCE_MAX_TOKENS_OLLAMA=16000
GITPR_MAP_REDUCE_MAX_PART_TOKENS_OLLAMA=8000
GITPR_MAP_REDUCE_MAX_TOKENS_GEMINI=120000
GITPR_MAP_REDUCE_MAX_PART_TOKENS_GEMINI=60000
```

If `resolve_scm_settings` warns that your `origin` remote host is unrecognized, bind the repository's SCM provider and credentials explicitly:

```bash
gitpr --init
```

## Useful Tips

- `gitpr config` opens a master-detail screen over `~/.gitpr/.env`, so nobody has to remember whether a value is `true`, `1` or `yes`: each field gets the control its type deserves and an invalid value is hard to type. F2 validates and saves, Ctrl+R restores the default (removes the line), `/` searches labels and variables across every category, and Esc leaves after asking before discarding. It edits the same file you already had — not a second source of configuration.
