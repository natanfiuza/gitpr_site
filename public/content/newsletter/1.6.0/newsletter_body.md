# GitPR 1.6.0 — What's New

## What's New in This Version

- **The forge became a property of the repository, not of the machine:** `src/scm_settings.py` resolves, in a single function, **which forge, which token and which base URL** apply in that repository — project override → `origin` remote → `github`. Each forge got its own credential slot in `~/.gitpr/.env` (`GITHUB_/GITLAB_/BITBUCKET_/AZURE_DEVOPS_TOKEN_ENCRYPTED` + `*_BASE_URL`), and the legacy `GITPR_SCM_PROVIDER`/`GITPR_SCM_TOKEN_ENCRYPTED`/`GITPR_SCM_BASE_URL` keys are migrated **a single time, idempotently** ([ADR-016](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/plans/ADR-016-per-provider-scm-settings.md)).
- **Automatic issue closing is no longer GitHub-only:** GitLab and Bitbucket implement the contract read-back (`get_issue`, `get_default_branch`, `closing_line`) and Azure DevOps degrades to `not-supported` instead of ignoring the feature. The local registry moved to `.gitpr/conf/issue.yml`, with transparent reading of the legacy `github.yml` and `IssueInfo.is_open` ([ADR-010](https://github.com/gitpr-cli/gitpr.git/blob/main/docs/adr/0010-multiforge-issue-autoclose.md)).
- **The local configuration bootstrap became a command:** `gitpr seed` (and the `-sd/--seed` flag) seeds `.gitpr/conf/gitpr.config.yml`, `.gitpr/conf/gitpr.smart-excludes.json` and `.gitpr/skill/.gitpr.linter.yml`, with `--force` to overwrite and `--all` to include all AI skills. The installation wizard was reordered into **5 steps**, starting with the project's local configuration.
- **The configuration TUI gained two new surfaces:** the **Smart Excludes** category (sources list + project file editor) and the **check** category (the 9 `GITPR_CHECK_*`). And `DEFAULT_CONFIG` is no longer a manual copy — it is now **derived from the schema** via `ConfigField.seeded`, so code and schema can no longer diverge.
- **The dashboard gained trend and latency, and the prompt stopped receiving binary garbage:** `metrics` now renders **Token Trend** (sparkline + daily bars, 14-day cap) and **Latency Histogram** (mean, median and P95) in the CLI, in the TUI and in the MCP tool `get_usage_metrics`; `collapse_binary_hunks()` rewrites hunks flooded with `U+FFFD` as `Binary files … differ`, and the smart excludes catalog gained **100+ patterns** in 8 binary families.
- **The suite no longer stalls, and 1.5.0 is on PyPI:** `-c` only opens `CommitApp` when a real TTY exists (`_may_ask_the_user()`), which **unblocked** the scenario that froze the full run at ~33%, and the commit headless test was stabilized. The suite now runs to the end — **3422 scenarios in 159 files**. **1.6.0** is in `CHANGELOG.md` and on `develop_natan`, but **it still has no tag or publication**.

## How to Use

1.6.0 is in `CHANGELOG.md` and on the `develop_natan` branch, but it still has no tag; at the time of this edition PyPI serves 1.5.0. Once `v1.6.0` is published, update with:

```
pip install --upgrade gitpr-cli
```

To create the project's local configuration explicitly and idempotently, run `gitpr seed` (or `gitpr -sd`). It writes `.gitpr/conf/gitpr.config.yml`, `.gitpr/conf/gitpr.smart-excludes.json` and `.gitpr/skill/.gitpr.linter.yml`, never overwrites an existing file, and accepts `--force` to overwrite and `--all` to also download every AI skill.

To pin the forge per repository, version the `scm` section of this file with the project:

```yaml
scm:
  provider: gitlab
```

GitPR only writes `scm.provider` when the chosen forge differs from the one detected in `origin`; the project override wins over remote detection. Tokens live in `~/.gitpr/.env`, one slot per forge, and the legacy `GITPR_SCM_*` keys migrate on their own on the first run.

## Useful Tips

GitPR publishes to four forges, not one: GitHub, GitLab, Bitbucket Cloud and Azure DevOps. The forge is auto-detected from the `origin` URL, and `gitpr --init` walks the setup — confirms the provider, asks for the extras (Azure organization and project, Bitbucket username), validates the token up to three times and only then writes the Fernet-encrypted credential to `~/.gitpr/.env`. A self-managed GitLab or Azure needs only `GITPR_SCM_BASE_URL`.
